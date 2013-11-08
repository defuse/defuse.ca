<?php

require_once('/etc/creds.php');

class Upvote
{

# ----------------------------------------------------------------------------
# Configuration:
# ----------------------------------------------------------------------------

    // Let IP addresses vote again after this many seconds have elapsed.
    const VOTE_OLD_AFTER_SECONDS = 86400; 

    // PDO connection to the database (set in InitDB()).
    private static $DB = false;

    private static function InitDB()
    {
        if (self::$DB)
            return;

        try {
            $creds = Creds::getCredentials("df_upvote");
            self::$DB = new PDO(
                // TODO: Fill in your host, database, username, and password.
                "mysql:host={$creds[C_HOST]};dbname={$creds[C_DATB]}",
                $creds[C_USER], // Username
                $creds[C_PASS], // Password
                array(PDO::ATTR_PERSISTENT => true)
            );
            self::$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            unset($creds);
        } catch(Exception $e) {
            die('Failed to connect to upvotes database');
        }
    }

# ----------------------------------------------------------------------------
# Rendering:
# ----------------------------------------------------------------------------

    // Render the upvote arrow for a page.
    // If the page does not exist, it will be created.
    // If the page already exists, but the category, title, description, or
    // canonical url differ, then those properties are updated in the database.
    public static function render_arrows(
        // Unique, unchanging page ID. Must be a valid CSS class name.
        $permanent_id, 
        // Category ID. $permanent_id must be unique even ACROSS categories.
        $category, 
        // Short page title used in the table link.
        $title, 
        // Short page description shown in the table under the link.
        $description, 
        // Canonical url to the page.
        $canonical_url,
        // The class to give the surrounding div. Used internally.
        $class = "upvotearrows"
    )
    {
        self::add_counter($permanent_id, $category, $title, $description, $canonical_url);
        ?>
            <div class="<?php echo self::htmle($class); ?>">
            <?
                self::render_uparrow($permanent_id);
                self::render_count($permanent_id);
                self::render_downarrow($permanent_id);
            ?>
            </div>
        <?
    }

    // Render a table of page links beside their upvote arrows.
    // If $maximum is set, it is the maximum number of pages to show.
    // If $category is set, only pages in that category are shown.
    public static function render_list($maximum = null, $category = null)
    {
        $q = self::get_list_rows($maximum, $category);
        echo '<table class="upvote_pagelist">';
        while (($res = $q->fetch()) !== FALSE) {
            self::render_list_row($res);
        }
        echo '</table>';
    }

# ----------------------------------------------------------------------------
# Request Processing:
# ----------------------------------------------------------------------------

    // Look for an arrow click POST request and process it if there is one.
    // Set $redirect_get to true to 302 redirect to the current page so the user
    // doesn't get a re-submit warning when they refresh.
    public static function process_post($redirect_get = true)
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

    // Look for an arrow click AJAX request and process it if there is one.
    public static function process_ajax()
    {
        if (isset($_POST['upvotes_id']) && isset($_POST['upvotes_direction'])) {
            // Add the vote.
            $permanent_id = $_POST['upvotes_id'];
            $direction = $_POST['upvotes_direction'];
            self::process_vote($permanent_id, $direction);

            // Tell the client what the arrows/counter should look like now.
            $upvotes = self::get_upvotes($permanent_id);
            $downvotes = self::get_downvotes($permanent_id);
            $total = $upvotes - $downvotes;

            switch (self::get_user_action($permanent_id)) {
                case "upvote":
                    $uparrow = "Y";
                    $downarrow = "N";
                    break;
                case "downvote":
                    $uparrow = "N";
                    $downarrow = "Y";
                    break;
                default:
                    $uparrow = "N";
                    $downarrow = "N";
            }
            self::send_ajax_response("pass", $uparrow, $downarrow, $total);
        } else {
            self::send_ajax_response("fail", "", "", "", "");
        }
    }

# ----------------------------------------------------------------------------
# Automated Voting:
# ----------------------------------------------------------------------------

    public static function give_upvote($permanent_id, $undo_downvote = false)
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

