<?php
/*
 * This file is Copyright (C) 2011 OSSBox.com
 * This code is provided for peer review purposes only.
 * It may not be distributed, copied, or altered in any way.
 *
 * Sorry, I just don't want backdoored RNG services that look like TRENT to be created.
 * Thanks for your understanding.
*/

// Set the default date to UTC so TRENT is usable from different time zones.
date_default_timezone_set("UTC"); 

$dbhost = 'localhost';
$dbuser = 'ossbox';
$dbpass = 'Nw552SfbbZp';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');

$dbname = 'cracky_trent';
mysql_select_db($dbname);

?>

<!-- Begin TRENT Header -->
<div style="font-size: 8pt; text-align:center; border-bottom: solid #330000 5px; padding-bottom: 5px;">
<img src="/images/trusted_third_party.png" alt="TRENT - Trusted Random Entropy Generator" title="TRENT - Trusted Third Party" />
<br />
<strong>The FREE Third Party Random Number Generator</strong>
</div>
<!-- End TRENT Header -->

<!-- Clock -->
<div style="text-align:right;">
	<?php
		echo "<p>Current UTC Time: " . date("D M j G:i:s T Y") . "</p>";
	?>
</div>

<?php
// ============ Begin Drawing Status Code ============
if(isset($_GET['drawingnum']))
{
	$num = (int)$_GET['drawingnum'];
	$q = mysql_query("SELECT * FROM drawings WHERE drawingnum='$num'");
	if($q && mysql_num_rows($q))
	{
		$ary = mysql_fetch_array($q);
		$complete = ($ary['complete'] == '1');
		$starttime = (int)$ary['starttime'];
		$reviewtime = (int)$ary['reviewtime'];
		$safeprintout = htmlentities($ary['printout'], ENT_QUOTES);
		$safeuserprintout = htmlentities($ary['userprintout'], ENT_QUOTES);
		$drawingtime = $reviewtime + $starttime;
		$drawdate = date("D M j G:i:s T Y", $drawingtime);
		if(!$complete)
		{
		?>
			<div style="background-color: #c9FFd1; border: solid 2px black; margin:20px; padding:10px;">
			<h3 class="nopaddingatall">Info for drawing #<?php echo $num ?></h3>
			<p>
				The random numbers for this drawing have not yet been chosen. <br />
				The organizer of this drawing will be able to run the random number selection after <b><?php echo $drawdate; ?></b>.
			</p>
			
			<p>
				This time period is enforced to allow drawing organizers to prove to their clients that they are not repeating the drawing over and over until TRENT picks the numbers they want. 
				Since you're seeing this message prior to the random number selection, you can be sure that <b>drawing #<?php echo $num; ?> can not be repeated</b>. 
				Once the random numbers for drawing <?php echo $num; ?> have been selected, they cannot be changed.
			</p>
			</div>
		<?
		}
		else
		{
		?>
		<div style="background-color: #C9FFD1; border: solid 2px black; margin:20px; padding:10px;">

			<h3 class="nopaddingatall">Description of drawing #<?php echo $num; ?>:</h3>
			<textarea rows="10" cols="80" style="width:98%; background-color: white; color:black;" readonly="readonly" ><?php echo $safeuserprintout; ?></textarea><br /><br />

			<h3 class="nopaddingatall">Results for drawing #<?php echo $num; ?>:</h3>
			<textarea rows="20" cols="80" style="width:98%; background-color: white; color:black;" readonly="readonly" ><?php echo $safeprintout; ?></textarea><br /><br />

			<h3 class="nopaddingatall">How do I tell if the drawing was fair?</h3>
			<p>Verify the following points:</p>
			<ul>
				<li>Check that the timestamp looks to be on or near the date the drawing was done.</li>
				<li>The drawing organizer published the drawing number before the drawing took place.</li>
				<li>The drawing organizer's description of how to use the random numbers to select the results of the drawing is straight forward and doesn't bias the results. If the process is extremely complicated or you don't understand the steps, this is cause for concern.</li>
				<li>Follow the process of determining the results (e.g. the lottery winner) yourself to determine if the results claimed by the drawing organizer are valid. The drawing organizer should have provided all the steps you need to follow to determine the winner in the "DESCRIPTION" section of the drawing information.</li>
				<li>The range of random numbers makes sense for what they are being used for (e.g. they include everyone, in a lottery scenario).</li>
				<li>Obtain any related files from the organizer and verify that the <b>SHA256</b> checksum is <b>exactly identical</b> using the <a href="checksums.htm">checksum calculator</a>.</li>
				<li>Make sure the random data is in the <strong>second</strong> text area with the title &quot;<?php echo "Results for drawing #$num"; ?>&quot;. The drawing organizer has full control over the text in the first text box.</li> 
			</ul>
			<p>Once you have verified each point, you can be sure that the drawing organizer was not able to influence the random numbers in any way, and you can trust the results. If you are in doubt, feel free to <a href="contact.htm">contact OSSBox</a>, we'll be glad to help.</p>
		</div>
		<?
		}
	}
	else
	{
	?>
		<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
		<b>Drawing #<?php echo $num ?> does not exist.</b>
		</div>
	<?
	}
}
// ============ End Drawing Status Code ============
?>

