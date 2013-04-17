<?php
    Upvote::render_arrows(
        "raceconditions",
        "defuse_pages",
        "Practical Race Condition Vulnerabilities in Web Applications",
        "An example of a web application (PHP) vulnerable to a race condition, and how to fix it.",
        "https://defuse.ca/race-conditions-in-web-applications.htm"
    );
?>
<h1>Practical Race Condition Vulnerabilities in Web Applications</h1>

<h2>What are Race Conditions?</h2>

<p>Race conditions in software are when two concurrent threads of execution access a shared resource in a way that unintentionally produces different results depending on the time at which the code is executed. For example, a multi-threaded program may spawn 2 threads that have access to the same location in memory. Thread #1 might store the value 300 in this location and expect it to still be 300 a few instructions later. Since thread #2 is executing at the same time as thread #1, thread #2 may overwrite the memory location with another value while thread #1 still expects it to be 300. Sometimes it will happen, sometimes it will not. It depends if thread #2 is "lucky enough" to execute just after thread #1 wrote the value 300.</p>

<p>Another example: Suppose Alice, Bob, and Carol have accounts at an online banking service. Alice, Bob, and Carol each have $100 in their accounts. Alice and Bob both transfer $10 to Carol at the exact same time. The web server starts processing Alice's transaction first. The server checks that Alice has sufficient funds in her account then gets Carol's balance into a local variable and adds $10. The next step would be to deduct $10 from Alice's account and then store Carol's updated balance in the database, but the web server decides to pause the execution of Alice's transaction and work on Bob's. Bob's transaction runs to completion, decrementing his balance $10 and incrementing Carol's balance by $10 (remember that Carol's balance in the database was still $100, Alice's request hasn't changed it yet). Now Carol has $110 in her account. The web server goes back to processing Alice's transaction. The variable that holds Carol's updated balance still has the value $110, so the web server sets Carol's balance to $110 unaware that Bob's transaction had already added $10 to Carol's account, and Carol ends up with $110 but Alice and Bob each lost $10.</p>

<p>You wouldn't normally think that a web application written in PHP (which doesn't even have multi-threading) would be vulnerable to race condition attacks, but they can be.</p>

<h2>CPU Time Sharing</h2>

<p>Computer programs seem to execute simultaneously, but unless you have multiple processors or a multi-core CPU, they actually don't. What really happens is that the operating system shares the CPU time between processes and threads. The OS will start some process executing, then a few microseconds later will let some other process use the CPU. After that process has been executing for a few microseconds, yet another process gets its turn to use the CPU. Doing this shares the CPU with all running processes, and makes it look like everything is running at the same time. Wikipedia has an <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Computer_multitasking">excellent article on computer multitasking</a>.</p>

<h2>Online Bank Account Example</h2>

<p>Consider the following PHP code for withdrawing money or credits from some kind of online account. Suppose getBalance() and setBalance() are functions that load/store an account balance from a MySQL database.</p>

<div class="code">
function withdraw($amount)<br />
{<br />
 &nbsp; &nbsp;$balance = getBalance();<br />
 &nbsp; &nbsp;if($amount &lt;= $balance)<br />
 &nbsp; &nbsp;{<br />
 &nbsp; &nbsp; &nbsp; &nbsp;$balance = $balance - $amount;<br />
 &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;You have withdrawn: $amount&quot;;<br />
 &nbsp; &nbsp; &nbsp; &nbsp;setBalance($balance);<br />
 &nbsp; &nbsp;}<br />
 &nbsp; &nbsp;else<br />
 &nbsp; &nbsp;{<br />
 &nbsp; &nbsp; &nbsp; &nbsp;echo &quot;Insufficient funds.&quot;;<br />
 &nbsp; &nbsp;}<br />
}<br />
</div>

<p>Just get the balance, see if there is enough money in the account, and if so, proceed with the transaction and deduct the amount from the balance. Easy, right? Wrong. This function, even though it executes in less than half of a millisecond on my PC, is vulnerable to a race condition attack that allows an attacker to withdraw funds without decreasing the balance. The attack is practical and can even be executed over the internet.</p>

<h3>Proof of Concept</h3>

<p>To demonstrate the attack, I implemented the withdraw function using a 1-entry MySQL table to hold the balance, and setup a PHP script that accepts a URL of the form <tt>poc.php?wd=10</tt> to withdraw the value specified in the URL (in this case, 10). I then wrote a python script to make 128 simultaneous requests to the URL:</p>

