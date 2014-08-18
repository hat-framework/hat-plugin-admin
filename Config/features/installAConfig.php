<?php

class plugins_admin_Config_installAConfig extends classes\Model\configModel {
    
    public $name = "Definições Gerais";
    public function  __construct() {
        $this->setFilename(__FILE__, __CLASS__);
    }

     protected $dados = array(
        'INSTALL_DB_POPULATE' => array(
            'name'        => 'Botão popular',
            'type'        => 'bit',
            'default'     => INSTALL_DB_POPULATE,
            'description' => 'Exibir Botão "popular banco de dados" na página de instalação do sistema'
        ),
        'INSTALL_DB_UPDATE' => array(
            'name'        => 'Botão atualizar',
            'type'        => 'bit',
            'default'     => INSTALL_DB_UPDATE,
            'description' => 'Exibir Botão "atualizar plugin" na página de instalação do sistema'
        ),
        'INSTALL_DB_ENABLE' => array(
            'name'        => 'Botão habilitar/desabilitar',
            'type'        => 'bit',
            'default'     => INSTALL_DB_ENABLE,
            'description' => 'Exibir Botão "habilitar/desabilitar plugin" na página de instalação do sistema'
        ),
    );
     
    public function select(){
        return $this->dados;
    }
    
    public function inserir($dados) {
        parent::inserir($dados);
    }
}