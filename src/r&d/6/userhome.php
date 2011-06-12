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
			<h2>Shared Stubs</h2>
			<table class="infotable">
			<tr><th>Title</th><th>Brand</th><th>Format</th><th>Price</th><th>Your Cut</th><th>Sales</th><th>Share</th></tr>
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
				echo "<tr><td>$linkbefore$product$linkafter</td><td>$owner</td><td>$formatName</td><td>$$price</td><td>$user_share%</td><td>$sales</td><td><a href=\"" . WEBROOT . "stubcode.php?id=$stubID\">Get Codes</a></td><td>$facebook</td><td>$twitter</td></tr>";
			}

			?>
			</table>
		</div>
		<div class="box">
			<h2>Recent Purchases</h2>
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
				echo "<tr><td>$$price</td><td>$linkbefore$productName$linkafter</td><td>$ownerName</td><td>$formatName</td><td><a href=\"$shareLink\"><img src=\"images/share.gif\" /></a></td><td>$fb</td><td>$twitter</td></tr>";
			}
			?>
			</table>
			<br />
			<a href="#">Full Purchase History</a>
		</div>
		<div class="box">
			<h2>Recommended Stubs</h2>
				<table class="storetable" >
				<?php
					$q = mysql_query("SELECT * FROM stubs WHERE owner!='$userid' ORDER BY id DESC LIMIT 5");
					while($q && $stub = mysql_fetch_array($q))
					{
						$stubID = $stub['id'];
						
						$productID = $stub['product'];

						if(StubShare::UserOwns($userid, $productID) !== false) //skip ones we own
							continue; 

						$productInfo = StubShare::GetProductInfo($productID);

						$prodName = htmlspecialchars($productInfo['name'], ENT_QUOTES);
						$prodDesc = htmlspecialchars(StubShare::LimitText($productInfo['description']), ENT_QUOTES);

						$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
								'"><img src="images/share.gif" /></a></td><td>' .
								 StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
						
						$stubHTML = StubShare::EncodeStubImage($stubID);
						$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
						//
						//echo "$prodDisp $stubHTML<br />";
						echo $disp;
					}
				?>
				</table>
				<br />
				<a href="stubstore.php"><b>More..</b></a> <br />
				<br />
				Browse content: 
				<?php
					$q = mysql_query("SELECT * FROM formats");
					while($q && $finfo = mysql_fetch_array($q))
					{	
						$fid = (int)$finfo['id'];
						$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
						echo "<a href=\"stubstore.php?fmt=$fid\">$fname</a>&nbsp;&nbsp;";
					}
				?>
				<a href="stubstore.php">All</a>
		</div>
<?php include('inc/uibottom.inc.php'); ?>
