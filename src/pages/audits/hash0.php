<?php
    Upvote::render_arrows(
        "audithashzero",
        "defuse_pages",
        "Hash0 Security Audit",
        "A security audit of the Hash0 pastebin.",
        "https://defuse.ca/audits/hash0.htm"
    );
?>
<div class="pagedate">
April 13, 2014
</div>
<h1>Hash0 Security Audit</h1>

<p>
This is the result of a 4-hour security audit of
<a href="https://github.com/dannysu/hash0">Hash0</a>, which is a tool for
turning a master password into unique passwords for websites based on the domain
name (like pwdhash). Thanks to Hash0's author Danny Su for funding this audit.
</p>

<pre>
-----------------------------------------------------------------------
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Security Audit of Hash0
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Taylor Hornby
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;April 13, &nbsp;2014
-----------------------------------------------------------------------

1. Introduction

&nbsp;&nbsp; This report is the result of a 5-hour audit of Hash0 [1]. Hash0 is
&nbsp;&nbsp; a tool for generating different passwords for websites based on
&nbsp;&nbsp; a master password, similar to pwdhash [2] and hashpass [3].

&nbsp;&nbsp; The audit scope and threat model are discussed in sections 1.2 and
&nbsp;&nbsp; 1.3 respectively. Section 2 gives an overview of Hash0&#039;s
&nbsp;&nbsp; cryptography. Section 3 presents security issues found during the
&nbsp;&nbsp; audit. Section 4 recommends improvements. Section 5 lists future
&nbsp;&nbsp; work, and Section 6 concludes.

1.2 Audit Scope

&nbsp;&nbsp; This audit focused on the implementation and design of Hash0&#039;s
&nbsp;&nbsp; cryptography, and did not explicitly check for other kinds of
&nbsp;&nbsp; vulnerabilities.

&nbsp;&nbsp; While some light review of the supporting libraries was performed,
&nbsp;&nbsp; the audit focused on code unique to Hash0 and did not include
&nbsp;&nbsp; significant time reviewing the PasswordMaker, SJCL, or CryptoJS
&nbsp;&nbsp; libraries.

&nbsp;&nbsp; The SHA1 of the commit that was reviewed is:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;09338e4f0f453e8ad95859e0f882cf7c7d54aa26

1.3 Threat Model

&nbsp;&nbsp; There are three types of entities involved in every use of Hash0:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;1. The User

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;The User is the person using the Hash0 software. The User
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;knows the master password and uses Hash0 to generate
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;site-specific passwords from the one master password.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;2. The Storage Provider

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;The Storage Provider is responsible for storing metadata
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;like the salts and synchronization settings. We assume this
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;entity is controlled by the adversary.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;3. The Website

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;This is the website the user is using Hash0 to generate
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;a password for. The website provides a standard username and
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;password login interface and is not necessarily aware that
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Hash0 is being used. We assume this entity is controlled by
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;the adversary.

&nbsp;&nbsp; The following list summarizes some kinds of attacks that would be
&nbsp;&nbsp; considered security flaws in Hash0.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- Attacks that leak useful information about the Master Password
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;to any entity other than the User, even when the attacker can
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;see many generated passwords.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- Attacks that leak passwords for one Website to any entity
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;other than the User or the intended Website.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- Attacks that cause weak passwords to be generated.

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- Attacks that speed up the recovery of the master key from
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;derived data (ciphertext, generated passwords, etc).

2. Cryptography Design

&nbsp;&nbsp; Given a master password, Hash0 runs it through 100 iterations PBKDF2
&nbsp;&nbsp; with the salt &quot;saltysnacks&quot; to produce 512 bits of output*. That
&nbsp;&nbsp; output is used as a key to HMAC the string &quot;zerobin1337&quot;. The HMAC
&nbsp;&nbsp; output is converted to a 30-character password, called the
&nbsp;&nbsp; &quot;encryption password&quot;, with a base conversion algorithm. This
&nbsp;&nbsp; password is used for encrypting and decrypting data stored by the
&nbsp;&nbsp; Storage Provider.

