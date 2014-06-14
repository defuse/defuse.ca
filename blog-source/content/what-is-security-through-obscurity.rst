What is Security Through Obscurity
###################################
:slug: what-is-security-through-obscurity
:author: Taylor Hornby
:date: 2012-06-04 00:00
:category: security
:tags: obscurity

Today a member of `GRC's Newsgroups`_ posed a question about security through
obscurity: 

.. _`GRC's Newsgroups`: https://www.grc.com/discussions.htm

    It seems to me that ALL digital security is EXACTLY "security through
    obscurity". Take, for example, AES... It isn't that the possible range of
    keys is unknown, it is just a cleverly hidden needle in an enormously HUGE
    haystack.  It could actually be possible to arrive at the correct key on the
    first guess.  However, I wouldn't bet anything you care about on that, since
    it would be about as likely as me saying, "I'm thinking of a particular atom
    somewhere in the universe. Can you guess which one?" Nevertheless, in set
    notation, it would be trivial to describe the set which includes the correct
    key. It is simply obscured by all the wrong keys.

    So, by your definition, what is *real* security?

It's a very interesting question, and it made me realize that in all of the
research I've done on cryptography, I've never seen a formal definition of
security through obscurity. It took me a while to come up with a reasonable
response (something other than "I know it when I see it."). My first thought was
anything that doesn't follow `Kerckhoffs's principle` is security through
obscurity, but I wanted something more general. After a bit more thinking,
I decided that measurability is the main distinction between something that
isn't security through obscurity, and something that is. Here's what I wrote: 

.. _`Kerckhoffs's principle`: http://en.wikipedia.org/wiki/Kerckhoffs%27s_principle

    Informally, I would define security, with respect to cryptography, as
    something that requires a well-defined and measurable amount of resources to
    break in a specific scenario.

    For example, if a message is encrypted in AES-256 in CBC mode, with a random
    key, and the adversary knows the plaintext is english, but doesn't know
    anything about the key, then the security factor is 2^256 i.e. the adversary
    has to do on average 2^255 operations to successfully decrypt the
    ciphertext.

    Note that this means that even if the key is just one bit, it is still
    "real" security, as long as the adversary knows that the key is either "1"
    or "0". It is very weak security, but the difficulty of cracking it is still
    well-defined and computable.

    Security though obscurity is anything that doesn't have a well-defined and
    computable "security factor" in at least one scenario.

    For example, if you are trying to smuggle pirated software across the U.S.
    border, you may choose to encode the data into the least significant bits of
    the RGB values in an image file. There is software for doing (and un-doing)
    this, so it would not be hard for the border patrol officer to extract the
    data, if he knew it was there. Your security rests on whether or not the
    officer will know to look in the least significant bits for hidden data.
    This is not well-defined and cannot be computed into a "security factor"
    like above.

    A real-world example is if you just won the lottery and you are looking for
    a place to hide your money (supposing banks don't exist):

    You could choose to buy a safe, rated to withstand 60 minutes of attack with
    blow torches. This is "real" security, the amount of work an adversary has
    to do to break into the safe is well-defined and computable.

    You could also choose to hide your money inside the door of your car
    (between the interior panel and the exterior metal). It would not be at all
    difficult for an adversary to steal your money, if they knew it was there.
    The security provided by "Will the adversary ever think that I am hiding the
    money in the door of my car?" is not well-defined nor computable, so this is
    security through obscurity.

    This is just my opinion on the matter. Cryptography texts and/or experienced
    cryptographers may disagree.

This definition can be applied to the idea of trying to keep an encryption
algorithm secret. If the security of the algorithm depends on it's secrecy, then
it is security through obscurity, because the amount of work an adversary (who
can run chosen plaintext and chosen ciphertext attacks) has to do to reverse
engineer the algorithm is not well-defined and cannot be computed. 
