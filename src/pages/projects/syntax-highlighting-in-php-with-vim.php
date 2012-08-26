<h1>Using the <i>Vim</i> Editor for Syntax Highlighting in PHP</h1>

<p>
The <a href="http://www.vim.org/">Vim</a> text editor supports many different
file formats and color schemes, so I wanted to see if I could harness its syntax
highlighting power in PHP. The following script is the result of that effort.
</p>

<p>
To use it, the user that runs the PHP script (e.g. www-data on a default Debian
installation) will need to have access to the 'vim' command (and optionally, if
you want better color support, 'gvim' and 'Xvfb'). Any extra color schemes will
have to be added either to the master Vim config folder, or to the runner's
~/.vim/ folder.
</p>

<p>
Once set up, using it is simple:
</p>

<?php
$source = <<<EOC
<?php
    require_once('VimHighlight.php');
    \$hl = new VimHighlight();
    \$hl->caching = true;
    \$hl->color_scheme = "jellybeans";
    \$hl->show_lines = true;
    \$hl->use_css = false;
    \$hl->setVimCommand("vim");
    echo \$hl->processFile("file_to_process.php", true);
?>
EOC;

printHlString($source, "php", false);
?>

<p>Here's the script (<a href="/source/VimHighlight.php">download</a>):</p>

<?php
    printSourceFile('source/VimHighlight.php', true);
?>
