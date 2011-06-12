<?php
require_once('inc/db.inc.php');
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}
?>
<html>
<head>
	<title>StubShare User Account Page</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a>
<h1>Sell with StubShare</h1>
<h2>Your Products</h2>
<table border=1 >
<tr><th>Name</th><th>Description</th><th>Price</th><th>Sale Count</th><th>Create Stub</th><th>Edit</th></tr>
<?php //Fill in Product table
$userID = StubShare::GetUserID($user);
$q = mysql_query("SELECT * FROM products WHERE owner='$userID'");
if($q && mysql_num_rows($q) > 0)
{
	while($prod_info = mysql_fetch_array($q))
	{
		$name = htmlspecialchars($prod_info['name'], ENT_QUOTES);
		$desc = htmlspecialchars($prod_info['description'], ENT_QUOTES);
		$price = htmlspecialchars($prod_info['price'], ENT_QUOTES);
		$sales = htmlspecialchars($prod_info['sales'], ENT_QUOTES);
		$id = (int)($prod_info['id']);
		echo "<tr><td>$name</td><td>$desc</td><td>$price</td><td>$sales</td>";
		echo '<td><a href="addstub.php?id=' . $id . '">Add Stub..</a></td>';
		echo '<td><a href="editproduct.php?id=' . $id . '">Edit...</a></td>';
		echo "</tr>";
	}
}
?>
</table>
<a href="addproduct.php">Add Product...</a>
<h2>Your Stubs</h2>
<table border=1 >
<tr><th>ID</th><th>Product</th><th>Product Owner</th><th>Total Price</th><th>Product Price</th><th>Your Cut</th><th>Charity Cut</th><th>Charity</th><th>Sale Count</th><th>Get Sharing Codes</th><th>Disable/Enable</th><th>Delete</th></tr>
<?php //Fill in the Stub Table
$userID = StubShare::GetUserID($user);
$q = mysql_query("SELECT * FROM stubs WHERE owner='$userID'");
if($q && mysql_num_rows($q) > 0)
{
	while($stub_info = mysql_fetch_array($q))
	{

		$id = (int)$stub_info['id'];
		$charity_id = (int)$stub_info['charity'];
		$user_pct = (double)$stub_info['profit_pct'];
		$charity_pct = (double)$stub_info['charity_pct'];
		$stub_pct = (double)$stub_info['stub_pct'];
		$sales = (int)$stub_info['sales'];

		$product_info = StubShare::GetProductInfo((int)$stub_info['product']);

		$product_name = htmlspecialchars($product_info['name'], ENT_QUOTES);
		$product_price = (double)$product_info['price'];

		$charity_name = StubShare::GetCharityName($charity_id);

		$product_owner = StubShare::GetUsernameFromID((int)$product_info['owner']);

		$user_cut = $product_price * $user_pct/100;
		$charity_cut = $product_price * $charity_pct/100;
		$stub_cut = $product_price * $stub_pct/100;

		$total = $product_price + $user_cut + $charity_cut + $stub_cut;
		
		$total = round($total, 2);		
		$user_cut = round($user_cut,2);
		$charity_cut = round($charity_cut,2);
		$stub_cut = round($stub_cut,2);
		echo "<tr><td>$id</td><td>$product_name</td><td>$product_owner</td><td>$total</td><td>$product_price</td><td>$user_cut</td><td>$charity_cut</td><td>$charity_name</td><td>$sales</td><td>to-do</td><td>to-do</td><td>to-do</td></tr>";
	}
}
?>
</table>
</body>
</html>

