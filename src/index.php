<?php
/*==============================================================================

		OSSBOX's Secure & Lightweight CMS in PHP for Linux.

	This code is hereby placed into the public domain by its author 
	FireXware. It may be freely used for any purpose whatsoever.

                      PUBLIC DOMAIN CONTRIBUTION NOTICE							 
   This work has been explicitly placed into the Public Domain for the
	benefit of anyone who may find it useful for any purpose whatsoever.

	This CMS is heavily dependant upon GRC's Script-Free Menuing System:
		        http://www.grc.com/menudemo.htm
	
==============================================================================*/


if($_SERVER["HTTPS"] != "on") {
   header("HTTP/1.1 301 Moved Permanently");
   header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
   exit();
}

require_once('libs/phpcount.php');

//Strengthen the server's CSPRNG
$entropy = implode(gettimeofday()) . implode($_SERVER) . implode($_GET) . implode($_POST) . implode($_COOKIE) . implode($_ENV) . microtime() . mt_rand() . mt_rand();
file_put_contents("/dev/random", $entropy);

//Connect to MySQL
//TODO: change this information.
//$username="ossbox";
//$password="Nw552SfbbZp";
//$database="cracky_ids";
//mysql_connect("localhost",$username,$password);
//@mysql_select_db($database) or die( "Unable to select database");
//if(md5($_SERVER['REMOTE_ADDR']) != "a99e050496bd36a9327ab92dbcaac4f7")
//{
//	mysql_query("UPDATE hits SET hitcount=(hitcount + 1)");

//	/*$ip = sqlsani($_SERVER['REMOTE_ADDR']);
//	$url = sqlsani($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//	$time = time();
//	$agent = sqlsani($_SERVER['HTTP_USER_AGENT']);
//	mysql_query("INSERT INTO ids (time, url, ip, agent) VALUES('$time', '$url', '$ip', '$agent')");*/
//}

//Default title of the page (shown on the top of the window and on top of tabs)
$TITLE = "OSSBox Security - Software Development &amp; Security Reviews";

//Default meta info (for search engines)
$META_DESCRIPTION = "OSSBox Security - Home to the PDF Exploit remover and Encrypted Anonymous Private Pastebin!";
$META_KEYWORDS = "ossbox, security, encryption, privacy, programming, code, research";


//name variable will contain the name of the page
$name = "";

//urls can be in any of the following forms:
// http://example.com/?page=test
// http://example.com/index.php?page=test
// http://example.com/test
// http://example.com/text.htm (all other forms will get directed to this form)
// http://example.com/index.php will be redirected to http://example.com/

if($_SERVER['HTTP_HOST'] != "ossbox.com" && 
	$_SERVER['HTTP_HOST'] != "localhost" && 
	$_SERVER['HTTP_HOST'] != "192.168.1.102" && 
	!strpos($_SERVER['REQUEST_URI'], "://ossbox.com/"))
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://ossbox.com" . RemoveDomain($_SERVER['REQUEST_URI']));
	die();
}

//grab the name of the page the user wants from a URL variable
if(isset($_GET['page']))
{
	$name = $_GET['page'];
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /$name.htm");
	die();
}
elseif (RemoveDomain($_SERVER['REQUEST_URI']) != "/index.php")
{
	$name = substr(RemoveDomain($_SERVER['REQUEST_URI']), 1);
	if(strpos($name, "?") !== false)
	{
		$name = substr($name, 0, strpos($name, "?"));
	}
	if($name != "" && strpos($name, ".htm") === false)
	{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /$name.htm");
		die();
	}
	$name = str_replace(".htm", "", $name);
}
elseif(RemoveDomain($_SERVER['REQUEST_URI']) == "/index.php")
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /");
	die();
}

function RemoveDomain($url)
{
	if($colon = strpos($url, "://"))
	{
		$thirdSlash = strpos($url, "/", $colon + 3);
		$url = substr($url, $thirdSlash);
	}
	return $url;
}

//folder where the pages are kept (relative to this script)
$root = "pages/";

//used by scripts in other folders that call index.php
if(isset($dirmod))
	$root = $dirmod . $root;

//default path if no page is specified (aka home)
$path = "home.html";

//holds the identification string for the comments (so each page can have their own set of comments)
//empty string to not show comments on the page
$commentid = 0;

$CID_ASUSKB = 1;

