<?php
require_once('inc/constants.inc.php');
require_once('inc/db.inc.php');
require_once('inc/stubshare.inc.php');
require_once('inc/security.inc.php');

$user = Security::GetCurrentUser();

include('inc/uitop.inc.php');

function PrintFilterMenu()
{
	echo "<a href=\"stubstore.php\">All</a>&nbsp;&nbsp;";
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
		<table style="width: 100%;" class="storetable" >
		<?php
			$format = (int)$_GET['fmt'];
			$q = mysql_query("SELECT * FROM products WHERE format='$format' ORDER BY id DESC");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description'], "stub.php?id=$stubID");

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
					'">Get&nbsp;Link</a></td><td>' . 
					StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
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
	<h2>Latest Releases</h2>
		<table style="width: 100%;" class="storetable" >
		<?php
			$q = mysql_query("SELECT * FROM products ORDER BY id ASC LIMIT 3");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description'], "stub.php?id=$stubID");

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
					'">Get&nbsp;Link</a></td><td>' . 
					StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
				$share = ""; //DISABLE
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
				echo $disp;
			}
		?>
		</table>
	</div>
	<div class="box">
	<h2>Top Stubs</h2>
		<table style="width: 100%;" class="storetable" >
		<?php
			$q = mysql_query("SELECT * FROM products ORDER BY id DESC LIMIT 5");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description'], "stub.php?id=$stubID");

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
					'">Get&nbsp;Link</a></td><td>' . 
					StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
				$share = ""; //DISABLE
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
				echo $disp;
			}
		?>
		</table>
	</div>
	<div class="box">
		<h2>All Stubs</h2>
		<?php PrintFilterMenu(); ?>
		<table style="width: 100%;" class="storetable" >
		<?php
			$q = mysql_query("SELECT * FROM products ORDER BY id DESC");
			while($prodInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$prodInfo['mainstub'];
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description'],"stub.php?id=$stubID");

				$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
					'">Get&nbsp;Link</a></td><td>' . 
					StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
				$share = ""; //DISABLE
				$stubHTML = StubShare::EncodeStubImage($stubID);
				$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
				echo $disp;
			}
		?>
		</table>
	</div>
	<?
}
?>
<?php include('inc/uibottom.inc.php'); ?>
