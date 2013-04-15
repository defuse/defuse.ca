<?php

// INSPIRED BY THIS POST:
// http://steve-yegge.blogspot.ca/2006/03/blog-or-get-off-pot.html

class Upvote
{
    const VOTE_OLD_AFTER_SECONDS = 86400; 

    private static $DB = false;

    private static function InitDB()
    {
        if (self::$DB)
            return;

        try {
            self::$DB = new PDO(
                'mysql:host=localhost;dbname=upvotes',
                'upvotes', // Username
                'ykqLBE29TMzIzr', // Password
                array(PDO::ATTR_PERSISTENT => true)
            );
        } catch(Exception $e) {
            die('Failed to connect to upvotes database');
        }
    }

    public static function render_arrows(
        $permanent_id, 
        $category, 
        $title, 
        $description, 
        $canonical_url,
        $class = "upvotearrows"
    )
    {
        self::add_counter($permanent_id, $category, $title, $description, $canonical_url);
        $upvotes = (int)self::get_upvotes($permanent_id);
        $downvotes = (int)self::get_downvotes($permanent_id);
        $total = $upvotes - $downvotes;
        $current_url = self::get_page_url();
        $user_action = self::get_user_action($permanent_id);
        if ($user_action == "upvote") {
            $countclass = "upvotecount_upvoted";
        } elseif ($user_action == "downvote") {
            $countclass = "upvotecount_downvoted";
        } else {
            $countclass = "upvotecount";
        }
        $upFormName = "upvoteUpForm" . $permanent_id;
        $downFormName = "upvoteDownForm" . $permanent_id;
        $upImageName = "upvoteUpImage" . $permanent_id;
        $downImageName = "upvoteDownImage" . $permanent_id;
        // FIXME: because IE doesn't recognize the name="" attribute on anything
        // but form elements, I'm using an ID for now. This means that if there
        // are ever two vote arrows rendered for the SAME thing, it will BREAK!
        $counterName = "upvoteCounter" . $permanent_id;
        $js_id = self::js_string_escape($permanent_id);
        ?>
<!-- ALL CREDIT TO reddit.com FOR THE ARROW STYLE! -->
<div class="<?php echo htmlentities($class, ENT_QUOTES); ?>">
    <div class="upvoteuparrow">
        <form 
            action="<?php echo htmlentities($current_url, ENT_QUOTES); ?>" 
            method="post"
            onsubmit="return upvoteSubmit('<?php echo $js_id; ?>', 'up')"
            name="<?php echo htmlentities($upFormName, ENT_QUOTES); ?>"
            class="upvoteform"
        >
            <input type="hidden" name="upvotes_direction" value="up" />
            <input type="hidden" name="upvotes_id" value="<?php echo htmlentities($permanent_id, ENT_QUOTES); ?>" />
            <?  if ($user_action == "upvote") { ?>
                <input id="upupup"
                    type="image" src="/images/upvote-selected.gif" alt="Upvote" 
                    name="<?php echo htmlentities($upImageName, ENT_QUOTES); ?>"
                />
            <? } else { ?>
                <input id="upupup"
                    type="image" src="/images/upvote.gif" alt="Upvote"
                    name="<?php echo htmlentities($upImageName, ENT_QUOTES); ?>"
                />
            <? } ?>
        </form>
    </div>
    <div 
        class="<?php echo htmlentities($countclass, ENT_QUOTES); ?>"
        id="<?php echo htmlentities($counterName, ENT_QUOTES); ?>"
    >
    <?php echo htmlentities($total, ENT_QUOTES); ?> 
    </div>
    <div class="upvotedownarrow">
        <form 
            action="<?php echo htmlentities($current_url, ENT_QUOTES); ?>"
            method="post"
            onsubmit="return upvoteSubmit('<?php echo $js_id; ?>', 'down')"
            name="<?php echo htmlentities($downFormName, ENT_QUOTES); ?>"
            class="upvoteform"
        >
            <input type="hidden" name="upvotes_direction" value="down" />
            <input type="hidden" name="upvotes_id" value="<?php echo htmlentities($permanent_id, ENT_QUOTES); ?>" />
            <?  if ($user_action == "downvote") { ?>
                <input 
                    type="image" src="/images/downvote-selected.gif" alt="Downvote" 
                    name="<?php echo htmlentities($downImageName, ENT_QUOTES); ?>"
                />
            <? } else { ?>
                <input 
                    type="image" src="/images/downvote.gif" alt="Downvote"
                    name="<?php echo htmlentities($downImageName, ENT_QUOTES); ?>"
                />
            <? } ?>
        </form>
    </div>
</div>
        <?
    }

