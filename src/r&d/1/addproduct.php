<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}

if(isset($_POST['add_product']))
{
	$name = $_POST['prod_name'];
	$desc = $_POST['prod_desc'];
	$price = $_POST['prod_price'];
	$url = $_POST['prod_url'];

	//TODO: validate data

	StubShare::AddProduct($user, $name, $desc, $price, $url);

	header('Location: products.php');
}

?>
<html>
<head>
	<title>StubShare User Account Page</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a>
<h1>Add a Product</h1>
<form action="addproduct.php" method="post" >
<table>
<tr>
	<td>Name:</td>
	<td><input type="text" name="prod_name" value="" /></td>
</tr>
<tr>
	<td>Description:</td>
	<td><textarea name="prod_desc" rows=10 cols=40 ></textarea></td>
</tr>
<tr>
	<td>Price:</td>
	<td><input type="text" name="prod_price" /></td>
</tr>
<tr>
	<td>Download URL:</td>
	<td><input type="text" name="prod_url" /></td>
</tr>
</table>
<input type="submit" name="add_product" value="Add" />
</form>

</body>
</html>
