<h1>Using the <i>Vim</i> Editor for Syntax Highlighting in PHP</h1>


<?php
require_once('libs/VimHighlight.php');
$hl = new VimHighlight();
$hl->caching = true;
$hl->color_scheme = "dw_cyan";
$hl->show_lines = false;
$hl->setVimCommand("gvim");
$hl->div_css = "padding: 30px;";
echo $hl->processFile('source/VimHighlight.php');
?>
