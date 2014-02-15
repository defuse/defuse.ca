<?php
    Upvote::render_arrows(
        "auditpefs",
        "defuse_pages",
        "PEFS Security Audit",
        "Security audit of the Private Encrypted File System (PEFS).",
        "https://defuse.ca/audits/pefs.htm"
    );
?>
<div class="pagedate">
February 14, 2014
</div>
<h1>PEFS Security Audit</h1>

<p>
This is the result of a short 13-hour security audit of <a
href="https://wiki.freebsd.org/PEFS">Private Encrypted File System (PEFS)</a>.
</p>

<p>
Thanks to Matt Olander for funding this audit.
</p>

<pre>
-----------------------------------------------------------------------
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;PEFS Security Audit
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Taylor Hornby
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; February 07, 2014
-----------------------------------------------------------------------

1. Introduction

&nbsp;&nbsp; This report documents the results of a 13-hour security audit on
&nbsp;&nbsp; Private Encrypted File System (PEFS). The audit uncovered several
&nbsp;&nbsp; minor problems, some of which may compromise confidentiality.

1.1. What is PEFS?

&nbsp;&nbsp; PEFS [1] is an encrypted filesystem similar to EncFS and eCryptFS.
&nbsp;&nbsp; PEFS is unlike block-level encryption (e.g. TrueCrypt) in that the
&nbsp;&nbsp; ciphertext mirrors the directory structure of the plaintext.

1.2. Audit Scope

&nbsp;&nbsp; The audit was performed by reading the PEFS source code [2]. The git
&nbsp;&nbsp; commit hash of the code that was audited is:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;fb7c4a188cfa7ba69987a1a61104c56f63db5395

&nbsp;&nbsp; This audit focused primarily on PEFS&#039;s cryptographic design and
&nbsp;&nbsp; implementation. This includes PEFS&#039;s use of crypto primitives, but
&nbsp;&nbsp; not the implementations of the primitives themselves (i.e. the AES
&nbsp;&nbsp; implementation). Some integer overflow issues were discovered, but
&nbsp;&nbsp; looking for memory corruption vulnerabilities was not a priority of
&nbsp;&nbsp; the audit. Bugs in the file system implementation (pefs_vnops.c) were
&nbsp;&nbsp; also out of scope.

2. Issues

&nbsp;&nbsp; This section covers all of the security issues discovered during the
&nbsp;&nbsp; audit. They are each given a rating in three variables:

&nbsp;&nbsp; Exploitability: How hard is it for an attacker to exploit?
&nbsp;&nbsp; Security Impact: How bad is it if an attacker can exploit it?
&nbsp;&nbsp; Confirmation: Has the vulnerability been confirmed to exist?

2.1. HMAC and Other Secrets Not Compared in Constant Time

&nbsp;&nbsp; Exploitability: Low
&nbsp;&nbsp; Security Impact: Low
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the code.

&nbsp;&nbsp; On line 357 of pefs_key.c, memcmp() is used to verify an HMAC. This
&nbsp;&nbsp; introduces a timing attack that an attacker might be able to use to
&nbsp;&nbsp; compute the HMAC of an arbitrary message. This is the HMAC used when
&nbsp;&nbsp; encrypting child keys for the key chains feature, not the VMAC (which
&nbsp;&nbsp; is compared in constant time).

&nbsp;&nbsp; There are several other non-constant-time comparisons. I am not sure
&nbsp;&nbsp; if they are relevant to security:

&nbsp;&nbsp; &nbsp; - The comparison of ptk_tweak in pefs_tkey_cmp() in pefs_vnops.c.

&nbsp;&nbsp; &nbsp; - Comparison of what might be cleartext filenames in
&nbsp;&nbsp; &nbsp; &nbsp; pefs_enccn_parsedir() in pefs_vnops.c.

&nbsp;&nbsp; &nbsp; - The comparisons of pk_keyid in pefs_crypto.c, pefs_ctl.c, and
&nbsp;&nbsp; &nbsp; &nbsp; pefs_keychain.c.

