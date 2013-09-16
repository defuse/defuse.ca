<?php
    Upvote::render_arrows(
        "flushreload",
        "defuse_pages",
        "FLUSH+RELOAD: Multi-User Systems are Doomed",
        "The FLUSH+RELOAD side-channel attack breaks the security of multi-user systems.",
        "https://defuse.ca/flush-reload-side-channel.htm"
    );
    $b = new Bibliography();
    $b->addReference(
        "1",
        "FLUSH+RELOAD: a High Resolution, Low Noise, L3 Cache Side-Channel Attack",
        "Yuval Yarom, Katrina Falkner",
        "July 18, 2013",
        "http://eprint.iacr.org/2013/448.pdf"
    );
    $b->addReference(
        "2",
        "Keyboard Acoustic Emanations",
        "Dmitry Asonov, Rakesh Agrawal",
        "2004",
        "http://rakesh.agrawal-family.com/papers/ssp04kba.pdf"
    );
    $b->addReference(
        "3",
        "Keyboard Acoustic Emanations Revisited",
        "Li Zhuang, Feng Zhou, J. D. Tygar",
        "November 2005",
        "http://www.cs.berkeley.edu/~tygar/papers/Keyboard_Acoustic_Emanations_Revisited/TISSEC.pdf"
    );
    $b->addReference(
        "4",
        "Communication Theory of Secrecy Systems",
        "C. E. Shannon",
        "October 1949",
        "http://www3.alcatel-lucent.com/bstj/vol28-1949/articles/bstj28-4-656.pdf"
    );
?>
<h1>FLUSH+RELOAD: Multi-User Systems are Doomed</h1>

<p>
I read a paper<?php $b->cite("1"); ?> in which Yarom and Falkner describe an L3
cache side-channel attack. The attack allows a 'spy' process to monitor
a 'victim' process's activity, even if the victim process is owned by
a different user and is running on a different CPU core.
</p>

<p>
The attack works by forcing a bit of code in the victim process out of the L3
cache, waiting a bit, then measuring the time it takes to access the code. If
the victim process executes the code while the spy process is waiting, it will
get put back into the cache, and the spy process's access to the code will be
fast. If the victim process doesn't execute the code, it will stay out of the
cache, and the spy process's access will be slow. So, by measuring the access
time, the spy can tell whether or not the victim executed the code during the
wait interval.
</p>

<br />
<center>
<img src="/images/flush-reload-timing.png">
</center>
<br />

<p>
The paper demonstrates that this can be done with a resolution of 1.5 MHz. The
authors show that it is enough to extract an RSA private key from a GnuPG
process, with an error rate of only 1.6%, by observing <em>just one</em> signing
operation.
</p>

<br />
<center>
<img src="/images/flush-reload-square-reduce-multiply.png">
</center>
<br />

<h2>Speculation</h2>

<p>
This implementation of the attack shows that it is not safe to use GnuPG on
multi-user systems.  However, it is not limited to GnuPG. It can be used to spy
on another user in many different ways. Here are some that I can think of:
</p>

<ul>
    <li>
        Compression: Learn partial or complete contents of the file(s) being
        compressed/decompressed.
    </li>
    <li>
        SSH: Extract the secret keys used for public-key authentication when
        a user connects to an SSH server.
    </li>
    <li>
        SSL: In a shared-hosting environment, get other users' private keys.
        Or, worse, get the hosting provider's private key. Extracting the
        private keys used by another user's web browser for client certificates
        may also be possible.
    </li>
    <li>
        Vim: Observe normal mode commands to learn what the user is doing, and
        possibly what they are typing into the document.
    </li>
    <li>
        Observe the main() function of common binaries like 'ls', 'cat', 'cd',
        to get a rough idea of what another user is typing into their terminal.
    </li>
    <li>
        Anything in userspace that exectutes in response to any key press. It
        may be possible to learn what's being typed from the timing of key
        presses alone. It is already well-known that the text can be recovered
        from an audio recording of the typing<?php $b->cite("2"); $b->cite("3");
        ?>.
    </li>
    <li>
        Code that runs when the mouse is moved, clicked, or hovered: Learn which
        direction the user is moving the mouse, where they hover, and where they
        click.
    </li>
    <li>
        Spell checkers.
    </li>
</ul>

<p>
Essentially, this attack turns all "if", "while", and "for" statements into
information leaks. This is extremely frightening. We are left wondering how
effective the isolation between users actually is, and whether or not it's all
just an illusion. What we do know for sure is that the barrier that supposedly
exists between users is full of holes.
</p>

<p>
Everyone worries about vulnerabilities that give attackers root access. With
root access, an attacker can read all users' files. But what if they don't need
root access to get what they want, since it's all leaking through side-channels?
</p>

<h2>Solution</h2>

<p>
It's hard to solve this problem. To counter the FLUSH+RELOAD attack, it seems
like you'd have to either (1) prevent non-privileged users from accessing the
current time, (2) stop using conditional branches, or (3) remove the possibility
of side-channel attacks (the shared caches) from the hardware architecture. None
of these seem reasonable.
</p>

<p>
I will propose a definition of what it means for a system's user isolation to be
secure. The property is called "Perfect Isolation" and is similar to the
definition of perfect secrecy<?php $b->cite("4"); ?> from cryptography:
</p>

<blockquote>
Perfect Isolation: <i>Suppose Alice and Bob are users of a system. Give Alice
a random bit. Have Alice try to tell Bob the bit. Have Bob try to receive the
bit. The system has Perfect Isolation if Bob cannot guess the bit with greater
than 1/2 probability.</i>
</blockquote>

<p>
Most, if not all, of our systems fail to meet this definition. If it is too
strong to meet in practice, then it can be weakened. For example, we might only
give Alice and Bob a polynomial amount of time to transfer the bit. It could be
weakened further by requiring more than one bit to be transfered, but even the
ability to transfer small messages (e.g. an 128-bit encryption key) is dangerous
in practice.
</p>

<p>
We will see more attacks like FLUSH+RELOAD in the near future. It's time to
start using formal definitions of security like Perfect Isolation to evaluate
our systems. We'll always be playing catch-up if we try to solve the
side-channel problem on an attack-by-attack basis.
</p>

<?php $b->printBibliography(); ?>