    public static function get_category_list()
    {

    }

    public static function render_list($maximum = null, $category = null)
    {
        self::InitDB();
        $max_clause = ($maximum === null) ? "" : "LIMIT " . (int)$maximum;
        $cat_clause = ($category === null) ? "" : "WHERE category = :category";
        $q = self::$DB->prepare(
            "SELECT * FROM counts $cat_clause
             ORDER BY (upvotes - downvotes) DESC $max_clause"
         );
        if ($category != null) {
            $q->bindParam(':category', $category);
        }
        $q->execute();

        echo '<table class="upvote_pagelist">';

        while (($res = $q->fetch()) !== FALSE) {
            $safe_title = htmlentities($res['title'], ENT_QUOTES);
            $safe_description = htmlentities($res['description'], ENT_QUOTES);
            $safe_url = htmlentities($res['canonical_url'], ENT_QUOTES);

        ?>
            <tr>
            <td class="upvote_list_arrowcell">
            <?php
                self::render_arrows(
                    $res['permanent_id'],
                    $res['category'],
                    $res['title'],
                    $res['description'],
                    $res['canonical_url'],
                    "upvotearrowsinlist"
                );
            ?>
            </td>
            <td class="upvote_list_titlecell">
                <a class="upvote_list_title" href="<?php echo $safe_url; ?>">
                    <?php echo $safe_title; ?>
                </a>
                <div class="upvote_list_desc">
                    <?php echo $safe_description; ?>
                </div>
            </td>
            </tr>
        <?
        }

        echo '</table>';
    }

    private static function add_counter(
        $permanent_id, 
        $category, 
        $title, 
        $description, 
        $canonical_url
    )
    {
        self::InitDB();

        $q = self::$DB->prepare(
            'SELECT * FROM counts
             WHERE permanent_id = :permanent_id'
        );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();

        if (($res = $q->fetch()) !== FALSE) {
            if ($res['category'] != $category || $res['title'] != $title ||
                $res['description'] != $description || 
                $res['canonical_url'] != $canonical_url ) {
                $q = self::$DB->prepare(
                    'UPDATE counts SET category=:category, title=:title,
                     description=:description, canonical_url=:canonical_url
                     WHERE permanent_id = :permanent_id'
                 );
                $q->bindParam(':category', $category);
                $q->bindParam(':permanent_id', $permanent_id);
                $q->bindParam(':title', $title);
                $q->bindParam(':description', $description);
                $q->bindParam(':canonical_url', $canonical_url);
                $q->execute();
            }
        } else {
            $q = self::$DB->prepare(
                'INSERT INTO counts (category, permanent_id, title, description, canonical_url, upvotes, downvotes)
                 VALUES (:category, :permanent_id, :title, :description, :canonical_url, :upvotes, :downvotes)'
             );
            $q->bindParam(':category', $category);
            $q->bindParam(':permanent_id', $permanent_id);
            $q->bindParam(':title', $title);
            $q->bindParam(':description', $description);
            $q->bindParam(':canonical_url', $canonical_url);
            $zero = 0;
            $q->bindParam(':upvotes', $zero);
            $q->bindParam(':downvotes', $zero);
            $q->execute();
        }
    }

    public static function process_post($redirect_get = false)
    {
        if (isset($_POST['upvotes_id']) && isset($_POST['upvotes_direction'])) {
            $permanent_id = $_POST['upvotes_id'];
            $direction = $_POST['upvotes_direction'];
            self::process_vote($permanent_id, $direction);
            if ($redirect_get) {
                $url = self::get_page_url();
                header("Location: $url", TRUE, 302);
            }
        }
    }

    public static function process_ajax()
    {
        if (isset($_POST['upvotes_id']) && isset($_POST['upvotes_direction'])) {
            $permanent_id = $_POST['upvotes_id'];
            $direction = $_POST['upvotes_direction'];

            self::process_vote($permanent_id, $direction);

            $existing_action = self::get_user_action($permanent_id);
            $upvotes = (int)self::get_upvotes($permanent_id);
            $downvotes = (int)self::get_downvotes($permanent_id);
            $total = $upvotes - $downvotes;

            header('Content-Type: text/xml');
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $xml .= "<response>\n";
            $xml .= "<status>pass</status>\n";
            if ($existing_action == "upvote") {
                $xml .= "<uparrow>Y</uparrow>\n";
            } else {
                $xml .= "<uparrow>N</uparrow>\n";
            }
            if ($existing_action == "downvote") {
                $xml .= "<downarrow>Y</downarrow>\n";
            } else {
                $xml .= "<downarrow>N</downarrow>\n";
            }
            $xml .= "<total>" . htmlentities($total, ENT_QUOTES) . "</total>\n";
            $xml .= "</response>\n";
            self::send_xml_response($xml);
        } else {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $xml .= "<response>\n";
            $xml .= "<status>fail</status>\n";
            $xml .= "</response>\n";
            self::send_xml_response($xml);
        }
    }

