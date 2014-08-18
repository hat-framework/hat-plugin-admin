<?php

class admin_indexModel extends \classes\Model\Model{

    public function listPlugins(){
        $this->LoadResource("files/dir", "dir");
        $files = classes\Classes\Registered::getAllPluginsLocation();
        
        $our = array();
        foreach($files as $nfile){
            if($nfile != ".DS_Store"){
                $out[] = $nfile;
            }
        }
        return $out;
    }
    
}

?>
