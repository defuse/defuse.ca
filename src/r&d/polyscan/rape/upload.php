<?php
include 'libs.php';
$rootfolder = "C:/scans/";
$rnd = rand_str();
$filecontents = file_get_contents($_FILES['file']['tmp_name']);
if(isset($_FILES['file']['tmp_name'])){
$name = $_FILES['file']['name'];
if($_POST['archive'] == 'on'){
$dir = 'C:/scans/' . $rnd . '/';
mkdir($dir);
move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $rnd);
afterupload($name, 'on', $rnd);
echo '<meta http-equiv="refresh" content="0;url=scanner.php?type=arc&id='.$rnd.'">';
} else {
$id = AddToQueue($name, sqlsani(bin2hex($filecontents)), '0', '0');
echo '<meta http-equiv="refresh" content="0;url=scanner.php?type=file&id='.$id.'">';
}}
?>
