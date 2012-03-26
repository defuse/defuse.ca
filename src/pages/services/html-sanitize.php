<h1>Online HTML Sanitize Tool (htmlspecialchars, htmlentities)</h1>
<p>This tool will take your text and convert all the special characters to their proper HTML codes, so you can paste text with special characters or HTML code onto your website.</p>

<?php
    if(isset($_POST['sanitize']))
    {
    ?>
        <p><span style="background-color: #AAFFAA; width:400px; padding:5px; border: solid black 1px;">Your Text Has Been Sanitized!</span></p>
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
    
    <textarea name="data" rows="30" cols="40" style="width:100%; margin-bottom: 10px;" >
<?php
if(isset($_POST['data']))
{
    require_once('libs/HtmlEscape.php');

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
    <input type="submit" name="sanitize" value="HTML-Sanitize Text" /> &nbsp;&nbsp;&nbsp;&nbsp;
    Tab width: <input type="text" name="tw" value="8" size="2" /> &nbsp;&nbsp;&nbsp;&nbsp;
    <input type="checkbox" name="br" value="yes" checked="checked" />Replace line endings with &lt;br /&gt;
</form>

