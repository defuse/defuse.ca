<h1>Blockwise Factoring</h1>

<p>I discovered what I will call the "Blockwise Factoring Algorithm" (BFA) while doing some research into the problem of factoring the product of two primes, specifically whether it can be done in polynomial time or not. The BFA isn't polynomial time, but I believe that it, and the thought process that inspired it may be a step towards discovering a polynomial time algorithm.</p>

<h2>The Square-Remainder-Merger Problem</h2>

<p>The first step in understanding the algorithm is understanding how the problem of factoring the product of two primes can be converted into another, seemingly simpler, problem. The problem is, given an X by X square made up of X<sup>2</sup> unit squares (or "blocks"), where X is an integer, and Y "remainder blocks", merge the X<sup>2</sup> square blocks with the Y remainder blocks to form an M by N rectangle made out of X<sup>2</sup> + Y blocks. We call this problem the Square-Remainder-Merger (SRM) Problem. It's a lot easier to visualize.</p>


    <p>Let Z = P * Q, where Q &lt; P are distinct primes. Z can be thought of as the area of a square with dimensions sqrt(Z) by sqrt(Z):</p>

    <center>
        <img src="/images/factoring-z-square.png" alt="A sqrt(Z) by sqrt(Z) square." />
    </center>

<p>It is easy to construct the maximal-area square whose sides are integer lengths that fits within this square. 
   The lengths of the sub-square's sides are floor(sqrt(Z)).
   This leaves us with a floor(sqrt(Z)) by floor(sqrt(Z)) square and Z - floor(sqrt(Z))<sup>2</sup> blocks left over (the red area).
</p>

    <center>
        <img src="/images/factoring-floor.png" alt="A sqrt(Z) by sqrt(Z) square divided into a floor(sqrt(Z))-sided subsquare and remainder area." />
    </center>

<p>
    Now the problem of factoring Z becomes: given the floor(sqrt(Z)) by floor(sqrt(Z)) square (green area), and the remainder blocks (the red area), merge them into an M-block by N-block rectangle. M and N are then the factors of Z (P and Q). This is the Square-Remainder-Merger Problem. The following is an example for P=11, Q=5, Z=5*11=55.
</p>

    <center>
        <b>The Problem:</b><br />
        <img src="/images/factoring-55.png" alt="Factoring 55." />
    </center>

    <center>
        <b>The Solution:</b><br />
        <img src="/images/factoring-55-solution.png" alt="Factoring 55 Solution." />
    </center>
<p>

<p><b>Definition 1:</b> <i>Square-Remainder-Merger (SRM) Problem.</i> <br/>
    An instance of the SRM problem is a pair of integers (A &gt;= 0, B &gt;= 0), where we must merge an A-block by A-block square with B remainder blocks to obtain an M-block by N-block rectangle, where M &gt;= N &gt;= 1.

</p>

<h2>The Blockwise Factoring Algorithm (BFA)</h2>
<p>The Blockwise Factoring Algorithm factors an integer Z=P*Q (Q,P prime, with Q&lt;P) by converting it to an instance of the SRM problem, and then solving the SRM problem, giving P and Q, the factorization of Z.</p>

<h3>Step 1: Converting to an instance of SRM</h3>

<p>Converting the problem of factoring Z into an instance of SRM is easy. As shown above, the problem of factoring Z is equivalent to the following instance of the SRM problem:</p>

<center>
    <p><b>( floor(sqrt(Z)) , Z - floor(sqrt(Z))<sup>2</sup> )</b></p>
</center>

<p>That is, we are given a floor(sqrt(Z)) by floor(sqrt(Z)) square of blocks and Z - floor(sqrt(Z))<sup>2</sup> remainder blocks, and we must merge them into an M by N rectangle of blocks. The dimensions of the resulting rectangle are the factors of Z.</p>

<h3>Step 2: Solving the instance of SRM</h3>

<p>To solve an instance of the SRM problem (A, B), we repeatedly apply a "shift" operation to the blocks in the A-by-A square until the difference between the area of the minimal-area rectangle surrounding them and A<sup>2</sup> is equal to B, the number of remainder blocks. We can then fill the empty space in the surrounding rectangle with the remainder blocks to get a solid rectangle containing all and only the remainder blocks and the blocks from the original square.</p>

<h4>Pseudocode</h4>
<pre>
X = A (width of the surrounding rectangle)
Y = A (height of the surrounding rectangle)
while (X*Y - A<sup>2</sup>) != B
    <i>shift blocks and update X, Y, the dimensions of the surrounding rectangle</i>
end
output: X, Y (X*Y = A<sup>2</sup> + B)
</pre>

<h4>The Shift Operation</h4>

<p>Each time the shift operation is applied, the width of the minimal surrounding rectangle is increased by 1. The height may decrease or remain the same, but not increase.</p>

</p>
</center>
