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
disclosure is the best approach for disclosing these vulnerabilities, since by
doing so, users can immediately re-evaluate their use of EncFS, and some of the
issues have already been disclosed but haven't been fixed.
</p>

<pre>
-------------------------------------------------------------------------------
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;EncFS Security Audit
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Taylor Hornby
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;January 14, 2014
-------------------------------------------------------------------------------

1. Introduction

&nbsp;&nbsp;This document describes the results of a 10-hour security audit of EncFS
&nbsp;&nbsp;1.7.4. The audit was performed on January 13th and 14th of 2014.

1.1. What is EncFS?

&nbsp;&nbsp;EncFS is a user-space encrypted file system. Unlike disk encryption software
&nbsp;&nbsp;like TrueCrypt, EncFS&#039;s ciphertext directory structure mirrors the plaintext&#039;s
&nbsp;&nbsp;directory structure. This introduces unique challenges, such as guaranteeing
&nbsp;&nbsp;unique IVs for file name and content encryption, while maintaining
&nbsp;&nbsp;performance.

1.2. Audit Results Summary

&nbsp;&nbsp;This audit finds that EncFS is not up to speed with modern cryptography
&nbsp;&nbsp;practices. Several previously known vulnerabilities have been reported [1, 2],
&nbsp;&nbsp;which have not been completely fixed. New issues were also discovered during
&nbsp;&nbsp;the audit.

&nbsp;&nbsp;The next section presents a list of the issues that were discovered. Each
&nbsp;&nbsp;issue is given a severity rating from 1 to 10. Due to lack of time, most
&nbsp;&nbsp;issues have not been confirmed with a proof-of-concept.

2. Issues

2.1. Same Key Used for Encryption and Authentication

&nbsp;&nbsp;SEVERITY: 3

&nbsp;&nbsp;EncFS uses the same key for encrypting data and computing MACs. This is
&nbsp;&nbsp;generally considered to be bad practice.

&nbsp;&nbsp;EncFS should use separate keys for encrypting data and computing MACs.

2.2. Stream Cipher Used to Encrypt Last File Block

&nbsp;&nbsp;SEVERITY: 7

&nbsp;&nbsp;As reported in [1], EncFS uses a stream cipher mode to encrypt the last file
&nbsp;&nbsp;block. The change log says that the ability to add random bytes to a block was
&nbsp;&nbsp;added as a workaround for this issue. However, it does not solve the problem,
&nbsp;&nbsp;and is not enabled by default.

&nbsp;&nbsp;EncFS needs to use a block mode to encrypt the last block.

&nbsp;&nbsp;EncFS&#039;s stream encryption is unorthodox:

&nbsp;&nbsp; &nbsp;1. Run &quot;Shuffle Bytes&quot; on the plaintext.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;N[J+1] = Xor-Sum(i = 0 TO J) { P[i] }
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;(N = &quot;shuffled&quot; plaintext value, P = plaintext)
&nbsp;&nbsp; &nbsp;2. Encrypt with (IV, key) using CFB mode.
&nbsp;&nbsp; &nbsp;3. Run &quot;Flip Bytes&quot; on the ciphertext.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;This reverses bytes in 64-byte chunks.
&nbsp;&nbsp; &nbsp;4. Run &quot;Shuffle Bytes&quot; on the ciphertext.
&nbsp;&nbsp; &nbsp;5. Encrypt with (IV + 1, key) using CFB mode.

&nbsp;&nbsp;This should be removed and replaced with something more standard. As far as
&nbsp;&nbsp;I can see, this provides no useful security benefit, however, it is relied
&nbsp;&nbsp;upon to prevent the attacks in [1]. This is security by obscurity.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
2.3. Generating Block IV by XORing Block Number

&nbsp;&nbsp;SEVERITY: 7

&nbsp;&nbsp;Given the File IV (an IV unique to a file), EncFS generates per-block IVs by
&nbsp;&nbsp;XORing the File IV with the Block Number. This is not a good solution, as it
&nbsp;&nbsp;leads to IV re-use when combined with the last-block stream cipher issue (see
&nbsp;&nbsp;the previous section):

&nbsp;&nbsp;The stream algorithm (see previous section) adds 1 to the IV, which could
&nbsp;&nbsp;*undo* the XOR with the block number, causing the IV to be re-used. Suppose
&nbsp;&nbsp;the file consists of one and a half blocks, and that the File IV&#039;s least
&nbsp;&nbsp;significant bit (LSB) is 1. The first block will be encrypted with the File IV
&nbsp;&nbsp;(block number = 0). The second (partial) block will be encrypted with File IV
&nbsp;&nbsp;XOR 1 (since block number = 1), making the LSB 0, using the stream algorithm.
&nbsp;&nbsp;The stream algorithm adds 1 to the IV, bringing the LSB back to 1, and hence
&nbsp;&nbsp;the same IV is used twice.

&nbsp;&nbsp;EncFS should use a mode like XTS for random-access block encryption, instead
&nbsp;&nbsp;of CBC mode with predictable IVs.

2.4. File Holes are Not Authenticated

&nbsp;&nbsp;SEVERITY: 5

