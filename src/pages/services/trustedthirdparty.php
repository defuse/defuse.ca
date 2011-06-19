<?php

/*
Copyright 2011 OSSBox.com
This source code is provided only for public review. You MAY NOT modify it. You 
MAY NOT redistribute it. You may use it for PERSONAL PROJECTS IF AND ONLY IF you
remove ALL markings related to 'TRENT' and 'OSSBox'.

I place these restrictions on this source code because I don't want immitation 
'TRENT' services to pop up, not because I want to be the only one, but because I
don't want *insecure* TRENT immitations to be made, which would ruin the real 
TRENT's reputation and trustworthiness. This is very similar to the TrueCrypt
license.

*/

date_default_timezone_set("UTC"); 
$dbhost = 'localhost';
$dbuser = 'ossbox';
$dbpass = 'Nw552SfbbZp';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die                      ('Error connecting to mysql');

$dbname = 'cracky_trent';
mysql_select_db($dbname);

?>
<div style="font-size: 18pt; text-align:center;">
<img style="margin-bottom: 15px;" src="/images/trusted_third_party.png" alt="TRENT - Trusted Random Entropy Generator" title="TRENT - Trusted Third Party" />
<br />
The FREE Third Party Drawing and RNG Service.
</div>
<div style="text-align:right;">
<?php
echo "<p>UTC TIME: " . date("D M j G:i:s T Y") . "</p>";
?>
</div>

<?php

if(isset($_GET['drawingnum']))
{
	$num = (int)$_GET['drawingnum'];
	$q = mysql_query("SELECT * FROM drawings WHERE drawingnum='$num'");
	if(mysql_num_rows($q))
	{
		$ary = mysql_fetch_array($q);
		$complete = ($ary['complete'] == '1');
		$starttime = (int)$ary['starttime'];
		$reviewtime = (int)$ary['reviewtime'];
		$safeprintout = htmlentities($ary['printout']);
		$drawingtime = $reviewtime + $starttime;
		$drawdate = date("D M j G:i:s T Y", $drawingtime);
		if(!$complete)
		{
		?>
			<div style="background-color: #66FF99; border: solid 2px black; margin:20px; padding:10px;">
			<b>Info for drawing #<?php echo $num ?></b><br />
			The random numbers for this drawing have not yet been chosen. The creator of this drawing will be able to do the drawing after <b><?php echo $drawdate; ?></b>. This period is enforced to allow users of the random numbers to verify that the person doing the drawing isn't repeating the drawing until they get the numbers they want. The fact that you are seeing this message means that you can be absolutely sure that the person doing the drawing cannot bias the results at all.
			</div>
		<?
		}
		else
		{
		?>
			<div style="background-color: #66FF99; border: solid 2px black; margin:20px; padding:10px;">
			<b>Results for drawing #<?php echo $num; ?>:</b><br />
			<textarea rows=20 style="width:98%;" ><?php echo $safeprintout; ?></textarea><br /><br />
			<b>How do I know the drawing is legit?</b><br />
			Verify that the following are true:<br /><br />
			<ul>
				<li>You verified that the drawing number was reserved before the drawing took place.</li>

				<li>The drawing organizer's description of how to use the random numbers to select the results of the drawing is straight forward and doesn't bias the results. If the process is extremely complicated or you don't understand the steps, this is cause for concern.</li>
				<li>Follow the process of determining the results (e.g. the lottery winner) yourself to determine if the results claimed by the drawing organizer are valid. The drawing organizer should have provided all the steps you need to follow to determine the "winner" in the "DESCRIPTION" section of the above information.</li>
				<li>The range of random numbers makes sense for what they are being used for (e.g. they include everyone, in a lottery scenario).</li>
				<li>Check that the timestamp looks to be on or near the date the drawing was done.</li>
				<li>Obtain any related files from the organizer and verify that the SHA256 checksum is <b>exactly identical</b> using the <a href="checksums.htm" />checksum calculator</a>.</li>
			</ul>
			<br />
			If all of those conditions are true, then there is no way the drawing organizer could have biased these numbers, and you can trust the results. If you are in doubt, <a href="contact.htm">contact me</a>. Send me the drawing number and I will look at it personally.
			</div>
		<?
		}
	}
	else
	{
	?>
		<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
		<b>Drawing #<?php echo $num ?> does not exist.</b>
		</div>
	<?
	}
}


?>

<h2>Validate Random Numbers</h2>

<form action="trustedthirdparty.htm" method="get">
<table>
<tr><td>Drawing Number:</td><td><input type="text" name="drawingnum" value="" /></td><td><input type="submit" name="submit" value="Check" /></td></tr>
</table>

