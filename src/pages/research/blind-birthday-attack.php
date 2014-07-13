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
about double-HMAC and side channels</a>. I solved it, but don't feel like typing
up the answer today. I'll give the Internet the opportunity to solve it on their
own first. Here it is:
</p>

<p>
<strong>Blind Birthday Attack Problem:</strong>
<i>An attacker can query an oracle which takes two bit strings B1 and B2 as
input, computes HMAC-SHA256(K, B1) and HMAC-SHA256(K, B2), for some secret key
K, and returns the length of the common prefix of the two HMACs (the number of
bits that are the same up until the first difference). Can the attacker find
a pair of distinct inputs A and B that make the HMACs collide in significantly
less than 2<sup>256</sup> queries?
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
If you need a hint or clarification, <a href="https://twitter.com/defusesec">ask
me on Twitter</a>.
</p>
