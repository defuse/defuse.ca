<?php
    Upvote::render_arrows(
        "synergy_cracking",
        "defuse_pages",
        "Cracking Synergy's Bad Cryptography",
        "A tool to crack Synergy's homebrew cryptography.",
        "https://defuse.ca/cracking-synergy-bad-cryptography.htm"
    );
?>
<div class="pagedate">
July 20, 2013
</div>
<h1>Cracking Synergy's Bad Cryptography</h1>

<p>
<a href="http://synergy-foss.org/">Synergy</a> is a cross-platform mouse and
keyboard sharing tool. The Synergy developers recently added an encryption
feature. Instead of using a protocol like TLS, the developers invented their own
protocol, which, as one should expect, is abysmal. I've posted about their bad
cryptography before, on my 
<a href="http://www.cryptofails.com/2013/07/synergy-integer-overflow-key-reuse-iv.html">Crypto Fails</a> blog.
</p>

<p>
Today, I am releasing a tool for cracking packet captures of Synergy 1.4.12
traffic.  Hopefully this will motivate the developers to find a better
encryption solution, or to at least warn their users that the encryption is
severely broken.
</p>

<h2>The Vulnerability</h2>

<p>
Synergy uses a data-at-rest encryption library to encrypt network
communications. All encryption is just a block cipher in one of the following
modes: CTR, OFB, CFB, or GCM. There are no sequence numbers, MACs, or anything
else that you would expect a network encryption protocol to have.
</p>

<p>
The Synergy client and server both share a password. When the client connects to
the server, they both derive a key an initialization vector (IV) from that
password.  Communications in both directions are encrypted with the same key and
IV until the Server sends a keyboard event. When CTR, OFB, or GCM mode is in
use, the keystream enciphering data the server sends is the same as the
keystream enciphering data the client sends. The client's data is very
predictable (most of it is the string "CNOP" repeating), so we can recover the
keystream from the client's transmissions, then use it to decrypt the server's
transmissions.
</p>

<p>
Because of this oversight, everything sent by the server, up to the first
keyboard event (press, release, repeat), can be decrypted passively. This
includes mouse movements and the next initialization vector.
</p>

<h2>The Cracking Tool</h2>

<p>
The tool is implemented as a Ruby 2.0 script. It can be downloaded from <a
href="https://github.com/defuse/synergy-crack">GitHub</a>. The actual script is
<a href="https://github.com/defuse/synergy-crack/blob/master/crack.rb">here</a>.
</p>

<h3>Example Server Transmission</h3>

<p>
This is what the Synergy server's encrypted transmissions look like.
</p>

