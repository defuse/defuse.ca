<?php

$COMMENTDB = loginToCommentDB();

function loginToCommentDB()
{
    $username = "comments";
    $password = "e1oQajBIji";
    $database = "comments";

    $result = FALSE;
    try 
    {
        $result = new PDO('mysql:host=localhost;dbname=' . $database,
                            $username,
                            $password,
                            array(PDO::ATTR_PERSISTENT => true)
                            );
    }
    catch(Exception $e)
    {
        $result = FALSE;
    }

    unset($username);
    unset($password);
    unset($database);
    return $result;
}

class Comments
{
    private static $page_whitelist = array("race-conditions-in-web-applications");

    public static function AddComment($page, $name, $comment)
    {
        global $COMMENTDB;

        if(!self::InWhiteList($page))
            return FALSE;

        $q = $COMMENTDB->prepare("INSERT INTO `comments` (page, name, comment, post_time) 
                                    VALUES(:page, :name, :comment, :time)");
        $q->bindParam(':page', $page);
        $q->bindParam(':name', $name);
        $q->bindParam(':comment', $comment);
        $now = time();
        $q->bindParam(':time', $now);
        $q->execute();
    }
    
    public static function InWhiteList($page)
    {
        return in_array($page, self::$page_whitelist);
    }

    public static function GetComments($page)
    {
        global $COMMENTDB;

        if(!self::InWhiteList($page))
            return FALSE;

        $q = $COMMENTDB->prepare("SELECT name, comment, post_time FROM `comments` WHERE page=:page
                                  ORDER BY post_time DESC");
        $q->bindParam(':page', $page);
        $q->execute();

        $all = $q->fetchAll();

        $result = array();
        foreach($all as $row)
        {
            $next = array(  "name" => $row['name'],
                            "comment" => $row['comment'],
                            "time" => $row['post_time']
                          );
            $result[] = $next;
        }

        return $result;
    }
}


?>
