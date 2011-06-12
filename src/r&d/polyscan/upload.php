<?php
session_start();
include 'libs.php';
include 'aes.php';
$rootfolder = "C:/scans/";
$rnd = rand_str();
if(isset($_POST['perm'])){
$perm = 1;
} else {
$perm = 0;
}
$filecontents = file_get_contents($_FILES['file']['tmp_name']);
if(isset($_FILES['file']['tmp_name'])){
$name = $_FILES['file']['name'];
$_SESSION['FNAME']= $_FILES['file']['name'];
if($_POST['archive'] == 'on'){
$dir = 'C:/scans/' . $rnd . '/';
mkdir($dir);
move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $rnd);
afterupload($name, 'on', $rnd, $perm);
echo '<meta http-equiv="refresh" content="0;url=scanner.php?type=arc&id='.$rnd.'">';
} else {
$id = AddToQueue($name, sqlsani(bin2hex($filecontents)), 0, 0, 0, $perm, 0);
if($id[1] == "rescan"){
echo " This File has Already Analysed previously.<br />";
echo "<a href='scanner.php?type=file&id=$id[0]&rescan=1'>Rescan File</a> || <a href='scanner.php?type=file&id=$id[0]'>Display Result</a>";
exit();
}
echo "<meta http-equiv='refresh' content='0;url=scanner.php?type=file&id=$id[0]'>";
}}
?>
