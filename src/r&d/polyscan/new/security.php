<?php

function xss($data)
{
	return htmlspecialchars($data, ENT_QUOTES);
}

function sqli($data)
{
	return mysql_real_escape_string($data);
}

function obf($data)
{
	$data = $data . "";
	$OBF_PASS = "A7SYd2mmLHAo5KmWdZsrLTvtDA7i4rGbJaKkuIlUuHd1dP3lz4mteddDxkj5vTwh";
	$obf_key = hash("SHA256", $OBF_PASS, true);
	$obf = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $obf_key, $data, MCRYPT_MODE_CBC, $obf_key);
	$de = deobf(bin2hex($obf));
	//echo $de; die();
	return bin2hex($obf);
}

function deobf($obf)
{
	$data = hex2bin($obf);
	$OBF_PASS = "A7SYd2mmLHAo5KmWdZsrLTvtDA7i4rGbJaKkuIlUuHd1dP3lz4mteddDxkj5vTwh";
	$obf_key = hash("SHA256", $OBF_PASS, true);
	$de = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $obf_key, $data, MCRYPT_MODE_CBC, $obf_key);
	$de = rtrim($de);
	return $de;
}

function hex2bin($h)
  {
  if (!is_string($h)) return null;
  $r='';
  for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
  return $r;
  }

?>