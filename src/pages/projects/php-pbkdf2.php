<h1>PBKDF2 For PHP</h1>

<p>
PBKDF2 (Password-Based Key Derivation Function) is a <a href="http://en.wikipedia.org/wiki/Key_stretching">key stretching</a> algorithm.
It can be used to hash passwords in a computationally intensive manner, so that dictionary and
brute-force attacks are less effective. See <a
href="http://crackstation.net/hashing-security.htm">CrackStation's Hashing Security Article</a> for
instructions on implementing salted password hashing.</p>

<p>
The following code is a PBKDF2 implementation in PHP. It is in the public domain, so feel free to use
it for any purpose whatsoever. It complies with the <a href="https://www.ietf.org/rfc/rfc6070.txt">PBKDF2 test vectors in RFC 6070</a>. Performance improvements to the original code were provided by <a href="http://www.variations-of-shadow.com/">variations-of-shadow.com</a>.
</p>


<h2>Benchmarks</h2>

<p>
The following benchmarks demonstrate the running time for various iteration counts, using the SHA256
hash function. The benchmarks
were run on an AMD Phenom 9600 2.3GHz CPU.
</p>

<div class="code">
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; 1 iteration : 0.000149 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; 2 iterations: 0.000036 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; 4 iterations: 0.000038 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; 8 iterations: 0.000052 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;16 iterations: 0.000090 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;32 iterations: 0.000157 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;64 iterations: 0.000297 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; 128 iterations: 0.000623 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; 256 iterations: 0.001141 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp; 512 iterations: 0.002258 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp;1024 iterations: 0.004594 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp;2048 iterations: 0.009575 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp;4096 iterations: 0.018386 seconds<br />
&nbsp;&nbsp; &nbsp; &nbsp;8192 iterations: 0.036070 seconds<br />
&nbsp;&nbsp; &nbsp; 16384 iterations: 0.073297 seconds<br />
&nbsp;&nbsp; &nbsp; 32768 iterations: 0.145324 seconds<br />
&nbsp;&nbsp; &nbsp; 65536 iterations: 0.294785 seconds<br />
&nbsp;&nbsp; &nbsp;131072 iterations: 0.577492 seconds<br />
&nbsp;&nbsp; &nbsp;262144 iterations: 1.173854 seconds<br />
&nbsp;&nbsp; &nbsp;524288 iterations: 2.334627 seconds<br />
&nbsp;&nbsp; 1048576 iterations: 4.688382 seconds<br />
&nbsp;&nbsp; 2097152 iterations: 9.249891 seconds<br />
&nbsp;&nbsp; 4194304 iterations: 18.69492 seconds<br />
&nbsp;&nbsp; 8388608 iterations: 36.90171 seconds<br />
&nbsp;&nbsp;16777216 iterations: 75.31797 seconds
</div>

<h2>Source Code</h2>

<div style="text-align: center; padding-bottom: 10px;">
    <a href="/source/pbkdf2.php"><strong>Click Here to Download</strong></a>
</div>

<div class="code" >
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/pbkdf2.php");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>

<h3>Benchmark Code</h3>

<div class="code">
for($i = 0; $i &lt; 25; $i++) {<br />
 &nbsp; &nbsp;$count = pow(2, $i);<br />
 &nbsp; &nbsp;$start = microtime(true);<br />
 &nbsp; &nbsp;$hash = pbkdf2(&quot;sha256&quot;, &quot;password&quot;, &quot;salt&quot;, $count, 32);<br />
 &nbsp; &nbsp;$time = microtime(true) - $start;<br />
 &nbsp; &nbsp;printf(&quot;%10d iterations: %f seconds\n&quot;, $count, $time);<br />
}<br />

</div>

<br />
<h3>Test Code</h3>

<div style="text-align: center; padding-bottom: 10px;">
    <a href="/source/pbkdf2-test.php"><strong>Click Here to Download</strong></a>
</div>

<div class="code">
    <?php
        require_once('libs/HtmlEscape.php');
        $code = file_get_contents("source/pbkdf2-test.php");
        echo HtmlEscape::escapeText($code, true, 4);
    ?>
</div>
