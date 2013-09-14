<?php
class Bibliography
{
    private $references;

    function __construct()
    {
        $this->references = array();
    }

    function addGeneralReference($key, $text, $url = null)
    {
        if ($url == null) {
            $this->references[$key] = htmlentities($html, ENT_QUOTES);
        } else {
            $url = htmlentities($url, ENT_QUOTES);
            $text = htmlentities($text, ENT_QUOTES);
            $this->references[$key] = "<a href=\"$url\">$text</a>";
        }
    }

    function addGeneralHTMLReference($key, $html)
    {
        $this->references[$key] = $html;
    }

    function addReference($key, $title, $authors, $date, $url)
    {
        $title = htmlentities($title, ENT_QUOTES);
        $authors = htmlentities($authors, ENT_QUOTES);
        $date = htmlentities($date, ENT_QUOTES);
        $url = htmlentities($url, ENT_QUOTES);
        $this->references[$key] = "$authors. $date. <a href=\"$url\">$title.</a>";
    }

    function cite($key)
    {
        if (array_key_exists($key, $this->references)) {
            $reference = $this->references[$key];
            $safe_key = htmlentities($key, ENT_QUOTES);
            echo "<sup><a href=\"#cite_$safe_key\">[" . htmlentities($key, ENT_QUOTES) . "]</a></sup>";
        } else {
            echo "<sup>ERROR: INVALID KEY</sup>";
            trigger_error("Invalid biblography key.", E_USER_WARNING);
        }
    }

    function printBibliography()
    {
        echo '<div id="references">';
        echo "<h2>References and Notes</h2>";
        ksort($this->references);
        foreach ($this->references as $key => $html) {
            $safe_key = htmlentities($key, ENT_QUOTES);
            echo "<a name=\"cite_$safe_key\"></a><span id=\"cite_$safe_key\">$safe_key. $html</span>";
            echo "<br />";
        }
        echo '</div>';
    }
}
?>
