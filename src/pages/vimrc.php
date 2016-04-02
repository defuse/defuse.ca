<?php
    Upvote::render_arrows(
        "myvimrc",
        "defuse_pages",
        "My Vim Configuration",
        "My configuration of the Vim text and code editor.",
        "https://defuse.ca/vimrc.htm"
    );
?>
<h1>My .vimrc</h1>

<p>
    This page is updated with the .vimrc on my PC at home every midnight.
    The file can be downloaded without formatting <a href="/upload/vim/vimrc">here</a>.
    The color scheme I use for gVim is
    <a href="https://vimcolorschemetest.googlecode.com/svn/colors/dw_cyan.vim">dw_cyan</a>, and I
    use <a href="https://github.com/nanotech/jellybeans.vim">jellybeans</a> for command-line Vim.
</p>

<p>
    Download my entire .vim directory <a href="/upload/vim/vim.zip">here</a>.
</p>

<p>
    You can also find my entire <a href="https://github.com/defuse/vim">Vim configuration on
    GitHub</a>.
</p>

<p>
   If you would like a list of my favourite .vimrc lines, see my blog post
  <a href="https://defuse.ca/blog/random-vimrc-tips.html">Random vimrc tips</a>.
</p>

<h2>Screenshots</h2>
<p style="text-align: center;"><i>These screenshots are not updated automatically, and may be out of date.</i></p>
<center>
<img src="/images/vim.png" alt="My Vim configuration (gVim)"/> <br /><br />
<img src="/images/vim-cli.png" alt="My Vim configuration (command-line Vim)"/>
</center>

<h2>Synchronization Script</h2>

<?php
    printSourceFile("source/vimupdate.sh");
?>

<h2>~/.vimrc</h2>

<?php
    printSourceFile("/var/www/defuse.ca-extras/upload/vim/vimrc");
?>
