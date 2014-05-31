<?php
    Upvote::render_arrows(
        "truecrypthashes",
        "defuse_pages",
        "Hashes of TrueCrypt Version 7.1a Files",
        "Hashes of all files from the last version of TrueCrypt",
        "https://defuse.ca/truecrypt-7.1a-hashes.htm"
    );
?>
<div class="pagedate">
May 31, 2014
</div>
<h1>TrueCrypt 7.1a Hashes</h1>

<p>
Here are the SHA256, SHA1, and MD5 hashes of all TrueCrypt version 7.1a files.
The signature of the list can be verified with <a href="/contact.htm">my public
key.</a> The <kbd>TrueCrypt_v7.1a.zip</kbd> file is <a
href="https://www.grc.com/misc/truecrypt/truecrypt.htm">GRC's archive of all
TrueCrypt v7.1a materials</a>.
</p>

<p style="text-align: center;">
    <a href="/downloads/truecrypt-hashes.asc">
    <strong>Download TrueCrypt v7.1a Hashes</strong>
    </a>
</p>

<?php
    printSourceFile("downloads/truecrypt-hashes.asc");
?>
