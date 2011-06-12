<?php
require_once('inc/constants.inc.php');
require_once('inc/db.inc.php');
require_once('inc/stubshare.inc.php');
require_once('inc/security.inc.php');

$user = Security::GetCurrentUser();
$userid = (int)StubShare::GetUserID($user);

include('inc/uitop.inc.php');

function PrintFilterMenu()
{
	global $html_profile_user;
	echo "<a href=\"profile.php?user=$html_profile_user\">$html_profile_user's Profile</a>&nbsp;&nbsp;";
	$q = mysql_query("SELECT * FROM formats");
	while($q && $finfo = mysql_fetch_array($q))
	{	
		$fid = (int)$finfo['id'];
		$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
		echo "<a href=\"profile.php?user=$html_profile_user&fmt=$fid\">$fname</a>&nbsp;&nbsp;";
	}
}

$profile_user = $user;
if(isset($_GET['user']))
{
	$profile_user = $_GET['user'];
	$html_profile_user = htmlspecialchars($profile_user);
}

?>

<div class="box">
		<h1><?php echo htmlspecialchars("$profile_user's statistics", ENT_QUOTES); ?></h1>
		<?php 
			$charity = round(StubShare::GetUserCharityContrib($profile_user),2);
			echo htmlspecialchars("So far, $profile_user has donated $$charity to charity."); 
		?>
</div>
<?

if(isset($_GET['fmt']) && $fmt_name = StubShare::GetFormatName($_GET['fmt']))
{
	?>
	<div class="box">
		<h1><?php echo htmlspecialchars("$profile_user's $fmt_name", ENT_QUOTES); ?></h1>
		<?php PrintFilterMenu(); ?>
		<div class="whitebox">
		<table style="width: 100%;" class="storetable" >
		<?php
			$q = mysql_query("SELECT * FROM stubs WHERE owner='$userid' ORDER BY id DESC LIMIT 5");
			while($stubInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$stubInfo['id'];
				$prodInfo = StubShare::GetProductInfo($stubInfo['product']);
				if($prodInfo['format'] != $_GET['fmt'])
					continue;
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description']);

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
	</div>
	<?
}
else
{
	?>

	<div class="box">
	<h1><?php echo htmlspecialchars("$profile_user's Recent Stubs", ENT_QUOTES); ?></h1>
		<?php PrintFilterMenu(); ?>
		<div class="whitebox">
		<table style="width: 100%;" class="storetable" >
		<?php
			$q = mysql_query("SELECT * FROM stubs WHERE owner='$userid' ORDER BY id DESC LIMIT 5");
			while($stubInfo = mysql_fetch_array($q))
			{
				$stubID = (int)$stubInfo['id'];
				$prodInfo = StubShare::GetProductInfo($stubInfo['product']);
				$productID = (int)$prodInfo['id'];
				$prodName = htmlspecialchars($prodInfo['name'], ENT_QUOTES);
				$prodDesc = StubShare::LimitText($prodInfo['description']);

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
	</div>

	<?
}
?>
<?php include('inc/uibottom.inc.php'); ?>
