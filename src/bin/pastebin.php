<?php
/*
 * This file is part of Defuse Security's Secure Pastebin
 * Find updates at: https://defuse.ca/pastebin.htm
 * Developer contact: havoc AT defuse.ca
 * This code is in the public domain. There is no warranty.
 */

require_once('PasswordGenerator.php');

// Credentials for database connection
require_once('/storage/creds.php');

// Constants
define("IV_BYTES", 16);

class Pastebin
{
    private static $DB = false;

    private static function InitDB()
    {
        if(self::$DB)
            return;

        try
        {
            $creds = Creds::getCredentials("pastebin");
            self::$DB = new PDO(
                "mysql:host={$creds[C_HOST]};dbname={$creds[C_DATB]}",
                $creds[C_USER], // Username
                $creds[C_PASS], // Password
                array(PDO::ATTR_PERSISTENT => true)
            );
            self::$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            unset($creds);
        }
        catch(Exception $e)
        {
            die('Failed to connect to phpcount database');
        }
    }

    public static function commit_post($text, $jsCrypt, $lifetime_seconds, $short = false)
    {
        self::InitDB();

        do {
            $urlKey = PasswordGenerator::getAlphaNumericPassword($short ? 8 : 22);
        } while( Pastebin::retrieve_post( $urlKey ) !== false );

        $id = Pastebin::get_database_id($urlKey);
        $encryptionKey = Pastebin::get_encryption_key($urlKey);

        $iv = mcrypt_create_iv(IV_BYTES, MCRYPT_DEV_URANDOM);

        $encrypted = Pastebin::SafeEncode(
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
        $time = (int)(time() + $lifetime_seconds);

        $q = self::$DB->prepare(
            'INSERT INTO pastes (token, data, time, jscrypt)
            VALUES( :id, :data, :time, :jscrypt )'
        );
        $q->bindParam(':id', $id);
        $q->bindParam(':data', $encrypted);
        $q->bindParam(':time', $time);
        $q->bindParam(':jscrypt', $jsCrypted);
        $q->execute();

        return $urlKey;
    }

    public static function retrieve_post($urlKey)
    {
        self::InitDB();

        $id = Pastebin::get_database_id($urlKey);

        $q = self::$DB->prepare(
            "SELECT * FROM `pastes` WHERE token=:id"
        );
        $q->bindParam(':id', $id);
        $q->execute();

        if(($cols = $q->fetch()) !== FALSE)
        {
            $postInfo = array();
            $postInfo['timeleft'] = $cols['time'] - time();
            $postInfo['jscrypt'] = $cols['jscrypt'] == "1";

            $encryptionKey = Pastebin::get_encryption_key($urlKey);
            $ciphertext = Pastebin::SafeDecode($cols['data']);
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

    public static function delete_post($urlKey)
    {
        self::InitDB();

        $id = Pastebin::get_database_id($urlKey);
        $q = self::$DB->prepare("DELETE FROM `pastes` WHERE token=:id");
        $q->bindParam(':id', $id);
        $q->execute();
    }

    public static function delete_expired_posts()
    {
        self::InitDB();

        $now = time();
        $q = self::$DB->prepare(
            "DELETE FROM pastes WHERE time <= :time"
        );
        $q->bindParam(':time', $now);
        $q->execute();
    }

    private static function get_database_id($urlKey)
    {
        return hash_hmac("SHA256", "database_identity", $urlKey, false);
    }

    private static function get_encryption_key($urlKey)
    {
        return hash_hmac("SHA256", "encryption_key", $urlKey, true);
    }

    private static function SafeEncode($data)
    {
        return base64_encode($data);
    }

    private static function SafeDecode($data)
    {
        return base64_decode($data);
    }

    // Escapes a string so that it is safe to include into a JavaScript string
    // literal.
    public static function js_string_escape($data)
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
}

?>
