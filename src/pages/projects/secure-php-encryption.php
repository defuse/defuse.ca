<?php
    Upvote::render_arrows(
        "securephpencryption",
        "defuse_pages",
        "How to Encrypt Data in PHP",
        "The right way to encrypt data in PHP.",
        "https://defuse.ca/secure-php-encryption.htm"
    );
?>
<h1>How to Encrypt Data in PHP</h1>

<div style="background-color: #FFAAAA; border: solid black 5px; padding: 10px;">
<span style="font-size: 30pt;">WARNING:</span>
<p>
Until today (May 2, 2013), the code on this page suffered from a serious
vulnerability, effectively making everything encrypt with the same key,
regardless of what key you pass to the encryption function. Please re-encrypt
any data that was encrypted with this code as soon as possible. It can be fixed
by applying the following diff (the code on this page is also updated):
</p>

<div style="border: solid black 1px; padding: 10px; margin-top: 5px;">
--- /tmp/Crypto.php &nbsp; &nbsp; 2013-05-02 06:39:28.059745329 -0600<br />
+++ source/Crypto.php &nbsp; 2013-05-02 06:52:17.987744595 -0600<br />
@@ -116,7 +116,7 @@<br />
&nbsp;&nbsp; &nbsp; &nbsp;*/<br />
&nbsp;&nbsp; &nbsp; public static function CreateSubkey($master, $purpose, $bytes)<br />
&nbsp;&nbsp; &nbsp; {<br />
- &nbsp; &nbsp; &nbsp; &nbsp;$source = hash_hmac(&quot;sha512&quot;, $purpose, true);<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;$source = hash_hmac(&quot;sha512&quot;, $purpose, $master, true);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if(strlen($source) &lt; $bytes) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; trigger_error(&quot;Subkey too big.&quot;, E_USER_ERROR);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return $source; // fail safe<br />
@@ -168,6 +168,15 @@<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return false;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; }<br />
&nbsp;<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;$key = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;$data = &quot;abcdef&quot;;<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;$ciphertext = Crypto::Encrypt($data, $key);<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;$wrong_key = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;if (Crypto::Decrypt($ciphertext, $wrong_key))<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;{<br />
+ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;FAIL: Ciphertext decrypts with an incorrect key.&quot;;<br />
+ &nbsp; &nbsp; &nbsp; &nbsp;}<br />
+<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; echo &quot;PASS\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; return true;<br />
&nbsp;&nbsp; &nbsp; }
</div>

<p>
The first change fixes the actual problem. The second adds a test for it.
</p>

</div>

<p>
PHP's <a href="http://php.net/mcrypt">mcrypt</a> functions can be used to
encrypt data, but it's not easy to use them correctly. The three
most common pitfalls are:
</p>

<ol>
    <li>
        <p><strong>Not using authenticated encryption.</strong></p>

        <p>
        Programmers who are new to cryptography often assume that just
        encrypting the data is enough. But that's not true. You have to make
        sure that nobody can <em>modify</em> with the encrypted data too. If you
        don't, then you open yourself up to a plethora of attacks.
        </p>

        <p>
        To detect ciphertext modification, apply a
        <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Message_authentication_code">Message Authentication Code (MAC)</a>
        (e.g. <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Hmac">
        HMAC</a>) to the ciphertext after encrypting, and check that it is
        correct before decrypting a ciphertext.
        </p>

    </li>
    <li>
       <p><strong>Not unambiguously padding the plaintext.</strong></p>

        <p>
        <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Block_cipher_modes_of_operation">
        Block cipher modes</a>
        like Cipher Block Chaining (CBC) can only encrypt texts whose length is
        a multiple of the cipher's block size. To encrypt data of any size, we
        add &quot;padding&quot; to the data before encrypting, so that its
        length is a multiple of the cipher's block size.
        </p>

        <p>
        Unfortunately, the mcrypt encryption functions just append zero bytes
        (0x00) to the data until its length is a multiple of the cipher's block
        size. This is ambiguous, because if you encrypt some data that ends in
        zero bytes, when you try to decrypt it, you won't be able to tell which
        zero bytes are part of the original data and which zero bytes are
        padding. So you have to implement your own padding.
        </p>

    </li>

    <li>
        <p><strong>MCRYPT_RIJNDAEL_256 is not AES-256.</strong></p>

        <p>
        MCRYPT_RIJNDAEL_256 refers to the version of the Rijndael cipher that
        operates on 256-bit <b>blocks</b>, not the version of the Rijndael
        cipher that uses 128-bit blocks and 256-bit <b>keys</b>. AES is only the
        128-bit block version of Rijndael (which can use 128-, 192-, and 256-bit
        keys). So MCRYPT_RIJNDAEL_256 is not AES. AES is MCRYPT_RIJNDAEL_128.
        <p>

        <p>
        The mcrypt methods determine the key size by the length of the
        string you pass in for the key. If you give it a 16-byte string, it'll
        use AES-128; if you give it a 32-byte string, it'll use AES-256.
        </p>

        <p>Do not use MCRYPT_RIJNDAEL_256. Use MCRYPT_RIJNDAEL_128.</p>
    </li>
</ol>


<p>
The following PHP class does encryption and decryption with AES-128 and uses
HMAC-SHA256 for authentication. It uses PKCS #7 padding so that decryption will
always return a string that is exactly the same as the one that was encrypted.
See the <kbd>Test()</kbd> method for sample usage.
</p>

<p>
<strong>Warning:</strong> Cryptography is <em>very</em> easy to get wrong. It's
difficult to overstate how hard it is to do crypto right. If you're new to
implementing cryptography, I very strongly recommend asking a professional
cryptographer for help. Almost all cryptosystem failures are due to
implementation errors.
</p>

<center><strong><a href="/source/Crypto.php">Download Crypto.php</a></strong></center>

<?php
    printSourceFile("source/Crypto.php",true);
?>
