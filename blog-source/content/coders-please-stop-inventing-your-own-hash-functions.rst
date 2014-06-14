Coders, PLEASE STOP inventing your own hash functions!
#######################################################
:slug: coders-please-stop-inventing-your-own-hash-functions
:author: Taylor Hornby
:date: 2012-08-21 00:00
:category: security
:tags: passwords, hashing

Today, I came across an `article on password cracking`_. In the comment section
I saw someone had posted (presumably right after they just read about salting)
the following reply:

.. _`article on password cracking`: "http://arstechnica.com/security/2012/08/passwords-under-assault/

.. code:: text

    My solution: don't store hashes as a single SHA1 or MD5 result ...
    combine it for further obfuscation. A hash is designed to fall into
    enemy hands, so don't make it easy to produce rainbow tables.
    
    SAMPLE - All other password weaknesses still apply!
    
    function generateHash($name, $pass)
    {
    $salt = '';
    for($i = 0; $i < strlen($name); $i++) { $salt = $i . $salt . $name . $i; }
    return (sha1($salt . $pass) . sha1($pass . $name . $salt));
    } 

This code is exactly as secure as ``sha1( $name . $pass );``. It does not make
it harder to produce rainbow tables, it is `security through obscurity`_.

.. _`security through obscurity`: https://defuse.ca/blog/what-is-security-through-obscurity.html

I come across an example like this almost ever week. Please stop inventing your
own hash functions and use a `standard salted and iterated key derivation
function`_.

.. _`standard salted and iterated key derivation function`: https://crackstation.net/hashing-security.htm
