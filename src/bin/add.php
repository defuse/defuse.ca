<?php

require_once('info.php');
$tendaysago = time() - (3600 * 24) * 10;
mysql_query("DELETE FROM pastes WHERE time <= $tendaysago");

if(isset($_POST['paste']) && !empty($_POST['paste']))
{
	//get the text
	$data = smartslashes($_POST['paste']);
    $data = str_replace("\r", "\n", $data);
    $data = str_replace("\n\n", "\n", $data); // Handles CRLF and Chrome's stupid LFLF

	//create the "password" 22 characters if 'long' or only 8 if short
	$contents = hash("SHA512", mcrypt_create_iv(512, MCRYPT_DEV_URANDOM), true);
	$alphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$password = format($alphanum,$contents);
	$password_length = 22;
	if($_POST['shorturl'] == "yes")
	{
		$password_length = 8;
	}
	$password = substr($password, 0, $password_length);

	//database id is the hash of the password, encryption key is hash(password+salt)
	$id = hash("SHA256", $password);
	//very important to use salt for the key, or else the id will BE the key
	$key = hash("SHA256", $password . 
		"243f6a8885a308d313198a2e03707344a4093822299f31d0082efa98ec4e6c89452821e638d01377be5466cf34e90c6cc0a", true);

	//IV isn't really important here as they key will be different each time
	$crypted = SafeEncode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, md5($key)));
	$time = time();
	$jsCrypted = "0";
	if($_POST['jscrypt'] == "yes")
		$jsCrypted = "1";
	mysql_query("INSERT INTO pastes (token, data, time, jscrypt) VALUES('$id', '$crypted', '$time', '$jsCrypted')");

	//redirect user to the view page
	header("Location: https://bin.ossbox.com/$password");
}
else
{
	die("Empty post!");
}

/*
 * formats random data into 64 characters of some character set
 * $charset - a string containing all the possible characters to choose from
 * $random - a random binary string (must have more bits of random entropy than 64 chars from $charset
 * returns - the $random formatted into a 64 char long string made up of chars from $charset
 */
function format($charset, $random)
{
	//Take $random and successivly divide it by strlen($charset), 
	//the remainder being the next char of return value.

	$mods = "";
	for($i = 0; $i < 64; $i++)
	{
		$remainder = 0;
		$total = 0;
		$quotient = "";
		$divisor = strlen($charset);
		for($j = 0; $j < strlen($random); $j++)
		{
			$total = ($remainder * 256 + ord(substr($random, $j, 1)));
			$quotient .= chr($total / $divisor);
			$remainder = $total % $divisor;	
		}
		$random = $quotient;
		$mods .= substr($charset, $remainder, 1);
	}
	return $mods;
}
?>
