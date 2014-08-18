<?php
namespace classes\Controller;
class exceptionAdmin extends \classes\Controller\Controller{

    public function __construct($vars) {
        parent::__construct($vars);
    }
    
    public function index(){
    	$this->display("admin/exception/index");
    }
}

?>
