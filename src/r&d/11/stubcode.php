<?php
require_once('inc/constants.inc.php');
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}

if(isset($_GET['id']))
{
	$stubID = (int)$_GET['id'];
	$info = StubShare::GetStubInfo($stubID);
	if(!$info)
	{
		header('Location: userhome.php');
		die();
	}

	$prodInfo = StubShare::GetProductInfo($info['product']);
	if(!$prodInfo)
	{
		header('Location: userhome.php');
		die();
	}
}

$image = StubShare::EncodeStubImage($stubID);
$bbcode = StubShare::EncodeStubBBC($stubID);
$plainURL = WEBROOT . "stub.php?id=$stubID";

$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
include('inc/uitop.inc.php');
?>
<div class="box">
<h2>Product Info</h2>
<table style="width:100%">
<tr><td>
	<table>
	<tr>
		<td>Name:</td>
		<td><?php echo htmlspecialchars($prodInfo['name'], ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>Description:</td>
		<td><?php echo StubShare::LimitText($prodInfo['description']); ?></td>
	</tr>
	<tr>
		<td>Image:</td>
		<td><img style="width 200px; height: 200px;" src="prodimg.php?id=<?php echo (int)$prodInfo['id']; ?>" /></td>
	</tr>
	<tr>
		<td>Price:</td>
		<td><?php echo "$" . htmlspecialchars($prodInfo['price'], ENT_QUOTES); ?></td>
	</tr>
	</table>
</td><td valign="top" >
	<b>Copy-paste this link to any digital space to share the content and collect the 12% coupon</b>
	<br /><br />
	<?php echo $plainURL; ?>
</tr></table>
</div>
<!--<div class="box">
<h2>Stub Codes for: <?php echo $prodName; ?></h2>
<table cellspacing=30 >
<tr><td><b>Share on Facebook:</b></td><td><?php echo StubShare::GetFacebookImageLink($stubID); ?></td></tr>
</table>
<table cellspacing=30 >
<tr><th>Type</th><th>Preview</th><th>Code</th></tr>
<tr><td>Image</td><td><?php echo $image; ?></td><td><pre><?php echo htmlspecialchars($image, ENT_QUOTES); ?></pre></td></tr>
<tr><td>URL</td><td><?php echo $plainURL; ?></td><td><?php echo $plainURL; ?></td></tr>
<tr><td>BBCode</td><td></td><td></td></tr>
</table>

</div>-->
<?php
	include('inc/uibottom.inc.php');
?>
