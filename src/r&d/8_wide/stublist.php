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
			<h2>Share Stubs</h2>
			Sort my stubs <a href="stublist.php?srt=source">by source</a> | <a href="stublist.php?srt=format">by format</a><br />
			Sort my stubs <a href="stublist.php?srt=recent">by most recent</a> | <a href="stublist.php?srt=best">by best selling</a><br /><br />
			Show <a href="stublist.php?srt=shared">my shared stubs</a> | <a href="stublist.php?srt=unshared">my unshared stubs</a><br /><br />
			<table class="infotable">
			<tr><th>Title</th><th>Source</th><th>Format</th><th>Price</th><th>Coupon</th><th>Sales</th><th>Share</th></tr>
			<?php
			$q = mysql_query("SELECT * FROM stubs WHERE owner='$userid'");
			while($ary = mysql_fetch_array($q))
			{
				$stubID = (int)$ary['id'];
				$sales = (int)$ary['sales'];
				$productID = (int)$ary['product'];		
				$productInfo = StubShare::GetProductInfo($productID);
				$price = (double)$productInfo['price'];

				$formatInfo = StubShare::GetProductFormat($productID);
				$formatName = $formatInfo['name'];
			
				$user_share = $productInfo['sharer_pct'];
				if($userid == $productInfo['owner'])
				{
					$user_share += $productInfo['owner_pct'];
				}

				$coupon = round($user_share / 100 * $price,2);			

				$ownerInfo = StubShare::GetUserInfo(StubShare::GetUsernameFromID($productInfo['owner']));
	
				$product = htmlspecialchars($productInfo['name'], ENT_QUOTES);
				$owner = htmlspecialchars($ownerInfo['username'], ENT_QUOTES); 

				$linkbefore = "";
				$linkafter = "";
				if($url = StubShare::UserOwns($userid, $productID))
				{
					$linkbefore = "<a href=\"$url\">";
					$linkafter = "</a>";
				}
				else
				{
					$linkbefore = "<a href=\"". WEBROOT . "stub.php?id=$stubID\">";
					$linkafter = "</a>";
				}

				$facebook = StubShare::GetFacebookImageLink($stubID);
				$twitter = StubShare::GetTwitterImageLink($stubID);
				echo "<tr><td>$linkbefore$product$linkafter</td><td>$owner</td><td>$formatName</td><td>$$price</td><td>$$coupon</td><td>$sales</td><td><a href=\"" . WEBROOT . "stubcode.php?id=$stubID\">Get&nbsp;Link</a></td><td>$facebook</td><td>$twitter</td></tr>";
			}

			?>
			</table>
		</div>
		
<?php include('inc/uibottom.inc.php'); ?>
