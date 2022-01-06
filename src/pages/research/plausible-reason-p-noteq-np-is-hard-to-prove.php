<?php
    Upvote::render_arrows(
        "plausiblepneqnpprove",
        "defuse_pages",
        "A Plausible Reason It's So Hard To Prove P!=NP",
        "Attempting to show why P!=NP is hard to prove using hash functions.",
        "https://defuse.ca/plausible-reason-p-noteq-np-is-hard-to-prove.htm"
    );
?>
<div class="pagedate">
January 5, 2022
</div>
<h1>A Plausible Reason It's So Hard To Prove P!=NP</h1>


<p>
Let’s do something that seems insane at first: <strong>let’s try to solve NP-complete
decision problems using the “random” outputs of cryptographic hash functions</strong>.
</p>

<p>
It doesn’t matter exactly which hash functions we use. In fact, in this
argument, the hash functions are really a stand-in for any polynomial-time
algorithm, but I want to take advantage of your intuitive sense of what
a cryptographic hash function is, so I’m calling them hash functions. The
intuitive properties I want you to have in mind are that they are
random-behaving and computationally irreducible, i.e. there’s no way to learn or
predict properties of their output without actually computing them.
</p>

<p>
A bit of notation will help make things clear. Let H<sub>1</sub>, H<sub>2</sub>, … be any sequence
of keyed polynomial-time hash functions where H<sub>n</sub> takes a key k of size |k|
= n as well as an arbitrary-length input x. For every key k, we can define the
language HL(k) = { x | H<sub>|k|</sub>(k, x)’s first bit is 1 }. In other words, for every
key of unbounded size, there’s a language HL(k), and to find out whether
a string is in HL(k) you simply hash the string with the key k and check if the
first bit of the hash is 1.
</p>

<p>
Now, here’s the question: If we keep passing bigger and bigger keys to bigger
and bigger members of the hash function family, is it possible that we would
stumble upon some enormous key k such that HL(k) is an NP-complete language? If
that happens, then P would equal NP, because although k would be impractically
large (probably so large that its length would put <a
href="https://en.wikipedia.org/wiki/Graham%27s_number">Graham’s number</a> to shame),
we could decide an NP-complete language in polynomial time using one call to
H<sub>n</sub> with the hard-coded key. So, could that happen?
</p>

<p>
There are infinitely many NP-complete languages, so in one sense there are
infinitely many collision targets. Yet, on the other hand, it seems like we need
an infinite sequence of ‘accidental’ bit collisions, one for every string, to
collide an entire language.
</p>

<p>
If the hash functions were truly random, modeled as random oracles, the
probability of a collision would work out to be exactly zero for each
NP-complete language. But that’s a heuristic argument, there’s no real
probabilities involved here! Although we are thinking about the hash functions
as random-behaving, they’re not really random, they’re all deterministic
computations. A language collision is therefore not ruled out by any statistical
argument.
</p>

<p>
An information-theoretic argument can’t rule out a language collision either,
because NP-complete languages have concise descriptions in the form of their
polynomial-time solution-checking machine, and k can be arbitrarily large,
holding arbitrary amounts of information.
</p>

<p>
If P is not equal to NP, then for <em>any</em> choice of hash function family,
all <em>infinitely-many, arbitrary-length</em> keys k, HL(k) must definitely
never collide with <em>any</em> NP-complete language. If P!=NP were
true and unprovable, this wouldn’t be so weird, we’d just say that no language
collision occurs, and that’s just a brute fact about math with no real need for
a reason.
</p>

<p>
However, if P!=NP is true <em>and provable</em>, then this starts to look really
weird. In this case it looks like whatever logic leads to P!=NP is somehow
“forcing” hash functions’ “random” outputs to always miss the NP-complete
languages. The logic in the P!=NP proof would need to explain how these
apparently-structureless functions have a "global" property of never colliding
with an NP-complete language.
</p>

<p>
If we had a proof of P!=NP, then we would <em>know for sure</em> that all hash
functions’ outputs will always miss all of the NP-complete languages,
<em>without ever having to evaluate the hash functions</em>! <em>That</em> seems
really strange, maybe even as strange as a language collision occurring.
</p>

<p>
So, if P!=NP is provable, then our intuitive notion of cryptographic functions
behaving randomly, as well as the concept of computational irreducibility,
become suspect. If we believe computational irreducibility is real, not just
something we think is real because we are computationally bounded humans, we
might be tempted to conclude that one of the following conjectures is true:
</p>

<p>
<strong>Conjecture 1:</strong> P=NP, but only by accident as described above,
for a key so large that we’d never find out. (In this case, P=NP is probably
unprovable, because even if we knew the key and it was small enough to write
down, there's no reason to expect there to be a line of reasoning tying hash function outputs
to the NP-complete language. It’s just an accident.)
</p>

