<?php
include 'libs.php';
$rootfolder = "C:/scans/";
if(isset($_FILES['file']['tmp_name']))
{
		$name = $_FILES['file']['name'];
		$id = AddToQueue($name, file_get_contents($_FILES['file']['tmp_name']), '0', '0');
		Header("Location: scanner1.php?hash=$id");
}

?>