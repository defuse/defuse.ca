<h1>Spoofing Email with Ruby</h1>

<p> Here's a Ruby script that spoofs email directly to the destination's SMTP
server. It's easy to do the same thing with telnet, this script just automates
the process. I added a few "neat" features like automatically looking up the MX
records and detecting whether the domain being spoofed has SPF records, so you
can see how to do those in Ruby, too.  </p>

<p style="text-align: center;">
    <strong><a href="/source/mailspoof.rb">Download</a></strong>
</p>
<?php
    printSourceFile("source/mailspoof.rb",true);
?>