//this is our firewall, we use a switch to turn the name of a page into the path where the .html content is located
//this protects us against RFI and LFI
switch($name)
{
	//Map each page name to the name of the file within the $root folder.
	//Also, set $commentid for each page, leave it as "" to disable comments for that page
	//Two pages may use the same commentid, they will show the same set of comments.
	case "":
		$name = "home";
		$path = "home.html";
		break;
	case "home":
		$path = "home.html";
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /");
		die();
		break;
	case "about":
		$path = "about.html";
		$META_DESCRIPTION = "About OSSBox.";
		break;
	case "donated":
		$path = "donated.html";
		break;
	case "resume":
		$path = "resume.html";
		$TITLE = "My Resume - OSSBox";
		$META_DESCRIPTION = "OSSBox Resume";
		break;
	case "blowfish":
		$path = "projects/blowfish.html";
		$TITLE = "Blowfish C# and C++ Source code - OSSBox";
		$META_DESCRIPTION = "C# and C++ implementation of the BLOWFISH block cipher.";
		break;
	case "wordlists":
		$path = "projects/wordlists.html";
		$TITLE = "Password Cracking Wordlists - OSSBox";
		$META_DESCRIPTION = "The best password cracking wordlists and dictionaries on the internet.";
		$META_KEYWORDS = "password cracking, word list, wordlist, dictionary, md5, hash cracking";
		break;
	case "projects":
		$path = "projects/projects.html";
		$TITLE = "OSSBox Projects";
		$META_DESCRIPTION = "All of OSSBox's past, current, and future projects.";
		break;
	case "php-hit-counter":
		$path = "projects/php-hit-counter.html";
		$TITLE = "PHP &amp; MySQL Unique Hit Count Tracker - OSSBox";
		$META_DESCRIPTION = "An unique hit counter for PHP. Tracks unique hits without storing the IP address.";
		$META_KEYWORDS = "hit counter, php, secure, private, anonymous, unique hits, track";
		break;
	case "helloworld-cms":
		$path = "software/helloworld-cms.html";
		$TITLE = "Secure and Light PHP CMS - OSSBox";
		$META_DESCRIPTION = "A lightweight open source PHP CMS designed for security";
		$META_KEYWORDS = "secure, light, php, template, backend, cms";
		break;
	case "passwordbolt":
		$path = "software/passwordbolt.html";
		$TITLE = "Password Bolt Password Manager - OSSBox";
		$META_DESCRIPTION = "Password Bolt, an extremely web based secure password manager.";
		$META_KEYWORDS = "passwordbolt, password, password manager, online password manager, web password manager, open source";
		break;
	case "bitcannon":
		$path = "software/bitcannon.html";
		$TITLE = "BitCannon - The cross-platform, fast, secure, encrypted internet file transfer program for Windows, Macintosh, and Linux - OSSBox";
		$META_DESCRIPTION = "Easily and quickly transfer large files over a direct encrypted connection.";
		$META_KEYWORDS = "encrypted file transfer, fast file transfer, secure file transfer, windows, mac, linux";
		break;
	case "bitcannon-cryptography":
		$path = "software/bitcannoncrypto.html";
		$TITLE = "BitCannon - Cryptographic Protocol Description - OSSBox";
		$META_DESCRIPTION = "Detailed description of the encryption technology behind the BitCannon file transfer program.";
		$META_KEYWORDS = "secure, encryption, cryptography, file transfer";
		break;
	case "softwaredevelopment":
		$path = "services/softwaredevelopment.html";
		$TITLE = "Custom Software Development - OSSBox";
		$META_DESCRIPTION = "Custom secure software development to suit your needs.";
		$META_KEYWORDS = "software development, custom software, programming, security";
		break;
	case "webdevelopment":
		$path = "services/webdevelopment.html";
		$TITLE = "Custom Web Design and Development - OSSBox";
		$META_DESCRIPTION = "Custom web software development.";
		break;
	case "services":
		$path = "services/services.html";
		$TITLE = "OSSBox Services";
		$META_DESCRIPTION = "OSSBox Services.";
		break;
	case "peerreview":
		$path = "services/peerreview.html";
		$TITLE = "Peer Review and Security Testing Service - OSSBox";
		$META_DESCRIPTION = "Free peer review and security testing service.";
		$META_KEYWORDS = "security, peer review, testing, software security";
		break;
	case "trent":
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /trustedthirdparty.htm");
		die();
		break;
	case "trustedthirdparty":
		$path = "services/trustedthirdparty.php";
		$TITLE = "TRENT - FREE Third party Drawing Service - OSSBox";
		$META_DESCRIPTION = "TRENT, the trusted random number generator for contests and drawings.";
		$META_KEYWORDS = "contest drawing, third party, trusted, lottory, trent, random number generator";
		break;
	case "trentsource":
		$path = "services/trentsource.html";
		$TITLE = "TRENT Peer Review Source Code - OSSBox";
		$META_DESCRIPTION = "The complete source code for OSSBox's TRENT service. Made available for peer review.";
		$META_KEYWORDS = "contest drawing, third party, trusted, lottory, trent, random number generator";
		break;
	case "contact":
		$path = "contact.html";
		$TITLE = "OSSBox Contact Information";
		$META_DESCRIPTION = "OSSBox contact informaion.";
		break;
	case "theoretical-attack-on-bitcoin":
		$path = "research/theoretical-attack-on-bitcoin.html";
		$TITLE = "Economic DDoS - A Theoretical Attack on Bitcoin - OSSBox";
		$META_DESCRIPTION = "State-sponsored attacks could secretly destroy the bitcoin economy.";
		break;
	case "choosing-good-passwords-longer-is-better":
		$path = "research/choosing-good-passwords-longer-is-better.html";
		$TITLE = "How to Pick a Good Password - Longer is Better - OSSBox";
		$META_DESCRIPTION = "The length of passwords is far more important than their character set or randomness.";
		break;
	case "bitcoin-pool-ddos":
		$path = "research/bitcoin-pool-ddos.html";
		$TITLE = "BitCoin Centralization - DDoS Attacks on Pools & Mt. Gox Hacked - OSSBox";
		$META_DESCRIPTION = "Centralization is harmful to the BitCoin network and community.";
		$META_KEYWORDS = "bitcoin, ddos, denial of service, pool, mining, mtgox, centralization, hacked";
		break;
	case "ssl-fundamental-flaw-fix":
		$path = "research/ssl-fundamental-flaw-fix.html";
		$TITLE = "Fixing the Flaw in the SSL Certificate Authority Architecture - OSSBox";
		$META_DESCRIPTION = "Dangers of the Internet, an explanation of the dangers you face online.";
		break;
	case "internetdangers":
		$path = "research/internetdangers.html";
		$TITLE = "Dangers of the Internet - OSSBox";
		$META_DESCRIPTION = "Dangers of the Internet, an explanation of the dangers you face online.";
		break;
	case "softwaresecurity":
		$path = "research/softwaresecurity.html";
		$TITLE = "Software Security - Bypassing KeyScrambler and Avira - OSSBox";
		$META_DESCRIPTION = "The fundamental flaw in the software security model. Bypassing KeyScrambler and Avira";
		$META_KEYWORDS = "software security, antivirus, keyscrambler, antivir";
		break;
	case "web-application-security":
		$path = "research/web-application-security.html";
		$TITLE = "Web Application Security - OSSBox";
		$META_DESCRIPTION = "Why are websites so insecure? What design patterns will help solve these problems?";
		$META_KEYWORDS = "web application security, cross site scripting, sql injection, remote code execution, php, asp, scripting";
		break;
	case "research":
		$path = "research/research.html";
		$TITLE = "OSSBox Research";
		$META_DESCRIPTION = "Research projects by OSSBox";
		break;
	case "cryptographyunderattack":
		$path = "research/cryptographyunderattack.html";
		$TITLE = "Cryptography Under Attack - OSSBox";
		$META_DESCRIPTION = "Cryptography under attack essay.";
		break;
	case "passwordinsecurity":
		$path = "research/passwordinsecurity.html";
		$TITLE = "Are Passwords Secure? - OSSBox";
		$META_DESCRIPTION = "Finding out if passwords are right way to be authenticating users.";
		break;
	case "keyboarddefect":
	case "asuskeyboarddefect":
		$path = "research/asuskeyboarddefect.html";
		$TITLE = "ASUS G50 G51 Keyboard Problem: Backspace, P, and 1 keys don't work. - OSSBox";
		$META_DESCRIPTION = "Solution to the keyboard problem for the ASUS G50, G51, and G51VX series laptops.";
		$META_KEYWORDS = "asus keyboard, g series, g51, g50, g51vx, backspace, p, q, keys, broken";
		$commentid = $CID_ASUSKB;
		break;
	case "passgen":
		$path = "software/passgen.html";
		$TITLE = "Secure Windows &amp; Linux Password Generator - OSSBox";
		$META_DESCRIPTION = "A secure random password generator for Windows,  Linux and Macintosh. Generates ASCII and HEX.";
		$META_KEYWORDS = "password generator, secure, encryption, windows, linux, macintosh";
		break;
	case "software":
		$path = "software/software.html";
		$TITLE = "OSSBox Software";
		$META_DESCRIPTION = "Software created by OSSBox";
		break;
	case "winrrng":
		$path = "software/winrrng.html";
		$TITLE = "Real Random Number Generator for Windows - OSSBox";
		$META_DESCRIPTION = "A real random number generator for Windows";
		break;
	case "textractor":
		$path = "software/textractor.html";
		$TITLE = "Textractor - A tool for extracting words from large binary files - OSSBox";
		$META_DESCRIPTION = "A Windows and Linux tool for extracting words or sequences of alphabetical characters from large binary files";
		$META_KEYWORDS = "extract, words, wordlist creator, extract words";
		break;
	case "dotnetbenchmark":
		$path = "software/dotnetbenchmark.html";
		$TITLE = "Benchmark Tool for the .NET Framework - OSSBox";
		$META_DESCRIPTION = "A benchmark tool for the .NET Framework";
		$META_KEYWORDS = "dot net framework, benchmark";
		break;
	case "eotp":
		$path = "research/eotp.html";
		$TITLE = "Encrypting One Time Passwords System - OSSBox";
		$META_DESCRIPTION = "A One Time Password protocol that can be used with encryption.";
		$META_KEYWORDS = "encrypting one time passwords, static key, one time password";
		break;
	case "meetbadguys":
		$path = "research/doi/meetbadguys.html";
		$TITLE = "Dangers of the Internet - Meet the Bad Guys";
		break;
	case "spam":
		$path = "research/doi/spam.html";
		$TITLE = "Dangers of the Internet - Scams and Phishing";
		$META_DESCRIPTION = "An explanation of spam, phishing, and social engineering attacks.";
		break;
	case "passwordsecurity":
		$path = "research/doi/passwordsecurity.html";
		$TITLE = "Dangers of the Internet - Password Security";
		$META_DESCRIPTION = "An explanation of why its important to use strong and different passwords for each website you use.";
		break;
	case "malware":
		$path = "research/doi/malware.html";
		$TITLE = "Dangers of the Internet - Viruses, Worms, and Exploits - Rooting Your Box";
		$META_DESCRIPTION = "An explanation of what viruses, worms, and exploits are.";
		break;
	case "privacy":
		$path = "research/doi/privacy.html";
		$TITLE = "Dangers of the Internet - Privacy and Tracking";
		$META_DESCRIPTION = "A guide to protecting your privacy on the internet.";
		break;
	case "networks":
		$path = "research/doi/networks.html";
		$TITLE = "Dangers of the Internet - Wireless Network Security";
		$META_DESCRIPTION = "How to be safe when using open wifi, and how to secure your wireless network at home.";
		break;
	case "todo":
		$path = "research/doi/todo.html";
		$TITLE = "Dangers of the Internet - Security Checklist";
		$META_DESCRIPTION = "A brief guide to keeping yourself safe on the internet.";
		break;
	case "securitytest":
		$path = "research/doi/securitytest.html";
		$TITLE = "Dangers of the Internet - Security Knowledge Test";
		$META_DESCRIPTION = "Security Knowledge Test";
		break;
	case "cbcmodeiv":
		$path = "research/cbcmodeiv.html";
		$TITLE = "Should CBC Mode Initialization Vector Be Secret - OSSBox";
		$META_DESCRIPTION = "Should the initialization vector used for CBC mode be kept secret?";
		$META_KEYWORDS = "cbc mode, encryption, initialization vector, iv, secret, secure";
		break;
	case "password-policy-statistics":
		$path = "research/password-policy-statistics.php";
		$TITLE = "Password Policy Statistics - OSSBox";
		$META_DESCRIPTION = "Password length and character restriction statistics. Top 100 sites.";
		$META_KEYWORDS = "password, statistics, restrictions, maximum length, symbols, plain text, hashing, salt";
		break;
	case "password-policy-hall-of-shame":
		$path = "research/hallofshame.php";
		$TITLE = "Password Policy Hall of SHAME - OSSBox";
		$META_DESCRIPTION = "List of websites and services that impose password restrictions and may be storing passwords in plaintext.";
		$META_KEYWORDS = "hall of shame, password hall of shame, plaintext, password restrictions, maximum password length, restriction, insecure";
		break;
	case "gpucrack":
		$path = "projects/gpucrack.html";
		$TITLE = "CUDA Salted MD5 GPU Cracker Source Code - OSSBox";
		$META_DESCRIPTION = "Source code for a salted MD5 hash cracker using nvidia graphics cards (CUDA).";
		$META_KEYWORDS = "salted md5, hash cracking, gpu, graphics cards, nvidia, cuda";
		break;
	case "waterfall":
		$path = "projects/waterfall.html";
		$TITLE = "Waterfall Password Cracking Algorithm - OSSBox";
		$META_DESCRIPTION = "The Waterfall password cracking algorithm with implementation and source code.";
		$META_KEYWORDS = "password cracking, memorable passwords, keyspace reduction, fast dictionary attack";
		break;
	case "csharpthreadlibrary":
		$path = "projects/csharpthreadlibrary.html";
		$TITLE = "PolyThread - C# Thread Library - OSSBox";
		$META_DESCRIPTION = "Source code for a job-based multi-threading library for the .NET framework. Written in C#.";
		$META_KEYWORDS = "multi-threading, threads, c#, library, job based, parallel";
		break;
	case "simppsk":
		$path = "projects/simppsk.html";
		$TITLE = "SimpPSK - A simple pre-shared key authentication and session key exchange implementation - OSSBox";
		$META_KEYWORDS = "pre-shared key, key exchange, library, c++, c plus plus";
		break;
	case "checksums":
		$path = "services/checksums.php";
		$TITLE = "Online Text and File Hash Calculator - MD5, SHA1, SHA256, SHA512, WHIRLPOOL Hash Calculator - OSSBox";
		$META_DESCRIPTION = "Online Hash Tool. Calculate hash of file or text. MD5, SHA1, SHA256, SHA512 and more...";
		$META_KEYWORDS = "file hasher, online, hash, md5, sha256, sha1, text hash, checksum";
		break;
	case "html-sanitize":
		$path = "services/html-sanitize.php";
		$TITLE = "Online HTML Sanitizer Tool - htmlspecialchars - OSSBox";
		$META_DESCRIPTION = "Convert text containing special characters into proper HTML.";
		$META_KEYWORDS = "html sanitizer, htmlspecialchars, htmlencode";
		break;
	case "pdfcleaner":
		$path = "services/pdfcleaner.php";
		$TITLE = "PDF Exploit Sanitizer and Cleaner - Remove exploits from PDF files - OSSBox";
		$META_DESCRIPTION = "PDF Exploit Remover. Remove ANY exploit from a PDF files. Even unknown exploits.";
		$META_KEYWORDS = "pdf exploit, exploit remover, unknown exploit, sanitizer";
		break;
	case "tics":
		$path = "research/tics.html";
		$TITLE = "TICS - Time Intensive Computer Solvable CAPTCHA Alternative - OSSBox";
		break;
	case "browserui":
		$path = "research/browserui.html";
		$TITLE = "Redesigning the Web Browser GUI - OSSBox";
		$META_DESCRIPTION = "The web browser needs a redesign..";
		break;
	case "flashsupercomputer":
		$path = "research/flashsupercomputer.html";
		$TITLE = "Building a Distributed Supercomputer with Flash - OSSBox";
		$META_DESCRIPTION = "Easy, super-scalable supercomputing.";
		break;
	case "stopspying":
		$path = "research/stopspying.php";
		$TITLE = "Stop Spying! Bills C-50, C-51, C-52. Warrantless Wiretaps in Canada.";
		$META_DESCRIPTION = "canada, warrant-less wiretap, isp, c51, c52, c50, spying, law, bill, internet privacy";
		break;
	case "filesystem-events-ntfs-permissions":
		$path = "research/filesystemevents.html";
		$TITLE = "File System Events Disclose NTFS Protected Folder Contents - OSSBox";
		$META_DESCRIPTION = "Obtain list of files in folder protected with NTFS permissions via filesystem events";
		break;
	case "pastebin":
		$path = "services/pastebin.html";
		$TITLE = "Encrypted Pastebin - Keep your data private and secure! - OSSBox";
		$META_DESCRIPTION = "An Encrypted, Anonymous, Secure, and PRIVATE Pastebin. Send large amounts of text to your friends without it being leaked onto the internet!";
		$META_KEYWORDS = "private pastebin, encrypted pastebin, secure pastebin, anonymous pastebin, privacy";
		break;
	case "onedetection":
		$path = "research/onedetection.html";
		$TITLE = "The PUP Confusion Antivirus Detection Evasion Technique - OSSBox";
		$META_DESCRIPTION = "The PUP Confusion Antivirus Detection Evasion Technique. Multiple detections per file.";
		$META_KEYWORDS = "antivirus, single detection, only one detection, can't detect more than one, multiple virus, two viruses in one file";
		break;
	case "salted-password-hashing-doing-it-properly":
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://crackstation.net/hashing-security.html");
		die();
		/*$path = "research/salted-password-hashing-doing-it-properly.html";
		$TITLE = "Secure Password Hashing - How to do it Properly - OSSBox";
		$META_DESCRIPTION = "How to hash hasswords with salt properly. For use in secure web based loging systems.";
		$META_KEYWORDS = "password hashing, storing passwords, how to hash, salt, web development, md5, sha1, sha256, crypto, cryptography, php, mysql, database";*/
		break;
	case "scratch":
		$path = "scratch.html";
		$TITLE = "";
		$META_DESCRIPTION = "";
		break;
	case "r&d/bitcannon-experimental":
		$path = "rd/bitcannon-exp.html";
		$TITLE = "";
		$META_DESCRIPTION = "";
		break;
	case "privacy-policy":
		$path = "privacy.html";
		$TITLE = "";
		$META_DESCRIPTION = "";
		break;
	case "notfound":
		$name = ""; // destroy any possibly bad user input asap.
		$commentid = "";
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		$path = "404.html";
		break;
	default: //page name wasn't valid. 404 and exit
		$name = ""; // destroy any possibly bad user input asap.
		$commentid = "";
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		$path = "404.html";
		break;
}

