<?php
namespace classes\Controller;
class exceptionController extends \classes\Controller\Controller{

    public function __construct($vars) {
        parent::__construct($vars);
        $this->LoadModel("usuario/login", "model");
    }
    
    public function index(){
    	$this->display("admin/exception/index");
    }
}