<h2>Validate Random Numbers</h2>

<!-- Begin Reserve Drawing Number -->
<form action="trustedthirdparty.htm" method="get">
<table>
	<tr>
		<td>Drawing Number:</td>
		<td><input type="text" name="drawingnum" value="" /></td>
		<td><input type="submit" name="submit" value="Check" /></td>
	</tr>
</table>
</form>
<!-- End Reserve Drawing Number -->

<!-- Begin Create Random Numbers -->
<h2>Create Random Numbers</h2>
<a name="dnum"></a><h3>Step 1: Reserve a Drawing Number</h3>
<?php

if(isset($_POST['makedrawingnumber']))
{
	$reviewtime = (int)$_POST['prereview'];
	if($reviewtime < 3600 * 24)
		die("Nope...");
	
	$starttime = time();
	$drawingdate = date("D M j G:i:s T Y", $reviewtime + $starttime);
	$password = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
	$passwordhash = hash("SHA256", $password);
	mysql_query("INSERT INTO drawings (complete, passwordhash, starttime, reviewtime) VALUES (0, '$passwordhash', '$starttime', '$reviewtime')");
	$drawingnum = mysql_insert_id();
	$url = "https://defuse.ca/trustedthirdparty.htm?drawingnum=" . $drawingnum;
	?>
		<div style="background-color: #C9FFD1; border: solid 2px black; margin:20px; padding-left:10px; padding-right: 10px;">
		<p><b>Your Drawing Number Has Been Reserved...</b><br />
		Your drawing number is: <b><? echo $drawingnum; ?></b><br />
		Your passcode is: <b><? echo $password; ?></b><br />
		You will be able to do the drawing after <b><?php echo $drawingdate; ?></b></p>
		<p><b>Please remember this information</b>. Keep your password safe. You will need the drawing number and password to complete the actual drawing.</p>
		<p>Give your clients the following URL:<br />
		<b><a href="<?php echo $url; ?>"><?php echo $url; ?></a></b></p>
		<p>This allows them to see that the drawing number has been reserved BEFORE the drawing took place, which proves to them that you can't redo the drawing over and over until TRENT picks the "right numbers". Your clients can also get to that page by entering the drawing number in the "Validate Random Numbers" section above.</p>
		</div>
	<?
}

?>
<form action="trustedthirdparty.htm#dnum" method="post">
	
Review time:
				<select name="prereview">
					<option value="86400" selected="selected" >24 hours</option>
					<option value="345600">4 days</option>
					<option value="518400">6 days</option>
					<option value="1123200">13 days</option>
					<option value="2592000">30 days</option>
				</select>
				
