<?php
require_once('inc/constants.inc.php');
require_once('inc/db.inc.php');
require_once('inc/stubshare.inc.php');
require_once('inc/security.inc.php');

$user = Security::GetCurrentUser();

include('inc/uitop.inc.php');

function PrintFilterMenu()
{
	$q = mysql_query("SELECT * FROM formats");
	while($q && $finfo = mysql_fetch_array($q))
	{	
		$fid = (int)$finfo['id'];
		$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
		echo "<a href=\"stubstore.php?fmt=$fid\">$fname</a>&nbsp;&nbsp;";
	}
}

if(isset($_GET['fmt']) && $fmt_name = StubShare::GetFormatName($_GET['fmt']))
{
	?>
	<div class="box">
		<h2>Stub Store - <?php echo htmlspecialchars($fmt_name, ENT_QUOTES); ?></h2>
		<?php PrintFilterMenu(); ?>
		<table style="width: 100%;">
		<?php
			$format = (int)$_GET['fmt'];
			$q = mysql_query("SELECT * FROM products WHERE format='$format' ORDER BY id DESC");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = $prodInfo['name'];
				$prodDesc = $prodInfo['description'];

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . '">Share</a>' . StubShare::GetFacebookImageLink($stubID);
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td></tr>";
				echo $disp;
			}
		?>
		</table>
	</div>
	<?
}
else
{
	?>
	<div class="box">
		<h2>Stub Store - All Content</h2>
		<?php PrintFilterMenu(); ?>
		<table style="width: 100%;">
		<?php
			$q = mysql_query("SELECT * FROM products ORDER BY id DESC");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = $prodInfo['name'];
				$prodDesc = $prodInfo['description'];

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . '">Share</a>' . StubShare::GetFacebookImageLink($stubID);
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td></tr>";
				echo $disp;
			}
		?>
		</table>
	</div>
	<?
}
?>
<?php include('inc/uibottom.inc.php'); ?>
