<?php

/* This file provides PHP functions for mitigating the BREACH attack until a
 * better solution is found and implemented. Written by Taylor Hornby.
 *
 * For updates, visit:
 *   https://defuse.ca/mitigating-breach-tls-attack-in-php.htm
 * 
 * How-To:
 *   Apply the breach_encode function to all secrets put into the page's HTML.
 *   This includes things like CSRF tokens, keys, passwords, etc. Retrieve the
 *   original value with breach_decode. If the user needs to see the secret,
 *   then it's best to implement breach_decode in JavaScript to decode the
 *   encoded strings after the page has finished loading.
 *
 *   The breach_visual_html() function provides an experimental means to encode
 *   secrets that need to be shown to the user, without having to decode them
 *   with JavaScript. I believe it is enough, but it has not been approved by 
 *   real cryptographers, so do not rely on it.
 *
 *   WARNING: Do not re-use the output of breach_encode() across requests.
 *
 * Read the BREACH paper:
 *   http://tinyurl.com/m2rbceg
 *   The technique used by breach_encode() is discussed in section 3.4.
 */

function breach_encode($str)
{
    if (!function_exists("mcrypt_create_iv")) {
        trigger_error(
            "Required function is missing: mcrypt_create_iv()",
            E_USER_ERROR
        );
    }

    $pad = mcrypt_create_iv(strlen($str), MCRYPT_DEV_URANDOM);
    $encoded = "";
    for ($i = 0; $i < strlen($str); $i++) {
        $encoded .= chr(ord($str[$i]) ^ ord($pad[$i]));
    }
    return bin2hex($pad . $encoded);
}

function breach_decode($str)
{
    $str = pack("H*", $str);
    if (strlen($str) % 2 !== 0) {
        return false;
    }

    $length = strlen($str) / 2;
    $pad = substr($str, 0, $length);
    $encoded = substr($str, $length);

    $decoded = "";
    for ($i = 0; $i < $length; $i++) {
        $decoded .= chr(ord($pad[$i]) ^ ord($encoded[$i]));
    }
    return $decoded;
}


// WARNING: This function is EXPERIMENTAL and should not be relied on.
function breach_visual_html($str)
{
    $ret = "";
    for ($i = 0; $i < strlen($str); $i++) {
        $ret .= breach_comment_string();
        $ret .= breach_zws_string();
        $ret .= substr($str, $i, 1);
        $ret .= breach_zws_string();
        $ret .= breach_comment_string();
    }
    return $ret;
}

function breach_zws_string()
{
    if (!function_exists("mcrypt_create_iv")) {
        trigger_error(
            "Required function is missing: mcrypt_create_iv()",
            E_USER_ERROR
        );
    }

    $zws_count = ord(mcrypt_create_iv(1, MCRYPT_DEV_URANDOM)) % 16;
    $ret = "";
    for ($i = 0; $i < $zws_count; $i++) {
        if (ord(mcrypt_create_iv(1, MCRYPT_DEV_URANDOM)) % 2 == 0) {
            $ret .= "&#8203;";
        } else {
            $ret .= "&#x200b;";
        }
    }
    return $ret;
}

function breach_comment_string()
{
    if (!function_exists("mcrypt_create_iv")) {
        trigger_error(
            "Required function is missing: mcrypt_create_iv()",
            E_USER_ERROR
        );
    }

    return '<!-- ' . bin2hex(mcrypt_create_iv(4, MCRYPT_DEV_URANDOM)) . ' -->';
}

?>
