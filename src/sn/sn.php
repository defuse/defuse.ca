<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Temporary SecurityNow! Gateway</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
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

try {
    $ep = new SNDB($number);
}
catch(Exception $e)
{
    $ep = new SNDB(1);
}
?>
    <div id="squarediv">
    <h1><a href="https://www.grc.com/securitynow.htm">SecurityNow!</a> Episode #<?php echo htmlentities($ep->getEpNumber(), ENT_QUOTES); ?></h1>
    <h2><?php echo $ep->getTitle(); ?></h2>
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