&nbsp;&nbsp; The data stored by the Storage Provider is encrypted and decrypted
&nbsp;&nbsp; using SJCL&#039;s encrypt() and decrypt() convenience functions, with the
&nbsp;&nbsp; default parameters. The default is to derive an 128-bit key from the
&nbsp;&nbsp; password with PBKDF2 then encrypt the data with AES in CCM mode,
&nbsp;&nbsp; which is an authenticated encryption mode.

&nbsp;&nbsp; To generate a password for a website, Hash0 runs the master password
&nbsp;&nbsp; through 100 iterations of PBKDF2 with a random salt** to generate 512
&nbsp;&nbsp; bits of output. &nbsp;That output is used as a key to HMAC the string
&nbsp;&nbsp; which is the domain name of the website prefixed to the password
&nbsp;&nbsp; number (used to generate multiple passwords for the same website).
&nbsp;&nbsp; The HMAC output is converted to a password of configurable length
&nbsp;&nbsp; using a base conversion algorithm.

&nbsp;&nbsp; * - The PBKDF2 output is encoded in hex and is used as the HMAC key
&nbsp;&nbsp; &nbsp; &nbsp; without being decoded.

&nbsp;&nbsp; **- The salt may be the empty string if the Storage Provider URL is
&nbsp;&nbsp; &nbsp; &nbsp; not configured. See Issue 3.7. The salt is encoded (and used) as
&nbsp;&nbsp; &nbsp; &nbsp; a 128-bit hex string.

3. Issues

&nbsp;&nbsp; This section lists the issues discovered during the audit. We do not
&nbsp;&nbsp; attempt to assign criticality or exploitability ratings to the
&nbsp;&nbsp; issues.

3.1 Encryption is Stored in LocalStorage

&nbsp;&nbsp; The encryption key, which is used to encrypt and decrypt the data
&nbsp;&nbsp; stored by the Storage Provider, is stored in localStorage. Unless the
&nbsp;&nbsp; browser is in private browsing mode, it will be written to disk. It
&nbsp;&nbsp; should be kept it memory.

&nbsp;&nbsp; The Hash0 author was aware of this issue before this audit began.

3.2 Salts Generated with Math.random()

&nbsp;&nbsp; The salts are generated with CryptoJS&#039;s WordArray.random(), which
&nbsp;&nbsp; uses Math.random(). This is insecure. Salts must be generated with
&nbsp;&nbsp; a CSPRNG.

&nbsp;&nbsp; Use window.crypto.getRandomValues() or the SJCL cryptographic random
&nbsp;&nbsp; number generator.

&nbsp;&nbsp; The Hash0 author was aware of this issue before this audit began.

3.3 Low PBKDF2 Iteration Count

&nbsp;&nbsp; Only 100 iterations of PBKDF2 are used when deriving the encryption
&nbsp;&nbsp; password or a website password. This low value was explicitly chosen
&nbsp;&nbsp; for performance reasons. According to these benchmarks...

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;https://wiki.mozilla.org/SJCL_PBKDF2_Benchmark

&nbsp;&nbsp; ...most platforms can support many more iterations. The iteration
&nbsp;&nbsp; count should be increased to 1000.

&nbsp;&nbsp; This is probably because 1000 iterations of PBKDF2 actually are being
&nbsp;&nbsp; used, but not in the right place. SJCL&#039;s encrypt() and decrypt()
&nbsp;&nbsp; functions compute 1000 iterations of PBKDF2 to turn the passed string
&nbsp;&nbsp; into a key. To avoid this, pass a key (bitArray), not a string, to
&nbsp;&nbsp; encrypt() and decrypt().

&nbsp;&nbsp; The Hash0 author was aware of this issue before the audit began.

