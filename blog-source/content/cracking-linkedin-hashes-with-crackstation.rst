Cracking LinkedIn's Hashes with Crackstation
######################################################
:slug: cracking-linkedin-hashes-with-crackstation
:author: Taylor Hornby
:date: 2012-06-08 00:00
:category: security
:tags: passwords, hashing, linkedin 

`LinkedIn`_'s user database has been `breached`_. The passwords were hashed with
SHA1, but salt was not used. 6 million of them have been published to the
internet. You can download them via `torrent`_ or via HTTP from `defuse.ca's
mirror`_. 3.5 million of them have had their first 5 hex digits replaced with
zero -- some suspect this was used as a way to mark which have been cracked by
another party `(source)`_.

.. _`LinkedIn`: http://linkedin.com/
.. _`breached`: https://news.ycombinator.com/item?id=4073309
.. _`torrent`: http://thepiratebay.se/torrent/7334648/LinkedIn_Password_Hashes
.. _`defuse.ca's mirror`: http://defuse.ca/files/linkedin-sha1.txt
.. _`(source)`: https://news.ycombinator.com/item?id=4073928

A bit of googling reveals that LinkedIn has about 120 million registered
accounts, so the 6,143,150 hashes represent about 5% of LinkedIn users
Correction: Since duplicates were removed from the hash list, it must represent
a much larger portion of LinkedIn users. Since passwords are so often re-used,
I wouldn't be surprised if it was the entire database. 3,521,180 have been
cracked by an unknown third party. That leaves 2,621,970 for me to try to crack
with `CrackStation`_.

.. _`CrackStation`: https://crackstation.net/

What is CrackStation?
======================

CrackStation is basically a giant lookup table -- a sorted list of hashes,
mapped to their corresponding plaintext. To crack a hash, all I have to do is
run a binary search through the hash index file to get the location of the
plaintext in the wordlist file. This process is very efficient; a 200GB index
file (15 billion hashes) can be searched in only a fraction of a second, even on
a standard 7200rpm hard disk. 

CrackStation Results
=====================

It took 31.8 hours to process all 2,621,970 not-yet-cracked hashes. That's an
average of 22 hashes processed per second. In the end, I was able to crack
31,831 hashes -- about 1.2%. CrackStation's "medium" dictionary, which has
1,493,677,782 entries, cracked 23,529 (0.8%) of them. The "huge" dictionary,
which has 15,171,326,912 cracked 10034 (0.3%) of them. These figures are not
exclusive -- 1732 hashes were cracked by both dictionaries (I did not optimize).

Duplicates were removed from the original list of hashes, so I cannot provide
"most common passwords" statistics. Here's a random sample of what I cracked (I
will not be releasing the full list):

.. code:: text

    6764dcf2d9c84b9d66fd5c8a78691affde1045e1 :: sangeetalahiri
    d6b3ebab49bd83a60de4f5b84d9fe61eff5cba73 :: DualPhone
    c79b146f16d3442bb2adb00cf9a6e456aacdb877 :: juan.rondon
    777bb3743c7424b477ae95f0b002cffc3a6d8f57 :: Charlcombe1
    a567e183044b8a881a8aec41387614f7963b60be :: hydroworx1
    7f97cd544c2937e494265a6a5a4ae3edf6a391e8 :: Jetravaille
    87f11d9ccda89e463cf92e3f84dbf1f5b9b5f84a :: Nangiya1
    1168caf06e26fb82642b069dc306b9a849e9448f :: kab@las
    3722fd3f3cded4dc1231df6b16190364eaf71184 :: grasmachien
    5a61c766bb6ea7294a6a0e6e2ccbda8c4eed0ebc :: Eidner4


These results aren't really meaningful, since I only tried to crack the set of
hashes that were not already cracked by someone else, but it was a fun exercise
to test the strength of CrackStation's password dictionaries. It is also a great
demonstration of why salt is **crucial** to password security. If we assume that
all of the previously-cracked hashes could have been cracked by CrackStation,
then I would have been able to crack 3,553,011 (57.8%) in only 78 hours, or 19
hours if cracked them in parallel on a quad-disk quad-core system. That figure
is consistent with other bulk cracking jobs CrackStation has done in the past. 

The reason I cannot crack the ones that had their first 5 hex digits set to
00000 is because CrackStation's indexes are sorted on the first 48 bits of the
hash, with the first bits being the most significant. Without all of those bits,
I can't do a binary search through the index file. I could rebuild the index
using the last 48 bits of the hash, but that would take days and hundreds of
gigabytes of hard drive space. I don't want LinkedIn users' passwords that bad.
If there is significant public interest in the results, then I will consider
doing it. 

Conclusion
===========

This kind of attack wouldn't work if the hashes were salted. If they were
salted, I'd have to run a dictionary attack on each hash individually, which can
take upwards of an hour per hash for a similar-quality attack. So if you run
a website that stores user passwords, please make sure they are `hashed with
salt`_. If they aren't, (LinkedIn, are you listening?), then you are an
irresponsible webmaster. 

.. _`hashed with salt`: https://crackstation.net/hashing-security.htm