&nbsp;&nbsp; In general, when secrets are compared for equality, it should be done
&nbsp;&nbsp; in constant time so that the running time does not leak information
&nbsp;&nbsp; about their actual values.

2.2. Same Key Used for HMAC and Encryption

&nbsp;&nbsp; Exploitability: Very Low
&nbsp;&nbsp; Security Impact: Low
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the code.

&nbsp;&nbsp; This issue affects the key chain system, which is an optional PEFS
&nbsp;&nbsp; feature. If the key chains feature is not used, the file encryption
&nbsp;&nbsp; is unaffected.

&nbsp;&nbsp; The same key is used for encryption and authentication in pefs_key.c.
&nbsp;&nbsp; This happens in the pefs_key_cipher() procedure. The variable &#039;key&#039;
&nbsp;&nbsp; is created by HMACing a distinguisher string with another key called
&nbsp;&nbsp; pxk_key. The resulting &#039;key&#039; is used twice: Once to encrypt the data,
&nbsp;&nbsp; and a second time to HMAC the ciphertext.

&nbsp;&nbsp; This does not create immediate problems, but using the same key for
&nbsp;&nbsp; multiple purposes is a bad practice. Instead, use HKDF to generate
&nbsp;&nbsp; two separate keys for the two different purposes.

2.3. Zero IV Used with CTR Mode in pefs_key.c

&nbsp;&nbsp; Exploitability: Medium (Depends on the use case.)
&nbsp;&nbsp; Security Impact: High
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the code.

&nbsp;&nbsp; This issue affects the key chain system, which is an optional PEFS
&nbsp;&nbsp; feature. If the key chains feature is not used, the file encryption
&nbsp;&nbsp; is unaffected.

&nbsp;&nbsp; The pefs_key_cipher() procedure in pefs_key.c encrypts data using AES
&nbsp;&nbsp; in CTR mode with a zero IV. This means that if multiple messages are
&nbsp;&nbsp; encrypted with the same key, they are XORed with the same keystream.

&nbsp;&nbsp; Here&#039;s an example of how this can be exploited to reveal the context
&nbsp;&nbsp; of another user&#039;s files:

&nbsp;&nbsp; &nbsp; The key chain graph looks like:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;A -&gt; Z
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;A -&gt; S
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;B -&gt; S

&nbsp;&nbsp; &nbsp; The &#039;Z&#039; key is encrypted with a parent key that only Alice knows.
&nbsp;&nbsp; &nbsp; The &#039;S&#039; key is encrypted with Alice&#039;s parent key as well. Bob also
&nbsp;&nbsp; &nbsp; has access to &#039;S&#039;.

&nbsp;&nbsp; In this scenario, Bob knows S, so he can XOR the known value of
&nbsp;&nbsp; S with Alice&#039;s encryption of S to get the key stream generated by
&nbsp;&nbsp; Alice&#039;s parent key. Bob can then XOR this with Alice&#039;s Z ciphertext
&nbsp;&nbsp; to get Z. If Bob did not know S, he could learn Z XOR S by XORing the
&nbsp;&nbsp; two ciphertexts together.

&nbsp;&nbsp; This is a problem whenever a parent key encrypts two or more children
&nbsp;&nbsp; keys. Instead of using a NULL IV, a random IV should be generated at
&nbsp;&nbsp; encryption time and stored in the ciphertext (remember to HMAC the IV
&nbsp;&nbsp; too!).

2.4. No Salt Used When Hashing Password

&nbsp;&nbsp; Exploitability: Medium
&nbsp;&nbsp; Security Impact: Medium
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the code.

&nbsp;&nbsp; When deriving a key from a password, PEFS does not use salt. This is
&nbsp;&nbsp; necessary, otherwise an attacker can use pre-computation attacks
&nbsp;&nbsp; (lookup tables, rainbow tables, etc.) to significantly speed up the
&nbsp;&nbsp; process of cracking PEFS-encrypted directories with weak passwords.

