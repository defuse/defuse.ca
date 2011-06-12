<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '1234567890';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');

$dbname = 'main';
mysql_select_db($dbname);

$a = mysql_fetch_array(mysql_query("SELECT * FROM queue WHERE id='3'"));

$n = 0;
$a = explode("\r\n", $a['sigcheck']);
$nn = count($a);
print "<table>";
while($a[$n] <= $nn){
$b = explode(":", $a[$n]);
print "<tr><td>" . $b[0] . "</td><td>";
if(count($b) > '2'){
print $b[1] . ":" . $b[2] . "</td></tr>";
} else {
print $b[1] . "</td></tr>";
}
$n++;
}
print "</table>";
?>