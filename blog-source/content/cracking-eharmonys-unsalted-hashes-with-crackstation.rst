Cracking eHarmony's Unsalted Hashes with CrackStation
######################################################
:slug: cracking-eharmonys-unsalted-hashes-with-crackstation
:author: Taylor Hornby
:date: 2012-06-09 00:00
:category: security
:tags: passwords, hashing, eharmony 


This week, LinkedIn, eHarmony, and last.fm have all confirmed that their
password hash databases have been breached. In all three cases, passwords were
stored as an unsalted hash. I've already reported the `results of cracking
LinkedIn's password hashes with CrackStation`_, now it's eHarmony's turn.

.. _`results of cracking LinkedIn's password hashes with CrackStation`: https://defuse.ca/blog/2012/06/cracking-linkedin-hashes-with-crackstation/

eHarmony stored their users' passwords as an unsalted md5 hash. The leaked file
contains 1,516,877 hashes. You can download the original file from one of either
of the following links:

- `eHarmony Hashes HTTP Mirror`_.
- `eHarmony Hashes TORRENT Mirror`_.

.. _`eHarmony Hashes HTTP Mirror`: https://defuse.ca/files/eharmony-hashes.txt
.. _`eHarmony Hashes TORRENT Mirror`: http://thepiratebay.se/torrent/7341755/eHarmony_Unsalted_MD5_Hash_Database

There are no repeated hashes in the file, so presumably the party that leaked
them removed the duplicates. Because there are no duplicates, I can't provide
any "most common passwords" statistics, but I can report how many
`CrackStation`_ was able to crack, and how fast it did so.

.. _`CrackStation`: https://crackstation.net/

The cracking process took 23.47 hours and recovered 275,860 (18.2%) of the
passwords. CrackStation processed the hashes at an average rate of 17.94 hashes
per second. All passwords are upper case, so I suspect that eHarmony was
converting passwords to upper case before hashing them to implement case
insensitivity (another big no-no).

CrackStation uses two dictionaries. One, which I call the "medium" dictionary,
has 1,493,677,782 entries. The other, which I call the "huge" dictionary, has
15,171,326,912 entries. 250,122 of the cracked passwords are present in the
"medium" dictionary, 97,419 are in the "huge" dictionary, and 71,380 passwords
are in both.

If eHarmony had `used salt in their hashes`_ like they should have been,
I wouldn't have been able to run this attack. In fact, salting would have forced
me to run a dictionary attack on each hash by itself, and that would have taken
me over 31 years.

.. _`used salt in their hashes`: https://crackstation.net/hashing-security.htm

I'm very glad the LinkedIn, eHarmony, and last.fm breaches all went public at
nearly the same time. The stories are have been covered by nearly every tech
news website, and the issue of inadequate server-side password protection is
finally being addressed. I hope other companies will learn from these breaches
and start using salted hashes if they aren't already doing so.

Unsalted hashes are bad, but storing passwords in plain-text is even worse.
Visit my `Password Policy Hall of Shame`_ for a list of websites that are
probably storing your password in the clear.

.. _`Password Policy Hall of Shame`: https://defuse.ca/password-policy-hall-of-shame.htm

You probably came here wanting to see passwords, so I won't disappoint. Here's
a random sample:

.. code:: text

    39007e310acd5cc5582c34c408ebf4cc :: RAWANN
    c69f24b431852eb5b2db419860387dad :: SHELDURAY88
    713f6fcbeeacbd3a78401382f88f5d1f :: KJ1017
    86fc184cabcf0626c479927cc4e5e998 :: PUSSY310
    c075be959b6831fb01d52591963d12b7 :: KARAAGE4
    6c058fea843d5bff058c939fde3a6eb8 :: TH3EMPRESS
    2dfecece2bf00698dd5fbae90e2f0860 :: ELLEGRA
    e720578393bb68956f079c3db426d6ec :: 81PIRTER73
    b0963f1a7fd94698a6e76e402daffcef :: KORRESHI
    026d29ad37c6c672315e5a9c358d0a7c :: CLC47
    7d01d8893e7ddbb18ceb40c59ef384c2 :: ROMELP
    bf1a688689f82ba39b9efa236d09c539 :: OFTROY
    dfcd9ad16c5358c1c420343a7b2b683b :: MVN2006
    14f6609d95e3830b14709bce165041af :: MIKYONE
    2b1841891df6de05b163d8dbbe79036c :: JAMFILE
    9fe44d8eb7fbc5217fad2e54d09871ed :: TRIATA
    961887ee6a084c02619f22f6b2e8a852 :: IAMTHATIS
    fd9d9fa433c0d9385345355c17be13f4 :: PEACHKA1
    fa22376fe0782fe9313385846e66b979 :: JAHAAD
    095614e5971ad8a81d4f232c00bbf33d :: SARAHMIA1
    f14c92dd1b2281cdf0f5999e595442de :: LEBIN
    6e0f9ff4606b41705ea25bcbff1ac94b :: WINGK2
    49cacc76a288058b68ded69f0c804269 :: CHARLIEXX
    cbc7f37615111efe8e246d3316e24408 :: MB1229
    81fc99cc34d28eec3c90109cab7ddfbe :: EMETI
    e838f7db8ef56faa9b49e8215dfbbee7 :: KITABLAR
    02ae65a2f3dac98a54290f09c39758ed :: JENGARY
    3a7edcb86afee6b0853fba0a16672bcd :: GODSPELL2
    22d541659b917e40146f4d4256b2a2e2 :: LETSTALK2
    4e953e9a0a0f2503e8b8269b2c4a8057 :: 1MEMO1
