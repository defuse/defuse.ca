<?php
require_once('inc/db.inc.php');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");  

$imagepath = "images/noimage.jpg";
if(isset($_GET['id']))
{
	$id = (int)$_GET['id'];
	$q = mysql_query("SELECT image FROM products WHERE id='$id'");
	if($q && mysql_num_rows($q) > 0)
	{
		$prod_info = mysql_fetch_array($q);
	
		if(!empty($prod_info['image']))
		{
			//Header ('Content-type: image/png');
			$imagepath = "user_images/" . $prod_info['image'];
		}
	}
}

header("Location: $imagepath");

?>
