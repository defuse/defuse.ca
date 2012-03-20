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
Defuse Cyber-Security&#039;s Hash Cracking Script - v1.1<br />
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

<div class="code">
&lt;?php<br />
/*<br />
&nbsp;* PHP Hash Cracker v1.1: https://defuse.ca/php-hash-cracker.htm<br />
&nbsp;* Usage: php crack.php &lt;arguments&gt;<br />
&nbsp;* Arguments:<br />
&nbsp;* &nbsp; &nbsp; -w &lt;wordlist&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Wordlist or &quot;stdin&quot; for standard input.<br />
&nbsp;* &nbsp; &nbsp; -s &lt;start line number&gt; &nbsp;Skip lines of the wordlist.<br />
&nbsp;* &nbsp; &nbsp; -o &lt;output file&gt; &nbsp; &nbsp; &nbsp; &nbsp;Save session/results to file.<br />
&nbsp;* &nbsp; &nbsp; -f &lt;output file&gt; &nbsp; &nbsp; &nbsp; &nbsp;Recover crashed session.<br />
&nbsp;* &nbsp; &nbsp; -c &lt;hash&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; The hash to crack.<br />
&nbsp;* &nbsp; &nbsp; -t &lt;hash type&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;The type of hash.<br />
&nbsp;* &nbsp; &nbsp; -l &lt;left salt&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Salt prepended to the password.<br />
&nbsp;* &nbsp; &nbsp; -r &lt;right salt&gt; &nbsp; &nbsp; &nbsp; &nbsp; Salt appended to the password.<br />
&nbsp;* &nbsp; &nbsp; -d &lt;s&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Hash &lt;s&gt; with all supported hash types.<br />
&nbsp;* &nbsp; &nbsp; -h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Print help message.<br />
&nbsp;* ** All other arguments are ignored when using -f or -d **<br />
&nbsp;*/<br />
&nbsp;&nbsp; &nbsp;// ========================= CONSTANTS ================================<br />
&nbsp;&nbsp; &nbsp;define(&quot;DEFUSE_URL&quot;, &quot;https://defuse.ca/php-hash-cracker.htm&quot;);<br />
&nbsp;&nbsp; &nbsp;define(&quot;VERSION&quot;, &quot;v1.1&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp;// Print a status update every OUTPUT_RATE lines.<br />
&nbsp;&nbsp; &nbsp;define(&quot;OUTPUT_RATE&quot;, 1000000);<br />
&nbsp;&nbsp; &nbsp;// Save the session status every BACKUP_RATE lines.<br />
&nbsp;&nbsp; &nbsp;define(&quot;BACKUP_RATE&quot;, 1000000);<br />
<br />
<br />
&nbsp;&nbsp; &nbsp;// ========================= ENTRY POINT ===============================<br />
<br />
&nbsp;&nbsp; &nbsp;$opts = getopt(&quot;d:w:s:c:t:l:r:f:o:h::&quot;);<br />
&nbsp;&nbsp; &nbsp;$args = parseOptions($opts);<br />
<br />
&nbsp;&nbsp; &nbsp;checkHashFormat($args[&#039;hash_str&#039;], $args[&#039;hash_type&#039;]);<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp;printPreamble($args[&#039;wordlist&#039;], $args[&#039;start_line&#039;], $args[&#039;hash_str&#039;],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_type&#039;], $args[&#039;left_salt&#039;],<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;right_salt&#039;], $args[&#039;output_file&#039;]);<br />
<br />
&nbsp;&nbsp; &nbsp;$res = crack($args[&#039;wordlist&#039;], $args[&#039;start_line&#039;], $args[&#039;hash_str&#039;], <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_type&#039;], $args[&#039;left_salt&#039;], <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;right_salt&#039;], $args[&#039;output_file&#039;]);<br />
&nbsp;&nbsp; &nbsp;if($res == false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Error reading wordlist.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp;// ===================== HASH CRACKING FUNCTIONS ========================<br />
<br />
&nbsp;&nbsp; &nbsp;function crack($wordlist, $start_line, $hash_str, $hash_type, &nbsp;$left_salt, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$right_salt, $output_file)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$fh = fopen($wordlist, &quot;r&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($fh === false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return false;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$line_number = 0;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Find the start line<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($start_line &gt; 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Seeking start line...\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;while($line_number &lt; $start_line &amp;&amp; ($line = fgets($fh)) !== false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$line_number++;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($line_number &gt; 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Starting at line $line_number...\n&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$success = false;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// For each line in the file...<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;while(($line = fgets($fh)) !== false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// ... hash it ...<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$line = trim($line);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash = computeHash($hash_type, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$left_salt . $line . $right_salt, $hash_str);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// ... and compare it with the hash being cracked.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if($hash == $hash_str)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;outputSuccess($line, $hash_str, $hash_type, $left_salt, $right_salt);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;writeSuccessToFile($output_file, $wordlist, $line_number, $hash_str, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash_type, $left_salt, $right_salt, $line);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$success = true;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Print a status update every OUTPUT_RATE lines in the wordlist.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if($line_number &gt; 0 &amp;&amp; $line_number % OUTPUT_RATE == 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;outputStatus($line, $line_number);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;// Save the session every BACKUP_RATE lines in the wordlist.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if($line_number % BACKUP_RATE == 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;writeStatusToFile($output_file, $wordlist, $line_number, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash_str, $hash_type, $left_salt, $right_salt);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$line_number++;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;fclose($fh);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($success == false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;outputFailure();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;writeFailureToFile($output_file, $wordlist, $hash_str, $hash_type, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$left_salt, $right_salt);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return true;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function computeHash($hash_type, $plaintext, $cracking_hash)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// &nbsp;If you want absolute maximum speed, comment out the <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// &nbsp;uneccessary code in this method.<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Performance hack: <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// &nbsp;We don&#039;t want to call in_array for every hash, since it&#039;s slow.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// &nbsp;So we cache the result of in_array for the last $hash_type we<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// &nbsp;were called with. <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;static $builtin_hash = null;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;static $last_type = null;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(is_null($builtin_hash) || $last_type != $hash_type)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$builtin_hash = in_array($hash_type, hash_algos());<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$last_type = $hash_type;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($builtin_hash)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return hash($hash_type, $plaintext);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;elseif($hash_type == &quot;ntlm&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash = bin2hex(NTLMHash($plaintext));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return $hash;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;elseif($hash_type == &quot;md5md5&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return hash(&quot;md5&quot;, hash(&quot;md5&quot;, $plaintext));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;elseif($hash_type == &quot;mysql41&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return hash(&quot;sha1&quot;, hash(&quot;sha1&quot;, $plaintext, true));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;elseif($hash_type == &quot;lm&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return LMHash($plaintext); &nbsp; &nbsp; &nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;elseif($hash_type == &quot;crypt&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return crypt($plaintext, $cracking_hash);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return false;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function printAllHashesOf($string)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;printHash(&quot;NTLM&quot;, computeHash(&quot;ntlm&quot;, $string, &quot;&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;printHash(&quot;LM&quot;, computeHash(&quot;lm&quot;, $string, &quot;&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;printHash(&quot;MySQL 4.1&quot;, computeHash(&quot;mysql41&quot;, $string, &quot;&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$hashes = hash_algos();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;foreach($hashes as $hashType)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printHash($hashType, computeHash($hashType, $string, &quot;&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;printHash(&quot;md5(md5)&quot;, computeHash(&quot;md5md5&quot;, $string, &quot;&quot;));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;die;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function printHash($type, $value)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$type .= &quot;:&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$type .= str_repeat(&quot; &quot;, 20 - strlen($type));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo $type . $value . &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;// ===================== STATUS OUTPUT FUNCTIONS ========================<br />
<br />
&nbsp;&nbsp; &nbsp;function outputSuccess($password, $hash_str, $hash_type, $left_salt, $right_salt)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\nPASSWORD FOUND: \t$password (0x&quot;, bin2hex($password), &quot;)\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;HASH:\t\t\t$hash_str\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;HASH TYPE:\t\t$hash_type\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($left_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;LEFT SALT: \t\t$left_salt (0x&quot;, bin2hex($left_salt), &quot;)\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($right_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;RIGHT SALT: \t\t$right_salt (0x&quot;, bin2hex($right_salt), &quot;)\n&quot;;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function outputStatus($line, $line_number)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;static $start_time = null;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$padded_line = $line . str_repeat(&quot; &quot;, max(25 - strlen($line), 0));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Current Line: $line_number\t Current Password: $padded_line&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!is_null($start_time))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$duration = microtime(true) - $start_time;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$rate = (int)((OUTPUT_RATE / $duration) / 1000);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot; Rate: $rate k/s\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$start_time = microtime(true);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function outputFailure()<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;PASSWORD NOT FOUND.\n&quot;;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function printPreamble($wordlist, $start_line, $hash_str, $hash_type, &nbsp;$left_salt, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$right_salt, $output_file)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Defuse Cyber-Security&#039;s Hash Cracking Script - &quot; . VERSION . &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Homepage: &quot; . DEFUSE_URL . &quot;\n\n&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$now = date(&quot;F j, Y, g:i a&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Begin execution: $now \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Wordlist: $wordlist \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Start line: $start_line \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Hash: $hash_str \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Hash type: $hash_type \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($left_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Left salt: $left_salt \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($right_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Right salt: $right_salt \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!is_null($output_file))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Session file: $output_file \n&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
<br />
&nbsp;&nbsp; &nbsp;// ===================== FILE OUTPUT FUNCTIONS ========================<br />
<br />
&nbsp;&nbsp; &nbsp;function writeStatusToFile($file, $wordlist, $line_number, $hash_str, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash_type, $left_salt, $right_salt)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($file == null)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$status = &quot;Status: CRACKING\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$wordlist . &quot;\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$line_number . &quot;\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash_type . &quot;\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;bin2hex($hash_str) . &quot;\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;bin2hex($left_salt) . &quot;\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;bin2hex($right_salt) . &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;file_put_contents($file, $status);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function writeSuccessToFile($file, $wordlist, $line_number, $hash_str, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$hash_type, $left_salt, $right_salt, $password)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($file == null)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$status = &quot;Status: FOUND\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tPassword: $password (0x&quot; . bin2hex($password) . &quot;)\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tIn wordlist: $wordlist\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tLine number: $line_number\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tHash: $hash_str\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tHash type: $hash_type\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($left_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$status .= &quot;\tLeft salt: $left_salt (0x&quot; . bin2hex($left_salt) . &quot;)\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($right_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$status .= &quot;\tRight salt: $right_salt (0x&quot; . bin2hex($right_salt) . &quot;)\n&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;file_put_contents($file, $status);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function writeFailureToFile($file, $wordlist, $hash_str, $hash_type, $left_salt, $right_salt)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($file == null)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$status = &quot;Status: NOT FOUND\n&quot; . <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tIn wordlist: $wordlist\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tHash: $hash_str\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;\tHash type: $hash_type\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($left_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$status .= &quot;\tLeft salt: $left_salt (0x&quot; . bin2hex($left_salt) . &quot;)\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($right_salt))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$status .= &quot;\tRight salt: $right_salt (0x&quot; . bin2hex($right_salt) . &quot;)\n&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;file_put_contents($file, $status);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;// Prints the help message, with an optional error message, then exits.<br />
&nbsp;&nbsp; &nbsp;function printUsage($msg = &quot;&quot;)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;PHP Hash Cracker &quot; . VERSION . &quot;: &quot; . DEFUSE_URL . &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(!empty($msg))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;** $msg ** \n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Usage: php crack.php &lt;arguments&gt;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Arguments:\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-w &lt;wordlist&gt;\t\t\tWordlist or \&quot;stdin\&quot; for standard input.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-s &lt;start line number&gt;\t\tSkip lines of the wordlist.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-o &lt;output file&gt;\t\tSave session/results to file.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-f &lt;output file&gt;\t\tRecover crashed session.\n&quot; . <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-c &lt;hash&gt;\t\t\tThe hash to crack.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-t &lt;hash type&gt;\t\t\tThe type of hash.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-l &lt;left salt&gt;\t\t\tSalt prepended to the password.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-r &lt;right salt&gt;\t\t\tSalt appended to the password.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-d &lt;s&gt;\t\t\t\tHash &lt;s&gt; with all supported hash types.\n&quot; .<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &quot;\t-h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;\t\tPrint help message.\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;** All other arguments are ignored when using -f or -d **\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\nSupported Hash Types:\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$hashes = hash_algos();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\tcrypt lm ntlm mysql41 md5md5&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$i = 0;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;foreach($hashes as $hashtype)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if($i % 4 == 0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\n\t&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;$hashtype &quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$i++;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;** Do not include the * in mysql41 hashes. **\n&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;die;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;// ===================== OPTION PARSING FUNCTIONS ========================<br />
<br />
&nbsp;&nbsp; &nbsp;function parseOptions($opts)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$args = array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;f&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args = parseSessionFile($opts, $opts[&#039;f&#039;]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;h&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage();<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;d&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printAllHashesOf($opts[&#039;d&#039;]);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;w&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;wordlist&#039;] = ($opts[&#039;w&#039;] == &quot;stdin&quot;) ? <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&quot;php://stdin&quot; : realpath($opts[&#039;w&#039;]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Please specify a wordlist.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(empty($args[&#039;wordlist&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Invalid wordlist path.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;c&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_str&#039;] = $opts[&#039;c&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Please specify a hash.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;t&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_type&#039;] = strtolower($opts[&#039;t&#039;]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Please specify a hash type.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;l&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;left_salt&#039;] = $opts[&#039;l&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;left_salt&#039;] = &quot;&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;r&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;right_salt&#039;] = $opts[&#039;r&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;right_salt&#039;] = &quot;&quot;;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;s&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;start_line&#039;] = (int)$opts[&#039;l&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;start_line&#039;] = 0;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(isset($opts[&#039;o&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;output_file&#039;] = $opts[&#039;o&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(file_exists($args[&#039;output_file&#039;]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Output file exists. Did you mean -f?&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;output_file&#039;] = null;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($args[&#039;hash_type&#039;] != &quot;crypt&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_str&#039;] = strtolower($args[&#039;hash_str&#039;]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return $args;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function parseSessionFile($opts, $path)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// See writeStatusToFile() for the file format.<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$file = file_get_contents($path);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($file === false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Cannot read file.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(strpos($file, &quot;Status: CRACKING&quot;) !== false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args = array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$params = explode(&quot;\n&quot;, $file);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if(count($params) &lt; 7)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;die(&quot;Malformed file.\n&quot;);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;wordlist&#039;] = $params[1];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;start_line&#039;] = (int)$params[2];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_type&#039;] = $params[3];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;hash_str&#039;] = hex2bin($params[4]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;left_salt&#039;] = hex2bin($params[5]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;right_salt&#039;] = hex2bin($params[6]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$args[&#039;output_file&#039;] = $opts[&#039;f&#039;];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return $args;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;echo $file;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;die;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function checkHashFormat($hash_str, $hash_type)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Make sure we support the hash type<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if(($sample = computeHash($hash_type, &quot;&quot;, &quot;&quot;)) === false)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Invalid hash type.&quot;);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Make sure the hash &quot;looks right&quot;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if($hash_type != &quot;crypt&quot; &amp;&amp; strlen($sample) != strlen($hash_str))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;printUsage(&quot;Hash string length is not the expected length.&quot;);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
<br />
&nbsp;&nbsp; &nbsp;// ================== NTLM and LM Hash Functions ========================<br />
<br />
&nbsp;&nbsp; &nbsp;// NTLM Code Source: http://www.php.net/manual/en/ref.hash.php#82018<br />
&nbsp;&nbsp; &nbsp;function NTLMHash($Input) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;// Convert the password from UTF8 to UTF16 (little endian)<br />
&nbsp;&nbsp; &nbsp; &nbsp;$Input=@iconv(&#039;UTF-8&#039;,&#039;UTF-16LE&#039;,$Input);<br />
&nbsp;&nbsp; &nbsp; &nbsp;$MD4Hash=hash(&#039;md4&#039;,$Input, true);<br />
&nbsp;&nbsp; &nbsp; &nbsp;return $MD4Hash;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;// LM Code Source: http://www.php.net/manual/en/ref.hash.php#84587<br />
&nbsp;&nbsp; &nbsp;function LMhash($string)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$string = strtoupper(substr($string,0,14));<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$p1 = LMhash_DESencrypt(substr($string, 0, 7));<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$p2 = LMhash_DESencrypt(substr($string, 7, 7));<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return bin2hex($p1.$p2);<br />
&nbsp;&nbsp; &nbsp;}<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp;function LMhash_DESencrypt($string)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key = array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$tmp = array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$len = strlen($string);<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for ($i=0; $i&lt;7; ++$i)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$tmp[] = $i &lt; $len ? ord($string[$i]) : 0;<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = $tmp[0] &amp; 254;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[0] &lt;&lt; 7) | ($tmp[1] &gt;&gt; 1);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[1] &lt;&lt; 6) | ($tmp[2] &gt;&gt; 2);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[2] &lt;&lt; 5) | ($tmp[3] &gt;&gt; 3);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[3] &lt;&lt; 4) | ($tmp[4] &gt;&gt; 4);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[4] &lt;&lt; 3) | ($tmp[5] &gt;&gt; 5);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = ($tmp[5] &lt;&lt; 2) | ($tmp[6] &gt;&gt; 6);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key[] = $tmp[6] &lt;&lt; 1;<br />
&nbsp;&nbsp; &nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$is = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$iv = mcrypt_create_iv($is, MCRYPT_RAND);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$key0 = &quot;&quot;;<br />
&nbsp;&nbsp; &nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;foreach ($key as $k)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;$key0 .= chr($k);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;$crypt = mcrypt_encrypt(MCRYPT_DES, $key0, &quot;KGS!@#$%&quot;, MCRYPT_MODE_ECB, $iv);<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return $crypt;<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
&nbsp;&nbsp; &nbsp;function hex2bin($hex)<br />
&nbsp;&nbsp; &nbsp;{<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;return pack(&quot;H*&quot;, $hex);<br />
&nbsp;&nbsp; &nbsp;}<br />
<br />
?&gt;
</div>
