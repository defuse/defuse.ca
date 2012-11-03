<?php
/*==============================================================================

        Defuse Security's Secure & Lightweight CMS for PHP.

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

    <style type="text/css">
    body {
        background-color: silver;
        color: black;
    }
    h1 {
        text-align: center;
    }
    #pagecontent {
        border: solid black 1px;
        padding: 20px;
        margin: 20px;
        background-color: white;
    }
    #navbar {
        text-align: center;
        padding: 10px;
        margin: 20px;
        border: solid black 1px;
        background-color: white;
    }
    #navbar a{
        margin-right: 20px;
        font-weight: bold;
    }
    #powered {
        text-align: center;
        padding: 20px;
    }
    </style>
</head>
<body>
    <h1>Defuse.ca's HelloWorld! &quot;CMS&quot;</h1>

    <div id="navbar">
        <a href="/">Home</a>
        <a href="/about.htm">About</a>
        <a href="/skadlfjasdklfjsdklaf">404 page</a>
    </div>

    <div id="pagecontent">
        <?php
            URLParse::IncludePageContents();
        ?>
    </div>

    <div id="powered">
        Powered by 
            <a href="https://defuse.ca/helloworld-cms.htm">
                Defuse.ca's HelloWorld! CMS
            </a>
    </div>
</body>
</html>
