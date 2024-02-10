<?php
    Upvote::render_arrows(
        "godelsecondtms",
        "defuse_pages",
        "A Simple Proof of Gödel's Second Incompleteness Theorem Using Turing Machines",
        "Proving Gödel's second incompleteness theorem in a way that should be understandable to anyone who understands the proof that the halting problem is undecidable.",
        "https://defuse.ca/godel-second-incompleteness-theorems-by-turing-machines.php"
    );
?>
<div class="pagedate">
February 9, 2024
</div>

<h1>A Simple Proof of Gödel's Second Incompleteness Theorem Using Turing Machines</h1>

<p>
Gödel's second incompleteness theorem, in a nutshell, says that if a certain
kind of mathematical theory can prove its own consistency (i.e. that it does not
prove any contradictions), then that mathematical theory must itself be
inconsistent.
</p>

<p>
Gödel's original proof of the second theorem is quite complicated, and even
modern treatments of the full theorem involve hard-to-wrap-your-head-around
concepts like Gödel numbering, <a
href="https://en.wikipedia.org/wiki/%CE%A9-consistent_theory">ω-consistency</a>,
and <a href="https://en.wikipedia.org/wiki/Rosser%27s_trick">Rosser's trick</a>. 
</p>

<p>
It turns out that all of this complexity can be avoided if you're willing to
consider a version of the theorem that applies to mathematical theories strong
enough to easily express strings and Turing machines. All of the Gödel-numbering
complexity is necessary to make the theorem work in weaker systems like Peano
arithmetic, but if you just want to prove it for theories like <a href="https://en.wikipedia.org/wiki/Zermelo%E2%80%93Fraenkel_set_theory">ZF(C)</a>, where most
of modern mathematics operates, the proof is much easier, and we'll sketch that
proof below.
</p>

<p>
I claim that if you can understand the proof that the halting problem is
undecidable, then you can understand the proof of Gödel's second incompleteness
theorem for theories like ZF(C).
</p>

<p>
Let's let <em>T</em> be the mathematical theory we're working in. For example,
<em>T</em> could be ZF(C) set theory. You should imagine that all of the
arguments we're about to give are translated into the formal language of <em>T</em>.
</p>

<p>
We need to make some assumptions about <em>T</em>, namely that there's an algorithm for
checking proofs in <em>T</em> that always halts and tells us whether a proof is correct
or not, which we'll call <em>Prf<sub>T</sub></em>. The string <em>p</em> is a
valid proof of the statement <em>x</em> within the theory T if and only if
<em>Prf<sub>T</sub>(p, x) = true</em>. 
</p>

<p>
A statement <em>x</em> is provable in <em>T</em> if and only if there exists
some string <em>p</em> (the proof) such that <em>Prf<sub>T</sub>(p, x) =
true</em>.
</p>

<p>
What it means for T to be consistent is that it cannot prove a contradiction,
i.e. there is no string <em>A</em> such that <em>A</em> and <em>¬A</em> are both
provable in T.
</p>

<p>
Gödel's second theorem says that if <em>T</em> can prove its own consistency,
i.e. that there is no <em>A</em> such that both <em>A</em> and
<em>¬A</em> are provable within <em>T</em>, then <em>T</em> has to be
inconsistent.
</p>

<p>
To prove this, we'll start by defining a Turing machine <em>M</em> that does the following:
</p>

<p>
On input <em>X</em>:
</p>

<ol>
    <li>For all strings <em>p</em> in lexicographical order: if <em>Prf<sub>T</sub>(p, &quot;X(X) never halts&quot;)</em>, halt.</li>
</ol>

<p>
If we run <em>M</em> on itself, <em>M(M)</em>, then what it will do is start
enumerating all possible strings, looking for a valid proof in <em>T</em> that
<em>M</em> halts when it's given a description of itself as input. Crucially,
<em>Prf<sub>T</sub></em>, the proof-checking algorithm, always halts, so
<em>M(M)</em> halts if and only if there exists a proof within <em>T</em> that
<em>M(M)</em> never halts.
</p>

<p>
Note that if <em>M(M)</em> halts, then <em>T</em> has to be inconsistent. Since
<em>M(M)</em> halted, it found a proof that <em>M(M)</em> doesn't halt. But
since <em>M(M)</em> halted, there's also a proof that <em>M(M)</em> halts, which
can be made by executing <em>M(M)</em> one step at a time inside a <em>T</em> proof until
it halts. With a proof that <em>M(M)</em> halts and a proof that <em>M(M)</em>
doesn't halt, we have a proof of a contradiction in <em>T</em>.
</p>

<p>
In other words, we've proven...
</p>

<p>
<em>M(M) halts → ¬Con(T)</em>
</p>

<p>
...where <em>Con(T)</em> means that <em>T</em> is consistent.
</p>

<p>
Now suppose our theory <em>T</em> can prove its own consistency, i.e. there's a proof of <em>Con(T)</em> within <em>T</em> itself. 
</p>

<p>
Using this, we can prove that <em>M(M)</em> never halts:
</p>

<ol>
    <li><em>Con(T)</em> (by the proof we suppose exists)</li>
    <li><em>M(M) halts → ¬Con(T)</em> (as above)</li>
    <li>Therefore, <em>¬(M(M) halts)</em>.
</ol>

<p>
But this argument can be turned into a valid proof in <em>T</em>, and so
<em>M(M)</em> will eventually find it, and once it does, it will halt. This
means that:
</p>

<p>
<em>Con(T) is provable in T → M(M) halts</em>.
</p>

<p>
We can now complete the argument:
</p>

<ol>
    <li><em>Con(T) is provable in T → M(M) halts</em></li>
    <li><em>M(M) halts → ¬Con(T)</em></li>
    <li>Therefore, <em>Con(T) is provable in T → ¬Con(T)</em>.</li>
</ol>

<p>
Summarizing, in English: if <em>T</em> can prove its own consistency, then
there's a proof that <em>M(M)</em> never halts. But since <em>M(M)</em> will
find that proof, <em>M(M)</em> will actually halt, and since <em>M(M)</em>
halts, <em>T</em> is inconsistent. We've established Gödel's second
incompleteness theorem for <em>T</em>.
<p>

<p>
The only requirement for this argument to work is that <em>T</em> is powerful
enough to express strings and Turing machines, and to formalize all of the
arguments given above.
</p>

<p>
There you go! A proof of Gödel's second incompleteness theorem that's no harder
to understand than the proof that the halting problem is undecidable!
</p>