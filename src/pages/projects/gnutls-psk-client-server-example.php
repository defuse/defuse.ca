<?php
    Upvote::render_arrows(
        "gnutlspsk",
        "defuse_pages",
        "GnuTLS Pre-Shared Key Client-Server Example",
        "How to establish an SSL/TLS connection with GnuTLS and PSK authentication.",
        "https://defuse.ca/gnutls-psk-client-server-example.htm"
    );
?>
<h1>GnuTLS Pre-Shared Key Client-Server Example Code</h1>

<p>
It is very easy to set up a secure TLS connection with pre-shared key
authentication using GnuTLS. The following code is an example of how to do it.
The code is heavily documented, so it should be readable even to someone who has
never worked with GnuTLS before. The code is in the public domain, so feel free
to do absolutely anything you want with it.
</p>

<p>
Here is the <a href="http://www.gnutls.org/manual/gnutls.html">GnuTLS
Manual</a>, which is a great reference.
</p>

<h2>Makefile</h2>
<center><strong><a href="/source/Makefile">Download Makefile</a></strong></center>
<?php
    printSourceFile("source/Makefile", true);
?>

<h2>Server</h2>
<center><strong><a href="/source/server.c">Download server.c</a></strong></center>
<?php
    printSourceFile("source/server.c", true);
?>

<h2>Client</h2>
<center><strong><a href="/source/client.c">Download client.c</a></strong></center>
<?php
    printSourceFile("source/client.c", true);
?>
