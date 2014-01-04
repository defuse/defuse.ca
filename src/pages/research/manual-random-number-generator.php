<?php
    Upvote::render_arrows(
        "manualrng",
        "defuse_pages",
        "A Manual Random Number Generator",
        "Generating random numbers with paper coins.",
        "https://defuse.ca/manual-random-number-generator.htm"
    );
?>
<div class="pagedate">
January 4, 2014
</div>
<h1>A Manual Random Number Generator</h1>

<p>
Computers, as deterministic machines, cannot generate truly random numbers
themselves. They have to extract randomness from their inputs.  This means
things like the user's keystrokes, mouse movements, network packet times, hard
drive seek times, and so on.
</p>

<p>
If you're lucky, your computer might have a built-in true random number generator. Late-model Intel CPUs <a
href="https://en.wikipedia.org/wiki/RdRand">have one</a>, but most
older CPUs don't. Even if yours does, you have to wonder if it's
<a href="http://arstechnica.com/security/2013/12/we-cannot-trust-intel-and-vias-chip-based-crypto-freebsd-developers-say/">
backdoored by the NSA</a>.
</p>

<p>
Random numbers are extremely important for cryptography. They're used to
generate the secret keys that protect our data. Because they're so important, we
might wonder if there's an easy extreme-paranoia way to help the computer
generate them.
</p>

<p>
This page describes a process that anyone with a sheet of paper, a pen,
scissors, a drinking glass or other container, and a digital camera can do to
generate a good random number.
</p>

<p>
Humans usually generate random numbers by tossing coins or throwing dice.  To
get a random number suitable for cryptography, we'd have to toss 128 coins or
throw 50 six-sided dice. Obviously, doing it with real coins or dice is
cumbersome, noisy, and takes a long time. We need something better.
</p>

<p>
In this approach, we make our own "coins" out of paper, toss them all at once,
then photograph the result to generate a random number. The whole process
should take no more than 10 minutes.
</p>

<h2>Step 1: Prepare the "Paper Coins"</h2>

<p>
First, take a sheet of paper and scribble on one side as randomly as you can.
Try to make the scribbles "dense" so that when we cut the paper into tiny
peices, each peice has a mark on it. This should take about two minutes.
</p>

<center>
<img src="/images/manual-random-scribbles.jpg" alt="Scribbles on a sheet of paper." />
</center>

<p>
When you're done scribbling, fold the paper in half three times.
</p>

<center>
<img src="/images/manual-random-folded.jpg" alt="The same sheet of paper folded in half three times." />
</center>

<p>
Then cut both long edges off. This removes the "folds" so that you'll have
8 detached peices of paper.
</p>

<center>
<img src="/images/manual-random-cutting.jpg" alt="Cutting the edges off of the folded sheet." />
</center>

<p>
Next, hold all 8 peices at the same time and cut it into half-inch segments. Try
to make at least 16 cuts (more is better).
</p>

<center>
<img src="/images/manual-random-cut-twice.jpg" alt="A pile of paper segemnts." />
</center>

<h2>Step 2: Randomize</h2>

<p>
Put the segments (there should be at least 128 of them) into a glass or other
container (bigger is better). Make sure nobody is watching you (close your
blinds) or can hear you (turn on some music), then <b>shake randomly for at
least two minutes.</b>
</p>

<center>
<img src="/images/manual-random-in-glass.jpg" alt="Paper segments in a glass." />
</center>

<h2>Step 3: Photograph and Hash</h2>

<p>
Pour the paper segments out of the container onto a table. Spread them out so
each piece is visible, but try not to flip any over, and don't disturb their
orientation too much.
</p>

<center>
<img src="/images/manual-random-spread-out.jpg" alt="Randomized paper segments laid out on a table." />
</center>

<p>
Take a photograph, upload it to your computer, then hash the image file to get
the random number. On Linux, you can copy the file into /dev/random to add the
randomness to the system's random number generator.
</p>

<pre class="code">
$ sha256sum 100_0432.JPG 
<b>81d73c5d451dc97d033b197d526fd5d6a1a347af7eef3ffa838175fa823ee483</b>  100_0432.JPG
</pre>

<h2>Step 4: Destroy Evidence</h2>

<p>
<strong>DO NOT SKIP THIS STEP!</strong>
</p>

<p>
If your adversary finds the image file or learns what orientation the pieces
fell in, they'll be able to recover your random number. To prevent this, make
sure you shred the image file after you're done with it. To dispose of the paper
segments, put them back in the container and shake for a little while before
throwing them out.
</p>

<p>
If you want to be extra cautious, place each piece ink-side-up, then sort them
according to how much ink is on the piece. This destroys all information about
how they landed on the table. Doing that takes a really long time, so... shaking
should be fine.
</p>

<p>
If you used a digital camera with an SD card, make sure you shred the entire SD
card, or better, shred it then physically destroy it. If you used a scanner,
unplug the power cord for a few minutes to remove any trace of the image in
non-volatile memory (warning: some scanners <a href="http://www.cbsnews.com/news/digital-photocopiers-loaded-with-secrets/">save every image to a hard drive</a>, in
which case you're out of luck). Using a webcam attached directly to your PC is
a much better option.
</p>

<h2>Security Analysis and Conclusion</h2>

<p>
Okay, I wrote this as a joke, but I think it gives a good "real-world" analogy
to some of the difficulties computers have when generating random numbers for
cryptographic use.
</p>

<p>
It should actually work, though. There are poorly-random scribbles on at least
128 "coins" flipped and oriented in decently-random ways. I'd be very surprised
if the result didn't have at least 128 bits of entropy. 
</p>
