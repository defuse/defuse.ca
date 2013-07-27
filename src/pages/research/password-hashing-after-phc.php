<?php
    Upvote::render_arrows(
        "passhashingphc",
        "defuse_pages",
        "Password Hashing After the PHC",
        "Giving authenticators an advantage over password crackers with cache.",
        "https://defuse.ca/password-hashing-after-phc.htm"
    );
?>
<h1>Password Hashing After the PHC</h1>

<p>
The ultimate goal of password hashing function research is to get password
testers (authenticators) and password crackers to use the same implementation of
a password hashing system. When this is the case, it means that the crackers do
not have an incentive to build a different implementation, because it would not
be cheaper than building more of the system in use by the authenticators.
A password hashing system's implementation is "broken" (in the cryptography
sense) when the crackers have incentive to use a different implementation.
</p>

<p>
The goal of the <a href="http://password-hashing.net/">Password Hashing
Competition (PHC)</a> is therefore to find computationally- and memory-intensive
functions that have no "shortcuts," and to build a hardware implementation whose
performance is as close to the theoretical optimum as possible. This may (and
hopefully will) take the form of a system whose theoretical optimum performance
can be implemented on commodity processors, removing the need for authenticators
to buy new hardware.
</p>

<p>
There is a lot of room for improvement in this area. For example, given
a password hashing function <i>F</i> and an implementation <i>I</i> of <i>F</i>,
we would like to know if there is a faster implementation <i>I'</i> of
<i>F</i>. Proving that there is no faster implementation than <i>I</i> (or that
<i>I</i> is within some constant factor of the optimum) is very difficult, and
I am excited to see what new advances in this area come out of the PHC.
</p>

<p>
Let's suppose the PHC is finished and we now have a wonderful implementation of
a password hashing function, along with strong guarantees that the
implementation is very close to the theoretical optimum. We now have a black box
that can be purchased at some price, computes some amount of password hashes
per second, using some amount of power, and taking up some volume of space.
Authenticators are using them to test passwords and crackers are using them to
crack hashes stolen from the authenticators.
</p>

<p>
When this is the case, the cost to a password cracker is some multiple of the
cost to the authenticator. For example, if the authenticator needs to compute
100 hashes per second and the cracker wants to compute 100,000 hashes per
second, it costs 1000x more for the cracker to operate his cracking rig than it
does for the authenticator to operate their authentication server. Remember:
these are ideal conditions. In practice it probably won't turn out so well.
</p>

<p>
At this point, we can ask if there is anything more we can do to give the
authenticators an advantage over the crackers. The authenticator has one thing
that the cracker doesn't: the stream of incoming authentication requests. Can
the authenticator use this to gain a performance/cost advantage over the
cracker? The answer is yes: We give the authenticator a secure cache.
</p>

<p>
The cache exposes the following API and should make every effort to protect its
internal state.
</p>

<pre>
String cache_put(String key, String value)
    Insert the key-value pair (key, value) into the cache.
    Returns in constant time.

String cache_get(String key)
    Retrieve the cached value for key 'key'.
    If 'key' is not in the cache, return NULL.
    Returns in constant time.
</pre>

<p>
This could be implemented as a tamper-resistant hardware module or as a Linux
kernel driver that stores the cache in the kernel's address space. The main
requirement is that, if the system is compromised, the cracker should not be
able to retrieve the cache contents.
</p>

<p>
The authenticator would use the API to cache computations of the password hashing
function by, for example, using a single-iteration salted hash of the password
as the key. This would reduce the number of computations per second the
authenticator needs to perform, allowing them to choose a higher difficulty
setting for the hash function. Assuming a sufficiently large cache, the hash
function would only need to be computed when an account is created, password is
changed, or an incorrect password is tested. Common password mistakes would get
cached too.
</p>

<p>
If a cracker has access to the cache API, they can use it to test passwords
faster than by computing the password hashing function. Therefore, the cache should
artificially limit the rate at which it will respond to requests, and alert
a system administrator if the observed request rate crosses this threshold. In
an online attack, a cracker can determine that their password guess is wrong,
before the server responds, if the server is taking longer to respond than it does
for a correct password. This can be fixed by having the server respond in
constant time regardless of the outcome. It would still be cheaper for the
authenticator, since in the case of successful authentication, another thread
can be scheduled onto the CPU while the first one waits.
</p>

<p>
If we are able to secure a cache's state against an intruder, then it might be
better to include a secret key into the hash function, and protect that key the
same way we're protecting the cache.  Then, crackers could not test passwords
without extracting key. However, there is a reason to use a protected cache even
in the presence of a secret key. If an organization loses control of the
password hash keys, it can no longer test passwords, so keys have to be backed
up, and this exposes them to a greater risk. Cache, on the other hand, only
increases performance. If cache is lost, authentication will take longer than
normal until the cache has been rebuilt. For most applications, this is
acceptable, so the cache does not need to be backed up outside of the
secure hardware module or kernel address space (or however it is being
protected).
</p>

<p>
There's another way to give the authenticators an advantage. Make it very hard
for crackers to acquire and/or use as many of the black-box hash function units
as they want. Doing so is not easy, but it can be done in principal. For
example, by making it illegal to possess more than 100 of the hashing units, by
exploiting some as-of-yet undiscovered property of physics that could prevent
two units from operating within 100 meters of each other, or using the patent
system to limit production and distribution. Whether or not this kind of thing
is ethical is bound to be extremely controversial, but I think the idea is worth
exploring.
</p>

<p>
In conclusion, the PHC will probably give us a really good slow hashing
function. But it doesn't end there. There's a lot more we can do to give
authenticators an advantage over the password crackers, and we shouldn't leave
these options unexplored.
</p>
