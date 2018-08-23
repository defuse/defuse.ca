<?php
    Upvote::render_arrows(
        "mastersthesis",
        "defuse_pages",
        "Concentration Bounds from Parallel Repetition Theorems",
        "My master's thesis, showing how concentration bounds can be derived from parallel repetition theorems for nonlocal games and interactive proofs.",
        "https://defuse.ca/concentration-bounds-from-parallel-repetition-theorems.htm"
    );
?>
<div class="pagedate">
August 23, 2018
</div>
<h1>Concentration Bounds from Parallel Repetition Theorems (My Master's Thesis)</h1>

<p>
My master's thesis, titled "Concentration Bounds from Parallel Repetition
Theorems" was just accepted. You can find a link to it below. Here's
a point-form summary:
</p>

<ul>
    <li>(Background info) There are protocols called nonlocal games, where two players collaborate
to try to answer a referee's questions correctly. The players aren't allowed to
communicate, yet they still want to work together to maximize their probability
of winning. There are some games that can be won more easily when the players
share quantum entanglement, so studying these games is important to
understanding entanglement.</li>
    <li>(Background info) There are also interactive proofs, where a powerful
prover tries to convince a weak (e.g. polynomial-time) verifier that something
is true (e.g. that a string is in a language).</li>
    <li>(Background info) It's important to be able to reduce the error in both types of protocol.
For nonlocal games, that means repeating the game to get a more accurate
upper-bound on the players' chance of winning. For interactive proofs, it means
repeating the protocol so that the verifier can be more sure that the prover
isn't getting lucky and convincing the verifier of something false.</li>
    <li>(Background info) It's one thing to repeat the protocols sequentially in time. Doing that,
you can reduce error in the obvious ways. But it's useful to be able to reduce
error by repeating the protocols in parallel, i.e. running multiple instances of
the protocol at the same time. It's not so clear that you can reduce error that
way, because the prover (or players) might have some clever trick to win more
often when there are many instances of the protocol going on at the same
time.</li>
    <li>(Background info) In order to reduce error through parallel repetition,
you need something I (somewhat erroneously) call a "concentration bound."
A concentration bound is a theorem of the form "The probability that the prover
(or players) can win at least this fraction of this many parallel repetitions of
this protocol is less than...."</li>
    <li>(Background info) Compare that to "parallel repetition theorems" which
have the form "The probability that the prover (or players) can win <em>all</em>
of this many parallel repetitions of this protocol is less than..."</li>
    <li>(Contribution) There are lots of special types of nonlocal games where
a parallel repetition theorem is known, but not a concentration bound. My main
result is to come up with a technique for converting parallel repetition
theorems into concentration bounds. Then I use that technique to create
concentration bounds out of a bunch of known parallel repetition theorems.
You'll have to read Chapter 2 to find out how the technique works.</li>
    <li>(Contribution) The technique also works for interactive proofs, and
I show that you can reduce error for <em>quantum</em> interactive proofs using
the standard parallel repetition techniques that are already known to work for
classical interactive proofs. This result was already almost established, the
previous result only works if the soundness error and completeness error of the
protocol being repeated are separated by a special function. My result just
removes that requirement (at the cost of requiring a log-factor more
repetitions).
</ul>

<center>
    <p><b><a href="http://hdl.handle.net/10012/13638">Concentration Bounds From
Parallel Repetition Theorems (on UWSpace)</a></b></p>
</center>

<p>
Unfortunately, a chapter on a topic that I really wanted to discuss didn't make
it into the accepted version of my thesis. Eventually, I hope to turn this
chapter into a paper and publish it, but for now, you can find a link to the
"bonus chapter" below. Here's the point-form summary:
</p>

<ul>
    <li>Your iPhone uses tamper-resistant hardware to make an
easily-brute-forceable short PIN code secure enough to protect your data.
Basically, the tamper-resistant device holds a longer encryption key and refuses
to give it up unless it sees the correct PIN code.</li>
    <li>The question I'm interested in is: Can we build a quantum-crypto
primitive that accomplishes the same task (protect a long secure encryption key
with a short easily-brute-forced PIN/password) <em>without</em> relying on
tamper-resistant hardware?</li>
    <li>The fact that quantum information can't always be cloned, and that
measuring a quantum state can disturb it gives hope that it might be possible.
We might be able to encode a long key into a quantum state, such that you need
to know the short key to figure out how it's encoded, and if you don't know the
short key you're likely to make incorrect measurements and destroy information
about the long key.</li>
    <li>I try to capture this idea by definining a new kind of primitive called
an "offline key expander." Now, the question is: do these things exist or
not?</li>
    <li>After trying, I couldn't rule them out, nor prove any are secure. So,
I do the next best thing and put forward a design called Probabilistic Conjugate
Coding (PCC) that seems like it could possibly work and then I try to break it.</li>
    <li>I show an attack on PCC that works whenever the long key is too much
longer than the short key. The attack is basically to Grover search for the
short key, except it's a little tricky to show that you can still do a Grover
search when you have to implement the function you're searching with a quantum
state, and you only have one copy of that state. I'm uncertain if the attack can
be improved to break PCC in general, but I suggest an idea that might improve
the proof to show that the same attack works against PCC for all parameter
ranges.</li>
</ul>

<center>
    <p><b><a href="https://defuse.ca/downloads/SymmetricKeyStrengthening.pdf">Bonus Chapter: Strengthening Symmetric Keys with Quantum Information</a></b></p>
</center>