//combine the folder and the name of the file within the folder to create the full name
$fullpath = $root . $path;


//handles when the user adds a comment

if(isset($_POST['submit']) && !empty($commentid))
{
	$commentname = sqlsani(smartslashes($_POST['name']));
	if(empty($commentname))
		$commentname = "Anonymous";
	$comment = sqlsani(smartslashes($_POST['comment']));
	mysql_query("INSERT INTO comments (name, comment, commentid) VALUES('$commentname', '$comment', '$commentid')");
}

//-----SECURITY AND HELPER FUNCTIONS----//

//XSS sanitize, makes sure $data can be printed on the screen without any chance of XSS
//returns sanitized string
function xsssani($data)
{
	$data = htmlspecialchars($data, ENT_QUOTES);
	$data = str_replace("\r\n", "<br />", $data);
	$data = str_replace("\n", "<br />", $data);
	$data = str_replace("\r", "<br />", $data);
	return $data;
}

//SQL sanitize, makes sure that $data is safe to use in a mysql query
//returns the sanitized string
function sqlsani($data)
{
	return mysql_real_escape_string($data);
}

//shows the comments for a comment id
//uses the xsssani function to ensure that there are no XSS vulnerabilities
function showcomments($id)
{
	$id = sqlsani($id);
	$comments = mysql_query("SELECT * FROM comments WHERE commentid='$id'");
	if(mysql_num_rows($comments) > 0)
	{
			echo '<h2>Comments</h2>';
			while($c = mysql_fetch_array($comments))
			{
				echo '<div style="background-color:black;color:white; width:600px;"><h4>' . xsssani($c['name']) . ' says:</h4>' . xsssani($c['comment']) . '</div>';
			}
	}
}

