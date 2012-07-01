<?php
/*
 * This file is part of Defuse Cyber-Security's Secure Pastebin
 * Find updates at: https://defuse.ca/pastebin.htm
 * Developer contact: havoc AT defuse.ca
 * This code is in the public domain. There is no warranty.
 */

require_once('PasswordGenerator.php');

// Database connection
$username="ossbox";
$password="Nw552SfbbZp";
$database="cracky_bin";
mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

// Constants
define("IV_BYTES", 16);
define("POST_LIFETIME_SECONDS", 3600 * 24 * 10);

function commit_post($text, $jsCrypt, $short = false)
{
    $urlKey = PasswordGenerator::getAlphaNumericPassword($short ? 8 : 22);
    $id = get_database_id($urlKey);
    $encryptionKey = get_encryption_key($urlKey);

    $iv = mcrypt_create_iv(IV_BYTES, MCRYPT_DEV_URANDOM);

    $encrypted = SafeEncode(
        $iv .
        mcrypt_encrypt(
            MCRYPT_RIJNDAEL_128,
            $encryptionKey,
            $text,
            MCRYPT_MODE_CBC,
            $iv
        )
    );

    $jsCrypted = $jsCrypt ? 1 : 0;
    $time = time();

    mysql_query(
        "INSERT INTO pastes (token, data, time, jscrypt) 
        VALUES('$id', '$encrypted', '$time', '$jsCrypted')"
    );

    return $urlKey;
}

function retrieve_post($urlKey)
{
    $id = mysql_real_escape_string(get_database_id($urlKey));

    $query = mysql_query("SELECT * FROM `pastes` WHERE token='$id'");
    if(mysql_num_rows($query) > 0)
    {
        $cols = mysql_fetch_array($query);

        $postInfo = array();
        $postInfo['timeleft'] = ($cols['time'] + POST_LIFETIME_SECONDS) - time();
        $postInfo['jscrypt'] = $cols['jscrypt'] == "1";

        $encryptionKey = get_encryption_key($urlKey);
        $ciphertext = SafeDecode($cols['data']);
        $iv = substr($ciphertext, 0, IV_BYTES);
        $encryptedText = substr($ciphertext, IV_BYTES);
        $postInfo['text'] = 
            str_replace("\0", "",
                mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_128,
                    $encryptionKey,
                    $encryptedText, 
                    MCRYPT_MODE_CBC,
                    $iv
                )
            );
        return $postInfo;
    }
    else
        return false;
}

function delete_expired_posts()
{
    $oldest = time() - POST_LIFETIME_SECONDS;
    mysql_query("DELETE FROM pastes WHERE time <= '$oldest'");
}

function get_database_id($urlKey)
{
    return hash_hmac("SHA256", "database_identity", $urlKey, false);
}

function get_encryption_key($urlKey)
{
    return hash_hmac("SHA256", "encryption_key", $urlKey, true);
}

function SafeEncode($data)
{
	return mysql_real_escape_string(base64_encode($data));
}

function SafeDecode($data)
{
	return base64_decode($data);
}

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

// Escapes a string so that it is safe to include into a JavaScript string
// literal.
function js_string_escape($data)
{
    $safe = "";
    for($i = 0; $i < strlen($data); $i++)
    {
        if(ctype_alnum($data[$i]))
            $safe .= $data[$i];
        else
            $safe .= sprintf("\\x%02X", ord($data[$i]));
    }
    return $safe;
}

?>
