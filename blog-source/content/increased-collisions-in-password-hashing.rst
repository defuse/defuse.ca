Increasing Collisions in Password Hashing
##########################################
:slug: increasing-collisions-in-password-hashing
:author: Taylor Hornby
:date: 2012-12-11 00:00
:category: security
:tags: passwords, hashing

With this post I will try to show how, in a theoretical scenario, deliberately
weakening the collision resistance of a password hash function can increase
security. This is by no means a formal proof.

Suppose Alice has an account on two computer systems *X* and *Y*. She uses the
same password on both systems.

Suppose both systems implement the following password hashing scheme: Let *t* be
some positive integer. Given a password *P*, compute the entropy of the password
*H(P)*. Create and store a salted *B*-bit hash of *P* where *B = H(P) - lg(t)*.
To verify a password, wait for *t* units of time then check the hash in the
usual manner. Here, the entropy of the password is the logarithm base two of the
number of guesses it will take an adversary to guess the exact password. Also
suppose that it takes one unit of time to compute a hash.

I claim that using this scheme, Alice's accounts are no less secure than if we
used 256-bit hashes (if *H(P) < 256*), and that if System *X* gets compromised,
then her account on System *Y* is more secure than if we used 256-bit hashes.

To see why, note two things:

1. The probability of any random string *S* having the same hash as Alice's
   password *P* is *1/(2^B)*. So if Mallory tries to guess Alice's password
   using an online attack, it will take about *2^B* guesses, so about *2^B * t*
   time. So the effective security against an online attack is *B + lg(t)*. By
   the construction, *B + lg(t) = H(P)* so this is no easier than guessing *P*.

2. Suppose Mallory roots System *X* and gets the *B*-bit hash of Alice's
   password. It takes Mallory about *2^B* guesses to find a preimage. But since
   *B = H(P) - lg(t)*, Mallory will find about *t* collisions when she performs
   the same search she would if it was instead a 256-bit hash (this is not at
   all a formal argument, but hopefully you see the idea behind it).

By supposition, Mallory has rooted System *X*, so there is no point trying to
secure Alice's account on *X* anymore. All that remains is the security of
Alice's account on system *Y*. This breaks down if Mallory can get read-only
access to the hash database without rooting... but suppose the root password is
stored in plain text in the hash database ;) .

Because System *Y* is using different salts, it is unlikely that any of the *t*
collisions Mallory found, except *P* itself, can be used to authenticate as
Alice to System *Y* (reader: double check this). So Mallory's best option is to
try authenticating to System *Y* as Alice using the *t* collisions as passwords
guesses, since she knows *P* is one of those *t* collisions, and by construction
*lg(t) < H(P)*. This will take *t * t* time, for an effective security of *2
* lg(t)* bits.

If, instead, System *X* was using standard 256-bit hashes, Mallory would have
found the exact *P* and could get into System *Y* on the first try. Using this
scheme it takes about *t* tries. So by weakening the hash, Alice's account on
System *Y* is more secure than it would have been had the hash not been
weakened.

Of course, this system is impractical, since it assumes knowledge of how Mallory
would guess *P*, and thus the entropy of *P*. It also assumes that if Alice's
hash on System *X* gets compromised, then the security of her account on *X* no
longer matters at all, which is obviously not always the case. It is
nevertheless interesting to see how the altruism of the compromised system can
increase the security of Alice's account on the other system. 

Another problem is that the user must wait *t* units of time when logging in, so
in practice there would be tradeoff between security and log-in wait time.
Locking the accounts after significantly less than *t* attempts might be
a better approach.

It doesn't look like it can be made any better than this, since if you decrease
B any further, finding collisions with an online attack becomes easier than
guessing P, but it is still an online (thus detectable, stoppable) attack so it
might still be harder. I would guess that the wait time *t* would be better
spent computing iterations of PBKDF2 or similar. 
