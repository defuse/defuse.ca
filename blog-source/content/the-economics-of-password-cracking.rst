The Economics of Password Cracking
###################################
:slug: the-economics-of-password-cracking
:author: Taylor Hornby
:date: 2012-05-05 00:00
:category: security
:tags: passwords, hashing, cracking

We know that it's possible to come up with a password that no computer on earth
can crack. Use true random data to generate a sequence of 20 characters from the
full ASCII printable set, spend an hour memorizing, and you're good. We also
know that most people don't do that. What we don't know (or at least often
forget) is that people don't need to do that.

We estimate our password strength by the amount of time it would take the
fastest imaginable supercomputer to search through the set of all passwords of
equal or less length and find ours. What we often forget is that the bad guys
don't have those kinds of resources, and they rarely ever crack passwords via
brute-force search.

Why not? Because most bad guys won't build a billion-dollar supercomputer to
crack one password, and most people with billion-dollar supercomputers aren't
willing to rent out their processing power for password cracking. It isn't
profitable, and those supercomputers can be used for better things like
scientific research.

The Bitcoin network is currently computing 11.74 trillion sha256 hashes per
second (`see here for current stats`_). The network will compute ``3.702x10^20``
hashes in one year if it continues at it the same rate (my guess is that it will
grow, but it's already over 10 times as powerful as `the fastest known
supercomputer`).  That may seem like a lot of hashes, but if you do the math,
you'll see that its not even enough to do a brute-force search through the set
of all 11-character ASCII passwords. ``log(3.702x1020)/log(95) = 10.40``

.. _`see here for current stats`: http://bitcoinwatch.com/
.. _`the fastest known supercomputer`: http://i.top500.org/system/177232

Read it again: **The Bitcoin network is made up of millions of dollars worth of
hardware and is 10x faster than the fastest known supercomputer, but it can not
brute-force guess an 11-character password in a year.**

There is no business model that makes brute-force password cracking profitable.
To see why, consider the following argument:

1. If the password can't be cracked with good quality dictionaries or rainbow
   tables, it is long and complex.
2. Since the fastest distributed computing network on earth can barely search
   through the set of all 10-character ASCII passwords in a year, the
   probability of cracking a long and complex password via brute-force search in
   a reasonable amount of time is low.
3. Since the probability of successfully cracking a password via brute-force is
   low, to make any money, you must charge the customer even if you cannot crack
   it.
4. Since the probability of successfully cracking a password via brute-force is
   low, and the customer must pay even if you cannot crack it, the customer will
   not be willing to pay very much.
5. A decent brute-force attack requires a lot of time on an expensive
   supercomputer, so you must charge a lot.
6. (4) and (5) are incompatible with each other, so it is impossible to run
   a profitable brute-force cracking business.

Of course (4) may not hold true when the customer is a government and the
password is the key to winning a war, but most passwords don't have that much
value.

What this tells us is that we don't need to worry about brute-force attacks.
They always succeed (by definition), but rarely in a reasonable amount of time
and there is rarely ever a password valuable enough to justify one.

So ironically, the most profitable kind of password cracking requires very
little computational resources (a single 6-core system satisfies
`crackstation.net`_'s needs). The key to being profitable in the password
cracking industry is to be clever and do as much as you can without
a supercomputer. That means implementing extremely fast look-up tables and
constructing extremely effective dictionaries -- going after the low-hanging
fruit.

.. _`crackstation.net`: https://crackstation.net/

That's exactly what the bad guys do, and that's exactly what my hash cracking
service does. If you want your password to be secure, don't worry about
comparing its strength to a hypothetical supercomputer, just make sure it's not
a low-hanging fruit, and make brute-force search the only option for your
adversary. Then they probably won't even bother trying.

The easiest way to get out of the low-hanging fruit zone is to make your
password long. Take a look at `GRC's Password Haystacks`_ page for some good
advice.

.. _`GRC's Password Haystacks`: https://www.grc.com/haystack.htm

Here's a tip: My hash cracking service is backed by the largest known password
cracking dictionary, which has just over 15 billion entries. There are over
7 billion possible 5-character ASCII strings. So if you include 5 random ASCII
characters in your password and pad it out to at least 12 characters with
something memorable (not a word!), you can be pretty sure that your password
isn't a low-hanging fruit.
