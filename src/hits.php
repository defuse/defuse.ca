<?php
require_once('libs/phpcount.php');
echo "t: " . PHPCount::GetTotalHits() . " u: " .  PHPCount::GetTotalHits(true);
?>
