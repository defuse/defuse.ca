<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Temporary SecurityNow! Gateway</title>
<style type="text/css">
* {
    padding: 0; margin: 0; border: 0;
}

body {
    background-color: #550000;
}

h1 {
    font-size: 16pt;
    text-align: center;
    padding-bottom: 5px;
}

h2 {
    font-size: 12pt;
    text-align: center;
    padding-bottom: 5px;
}

h3 {
    text-align: center;
    font-size: 12pt;
    margin-top: 20px;
}

h4 {
    font-weight: normal;
    font-size: 8pt;
    text-align: center;
    margin-bottom: 10px;
}

p {
    margin: 5px;
    font-size: 8pt;
}

#squarediv {
    width: 500px;
    margin: 0 auto;
    background-color: #F1EAD7;
    margin-top: 20px;
    padding: 10px;
    border: solid black 5px;
}

.links {
    font-size: 8pt;
    text-align: center;
    margin: 0 auto;
}

.links td {
    padding: 20px;
}

.links td:hover {
    background-color: #FFFFFF;
    border: solid black 1px;
    padding: 19px; /* compensate for border, so the box stays the same size*/
}

.ln {
    width: 100%;
    height: 100%;
}

.links a {
    text-decoration: none;
    color: black;
    display: block;
    height: 100%;
    width: 100%;
}

#footer {
    color: #404040;
    margin-top: 20px;
}

#footer a {
    color: #202020;
}
</style>
</head>
<body>

<?php
if(isset($_GET['e']))
{
    $number = (int)$_GET['e'];
}
else
{
    $number = 1;
}

if($number <= 0)
    $number = 1;

require_once('sndb/SNDB.php');

$ep = new SNDB($number);
?>
    <div id="squarediv">
    <h1>SecurityNow! Episode #<?php echo htmlentities($ep->getEpNumber(), ENT_QUOTES); ?>:</h1>
    <h2><?php echo htmlentities($ep->getTitle(), ENT_QUOTES); ?></h2>
    <h4><strong>Date: </strong><?php echo htmlentities($ep->getDate(), ENT_QUOTES);?> &nbsp;&nbsp;&nbsp;&nbsp; 
            <strong>Running Time: </strong><?php echo htmlentities($ep->getRunTime(), ENT_QUOTES); ?></h4>

    <table class="links">
        <tr>
            <td>
                <a href="<?php echo htmlentities($ep->getHQLink(), ENT_QUOTES); ?>">
                    <div class="ln">
                        <img src="/images/shq.png" />
                        <br />
                        High Quality MP3
                    </div>
                </a>
            </td>
            <td>
                <a href="<?php echo htmlentities($ep->getLQLink(), ENT_QUOTES); ?>">
                    <div class="ln">
                        <img src="/images/slq.png" />
                        <br />
                        Low Quality MP3
                    </div>
                </a>
            </td>
        </tr>
    </table>

    <h3>Transcript</h3>

    <table class="links">
        <tr>
            <td>
                <a href="<?php echo htmlentities($ep->getHTMLLink(), ENT_QUOTES); ?>">
                    <div class="ln">
                        HTML Transcript
                    </div>
                </a>
            </td>
            <td>
                <a href="<?php echo htmlentities($ep->getPDFLink(), ENT_QUOTES); ?>">
                    <div class="ln">
                        PDF Transcript
                    </div>
                </a>
            </td>
            <td>
                <a href="<?php echo htmlentities($ep->getPlainLink(), ENT_QUOTES); ?>">
                    <div class="ln">
                        Plain Text Transcript
                    </div>
                </a>
            </td>
        </tr>
    </table>

    <p>
    <strong>Description:</strong>
    <?php echo $ep->getDescription(); ?>
    </p>

    <p id="footer">SecurityNow! is a netcast produced by <a href="http://twit.tv/">TWiT</a> and Steve Gibson of <a href="https://www.grc.com/intro.htm">GRC</a>. Find the full list of episodes <a href="https://www.grc.com/securitynow.htm">here</a>. This page is hosted by <a href="https://defuse.ca/">defuse.ca</a> and is not endorsed by, or affiliated with, TWiT or GRC.
    </div>

</body>
</html>