    public static function undo_upvote($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'UPDATE counts SET upvotes = upvotes - 1
             WHERE permanent_id = :permanent_id'
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    public static function get_upvotes($permanent_id)
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

    public static function give_downvote($permanent_id, $undo_upvote = false)
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

    public static function undo_downvote($permanent_id)
    {
        self::InitDB();
        $q = self::$DB->prepare(
            'UPDATE counts SET downvotes = downvotes - 1
             WHERE permanent_id = :permanent_id'
         );
        $q->bindParam(':permanent_id', $permanent_id);
        $q->execute();
    }

    public static function get_downvotes($permanent_id)
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

# ----------------------------------------------------------------------------
# Rendering (Private):
# ----------------------------------------------------------------------------

    private static function render_uparrow($permanent_id)
    {
        $upFormName = "upvoteUpForm" . $permanent_id;
        $upImageName = "upvoteUpImage" . $permanent_id;
    ?>
        <div class="upvoteuparrow">
            <form 
                action="<?php echo self::htmle(self::get_page_url()); ?>" 
                method="post"
                onsubmit="return upvote.submit('<?php echo self::jse($permanent_id); ?>', 'up')"
                class="upvoteform <?php echo self::htmle($upFormName); ?>"
            >
                <input type="hidden" name="upvotes_direction" value="up" />
                <input type="hidden" name="upvotes_id" value="<?php echo self::htmle($permanent_id); ?>" />
                <?  if (self::get_user_action($permanent_id) == "upvote") { ?>
                    <input
                        type="image" src="/images/upvote-selected.gif" alt="Upvote" 
                        name="<?php echo self::htmle($upImageName); ?>"
                    />
                <? } else { ?>
                    <input
                        type="image" src="/images/upvote.gif" alt="Upvote"
                        name="<?php echo self::htmle($upImageName); ?>"
                    />
                <? } ?>
            </form>
        </div>
    <?
    }

    private static function render_downarrow($permanent_id)
    {
        $downFormName = "upvoteDownForm" . $permanent_id;
        $downImageName = "upvoteDownImage" . $permanent_id;
    ?>
        <div class="upvotedownarrow">
            <form 
                action="<?php echo self::htmle(self::get_page_url()); ?>" 
                method="post"
                onsubmit="return upvote.submit('<?php echo self::jse($permanent_id); ?>', 'down')"
                class="upvoteform <?php echo self::htmle($downFormName); ?>"
            >
                <input type="hidden" name="upvotes_direction" value="down" />
                <input type="hidden" name="upvotes_id" value="<?php echo self::htmle($permanent_id); ?>" />
                <?  if (self::get_user_action($permanent_id) == "downvote") { ?>
                    <input 
                        type="image" src="/images/downvote-selected.gif" alt="Downvote" 
                        name="<?php echo self::htmle($downImageName); ?>"
                    />
                <? } else { ?>
                    <input 
                        type="image" src="/images/downvote.gif" alt="Downvote"
                        name="<?php echo self::htmle($downImageName); ?>"
                    />
                <? } ?>
            </form>
        </div>
    <?
    }

    private static function render_count($permanent_id)
    {
        switch(self::get_user_action($permanent_id)) {
            case "upvote":
                $countclass = "upvotecount_upvoted";
                break;
            case "downvote":
                $countclass = "upvotecount_downvoted";
                break;
            default:
                $countclass = "upvotecount";
        }
        $counterName = "upvoteCounter" . $permanent_id;
        $upvotes = (int)self::get_upvotes($permanent_id);
        $downvotes = (int)self::get_downvotes($permanent_id);
        $total = $upvotes - $downvotes;
    ?>
        <div class="<?php echo self::htmle($countclass . " " . $counterName); ?>" >
            <?php echo self::htmle($total); ?> 
        </div>
    <?
    }

    private static function render_list_row($res)
    {
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

    private static function get_list_rows($maximum = null, $category = null)
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
        return $q;
    }

# ----------------------------------------------------------------------------
# Request Processing (Private):
# ----------------------------------------------------------------------------

    private static function add_counter(
        $permanent_id, 
        $category, 
        $title, 
        $description, 
        $canonical_url
    )
    {
        self::InitDB();

        if (preg_match("/\\A[a-zA-Z][a-zA-Z0-9._\\-]+\\Z/", $permanent_id) !== 1) {
            trigger_error( "Invalid upvote permanent id [$permanent_id].", E_USER_ERROR );
            return;
        }
        if (preg_match("/\\A[a-zA-Z][a-zA-Z0-9._\\-]+\\Z/", $category) !== 1) {
            trigger_error( "Invalid upvote category id [$category].", E_USER_ERROR );
            return;
        }

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


    private static function send_ajax_response($status, $uparrow, $downarrow, $total)
    {
        $status = self::htmle($status);
        $uparrow = self::htmle($uparrow);
        $downarrow = self::htmle($downarrow);
        $total = self::htmle($total);

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= "<response>\n";
        $xml .= "<status>$status</status>\n";
        $xml .= "<uparrow>$uparrow</uparrow>\n";
        $xml .= "<downarrow>$downarrow</downarrow>\n";
        $xml .= "<total>$total</total>\n";
        $xml .= "</response>\n";

        self::send_xml_response($xml);
    }


    private static function send_xml_response($xml)
    {
        header('Content-Type: text/xml');
        echo $xml;
        die();
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

# ----------------------------------------------------------------------------
# Utility Functions (Private):
# ----------------------------------------------------------------------------

    private static function get_page_url()
    {
        // Taken from: http://stackoverflow.com/a/1229924
        $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    private static function htmle($str)
    {
        return htmlentities($str, ENT_QUOTES);
    }

    private static function jse($data)
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