<pre>
0000000: 0000 000b 3fb0 2628 4341 3b67 e27d 8000  ....?.&amp;(CA;g.}..
0000010: 0000 04a1 fb68 2a00 0000 04ca aacd 6b00  .....h*.......k.
0000020: 0000 04f2 12fa 7a00 0000 10a9 d7e6 de47  ......z........G
0000030: ff97 e29a e58d 49ac 2f44 a000 0000 0e2c  ......I./D.....,
0000040: f925 334a efc2 00c9 f1c4 9b10 3f00 0000  .%3J........?...
0000050: 11d7 55e9 6df9 8b50 5d64 6cde d4d0 94ef  ..U.m..P]dl.....
0000060: cdf3 0000 0011 a8b2 213c 90c5 ef10 8ea4  ........!&lt;......
0000070: 1fce 430a d34b ec00 0000 0883 8377 0941  ..C..K.......w.A
0000080: 3ffe f000 0000 08f4 103d cfe4 cf02 0900  ?........=......
0000090: 0000 080e f3bf a791 685a df00 0000 086a  ........hZ.....j
00000a0: 2016 e728 e64d b100 0000 08ea 336a a509   ..(.M......3j..
00000b0: 8cbd c000 0000 08ec 0790 5fc4 7f8d 0100  .........._.....
00000c0: 0000 082d 8dd2 8920 83f5 ba00 0000 08ea  ...-... ........
00000d0: 5b72 5fa7 bddf 4200 0000 0862 d5c0 21cd  [r_...B....b..!.
00000e0: 3a93 1900 0000 085f ac35 839b 8340 4900  :......_.5...@I.
00000f0: 0000 0858 466b ea0a 0a7b 4d00 0000 08d3  ...XFk...{M.....
0000100: 692e 0416 7a3a 0100 0000 08c4 bb4c 4577  i...z:.......LEw
0000110: a947 8100 0000 0817 ce4f 5d52 0545 ca00  .G.......O]R.E..
0000120: 0000 08a8 9163 770e e7c6 fb00 0000 082a  .....cw........*
0000130: 6332 ede5 1dfd ea00 0000 08d9 5ca8 e81a  c2..........\...
0000140: 7bf4 ff00 0000 08fa 1b81 79d3 7778 d100  {.........y.wx..
0000150: 0000 0868 02b8 9537 c4d4 9a00 0000 08bc  ...h...7........
...
</pre>

<h3>Cracked Server Transmission</h3>

<p>
This is what the tool prints when run on the above transmission. Note the 'DMMV'
mouse movement commands are now visible.
</p>

<pre>
"Synergy\x00\x01\x00\x04"
"QINF"
"CIAK"
"CROP"
"DSOP\x00\x00\x00\x02VSU\x92\x00\x00\x01\xF7"
"B\xB0NN\x00\x00\x01\xFC\x00\x00\x00\x01 \x00"
"DCLP\x00\x00\x00\x00\x00\x00\x00\x00\x04\x00\x00\x00\x00"
"DCLP\x01\x00\x00\x00\x00\x00\x00\x00\x04\x00\x00\x00\x00"
"DMMV\x00\x17\x01\xF7"
"DMMV\x000\x01\xF2"
"DMMV\x00k\x01\xE5"
"DMMV\x00\x8A\x01\xDC"
"DMMV\x00\xCB\x01\xCD"
"DMMV\x00\xEE\x01\xC4"
"DMMV\x01\x11\x01\xBD"
"DMMV\x014\x01\xB6"
"DMMV\x01Q\x01\xAF"
"DMMV\x01l\x01\xAA"
"DMMV\x01\x7F\x01\xA5"
"DMMV\x01\x88\x01\xA3"
"DMMV\x01\x89\x01\xA3"
"DMMV\x01\x8A\x01\xA3"
"DMMV\x01\x8D\x01\xA3"
"DMMV\x01\x95\x01\xA4"
"DMMV\x01\xA9\x01\xA8"
"DMMV\x01\xC6\x01\xAD"
"DMMV\x01\xE3\x01\xB2"
"DMMV\x02\x00\x01\xB7"
"DMMV\x02\x1D\x01\xBC"
"DMMV\x02:\x01\xBE"
...
</pre>

<h2>What the Synergy Developers Should Do</h2>

<p>
Synergy's developers should immediately take the following actions:
</p>

<ul>
    <li>
        Display a prominent warning on the user interface and project web pages. I suggest the following text: <b>&quot;Synergy's encryption is severely broken. It does not provide protection against anything other than casual (accidental) network traffic observation.&quot;</b>
    </li>
    <li>
        Open a ticket for implementing TLS-SRP or TLS-PSK encryption.
    </li>
</ul>

<p>
It is possible to break this script by making a simple change, for example, by
not reusing the IV. <b>This is not a solution</b>. This script only exploits one
of the many holes in Synergy's encryption. When active attacks are considered,
much more is possible, including decrypting more of the server's transmissions
and injecting commands. It is not possible to iteratively improve the current
protocol into something secure. It must be re-engineered.
</p>

<h2>What the Synergy Developers Have Done</h2>

<p>
This bug is being tracked in
<a href="http://synergy-foss.org/spit/issues/details/3760/">Bug #3760</a>. The
status of the ticket is "Fixed", although all that was done is disable all modes
except CFB mode. This breaks this specific attack, but is probably still
vulnerable to other active attacks.
</p>
