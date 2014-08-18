<?php

class funcionalidadeComponent extends classes\Component\Component{
    public function draw($actions, $msg, $title){
        if(empty ($actions)) return;
        $this->Html->LoadCss('area-admin');
        
        if(empty ($actions)) return;
        $this->Html->LoadCss('area-admin');
        
        $id = 'panel_'.str_replace('/', '_', LINK);
        echo "
        <h1>$title</h1>
        <div class='painel-item' id='$id'>
            <div class='painel-item-titulo'>
                <h2>Funcionalidades</h2>
                <span class='painel-item-desc'>$msg</span>
            </div>
            <div class='painel-item-conteudo'>";
                 foreach($actions as $act => $link){
                     $act  = ucfirst($act);
                     $link = $this->Html->getLink($link);
                     $id   = 'func_'.GetPlainName($act);
                     $id2  = 'link_'.GetPlainName($act);
                     echo "<div class='link' id='$id'><a href='$link' id='$id2'>$act</a></div>";
                 }
        echo "</div>
        </div>";
    }
}

?>
