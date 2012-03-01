<h1>PBKDF2 For PHP</h1>
<p>PBKDF2 (Password Based Key Derivation Function) for PHP. The following code is public domain, feel free to use it for any purpose. The code complies with test vectors at <a href="https://www.ietf.org/rfc/rfc6070.txt">https://www.ietf.org/rfc/rfc6070.txt</a>.</p>
<div class="code" >
&nbsp;&nbsp;&nbsp;&nbsp;/*<br />
&nbsp;&nbsp;&nbsp;&nbsp; * PBKDF2 key derivation function as defined by RSA&#039;s PKCS #5: https://www.ietf.org/rfc/rfc2898.txt<br />
&nbsp;&nbsp;&nbsp;&nbsp; * $algorithm - The hash algorithm to use. Recommended: SHA256<br />
&nbsp;&nbsp;&nbsp;&nbsp; * $password - The password.<br />
&nbsp;&nbsp;&nbsp;&nbsp; * $salt - A salt that is unique to the password.<br />
&nbsp;&nbsp;&nbsp;&nbsp; * $count - Iteration count. Higher = better. Recommended: At least 1024.<br />
&nbsp;&nbsp;&nbsp;&nbsp; * $key_length - The length of the derived key in BYTES.<br />
&nbsp;&nbsp;&nbsp;&nbsp; * Returns: A $key_length-byte key derived from the password and salt (in binary).<br />
&nbsp;&nbsp;&nbsp;&nbsp; *<br />
&nbsp;&nbsp;&nbsp;&nbsp; * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt<br />
&nbsp;&nbsp;&nbsp;&nbsp; */<br />
&nbsp;&nbsp;&nbsp;&nbsp;function pbkdf2($algorithm, $password, $salt, $count, $key_length)<br />
&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$algorithm = strtolower($algorithm);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if(!in_array($algorithm, hash_algos(), true))<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;die(&#039;PBKDF2 ERROR: Invalid hash algorithm.&#039;);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if($count &lt; 0 || $key_length &lt; 0)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;die(&#039;PBKDF2 ERROR: Invalid parameters.&#039;);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if($key_length &gt; 4294967295)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;die(&#039;PBKDF2 ERROR: Derived key too long.&#039;);<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$hLen = strlen(hash($algorithm, &quot;&quot;, true));<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$numBlocks = (int)ceil((double)$key_length / $hLen);<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$output = &quot;&quot;;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for($i = 1; $i &lt;= $numBlocks; $i++)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$output .= pbkdf2_f($password, $salt, $count, $i, $algorithm, $hLen);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return substr($output, 0, $key_length);<br />
&nbsp;&nbsp;&nbsp;&nbsp;}<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;/*<br />
&nbsp;&nbsp;&nbsp;&nbsp; * The pseudorandom function used by PBKDF2.<br />
&nbsp;&nbsp;&nbsp;&nbsp; * Definition: https://www.ietf.org/rfc/rfc2898.txt<br />
&nbsp;&nbsp;&nbsp;&nbsp; */<br />
&nbsp;&nbsp;&nbsp;&nbsp;function pbkdf2_f($password, $salt, $count, $i, $algorithm, $hLen)<br />
&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//$i encoded as 4 bytes, big endian.<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$last = $salt . chr(($i &gt;&gt; 24) % 256) . chr(($i &gt;&gt; 16) % 256) . chr(($i &gt;&gt; 8) % 256) . chr($i % 256);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$xorsum = &quot;&quot;;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for($r = 0; $r &lt; $count; $r++)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$u = hash_hmac($algorithm, $last, $password, true);<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$last = $u;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if(empty($xorsum))<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$xorsum = $u;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;else<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for($c = 0; $c &lt; $hLen; $c++)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$xorsum[$c] = chr(ord(substr($xorsum, $c, 1)) ^ ord(substr($u, $c, 1)));<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $xorsum;<br />
&nbsp;&nbsp;&nbsp;&nbsp;}
</div>
