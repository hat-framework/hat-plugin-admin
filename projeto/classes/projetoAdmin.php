<?php

class projetoAdmin extends Admin{
    
    public $model_name = "admin/projeto";
    public function __construct($vars){
        parent::__construct($vars);
    }

    public function index(){
        $this->registerVar("projetos", $this->model->selecionar());
        $this->display('admin/projeto/index');
    }
}
?>
