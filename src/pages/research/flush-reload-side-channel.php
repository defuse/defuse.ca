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
?>
<h1>FLUSH+RELOAD: Multi-User Systems are Doomed</h1>

<p>
I just finished reading a paper<?php $b->cite("1"); ?> in which Yarom and
Falkner describe an L3 cache side-channel attack. The attack allows a 'spy'
process to precisely monitor what a 'victim' process is doing, even if the
victim process is owned by a different user and is running on a different CPU
core.
</p>

<p>
The attack works by evicting an interesting section of code in the victim
processs out of the L3 cache, waiting a bit, then measuring the time it takes to
access that code. If the victim process executes the code while the spy process
is waiting, it will get put back into the cache, and the spy process's access to
that code will be fast. If the victim process doesn't execute the code, it will
stay out of the cache, and the spy process's access will be slow. So, by
measuring the access time after the wait, the spy can tell whether or not the
victim executed that code during the wait interval.
</p>

<p>
The paper demonstrates that this can be done with a resolution of 1.5 MHz, or
about one measurement every 0.66 microseconds. This is enough to extract the
private key of a GnuPG process, with an error rate of 1.6%, by observing
<em>just one</em> signing operation.
</p>

<p>
This attack clearly shows that it is not safe to use GPG on multi-user systems.
However, it is not limited to GPG. There are many ways it could be used to spy
on other users of the same system. Here are some of the ones I can think of:
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
        SSL: In a shared-hosting environment, get other users' private SSL keys.
        Or, worse, get the hosting provider's private key. Extracting the
        private keys used in another user's web browser for client certificates
        may also be possible.
    </li>
    <li>
        Vim: Observe sections of the normal mode commands to learn what the user
        is doing, and possibly what they are typing.
    </li>
    <li>
        Observe the main() function of common binaries like 'ls', 'cat', 'cd',
        to get a rough idea of what another user is typing into their terminal.
    </li>
    <li>
        Anything in userspace that exectutes in response to any key press. Using
        just the timing of key presses, it may be possible to learn what is
        being typed. It is known that text can be recovered from an audio
        recording of the keyboard clicks <?php $b->cite("2"); $b->cite("3"); ?>.
    </li>
    <li>
        Spell checkers: Learn information about the text being edited.
    </li>
    <li>
        Code that runs when the mouse is moved or clicked: Learn which
        direction the user is moving the mouse and where they click on the
        screen.
    </li>
</ul>

<p>
Essentially, this attack turns all "if", "while", and "for" statements into
information leaks. This is extremely frightening. We are left wondering how
effective the isolation between users actually is, and whether or not it's all
just an illusion. What we do know for sure is that the barrier between users is
full of holes.
</p>

<p>
This is an extremely difficult problem to fix. We have to either stop using
conditional branches or remove the side-channel from the hardware. Fixing the
hardware would be prohibitively expensive and would reduce performance, and it
certainly isn't possible to write useful programs without conditional branching.
</p>

<?php $b->printBibliography(); ?>
