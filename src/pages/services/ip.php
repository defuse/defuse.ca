<h1>Your IP Address</h1>

<div style="font-size: 30pt; text-align: center;">
HTTPS IP:
<?php
    echo htmlentities($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
?>
</div>

<iframe width="100%" height="60px" src="http://defuse.ca/ip-insecure.php"></iframe>

<p>
This page shows your IP from two perspectives: HTTPS and HTTP. The HTTPS IP is your IP when you connect through an SSL/TLS (secure) connection. The HTTP IP is your IP when you connect through a normal unencrypted connection. Normally the IP addresses will be the same. If they are different, it means there is a server/router between you and this website that is automatically and transparently caching your HTTP traffic.
</p>
