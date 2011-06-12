<?php
require_once('inc/db.inc.php');
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$STUBSHARE_PCT = 4.0;
$CHARITY_PCT = 12.5;
$SHARER_PCT = 12.5;
$OWNER_PCT = 71;


$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}

if(isset($_POST['add_product']))
{
	$name = $_POST['prod_name'];
	$format = (int)$_POST['format'];
	$desc = $_POST['prod_desc'];
	$price = (double)$_POST['prod_price'];
	$url = $_POST['prod_url'];

	//TODO: validate data

	$imageFile = "";

	if(isset($_FILES['image']['tmp_name']) && file_exists($_FILES['image']['tmp_name']))
	{
		$image_name = mt_rand() . "." . Ext($_FILES['image']['name']);
		$file = file_get_contents($_FILES['image']['tmp_name']);
		file_put_contents("/var/www/html/stubshare/user_images/$image_name", $file);
	}

	StubShare::AddProduct($user, $name, $format, $desc, $image_name, $price, $OWNER_PCT, $CHARITY_PCT, $SHARER_PCT, $STUBSHARE_PCT, $url);

	header('Location: products.php');
}

function Ext($name)
{
	$ext = substr($name, strrpos($name, ".") + 1);
	$ext = strtolower($ext);
	
	if($ext == "jpg" || $ext == "gif" || $ext == "png" || $ext == "jpeg")
		return $ext;
	else
		return "jpg";
}
include('inc/uitop.inc.php');
?>
<div class="box">
<h2>Add a Product</h2>
<form action="addproduct.php" method="post" enctype="multipart/form-data" >
<table>
<tr>
	<td>Title:</td>
	<td><input type="text" name="prod_name" value="" /></td>
</tr>
<tr>
	<td>Format:</td>
	<td>
	<select name="format">
		<?php
			$q = mysql_query("SELECT * FROM formats");
			while($q && $ary = mysql_fetch_array($q))
			{
				$name = htmlspecialchars($ary['name'], ENT_QUOTES);
				$id = (int)$ary['id'];
				echo "<option value=\"$id\">$name</option>";
			}
		?>
	</select>
	</td>
</tr>
<tr>
	<td>Description:</td>
	<td><textarea name="prod_desc" rows=10 cols=40 ></textarea></td>
</tr>
<tr>
	<td>Image (5MB MAX):</td>
	<td><input type="file" name="image" /></td>
</tr>
<tr>
	<td>Price:</td>
	<td><input type="text" name="prod_price" /> (Including charity's, sharer's, and StubShare's profit)</td>
</tr>
<tr>
	<td>Download URL:</td>
	<td><input type="text" name="prod_url" /></td>
</tr>
</table>
<input type="submit" name="add_product" value="Add" />
</form>
</div>
<?php include('inc/uibottom.inc.php'); ?>
