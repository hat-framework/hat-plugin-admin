<?php

class plugins_admin_Config_configuracaoAConfig extends classes\Model\configModel {
    
    public $name = "Definições Gerais";
    public function  __construct() {
        $this->setFilename(__FILE__, __CLASS__);
    }
    
    protected $dados = array(
        'CONFIGURACAO_GERAL_TITLE' => array(
            'name'        => 'Título Configurações Gerais',
            'type'        => 'varchar',
            'default'     => CONFIGURACAO_GERAL_TITLE,
            'description' => 'Este título aparece na página inicial de configurações',
        ),
        'CONFIGURACAO_PLUGINS_TITLE' => array(
            'name'        => 'Título Configurações dos Plugins',
            'type'        => 'varchar',
            'default'     => CONFIGURACAO_PLUGINS_TITLE,
            'description' => 'Este título aparece na página inicial de configurações',
        ),
        'CONFIGURACAO_RESOURCE_TITLE' => array(
            'name'        => 'Título Configurações dos Recursos',
            'type'        => 'varchar',
            'default'     => CONFIGURACAO_RESOURCE_TITLE,
            'description' => 'Este título aparece na página inicial de configurações',
        ),
        'CONFIGURACAO_JSPLUGINS_TITLE' => array(
            'name'        => 'Título Configurações dos JsPlugins',
            'type'        => 'varchar',
            'default'     => CONFIGURACAO_JSPLUGINS_TITLE,
            'description' => 'Este título aparece na página inicial de configurações',
        )
    );
     
    public function select(){
        return $this->dados;
    }

}