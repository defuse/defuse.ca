<?php
/*==============================================================================

        Defuse Security's Secure & Lightweight CMS in PHP for Linux.

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

    This CMS is heavily dependant upon GRC's Script-Free Menuing System:
                http://www.grc.com/menudemo.htm
    
==============================================================================*/

require_once('libs/URLParse.php'); 
require_once('libs/phpcount.php');
require_once('libs/VimHighlight.php');

function printHlString($text, $ft, $numbers = false) {
    $hl = new VimHighlight();
    $hl->caching = true;
    $hl->color_scheme = "dw_cyan";
    $hl->show_lines = $numbers;
    $hl->use_css = true;
    $hl->file_type = $ft;
    $hl->setVimCommand("vim");
    echo '<div class="vimhighlight">' . $hl->processText($text, true) . '</div>';
}

function printSourceFile($path, $numbers = false) {
    $hl = new VimHighlight();
    $hl->caching = true;
    $hl->color_scheme = "dw_cyan";
    $hl->show_lines = $numbers;
    $hl->use_css = true;
    $hl->setVimCommand("vim");
    echo '<div class="vimhighlight">' . $hl->processFile($path, true) . '</div>';
}

// Standardize the times & dates to UTC because people don't live in the same timezone as the server.
date_default_timezone_set("UTC"); 

//Strengthen the server's CSPRNG
$entropy = implode(gettimeofday()) . implode($_SERVER) . implode($_GET) . implode($_POST) . implode($_COOKIE) . implode($_ENV) . microtime() . mt_rand() . mt_rand();
file_put_contents("/dev/urandom", $entropy);

$name = URLParse::ProcessURL();

if($name == "passgen")
{
    header('Expires: Mon, 01 Jan 1990 00:00:00 GMT');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
}

// HSTS header (force HTTPS)
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' &&
    $_SERVER['HTTP_HOST'] != "localhost" && 
    $_SERVER['HTTP_HOST'] != "192.168.1.102")
{
    header('Strict-Transport-Security: max-age=604800'); /* 7 days */
}

// Prevent pages from being displayed in iframes. Not supported by all browsers.
header('X-Frame-Options: SAMEORIGIN'); 

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title><?php 
            $title = URLParse::getPageTitle($name);
            echo htmlspecialchars($title, ENT_QUOTES);
?></title>
<meta name="description" content="<?php 
            $metd = URLParse::getPageMetaDescription($name);
            echo htmlspecialchars($metd, ENT_QUOTES);
?>" />
<meta name="keywords" content="<?php 
            $metk = URLParse::getPageMetaKeywords($name);
            echo htmlspecialchars($metk, ENT_QUOTES);
?>" />
<meta name="google-site-verification" content="LjgndE9fyTkxbPz8aMFyJQFSS3cQiXIrYchE_b2VXlg" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" media="all" type="text/css" href="/mainmenu2.css" />
<link rel="stylesheet" media="all" type="text/css" href="/main.css" />
<link rel="stylesheet" media="all" type="text/css" href="/vimhl.css" />
<!--[if !IE 7]>
	<style type="text/css">
		#wrap {display:table;height:100%}
	</style>
<![endif]-->
</head>
<body <?php if( $name == "" ) echo 'style="background:white;" '; ?> >
<div id="wrap">

<!-- This menuing system was made by Steve Gibson at GRC.COM 
            see more at http://www.grc.com/menudemo.htm -->

<div class="menuminwidth0"><div class="menuminwidth1"><div class="menuminwidth2">
<div id="masthead">
    <div style="font-size:30px;"><img src="/images/1by1.gif" alt="Defuse Security Research and Development" /></div>
    <div style="text-align:center; position: absolute; top: 10px; right: 0px;">
        <a href="https://www.eff.org/pages/blue-ribbon-campaign">
            <img src="/images/br.gif" alt="Join the Blue Ribbon Online Free Speech Campaign" />
        </a>
    </div>
</div>

<div class="menu">