3.4 Corrupted Ciphertext Exception is Not Caught

&nbsp;&nbsp; The SJCL decrypt() function will throw sjcl.exception.corrupt if the
&nbsp;&nbsp; key is wrong or if an attacker has tampered with the ciphertext.
&nbsp;&nbsp; Hash0 does not handle this case, and simply crashes without giving
&nbsp;&nbsp; the user any explanation.

&nbsp;&nbsp; This exception should be caught, and the user should be told that
&nbsp;&nbsp; either the password they entered was wrong, or an attacker has
&nbsp;&nbsp; tampered with the data saved by the Storage Provider.

3.5 Encryption Password Derived with Constant Salt

&nbsp;&nbsp; The encryption password is derived from the master password with
&nbsp;&nbsp; a constant salt:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;generatePassword(
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&#039;on&#039;, 30, &#039;zerobin&#039;, &#039;1337&#039;, &#039;saltysnacks&#039;, password
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;);

&nbsp;&nbsp; This is insecure, because the same master password will always
&nbsp;&nbsp; generate the same encryption password, so rainbow tables and lookup
&nbsp;&nbsp; tables can be used to crack the encrypted data.

&nbsp;&nbsp; Fixing this is left as future work. See Section 5.3.

3.6 Migration Code Always Runs

&nbsp;&nbsp; This is not a security issue. The code to prompt the user if they
&nbsp;&nbsp; want to migrate will always run, because the &quot;if&quot; statement&#039;s
&nbsp;&nbsp; condition will always be true:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;var password = $(&#039;#setup_master&#039;).val();
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;localStorage[&#039;encryptionPassword&#039;] = // ... snipped ...

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;var url = $(&#039;#setup_url&#039;).val();
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;localStorage[&#039;settingsURL&#039;] = url;

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;// Check if there is existing settings
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if (defined(localStorage[&#039;settingsURL&#039;]) &amp;&amp;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;defined(localStorage[&#039;encryptionPassword&#039;])) {

3.7 Empty Salt Used Without Warning

&nbsp;&nbsp; If the URL to the Storage Provider is not provided or is empty, an
&nbsp;&nbsp; empty salt is used:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if (!defined(localStorage[&#039;encryptionPassword&#039;]) ||
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;!defined(localStorage[&#039;settingsURL&#039;]) ||
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;localStorage[&#039;settingsURL&#039;] == &#039;&#039;) {
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;salt = &#039;&#039;;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;} else {
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;...

&nbsp;&nbsp; Instead of using an empty salt, display an error (refuse to generate
&nbsp;&nbsp; passwords), or warn the user and ask them to opt-in to using the
&nbsp;&nbsp; empty salt.

3.8 HMAC Key is a Hex String

&nbsp;&nbsp; When deriving the encryption password or a website password, the
&nbsp;&nbsp; string used for the HMAC key is hex-encoded. This does not cause any
&nbsp;&nbsp; immediate weaknesses, however using a key that isn&#039;t uniformly
&nbsp;&nbsp; distributed is not ideal and probably breaks some of HMAC&#039;s security
&nbsp;&nbsp; proofs.

3.9 Salt is a Hex String

&nbsp;&nbsp; The salt passed to PBKDF2 is a hex string. While this doesn&#039;t cause
&nbsp;&nbsp; any immediate security problems, it is not ideal.

3.10 Password is Output Before Settings are Saved

&nbsp;&nbsp; When generating a website password, the password is shown to the user
&nbsp;&nbsp; before the settings are uploaded. This means an attacker who can
&nbsp;&nbsp; prevent the settings from being uploaded (e.g. by a DoS attack) can
&nbsp;&nbsp; cause password reuse in some cases:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;1. User generates a password for example.org.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;2. Example.org&#039;s password database is breached.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;3. User generates a new password, but the upload fails.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;4. Example.org&#039;s password database is breached again.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;5. User generates a new password, but this time it&#039;s the same as
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; the one generated in (4) because the upload with the
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; incremented number failed.

3.11 Browser Tab Race Conditions

&nbsp;&nbsp; Because of a TOCTTOU bug, it&#039;s possible for the password to be
&nbsp;&nbsp; entered in to the wrong tab.&nbsp;
&nbsp;&nbsp; &nbsp;
&nbsp;&nbsp; The init() function first obtains the password for the current param
&nbsp;&nbsp; (domain), and then, AFTER it already has the password, it inserts it
&nbsp;&nbsp; into the current tab. The tab might have changed in between.
&nbsp;&nbsp; &nbsp;
&nbsp;&nbsp; A malicious website could potentially &quot;steal focus&quot; at just the right
&nbsp;&nbsp; time to steal a password that was intended for another website.

&nbsp;&nbsp; To fix this, make sure that the tab the password is going to be
&nbsp;&nbsp; inserted into is the same as the one that was the source of param.

4. Recommendations

4.1 Unit Tests

&nbsp;&nbsp; Hash0 could benefit from unit tests, especially of important
&nbsp;&nbsp; functions like initWithUrl() and generatePassword().

4.2 Use PBKDF2 alone, not PBKDF2 then HMAC

&nbsp;&nbsp; It doesn&#039;t seem necessary to use PBKDF2 to generate a key then to use
&nbsp;&nbsp; HMAC on top of that to apply the param and number. It would be better
&nbsp;&nbsp; to encode everything unambiguously into the PBKDF2 salt.

4.3 Do Not Use Passwords as Intermediate Keys

&nbsp;&nbsp; Hash0 is somewhat strange in that instead of deriving an encryption
&nbsp;&nbsp; key from the password, it derives another &quot;encryption password.&quot; This
&nbsp;&nbsp; is inefficient at best, and error-prone at worst. Stick to binary
&nbsp;&nbsp; keys whenever possible.

5. Future Work

5.1 Side Channel Attacks

&nbsp;&nbsp; Some of the code seems vulnerable to side-channel attacks. For
&nbsp;&nbsp; example, passwordmaker/hashutils.js uses charAt() to get the
&nbsp;&nbsp; character at an index into the character set, where the index is
&nbsp;&nbsp; a secret.

&nbsp;&nbsp; It could be possible for other scripts running in the browser, or
&nbsp;&nbsp; other processes running on the system (as other users), to extract
&nbsp;&nbsp; the key this way.

5.2 URL Reliability

&nbsp;&nbsp; This audit did not fully explore all possible problems with the URL
&nbsp;&nbsp; used to find the domain name not matching up with the actual URL of
&nbsp;&nbsp; the page. Is it possible for a page to lie about its URL, so that
&nbsp;&nbsp; Hash0 is fooled into giving it the password for another website?

5.3 Salting the Master Password

&nbsp;&nbsp; The encryption key is derived from the master key with a fixed salt.
&nbsp;&nbsp; Obviously, a random salt should be used instead. More time is needed
&nbsp;&nbsp; to design a secure solution.

5.4 Storage Provider Replaying Old Data

&nbsp;&nbsp; What exactly happens when the Storage Provider replays an old
&nbsp;&nbsp; ciphertext? &nbsp;This will cause old passwords to be generated, and
&nbsp;&nbsp; possibly some passwords re-used. What kind of risk does this pose to
&nbsp;&nbsp; the user?

6. Conclusion

&nbsp;&nbsp; No fatal flaws in Hash0 were identified. However, there is room for
&nbsp;&nbsp; improvement. The most significant issues are 3.1, 3.2, 3.4, and 3.5.
&nbsp;&nbsp; 3.11 may be very important as well, depending on how exploitable it
&nbsp;&nbsp; is in practice (something this audit did not investigate).

7. References

[1] https://github.com/dannysu/hash0
[2] https://www.pwdhash.com/
[3] http://www.hashapass.com/en/index.html
</pre>