<input type="submit" name="makedrawingnumber" value="Reserve a Drawing Number" />
</form>
<a name="create"></a><h3>Step 2: Create Random Numbers</h3>
<?php
if(isset($_POST['create']))
{
	$drawingnum = (int)$_POST['drawingnumber'];
	$passcode = trim($_POST['passcode']);
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);

	$q = mysql_query("SELECT * FROM drawings WHERE drawingnum='$drawingnum'");
	if(mysql_num_rows($q) > 0)
	{
		$info = mysql_fetch_array($q);
		$passwordHash = $info['passwordhash'];
		$drawingtime = $info['starttime'] + $info['reviewtime'];

		$randlines1 = (int)$_POST['randlines1'];
		$randlines2 = (int)$_POST['randlines2'];
		$randlines3 = (int)$_POST['randlines3'];

		$noLineRepeat = !(isset($_POST['chosentwice']) && $_POST['chosentwice'] == 'true');

		$rangelow = (int)$_POST['lowval'];
		$rangehigh = (int)$_POST['highval'];

		$numnums = (int)$_POST['numgen'];

		
		$file1hash = isset($_POST['file1hash']) ? ($_POST['file1hash']) : ("");
		$file2hash = isset($_POST['file2hash']) ? ($_POST['file2hash']) : ("");
		$file3hash = isset($_POST['file3hash']) ? ($_POST['file3hash']) : ("");

		$file1path = "";
		$file2path = "";
		$file3path = "";

		if(ctype_xdigit($file1hash))
		{
			$file1path = "/tmp/$file1hash";
		}
		elseif(isset($_FILES['file1']))
		{
			$file1path = $_FILES["file1"]["tmp_name"];
		}

		if(ctype_xdigit($file2hash))
		{
			$file2path = "/tmp/$file2hash";
		}
		elseif(isset($_FILES['file2']))
		{
			$file2path = $_FILES["file2"]["tmp_name"];
		}

		if(ctype_xdigit($file3hash))
		{
			$file3path = "/tmp/$file3hash";
		}
		elseif(isset($_FILES['file3']))
		{
			$file3path = $_FILES["file3"]["tmp_name"];
		}

		if($thispasshash = hash("SHA256", $passcode) != $passwordHash)
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>Incorrect password for drawing #<?php echo $drawingnum ?>.</b><br />
			</div>
		<?
		}
		elseif($info['complete'] != '0')
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>The random numbers for drawing #<?php echo $drawingnum ?> have already been chosen.</b><br />
			</div>
		<?	
		}
		elseif(time() < $drawingtime)
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>The review period for drawing #<?php echo $drawingnum ?> is not complete. You will be able to do the drawing after <?php echo date("D M j G:i:s T Y", (int)$info['starttime'] + (int)$info['reviewtime']); ?></b><br />
			</div>
		<?
		}
		elseif ($rangelow >= $rangehigh && $numnums != 0)
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>The number range is invalid.</b>
			</div>
		<?
		}
		elseif ($numnums < 0 || $randlines1 < 0 || $randlines2 < 0 || $randlines3 < 0)
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>We couldn't possibly generate a NEGATIVE amount of random numbers...</b>
			</div>
		<?
		}
		elseif ($numnums > 1000 || $randlines1 > 1000 || $randlines2 > 1000 || $randlines3 > 1000)
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>Sorry, we can only generate 1000 random numbers at a time.</b>
			</div>
		<?
		}
		elseif(!AllFilesUnder50())
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>Sorry, maximum file size is 50MB.</b>
			</div>
		<?
		}
		elseif($noLineRepeat && (!EnoughLines($file1path, $randlines1) || 
				!EnoughLines($file2path, $randlines2) || 
				!EnoughLines($file3path, $randlines3)))
		{
		?>
			<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
			<b>One of the files doesn't have enough lines to be able to choose the requested number of lines.</b>
			</div>
		<?
		}
		else
		{
			if(isset($_POST['confirmed']) && $_POST['confirmed'] == "true")
			{
				$userprintout = "NAME: $name\n";
				$userprintout .= "DESCRIPTION:\n$description";

				$printout = "DRAWING NUMBER: $drawingnum\n";
				$printout .= "DRAWING DATE: " . date("D M j G:i:s T Y") . "\n";
				$printout .= "AMOUNT OF NUMBERS: $numnums\n";
				$printout .= "RANGE: $rangelow TO $rangehigh\n\n";

				if(ctype_xdigit($file1hash) && file_exists("/tmp/$file1hash"))
				{
					$printout .= "FILE1 SHA256: $file1hash\n\n";
					$printout .= GetRandomLinesOutput("/tmp/$file1hash", $randlines1, $noLineRepeat);
				}

				if(ctype_xdigit($file2hash) && file_exists("/tmp/$file2hash"))
				{
					$printout .= "FILE2 SHA256: $file2hash\n\n";
					$printout .= GetRandomLinesOutput("/tmp/$file2hash", $randlines2, $noLineRepeat);
				}

				if(ctype_xdigit($file3hash) && file_exists("/tmp/$file3hash"))
				{
					$printout .= "FILE3 SHA256: $file3hash\n\n";
					$printout .= GetRandomLinesOutput("/tmp/$file3hash", $randlines3, $noLineRepeat);
				}

				for($i = 1; $i <= $numnums; $i++)
				{
					$randsrc = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
					$randnum = SelectRandomNumber($randsrc, $rangelow, $rangehigh);
					$srchex = bin2hex($randsrc);
					$printout .= "RANDOM NUMBER NUMBER $i: $randnum\n";
				}

				$msafeprintout = mysql_real_escape_string($printout);
				$msafeuserprintout = mysql_real_escape_string($userprintout);
				mysql_query("UPDATE drawings SET complete='1', printout='$msafeprintout', userprintout='$msafeuserprintout' WHERE drawingnum='$drawingnum'");

				$url = "https://defuse.ca/trustedthirdparty.htm?drawingnum=$drawingnum";
				?>
				<div style="background-color: #C9FFD1; border: solid 2px black; margin:20px; padding:10px;">
				<b>Drawing Complete!</b><br />
				Follow this URL to see the results:<br />
				<a href="<?php echo $url; ?>"><?php echo $url; ?></a>
				</div>
				<?
				
			}
			else
			{
			?>
				<div style="background-color: #C9FFD1; border: solid 2px black; margin:20px; padding:10px;">
				<h3 class="nopaddingatall">Confirm Drawing #<?php echo $drawingnum; ?></h3>
				<p>Please confirm the parameters you provided. </p>
				<h4 class="nopaddingatall">Name</h4>
				<?php echo htmlentities($name, ENT_QUOTES); ?><br /><br />
				<h4 class="nopaddingatall">Description</h4>
				<?php echo htmlentities($description, ENT_QUOTES); ?><br /><br />
				<h4 class="nopaddingatall">File1</h4>
				<?php 
					echo GetFileFileInfoHTML("file1");
					echo "<br />Random Lines: $randlines1";
				?>
				<br /><br />
				<h4 class="nopaddingatall">File2</h4>
				<?php 
					echo GetFileFileInfoHTML("file2");
					echo "<br />Random Lines: $randlines2";
				?>
				<br /><br />
				<h4 class="nopaddingatall">File3</h4>
				<?php 
					echo GetFileFileInfoHTML("file3");
					echo "<br />Random Lines: $randlines3";
				?>
				<br /><br />
				<h4 class="nopaddingatall">Number Range</h4>
				<?php echo "$rangelow to $rangehigh"; ?>
				<br /><br />
				<h4 class="nopaddingatall">Amount of Numbers:</h4>
				<?php echo $numnums; ?>
				<br /><br />
				<form action="trustedthirdparty.htm#create" method="post">
				<p><b>These settings <u>CAN NOT BE CHANGED</u> after the random numbers have been selected.</b></p>
				<input type="submit" name="create" value="These values are correct, draw my random numbers!" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="cancel" value="Cancel" />				
				<?php
					$file1hash = "";
					$file2hash = "";
					$file3hash = "";

					//move files to /tmp/sha256
					if(isset($_FILES["file1"]["tmp_name"]) && file_exists($_FILES["file1"]["tmp_name"]))
					{
						$file1hash = hash_file("SHA256", $_FILES["file1"]["tmp_name"]);
						rename($_FILES["file1"]["tmp_name"], "/tmp/$file1hash");	
					}
					if(isset($_FILES["file2"]["tmp_name"]) && file_exists($_FILES["file2"]["tmp_name"]))
					{
						$file2hash = hash_file("SHA256", $_FILES["file2"]["tmp_name"]);
						rename($_FILES["file2"]["tmp_name"], "/tmp/$file2hash");	
					}
					if(isset($_FILES["file3"]["tmp_name"]) && file_exists($_FILES["file3"]["tmp_name"]))
					{
						$file3hash = hash_file("SHA256", $_FILES["file3"]["tmp_name"]);
						rename($_FILES["file3"]["tmp_name"], "/tmp/$file3hash");	
					}
				?>
				<input type="hidden" name="confirmed" value="true" />
				<input type="hidden" name="drawingnumber" value="<?php echo htmlentities($drawingnum); ?>" />
				<input type="hidden" name="passcode" value="<?php echo htmlentities($passcode); ?>" />
				<input type="hidden" name="name" value="<?php echo htmlentities($name); ?>" />
				<input type="hidden" name="description" value="<?php echo htmlentities($description); ?>" />
				<input type="hidden" name="file1hash" value="<?php echo htmlentities($file1hash); ?>" />
				<input type="hidden" name="file2hash" value="<?php echo htmlentities($file2hash); ?>" />
				<input type="hidden" name="file3hash" value="<?php echo htmlentities($file3hash); ?>" />
				<input type="hidden" name="randlines1" value="<?php echo htmlentities($randlines1); ?>" />
				<input type="hidden" name="randlines2" value="<?php echo htmlentities($randlines2); ?>" />
				<input type="hidden" name="randlines3" value="<?php echo htmlentities($randlines3); ?>" />
				<input type="hidden" name="chosentwice" value="<?php if(!$noLineRepeat) echo "true"; ?>" />
				<input type="hidden" name="lowval" value="<?php echo htmlentities($rangelow); ?>" />
				<input type="hidden" name="highval" value="<?php echo htmlentities($rangehigh); ?>" />
				<input type="hidden" name="numgen" value="<?php echo htmlentities($numnums); ?>" />
				</form>
				</div>
			<?
			}
		}
	}
	else
	{
	?>
		<div style="background-color: #FF9F9F; border: solid 2px black; margin:20px; padding:10px;">
		<b>Drawing #<?php echo $drawingnum ?> does not exist.</b><br />
		</div>
	<?
	}
}


