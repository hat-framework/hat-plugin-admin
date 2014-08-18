<?php

namespace classes\Controller;
class installAdmin extends \classes\Controller\Controller
{
    public function __construct($vars){
        parent::__construct($vars);
        $this->LoadModel("admin/install", "model");
    }

    public function index(){
        
        if(array_key_exists('messages', $_SESSION)){
            foreach($_SESSION['messages'] as $arr => $msg)
                $this->registerVar($arr, $msg);
            unset($_SESSION['messages']);
        }

        $this->genTags("Gerenciamento de Plugins");
        $this->model->disableCache();
        $this->registerVar('pluginst', $this->model->listarPlugins());
        $this->display("admin/install/index");
    }

    public function install(){
        $this->action("Install");
    }
    
    public function unstall(){
        $this->action("Unstall");
    }
    
    public function disable(){
        if(INSTALL_DB_ENABLE){
            $this->action(__FUNCTION__);
        }else Redirect(PAGE);
    }
    
    public function enable(){
        if(INSTALL_DB_ENABLE){
            $this->action(__FUNCTION__);
        }else Redirect(PAGE);
    }
    
    public function populate(){
        if(INSTALL_DB_POPULATE){
            $this->action(__FUNCTION__);
        }else Redirect(PAGE);
    }

    public function update(){
        if(INSTALL_DB_UPDATE){
            $this->action(__FUNCTION__);
        }else Redirect(PAGE);
    }

    private function action($action){
        $modulo = $this->getModule();
        $this->model->$action($modulo);

        $this->genTags("Ação $action");
        $_SESSION['messages'] = $this->model->getMessages();
        Redirect('admin/install');

    }
    
    private function getModule(){
     	$modulo  = array_shift($this->vars);
        return ($modulo == "")? false:$modulo;
    }
    
    public function apagar(){
        $this->LoadModel('plugins/plug', 'md');
        $id = array_shift($this->vars);
        $this->md->apagar($id);
        $_SESSION['messages'] = $this->md->getMessages();
        Redirect('admin/install');
    }
    
}
?>
