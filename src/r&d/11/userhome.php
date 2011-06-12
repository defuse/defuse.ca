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
		<h2>Transfer Credit</h2>
		Move credit <a href="addbalance.php">into</a> | <a href="#">out of</a> my Stubshare account.
		</div>

		<div class="box">
			<?php
				$rebold = "";
				$reboldEnd = "";
		
				$stubsBold = "";
				$stubsBoldEnd = "";

				$unsharedBold = "";
				$unsharedBoldEnd = "";

				$pBold = "";
				$pBoldEnd = "";

				if(!isset($_GET['tbl']))
				{
					$rebold = "<b>";
					$reboldEnd = "</b>";
				}
				elseif($_GET['tbl'] == "stubs")
				{
					$stubsBold = "<b>";
					$stubsBoldEnd = "</b>";
				}
				elseif($_GET['tbl'] == "unshared")
				{
					$unsharedBold = "<b>";
					$unsharedBoldEnd = "</b>";
				}
				elseif($_GET['tbl'] == "purchases")
				{
					$pBold = "<b>";
					$pBoldEnd = "</b>";
				}

				echo "<a href=\"userhome.php\">$rebold Recommended$reboldEnd</a> | <a href=\"userhome.php?tbl=stubs\">$stubsBold My Recent Stubs$stubsBoldEnd</a> | <a href=\"userhome.php?tbl=purchases\"> $pBold My Recent Purchases $pBoldEnd</a> | <a href=\"userhome.php?tbl=unshared\">$unsharedBold Unshared Purchases$unsharedBoldEnd</a>"
			?>
			
			<?php
			if(!isset($_GET['tbl']) )
			{
			?>
<table class="storetable" >
				<?php
					$q = mysql_query("SELECT * FROM stubs WHERE owner!='$userid' ORDER BY id DESC");
					$count = 0;
					while($q && $stub = mysql_fetch_array($q))
					{
						$stubID = $stub['id'];
						
						$productID = $stub['product'];

						if(StubShare::UserOwns($userid, $productID) !== false) //skip ones we own
							continue; 

						$productInfo = StubShare::GetProductInfo($productID);

						$prodName = htmlspecialchars($productInfo['name'], ENT_QUOTES);
						$prodDesc = StubShare::LimitText($productInfo['description'], "stub.php?id=$stubID");

						$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
								'">Get&nbsp;Link</a></td><td>' .
								 StubShare::GetFacebookImageLink($stubID) . "</td><td>" . StubShare::GetTwitterImageLink($stubID);
						
						$stubHTML = StubShare::EncodeStubImage($stubID);
						$disp = "<tr><td style=\"width:62px\"><img src=\"prodimg.php?id=$productID\" style=\"float:left; width:60px; height:60px;\"/></td><td>$prodName</td><td>$prodDesc</td><td>$stubHTML</td><td>$share</td></tr>";
						//
						//echo "$prodDisp $stubHTML<br />";
						echo $disp;
						if($count >= 5)
							break;
						$count++;
					}
				?>
				</table>
				<br />
				<a href="stubstore.php" class="colorlink"><b>View more..</b></a> <br />
				<br />
				Browse: 
				<?php
					$q = mysql_query("SELECT * FROM formats");
					while($q && $finfo = mysql_fetch_array($q))
					{	
						$fid = (int)$finfo['id'];
						$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
						echo "<a class=\"colorlink\" href=\"stubstore.php?fmt=$fid\">$fname</a>&nbsp;&nbsp;";
					}
				?>
				<a href="stubstore.php" class="colorlink" >All</a>
			<?
			}
			else if($_GET['tbl'] == "stubs")
			{
			?>
				<table class="infotable">
				<tr><th>Price</th><th>Title</th><th>Source</th><th>Format</th><th>Share</th></tr> <br /><br />
				<?php
				$q = mysql_query("SELECT * FROM stubs WHERE owner='$userid' ORDER BY id DESC  LIMIT 3");
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

					$stub = StubShare::EncodeStubImage($stubID);
					echo "<tr><td>$stub</td><td>$linkbefore$product$linkafter</td><td>$owner</td><td>$formatName</td><td><a href=\"" . WEBROOT . "stubcode.php?id=$stubID\">Get&nbsp;Link</a></td><td>$facebook</td><td>$twitter</td></tr>";
				}

				?>
				</table>
				<a href="stublist.php">More..</a>
			<?
			}
			else if($_GET['tbl'] == "unshared")
			{
			?>
				<table class="infotable">
				<tr><th>Paid</th><th>Title</th><th>Source</th><th>Format</th><th>Share</th></tr> <br /><br />
				<?php
				$q = mysql_query("SELECT * FROM purchases WHERE user='$userid' ORDER BY id DESC LIMIT 3");
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
						$stub = StubShare::EncodeStubImage($productInfo['mainstub']);
					}
					else
					{
						$fb = StubShare::GetFacebookImageLink($stubID);
						$twitter = StubShare::GetTwitterImageLink($stubID);
						$stub = StubShare::EncodeStubImage($stubID);
					}
					echo "<tr><td>$stub</td><td>$linkbefore$productName$linkafter</td><td>$ownerName</td><td>$formatName</td><td><a href=\"$shareLink\">Get&nbsp;Link</a></td><td>$fb</td><td>$twitter</td></tr>";
				}
				?>
				</table>
				<a href="finance.php">More..</a>
			<?
			}
			else if($_GET['tbl'] == "purchases")
			{
			?>

				<table class="infotable">
				<tr><th>Paid</th><th>Title</th><th>Source</th><th>Format</th><th>Share</th></tr> <br /><br />
				<?php
				$q = mysql_query("SELECT * FROM purchases WHERE user='$userid' ORDER BY id DESC LIMIT 3");
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
						$stub = StubShare::EncodeStubImage($productInfo['mainstub']);
					}
					else
					{
						$fb = StubShare::GetFacebookImageLink($stubID);
						$twitter = StubShare::GetTwitterImageLink($stubID);
						$stub = StubShare::EncodeStubImage($stubID);
					}
					echo "<tr><td>$stub</td><td>$linkbefore$productName$linkafter</td><td>$ownerName</td><td>$formatName</td><td><a href=\"$shareLink\">Get&nbsp;Link</a></td><td>$fb</td><td>$twitter</td></tr>";
				}
				?>
				</table>
				<a href="finance.php">More..</a>
			<?
			}
			?>
		</div>

		<div class="box">
			<h2>Share Stubs</h2>
			Sort my stubs <a href="stublist.php?srt=source">by source</a> | <a href="stublist.php?srt=format">by format</a><br />
			Sort my stubs <a href="stublist.php?srt=recent">by most recent</a> | <a href="stublist.php?srt=best">by best selling</a><br /><br />
			Show <a href="stublist.php?srt=shared">my shared stubs</a> | <a href="stublist.php?srt=unshared">my unshared stubs</a><br /><br />
		</div>
		
<?php include('inc/uibottom.inc.php'); ?>