function GetFileChecksum($postname)
{
	if(isset($_FILES[$postname]["tmp_name"]) && file_exists($_FILES[$postname]["tmp_name"]))
	{
		return hash_file("SHA256", $_FILES[$postname]["tmp_name"]);
	}
	else
	{
		return "NO FILE";
	}
}

function GetFileSizeHR($postname)
{
	if(isset($_FILES[$postname]["tmp_name"]) && file_exists($_FILES[$postname]["tmp_name"]))
	{
		return format_bytes(filesize($_FILES[$postname]["tmp_name"]));
	}
	else
	{
		return "NO FILE";
	}
}

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

function GetFileName($postname)
{
	if(isset($_FILES[$postname]["name"]) && file_exists($_FILES[$postname]["tmp_name"]))
	{
		return $_FILES[$postname]["name"];
	}
	else
	{
		return "NO FILE";
	}
}

function GetFileFileInfoHTML($postname)
{
	$filename = htmlentities(GetFileName($postname), ENT_QUOTES);
	$filesize = htmlentities(GetFileSizeHR($postname), ENT_QUOTES);
	$filesha256 = htmlentities(GetFileChecksum($postname), ENT_QUOTES);
	echo "Name: $filename <br />Size: $filesize <br />SHA256: $filesha256";
}

