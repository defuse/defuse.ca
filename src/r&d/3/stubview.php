<?php
require_once('inc/stubshare.inc.php');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");  

$stub = "inc/stub.gif";
$im = @imagecreatefromgif ($stub);
$price = "----";
$stubID = (int)$_GET['id'];
$stubInfo = StubShare::GetStubInfo($stubID);
if($stubInfo)
{
	$productInfo = StubShare::GetProductInfo($stubInfo['product']);
	if($productInfo)
		$price = "$" . round((double)$productInfo['price'],2);
}

imagestring($im, 4, 20, 20, $price, imagecolorallocate($im, 0xFF, 0xFF, 0xFF));
Header ('Content-type: image/jpeg');
imagejpeg($im,NULL,100);
ImageDestroy($im); 
?>
