<?php

class admin_installModel extends \classes\Model\Model{

    private $blacklist = array("usuario", "admin", ".DS_Store");
    private $webmasterlist = array("gerador");
    private $observers = array(
        'notificacao/notifica', 'files/pasta', 'site/menu', 'usuario/gadget',
        'site/conffile'
    );
    public function  __construct() {
        $this->LoadResource("files/dir"  , "dir");
        $this->LoadResource("database"   , "db");
        $this->LoadResource("database/creator", 'iobj');
        $this->LoadResource("files/file" , "file");
        $this->LoadModel("plugins/plug"  , "plug");
        $this->LoadModel("admin/install/features", 'fea');
        $this->fea->SaveFeatures();
        
        $this->LoadModel('usuario/login', 'uobj');
        if($this->uobj->UserIsWebmaster()){
            $this->blacklist = array(".DS_Store");
        }
        parent::__construct();
    }

    private function editPlugin($module, $status){
        $post = array('status' => $status);
        $bool = $this->plug->editar($module, $post, "plugnome");
        $this->setErrorMessage($this->plug->getErrorMessage());
        return $bool;
    }

    private function getModuleName($module){
        $module = explode("/", $module);
        return array_shift($module);
    }

    public function install($module){
        
        //inicializa as variaveis
        $module = $this->getModuleName($module);
        $var    = $this->getPlugin($module);
        
        //se o plugin já está instalado, então retorna
        if(!empty ($var) && $var['status'] == 'instalado'){
            $this->setSuccessMessage("Plugin $module já está instalado");
            return true;
        }
        
        //inicializa a instalação no banco de dados
        $this->Start($module, "install");
        
        //marca o plugin como instalado
        if(!$this->editPlugin($module, 'instalado')){
            $this->setSuccessMessage("");
            $this->setAlertMessage("Não foi possível atualizar o status do plugin no banco de dados,
                porém ele foi instalado.");
            return false;
        }
        
        //executa o arquivo populate.sql
        if(!$this->selfpopulate($module, 'instalado')){
            $this->setSuccessMessage("");
            $this->setAlertMessage("Não foi possível popular o plugin automaticamente!");
            return false;
        }
        
        
        //plugin instalado corretamente!!
        $this->setSuccessMessage("Plugin $module instalado corretamente!");
        return true;
    }
    
    private function selfpopulate($module){
        $file = classes\Classes\Registered::getPluginLocation($module, true)."/Config/populate.sql";
        if(!file_exists($file)) {return true;}

        $counteudo = file_get_contents($file);
        if(!$this->db->ExecuteInsertionQuery($counteudo)){
            $this->setErrorMessage($this->db->getErrorMessage());
            return false;
        }
        return true;
    }

    
    public function unstall($module){
        $module = $this->getModuleName($module);
        $var = $this->getPlugin($module);
        if(!empty ($var) && $var['status'] == 'desinstalado'){
            $this->setSuccessMessage("O plugin $module já está desinstalado");
            return true;
        }
        
        if($this->Start($module, "unstall") === false){
            $plugin = $this->getPlugin($module);
            if(empty($plugin)) return true;
            
            $arq = array_keys(classes\Classes\Registered::getAllPluginsLocation());
            if(in_array($plugin['plugnome'], $arq)){
                $this->install($module);
                $this->setSuccessMessage("");
                return false;
            }
            if(!$this->plug->apagar($plugin['plugnome'], 'plugnome')){
                $this->setErrorMessage("Não foi possível desinstalar o plugin {$plugin['plugnome']}. 
                Seu diretório foi removido, mas ele ainda consta no banco de dados e não pôde ser excluído do sistema. 
                Detalhes do erro:" . $this->plugin->getErrorMessage());
                return false;
            }
            return true;
        }
        
        if(!$this->editPlugin($module, 'desinstalado')){
            $this->setSuccessMessage("");
            $this->setAlertMessage("Não foi possível atualizar o status do plugin no banco de dados,
                porém ele foi desinstalado.");
            return false;
        }
        
        if(!$this->notifyAllObservers($module)) return true;
   
        $this->setSuccessMessage("O plugin $module foi desinstalado corretamente!");
        return true;
    }

    private function notifyAllObservers($module){
        $alert = array();
        $bdname = (defined("cbd_name"))?cbd_name:bd_name;
        foreach($this->observers as $ob){
            try {
                $this->LoadModel($ob, 'observer');
                $table = $this->observer->getTable();
                $count = $this->db->ExecuteQuery("
                    SELECT COUNT(*) as total
                    FROM information_schema.tables 
                    WHERE table_schema = '$bdname' 
                    AND table_name = '$table';"
                );
                if($count[0]['total'] > 0){
                    if(!$this->observer->unstall($module)){
                        $alert[] = $this->observer->getErrorMessage();
                    }
                }
            }catch (Exception $e){/*do nothing*/}
        }
        
        if(!empty($alert)){
            $msg = implode('<br/>', $alert);
            $this->setAlertMessage($msg);
            return false;
        }
        return true;
    }

    public function disable($module){
        $module = $this->getModuleName($module);
        $var = $this->getPlugin($module);
        if(!empty ($var) && $var['status'] == 'desativado'){
            $this->setSuccessMessage("O plugin $module já está desativado");
            return true;
        }
        $this->editPlugin($module, 'desativado');
        $this->setSuccessMessage("O plugin $module foi desativado");
        return true;
    }

    public function enable($module){

        $module = $this->getModuleName($module);
        $var = $this->getPlugin($module);
        if(empty ($var)){
            $this->setErrorMessage("O plugin $module não existe");
            return false;
        }
        if($var['status'] == 'instalado'){
            $this->setSuccessMessage("O plugin $module já está ativo");
            return true;
        }
        if($var['status'] == 'desinstalado'){
            $this->setErrorMessage("O plugin $module não está instalado");
            return false;
        }
        $this->editPlugin($module, 'instalado');
        $this->setSuccessMessage("O plugin $module foi reativado");
        return true;
    }

    public function populate($module){
        $this->LoadResource("database/populator", "pop");
        $this->pop->populate($module);
    }

    public function update($module){

        $module = $this->getModuleName($module);
        $var = $this->getPlugin($module);
        if(empty ($var)){
            $erro = "O plugin $module não existe ou não pode ser modificado!";
            \classes\Utils\Log::save(LOG_INSTALACAO, $erro);
            $this->setErrorMessage($erro);
            return false;
        }
        
        if($this->Start($module, 'update') === false){
            \classes\Utils\Log::save(LOG_INSTALACAO, "Erro ao executar o update!");
            return false;
        }
        
       \classes\Utils\Log::save(LOG_INSTALACAO, "Atualizando os modelos do plugin");
        //$this->updatePluginModels($module);
        $this->setSuccessMessage("O plugin $module foi atualizado corretamente!");
        return true;
    }


    public function init($plugin){

        $plugin = $this->getModuleName($plugin);
        
        //instala os modulos basicos
        if(!$this->install($plugin)) return false;
        
        $dados['status'] = 'instalado';
        $dados['plugnome'] = $plugin;
        if(!$this->plug->inserir($dados)){
            $this->setErrorMessage($this->plug->getErrorMessage());
            return false;
        }
        return true;
    }

    private function Start($plugin, $type){
        //carrega o modulo de instalacao
        \classes\Utils\Log::save(LOG_INSTALACAO, "Realizando as mudanças dos models no banco de dados");
        $bool = true;
        if($type === "update"){
            $bool = $bool and $this->iobj->install($plugin);
        }
        $bool = $bool and $this->iobj->$type($plugin);
        $this->setMessages($this->iobj->getMessages());
        $subplugins = $this->iobj->getPlugin($plugin);
        return $bool and ($type == "unstall")? $this->deleteModels($plugin):$this->registerModels($plugin, $subplugins);
    }
    
    public function updatePluginModels($plugin){
        $subplugins = $this->iobj->getPlugin($plugin);
        return $this->registerModels($plugin, $subplugins);
    }
    
    private function deleteModels($plugin){
        
        \classes\Utils\Log::save(LOG_INSTALACAO, "Apagando os modelos do sistema");
        $item = $this->plug->getItem($plugin, 'plugnome');
        if(empty($item)){
            $erro = "Erro ao registrar subplugins: O plugin '$plugin' não foi registrado no banco de dados!";
            \classes\Utils\Log::save(LOG_INSTALACAO, "Abortado $erro");
            $this->setErrorMessage($erro);
            return false;
        }
        $cod  = $item['cod_plugin'];
        $this->LoadModel('plugins/model', 'smd');
        $this->smd->apagar($cod, "cod_plugin");
        
        $this->LoadModel('plugins/action', 'act');
        $tb1 = $this->smd->getTable();
        $tb2 = $this->act->getTable();
        $this->db->ExecuteQuery(
            "ALTER TABLE $tb1 AUTO_INCREMENT = 1; ".
            "ALTER TABLE $tb2 AUTO_INCREMENT = 1;"
        );
        //die($this->db->getSentenca());
        return true;
    }
    
    private function registerModels($plugin, $subplugins){
        \classes\Utils\Log::save(LOG_INSTALACAO, "<h4>Iniciando o registro de modelos</h4>");
        //registra o plugin atual e os subplugins no sistema
        $this->LoadClassFromPlugin('admin/install/inclasses/registerModels', 'rmds');
        if(!$this->rmds->register($plugin, $subplugins)){            
            \classes\Utils\Log::save(LOG_INSTALACAO, $this->rmds->getMessages());
            $this->setMessages($this->rmds->getMessages());
            return false;
        }
        $cod = $this->rmds->getCodPlugin();
        
        
        $install_classes = $this->FindInstallClasses();
        foreach($install_classes as $class){
            $this->LoadClassFromPlugin("admin/install/inclasses/$class", 'r');
            if(!($this->r instanceof install_subsystem)) continue;
            \classes\Utils\Log::save(LOG_INSTALACAO, "Executando $class");
            if(!$this->r->register($plugin, $cod)){
                \classes\Utils\Log::save(LOG_INSTALACAO, "abortado!");
                \classes\Utils\Log::save(LOG_INSTALACAO, $this->r->getMessages());
                $this->setMessages($this->r->getMessages());
                return false;
            }
        }
        return true;
    }
    
            private function FindInstallClasses(){
                $dir = realpath(dirname(__FILE__));
                $this->LoadResource('files/dir', 'dobj');
                $install_classes = $this->dobj->getArquivos("$dir/inclasses");
                foreach($install_classes as &$iclass){
                    $iclass = str_replace('.php', '', $iclass);
                }
                return $install_classes;
            }

    public function getPluginStatus(){
        return $this->plug->selecionar();
    }

    private function getPlugin($plugin){
        $var = array();
        if($plugin != "admin"){
            $var = $this->plug->selecionar(array(), "`plugnome` = '$plugin'");
            if(!empty ($var)) $var = array_shift($var);
        }
        return $var;
    }

    public function listarPlugins(){

        $data    = $this->plug->selecionar();
        $plugins = array();
        foreach($data as $arr) $plugins[$arr['plugnome']] = $arr;
        
        $this->LoadModel("usuario/login", 'uobj');
        $webm = $this->uobj->UserIsWebmaster();
        $inseriu = false;
        
        //atualiza a visibilidade dos plugins de acordo com a permissao de webmaster
        $arq = array_keys(classes\Classes\Registered::getAllPluginsLocation());
        foreach($arq as $plugname){
            //insere os plugins que não existem
            if(!array_key_exists($plugname, $plugins)) {
                $inseriu = true;
                $this->inserirPlugin($plugname);
                $plugins[$plugname] = ucfirst($plugname);
            }
            if(in_array($plugname, $this->blacklist)){
                unset($plugins[$plugname]);
            }
            if(!$webm && in_array($plugname, $this->webmasterlist)) unset($plugins[$plugname]);
            
        }

        //remove os plugins cujas pastas foram removidas mas que permaneceram no banco de dados
        foreach($plugins as $pname => $parr){
            if(in_array($pname, $arq)) continue;
            $this->unstall($pname);
            unset($plugins[$pname]);
        }
        
        //recupera os plugins novamente caso algum tenha sido inserido
        if($inseriu){
            $data    = $this->plug->selecionar();
            $plugins = array();
            foreach($data as $arr) $plugins[$arr['plugnome']] = $arr;
        }
        
        return $plugins;
    }

    public function inserirPlugin($plugnome){
        //print_r($this->blacklist);
         //if(!in_array($plugnome, $this->blacklist)){
        $dados['plugnome'] = $plugnome;
        if(!$this->plug->inserir($dados)){
            $this->setErrorMessage($this->plug->getErrorMessage());
            return false;
        }
         //}
         return true;
        
    }

}