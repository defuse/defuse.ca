<?php
    require_once('libs/HtmlEscape.php');
?>
<?php
    Upvote::render_arrows(
        "onlinehtmlsanitize",
        "defuse_pages",
        "HTML Escape Tool",
        "A tool for HTML-escaping text so that it looks and behaves exactly like it does in a text editor.",
        "https://defuse.ca/html-sanitize.htm"
    );
?>
<h1>Online HTML Escape Tool (htmlspecialchars, htmlentities)</h1>
<p>This tool will take your text and convert all the special characters to their proper HTML codes, so you can paste text with special characters or HTML code onto your website.
   It has been carefully designed so that the HTML produced by this tool looks and <em>behaves</em> exactly like the original text does in a text editor.
</p>

<?php
    if(isset($_POST['sanitize']))
    {
    ?>
        <p><strong>Your text has been escaped!</strong></p>
    <?
    }
    else
    {
    ?>
        <p><strong>Enter Your Text:</strong></p>
    <?
    }
?>


<form action="html-sanitize.htm" method="post">
    
    <textarea name="data" rows="15" cols="40" style="width:100%; margin-bottom: 10px;" >
<?php
if(isset($_POST['data']))
{

    $tabWidth = (int)$_POST['tw'];
    if($tabWidth >= 1)
    {
        $esc = HtmlEscape::escapeText($_POST['data'], 
                isset($_POST['br']) && $_POST['br'] == "yes", $tabWidth);
        echo htmlspecialchars($esc, ENT_QUOTES);
    }
    else
    {
        echo "Invalid tab width.";
    }
}
?></textarea><br />
    <input type="submit" name="sanitize" value="HTML-Escape Text" /> &nbsp;&nbsp;&nbsp;&nbsp;
    Tab width: <input type="text" name="tw" value="8" size="2" /> &nbsp;&nbsp;&nbsp;&nbsp;
    <input type="checkbox" name="br" value="yes" checked="checked" />Replace line endings with &lt;br /&gt;
</form>

<h2>How it works</h2>

<p>Escaping text is a bit tricky when you want it to look exactly the same in HTML as it would look in a text editor.
 The code this page uses to escape text is presented below. If you read the comments, you'll see that the solution is
 not as obvious as you might think!
</p>

<?php
    printSourceFile("libs/HtmlEscape.php", true);
?>

