<?php

class registerPermissions extends classes\Classes\Object {
    
    //objetos da classe
    protected $acc         = NULL;
    protected $perm        = NULL;
    protected $user_perfil = NULL;
    private   $insert      = array();
    //variaveis de estado
    private $perfis = array();
   
    public function __construct() {
        $this->LoadModel('usuario/perfil'   , 'user_perfil');
        $this->LoadModel('plugins/acesso'   , 'acc' );
        $this->LoadModel('plugins/permissao', 'perm');
    }
    
    public function unregister($cod_plugin, $permissoes){
        $names = array();
        foreach($permissoes as $perm){
            $names[] = trim($perm['nome']);
        }
        $in     = implode("','", $names);
        $all    = $this->perm->selecionar(array('plugins_permissao_cod'), "cod_plugin = '$cod_plugin' AND plugins_permissao_nome NOT IN('$in')");
        if(empty($all)){return;}
        
        $remove = array();
        foreach($all as $a){
            $remove[] = $a['plugins_permissao_cod'];
        }
        $in2 = implode("','", $remove);
        $this->perm->simpleDrop("plugins_permissao_cod IN ('$in2')");
    }
    
    public function register($cod_plugin, $permissoes){
        //inicializa as variaveis
        $this->insert        = array();
        $this->perfis        = $this->user_perfil->selecionar(array('usuario_perfil_cod'));
        $preparedPermissions = $this->preparePermissionsToInsertion($cod_plugin, $permissoes);
        
        //percorretodas as permissoes do plugin
        foreach($preparedPermissions as $prepared){
            
            //se a permissao já foi registrada atualiza
            $insert = true;
            $id     = $this->getPermCod($prepared['plugins_permissao_nome']);
            if($id !== false) {
                $insert = false;
                $this->updatePermission($prepared);
            }
            //insere a permissao
            else{$id = $this->insertPermission($prepared);}
            
            //atualiza os perfis para que os perfis cadastrados tenham acesso as permissões default
            $this->preparePerfis($prepared, $id, $insert);
        }
        return $this->importData();
    }
    
            private function getPermCod($permission_name){
                $cod = ($this->perm->selecionar(array('plugins_permissao_cod'),"plugins_permissao_nome = '$permission_name'" ));
                if(empty($cod)){return false;}
                $temp = array_shift($cod);
                return $temp['plugins_permissao_cod'];
            }
    
            private function updatePermission($prepared){
                $cod = $this->perm->getCodPermissionByName($prepared['plugins_permissao_nome']);
                if(!$this->perm->editar($cod, $prepared)){
                    die(implode("<br/>", __CLASS__ . " - " .$this->perm->getMessages()));
                }
            }

            private function insertPermission($prepared_permission){

                //insere uma nova permissão
                if(!$this->perm->inserir($prepared_permission)){
                    $erro = (DEBUG)?$this->perm->getErrorMessage():"";
                    throw new classes\Exceptions\modelException(__CLASS__, "AIRPIP01 - Erro ao registrar permissão. $erro");
                }

                //retorna o código da permissão inserida
                return $this->perm->getLastId();
            }
    
            private function preparePerfis($prepared, $id){
                
                foreach($this->perfis as $perf){
                    $add['plugins_permissao_cod']   = $id;
                    $add['plugins_acesso_permitir'] = $this->getPermission($perf, $prepared);
                    $add['usuario_perfil_cod']      = $perf['usuario_perfil_cod'];
                    $this->insert[]                 = $add;
                }
            }
    
            private function getPermission($perf, $prepared){
                if(in_array($perf['usuario_perfil_cod'], array(Admin, Webmaster))) return "s";
                return array_key_exists('plugins_permissao_default', $prepared)?$prepared['plugins_permissao_default']:"n";
            }
            
            private function importData(){
                if(!$this->acc->importDataFromArray($this->insert)){
                    throw new classes\Exceptions\modelException(__CLASS__, $this->acc->getErrorMessage());
                }
                //$this->print_data();
                return true;
            }
            
            private function print_data(){
                static $i = 0;
                
                $this->acc->print_all();
                
                $i++;
                //if($i == 3){die('___________ Aborted ___________: <br/> Método:' . __METHOD__ . "<br/>Linha: ".__LINE__ . "<hr/>");}
                
                usort($this->insert, function($a, $b){
                    if($a['plugins_permissao_cod'] == $b['plugins_permissao_cod']){
                        return($a['plugins_acesso_permitir'] > $b['plugins_acesso_permitir']);
                    }
                    return($a['plugins_permissao_cod'] > $b['plugins_permissao_cod']);
                });
                print_in_table($this->insert);
                echo"<hr/><hr/>";
            }
    
    private function preparePermissionsToInsertion($cod_plugin, $permissoes){
        $out = array();
        foreach($permissoes as $nm => $var){
            foreach($var as $name => $v){
                $out[$nm]["plugins_permissao_$name"]= $v;
            }
            $out[$nm]['cod_plugin'] = $cod_plugin;
        }
        return $out;
    }
    
}