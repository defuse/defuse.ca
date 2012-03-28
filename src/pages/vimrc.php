<h1>My .vimrc</h1>

<div class="code">
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/vimrc");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>