function AllFilesUnder50()
{
	return (!isset($_FILES["file1"]["tmp_name"]) || filesize($_FILES["file1"]["tmp_name"]) < 1024 * 1024 * 50) && 
		(!isset($_FILES["file2"]["tmp_name"]) || filesize($_FILES["file2"]["tmp_name"]) < 1024 * 1024 * 50) && 
		(!isset($_FILES["file3"]["tmp_name"]) || filesize($_FILES["file3"]["tmp_name"]) < 1024 * 1024 * 50);
}

function SelectRandomNumber($randombinary, $low, $high)
{
	$divisor = abs($high - $low);
	$remainder = 0;
	for($i = 0; $i < 32; $i++)
	{
		$total = $remainder * 256 + ord(substr($randombinary, $i, 1));
		$remainder = $total % $divisor;
	}
	return $low + $remainder;
}

function GetRandomFileLine($filepath, $exclude)
{
	$linecount = CountFileLines($filepath);
	$randsrc = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
	$srchex = bin2hex($randsrc);
	$randline = SelectRandomNumber($randsrc, 0, $linecount - 1);

	if(in_array($randline, $exclude)) //recursivly keep selecting random lines until we get one that hasn't been chosen yet
		return GetRandomFileLine($filepath, $exclude);

	$linetext = GetLineText($filepath, $randline);

	return array($randline, $srchex, $linetext);
}