//checks whether smart slashes are enabled and removes them if they are
function smartslashes($data)
{
	if(get_magic_quotes_gpc())
	{
		return stripslashes($data);
	}
	else
	{
		return $data;
	}
}

//Finally display the page:
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title><?php echo $TITLE; ?></title>
<meta name="description" content="<?php echo $META_DESCRIPTION; ?>" />
<meta name="keywords" content="<?php echo $META_KEYWORDS; ?>" />
<meta name="google-site-verification" content="b6rfOTW9eLm4PyU1LMdom1rUg1LUl-Df448sKjUEn74" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" media="all" type="text/css" href="/mainmenu2.css" />
<link rel="stylesheet" media="all" type="text/css" href="/main.css" />

</head>
<body <?php if( $name == "home" ) echo 'style="background:black;"'; ?> >

<!-- This menuing system was made by Steve Gibson at GRC.COM 
			see more at http://www.grc.com/menudemo.htm -->

<div class="menuminwidth0"><div class="menuminwidth1"><div class="menuminwidth2">
<div id="masthead">

	<!--[if !IE]>-->
	<!--<a href="/"><img id="mastheadlogo" src="/images/ossbox-header2.png"  alt="OSSBox Security" title="OSSBox Security Homepage" /></a>-->
	<a href="/"><img id="mastheadlogo" src="/images/ossbox_security.png"  alt="OSSBox Security" title="OSSBox Security Homepage" /></a>
	<!--<![endif]-->
	
	<!--[If lte IE 6]>
	<a href="/"><img id="mastheadlogo" src="/images/ossbox_security.png"  alt="OSSBox Security" title="OSSBox Security Homepage" style="border: solid black 3px;"/></a>
	<![endif]-->

	<!--[If gt IE 6]>
	<a href="/"><img id="mastheadlogo" src="/images/ossbox_security.png"  alt="OSSBox Security" title="OSSBox Security Homepage" /></a>
	<![endif]-->

	
