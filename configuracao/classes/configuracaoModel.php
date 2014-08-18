<?php

use classes\Classes\Object;
class admin_configuracaoModel extends classes\Classes\Object {

    private $type = "";
    public function __construct() {
        $this->LoadModel("usuario/login", 'uobj');
        if($this->uobj->UserIsWebmaster()) $this->setConfigType("AConfig");
        else $this->setConfigType("Config");
    }
    
    public function setConfigType($conf){
        $arr = array("AConfig", "Config");
        $this->type = in_array($conf, $arr)?$conf:"Config";
        
    }
   
    private function filtherconfigfiles($files){
        $out = array();
        foreach($files as $file){
            if(strstr("$this->type.php", $file)) die('sfas');
            preg_match("/[a-zA-Z0-9]+$this->type.php/", $file, $matches);
            if(!empty ($matches)){
                $temp = str_replace("$this->type.php", "", $file);
                $out[$temp] = str_replace(array('_', '-'), ' ', $temp);
            }
        }
        return $out;
    }
    
    public function selecionar(){
        //carrega as configuracoes gerais
        $this->LoadResource("files/dir", 'dir');
        $files = $this->dir->getArquivos(CONFIG);
        $out[CONFIGURACAO_GERAL_TITLE] = $this->filtherconfigfiles($files);
        
        $this->LoadModel('plugins/plug', 'sobj');
        $var = $this->sobj->listPlugins();
        $files = array_keys($var);
        foreach($files as $plugin){
            $f2    = classes\Classes\Registered::getPluginLocation($plugin, true)."/Config/defines";
            $conff =  $this->filtherconfigfiles($f2);
            if(!empty ($conff)){
                foreach($conff as $name => $c) {
                    $out[CONFIGURACAO_PLUGINS_TITLE][ucfirst($plugin)][$c] = "plugin/$plugin/$name";
                }
            }
        }
        
        $files = classes\Classes\Registered::getAllResourcesLocation();
        foreach($files as $resource => $resourceDir){
            $f2    = $this->dir->getArquivos(DIR_BASIC.$resourceDir."/src/defines");
            $conff =  $this->filtherconfigfiles($f2);
            if(!empty ($conff)){
                foreach($conff as $name => $c) $out[CONFIGURACAO_RESOURCE_TITLE][ucfirst($resource)][$c] = "resource/$resource/$name";
            }
        }
        return ($out);
    }
    
    //seleciona todas as configurações contidas em um plugin apenas
    public function selecionar_plugin_config($plugin){
        $this->LoadResource("files/dir", 'dir');
        $files = $this->dir->getArquivos(classes\Classes\Registered::getPluginLocation($plugin, true)."/Config/defines/modelos");
        $out   = array();
        if(!empty ($files)){
            foreach($files as $file) {
                $name  = $plugin . "/".str_replace('Config.php', '', $file);
                $out[$name] = $this->get_plugin_data($plugin, $file);
                if(empty($out[$name])) unset($out[$name]);
            }
        }
        return $out;
    }
    
    public function get_config_by_plugin($plugin, $file){        
        return $this->get_plugin_data($plugin, "{$file}Config.php");
    }
    
    private function get_plugin_data($plugin, $file){
        $out = array();
        
        if(!$this->load_plugin_file_if_exists($plugin, $file)) return $out;
        $class = str_replace(".php"  , '', $file);
        if(!class_exists($class, false)) $out;

        $obj                = new $class();
        $out['title']       = $obj->getTitle();
        $out['description'] = $obj->getDescription();
        $out['data']        = $obj->getDados();
        $out['group']       = $obj->getGrupo();
        return $out;
    }
    
    private function load_plugin_file_if_exists($plugin, $file){
        $diretorio = classes\Classes\Registered::getPluginLocation($plugin, true)."/Config/defines/modelos/$file";
        if(!file_exists($diretorio)) {
            $msg = (DEBUG)?"<p><b>Plugin: </b> $plugin <br/> <b>Arquivo Procurado: </b> $diretorio </p>":""; 
            $this->setErrorMessage("O arquivo de configuração que você procura não foi encontrado ou não existe! $msg");
            return false;
        }
        require_once $diretorio;
        return true;
    }
    
    public function editar_plugin($plugin, $file, $post){
        $data = $this->get_plugin_data($plugin, "{$file}Config.php");
        if(empty($data)) return false;
        
        $dados = $data['data'];
        $out   = array();
        foreach($dados as $name => $dt){
            $out[$name] = array_key_exists($name, $post)?$post[$name]:$dt['default'];
        }
        
        $class = str_replace(".php"  , '', $file);
        $class = "{$class}Config";
        if(!class_exists($class, false)) false;
        $obj = new $class();
        $bool = $obj->editar($out);
        $this->setMessages($obj->getMessages());
        return $bool;
    }
    
    private function getFileName($file){
        $temp = explode("/", $file);
        if(count($temp)== 1){$dir = CONFIG;}
        elseif($temp[0] == 'plugin'){
            array_shift($temp);
            $pname = array_shift($temp);
            $ofile = array_shift($temp);
            $dir   = classes\Classes\Registered::getPluginLocation("$pname", true);
            $file  = "/Config/defines/$ofile";
        }
        elseif($temp[0] == 'resource'){
            array_shift($temp);
            $pname = array_shift($temp);
            $ofile = array_shift($temp);
            $dir   = classes\Classes\Registered::getResourceLocation("$pname", true);
            $file  = "/src/defines/$ofile";
        }
        elseif($temp[0] == 'jsplugin'){
            array_shift($temp);
            $pname  = array_shift($temp);
            $ofile  = array_shift($temp);
            $jsname = array_shift($temp);
            $dir    = classes\Classes\Registered::getResourceLocation("$pname", true);
            $file   = "/src/jsplugin/$jsname/defines/$ofile";
        }
        $arq = $dir . $file . "$this->type.php";
        return $arq;
    }

    public function hasConfigurationFile($file){
        $arq = $this->getFileName($file);
        return file_exists($arq);
    }

    public function getConf($file){
        if(!$this->loadObj($file, 'obj')){
            return array();
        }
        return $this->obj->select();
    }

    public function editar($conffile, $dados){
        if(!$this->loadObj($conffile, 'obj')) return false;
        $bool = $this->obj->editar($dados);
        $this->setMessages($this->obj->getMessages());
        return $bool;
    }

    private function loadObj($file, $obj){
        if(!$this->hasConfigurationFile($file)){
            $this->setErrorMessage("o arquivo $file não existe");
            return false;
        }
        $arq = $this->getFileName($file);
        require_once $arq;
        
        $temp  = explode("/", $file);
        $temp  = end($temp);
        $class = "config_".$temp . "$this->type";
        if(!class_exists($class)){
            $this->setErrorMessage("a class $class não existe");
            return false;
        }
        $this->$obj = new $class();
        return true;
    }
    
    public function getModelName() {
        return "";
    }
    public function getModelLabel(){
    	return "";
    }
    
    public function getModelDescription(){
    	return "";
    }

}

?>