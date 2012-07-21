<?php
/*
 * This file is part of Defuse Cyber-Security's Secure Pastebin
 * Find updates at: https://defuse.ca/pastebin.htm
 * Developer contact: havoc AT defuse.ca
 * This code is in the public domain. There is no warranty.
 */

require_once('pastebin.php');

// Never show a post over an insecure connection
if($_SERVER["HTTPS"] != "on") {
   header("HTTP/1.1 301 Moved Permanently");
   header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
   die();
}

delete_expired_posts();

//Disable caching of viewed posts:
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Mon, 01 Jan 1990 00:00:00 GMT"); 

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Defuse Cyber-Security's Encrypted Pastebin</title>
    <style type="text/css">
    body {
        background-color: #e7e7e7; 
        color:black;
        padding: 0;
        margin: 0;
        font-family: verdana, tahoma, arial, helvetica, sans-serif, "MS Sans Serif";
        font-size: 10pt;
    }
    
    .codebox {
        font-family:monospace; 
        background-color: #e7e7e7; 
        /*border: solid black 1px;*/
        padding-left: 20px;
        clear:both;
    }
    
    textarea {
        width:100%;
        height: 200px;
        background-color: white;
        color:black;
        border:solid black 1px;
        font-family: monospace;
        resize: none;
    }
    
    .div0 {
        font-family: monospace;
        background-color: #e7e7e7;
        margin-right: 10px;
    }
    /* every second row of text is shaded */
    .div1 {
        background-color: #FFFFFF;
        font-family: monospace;
        margin-right: 10px;
    }
    
    #timeleft {
        font-weight: bold;
        padding-bottom: 10px;
    }
    
    #header {
        margin: 0;
        padding: 10px;
        font-size: 15pt;
        float: left;
    }
    
    #header a {
        color: black;
        text-decoration: none;
    }
    
    #header a:visited {
        color: black;
    }
    
    #header a:hover {
        text-decoration: underline;
    }
    
    #timeleft {
        padding: 10px;
        text-align: right;
        color: #404040;
    }
    
    #pasteform {
        padding-left: 10px;
        padding-right: 10px;
    }

    #encinfo {
        padding-left: 10px;
        padding-top: 5px;
        padding-bottom: 20px;
    }

    #passwordprompt {
        padding-left: 10px;
        font-weight: bold;
        margin-bottom: 10px;
        clear: both;
    }
    
    h2 {
        font-size: 15pt;
    }

    #sorry {
        clear: both;
        text-align: center;
    }
    </style>
</head>
<body>
<!-- Scripts required for client-side decryption -->
<script type="text/javascript" src="https://defuse.ca/js/cryptoHelpers.js" ></script>
<script type="text/javascript" src="https://defuse.ca/js/jsHash.js" ></script>
<script type="text/javascript" src="https://defuse.ca/js/aes.js" ></script>
<script type="text/javascript" src="https://defuse.ca/js/firexware.js" ></script>
<script type="text/javascript">
<!--
function encrypt()
{
	var pass1 = document.getElementById("pass1").value;
	var pass2 = document.getElementById("pass2").value;
	if(pass1 == pass2 && pass1 != "")
	{
		var iv = "<?php echo bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); ?>";
		var salt = "<?php echo bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); ?>";
		var plain = document.getElementById("paste").value;
		var ct = fxw.encrypt(pass1, salt, iv, plain);
		document.getElementById("paste").value = ct;
		document.getElementById("jscrypt").value = "yes";
		document.pasteform.submit();
	}
	else if(pass1 != pass2)
	{
		alert("Your passwords do not match! Please try again.");
	}
	else if(pass1 == "")
	{
		alert("Encrypting with no password is pointless!");
	}
}
-->
</script>
<!-- End of scripts for client-side decryption -->

<h1 id="header"><a href="https://defuse.ca/pastebin.htm">Defuse Cyber-Security</a>'s Secure Pastebin</h1>

<?php

require_once('pastebin.php');

// The urlKey can be a url parameter (backwards compatibility) or part of the URL itself.
if(isset($_GET['h']))
	$urlKey = $_GET['h'];
