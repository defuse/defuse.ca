<?php
class HtmlEscape
{
    public static function escapeText($text, $brTags, $tabWidth)
    {
        // Escape all characters that have a special meaning in HTML
        $esc = htmlspecialchars($text, ENT_QUOTES);

        // Replace tabs with spaces
        $esc = str_replace("\t", str_repeat(" ", $tabWidth), $esc);

        // Replace repeated spaces with &nbsp;
        //      This is tricky. Spaces cannot simply be replaced with &nbsp;
        //      because the line of text will not break, so we have to leave
        //      normal spaces in between pairs of &nbsp; to let the line break.
        //      The space must come before the &nbsp; because we want three
        //      spaces in a row to turn into " &nbsp; " not "&nbsp;  " (which
        //      will look like two spaces in the browser.
        $esc = str_replace("  ", " &nbsp;", $esc);

        // HTML ignores leading spaces in elements like <p> and <div> so we 
        // have to replace spaces at the beginning of the line with an &nbsp;
        // 0x20 = ASCII SPACE
        $esc = preg_replace('/^\x20/m', "&nbsp;", $esc);

        if($brTags)
        {
            // To add <br> tags, we first normalize the line endings to \n
            // First convert Windows-style CRLF lines to \n
            $esc = str_replace("\r\n", "\n", $esc);
            // Then convert Mac-style CR lines to \n. Order matters here.
            // If we did this before replacing \r\n, we would replace \r\n with
            // \n\n, which will be two lines instead of one.
            $esc = str_replace("\r", "\n", $esc);
            // Then add a <br /> after each \n
            $esc = str_replace("\n", "<br />\n", $esc);
            // Note: We can't just replace \r with \n because it will convert
            // \r\n (one line in Windows) to <br />\n<br />\n (two lines).
        }

        return $esc;
    }
}
?>