</form>
<h2>Create Random Numbers</h2>

<a name="dnum"></a><h3>Step 1: Reserve a Drawing Number</h3>
<?php

if(isset($_POST['makedrawingnumber']))
{
	$reviewtime = (int)$_POST['prereview'];
	if($reviewtime < 3600 * 24)
		die("Don't hax...");
	
	$starttime = time();
	$drawingdate = date("D M j G:i:s T Y", $reviewtime + $starttime);
	$password = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
	$passwordhash = hash("SHA256", $password);
	mysql_query("INSERT INTO drawings (complete, passwordhash, starttime, reviewtime) VALUES (0, '$passwordhash', '$starttime', '$reviewtime')");
	$drawingnum = mysql_insert_id();
	$url = "http://ossbox.com/trustedthirdparty.htm?drawingnum=" . $drawingnum;
	?>
		<div style="background-color: #66FF99; border: solid 2px black; margin:20px; padding:10px;">
		<b>Your Drawing Number Has Been Reserved...</b><br />
		Your drawing number is: <b><? echo $drawingnum; ?></b><br />
		Your passcode is: <b><? echo $password; ?></b><br />
		You will be able to do the drawing after <b><?php echo $drawingdate; ?></b><br /><br />
		Please remember this information. Keep your password safe. You will need the drawing number and password to complete the actual drawing. Publish the drawing number BEFORE you do the drawing.
		<br /><br />
		Give your clients the following URL:<br />
		<b><?php echo $url; ?></b><br />
		This allows them to see that the drawing number has been reserved BEFORE the drawing took place, which prooves to them that you didn't redo the drawing over and over until TRENT picked the "right numbers". Your clients can get to the same page by entering the drawing number in the "Validate Random Numbers" section above.
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
	$passcode = $_POST['passcode'];
	$name = $_POST['name'];
	$description = $_POST['description'];

	$q = mysql_query("SELECT * FROM drawings WHERE drawingnum='$drawingnum'");
	if(mysql_num_rows($q) > 0)
	{
		$info = mysql_fetch_array($q);
		$passwordHash = $info['passwordhash'];
		$drawingtime = $info['starttime'] + $info['reviewtime'];

		$randlines1 = (int)$_POST['randlines1'];
		$randlines2 = (int)$_POST['randlines2'];
		$randlines3 = (int)$_POST['randlines3'];

		$noLineRepeat = !($_POST['chosentwice'] == 'true');

		$rangelow = (int)$_POST['lowval'];
		$rangehigh = (int)$_POST['highval'];

		$numnums = (int)$_POST['numgen'];

		
		$file1hash = $_POST['file1hash'];
		$file2hash = $_POST['file2hash'];
		$file3hash = $_POST['file3hash'];

		$file1path = "";
		$file2path = "";
		$file3path = "";

		if(ctype_xdigit($file1hash))
		{
			$file1path = "/tmp/$file1hash";
		}
		else
		{
			$file1path = $_FILES["file1"]["tmp_name"];
		}

		if(ctype_xdigit($file2hash))
		{
			$file2path = "/tmp/$file2hash";
		}
		else
		{
			$file2path = $_FILES["file2"]["tmp_name"];
		}

		if(ctype_xdigit($file3hash))
		{
			$file3path = "/tmp/$file3hash";
		}
		else
		{
			$file3path = $_FILES["file3"]["tmp_name"];
		}

		if($thispasshash = hash("SHA256", $passcode) != $passwordHash)
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>Incorrect password for drawing #<?php echo $drawingnum ?>.</b><br />
			</div>
		<?
		}
		elseif($info['complete'] != '0')
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>The random numbers for drawing #<?php echo $drawingnum ?> have already been chosen.</b><br />
			</div>
		<?	
		}
		elseif(time() < $drawingtime)
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>The review period for drawing #<?php echo $drawingnum ?> is not complete. You will be able to do the drawing after <?php echo date("D M j G:i:s T Y", (int)$info['starttime'] + (int)$info['reviewtime']); ?></b><br />
			</div>
		<?
		}
		elseif ($rangelow >= $rangehigh && $numnums != 0)
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>The number range is invalid.</b>
			</div>
		<?
		}
		elseif ($numnums < 0 || $randlines1 < 0 || $randlines2 < 0 || $randlines3 < 0)
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>We couldn't possibly generate a NEGATIVE amount of random numbers...</b>
			</div>
		<?
		}
		elseif ($numnums > 1000 || $randlines1 > 1000 || $randlines2 > 1000 || $randlines3 > 1000)
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>Sorry, we can only generate 1000 random numbers at a time.</b>
			</div>
		<?
		}
		elseif(!AllFilesUnder50())
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>Sorry, maximum file size is 50MB.</b>
			</div>
		<?
		}
		elseif($noLineRepeat && (!EnoughLines($file1path, $randlines1) || 
				!EnoughLines($file2path, $randlines2) || 
				!EnoughLines($file3path, $randlines3)))
		{
		?>
			<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
			<b>One of the files doesn't have enough lines to be able to choose the requested number of lines.</b>
			</div>
		<?
		}
		else
		{
			if($_POST['confirmed'] == "true")
			{
				$printout = "DRAWING NUMBER: $drawingnum\n";
				$printout .= "DRAWING DATE: " . date("D M j G:i:s T Y") . "\n";
				$printout .= "AMOUNT OF NUMBERS: $numnums\n";
				$printout .= "RANGE: $rangelow TO $rangehigh\n";
				$printout .= "NAME: $name\n";
				$printout .= "DESCRIPTION:\n$description\n\n";

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
					$printout .= "RANDOM NUMBER NUMBER $i\nSOURCE DATA: $srchex\nCHOSEN NUMBER: $randnum\n\n";
				}

				$msafeprintout = mysql_real_escape_string($printout);
				mysql_query("UPDATE drawings SET complete='1', printout='$msafeprintout' WHERE drawingnum='$drawingnum'");
				$url = "http://ossbox.com/trustedthirdparty.htm?drawingnum=$drawingnum";
				?>
				<div style="background-color: #66FF99; border: solid 2px black; margin:20px; padding:10px;">
				<b>Drawing Complete!</b><br />
				Follow this URL to see the results:<br />
				<a href="<?php echo $url; ?>"><?php echo $url; ?></a>
				</div>
				<?
				
			}
			else
			{
			?>
				<div style="background-color: #66FF99; border: solid 2px black; margin:20px; padding:10px;">
				<b>Please Confirm Information for Drawing #<?php echo $drawingnum; ?>...</b><br /><br />
				<b>Name:</b><br />
				<?php echo htmlentities($name, ENT_QUOTES); ?><br /><br />
				<b>Description:</b><br />
				<?php echo htmlentities($description, ENT_QUOTES); ?><br /><br />
				<b>File1:</b><br />
				<?php 
					echo GetFileFileInfoHTML("file1");
					echo "<br />Random Lines: $randlines1";
				?>
				<br /><br />
				<b>File2:</b><br />
				<?php 
					echo GetFileFileInfoHTML("file2");
					echo "<br />Random Lines: $randlines2";
				?>
				<br /><br />
				<b>File3:</b><br />
				<?php 
					echo GetFileFileInfoHTML("file3");
					echo "<br />Random Lines: $randlines3";
				?>
				<br /><br />
				<b>Number Range:</b><br />
				<?php echo "$rangelow to $rangehigh"; ?>
				<br /><br />
				<b>Amount of Numbers:</b><br />
				<?php echo $numnums; ?>
				<br /><br />
				<form action="trustedthirdparty.htm" method="post">
				<b>These settings can NOT be changed once the drawing has been completed.</b>
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
		<div style="background-color: #FF0033; border: solid 2px black; margin:20px; padding:10px;">
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
	//true when the file doesn't exist or the filesize is under 50mb
	return !file_exists($_FILES["file1"]["tmp_name"]) || filesize($_FILES["file1"]["tmp_name"]) < 1024 * 1024 * 50 && 
		!file_exists($_FILES["file2"]["tmp_name"]) || filesize($_FILES["file2"]["tmp_name"]) < 1024 * 1024 * 50 && 
		!file_exists($_FILES["file3"]["tmp_name"]) || filesize($_FILES["file3"]["tmp_name"]) < 1024 * 1024 * 50;
}

