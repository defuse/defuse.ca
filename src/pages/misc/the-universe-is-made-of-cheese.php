<?php
    Upvote::render_arrows(
        "universeOfCheese",
        "defuse_pages",
        "The Cheese Universe Paradox",
        "A formal proof that the universe is made entirely of cheese.",
        "https://defuse.ca/the-universe-is-made-of-cheese.htm"
    );
?>
<h1>The Cheese Paradox</h1>


<p>
What follows is a simple proof that the universe is entirely filled with cheese.
Can you find the error(s)?
</p>

<p>
<b>Claim:</b> All points of space-time are simultaneously occupied by
a humongous block of cheese.
<p>

<p>
<b>Proof:</b>
</p>

<p>
Let <i>FST(x)</i> denote the amount of space-time that is not occupied by an
object <i>x</i>.  This function evaluates to a positive real number or to some
form of infinity, in units of meter-cubed-seconds (<i>m<sup>3</sup>s</i>). This
function can be thought of as being equivalent to the size of the universe minus
the size of the object <i>x</i>, though it need not be defined that way (in
fact, it should not be defined that way, since it would be undefined if either
the size of the universe or the object were infinite).
</p>

<p> 
Let <i>c</i> be the largest <em>conceivable</em> block of cheese
i.e. with FST(c) being minimal.  In other words, there is no conceivable block
of cheese <i>c'</i> such that <i>FST(c') &lt; FST(c)</i>.  We can imagine
a block of cheese that leaves all but a finite amount of space-time unoccupied.
Clearly, then, <i>FST(c)</i> is finite, since all positive infinities are larger
than all positive finite reals.  So there exists at most a finite amount of
space-time that is not occupied by this block of cheese, <i>c</i>.
</p>

<p>
Further, <em>every</em> point in space-time must be occupied by <i>c</i>, since
if it were not, we could conceive of a block of cheese <i>c'</i> with <i>FST(c')
&lt; FST(c)</i> by placing cheese in the space not occupied by <i>c</i> and then
melting it into <i>c</i>. But there is no such block of cheese <i>c'</i> for
which <i>FST(c') &lt; FST(c)</i>, so this is a contradiction, and therefore it
must be the case that every point in space-time is occupied by <i>c</i>. So
<i>FST(c) = 0</i>.
</p>

<p>
So far it has been shown that there is a conceivable, or theoretical, block of
cheese that occupies every point of space-time. But this object must in fact
<em>be real</em>, since if it did not exist in the real world, our universe,
there would be some points in space-time which it does not occupy, so <i>FST(c)
> 0</i>. But <i>FST(c) = 0</i>, so that would be a contradiction. Therefore <i>c</i>
really exists in our physical world.
</p>

<p>
Therefore, every point in space-time, including those in the real world, are
occupied by cheese.  So, in the past, present, and future, every point of space,
including the space of which our universe consists, has been, is, and forever
will be occupied by cheese. 
</p>

<p>
<b>Open Question:</b> While the existence of this block of cheese has been
deductively established, it remains unknown <em>what kind</em> of cheese it
is. Is it cheddar, gouda, or havarti? Or is it a mix of different kinds of
cheese?
</p>


<h2>Proposed Resolutions:</h2>

<p>
Think you know what's wrong? Send me an <a href="/contact.htm">email</a> or <a
href="https://twitter.com/defusesec">tweet me</a> and I'll add your response
here.
</p>

<p>
Here's one response from ctamblyn. I haven't had time to check it, but it sounds
right:
</p>

<pre>
Here’s my take on it:

In the first three paragraphs, the function *FST* was clearly meant to
denote something which maps objects *whether imagined or real* onto
space-time volumes.  The mere fact that we can compute *FST*(*x*) for some
*x* does not require that *x* exists.  Thus, the step in the second
sentence of the fourth paragraph that goes “if it did not exist in the real
world … *FST*(*c*) &gt; 0” is invalid, and the attempted *reductio ad
absurdum*falls apart.

Alternatively, if *FST* was intended only to apply to actually existing
objects, the first paragraph is already assuming the conclusion, namely
that *c* exists, and the argument is circular.
</pre>