<ul>
    <li class="headerlink" ><a href="/">Home<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul class="leftbutton">
            <li><a href="/about.htm">&nbsp;About Defuse</a></li>
            <li><a href="/resume.htm">&nbsp;My R&eacute;sum&eacute;</a></li>
            <!-- <li><a href="/projects.htm">&nbsp;Project List</a></li> -->
            <li><a href="https://twitter.com/#!/defusesec" >&nbsp;Twitter</a></li>
            <li><a href="https://defuse.ca/blog/" >&nbsp;Blog</a></li>

            <li><a href="/contact.htm">&nbsp;Contact</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/services.htm">Services<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <!--<li><a href="/softwaredevelopment.htm">&nbsp;Software Development</a></li>-->
            <!--<li><a href="/webdevelopment">&nbsp;Web Development</a></li>-->
            <!--<li><a href="/peerreview.htm">&nbsp;Peer Review &amp; Testing</a></li>-->

            
            <li><a href="/pastebin.htm">&nbsp;Encrypted Pastebin</a></li>
            <li><a href="/trustedthirdparty.htm">&nbsp;TRENT - Trusted RNG</a></li>
            <li><a href="/pdfcleaner.htm">&nbsp;PDFCleaner</a></li>

            <li><a href="/big-number-calculator.htm">&nbsp;Big Number Calculator</a></li>
            <li><a href="/online-x86-assembler.htm">&nbsp;Online x86 Assembler</a></li>
            <li><a href="/checksums.htm">&nbsp;Checksum Calculator</a></li>
            <li><a href="/html-sanitize.htm">&nbsp;HTML Sanitizer</a></li>

            <li><a href="http://crackstation.net/">&nbsp;CrackStation.net</a></li>

        </ul>
    <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Software<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="/passgen.htm">&nbsp;Password Generator</a></li>
            <li><a href="/helloworld-cms.htm">&nbsp;HelloWorld! Secure CMS</a></li>
            <li><a href="/php-hash-cracker.htm">&nbsp;PHP Hash Cracker</a></li>
            <!--<li><a href="/sockstress.htm">&nbsp;Sockstress</a></li>-->
            <li><a href="/backup-verify-script.htm">&nbsp;Backup Checker</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Projects &amp; Code<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="/secure-php-encryption.htm">&nbsp;Encryption in PHP</a></li>
            <li><a href="/email-spoofing-in-ruby.htm">&nbsp;Ruby Email Spoofing</a></li>
            <li><a href="/syntax-highlighting-in-php-with-vim.htm">&nbsp;PHP &amp; Vim Highlighting</a></li>
            <li><a href="/php-hit-counter.htm">&nbsp;PHP Hit Counter</a></li>
            <li><a href="/generating-random-passwords.htm">&nbsp;PHP Random Passwords</a></li>
            <li><a href="/php-pbkdf2.htm">&nbsp;PBKDF2 For PHP</a></li>
            <li><a href="/force-print-background.htm">&nbsp;Force Print BG Color</a></li>
            <!--<li><a href="/csharpthreadlibrary.htm">&nbsp;PolyThread</a></li>-->
            <li><a href="/passwordbolt.htm">&nbsp;Password Bolt [BETA]</a></li>
            <!--<li><a href="/gpucrack.htm">&nbsp;GPU MD5+Salt Cracker</a></li>-->
            <!--<li><a href="/simppsk.htm">&nbsp;SimpPSK</a></li>-->
            <!--<li><a href="/blowfish.htm">&nbsp;Blowfish C#/C++</a></li>-->
            <!--<li><a href="/blowfish.htm">&nbsp;BLOWFISH C# &amp; C++</a></li>-->
            
            <!--<li><a href="wordlists.htm">&nbsp;WordLists</a></li>-->
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Research<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="#"><span class="drop"><span>Security</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/web-browser-javascript-cryptography.htm">&nbsp;Browser Cryptography</a></li>        
                    <li><a href="/race-conditions-in-web-applications.htm">&nbsp;Race Conditions</a></li>        
                    <li><a href="/passwordrestrictions.htm">&nbsp;Password Restrictions</a></li>        
                    <li><a href="/password-policy-hall-of-shame.htm">&nbsp;Password Hall of Shame</a></li>        
                    <li><a href="/web-application-security.htm">&nbsp;Website Security</a></li>        
                    <li><a href="/filesystem-events-ntfs-permissions.htm">&nbsp;File-System Events</a></li>
                    <li><a href="/onedetection.htm">&nbsp;PUP Confusion</a></li>
                    <li><a href="/bitcoin-pool-ddos.htm">&nbsp;BitCoin Centralization</a></li>
                    <li><a href="/softwaresecurity.htm">&nbsp;Software Security</a></li>
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>
            <li><a href="#"><span class="drop"><span>Crypto</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/eotp.htm">&nbsp;EOTP</a></li>
                    <li><a href="/cbcmodeiv.htm">&nbsp;CBC Mode IV Security</a></li>
                    <li><a href="http://crackstation.net/hashing-security.html">&nbsp;Salted Password Hashing</a></li>
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>        
            <li><a href="/notes/">&nbsp;Notes</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Miscellaneous<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <!--<li><a href="wordlists.htm">&nbsp;WordLists</a></li>-->
            <li><a href="/vimrc.htm">&nbsp;My Vim Configuration</a></li>
            <li><a href="/asuskeyboarddefect.htm">&nbsp;ASUS G51 Keyboard</a></li>
            <li><a href="/honestyware.htm">&nbsp;Honestyware</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>


