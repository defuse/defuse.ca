<?php
include 'libs.php';
include 'aes.php';
$hash = sqlsani($_GET['id']);
	$result = mysql_query("SELECT * FROM queue WHERE hash='$safehash'");
	if($result) {
		if(mysql_num_rows($result) == 1) {
		$file = mysql_fetch_assoc($result);
		$filedata = $file['filedata'];
		$name = $file['filename'];
		
		$backup = array_num_row(mysql_query("SELECT * FROM queue WHERE hash='$safehash' AND perm='0'"));
		AddToQueue($backup['filename'], $backup['filedata'], $backup['arcname'], $backup['arcid'], $backup['hash'], 0, 0)
		$del=mysql_query("DELETE FROM queue WHERE hash='$safehash' AND perm='0'");
		
		
		if($del){
		$hash = AddToQueue($name, $filedata, '0', '0');
		header("location:scanner.php?type=file&id=$safehash");
		} else {
		echo "cant rescan";
		}} else {
		echo "Invalid File";
		}} else {
		echo "Error ";
		}
?>