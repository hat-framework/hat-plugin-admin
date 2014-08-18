<?php

class admin_projetoModel extends \classes\Model\Model {

    protected $tabela = "admin_projeto";
    protected $pkey   = "cod_projeto";
    protected $dados = array(
        
        'cod_projeto' => array(
            'name'    => "Projeto",
            'pkey'    => true,
            'ai'      => true,
            'type'    => 'int',
            'grid'    => true,
            'notnull' => true
         ),
        
        'nome' => array(
            'name'    => 'Nome',
            'type'    => 'varchar',
            'size'    => '50', 
            'unique'  => array('model' => 'admin/projeto'),
            'grid'    => true,
            'notnull' => true
       	),
        
        'template' => array(
            'name'     => 'Template',
            'type'     => 'varchar',
            'especial' => 'listfolder',
            'listfolder' => array(
                "folder" => TEMPLATES,
                "hide"   => array('config')
            ),
            'size'     => '50', 
            'grid'     => true
       	),
        
        'module_default' => array(
            'name'    => 'Módulo Padrão',
            'type'    => 'varchar',
            'size'    => '50', 
            'grid'    => true
       	)
    );

    public function setSession($projeto){
        $item = $this->getItem($projeto,'nome');
        if(empty ($item)) {
            if(isset($_SESSION['projeto'])) unset($_SESSION['projeto']);
            $dados = array_keys($this->dados);
            foreach($dados as $name) 
                if(isset($_SESSION[$name])) unset($_SESSION[$name]);
            return false;
        }
        $item['projeto'] = $item['nome'];
        unset($item['cod_projeto']);
        unset($item['nome']);
        foreach($item as $name => $it){
            $_SESSION[$name] = $it;
        }
        return true;
    }

}

?>
