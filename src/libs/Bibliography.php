<?php
/*
 * Defuse.ca
 * Copyright (C) 2013  Taylor Hornby
 * 
 * This file is part of Defuse.
 * 
 * Defuse is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * Defuse is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
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
            echo '<div class="ref_item">';
            echo "<a name=\"cite_$safe_key\"></a>$safe_key. <span id=\"cite_$safe_key\">$html</span>";
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
