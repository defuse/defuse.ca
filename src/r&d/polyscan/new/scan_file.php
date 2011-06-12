<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
//NOTE ABOUT CREDIT:
//	this page takes credit for archives and says no credit if they dont have enough to scan the whole archive
//NO checkes are made for single file uplloads, they are just added with username 'public'
$docount = 0;
$username = sqli(GetUsername());
if(!CanPrivateScan() || $username == 'public')
{
	$username = 'public';
	$credit = 99999;
}
else
{
	$credit = NumCredit();
}
if(HasUnlimited())
{
	$credit = 99999;
}
if(isset($_POST['submit']))
{
	$asquared = DoScan("asquared");
	$avira = DoScan("avira");
	$bitdefender = DoScan("bitdefender");
	$eset = DoScan("eset");
	$esafe = DoScan("esafe");
	$fprot = DoScan("fprot");
	$ikarus = DoScan("ikarus");
	$kaspersky = DoScan("kav");
	$mcafee = DoScan("mcafee");
	$norton = DoScan("norton");
	$panda = DoScan("panda");
	$quickheal = DoScan("quickheal");
	$solo = DoScan("solo");
	$sophos = DoScan("sophos");
	$vba32 = DoScan("sophos");
	$virusbuster = DoScan("virusbuster");
	if($docount == 0)
	{
		echo "Please select at least one antivirus."; die();
	}
	//TODO: check file size is reasonable.
	//TODO: max files in archive
	$toemail = 0;
	$addr = '';
	if(!empty($_POST['email']))
	{
		$toemail = 1;
		$addr = sqli($_POST['email']);
	}
	if($_POST['archive'] == "extract")
	{
		$arcpath = $_FILES['toscan']['tmp_name'];
		$arcname = basename($_FILES['toscan']['name']);
		$ext = substr($arcname, strpos($arcname, "."));

		$arcdata = file_get_contents($arcpath);
		$archash = hash("SHA256", $arcdata);
		$files = GetFilesFromArchive($arcdata, $ext);
		if(count($files) == 0)
		{
			echo "Invalid Archive.";
			die();
		}
		else if(count($files) > $credit)
		{
			echo "Not enough credit.";
			die();
		}
		//values the same for all file
		$time = time();
		$saveuntil = 0;
		if($_POST['time'] == "NO")
		{
			$saveuntil = 0;
		}
		else
		{
			$saveuntil = (int)$_POST['time'] * 24 * 3600 + time();
		}
		
		$arcname = sqli($arcname);
		if(!HasUnlimited())
			RemoveCredit(count($files));
		$archive = mysql_query("INSERT INTO archive (arcname, hash, username, email, addr) VALUES('$arcname', '$archash', '$username', '$toemail', '$addr')");
		$arcid = mysql_insert_id();
		foreach($files as $file)
		{
			$filename = sqli($file['name']);
			$filedata = $file['data'];

			$size = strlen($filedata);
			$queue = 0;
			$sha256 = hash("SHA256", $filedata);
			$md5 = md5($filedata);
			$sha1 = sha1($filedata);
			$hexdata = bin2hex($filedata);
			
			//always scan archives dont do any rescan bullshit
			mysql_query("INSERT INTO queue (filename, md5, sha1, sha256, complete, queue, username, filesize, filedata, added, keepuntil, arcid, asquared, avira, bitdefender, esafe, eset, fprot, ikarus, kav, mcafee, norton, panda, quickheal, solo, sophos, vba32, virusbuster) VALUES('$filename', '$md5', '$sha1', '$sha256', '0', '$queue', '$username', '$size', '$hexdata',  '$time', '$saveuntil', '$arcid', '$asquared', '$avira', '$bitdefender', '$esafe', '$eset', '$fprot', '$ikarus', '$kaspersky', '$mcafee', '$norton', '$panda', '$quickheal', '$solo', '$sophos', '$vba32', '$virusbuster')");
		}

		$arcid = obf($arcid);
		Header("Location: index.php?p=view_archive&id=$arcid");
	}
	else
	{
		$path = $_FILES['toscan']['tmp_name'];
		$size = filesize($path);
		$data = file_get_contents($path);
		$queue = 0;
		$sha256 = hash("SHA256", $data);
		$md5 = md5($data);
		$sha1 = sha1($data);
		$data = bin2hex($data);
		$time = time();
		$saveuntil = 0;
		if($_POST['time'] == "NO")
		{
			$saveuntil = 0;
		}
		else
		{
			$saveuntil = $_POST['time'] * 24 * 3600 + time();
		}
		$filename = sqli(basename( $_FILES['toscan']['name']));
		//taking credit moved to when complete gets set to 0
		/*if(!HasUnlimited())
			RemoveCredit(1);*/
			
		//$query = mysql_query("INSERT INTO queue (filename, md5, sha1, sha256, complete, queue, username, filesize, filedata, added, keepuntil, email, addr) VALUES ('$filename', '$md5', '$sha1', '$sha256', '69', '$queue', '$username', '$size', '$data',  '$time', '$saveuntil', '$toemail', '$addr')");
		$query = mysql_query("INSERT INTO queue (filename, md5, sha1, sha256, complete, queue, username, filesize, filedata, added, keepuntil, email, addr, asquared, avira, bitdefender, esafe, eset, fprot, ikarus, kav, mcafee, norton, panda, quickheal, solo, sophos, vba32, virusbuster) VALUES ('$filename', '$md5', '$sha1', '$sha256', '69', '$queue', '$username', '$size', '$data',  '$time', '$saveuntil', '$toemail', '$addr', '$asquared', '$avira', '$bitdefender', '$esafe', '$eset', '$fprot', '$ikarus', '$kaspersky', '$mcafee', '$norton', '$panda', '$quickheal', '$solo', '$sophos', '$vba32', '$virusbuster')");
		$ourid = obf(mysql_insert_id());
		
		Header("Location: index.php?p=view&id=$ourid");
	}
}

function GetFilesFromArchive($arcdata, $ext)
{
	$dir = "C:/xampp/htdocs/new/arc/" . rand_str();
	mkdir($dir);
	$arc = $dir."/arc".$ext;
	file_put_contents($arc, $arcdata);
	exec("izarce.exe -p" . $dir . " -e " . $arc, $out);

	unlink($arc);
	$list = Array();
	if ($handle = opendir($dir)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if($file == "." || $file == "..") //skip 
				continue; 
			
			$finfo = Array();
			$finfo['name'] = $file;
			$finfo['data'] = file_get_contents($dir . "/" . $file);
			$list[] = $finfo;
		}
		closedir($handle);
	}
	return $list;
}

function rand_str()
{
	$set = "abcdefghijklmnopqrstuvwxyz1234567890";
	$foo = "";
	for($i = 0; $i < 8; $i++)
	{
		$foo .= $set[mt_rand() % strlen($set)];
	}
	return $foo;
}

function DoScan($avname)
{
	global $docount;
	$docount++;
	if($_POST[$avname] === "true")
	{
		return "";
	}
	else
	{
		return "DONTDO";
	}
}
?>
