<?php
include 'libs1.php';
$rootfolder = "C:/scans/";
$rnd = rand_str();
if(isset($_FILES['file']['tmp_name']))
{
$filecontents = file_get_contents($_FILES['file']['tmp_name']);
$name = $_FILES['file']['name'];
if($_POST['archive'] == 'on'){
$dir = 'C:/scans/' . $rnd . '/';
mkdir($dir);
move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $rnd);
afterupload($name, 'on', $rnd);

echo '<meta http-equiv="refresh" content="0;url=scanner1.php?type=arc&id='.$rnd.'">';
} else {
$id = afterupload($name, 'off', '0');
echo '<meta http-equiv="refresh" content="0;url=scanner1.php?type=file&id='.$id.'">';
}}
?>
