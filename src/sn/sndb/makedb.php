<?php
include('SNDB.php');
SNDB::createDatabase("idx.idx", "db.db");

$foo = new SNDB(164);

echo "EPN: ", $foo->getEpNumber(), "\n";
echo "TITLE: ", $foo->getTitle(), "\n";
echo "DESC: ", $foo->getDescription(), "\n";
echo "HQ: ", $foo->getHQLink(), "\n";
echo "LQ: ", $foo->getLQLink(), "\n";
echo "HTML: ", $foo->getHTMLLink(), "\n";
echo "PDF: ", $foo->getPDFLink(), "\n";
echo "TXT: ", $foo->getPlainLink(), "\n";
echo "DATE: ", $foo->getDate(), "\n";
echo "RUNT: ", $foo->getRunTime(), "\n";
?>