</div>

<div class="menu">

<ul>
	<li class="headerlink" ><a href="/">Home&nbsp;<span class="darr">&#9660;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul class="leftbutton">
			<li><a href="/about.htm">&nbsp;About OSSBox</a></li>
			<li><a href="/resume.htm">&nbsp;My R&eacute;sum&eacute;</a></li>
			<li><a href="/projects.htm">&nbsp;Project List</a></li>
			<li><a href="http://blog.ossbox.com" >&nbsp;Blog</a></li>
			<li><a href="/contact.htm">&nbsp;Contact</a></li>
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="/peerreview.htm">Services&nbsp;<span class="darr">&#9660;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<!--<li><a href="/softwaredevelopment.htm">&nbsp;Software Development</a></li>-->
			<!--<li><a href="/webdevelopment">&nbsp;Web Development</a></li>-->
			<li><a href="/peerreview.htm">&nbsp;Peer Review &amp; Testing</a></li>

			
			<li style="border-top:solid 1px white;"><a href="/pastebin.htm">&nbsp;Encrypted Pastebin</a></li>
			<li><a href="/trustedthirdparty.htm">&nbsp;TRENT - Trusted RNG</a></li>
			<li><a href="/pdfcleaner.htm">&nbsp;PDFCleaner</a></li>

			<li><a href="#"><span class="drop"><span>Utilities</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
				<ul>
					<li><a href="/checksums.htm">&nbsp;Checksum Calculator</a></li>
					<li><a href="/html-sanitize.htm">&nbsp;HTML Sanitizer</a></li>
				</ul>
				<!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
			<li><a href="http://crackstation.net/" style="color:#cc0000;">&nbsp;CrackStation.net</a></li>

		</ul>
	<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="/projects.htm">Software&nbsp;<span class="darr">&#9660;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="/passgen.htm">&nbsp;Password Generator</a></li>
			<li><a href="/helloworld-cms.htm">&nbsp;HelloWorld! Secure CMS</a></li>
			<li><a href="/textractor.htm">&nbsp;Textractor</a></li>
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="/projects.htm">Projects &amp; Code&nbsp;<span class="darr">&#9660;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="/gpucrack.htm">&nbsp;GPU MD5+Salt Cracker</a></li>
			<li><a href="/php-hit-counter.htm">&nbsp;PHP Hit Counter</a></li>
			<li><a href="/csharpthreadlibrary.htm">&nbsp;PolyThread</a></li>
			<li><a href="/passwordbolt.htm">&nbsp;Password Bolt [BETA]</a></li>
			<li><a href="/simppsk.htm">&nbsp;SimpPSK</a></li>
			<li><a href="/blowfish.htm">&nbsp;Blowfish C#/C++</a></li>
			<!--<li><a href="/blowfish.htm">&nbsp;BLOWFISH C# &amp; C++</a></li>-->
			
			<!--<li><a href="wordlists.htm">&nbsp;WordLists</a></li>-->
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="/projects.htm">Research&nbsp;<span class="darr">&#9660;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="#"><span class="drop"><span>Security</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
				<ul>
					<li><a href="/password-policy-hall-of-shame.htm">&nbsp;Password Policy HoS</a></li>
					<li><a href="/bitcoin-pool-ddos.htm">&nbsp;BitCoin Centralization</a></li>
					<li><a href="/web-application-security.htm">&nbsp;Website Security</a></li>		
					<li><a href="/filesystem-events-ntfs-permissions.htm">&nbsp;File-System Events</a></li>
					<li><a href="/onedetection.htm">&nbsp;PUP Confusion</a></li>
					<li><a href="/softwaresecurity.htm">&nbsp;Software Security</a></li>
				</ul>

				<!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
			<!--put internet dangers HERE-->	
			<!--<li><a href="/bitcoin-pool-ddos.htm">&nbsp;BitCoin Pool DDoS</a></li>
			<li><a href="/web-application-security.htm">&nbsp;Website Security</a></li>		
			<li><a href="/filesystem-events-ntfs-permissions.htm">&nbsp;File-System Events</a></li>
			<li><a href="/onedetection.htm">&nbsp;PUP Confusion</a></li>
			<li><a href="/softwaresecurity.htm">&nbsp;Software Security</a></li>
			<li><a href="/asuskeyboarddefect.htm">&nbsp;ASUS G51 Keyboard</a></li>-->
			<li><a href="#"><span class="drop"><span>Crypto</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
				<ul>
					<li><a href="/eotp.htm">&nbsp;EOTP</a></li>
					<li><a href="/cbcmodeiv.htm">&nbsp;CBC Mode IV Security</a></li>
					<li><a href="http://crackstation.net/hashing-security.html" style="color:#cc0000;">&nbsp;Salted Password Hashing</a></li>
				</ul>

				<!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
			
			<li><a href="/asuskeyboarddefect.htm">&nbsp;ASUS G51 Keyboard</a></li>
			<li><a href="#"><span class="drop"><span>Future</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
				<ul>
					<li><a href="#">&nbsp;IE Image Privacy Vuln.</a></li>
					<li><a href="#">&nbsp;Crypto for Web Developers</a></li>
					<li><a href="/tics.htm">&nbsp;TICS</a></li>
					<li><a href="/internetdangers.htm">&nbsp;Internet Security Basics</a></li>
					<li><a href="#">&nbsp;Cortical Cryptanalysis</a></li>
				</ul>

				<!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
			
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>


