<h1>Triangle of Secure Code Delivery</h1>

<p>
I have a proposal for a "triangle", or 3 things, that are necessary and
sufficient for code delivery to be secure. The three points of the triangle are:
</p>

<ol>
    <li>
        <strong>Reproducible Builds:</strong>
<p>
<i>
Given the application's source code, it should be possible to reproduce the
distributed package exactly, down to contents that are known to vary benignly,
such as build timestamps.
</i>
</p>

<p>
This property is important for auditing. A developer can sign both the source
code and distributed binary package, but how does the user (or, more likely,
a security auditor) know the source code actually represents the binary? To be
sure, it has to be possible to re-create the binary package from the source
exactly, or at least without unexplained differences. This provides some defense
if the software's developers turn malicious or are successfully attacked.
</p>

    </li>

    <li>
        <strong>Commonality Verification:</strong>
<p>
<i>
Users of the software should be able to check that the package they received is
identical to the one that all other user received. These packages should be
available permanently in a public record.
</i>
</p>

<p>
This is the most important of the three properties. Simply put: Everyone gets
the same thing. If you can guarantee that everyone gets an identical copy of the
software, then it becomes impossible to hide a targeted attack. If an attacker
wants to backdoor one user's software, they have to backdoor every user's
software. This greatly increases the attacker's risk of being detected. 
</p>

    </li>

    <li>
        <strong>Cryptographic Signing:</strong>
<p>
<i>
The software package, source code, and patches (changes) should be
cryptographically signed by the upstream software source (i.e. the developers). 
</i>
</p>

<p>
This serves to establish an anchor of trust to a person or organization
responsible for maintaining the software. Without this property, a window of
vulnerability exists before the software gets distributed widely enough for the
second property (Commonality Verification) to be effective.
</p>

    </li>

</ol>

<p>
I conjecture that these three properties are sufficient to disincentivize both
large-scale attacks (i.e. the NSA wants to put a vulnerability in everyone's
copy of Tor) and localized targeted attacks (i.e. the NSA wants to compromise
a single user's .exe download to take control of their system). It's
<em>not</em> enough to be NSA-proof, but it might be enough to force the NSA
into another avenue for exploitation.
</p>

<p>
Having just two of these properties is not enough:
</p>

<ul>
    <li>
        <strong>Without Reproducible Builds:</strong>
<p>
Without Reproducible Builds, the software developer can be <a
href="https://blog.torproject.org/blog/deterministic-builds-part-two-technical-details">compromised</a>
and backdoors can be inserted into binaries prior to signing. With the binary
distributed widely and in the public record, detection is still a risk for the
attacker, but only if lots of people are looking very closely. Detection is
still possible, but with reproducible builds, it becomes immediate.
</p>
    </li>
    <li>
        <strong>Without Commonality Verification:</strong>
<p>
Without Commonality Verification, localized targeted attacks are much easier.
This is especially true when the software developers themselves are malicious
(or controlled by the NSA), and they want to serve backdoored copies to some
users, but clean copies to most users.
</p>
    </li>
    <li>
        <strong>Without Developer Signing:</strong>
<p>
Without this property, there's a window of opportunity for an attack to happen
between the time when a new version of the software is released and when it
becomes widely publicized and the Commonality Verification becomes effective. 
</p>
    </li>
</ul>
