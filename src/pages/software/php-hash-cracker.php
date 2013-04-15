<?php
    Upvote::render_arrows(
        "phphashcracker",
        "defuse_pages",
        "Salted Hash Cracking PHP Script",
        "A script for cracking hashes when all you have is PHP.",
        "https://defuse.ca/php-hash-cracker.htm"
    );
?>
<h1>Salted Hash Cracking PHP Script</h1>

<p>
The following is a PHP script for running dictionary attacks against both salted and unsalted password hashes. It is capable of attacking every hash function supported by PHP's <a href="http://php.net/hash">hash</a> function, as well as md5(md5), LM, NTLM, MySQL 4.1, and crypt hashes. It also supports crashed session recovery.
</p>

<h2>Command-Line Options</h3>

<div class="code">
PHP Hash Cracker v1.1: https://defuse.ca/php-hash-cracker.htm<br />
Usage: php crack.php &lt;arguments&gt;<br />
Arguments:<br />
&nbsp;&nbsp; &nbsp;-w &lt;wordlist&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Wordlist or &quot;stdin&quot; for standard input.<br />
&nbsp;&nbsp; &nbsp;-s &lt;start line number&gt; &nbsp;Skip lines of the wordlist.<br />
&nbsp;&nbsp; &nbsp;-o &lt;output file&gt; &nbsp; &nbsp; &nbsp; &nbsp;Save session/results to file.<br />
&nbsp;&nbsp; &nbsp;-f &lt;output file&gt; &nbsp; &nbsp; &nbsp; &nbsp;Recover crashed session.<br />
&nbsp;&nbsp; &nbsp;-c &lt;hash&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; The hash to crack.<br />
&nbsp;&nbsp; &nbsp;-t &lt;hash type&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;The type of hash.<br />
&nbsp;&nbsp; &nbsp;-l &lt;left salt&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Salt prepended to the password.<br />
&nbsp;&nbsp; &nbsp;-r &lt;right salt&gt; &nbsp; &nbsp; &nbsp; &nbsp; Salt appended to the password.<br />
&nbsp;&nbsp; &nbsp;-d &lt;s&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Hash &lt;s&gt; with all supported hash types.<br />
&nbsp;&nbsp; &nbsp;-h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Print help message.<br />
** All other arguments are ignored when using -f or -d **<br />
</div>



<h2>Sample Output</h2>
<div class="code">
$ php crack.php -w small.lst -c 2c5419e6db59f283bcbb501c722e73c6 -t md5 -l a8f0h2 -r 8hf27<br />
Defuse Security&#039;s Hash Cracking Script - v1.1<br />
Homepage: https://defuse.ca/php-hash-cracker.htm<br />
<br />
Begin execution: March 17, 2012, 8:31 pm <br />
Wordlist: small.lst <br />
Start line: 0 <br />
Hash: 2c5419e6db59f283bcbb501c722e73c6 <br />
Hash type: md5 <br />
Left salt: a8f0h2 <br />
Right salt: 8hf27 <br />
<br />
Current Line: 1000000&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Current Password: IndigoIndigo &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
Current Line: 2000000&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Current Password: 5reinforce &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Rate: 239 k/s<br /><br />
PASSWORD FOUND: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Zygomaticing (0x5a79676f6d61746963696e67)<br />
HASH:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2c5419e6db59f283bcbb501c722e73c6<br />
HASH TYPE:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;md5<br />
LEFT SALT: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a8f0h2 (0x613866306832)<br />
RIGHT SALT: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;8hf27 (0x3868663237)<br />
</div>

<h2>Code</h2>

<center>
    <p>
    <a href="/source/crack.php">
    <strong>Click here to download the script.</strong>
    </a>
    </p>
</center>

<?php
    printSourceFile("source/crack.php", true);
?>

