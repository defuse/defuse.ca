<?php
//$stuff=$_GET['id'];//"String to enc/enc/dec/dec =,=,";
//$key="XiTo74dOO09N48YeUmuvbL0E";
//$enc =rot13encrypt("c24a99ff7466c36da9598b4b395bc0c93e86ac6a2ef888a0cb40cb710a59d762");
//echo $enc;
//echo "<br>";
echo rot13decrypt($enc);
function rot13encrypt ($str) {
   // return str_rot13(base64_encode($str));
	 return str_rot13($str);
    }

function rot13decrypt ($str) {
    //return base64_decode(str_rot13($str));
	return str_rot13($str);
    }
function nl() {
    echo "<br/> \n";
}

$iv = mcrypt_create_iv (mcrypt_get_block_size (MCRYPT_TripleDES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM);

// Encrypting
function encrypt($string) {
    $enc = "";
    global $iv;
global $key;
    $enc=mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_ENCRYPT, $iv);

  return base64_encode($enc);
}

// Decrypting 
function decrypt($string) {
    $dec = "";
    $string = trim(base64_decode($string));
    global $iv;
global $key;
    $dec = mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_DECRYPT, $iv);
  return $dec;
}

//$encrypted = encrypt($stuff, $key);
//$decrypted = decrypt($encrypted, $key);





//header("Location:./index.php?lol=$decrypted");

//echo "Encrypted is ".$encrypted;// . nl(); 
//echo "<br />";
//echo "Decrypted is ".$decrypted;// . nl(); 
?>