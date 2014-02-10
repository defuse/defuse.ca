<?php
    Upvote::render_arrows(
        "auditencfs",
        "defuse_pages",
        "EncFS Security Audit",
        "The results of an EncFS security audit.",
        "https://defuse.ca/audits/encfs.htm"
    );
?>
<div class="pagedate">
January 15, 2014
</div>
<h1>EncFS Security Audit</h1>

<p>
This report is the result of a paid 10-hour security audit of <a
href="http://www.arg0.net/encfs">EncFS</a>. It has been <a
href="http://sourceforge.net/mailarchive/message.php?msg_id=31849549">posted to
the EncFS mailing list</a>, so check there for follow-up. I feel that full
disclosure is the best approach for disclosing these vulnerabilities, since some
of the issues have already been disclosed but haven't been fixed, and by
disclosing them, users can immediately re-evaluate their use of EncFS.
</p>

<p>
Thanks to Igor Sviridov for funding this audit.
</p>

<p>
<b>Note:</b> This report was updated on February 5, 2014, thanks to feedback
from Robert Freudenreich, to correct a technical inaccuracy about how
initialization vectors are generated and to clarify the conclusion of the
report. You can see the old version of the report <a
href="/audits/encfs-old.htm">here</a>.
</p>

<pre>
------------------------------------------------------------------------
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;EncFS Security Audit
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Taylor Hornby
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;January 14, 2014
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;(Updated: February 5, 2014)
------------------------------------------------------------------------

1. Introduction

&nbsp;&nbsp;This document describes the results of a 10-hour security audit of
&nbsp;&nbsp;EncFS 1.7.4. The audit was performed on January 13th and 14th of 2014.

1.1. What is EncFS?

&nbsp;&nbsp;EncFS is a user-space encrypted file system. Unlike disk encryption
&nbsp;&nbsp;software like TrueCrypt, EncFS&#039;s ciphertext directory structure
&nbsp;&nbsp;mirrors the plaintext&#039;s directory structure. This introduces unique
&nbsp;&nbsp;challenges, such as guaranteeing unique IVs for file name and content
&nbsp;&nbsp;encryption, while maintaining performance.

1.2. Audit Results Summary

&nbsp;&nbsp;This audit finds that EncFS is not up to speed with modern
&nbsp;&nbsp;cryptography practices. Several previously known vulnerabilities have
&nbsp;&nbsp;been reported [1, 2], which have not been completely fixed. New issues
&nbsp;&nbsp;were also discovered during the audit.

&nbsp;&nbsp;The next section presents a list of the issues that were discovered.
&nbsp;&nbsp;Each issue is given a severity rating from 1 to 10. Due to lack of
&nbsp;&nbsp;time, most issues have not been confirmed with a proof-of-concept.

2. Issues

2.1. Same Key Used for Encryption and Authentication

&nbsp;&nbsp;Exploitability: Low
&nbsp;&nbsp;Security Impact: Low

&nbsp;&nbsp;EncFS uses the same key for encrypting data and computing MACs. This
&nbsp;&nbsp;is generally considered to be bad practice.

&nbsp;&nbsp;EncFS should use separate keys for encrypting data and computing MACs.

2.2. Stream Cipher Used to Encrypt Last File Block

&nbsp;&nbsp;Exploitability: Unknown
&nbsp;&nbsp;Security Impact: High

&nbsp;&nbsp;As reported in [1], EncFS uses a stream cipher mode to encrypt the
&nbsp;&nbsp;last file block. The change log says that the ability to add random
&nbsp;&nbsp;bytes to a block was added as a workaround for this issue. However, it
&nbsp;&nbsp;does not solve the problem, and is not enabled by default.

&nbsp;&nbsp;EncFS needs to use a block mode to encrypt the last block.

&nbsp;&nbsp;EncFS&#039;s stream encryption is unorthodox:

&nbsp;&nbsp; &nbsp;1. Run &quot;Shuffle Bytes&quot; on the plaintext.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;N[J+1] = Xor-Sum(i = 0 TO J) { P[i] }
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;(N = &quot;shuffled&quot; plaintext value, P = plaintext)
&nbsp;&nbsp; &nbsp;2. Encrypt with (setIVec(IV), key) using CFB mode.
&nbsp;&nbsp; &nbsp;3. Run &quot;Flip Bytes&quot; on the ciphertext.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;This reverses bytes in 64-byte chunks.
&nbsp;&nbsp; &nbsp;4. Run &quot;Shuffle Bytes&quot; on the ciphertext.
&nbsp;&nbsp; &nbsp;5. Encrypt with (setIVec(IV + 1), key) using CFB mode.

