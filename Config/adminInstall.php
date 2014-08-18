<?php

class adminInstall extends classes\Classes\InstallPlugin{
    
    protected $dados = array(
        'pluglabel' => 'Administrador',
        'isdefault' => 'n',
        'detalhes'  => 'O plugin de administração permite o controle dos outros plugins do site.',
        'system'    => 's',
    );
    
    public function install(){
        return true;
    }
    
    public function unstall(){
        return true;
    }
}