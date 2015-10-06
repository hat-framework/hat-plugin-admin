<?php

class registerConfigurations extends classes\Classes\Object implements \install_subsystem{
    
    private $obj        = null;
    private $files      = array();
    private $menu_itens = array();
    
    //variavel que informa se as configurações podem ou não ser instaladas. 
    //(Se o plugin site estiver desinstalado, então não pode registrar as configurações)
    private $block_register = false; 
    public function __construct() {
        //inicializa os objetos a serem usados pela classe
        $this->LoadModel('plugins/plug', 'plug');
        $this->LoadClassFromPlugin('site/conffile/conffileRegister', 'cfile');
        $this->LoadModel('site/menu' , 'menu');
        $plugin = $this->plug->getItem('site', 'plugnome');
        if(empty($plugin)) $this->block_register = true;
    }
    
    public function forceRegister(){
        $this->block_register = false;
    }
    
    public function register($plugin, $cod_plugin){
        if($this->block_register) {return true;}
        $bool = true;
        if(false === $this->LoadConfigClass($plugin)){return true;}
        if(false === $this->registerOne($plugin, $cod_plugin)){$bool = false;}
        if(false === $this->registerMenu($plugin)){$bool = false;}
        if(true  === $bool){$this->setSuccessMessage("Configurações inseridas com sucesso!");}
        return $bool;
    }

            private function LoadConfigClass($plugin){
                $class  = "{$plugin}Configurations";
                if(false === $this->loadClass($class, $plugin)){return false;}
                
                //carrega o arquivo
                $this->obj = new $class();
                if(!$this->obj instanceof \classes\Classes\Options) {
                    return $this->setErrorMessage("A classe $class não é uma instância da classe Options.");
                }
                return true;
            }
            
                    private function loadClass($class, $plugin){
                        if(false !== class_exists($class, false)){return true;}
                        $file   = classes\Classes\Registered::getPluginLocation($plugin, true). "/Config/$class.php";
                        getTrueDir($file);
                        if(false === file_exists($file)){return $this->setErrorMessage("O arquivo da classe $class não foi encontrado no diretório de configurações do plugin $plugin!");}
                        require_once $file;
                        if(false === class_exists($class, false)){
                            return $this->setErrorMessage("A classe $class não foi encontrada no plugin $plugin");
                        }
                        return true;
                    }
    
            private function registerOne($plugin, $cod_plugin){
                $this->cod_plugin = $cod_plugin;
                $this->files      = $this->obj->getFiles();
                $this->menu_itens = $this->obj->getMenu();

                $this->cfile->initPlugin($plugin, $cod_plugin);
                foreach($this->files as $path => $arr){
                    if(!$this->cfile->insertConffile($arr, $path)){
                        $this->setAlertMessage($this->cfile->getMessages());
                        return false;
                    }
                }
                $this->cfile->reset();
                return true;
            }

            private function registerMenu($plugin){
                if(empty($this->menu_itens)) {return true;}
                $insert_array = array();
                foreach($this->menu_itens as &$post){
                    if(!isset($post['menuid'])){continue;}
                    $toinsert['menuid'] = $post['menuid'];
                    $toinsert['menu']   = isset($post['menu'])  ?$post['menu']  :ucfirst($post['menuid']);
                    $toinsert['url']    = isset($post['url'])   ?$post['url']   :'';
                    $toinsert['ordem']  = isset($post['ordem']) ?$post['ordem'] :100;
                    $toinsert['pai']    = isset($post['pai'])   ?$post['pai']   :"";
                    $toinsert['icon']   = isset($post['icon'])  ?$post['icon']  :"";
                    $toinsert['plugin'] = trim($plugin);
                    $insert_array[]     = $toinsert;
                }
                if(empty($insert_array)){return true;}
                return $this->propagateMessage($this->menu, 'importDataFromArray', $insert_array, true);
            }        
}