&nbsp;&nbsp; &nbsp;Where setIVec(IV) = HMAC(globalIV || (IV), key), and,
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- &#039;globalIV&#039; is an IV shared across the entire filesystem.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;- &#039;key&#039; is the encryption key.

&nbsp;&nbsp;This should be removed and replaced with something more standard. As
&nbsp;&nbsp;far as I can see, this provides no useful security benefit, however,
&nbsp;&nbsp;it is relied upon to prevent the attacks in [1]. This is security by
&nbsp;&nbsp;obscurity.

2.3. Generating Block IV by XORing Block Number

&nbsp;&nbsp;Exploitability: Low
&nbsp;&nbsp;Security Impact: Medium

&nbsp;&nbsp;Given the File IV (an IV unique to a file), EncFS generates per-block
&nbsp;&nbsp;IVs by XORing the File IV with the Block Number, then passing the
&nbsp;&nbsp;result to setIVec(), which is described in Section 2.2. This is not
&nbsp;&nbsp;a good solution, as it leads to IV re-use when combined with the
&nbsp;&nbsp;last-block stream cipher issue in Section 2.2:

&nbsp;&nbsp;The stream algorithm (see previous section) adds 1 to the IV, which
&nbsp;&nbsp;could *undo* the XOR with the block number, causing the IV to be
&nbsp;&nbsp;re-used. Suppose the file consists of one and a half blocks, and that
&nbsp;&nbsp;the File IV&#039;s least significant bit (LSB) is 1. The first block will
&nbsp;&nbsp;be encrypted with the File IV (block number = 0). The second (partial)
&nbsp;&nbsp;block will be encrypted with File IV XOR 1 (since block number = 1),
&nbsp;&nbsp;making the LSB 0, using the stream algorithm. &nbsp;The stream algorithm
&nbsp;&nbsp;adds 1 to the IV, bringing the LSB back to 1, and hence the same IV is
&nbsp;&nbsp;used twice. The IVs are reused with different encryption modes (CBC
&nbsp;&nbsp;and CFB), but CFB mode starts out similar to CBC mode, so this is
&nbsp;&nbsp;worrisome.

&nbsp;&nbsp;EncFS should use a mode like XTS for random-access block encryption.

2.4. File Holes are Not Authenticated

&nbsp;&nbsp;Exploitability: High
&nbsp;&nbsp;Security Impact: Low

&nbsp;&nbsp;File holes allow large files to contain &quot;holes&quot; of all zero bytes,
&nbsp;&nbsp;which are not saved to disk. EncFS supports these, but it determines
&nbsp;&nbsp;if a file block is part of a file hole by checking if it is all
&nbsp;&nbsp;zeroes. If an entire block is zeroes, it passes the zeroes on without
&nbsp;&nbsp;decrypting it or verifying a MAC.

&nbsp;&nbsp;This allows an attacker to insert zero blocks inside a file (or append
&nbsp;&nbsp;zero blocks to the end of the file), without being detected when MAC
&nbsp;&nbsp;headers are enabled.

2.5. MACs Not Compared in Constant Time

&nbsp;&nbsp;Exploitability: Medium
&nbsp;&nbsp;Security Impact: Medium

&nbsp;&nbsp;MACs are not compared in constant time (MACFileIO.cpp, Line 209). This
&nbsp;&nbsp;allows an attacker with write access to the ciphertext to use a timing
&nbsp;&nbsp;attack to compute the MAC of arbitrary values.

&nbsp;&nbsp;A constant-time string comparison should be used.

2.6. 64-bit MACs

&nbsp;&nbsp;Exploitability: Low
&nbsp;&nbsp;Security Impact: Medium

&nbsp;&nbsp;EncFS uses 64-bit MACs. This is not long enough, as they can be forged
&nbsp;&nbsp;in 2^64 time, which is feasible today.

&nbsp;&nbsp;EncFS should use (at least) 128-bit MACs.

2.7. Editing Configuration File Disables MACs

