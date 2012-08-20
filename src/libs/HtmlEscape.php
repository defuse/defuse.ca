<?php
class HtmlEscape
{
    public static function escapeText($text, $brTags, $tabWidth)
    {
        // Replace tabs with spaces -- Must be done before htmlspecialchars 
        // because the tab width is dependant upon the cursor position.
        $esc = self::tabsToSpaces($text, $tabWidth);

        // Escape all characters that have a special meaning in HTML
        $esc = htmlspecialchars($esc, ENT_QUOTES);

        // Replace repeated spaces with &nbsp;
        //      This is tricky. Spaces cannot simply be replaced with &nbsp;
        //      because the line of text will not break, so we have to leave
        //      normal spaces in between pairs of &nbsp; to let the line break.
        //      The space must come before the &nbsp; because we want three
        //      spaces in a row to turn into " &nbsp; " not "&nbsp;  " (which
        //      will look like two spaces in the browser).
        $esc = str_replace("  ", " &nbsp;", $esc);

        // HTML ignores leading spaces in elements like <p> and <div> so we 
        // have to replace spaces at the beginning of the line with an &nbsp;
        // 0x20 = ASCII SPACE
        $esc = preg_replace('/^\x20/m', "&nbsp;", $esc);
        // The same thing happens when the space is at the end of a line.
        // Trailing spaces matter when someone copies text from the page.
        $esc = preg_replace('/\x20(?=\r|\n)/m', "&nbsp;", $esc);

        if($brTags)
        {
            // To add <br /> tags, we first normalize the line endings to \n
            // First convert Windows-style CRLF lines to \n
            $esc = str_replace("\r\n", "\n", $esc);
            // Then convert Mac-style CR lines to \n. Order matters here.
            // If we did this before replacing \r\n, we would replace \r\n with
            // \n\n, which will be two lines instead of one.
            $esc = str_replace("\r", "\n", $esc);
            // Then add a <br /> before each \n
            $esc = str_replace("\n", "<br />\n", $esc);
        }

        return $esc;
    }

    private static function tabsToSpaces($text, $tabWidth)
    {
        $spaces = "";
        $cursor = 0; 
        for($i = 0; $i < strlen($text); $i++)
        {
            if($text[$i] == "\t")
            {
                // Add spaces until the cursor position is divisible by 
                // $tabWidth, adding at least one space so that if $cursor
                // is already divisible by $tabWidth, we add $tabWidth spaces.
                $spaces .= " ";
                $cursor++;
                while($cursor % $tabWidth != 0)
                {
                    $spaces .= " ";
                    $cursor++;
                }
            }
            else
            {
                $spaces .= $text[$i];
                $cursor++;
                // Reset the cursor position to zero on CR or LF
                if($text[$i] == "\n" || $text[$i] == "\r")
                    $cursor = 0;
            }
        }
        return $spaces;
    }
}
?>
