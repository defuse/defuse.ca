<?php /*
<h1>GRC's Perfect Password Generator is not Cryptographically Secure</h1>

<p>
<a href="https://grc.com/passwords">GRC's "Perfect Passwords" Generator</a>
claims to be a cryptographically-secure psuedo-random number generator (CSPRNG).
Unfortunately, it isn't. It's vulnerable to an attack that lets anyone who can
recover the CSPRNG's state&mdash;either by guessing all 512 bits of state, or by
breaking into GRC's server&mdash;recover <em>all</em> of the passwords that have
ever been generated.
</p>


<p>
This kind of attack was formalized in <a href="https://www.schneier.com/paper-prngs.html">J. Kelsey, B. Schneier, D.
Wagner, and C. Hall's 1998 paper on attacking PRNGs</a>. It is called a "State
Compromise Extension Attack," which means that extracting the state of the CSPRNG lets
you learn more than you should be able to. Being able to
learn all previous output certainly qualifies as a state compromise extension
attack.
</p>

<p>
So, how does the attack work? 
</p>

<p>
GRC's generator is very simple: it's AES-256 used to encrypt a sequence of
increasing numbers in CBC mode. It starts with 3 parameters: the initialization
vector (IV), the counter (COUNTER), and the secret key (KEY). In each iteration, the following steps
are executed: 
</p>

<ol>
    <li>P = IV &oplus; COUNTER</li>
    <li>IV = ENCRYPT(KEY, P)</li>
    <li>Output IV</li>
    <li>Increment COUNTER</li>
</ol>

<p>
If somebody learns the values of IV, COUNTER, and KEY, at any point in time,
they can reverse the algorithm to recover previous output like this:
</p>

<ol>
    <li>Decrement COUNTER</li>
    <li>P = DECRYPT(KEY, IV)</li>
    <li>IV = P &oplus; COUNTER</li>
    <li>IV is the previous output</li>
</ol>

<p>
This process can be repeated by the attacker for as long as they want, until
they have recovered every password the generator has ever generated.
</p>

<p>
Is this really a problem?
</p>

<p>
The AES block cipher is pretty secure, so it's very unlikely that somebody is
going to recover the state cryptanalytically by looking at the generator's
output and trying to recover the parameters. The only feasible way to get at the
generator's state would be to break into GRC's server(s). So if you trust GRC's
servers not to get broken into, then your passwords are safe. But after
observing the frequency of 0day remote code execution exploit releases today,
it's hard to trust any server not to be hacked, even if it's run by a security
expert.
</p>

<p>
The problem is really easy to fix, so there's no reason to be putting passwords
at risk. My suggestions to GRC are as follows:
</p>

<ol>
    <li>
    Immediately re-seed the current generator implementation, securely
    destroying the old seed, so that all of the passwords generated thus far are
    safe.
    </li>
    <li>
    Add a cryptographic hash function into the process so that it cannot be
    reversed. One possibility is to hash the result of the XOR before or after
    feeding it into the cipher (but see point 3).
    </li>
    <li>
    Periodically re-seed from a truly-random source of data. This should be done
    automatically at least every 24 hours. The old state should be securely
    discarded.
    </li>
    <li>
    Use a well-tested CSPRNG instead, like Fortuna (Cryptography Engineering,
    Section 9.3).
    </li>
</ol>

<p>
When these suggestions are implemented, even if GRC's servers are compromised,
the attacker won't be able to reverse the generator.
</p>

<p>
</p>
 */
?>
Actually, it is. :-)
