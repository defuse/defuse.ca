<?php
session_start();
include '../libs.php';
include '../aes.php';
$result = 0;
$rootfolder = "C:/scans/";
$rnd = rand_str();
$filecontents = file_get_contents($_FILES['file']['tmp_name']);
if(isset($_FILES['file']['tmp_name'])){

$name = $_FILES['file']['name'];
$_SESSION['FNAME']= $_FILES['file']['name'];

$id = AddToQueue($name, sqlsani(bin2hex($filecontents)), '0', '0');
if(stristr($id,"rescan")){
$rescan = 1;
$id = explode("||",$id);
$hash = $id[1];
$hash=rot13encrypt($hash);
#echo " This File has Already Analysed previously.<br />";
#	echo "<a href='../rescan.php?type=file&id=$hash'>Rescan this again</a> || <a href='../scanner.php?type=file&id=$hash'>Display Result</a>";
#	exit();
} else {
$hash = rot13encrypt($id);
$rescan = 0;
}
$result = 1;
}
?>
<script language="javascript" type="text/javascript">window.top.window.stopUpload(<?php echo $result.','.$rescan.','.$hash.','.$_POST['arhive']; ?>);</script>
#if($_POST['archive'] == 'on'){
#$dir = 'C:/scans/' . $rnd . '/';
#mkdir($dir);
#move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $rnd);
#afterupload($name, 'on', $rnd);
#echo '<meta http-equiv="refresh" content="0;url=scanner.php?type=arc&id='.$rnd.'">';
#} else {
#$id = AddToQueue($name, sqlsani(bin2hex($filecontents)), '0', '0');

#if(stristr($id,"rescan")){
#$id = explode("||",$id);
#$hash = $id[1];
#$hash=rot13encrypt($hash);
#echo " This File has Already Analysed previously.<br />";
#	echo "<a href='../rescan.php?type=file&id=$hash'>Rescan this again</a> || <a href='../scanner.php?type=file&id=$hash'>Display Result</a>";
#	exit();
#}

#$id=rot13encrypt($id);
#echo '<meta http-equiv="refresh" content="0;url=../scanner.php?type=file&id='.$id.'">';
//echo $id;sqlsani(bin2hex($filecontents))
#}}