&nbsp;&nbsp;File holes allow large files to contain &quot;holes&quot; of all zero bytes, which are
&nbsp;&nbsp;not saved to disk. EncFS supports these, but it determines if a file block is
&nbsp;&nbsp;part of a file hole by checking if it is all zeroes. If an entire block is
&nbsp;&nbsp;zeroes, it passes the zeroes on without decrypting it or verifying a MAC.

&nbsp;&nbsp;This allows an attacker to insert zero blocks inside a file (or append zero
&nbsp;&nbsp;blocks to the end of the file), without being detected when MAC headers are
&nbsp;&nbsp;enabled.

2.5. MACs Not Compared in Constant Time

&nbsp;&nbsp;SEVERITY: 6

&nbsp;&nbsp;MACs are not compared in constant time (MACFileIO.cpp, Line 209). This allows
&nbsp;&nbsp;an attacker with write access to the ciphertext to use a timing attack to
&nbsp;&nbsp;compute the MAC of arbitrary values.

&nbsp;&nbsp;A constant-time string comparison should be used.

2.6. 64-bit MACs

&nbsp;&nbsp;SEVERITY: 5

&nbsp;&nbsp;EncFS uses 64-bit MACs. This is not long enough, as they can be forged in 2^64
&nbsp;&nbsp;time, which is feasible today.

&nbsp;&nbsp;EncFS should use (at least) 128-bit MACs.

2.7. Editing Configuration File Disables MACs

&nbsp;&nbsp;SEVERITY: 7

&nbsp;&nbsp;The purpose of MAC headers is to prevent an attacker with read/write access to
&nbsp;&nbsp;the ciphertext from being able to make changes without being detected.
&nbsp;&nbsp;Unfortunately, this feature provides little security, since it is controlled
&nbsp;&nbsp;by an option in the .encfs6.xml configuration file (part of the ciphertext),
&nbsp;&nbsp;so the attacker can just disable it by setting &quot;blockMACBytes&quot; to 0 and adding
&nbsp;&nbsp;8 to &quot;blockMACRandBytes&quot; (so that the MAC is not interpreted as data).

&nbsp;&nbsp;EncFS needs to re-evaluate the purpose of MAC headers and come up with
&nbsp;&nbsp;something more robust. As a workaround, EncFS could add a command line option
&nbsp;&nbsp;--require-macs that will trigger an error if the configuration file does not
&nbsp;&nbsp;have MAC headers enabled.

3. Future Work

&nbsp;&nbsp;There were a few potential problems that I didn&#039;t have time to evaluate. This
&nbsp;&nbsp;section lists the most important ones. These will be prioritized in future
&nbsp;&nbsp;audits.

3.1. Padding Oracle

&nbsp;&nbsp;POSSIBLE SEVERITY: 8

&nbsp;&nbsp;EncFS uses Mac-then-Encrypt. This might make decryption padding oracles
&nbsp;&nbsp;possible through timing attacks.

3.2. Chosen Ciphertext Attacks

&nbsp;&nbsp;POSSIBLE SEVERITY: 10

&nbsp;&nbsp;Since the same key is used to encrypt all files, it may be possible for an
&nbsp;&nbsp;attacker with read/write access to the ciphertext and partial read access to
&nbsp;&nbsp;the plaintext (e.g. to one directory when --public is used) to perform
&nbsp;&nbsp;a chosen ciphertext attack and decrypt ciphertexts for which they have no
&nbsp;&nbsp;plaintext access.

&nbsp;&nbsp;EncFS should consider using XTS mode.

3.3. Possible Out of Bounds Write in StreamNameIO and BlockNameIO

&nbsp;&nbsp;POSSIBLE SEVERITY: 7

&nbsp;&nbsp;There is a possible buffer overflow in the encodeName method of StreamNameIO
&nbsp;&nbsp;and BlockNameIO. The methods write to the &#039;encodedName&#039; argument without
&nbsp;&nbsp;checking its length. This may allow an attacker with control over file names
&nbsp;&nbsp;to crash EncFS or execute arbitrary code.

3.4. 64-bit Initialization Vectors

&nbsp;&nbsp;POSSIBLE SEVERITY: 5

&nbsp;&nbsp;Initialization vectors are only 64 bits, even when using AES instead of
&nbsp;&nbsp;Blowfish. This may lead to vulnerabilities when encrypting large (or lots of)
&nbsp;&nbsp;files.

4. Conclusion

&nbsp;&nbsp;In conclusion, while EncFS is a useful tool, it ignores many standard
&nbsp;&nbsp;best-practices in cryptography. This is most likely due to it&#039;s old age
&nbsp;&nbsp;(originally developed before 2005), however, it is still being used today, and
&nbsp;&nbsp;needs to be updated.

&nbsp;&nbsp;The EncFS author says that a 2.0 version is being developed [3]. This would be
&nbsp;&nbsp;a good time to fix the old problems.

&nbsp;&nbsp;As it is now, EncFS is not suitable for protecting mission-critical data.

5. References

[1] http://archives.neohapsis.com/archives/fulldisclosure/2010-08/0316.html

[2] http://code.google.com/p/encfs/issues/detail?id=128

[3] https://code.google.com/p/encfs/issues/detail?id=186

</pre>