    private static function send_xml_response($xml)
    {
        header('Content-Type: text/xml');
        echo $xml;
        die();
    }

    private static function process_vote($permanent_id, $direction)
    {
        $existing_action = self::get_user_action($permanent_id);
        if ($direction == "up" && $existing_action != "upvote") {
            // Change from (nothing or downvote) to upvote
            self::give_upvote($permanent_id, $existing_action == "downvote");
            self::set_user_action($permanent_id, "upvote");
        } elseif ($direction == "down" && $existing_action != "downvote") {
            // Change from (nothing or upvote) to downvote
            self::give_downvote($permanent_id, $existing_action == "upvote");
            self::set_user_action($permanent_id, "downvote");
        } elseif ($direction == "up" && $existing_action == "upvote") {
            // Undo an upvote.
            self::undo_upvote($permanent_id);
            self::set_user_action($permanent_id, "");
        } elseif ($direction == "down" && $existing_action == "downvote") {
            // Undo a downvote.
            self::undo_downvote($permanent_id);
            self::set_user_action($permanent_id, "");
        }
    }

    private static function remove_old_vote_history()
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'DELETE FROM history WHERE time_added < :delete_before'
        );
        $delete_before = time() - self::VOTE_OLD_AFTER_SECONDS;
        $q->bindParam(':delete_before', $delete_before);
        $q->execute();
    }

    private static function set_user_action($permanent_id, $action)
    {
        self::InitDB();
        $hash = hash("sha256", $permanent_id . $_SERVER['REMOTE_ADDR']);
        if (self::get_user_action($permanent_id) !== null) {
            $q = self::$DB->prepare(
                'UPDATE history SET action = :action, time_added = :time_added
                 WHERE hash = :hash'
             );
            $q->bindParam(':action', $action);
            $time = time();
            $q->bindParam(':time_added', $time);
            $q->bindParam(':hash', $hash);
            $q->execute();
        } else {
            $q = self::$DB->prepare(
                'INSERT INTO history (hash, action, time_added)
                 VALUES (:hash, :action, :time_added)'
             );
            $q->bindParam(':action', $action);
            $time = time();
            $q->bindParam(':time_added', $time);
            $q->bindParam(':hash', $hash);
            $q->execute();
        }
    }

    private static function get_user_action($permanent_id)
    {
        self::InitDB();
        self::remove_old_vote_history();
        $hash = hash("sha256", $permanent_id . $_SERVER['REMOTE_ADDR']);
        $q = self::$DB->prepare(
            'SELECT action FROM history WHERE hash = :hash'
        );
        $q->bindParam(':hash', $hash);
        $q->execute();
        if (($res = $q->fetch()) !== FALSE) {
            return $res['action'];
        }
    }

    private static function give_upvote($permanent_id, $undo_downvote = false)
    {
        self::InitDB();
        $undo = $undo_downvote ? ", downvotes = downvotes - 1" : "";
        $q = self::$DB->prepare(
            "UPDATE counts SET upvotes = upvotes + 1 $undo
             WHERE permanent_id = :permanent_id"
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    private static function give_downvote($permanent_id, $undo_upvote = false)
    {
        self::InitDB();
        $undo = $undo_upvote ? ", upvotes = upvotes - 1" : "";
        $q = self::$DB->prepare(
            "UPDATE counts SET downvotes = downvotes + 1 $undo
             WHERE permanent_id = :permanent_id"
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    private static function undo_upvote($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'UPDATE counts SET upvotes = upvotes - 1
             WHERE permanent_id = :permanent_id'
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    private static function undo_downvote($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'UPDATE counts SET downvotes = downvotes - 1
             WHERE permanent_id = :permanent_id'
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    private static function get_upvotes($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'SELECT upvotes FROM counts 
             WHERE permanent_id = :permanent_id'
        );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();

        if (($res = $q->fetch()) !== FALSE) {
            return $res['upvotes'];
        } else {
            return 0;
        }
    }

    private static function get_downvotes($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'SELECT downvotes FROM counts 
             WHERE permanent_id = :permanent_id'
        );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();

        if (($res = $q->fetch()) !== FALSE) {
            return $res['downvotes'];
        } else {
            return 0;
        }
    }

    private static function get_page_url()
    {
        // Taken from: http://stackoverflow.com/a/1229924
        // FIXME: Apache only!
        $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    private static function js_string_escape($data)
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
