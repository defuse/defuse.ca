<?php

// Put this file in /etc/creds.php then populate it with your credentials.

define('C_USER', 0);
define('C_PASS', 1);
define('C_HOST', 2);
define('C_DATB', 3);

class Creds
{
    private static $CREDENTIALS = array(
        "pastebin" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "cracky_bin",
        ),
        "pphos" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "pphos",
        ),
        "trent" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "cracky_trent",
        ),
        "pdfcleaner" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "cracky_pdf",
        ),
        "df_upvote" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "upvotes",
        ),
        "df_phpcount" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "phpcount",
        ),
        "ip2loc" => array(
            C_USER => "",
            C_PASS => "",
            C_HOST => "localhost",
            C_DATB => "ip2location",
        ),
    );

    public static function getCredentials($key)
    {
        if (array_key_exists($key, self::$CREDENTIALS)) {
            return self::$CREDENTIALS[$key];
        } else {
            return false;
        }
    }
}

?>
