<?php
require_once('libs/phpcount.php');
echo "t: " . number_format(PHPCount::GetTotalHits(), 0) . " u: " .  number_format(PHPCount::GetTotalHits(true),0);
?>
