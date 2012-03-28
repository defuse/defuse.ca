<h1>My .vimrc</h1>

<p>
    This page is updated with the .vimrc on my PC at home every midnight.
    The color scheme I use is
    <a href="https://vimcolorschemetest.googlecode.com/svn/colors/dw_cyan.vim">dw_cyan</a>.
</p>

<div class="code">
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/vimrc");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>