&nbsp;&nbsp; Not using a salt was an explicit design decision to avoid having to
&nbsp;&nbsp; store metadata. I recommend adding an (optional?) mode with metadata
&nbsp;&nbsp; to support having a salt, so that if it fits the user&#039;s use case,
&nbsp;&nbsp; they can benefit from the additional security.

2.5. Ambiguous Filename Encryption Padding

&nbsp;&nbsp; Exploitability: Very Low
&nbsp;&nbsp; Security Impact: Very Low
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the source code.

&nbsp;&nbsp; The filename encryption routine pads the plaintext with null bytes.
&nbsp;&nbsp; This is fine, since file names can&#039;t contain null bytes in UNIX, but
&nbsp;&nbsp; if this code is reused for something else, it could become a problem.
&nbsp;&nbsp; Pad the plaintext unambiguously by using a padding mode like PKCS#7.

2.6. Filename Encryption is Not Randomized When Files are Renamed

&nbsp;&nbsp; Exploitability: Medium (Depends on how the user renames files.)
&nbsp;&nbsp; Security Impact: Low
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the source code.

&nbsp;&nbsp; PEFS uses a random 64-bit value, called the tweak, to differentiate
&nbsp;&nbsp; between encrypted files. This value is actually re-used for three
&nbsp;&nbsp; things. It&#039;s used as part of the XTS mode tweak, to &quot;randomize&quot; the
&nbsp;&nbsp; filename encryption, and as the VMAC IV (after being encrypted).
&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp; For the filename encryption, the tweak value is used as a substitute
&nbsp;&nbsp; for an IV. Filenames are encrypted by first prefixing the tweak, then
&nbsp;&nbsp; encrypting them in CBC mode with a zero IV.

&nbsp;&nbsp; This is a problem, because the tweak does not change when files are
&nbsp;&nbsp; renamed. With CBC mode, the IV has to be chosen randomly at the time
&nbsp;&nbsp; of encryption, or it isn&#039;t IND-CPA secure. If the filename is
&nbsp;&nbsp; changed, the new ciphertext will be identical to the old ciphertext
&nbsp;&nbsp; up to the block that contains the first difference. See the third
&nbsp;&nbsp; part of this image for an awesome visual demonstration:

&nbsp;&nbsp; &nbsp; https://pbs.twimg.com/media/BekWHdVCYAAgsSm.jpg:large

&nbsp;&nbsp; There are plans to solve this problem by encrypting filenames with
&nbsp;&nbsp; wide-block encryption like EME2. This should be an adequate solution,
&nbsp;&nbsp; but it needs more analysis.

&nbsp;&nbsp; There may be other problems with the tweak design as well. More
&nbsp;&nbsp; analysis is recommended in Section 5.3

2.7. Part of Files Encrypted with CTR Mode

&nbsp;&nbsp; Exploitability: High
&nbsp;&nbsp; Security Impact: Medium-High
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the code.

&nbsp;&nbsp; If a file&#039;s size in bytes is not evenly divisible by 16, the last
&nbsp;&nbsp; block of bytes (up to 15 bytes) is encrypted with CTR mode. If the
&nbsp;&nbsp; last block is ever changed, and an attacker has the ciphertexts from
&nbsp;&nbsp; before and after the change, they can XOR both ciphertexts together
&nbsp;&nbsp; to learn the XOR of the old and new plaintexts. If the attacker knows
&nbsp;&nbsp; (or can guess) the old plaintext, they can get the new plaintext.

&nbsp;&nbsp; The relevant code is in xts_smallblock() in pefs_xts.c.

&nbsp;&nbsp; The only good remediation for this (that I can think of) involves
&nbsp;&nbsp; increasing the file size so that it is divisible by 16 bytes. I think
&nbsp;&nbsp; the severity vulnerability justifies the increase in complexity.

2.8. gf_mul128() Does Not Execute In Constant Time

&nbsp;&nbsp; Exploitability: Low
&nbsp;&nbsp; Security Impact: Low
&nbsp;&nbsp; Confirmation: Confirmed by inspecting the source code.