<div class="code">
import os<br />
os.fork() #2<br />
os.fork() #4<br />
os.fork() #8<br />
os.fork() #16<br />
os.fork() #32<br />
os.fork() #64<br />
os.fork() #128<br />
print os.popen(&#039;php -r &#039; + \<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#039;&quot;echo file_get_contents(\&#039;http://defuse.ca/poc.php?wd=10\&#039;);&quot;&#039;).read()<br />
</div>

<p>Executing this script will make 128 requests to withdraw $10. If all 128 requests work properly, and the balance starts out at $10,000, we expect the balance to be $10,000 - 128 * $10 = $8720 after the script finishes executing. But due to the race condition, there's actually more money left in the account than there should be.</p>

<center>
<p><strong>Table 1. Actual result of running the Python script.</strong></p>
<table border="1" cellpadding="5">
<tr>
    <th>Trial #</th>
    <th>Balance After Python Script Execution</th>
    <th>Profit (actual - expected)</th>
</tr>
<tr>
    <td>1</td>
    <td>8900</td>
    <td>+180</td>
</tr>
<tr>
    <td>2</td>
    <td>8830</td>
    <td>+110</td>
</tr>
<tr>
    <td>3</td>
    <td>8810</td>
    <td>+90</td>
</tr>
<tr>
    <td>4</td>
    <td>8880</td>
    <td>+160</td>
</tr>
</table>
</center>

<p>As you can see, if this were an actual online financial service (e.g. Paypal), I'd be able to make hundreds of dollars in only a few seconds. Just to be thorough, I can verify that $1280 was actually withdrawn by looking at the output of the Python script:</p>

<div class="code">
$ grep &quot;You have withdrawn: 10&quot; test2 &nbsp;| wc -l<br />
128<br />
</div>

<p>These tests were done using the VPS that hosts this website (Debian 6, running Apache 2.2.16) to host the withdraw script and my Debian 6 PC at home to run the Python script. I've also confirmed that the attack works just as well when the withdraw script is hosted on the same PC that executes the Python script. It works when they're separated by a 100mbps Ethernet LAN, too. You can download all of the proof of concept files <a href="/downloads/poc.zip">here</a>.</p>

<h3>What's going on here?</h3>

<p>This attack is possible because web servers like Apache process queries asynchronously. That means, if two requests come in at nearly the same time, they'll get executed at the same time (on a multi-core CPU) or their execution will be interleaved by the operating system's CPU time sharing system. The result is that a few of the requests end up being processed like this:</p>

<center>
    <img src="/images/racecondition.png" alt="PHP Race Condition" style="border: solid black 1px;" />
</center>

<p>After the two requests are processed, the balance should be $9980, but since the second request is processed while the first is still being processed, we end up with a balance of $9990. Both withdraws work (imagine that the echo statement was replaced by code to increment another account's balance), so $20 is withdrawn but only $10 is deducted from the balance.</p>

<h2>Solutions</h2>

<h3>Random Delay</h3>

<p>Adding a random delay to the withdraw script reduces the effectiveness of the attack, but doesn't prevent it. The following code can be used to add a random delay of up to 10 seconds before processing the withdraw:</p>

<div class="code">
$seconds = rand(1, 10);<br />
$nanoseconds = rand(100, 1000000000);<br />
time_nanosleep($seconds, $nanoseconds);<br />
</div>

<p>This makes the attack harder, but not impossible:</p>

<center>
<p><strong>Table 2. Results with random delay.</strong></p>
<table border="1" cellpadding="5">
<tr>
    <th>Trial #</th>
    <th>Balance After Python Script Execution</th>
    <th>Profit (actual - expected)</th>
</tr>
<tr>
    <td>1</td>
    <td>8730</td>
    <td>+10</td>
</tr>
<tr>
    <td>2</td>
    <td>8720</td>
    <td>0</td>
</tr>
<tr>
    <td>3</td>
    <td>8720</td>
    <td>0</td>
</tr>
<tr>
    <td>4</td>
    <td>8730</td>
    <td>+10</td>
</tr>
</table>
</center>

<p><strong>Don't use this method</strong>. Unless you use a <a href="https://secure.wikimedia.org/wikipedia/en/wiki/CSPRNG">CSPRNG</a> to generate the random delay time, an attacker can probably extract the weak PRNG's state, compute the future delay times, and delay his own queries so the withdraw function executes at the same time. The attacker can also increase the number of simultaneous queries to improve the chances of the withdraws occurring simultaneously. The long delay will annoy users, too.</p>

<h3>System V Semaphore</h3>

<p>If PHP is compiled with <tt>--enable-sysvsem</tt> then it will have System V-like semaphore abilities. We can make the withdraw function secure using the <a href="http://ca2.php.net/manual/en/function.sem-get.php">sem_get</a>, <a href="http://ca2.php.net/manual/en/function.sem-acquire.php">sem_acquire</a>, and <a href="http://ca2.php.net/manual/en/function.sem-release.php">sem_release</a> functions:</p>

<div class="code">
function withdraw($amount)<br />
{<br />
 &nbsp; $sem = sem_get(1234, 1);<br />
 &nbsp; if (sem_acquire($sem))<br />
 &nbsp; {<br />
 &nbsp; &nbsp; &nbsp; $balance = getBalance();<br />
 &nbsp; &nbsp; &nbsp; if($amount &lt;= $balance)<br />
 &nbsp; &nbsp; &nbsp; {<br />
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; $balance = $balance - $amount;<br />
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; echo &quot;You have withdrawn: $amount &lt;br /&gt;&quot;;<br />
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; setBalance($balance);<br />
 &nbsp; &nbsp; &nbsp; }<br />
 &nbsp; &nbsp; &nbsp; else<br />
 &nbsp; &nbsp; &nbsp; {<br />
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; echo &quot;Insufficient funds.&quot;;<br />
 &nbsp; &nbsp; &nbsp; }<br />
 &nbsp; &nbsp; &nbsp; &nbsp;sem_release($sem);<br />
 &nbsp; &nbsp;}<br />
}<br />
</div>

<p>In the call to sem_get, "1234" is the semaphore key (identifier) and "1" is the maximum number of threads/processes that can "acquire" the semaphore at once. If a thread has acquired semaphore 1234 but hasn't yet released it, and another thread calls sem_acquire on the same semaphore, the call will block until the first thread releases it. This prevents the race condition attack.</p> 

<p>You'll probably not want to use '1234' as the semaphore key. It would be better to, for example, use the ID of the user whose balance is being modified. Also, keep in mind that the semaphore functions are not universally supported. The following code can be used to emulate the semaphore functions on systems that do not support them (<a href="http://www.php.net/manual/en/ref.sem.php#105047">source</a>): </p>

<div class="code">
&lt;?php<br />
if ( !function_exists(&#039;sem_get&#039;) ) {<br />
 &nbsp; &nbsp;function sem_get($key) { return fopen(__FILE__.&#039;.sem.&#039;.$key, &#039;w+&#039;); }<br />
 &nbsp; &nbsp;function sem_acquire($sem_id) { return flock($sem_id, LOCK_EX); }<br />
 &nbsp; &nbsp;function sem_release($sem_id) { return flock($sem_id, LOCK_UN); }<br />
}<br />
?&gt;
</div>

<p>I've combined the System V semaphore functions and the file lock method into a single cross-platform Mutex class that you can download <a href="/source/mutex.php">here</a>.</p>

<center>
<p><strong>Table 3. Results with System V semaphore.</strong></p>
<table border="1" cellpadding="5">
<tr>
    <th>Trial #</th>
    <th>Balance After Python Script Execution</th>
    <th>Profit (actual - expected)</th>
</tr>
<tr>
    <td>1</td>
    <td>8720</td>
    <td>0</td>
</tr>
<tr>
    <td>2</td>
    <td>8720</td>
    <td>0</td>
</tr>
<tr>
    <td>3</td>
    <td>8720</td>
    <td>0</td>
</tr>
<tr>
    <td>4</td>
    <td>8720</td>
    <td>0</td>
</tr>
</table>
</center>

<h3>Multiple Servers</h3>

<p>If queries are being processed by more than one server, the System V semaphore method won't prevent the attack. Different semaphores will exist on each server so they won't help if an attacker floods each server with queries. To implement a multi-server mutex you'll need to give each server a common file system and use the file lock method, or use the locking mechanism provided by your database software.</p>

<h2>Useful Links</h2>
<ul>
<li><a href="http://security.dsi.unimi.it/~roberto/pubs/dimva08-web.pdf">&quot;On Race Condition Vulnerabilities in Web Applications&quot; - A 2008 paper on nearly the same subject.</a></li>
<li><a href="http://stackoverflow.com/questions/2921469/php-mutual-exclusion-mutex">Stack Overflow - PHP Mutual Exclusion</a></li>
<li><a href="/downloads/poc.zip">The proof of concept code used in this article</a></li>
<li><a href="https://en.wikipedia.org/wiki/Time-of-check-to-time-of-use">Wikipedia - Time Of Check To Time Of Use (TOCTTOU)</a></li>
<li><a href="https://secure.wikimedia.org/wikipedia/en/wiki/Computer_multitasking">Wikipedia - Computer Multitasking</a></li>
<li><a href="https://www.owasp.org/index.php/Testing_for_Race_Conditions_%28OWASP-AT-010%29">https://www.owasp.org/index.php/Testing_for_Race_Conditions_%28OWASP-AT-010%29</a></li>
</ul>
