<?php

class configuracaoAdmin extends Admin {

    public $model_name = LINK;
    public function index(){
        $item = $this->model->selecionar();
        $this->registerVar("configuracao", $item);
        $this->genTags("Gestão de configuração");
        $this->display($this->model_name.'/index');
    }

    public function configure(){

        $conffile = implode("/",$this->vars);
        if(!$this->model->hasConfigurationFile($conffile)) Redirect($this->model_name);
        if(!empty ($_POST)) $this->model->editar($conffile, $_POST);
        
        $this->genTags("Arquivo $conffile.php");
        $this->registerVar("cfile", $conffile);
        $this->registerVar("dados", $this->model->getConf($conffile));
        $this->setVars($this->model->getMessages());
        $this->display($this->model_name.'/configure');
    }

}
?>
