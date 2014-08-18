<?php

use classes\Classes\Object;
class registerModels extends classes\Classes\Object{
    
    //objetos a serem carregados
    protected $plug = NULL, $install_obj = NULL, $smd = NULL;
    
    //variaveis de estado
    private $item       = array();
    private $plugin     = "";
    private $models = array();
    private $cod        = "";
    
    public function __construct() {
        $this->LoadModel("plugins/plug" , "plug");
        $this->LoadModel("admin/install", "install_obj");
        $this->LoadModel('plugins/model', 'smd');
    }
    
   /**
    * Retorna o código do plugin do qual os modelos que foram inseridos pertencem
    * @return int código de um plugin
    */
    public function getCodPlugin(){
        return $this->cod;
    }
    
    /**
    * Registra os modelos(subplugins) perterncentes a um plugin
    * 
    * @param string $plugin <p> O nome do Plugin que cujo os modelos estão sendo registrados </p>
    * @param array $models <p> Um array, contendo os nomes de todos os subplugins pertencentes ao plugin </p>
    * @return boolean true - se todos os subplugins foram instalados, false caso o contrário
    */
    public function register($plugin, $models){   
        
        //inicializa as variaveis
        if(!$this->init($plugin, $models))
            return false;
        
        //registra os models
        foreach($this->models as $sub)
            $this->registerModel($sub);
        
        return true;
    }
    
   /**
    * Registra um modelo
    * 
    * @param  array $arr <p> Um array, contendo os dados do modelo</p>
    * @throws modelException <p> Erro ao registrar modelo</p>
    * @return boolean true - se o modelo for registrado corretamente
    */
    private function registerModel($arr){

        //inicializa as variaveis
        $name_model  = "";
        $label       = "";
        $description = "";
        $model       = array_shift($arr);
        $this->initModelVars($model, $name_model, $label, $description);
        
        //verifica se o subplugin já está registrado
        if($this->smd->getCount("plugins_model_name = '$name_model'") > 0) return true;

        //registra o modelo
        $out['cod_plugin']                  = $this->item['cod_plugin'];
        $out['plugins_model_name']          = $name_model;
        $out['plugins_model_label']         = $label;
        $out['plugins_model_description']   = $description;
        if(!$this->smd->inserir($out)) throw new classes\Exceptions\modelException(__CLASS__, "Erro AIRMRSP02 - ".$this->smd->getErrorMessage());
        return true;
    }
    
   /**
    * Inicializa as variáveis do modelo
    * 
    * @param  string $model <p> string, contendo o nome do plugin/subplugin </p>
    * @param  string $name_model <p> string vazia, onde será atribuído o nome do modelo (subplugin) no sistema</p>
    * @param  string $label <p> string vazia onde será atribuído o nome fantasia (label) do modelo (subplugin) </p>
    * @param  string $description <p> string vazia que onde será atribuída a descrição do modelo (subplugin) </p>
    * @throws modelException <p> Se nome do subplugin vazio </p>
    */
    private function initModelVars($model, &$name_model, &$label, &$description){
        $name_model  = $model;
        $label       = "";
        $description = "";
        $this->LoadModel($model, 'md', false);
        if(is_object($this->md)){
            if(method_exists($this->md, "getModelName")){
                $label       = $this->md->getModelLabel();
                $name_model  = $this->md->getModelName();
                $description = $this->md->getModelDescription();
                if($name_model == "") $name_model = $model;
            }
        }
        if($name_model != "") return; 
        throw new classes\Exceptions\modelException(__CLASS__, "Erro AIRMIMV01 - O Subplugin $model não possui nome ou o nome está vazio");
    }
    
   /**
    * Inicializa as variáveis da classe
    * 
    * @param  string $plugin <p> nome do plugin cujo os modelos serão registrados</p>
    * @param  array $models <p> um array contendo os subplugins pertencentes àquele plugin</p>
    * @return boolean true - se a inicialização ocorrer corretamente, false caso o não exista nenhum subplugin
    */
    private function init($plugin, $models){
        $this->plugin = $plugin;
        $this->models = $models;
        if(empty($this->models)) {
            $this->setAlertMessage("O plugin $this->plugin não possui subplugins");
            return false;
        }
        
        $this->item = $this->LoadPlugin();
        $this->cod  = $this->item['cod_plugin'];
        return true;
    }
    
    
    /**
    * Carrega um plugin do sistema atravéz do nome do plugin registrado na classe (atribulo $plugin)
    * 
    * @return array contendo os dados do plugin registrado no sistema
    * @throws modelException <p> Caso o plugin não seja registrado corretamente no sistema </p>
    */
    private function LoadPlugin(){
        $plugin = $this->plugin;
        $item = $this->plug->getItem($plugin, 'plugnome');
        if(!empty($item)) return $item;
        
        if(!$this->install_obj->inserirPlugin($plugin)){
            throw new classes\Exceptions\modelException(__CLASS__, 
                    "Erro AIRMLP01 - Erro ao registrar o plugin $plugin no banco de dados. Detalhes: ".$this->install_obj->getErrorMessage()
            );
        }
        
        $item = $this->plug->getItem($plugin, 'plugnome');
        if(!empty($item)) return $item;
        throw new classes\Exceptions\modelException(__CLASS__,
                "Erro AIRMLP02: Não foi possível registrar o plugin $plugin no banco de dados."
        );
        
    }
    
}

?>
