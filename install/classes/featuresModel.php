<?php

class admin_featuresModel extends \classes\Model\Model{
    
    public function SaveFeatures(){
        if(isset($_SESSION['sysfeatues'])) return;
        $this->LoadResource("files/dir", "dir");
        $plugins = classes\Classes\Registered::getAllPluginsLocation();
        $avaible = $defines = array();
        foreach($plugins as $plug){
            $avaible[strtoupper($plug)] = true;
            $file = classes\Classes\Registered::getPluginLocation($plug, true)."/Config/features.php";
            if(!file_exists($file)) continue;
            $tarr = $this->select($file);
            foreach($tarr as $name => $const){
                $defines[$name] = $const['default'];
            }
        }
        if(empty ($defines)) return;
        ksort($defines);

        $out = array();
        foreach($defines as $name => $def){
            $var = explode("_", $name);
            $this->toArray($var, $out);
        }
        $mout = array();
        $out = $this->generateFeatures($out, $avaible);
        $this->toArrString($out, $defines, $mout);
        $this->toFile($mout);
    }
    
    private function toFile($dados){
        $filename = CONFIG . "features.php";
        $valores = array('true', 'false');
        $data = "<?php \n";
        foreach($dados as $name => $valor){
            $name = str_replace (" ", "", $name);
            if(!in_array($valor, $valores))$valor = "'$valor'";
            $data .= "\n\t if(!defined('$name')) define('$name' , $valor);";
        }
        $data .= "\n ?>";
        if(file_put_contents($filename, $data) === false)
            die("Não foi possível inserir dados no arquivo ($filename) ");
        
    }
    
    private function generateFeatures($feature_arr, $avaible){
        
        foreach($feature_arr as $name => $arr){
            if(!array_key_exists($name, $avaible))unset($feature_arr[$name]);
        }
        
        return $feature_arr;
    }
    
    private function toArrString($feature_arr, $defines, &$out = array(), $nm = ""){
        
        if(empty($feature_arr)){
            if($defines[$nm] == "false") return;
            $out[$nm] = $defines[$nm];
        }
        
        //usando recursão de cauda para aumentar a eficiência de função recursiva
        else foreach($feature_arr as $name => $arr) {
            if($nm != "") $newnm = $nm. "_" . $name;
            else $newnm = $name;
            
            if(array_key_exists($newnm, $defines)){
                if($defines[$newnm] == "false") return;
                $out[$newnm] = $defines[$newnm];
            }
            $this->toArrString($arr, $defines, $out, $newnm);
        }
    }
    
    private function toArray($feature_arr, &$out = array()){
        if(empty ($feature_arr)) return 1;
        $i = array_shift($feature_arr);
        if(!array_key_exists($i, $out)) $out[$i] = array();
        $this->toArray($feature_arr, $out[$i]);
    }
    
    public function select($file){
        if(!file_exists($file)) return array();
        $subject = file_get_contents($file);
        $itens = array();
        $subject = str_replace(array("<?php", "?>", "<?", '"', "'"), "", $subject);
        $item = explode("define(", $subject);
        foreach($item as $it){
            $it = explode(");", $it);
            $it = array_shift($it);
            $it = explode(",", $it);
            if(count($it) < 2) continue;
            
            $n     = str_replace(array(" ", "'"), "", array_shift($it));
            $name  = str_replace(array(" ", "-", "_"), array("", " ", " "), $n);
            $value = implode(",", $it);
            $value = substr($value, 1);
            $itens[$n] = array(
                'name'  => ucfirst(strtolower($name)),
                'type'  => 'varchar',
                'default' => "$value"
            );
            
            if($value == 'true' || $value == 'false'){
                $itens[$n]['type'] = 'enum';
                $itens[$n]['options'] = array('true' => 'sim', 'false' => 'Não');
            }
            

        }
        return($itens);

    }
}

?>