else
	$urlKey = substr($_SERVER['REQUEST_URI'], 1);

$postInfo = retrieve_post($urlKey);

if($postInfo !== false)
{
    // Display remaining lifetime
    $timeleft = $postInfo['timeleft'];
    $days = (int)($timeleft / (3600 * 24));
    $hours = (int)($timeleft / (3600)) % 24;
    $minutes = (int)($timeleft / 60) % 60;
    echo "<div id=\"timeleft\">This post will be deleted in $days days, $hours hours, and $minutes minutes.</div>";

	if($postInfo['jscrypt'] == false) 
	{
        // If the post wasn't encrypted in JavaScript, we can display it right away
		$split = explode("\n", $postInfo['text']);
		$i = 0;
		echo '<div class="codebox"><ol>';
		foreach($split as $line)
		{
			$line = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $line);
			$line = str_replace("  ", "&nbsp;&nbsp;", $line);
            $line = htmlentities($line, ENT_QUOTES);
			echo '<li><div class="div' . $i . '">&nbsp;' . $line . '</div></li>';
			$i = ($i + 1) % 2;
		}
		echo '</ol></div>';
	}
	else 
	{
        // The post was encrypted in JavaScript, so we print a password prompt
		PrintPasswordPrompt(); 

        // JS will fill this div with the decrypted text
		echo '<div id="tofill" class="codebox"></div>';
        
        // JS decryption code
		PrintDecryptor($postInfo['text']);
	}

	?>
	<form name="pasteform" id="pasteform" action="https://bin.defuse.ca/add.php" method="post">

	<textarea id="paste" name="paste" spellcheck="false" rows="30" cols="80"><?
        if(!$postInfo['jscrypt'])
			echo htmlentities($postInfo['text']);
	?></textarea>

	<input id="jscrypt" type="hidden" name="jscrypt" value="no" />
	<input style="width:300px;" type="submit" name="submitpaste" value="Post Without Password Encryption" />
	<input type="checkbox" name="shorturl" value="yes" /> Short URL
    </form>

	<div id="encinfo">
		Password: 
		<input type="password" id="pass1" value="" size="8" /> &nbsp;
		Verify: <input type="password" id="pass2" value="" size="8" /> 
		<input type="button" value="Encrypt &amp; Post" onclick="encrypt()" /> 
		<noscript>
			<b>[ Please Enable JavaScript ]</b>
		</noscript>
	</div>
	<?
}
else // $postInfo === false, the post does not exist.
{
	echo "<div id=\"sorry\">Sorry, the paste you were looking for could not be found.</div>";
}

// ======================== FUNCTIONS ========================
function PrintPasswordPrompt()
{
?>
	<div id="passwordprompt">
        <b>Enter Password:</b> 
        <input type="password" id="password" name="password" value="" /><input type="button" name="decrypt" value="Decrypt" onClick="decrypt();" />
        <noscript>
			<b>[ Please Enable JavaScript ]</b>
        </noscript>
    </div>
<?
}

function PrintDecryptor($data)
{
?>
<script type="text/javascript">
function decrypt(){
	var encrypted = "<? echo js_string_escape($data); ?>";
	var password = document.getElementById("password").value;
	if(fxw.validate(password, encrypted))
	{
		document.getElementById("passwordprompt").innerHTML = "";
		var plaintext = fxw.decrypt(password, encrypted);

		document.getElementById("paste").value = plaintext;

		var lines = plaintext.split("\n");
		var fancyLines = [];
		var i = 0; 
		fancyLines.push("<ol>");
		for(i = 0; i < lines.length; i++)
		{
			var bgColor = i % 2;
			var line = lines[i].replace("\n", "");
			line = line.replace("\r", "");
			fancyLines.push("<li><div class=\"div" + bgColor + "\">&nbsp;" + fxw.allhtmlsani(line) + "</div></li>");
		}
		fancyLines.push("</ol>");

		var fill = document.getElementById("tofill");
        fill.style.display = "block";
		fill.innerHTML = fancyLines.join('');
	}
	else
	{
		alert("Wrong password.");
        document.getElementById("password").value = "";
	}
}
</script>
<?
}
?>
</body>
</html>
