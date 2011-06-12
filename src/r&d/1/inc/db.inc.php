<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'WF5ZR9GFfu8';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to database');

$dbname = 'stubshare';
mysql_select_db($dbname);
?>