function GetLineText($filepath, $lineidx)
{
	$fh = fopen($filepath, 'r');
	$i = 0;
	while(!feof($fh))
	{
		$line = fgets($fh, 1000);
		if($i == $lineidx)
			return $line;
		$i++;
	}
	fclose($fh);
	return $i;
}

function CountFileLines($filepath)
{
	if(is_dir($filepath))
		return 0;
	$fh = fopen($filepath, 'r');
	$i = 0;
	while(!feof($fh))
	{
		fgets($fh); //advance to the next line
		$i++;
	}
	fclose($fh);
	return $i;
}

function EnoughLines($path, $num)
{
	return !file_exists($path) || $num == 0 ||  CountFileLines($path) > $num;
}

function GetRandomLinesOutput($path, $numlines, $noLineRepeat)
{
	$exclude = array();
	$printout = "";
	for($i = 1; $i <= $numlines; $i++)
	{
		list($randnum, $sourcehex, $linepreview) = GetRandomFileLine($path, $exclude);
		if($noLineRepeat)
			$exclude[] = $randnum;
		$printout .= "FILE1 RANDOM LINE $i:\n";
		$printout .= "RANDOM LINE NUMBER (FILE1): $randnum\n";
		$printout .= "LINE PREVIEW: $linepreview\n\n";
	}
	return $printout;
}

?>
<form action="trustedthirdparty.htm#create" method="post" enctype="multipart/form-data">
<table>
<tr><td>Drawing Number:</td><td><input type="text" name="drawingnumber" /></td></tr>
<tr><td>Passcode:</td><td><input type="text" name="passcode" /></td></tr>
<tr><td><b>Drawing Info</b></td></tr>
<tr><td>Name:</td><td><input type="text" name="name" /></td></tr>
<tr><td>Description:</td><td><textarea cols="50" rows="10" name="description"></textarea></td></tr>
<tr><td>File 1 (50MB Max):</td><td><input type="file" name="file1" /> Choose <input type="text" size="2" name="randlines1" value="" /> random line(s)*</td></tr>
<tr><td>File 2 (50MB Max):</td><td><input type="file" name="file2" /> Choose <input type="text" size="2" name="randlines2" value="" /> random line(s)*</td></tr>
<tr><td>File 3 (50MB Max):</td><td><input type="file" name="file3" /> Choose <input type="text" size="2" name="randlines3" value="" /> random line(s)*</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="chosentwice" value="true" /> Allow lines to be chosen twice</td></tr>
<tr><td><b>Random Numbers</b></td></tr>
<tr><td>Number Range:</td><td>From <input type="text" size="9" name="lowval" /> to <input type="text" size="9" name="highval" /> (Inclusive. Range: -1000000000 to +1000000000)</td></tr>
<tr><td>Amount of Numbers:</td><td><input type="text" name="numgen" size="9" /> (Max: 1000)</td></tr>
<tr><td><input type="submit" name="create" value="Pick the Random Numbers!" style="margin-top: 10px;"/></td></tr>
</table>
<br />
* for random lines to be chosen, the file must be in plaintext (ASCII, .txt file) format. Maximum line length is 1000 characters.
</form>
<!-- End Create Random Numbers -->

<h2>What is TRENT?</h2>

