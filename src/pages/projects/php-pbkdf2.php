<h1>PBKDF2 For PHP</h1>
<p>PBKDF2 (Password Based Key Derivation Function) for PHP. The following code is public domain. Feel free to use it for any purpose. The code complies with test vectors at <a href="https://www.ietf.org/rfc/rfc6070.txt">https://www.ietf.org/rfc/rfc6070.txt</a>. Performance improvements were provided by <a href="http://www.variations-of-shadow.com">http://www.variations-of-shadow.com</a>.</p>

<div style="text-align: center; padding: 20px;">
    <a href="/source/pbkdf2.php"><img src="/images/download.png" alt="Download" /></a>
</div>

<div class="code" >
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/pbkdf2.php");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>
