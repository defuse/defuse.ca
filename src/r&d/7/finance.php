<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');
require_once('inc/db.inc.php');
$user = Security::GetCurrentUser();
$userid = (int)StubShare::GetUserID($user);
if($user === false)
{
	header('Location: login.php');
	die();
}

include('inc/uitop.inc.php');
?>
		<div class="box">
			<h2>Purchase History</h2>
			<table class="infotable">
			<tr><th>Paid</th><th>Title</th><th>Brand</th><th>Format</th><th>Share</th></tr>
			<?php
			$q = mysql_query("SELECT * FROM purchases WHERE user='$userid'");
			while($ary = mysql_fetch_array($q))
			{
				$price = (double)$ary['pricepaid'];
				$productInfo = StubShare::GetProductInfo($ary['product']);

				$productName = htmlspecialchars($productInfo['name'], ENT_QUOTES);
				$productID = (int)$productInfo['id'];	
				$ownerID = $productInfo['owner'];

				$formatInfo = StubShare::GetProductFormat($productID);
				$formatName = $formatInfo['name'];

				$ownerInfo = StubShare::GetUserInfo(StubShare::GetUsernameFromID($ownerID));
				$ownerName = htmlspecialchars($ownerInfo['username'], ENT_QUOTES);

				$shareLink = WEBROOT . "share.php?id=$productID";

				$linkbefore = "";
				$linkafter = "";
				if($url = StubShare::UserOwns($userid, $productID))
				{
					$linkbefore = "<a href=\"$url\">";
					$linkafter = "</a>";
				}

				//fb link shares the owners stub unless this user is sharing it
				$stubID = StubShare::GetStubFor($productID, $userid);

				if($stubID === false)
				{
					$fb = StubShare::GetFacebookImageLink($productInfo['mainstub']);
					$twitter = StubShare::GetTwitterImageLink($productInfo['mainstub']);
				}
				else
				{
					$fb = StubShare::GetFacebookImageLink($stubID);
					$twitter = StubShare::GetTwitterImageLink($stubID);
				}
				echo "<tr><td>$$price</td><td>$linkbefore$productName$linkafter</td><td>$ownerName</td><td>$formatName</td><td><a href=\"$shareLink\">Get&nbsp;Link</a></td><td>$fb</td><td>$twitter</td></tr>";
			}
			?>
			</table>
			<br />
			<a href="#">Full Purchase History</a>
		</div>
		
<?php include('inc/uibottom.inc.php'); ?>
