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
						$prodDesc = StubShare::LimitText($productInfo['description']);

						$share = '<a href="' . WEBROOT . "share.php?id=$productID" . 
								'">Get&nbsp;Link</a></td><td>' .
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
		</div>
		<div class="box">
			<h2>Share Stubs</h2>
			Sort my stubs <a href="stublist.php?srt=brand">by brand</a> | <a href="stublist.php?srt=format">by format</a><br />
			Sort my stubs <a href="stublist.php?srt=recent">by most recent</a> | <a href="stublist.php?srt=best">by best selling</a><br /><br />
			Show <a href="stublist.php?srt=shared">my shared stubs</a> | <a href="stublist.php?srt=unshared">my unshared stubs</a><br /><br />
		</div>
		
<?php include('inc/uibottom.inc.php'); ?>
