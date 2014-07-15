<?php
    Upvote::render_arrows(
        "blindbirthdayattack",
        "defuse_pages",
        "Blind Birthday Attack",
        "A birthday attack without knowing what the collision actually is.",
        "https://defuse.ca/blind-birthday-attack.htm"
    );
?>
<div class="pagedate">
July 13, 2014
</div>
<h1>Blind Birthday Attack</h1>

<p>
Here's a crypto brain teaser I thought of while having a <a
href="https://twitter.com/solardiz/status/488142298400980992">twitter discussion
about double-HMAC and side channels</a>. The solution is given in the next
section, but you should try to solve it on your own first.
</p>

<p>
<strong>Blind Birthday Attack Problem:</strong>
<i>An attacker can query an oracle which takes two messages B1 and B2 as input,
computes HMAC-SHA256(K, B1) and HMAC-SHA256(K, B2), for some secret 256-bit key
K, and returns the <b>length of the common prefix</b> of the two HMACs (the
number of bits that are the same up until the first difference). Can the
attacker find a pair of distinct messages B1 and B2 such that HMAC-SHA256(K, B1)
is equal to HMAC-SHA256(K, B2) in significantly less than 2<sup>256</sup>
queries?
</i>
</p>

<p>
If it's not clear from the description: The oracle keeps the actual HMAC values
a secret, only telling the attacker the <em>length</em> of the common prefix
between the two. In other words, you're trying to find a collision without
knowing what the actual colliding values are. The answer should not involve
breaking HMAC-SHA256 or guessing K.
</p>

<p>
I posted the problem a few days before I posted the solution. Only one person,
<a href="https://twitter.com/sschinke">Sam Schinke</a>, solved it in that time.
</p>

<h2>Solution</h2>

<p>
The answer is yes, it is possible to find a collision in about 2<sup>128</sup>
queries (ignoring log factors). To see how, first realize that if we sent
2<sup>128</sup> queries with random messages, then because of the birthday
paradox, it's very likely that the HMACs of <em>some pair</em> of them would
collide. It's very unlikely that any of the pairs we sent (we have to send
a pair in each query) will collide, but maybe the B1 from our 23727th query
collided with the B1 from our 9272617197227th query. If we just sent
2<sup>128</sup> random queries, we would never find those cross-query
collisions.
</p>

<p>
So <em>creating</em> a collision is easy: Any set of 2<sup>128</sup> messages
will probably create an HMAC collision <em>somewhere</em>. It's <em>finding</em>
that collision that's hard.
</p>

<p>
We can find the collision by building a sort of "blind" binary search tree. This
tree will have the following properties:
</p>

<ol>
    <li>Each node can have 0, 1, or 2 children.</li>
    <li>Each child is either a "left child" or a "right child."</li>
    <li>
A node can only have one "left child" and one "right child." This means for
a node with only one child, that child is either a left child or a right child.
For a node that has two children, it has one left child and one right child.
    </li>
    <li>There is one node in the tree for each message (one-to-one mapping between nodes and messages).</li>
    <li>
    For a node in the <i>N</i>th level of the tree (the root is level 1): 
    <ul>
        <li>
            If it has a right child, the first <i>N</i> bits of the node's message's HMAC matches the first <i>N</i> bits of the right child's message's HMAC.
        </li>
        <li>
            If it has a left child, the first <i>N-1</i> bits of the node's message's HMAC matches the first <i>N-1</i> bits of the left child's message's HMAC, and the <i>N</i>th bit differs.
        </li>
    </ul>
</ol>

<p>
All we have to do is give the tree a root node, with a random message, and then
keep adding random messages to the tree until we find a collision. The process
for adding a message to the tree is simple. You should convince yourself that
the tree properties are preserved:
</p>

<p>
<strong>Adding a Message to the Tree:</strong>
</p>

<ol>
    <li>Set the CURRENT node to the root node.</li>
    <li>Set LEVEL = 1.</li>
    <li>Query the Oracle for (MESSAGE, CURRENT) and get the length of the common prefix LENGTH.</li>
    <li>If LENGTH == 256 (MESSAGE, CURRENT) is a collision. Stop.</li>
    <li>If LENGTH &gt;= LEVEL, then set CURRENT to CURRENT-&gt;RIGHT. Or, if CURRENT doesn't have a right child, set CURRENT-&gt;RIGHT = MESSAGE and stop.</li>
    <li>Otherwise, set CURRENT to CURRENT-&gt;LEFT. Or, if CURRENT doesn't have a left child, set CURRENT-&gt;LEFT = MESSAGE and stop.</li>
    <li>Set LEVEL = LEVEL + 1.</li>
    <li>Go back to Step 3.</li>
</ol>

<p>
That's it. Just keep repeating that with random messages until a collision is
found. It's guaranteed to find the first HMAC collision that occurs in the set
of messages you add, so overall you should expect to find a collision after
about 2<sup>128</sup> messages have been added. The tree's maximum depth is 256
(since a 257th level would imply a collision by property 5), so the number of
oracle queries should be around 2<sup>136</sup>, much less than the
2<sup>256</sup> queries that would be required if the oracle didn't return the
common prefix length.
</p>

<h3>Implementation</h3>

<p>
I wrote a Ruby implementation of this attack against HMAC truncated to 32 bits
(so that it finishes in a reasonable amount of time). Here's the output, and the
source code follows:
</p>


<?php
    $str = <<<EOT
Closest collision so far: 1
Tree size: 0
Closest collision so far: 2
Tree size: 1
Closest collision so far: 3
Tree size: 4
Closest collision so far: 7
Tree size: 5
Closest collision so far: 9
Tree size: 11
Closest collision so far: 10
Tree size: 52
Closest collision so far: 14
Tree size: 86
Closest collision so far: 16
Tree size: 200
Closest collision so far: 17
Tree size: 347
Closest collision so far: 18
Tree size: 407
Closest collision so far: 19
Tree size: 679
Closest collision so far: 20
Tree size: 2895
Closest collision so far: 22
Tree size: 3256
Closest collision so far: 23
Tree size: 6067
Closest collision so far: 25
Tree size: 6678
Closest collision so far: 29
Tree size: 12976
Closest collision so far: 30
Tree size: 13006
Closest collision so far: 32
Tree size: 33425
Found a collision amongst 33425 in 445046 queries!
Message 1: 8db04aea6b6b8d3a80d93d7064ec78a0bd24a8cba3a56d3c2f8755d8a9b63a40
Message 2: 99cec515fff6c583134b0942c0e6381ebdb10c07b472f58fd74e1cb0fbb684b8
EOT;
    printHlString($str, "text", false);
?>

<p>
Here's the source code:
</p>

<?php
    printSourceFile("source/blind-birthday.rb", false);
?>
