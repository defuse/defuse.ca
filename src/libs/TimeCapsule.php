<?php

require_once('/etc/creds.php');

class TimeCapsule
{
    // PDO connection to the database (set in InitDB()).
    private static $DB = false;

    private static function InitDB()
    {
        if (self::$DB)
            return;

        try {
            $creds = Creds::getCredentials("timecapsule");
            self::$DB = new PDO(
                "mysql:host={$creds[C_HOST]};dbname={$creds[C_DATB]}",
                $creds[C_USER], // Username
                $creds[C_PASS], // Password
                array(PDO::ATTR_PERSISTENT => true)
            );
            self::$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            unset($creds);
        } catch(Exception $e) {
            throw new Exception('Failed to connect to the timecapsule database');
        }
    }

    public static function add_entry($message)
    {
        try {
            self::InitDB();
            $q = self::$DB->prepare(
                'INSERT INTO timecapsule (timestamp, message) VALUES (:timestamp, :message)'
            );
            if ($q === FALSE) {
                return false;
            }
            $q->bindParam(':timestamp', time());
            $q->bindParam(':message', $message);
            return $q->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public static function print_all_entries_in_order()
    {
        self::InitDB();

        $q = self::$DB->prepare(
            'SELECT * FROM timecapsule ORDER BY id'
        );
        $q->execute();

        while (($res = $q->fetch()) !== FALSE) {
            echo $res['message'] . "\n";
        }
    }

    public static function get_message_count()
    {
        try {
            self::InitDB();

            $q = self::$DB->prepare(
                'SELECT COUNT(*) AS count FROM timecapsule'
            );
            $q->execute();
            $res = $q->fetch();
            return (int)$res['count'];
        } catch (Exception $e) {
            return false;
        }
    }

    public static function get_last_timestamp()
    {
        try {
            self::InitDB();

            $q = self::$DB->prepare(
                'SELECT * FROM timecapsule ORDER BY id DESC LIMIT 1'
            );
            $q->execute();
            $res = $q->fetch();
            return (int)$res['timestamp'];
        } catch (Exception $e) {
            return false;
        }
    }
}
