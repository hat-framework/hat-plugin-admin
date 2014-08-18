<?php

class actionComponent extends classes\Component\Component{
    public function draw($actions){
        if(empty ($actions)) return;
        $this->Html->LoadCss('area-admin');
        
        echo "
        <div class='painel-item'>
            <div class='painel-item-conteudo'>";
                 foreach($actions as $link => $act){
                     if(strtolower($act) == CURRENT_ACTION) continue;
                     if($act == "Listar" && CURRENT_ACTION == "index") continue;
                     $link = $this->Html->getLink($link);
                     echo "<div class='link'><a href='$link'>$act</a></div>";
                 }
        echo "</div>
        </div>";
    }
}

?>
