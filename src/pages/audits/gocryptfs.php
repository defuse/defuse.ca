<?php
    Upvote::render_arrows(
        "auditgocryptfs0",
        "defuse_pages",
        "Gocryptfs Security Audit",
        "A security audit of the gocryptfs encrypted filesystem.",
        "https://defuse.ca/audits/gocryptfs.htm"
    );
?>
<div class="pagedate">
March 3, 2017
</div>

<h1>Gocryptfs Security Audit</h1>

<p>
I've completed a two-day audit of <a
href="https://nuetzlich.net/gocryptfs/">gocryptfs</a>'s cryptography design. You
can download the audit report by clicking the link below. Thanks to 23andMe for
funding this audit.
</p>

<h2>Abstract</h2>

<p>
This report documents a two-day audit of the gocryptfs encrypted
filesystem. Unlike full-disk encryption systems, gocryptfs encrypts
files individually using chunked AES-GCM (Galois Counter Mode) and encrypts
filenames with AES-EME (ECB-Mix-ECB). Our audit focused on the crypgography
design of gocryptfs's main file encryption features; it excluded
its dependencies and its more complicated "Reverse Mode" feature that uses
deterministic AES-SIV (Synthetic Initialization Vector) encryption. We did
not look at the implementation code except when it was necessary to
understand some aspect of the design.
</p>

<p>
We found that gocryptfs provides excellent confidentiality against
a passive adversary, i.e. one that does not tamper with the encrypted
files. On the other hand, we found that gocryptfs provides no
security at all against an active adversary who can modify the ciphertexts
while having read access to any subdirectory of the mounted filesystem.
Against a less-powerful active adversary who can modify the ciphertexts but
has <i>no</i> access to the mounted filesystem, gocryptfs keeps
file contents secret and provides imperfect integrity protection. In at
least one case, imperfections in the integrity protections lead to a break
of <i>confidentiality</i>. It is possible that the integrity imperfections
lead to further confidentiality breaks depending on which applications are
using the filesystem.
</p>

<p>
We believe the reason these vulnerabilities exist is because
gocryptfs doesn't have a clearly spelled-out threat model. Some of
the attacks seem hard to avoid given gocryptfs's performance goals
and may have been introduced "by design" to meet these goals. We suggest
writing down an explicit threat model and updating the website to better
communicate the security guarantees that gocryptfs provides. This
way, users are less likely to rely on it in ways which would make them
vulnerable.
</p>

<h2>Download</h2>

<p style="text-align: center;">
    <a href="/downloads/audits/gocryptfs-cryptography-design-audit.pdf">Security Audit of gocryptfs v1.2 (PDF)</a>
</p>

