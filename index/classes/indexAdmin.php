<?php

namespace classes\Controller;
class indexAdmin extends \classes\Controller\Controller{

	public function __construct($vars) {
            parent::__construct($vars);
        }
	
	public function index(){
            $this->genTags("Gerenciador de recursos");
            $this->display("admin/index/index");
	}
        
        public function info(){
            $this->display('admin/index/info');
        }

}

?>