</div> <!-- close "menu" div -->
<hr style="display:none" />
</div></div></div> <!-- close the "minwidth" wrappers -->
<!-- End of menu -->

<!--[if !IE]>-->
<div id="undergrad"></div>
<!--<![endif]-->

<!--[If gt IE 6]>
<div id="undergrad"></div>
<![endif]-->

<?php
    if($name != "")
        echo '<div id="content" >';
    else
        echo '<div id="contenthome" >';

    $included = URLParse::IncludePageContents();

    //TODO: sometime change this to use the name instead of the path.
    PHPCount::AddHit($included);
?>

</div>
</div> <!-- Wrap -->
<?php
    if($name != "")
    {
        ?>
        <div id="footerwrapper">
        <div id="footerborder"></div>
        <div id="footer">

        <div style="float: right;">
        <table>
            <tr>
                <th>Your IP:</th>
                <td>
                <?php
                    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                    {
                        echo htmlentities($_SERVER['HTTP_X_FORWARDED_FOR'], ENT_QUOTES); 
                    }
                    else
                    {
                        echo htmlentities($_SERVER['REMOTE_ADDR'], ENT_QUOTES); 
                    }
                ?>
                </td>
            </tr>
            <tr>
                <th>DNT Header:&nbsp;&nbsp;&nbsp;</th>
                <?php
                    $dnt = isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == "1";
                    if($dnt)
                    {
                        echo "<td><span style=\"color: #00FF00\">Enabled</span></td>";
                    }
                    else
                    {
                        echo "<td>Disabled</td>";
                    }
                ?>
            </tr>
        </table>
        </div>

        <?
            $last_modified = htmlspecialchars(
                                    date("F j, Y, g:ia e", filemtime($included)),
                                    ENT_QUOTES
                                    );
            $unique =  PHPCount::GetHits($included, true);
            $hits = PHPCount::GetHits($included);
            $total = PHPCount::GetTotalHits();
            $totalu = PHPCount::GetTotalHits(true);
        ?>
        <table>
            <tr>
                <th>Last Modified: &nbsp;&nbsp;</th>
                <td><?php echo $last_modified;?></td>
            </tr>
            <tr>
                <th>Page Hits:</th>
                <td><?php echo $hits; ?></td>
            </tr>
            <tr>
                <th>Unique Hits:</th>
                <td><?php echo $unique; ?></td>
            </tr>
        </table>
        Copyright &copy; 2012 <a href="/about.htm">Defuse Security</a> | 
        <a href="/pastebin.htm">Secure Pastebin</a> | 
        <a href="/passgen.htm">Password Generator</a> |
        <a href="http://crackstation.net/" rel="external">&raquo; CrackStation</a>
        </div> <!-- end footer -->
        </div> <!-- footerwrapper -->
        <?
    }
?>
</body>
</html>
