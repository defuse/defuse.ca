<?php
    Upvote::render_arrows(
        "syntaxhighlightingvim",
        "defuse_pages",
        "Using the Vim Editor for Syntax Highlighting in PHP",
        "Generate syntax-highlighted code automatically in PHP with Vim.",
        "https://defuse.ca/syntax-highlighting-in-php-with-vim.htm"
    );
?>
<h1>Using the <i>Vim</i> Editor for Syntax Highlighting in PHP</h1>

<p>
The <a href="http://www.vim.org/">Vim</a> text editor supports many different
file formats and color schemes, so I wanted to see if I could harness its syntax
highlighting power in PHP. The following script is the result of that effort.
</p>

<p>
To use it, the user that runs the PHP script (e.g. www-data on a default
Debian installation) will need to have access to the 'vim' command (and
optionally, if you want better color support, 'gvim' and 
'<a href="https://secure.wikimedia.org/wikipedia/en/wiki/Xvfb">Xvfb</a>').
Any extra color schemes will have to be added either to the master Vim config
folder, or to the runner's ~/.vim/ folder. 
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

<h2>Download</h2>

<p>
You can <a href="https://github.com/defuse/vimhl">find the PHP Vim Syntax
Highlighting script on GitHub</a>.
</p>

<p>
<b>Note:</b> This script has recently been <a
href="https://github.com/defuse/vimhl/commit/93a531f0a16e5f37937d9e3697f4e39eccefd3cc">updated</a>
to work with Vim 7.4. Also note that Vim 7.4 uses a different class name for the
line numbers. It now uses ".LineNr" instead of ".lnr". Update your CSS file
accordingly. 
</p>
