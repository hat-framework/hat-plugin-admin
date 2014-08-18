<?php

use classes\Classes\Object;
class registerPermissions extends classes\Classes\Object{
    
    //objetos da classe
    protected $acc         = NULL;
    protected $perm        = NULL;
    protected $user_perfil = NULL;
    private   $action_obj  = NULL;
    
    //variaveis de estado
    private $perfis = array();
   
    public function __construct() {
        $this->LoadModel('usuario/perfil'   , 'user_perfil');
        $this->LoadModel('plugins/acesso'   , 'acc' );
        $this->LoadModel('plugins/permissao', 'perm');
    }
    
    public function register($action_obj, $cod_plugin, $permissoes){
        
        //inicializa as variaveis
        $this->action_obj    = $action_obj;
        $this->perfis        = $this->user_perfil->selecionar(array('usuario_perfil_cod'));
        $preparedPermissions = $this->preparePermissionsToInsertion($cod_plugin, $permissoes);
        
        //percorretodas as permissoes do plugin
        foreach($preparedPermissions as $prepared){
            
            //se a permissao já foi registrada continua
            if($this->permissionExists($prepared['plugins_permissao_nome'])) {
                $this->updatePermission($prepared);
                continue;
            }
            
            //insere a permissao
            $id = $this->insertPermission($prepared);
            
            //atualiza os perfis para que os perfis cadastrados tenham acesso as permissões default
            $this->updatePerfis($prepared, $id);
        }
        return true;
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
    
    private function updatePerfis($prepared, $id){
        foreach($this->perfis as $perf){
            $add['usuario_perfil_cod']      = $perf['usuario_perfil_cod'];
            $add['plugins_permissao_cod']   = $id;
            $add['plugins_acesso_permitir'] = $this->getPermission($perf, $prepared);
            if(!$this->acc->inserir($add)) throw new classes\Exceptions\modelException(__CLASS__, $this->acc->getErrorMessage());
        }
    }
    
    private function getPermission($perf, $prepared){
        if(in_array($perf['usuario_perfil_cod'], array(Admin, Webmaster))) return "s";
        return array_key_exists('plugins_permissao_default', $prepared)?$prepared['plugins_permissao_default']:"n";
    }
    
    private function preparePermissionsToInsertion($cod_plugin, $permissoes){
        $out = array();
        $permissoes = $this->action_obj->getPermissions();
        foreach($permissoes as $nm => $var){
            foreach($var as $name => $v){
                $out[$nm]["plugins_permissao_$name"]= $v;
            }
            $out[$nm]['cod_plugin'] = $cod_plugin;
        }
        return $out;
    }
    
    private function updatePermission($prepared){
        $cod = $this->perm->getCodPermissionByName($prepared['plugins_permissao_nome']);
        if(!$this->perm->editar($cod, $prepared)){
            die(implode("<br/>", __CLASS__ . " - " .$this->perm->getMessages()));
        }
    }
    
    private function permissionExists($permission_name){
        return ($this->perm->getCount("plugins_permissao_nome = '$permission_name'" ) > 0);
    }
    
}

?>