<p>TRENT is a <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Backronym">backronym</a> for &quot;<u>T</u>rusted <u>R</u>andom <u>Ent</u>ropy.&quot; The name TRENT <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Alice_and_bob">comes from cryptography</a>, where he is normally used to represent a mutually trusted third party. That's exactly what OSSBox's TRENT service is: a third party random number generator for drawings, contests, and lotteries. TRENT allows both the drawing organizer and contestants to be sure that the winner was selected randomly, and that the random numbers were not biased in any way.</p>

<p>To understand how TRENT works, we must understand the problem that TRENT solves. Suppose a person named Chuck runs a website and wants to host a drawing between the users of his website. The lucky winner of the drawing will get a prize of $100. Chuck could pick a random user himself; he could even use <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Radioactive_decay">radioactive decay</a> as a source of randomness to ensure the drawing is absolutely fair. That would be great, but Chuck has no way of proving to his users that he actually did that instead of picking a friend (or even himself) and saying it was a random choice.</p>

<p>Chuck must be able to generate a random number and prove the following points to his users:</p>

<ul>
	<li>The number is actually random and wasn't biased towards a certain set of values.</li>
	<li>Chuck didn't repeat the random number selection over and over until he got the number he wanted.</li>
	<li>Everyone was given an equal chance of winning (when the random number is used for a drawing).</li>
	<li>Chuck didn't change the meaning of the random number after it had been selected.</li>
</ul>

<p>TRENT provides a means for Chuck to prove those points. TRENT solves the problem with the following security measures.</p>

<ul>
	<li>TRENT uses <a href="https://secure.wikimedia.org/wikipedia/en/wiki//dev/urandom">/dev/urandom</a>, the ultra-secure cryptographic random number generator built into Linux.</li>
	<li>TRENT does not allow Chuck to bias the numbers in any way.</li>
	<li>TRENT enforces at least a 24-hour period between when a drawing number is reserved and when the actual drawing takes place. This allows Chuck to publish the drawing number ahead of time to prove he isn't repeating the drawing until he gets the results he wants.</li>
	<li>TRENT accepts up to 3 texts files which can be used by Chuck to prove that the user list wasn't altered after the random numbers were selected. TRENT saves the SHA256 checksum of the file, so the <a href="/checksums.htm">checksum calculator</a> can be used to ensure the file Chuck provides to his users is the same as the one Chuck provided to TRENT.</li>
</ul>

<h3>How to Use TRENT for Your Drawings</h3>
<p>To use TRENT for your drawings, lotteries, and contests, all you have to do is follow a few simple steps:</p>

<ol>
	<li>Reserve a drawing number.</li>
	<li>Give the drawing number to your clients. Provide at least 24 hours to allow them to check that the drawing number has been reserved.</li>
	<li>Tell TRENT what kind of random numbers you want and let TRENT pick them.</li>
	<li>Give your users the link to TRENT's drawing results page so everyone can verify that the drawing was fair.</li>
</ol>

<p>There are a few important things you have to do to make sure your users can trust the results:</p>

<ul>
	<li>In the description field, provide an easy method to determine the winner from the random numbers.</li>
	<li>If the outcome of the drawing depends on some extra data, such as a user list, save it as a plaintext (.txt) file and give it to TRENT. Even if you don't want to pick a random line in the file, giving the extra data to TRENT allows your clients to verify that the data hasn't been altered <em>after</em> the drawing has taken place.</li>
</ul>

<p>That's all you have to do! TRENT will take care of explaining to your clients how to make sure the drawing was fair. Feel free to run a few practice drawings to get a feel for how TRENT works.</p>

<h2>Source Code</h2>

<p>TRENT's source code is available for peer review. Please note that the code is provided for peer review purposes only. Do not copy it, distribute it, or alter it in any way. I really would like to make TRENT open source but I can't because I know that if I did, backdoored versions of TRENT would be created. If you would like to use TRENT's code for a specific purpose, <a href="/contact.htm">ask me</a>, and I'll probably let you. Thanks for understanding.</p>

<p><a href="/source/trent.html"><strong>Download TRENT's Source Code</strong></a></p>
