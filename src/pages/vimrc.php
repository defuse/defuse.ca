<h1>My .vimrc</h1>

<p>
    This page is updated with the .vimrc on my PC at home every midnight.
    The file can be downloaded without formatting <a href="/source/vimrc">here</a>.
    The color scheme I use is
    <a href="https://vimcolorschemetest.googlecode.com/svn/colors/dw_cyan.vim">dw_cyan</a>.
</p>

<p>
    Download my entire .vim directory <a href="/source/vim.zip">here</a>.
</p>

<h2>Synchronization Script</h2>

<div class="code">
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/vimupdate.sh");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>

<h2>~/.vimrc</h2>

<div class="code">
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/vimrc");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>
