<?php
require_once('inc/db.inc.php');
require_once('inc/stubshare.inc.php');
?>
<html>
<head>
<title>Stub Store</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a> | <a href="stubstore.php">Stub Store/Archive</a> 
<h1>Stub Store</h1>
<?php
$q = mysql_query("SELECT * FROM stubs");
while($ary = mysql_fetch_array($q))
{
	$productInfo = StubShare::GetProductInfo($ary['product']);
	$name = $productInfo['name'];
	echo "<b>$name</b><br />";
	echo StubShare::EncodeStubImage($ary['id']);
	echo "<br />";	
}
?>
</body>
</html>
