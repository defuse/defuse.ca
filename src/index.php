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

require_once('libs/URLParse.php'); 
require_once('libs/phpcount.php');
require_once('libs/VimHighlight.php');
require_once('libs/Upvote.php');
require_once('libs/Bibliography.php');

Upvote::process_post(true);

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

function js_string_escape($data)
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

function action_alert()
{
    return;
?>
    <div id="actionalert">
        <span class="alcatch">
            Today is <a href="http://1984day.com/">1984 day</a>. Tell your
            government representatives that you oppose NSA spying!
        </span>
        <br />
        Canadians should write a letter to
            <a href="http://www.parl.gc.ca/Parlinfo/Compilations/HouseofCommons/MemberByPostalCode.aspx?Menu=HOC">their MP</a>.
    </div>
<?
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
    header('Strict-Transport-Security: max-age=31536000'); /* one year */
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
<script type="text/javascript" src="/js/upvote.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="/main.css" />
<link rel="stylesheet" media="all" type="text/css" href="/mainmenu.css" />
<link rel="stylesheet" media="all" type="text/css" href="/vimhl.css" />
<link rel="stylesheet" media="print" type="text/css" href="/print.css" />
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

<?php
    action_alert();
?>

<div class="menuminwidth0"><div class="menuminwidth1"><div class="menuminwidth2">
<div id="masthead">
    <div style="font-size:30px;"><img src="/images/1by1.gif" alt="Defuse Security Research and Development" /></div>
    <div id="sm">
        <a href="https://twitter.com/defusesec" title="Follow @DefuseSec on twitter!">
            <img id="twitterlogo" src="/images/twitter.png" alt="Follow me on twitter!" height="25" width="30" />
            Twitter
        </a>
        &nbsp;&middot;&nbsp;
        <a href="https://github.com/defuse" title="Defuse Security's GitHub">GitHub</a>
    </div>
</div>

<div class="menu">

<ul>
    <li class="headerlink" ><a href="/">Home<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul class="leftbutton">
            <li><a href="/about.htm">&nbsp;About Defuse</a></li>
            <li><a href="/resume.htm">&nbsp;My R&eacute;sum&eacute;</a></li>
            <li><a href="/hypothetico/">&nbsp;Hypothetico</a></li>
            <li><a href="https://twitter.com/#!/defusesec" >&nbsp;Twitter</a></li>
            <li><a href="https://github.com/defuse" >&nbsp;GitHub</a></li>
            <li><a href="https://defuse.ca/blog/" >&nbsp;Blog</a></li>

            <li><a href="/contact.htm">&nbsp;Contact</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/services.htm">Services<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="/pastebin.htm">&nbsp;Encrypted Pastebin</a></li>
            <li><a href="/trustedthirdparty.htm">&nbsp;TRENT - Trusted RNG</a></li>
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
            <li><a href="/sockstress.htm">&nbsp;Sockstress</a></li>
            <li><a href="/backup-verify-script.htm">&nbsp;Backup Checker</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Projects &amp; Code<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="/sockstress.htm">&nbsp;Sockstress</a></li>
            <li><a href="/gnutls-psk-client-server-example.htm">&nbsp;GnuTLS PSK Example</a></li>
            <li><a href="/secure-php-encryption.htm">&nbsp;Encryption in PHP</a></li>
            <li><a href="/email-spoofing-in-ruby.htm">&nbsp;Ruby Email Spoofing</a></li>
            <li><a href="/syntax-highlighting-in-php-with-vim.htm">&nbsp;PHP &amp; Vim Highlighting</a></li>
            <li><a href="/php-hit-counter.htm">&nbsp;PHP Hit Counter</a></li>
            <li><a href="/generating-random-passwords.htm">&nbsp;PHP Random Passwords</a></li>
            <li><a href="/php-pbkdf2.htm">&nbsp;PBKDF2 For PHP</a></li>
            <li><a href="/force-print-background.htm">&nbsp;Force Print BG Color</a></li>
            <li><a href="/passwordbolt.htm">&nbsp;Password Bolt [BETA]</a></li>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Research<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="#"><span class="drop"><span>Vulns &amp; Exploits</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/flush-reload-side-channel.htm">&nbsp;FLUSH+RELOAD</a></li>
                    <li><a href="/exploiting-cpp-vtables.htm">&nbsp;C++ VTABLES</a></li>        
                    <li><a href="/cracking-synergy-bad-cryptography.htm">&nbsp;Cracking Synergy</a></li>
                    <li><a href="/mitigating-breach-tls-attack-in-php.htm">&nbsp;PHP BREACH Defense</a></li>
                    <li><a href="/race-conditions-in-web-applications.htm">&nbsp;Race Conditions</a></li>        
                    <li><a href="/filesystem-events-ntfs-permissions.htm">&nbsp;File-System Events</a></li>
                    <li><a href="/onedetection.htm">&nbsp;PUP Confusion</a></li>
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>
            <li><a href="#"><span class="drop"><span>Crypto</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/cbcmodeiv.htm">&nbsp;CBC Mode IV Security</a></li>
                    <li><a href="/eotp.htm">&nbsp;EOTP</a></li>
                    <li><a href="/truecrypt-plausible-deniability-useless-by-game-theory.htm">&nbsp;TrueCrypt &amp; Game Theory</a></li>        
                    <li><a href="/web-browser-javascript-cryptography.htm">&nbsp;Browser Cryptography</a></li>        
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>        
            <li><a href="#"><span class="drop"><span>Passwords</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/password-hashing-after-phc.htm">&nbsp;Hash Caching<a></li>
                    <li><a href="/passwordrestrictions.htm">&nbsp;Password Restrictions</a></li>        
                    <li><a href="/password-policy-hall-of-shame.htm">&nbsp;Password Hall of Shame</a></li>        
                    <li><a href="http://crackstation.net/hashing-security.html">&nbsp;Salted Password Hashing</a></li>
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>        
            <li><a href="#"><span class="drop"><span>Other</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <ul>
                    <li><a href="/web-application-security.htm">&nbsp;Website Security</a></li>        
                    <li><a href="/bitcoin-pool-ddos.htm">&nbsp;BitCoin Centralization</a></li>
                    <li><a href="/microsoft-reads-your-skype-messages.htm">&nbsp;Skype Spying</a></li>        
                </ul>

                <!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>        
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </li>
</ul>

<ul>
    <li class="headerlink" ><a href="/projects.htm">Miscellaneous<img class="downimg" src="/images/downarrow.gif" alt="&#9660;"/><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
        <ul>
            <li><a href="/reading-list.htm">&nbsp;Reading List</a></li>
            <li><a href="/known-unknowns.htm">&nbsp;Known Unknowns</a></li>
            <li><a href="/writing-tips.htm">&nbsp;Writing Tips</a></li>
            <li><a href="/x-plane-combat.htm">&nbsp;X-Plane Combat</a></li>
            <li><a href="/the-universe-is-made-of-cheese.htm">&nbsp;Cheese Universe</a></li>
            <li><a href="/vimrc.htm">&nbsp;My Vim Configuration</a></li>
            <li><a href="/asuskeyboarddefect.htm">&nbsp;ASUS G51 Keyboard</a></li>
            <li><a href="/honestyware.htm">&nbsp;Honestyware</a></li>
            <li><a href="/online-free-computer-science-education.htm">&nbsp;CS Education</a></li>
            <li><a href="/rules-for-working-with-me.htm">&nbsp;Working With Me</a></li>
            <li><a href="/contributors.htm">&nbsp;Contributors</a></li>
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

<div style="padding: 10px; border-bottom: solid #CC0000 5px; text-align: center; font-size: 16pt;">
<b>News:</b>
I'm crowdfunding <a href="http://www.indiegogo.com/projects/better-web-forums">a
better web forum</a>. Please contribute if you can.
</div>

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
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0; vertical-align: bottom;" src="/images/cc-by-sa.png" /></a>
         <a href="/about.htm">Defuse Security</a> | 
        <a href="/pastebin.htm">Secure Pastebin</a> | 
        <a href="https://github.com/defuse/defuse">Source Code</a> |
        <a href="http://crackstation.net/" rel="external">&raquo; CrackStation</a>
        </div> <!-- end footer -->
        </div> <!-- footerwrapper -->
        <?
    }
?>
</body>
</html>