//convert the 32 random bytes to a number by computing the value of the huge number (32 bytes, base 256) mod the size range
function SelectRandomNumber($randombinary, $low, $high)
{
	$mod = abs($high - $low);
	$number = 0;
	for($i = 0; $i < 32; $i++)
	{
		$number = ($number +  ord($randombinary[$i]) * powmod(256, $i, $mod)) % $mod;
	}
	$number = mt_rand() % $mod;
	return $low + $number;
}

function powmod($base, $exp, $mod)
{
	$res = 1;
	for($i = 0; $i < $exp; $i++)
	{
		$res = ($res * $base) % $mod;
	}
	return $res;
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
		$printout .= "SOURCE DATA: $sourcehex\n";
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
<tr><td>Number Range:</td><td>From <input type="text" size="9" name="lowval" /> to <input type="text" size="9" name="highval" /> (inclusive, Range: -1000000000 to +1000000000)</td></tr>
<tr><td>Amount of Numbers:</td><td><input type="text" name="numgen" size="9" /> (Max: 1000)</td></tr>
<tr><td><input type="submit" name="create" value="Draw Random Number" /></td></tr>
</table>
<br />
* for random lines to be chosen, the file must be in plaintext format (ASCII text only file). Maximum line length is 1000 characters.
</form>
<h2>How does TRENT work?</h2>

<p>The hard part of doing an online contest drawing is getting the contestants to trust you. You can use the highest quality random numbers that were created by monitoring atmospheric noise, but the contestants might wonder if you're really doing that, or just saying that you are. They might think that you're selecting the winner yourself, always giving the prize to a freind or keeping it for yourself.</p>

<p>In order for the contestants to trust the contest organizer and the results of a drawing, the selection of the winner has to be done by a third party. This third party should have no ties to the contest organizer or any of the contestants. That way the third party has no reason to bias the results. TRENT is this third party.</p>

<p>Using TRENT, contest organizers will reserve a drawing number at least a day before the drawing takes place. The organizer will then send each contestant the drawing number. Before the drawing occurs, the contestants can visit the TRENT webpage to verify that the drawing number has been reserved. By doing so, they are guaranteeing that the organizer isn't reusing someone else's random numbers.</p>

<p>Whenever the organizer wants to do the drawing, he simply has to fill out the form below. TRENT will generate the random numbers that are required to select a winner, and contestants will be able to access the results by visiting the TRENT web page.</p>

<p>By seeing the results on the TRENT web page, the contestants will be able to truly trust the results of the contest. They will know for sure that the random numbers used are cryptographically secure, because TRENT uses the "/dev/urandom" CSPRNG built into Linux. They will also know for sure that the contest organizer had no control over who was selected as the winner. By remembering the drawing number, the contestants will also know know for sure that the contest organizer didn't re-do the drawing over and over until the "right" winner was selected.</p>

<p>To make things even better, TRENT will always be completely <b><u>FREE</u></b>. Also, <a href="trentsource.htm">TRENT's source code</a> is available for peer review.</p>
<h3>Instructions to Contest Organizers</h3>
<p>First, you will have to reserve a drawing number at least 24 hours before you want to do the drawing. This lets your clients ensure that you didn't just repeat the drawing until you got the results that you wanted. You must publish the drawing number and instruct your clients to write down the number BEFORE the drawing takes place. When you create a drawing number, you will be given a passcode to prevent someone else from being able to steal your drawing number. Write this passcode down and keep it locked away until you are ready to do the drawing.</p>

<p>At least 24 hours after you have reserved your drawing number, you will be able to do the actual drawing. You will fill out the "Step 2" form to complete the drawing.</p>

<p>The form requires that you enter a description. The description should describe <b>as clearly as possible</b> how you will use the random numbers to select a winner. These steps should be easily reproducible. For example "The random number will be the user-id of the winner" or "The first randomly selected line in file 1 will be the winner's username, the second random line will choose the runner up." Whatever process you use to decide the winner, it should not have any biases, and your clients should be able to easily reproduce the selection based on the random number(s). <b>The more complicated you make the process, the less your clients will trust the results.</b></p>

<p>The form allows you to submit up to 3 files. Only the <a href="https://secure.wikimedia.org/wikipedia/simple/wiki/Cryptographic_hash_function">SHA256</a> checksum of these files will be saved in TRENT. You will have to distribute these files yourself, but your clients can verify that you havn't changed them by checking the <a href="https://secure.wikimedia.org/wikipedia/simple/wiki/Cryptographic_hash_function">SHA256</a> hash. Make sure you respect your client's privacy. Don't publish everyone's email address. Instead, use their usernames for some popular web service (e.g. youtube or twitter).</p>

<p>After you do the drawing, you will be able to send your clients to the drawing page so they can follow your instructions in the description to verify the results you claim.</p>

<p>If you have any questions about how this works or if you need help, please <a href="contact.htm">contact me</a>. I'll be glad to help.</p>

<h2>Source Code</h2>
<p>I have made TRENT's full source code available for peer review. It is written in PHP and MySQL.</p>

<a href="trentsource.htm"><b>See TRENT's Source Code</b></a>