</div> <!-- close "menu" div -->
<hr style="display:none" />
</div></div></div> <!-- close the "minwidth" wrappers -->
<!-- End of menu -->

<!--[if !IE]>-->
<div id="undergrad"></div>
<!--<![endif]-->

<!--[If gt IE 6]>
<div id="undergrad"></div>
<![endif]-->

<?php
	$dnt = isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == "1";
	
	if(!$dnt)
	{
		PHPCount::AddHit($fullpath, $_SERVER['REMOTE_ADDR']);
	}
	
	if($name != "home")
		echo '<div id="content" >';
	else
		echo '<div id="contenthome" >';
	//TODO: Above and below this php section, put the html design code.

	//displays the page
	//not vulnerable to LFI or RFI, as all of filepath came from constant strings hard-coded into this file.
	if(file_exists($fullpath))
	{
		include($fullpath);
	}
	echo "";
	//show the previously posted comments and a box to add new comments if comments are enabled
	if($commentid != 0)
	{
		//show the previously posted comments if there are any
		/*showcomments($commentid);
		echo '<h2>Add a Comment</h2><form action="' . xsssani($name) . '" method="post">
			Name: <br /><input type="text" name="name" maxlength="30" /><br />
			Comment: <br />
			<textarea cols="80" rows="10" name="comment"></textarea><br />
			<input type="submit" name="submit" value="Submit" />
		</form>';*/
	}


?>

</div>
<?php
	if($name != "home")
	{
		?>
		<div id="footwrap">	<div id="footer">
		<?
		date_default_timezone_set("Canada/Mountain");
		$last_modified = htmlspecialchars(date("F j, Y, g:ia",filemtime($fullpath)), ENT_QUOTES);
		$unique =  PHPCount::GetHits($fullpath, true);
		$hits = PHPCount::GetHits($fullpath);
		$total = PHPCount::GetTotalHits();
		$totalu = PHPCount::GetTotalHits(true);

		echo "Page Hits: $hits &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Unique Hits: $unique<br />";
		echo "Last Change: $last_modified<br />";
		if($dnt)
		{
			echo "<span style=\"color:#00FF00;\">You have the DNT header enabled.</span><br />";
		}

		?>
			<span style="color:#bbbbbb;">Copyright &copy; 2011 OSSBOX.COM.</span>
			</div></div>
		<?
	}
?>
</body>
</html>