&nbsp;&nbsp;Exploitability: High
&nbsp;&nbsp;Security Impact: Medium

&nbsp;&nbsp;The purpose of MAC headers is to prevent an attacker with read/write
&nbsp;&nbsp;access to the ciphertext from being able to make changes without being
&nbsp;&nbsp;detected. &nbsp;Unfortunately, this feature provides little security, since
&nbsp;&nbsp;it is controlled by an option in the .encfs6.xml configuration file
&nbsp;&nbsp;(part of the ciphertext), so the attacker can just disable it by
&nbsp;&nbsp;setting &quot;blockMACBytes&quot; to 0 and adding 8 to &quot;blockMACRandBytes&quot; (so
&nbsp;&nbsp;that the MAC is not interpreted as data).

&nbsp;&nbsp;EncFS needs to re-evaluate the purpose of MAC headers and come up with
&nbsp;&nbsp;something more robust. As a workaround, EncFS could add a command line
&nbsp;&nbsp;option --require-macs that will trigger an error if the configuration
&nbsp;&nbsp;file does not have MAC headers enabled.

3. Future Work

&nbsp;&nbsp;There were a few potential problems that I didn&#039;t have time to
&nbsp;&nbsp;evaluate. This section lists the most important ones. These will be
&nbsp;&nbsp;prioritized in future audits.

3.1. Information Leakage Between Decryption and MAC Check

&nbsp;&nbsp;EncFS uses Mac-then-Encrypt. Therefore it is possible for any
&nbsp;&nbsp;processing done on the decrypted plaintext before the MAC is checked
&nbsp;&nbsp;to leak information about it, in a style similar to a padding oracle
&nbsp;&nbsp;vulnerability. EncFS doesn&#039;t use padding, but the MAC code does
&nbsp;&nbsp;iteratively check if the entire block is zero, so the number of
&nbsp;&nbsp;leading zero bytes in the plaintext is leaked by the execution time.

3.2. Chosen Ciphertext Attacks

&nbsp;&nbsp;Since the same key is used to encrypt all files, it may be possible
&nbsp;&nbsp;for an attacker with read/write access to the ciphertext and partial
&nbsp;&nbsp;read access to the plaintext (e.g. to one directory when --public is
&nbsp;&nbsp;used) to perform a chosen ciphertext attack and decrypt ciphertexts
&nbsp;&nbsp;for which they have no plaintext access.

&nbsp;&nbsp;EncFS should consider using XTS mode.

3.3. Possible Out of Bounds Write in StreamNameIO and BlockNameIO

&nbsp;&nbsp;There is a possible buffer overflow in the encodeName method of
&nbsp;&nbsp;StreamNameIO and BlockNameIO. The methods write to the &#039;encodedName&#039;
&nbsp;&nbsp;argument without checking its length. This may allow an attacker with
&nbsp;&nbsp;control over file names to crash EncFS or execute arbitrary code.

3.4. 64-bit Initialization Vectors

&nbsp;&nbsp;Initialization vectors are only 64 bits, even when using AES instead
&nbsp;&nbsp;of Blowfish. This may lead to vulnerabilities when encrypting large
&nbsp;&nbsp;(or lots of) files.

4. Conclusion

&nbsp;&nbsp;In conclusion, while EncFS is a useful tool, it ignores many standard
&nbsp;&nbsp;best-practices in cryptography. This is most likely due to it&#039;s old
&nbsp;&nbsp;age (originally developed before 2005), however, it is still being
&nbsp;&nbsp;used today, and needs to be updated.

&nbsp;&nbsp;The EncFS author says that a 2.0 version is being developed [3]. This
&nbsp;&nbsp;would be a good time to fix the old problems.

&nbsp;&nbsp;EncFS is probably safe as long as the adversary only gets one copy of
&nbsp;&nbsp;the ciphertext and nothing more. EncFS is not safe if the adversary
&nbsp;&nbsp;has the opportunity to two or more snapshots of the ciphertext at
&nbsp;&nbsp;different times. EncFS attempts to protect files from malicious
&nbsp;&nbsp;modification, but there are serious problems with this feature.

5. References

[1] http://archives.neohapsis.com/archives/fulldisclosure/2010-08/0316.html

[2] http://code.google.com/p/encfs/issues/detail?id=128

[3] https://code.google.com/p/encfs/issues/detail?id=186
</pre>