<p>
<strong>Conjecture 2:</strong> P!=NP, but it’s unprovable, because the existence
of a proof would violate our (admittedly informal) notions of computational irreducibility
and cryptographic security.
</p>

<p>
But hold on, there <em>are</em> complexity classes known to be larger than P,
like <a href="https://en.wikipedia.org/wiki/EXPTIME">EXPTIME</a> and classes containing <a href="https://en.wikipedia.org/wiki/Halting_problem">undecidable languages</a>. Why doesn’t this hash
function collision trick apply there, too? Why don’t those proofs, the proof
that P!=EXPTIME, and the proof that the halting problem is undecidable, both
also show that all hash functions have the mysterious language-avoidance
property that we’re worried violates computational irreducibility?
</p>

<p>
The answer can be found in the proofs of those theorems. Both the proof of
P!=EXPTIME through the <a
href="https://en.wikipedia.org/wiki/Time_hierarchy_theorem">time hierarchy
theorem</a> and the proof that the halting problem is undecidable use
diagonalization arguments. They define a language by way of an algorithm which
would simulate the hash functions (in fact all ‘faster’ algorithms) on some
inputs and disagree with them on purpose. Computational irreducibility is not
violated by those results, because the proofs <em>make reference to all of
the hash functions</em>, <em>reference their evaluations</em>, and then construct an
algorithm to disagree with them. In those proofs, it’s not that the hash
functions’ outputs miss the EXPTIME language or the undecidable language by
accident, it’s exactly the other way around: the EXPTIME language or undecidable
language <em>make reference to, and were designed to miss, the hash
functions</em>.
</p>


<p>
Because of completeness, i.e. the fact that there are polynomial-time reductions
between all NP-complete languages, it's not enough to construct <em>one</em>
NP-complete language that misses the hash functions like in the proofs mentioned
above. In fact, <em>ALL</em> NP-complete languages would have to miss
<em>ALL</em> of the hash functions in order for P to not equal NP. It seems that
it's either some cosmic accident that no collision occurs, or languages like SAT
and TQBF (in the case of P vs PSPACE) are "secretly" diagonalizing, somehow, to
disagree with all possible hash functions.
</p>

<p>
If this reasoning is correct, then it would explain why we can’t seem to improve
our results any better than the time hierarchy theorem allows: diagonalization
is the only way to be sure there isn't a collision. It would also explain why we
can’t even rule out <em>linear</em>-time algorithms for NP-complete problems
like SAT: good symmetric cryptography only needs linear time, so for all we know
the hash function that produces the freak collision runs in linear time, too.
Linear-time (or better) lower bounds for SAT or TQBF would count as evidence
against this idea, since any proof of those results would explain to us exactly
how the languages in NP or PSPACE are conspiring to miss all of the linear-time
hash functions, and linear-time hash functions should be just as computationally
irriducible as hash functions with quadratic runtime or greater.
</p>

<p>
If instead of hash functions, we allowed arbitrary <a
href="https://en.wikipedia.org/wiki/Circuit_complexity#Uniformity">uniform</a>
families of polynomial-time functions, then HL(k) equalling an NP-complete
language for some k would exactly be the definition of P=NP. By restricting
ourselves to hash functions, where computational irreducibility makes some kind
of intuitive sense, it’s easier to grasp how a proof of P!=NP either needs to do
time-hierarchy-theorem-style diagonalization or exploit a lack of computational
irreducibility present in all hash functions.
<p>

<p>
Of course this <em>is</em> just an intuitive argument. We haven’t proven
anything here. Perhaps it’s possible to formalize some kind of “computational
irreducibility hypothesis” and show that under that assumption, P!=NP is
unprovable. That’s left as an exercise to the reader.
</p>

<p>
Personally, I think P versus NP is the wrong question to care about. If P=NP
because of an impossible-to-discover collision then who cares? If P!=NP but we
can find machine learning algorithms to solve small cases of "hard" problems
then who cares? Studying concrete complexity, i.e. complexity theory without
hiding constant factors in asymptotics and hiding exponents in the word
“polynomial”, seems like it would be more useful. If we could get good at that,
then we could lower-bound the security of the cryptographic algorithms we
actually use, and maybe even lower-bound the size of a freak-collision-producing
key.
</p>

<p>
Concrete complexity is messy, though, because without big-O notation, the
machine model matters, and we pretty much end up with a different “complexity
theory” for every kind of machine. Unfortunately, I think computational
irreducibility—at least if my intuitive formulation of it is correct—will
prevent us from making much progress there, too.
</p>