&nbsp;&nbsp; The gf_mul128() procedure, part of the XTS implementation in
&nbsp;&nbsp; pefs_xts.c, branches on a value related to its input, which could
&nbsp;&nbsp; enable side channel attacks:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;static __inline void
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;gf_mul128(uint64_t *dst, const uint64_t *src)
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;static const uint8_t gf_128_fdbk = 0x87;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;int carry;

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;carry = shl128(dst, src);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if (carry != 0) &nbsp;// &lt;-- time difference could leak &#039;carry&#039;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;((uint8_t *)dst)[0] ^= gf_128_fdbk;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;}

&nbsp;&nbsp; I don&#039;t know if this leaks enough information to break the encryption
&nbsp;&nbsp; (very probably not), but it&#039;s better to be safe and do it in constant
&nbsp;&nbsp; time anyway. A constant time conditional XOR is given in [3].

2.9. Memory Corruption / Integer Overflows

&nbsp;&nbsp; Exploitability: Unknown
&nbsp;&nbsp; Security Impact: High (If exploitable)
&nbsp;&nbsp; Confirmation: Not confirmed.

&nbsp;&nbsp; I noticed several bits of code with integer overflow and
&nbsp;&nbsp; signed/unsigned problems. I didn&#039;t have time to check if any of these
&nbsp;&nbsp; are exploitable, or are even values controlled by the attacker, but
&nbsp;&nbsp; I will list them here to err on the side of caution:

&nbsp;&nbsp; &nbsp; - In pefs_name_encrypt(), the &quot;+ 1&quot; in the file name size check
&nbsp;&nbsp; &nbsp; &nbsp; could overflow the integer and make the check pass even if
&nbsp;&nbsp; &nbsp; &nbsp; the filename is too long:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; r = PEFS_NAME_NTOP_SIZE(pefs_name_padsize(size)) + 1;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if (r &gt; MAXNAMLEN) {
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return (-ENAMETOOLONG);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; }

&nbsp;&nbsp; &nbsp; - In pefs_enccn_set(), the variable encname_len is a *signed*
&nbsp;&nbsp; &nbsp; &nbsp; integer. It is checked to be less than MAXPATHLEN then passed to
&nbsp;&nbsp; &nbsp; &nbsp; memcpy(). When it gets passed to memcpy(), it is cast to
&nbsp;&nbsp; &nbsp; &nbsp; a size_t, which is unsigned. If encname_len has a negative value,
&nbsp;&nbsp; &nbsp; &nbsp; it will pass the first check, then memcpy will interpret it as
&nbsp;&nbsp; &nbsp; &nbsp; a large unsigned value:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if (encname_len &gt;= MAXPATHLEN)
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;panic(&quot;pefs_enccn_set: invalid encrypted name length: %d&quot;,
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;encname_len);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; // ...
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; memcpy(pec-&gt;pec_buf, encname, encname_len);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; ((char *) pec-&gt;pec_buf)[encname_len] = &#039;\0&#039;;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; pec-&gt;pec_cn.cn_namelen = encname_len;

&nbsp;&nbsp; &nbsp;- Several possibilities for integer overflows in pefs_xbase64.c:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if (datalength + 1 &gt; targsize)
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return (-1);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; // ...
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; target[tarindex] &nbsp; |= &nbsp;(pos - Base64) &gt;&gt; 4;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if ((size_t)tarindex + 1 &lt; targsize)
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; target[tarindex+1] =
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ((pos - Base64) &amp; 0x0f) &lt;&lt; 4 ;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; // ...
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; target[tarindex] &nbsp; |= &nbsp;(pos - Base64) &gt;&gt; 2;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if ((size_t)tarindex + 1 &lt; targsize)
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; target[tarindex+1] =
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;((pos - Base64) &amp; 0x03) &lt;&lt; 6;

