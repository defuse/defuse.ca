<?php
/*==============================================================================

        Defuse Cyber-Security's Secure & Lightweight CMS in PHP for Linux.
        Setup & Usage Instructions: https://defuse.ca/helloworld-cms.htm

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.

==============================================================================*/

require_once('libs/URLParse.php'); 

$name = URLParse::ProcessURL();

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

    <link rel="stylesheet" media="all" type="text/css" href="/hypothetico/style.css" />
</head>
<body>

    <div id="header">
        <img src="/hypothetico/hypothetico-logo.png" alt="HYPOTHETICO" />
    </div>

    <div id="navbar">
        <a href="/hypothetico/">Home</a>
        <a href="/hypothetico/issues.htm">Issues</a>
        <a href="/hypothetico/submit.htm">Submit</a>
    </div>

    <?php
        URLParse::IncludePageContents();
    ?>


<div id="license">
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0; vertical-align: bottom;" src="/images/cc-by-sa.png"/></a>
</div>

</body>
</html>