&nbsp;&nbsp; &nbsp;- Another in pefs_write_int() that could cause the
&nbsp;&nbsp; &nbsp; &nbsp;vnode_pager_setsize() call to be skipped, which might lead to
&nbsp;&nbsp; &nbsp; &nbsp;memory corruption down the road:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; nsize = fsize;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; MPASS(uio-&gt;uio_offset &lt;= fsize);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if (uio-&gt;uio_offset + uio-&gt;uio_resid &gt; nsize) {
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;PEFSDEBUG(&quot;pefs_write: extend: 0x%jx (old size: 0x%jx)\n&quot;,
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;uio-&gt;uio_offset + uio-&gt;uio_resid, nsize);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;nsize = uio-&gt;uio_offset + uio-&gt;uio_resid;
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;vnode_pager_setsize(vp, nsize);
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; }

3. Commendations

&nbsp;&nbsp; PEFS does a lot of things right. It uses standard constructions like
&nbsp;&nbsp; XTS mode, PBKDF2, and HKDF. It also diligently zeroes buffers that
&nbsp;&nbsp; once contained sensitive information. This makes auditing easier.

4. Recommendations

&nbsp;&nbsp; There are some things that PEFS could do better:

&nbsp;&nbsp; &nbsp; - Instead of re-implementing CBC mode in pefs_name_enccbc(), it
&nbsp;&nbsp; &nbsp; &nbsp; would be better to use a well-tested implementation from a crypto
&nbsp;&nbsp; &nbsp; &nbsp; library.

&nbsp;&nbsp; &nbsp; - Use test vectors in unit tests and runtime tests to make sure the
&nbsp;&nbsp; &nbsp; &nbsp; crypto algorithms (XTS, HKDF, PBKDF2, AES, SHA, etc.) are
&nbsp;&nbsp; &nbsp; &nbsp; correct.

&nbsp;&nbsp; &nbsp; - Make pefs_hkdf_expand() take a uint8_t instead of an int for the
&nbsp;&nbsp; &nbsp; &nbsp; &#039;idx&#039; parameter, so that there is a compiler warning when the
&nbsp;&nbsp; &nbsp; &nbsp; function is used incorrectly. Or check that &#039;idx&#039; is between
&nbsp;&nbsp; &nbsp; &nbsp; 0 and 255.

5. Future Work

5.1. Memory Corruption Vulnerabilities

&nbsp;&nbsp; This audit did not focus on &quot;classic&quot; memory corruptions
&nbsp;&nbsp; vulnerabilities. Because of the integer overflow issues documented in
&nbsp;&nbsp; Issue 2.9, I think PEFS could benefit from an audit that specifically
&nbsp;&nbsp; focuses on these concerns.

5.2. The Tweak&#039;s Triple Burden

&nbsp;&nbsp; The 64-bit random tweak is re-used for three different purposes:

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;1. It acts like an IV for the filename encryption.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;2. It is part of the XTS mode tweak.
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;3. (After being Encrypted) The VMAC IV.

&nbsp;&nbsp; There was not enough time in the audit to determine whether any of
&nbsp;&nbsp; these three uses interact in negative ways that would, for example,
&nbsp;&nbsp; lead to at least one of filename encryption, file contents
&nbsp;&nbsp; encryption, or the VMAC being broken. More analysis is needed.

&nbsp;&nbsp; The VMAC&#039;s IV appears to be taken from its input, which probably
&nbsp;&nbsp; causes problems. This issue was not investigated in this audit since
&nbsp;&nbsp; the VMAC is only used as a non-cryptographic checksum, and should not
&nbsp;&nbsp; be relied on for security.

6. Conclusion

&nbsp;&nbsp; This audit found several issues, the most significant of which are
&nbsp;&nbsp; issues 2.3, 2.6, and 2.7. The possible existence of memory corruption
&nbsp;&nbsp; bugs (Issue 2.9) is worrisome as well, since this code runs in kernel
&nbsp;&nbsp; space.

&nbsp;&nbsp; PEFS would benefit from more auditing time.

7. References

&nbsp;&nbsp; [1] https://wiki.freebsd.org/PEFS

&nbsp;&nbsp; [2] https://github.com/glk/pefs

&nbsp;&nbsp; [3] http://www.iacr.org/archive/ches2009/57470001/57470001.pdf
</pre>
