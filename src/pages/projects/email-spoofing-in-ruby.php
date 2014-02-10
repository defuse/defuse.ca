<?php
    Upvote::render_arrows(
        "email_spoofing_ruby",
        "defuse_pages",
        "Email Spoofing in Ruby",
        "A Ruby script for spoofing email to SMTP servers.",
        "https://defuse.ca/email-spoofing-in-ruby.htm"
    );
?>
<h1>Spoofing Email with Ruby</h1>

<p> Here's a Ruby script that spoofs email directly to the destination's SMTP
server. It's easy to do the same thing with telnet, this script just automates
the process. I added a few "neat" features like automatically looking up the MX
records and detecting whether the domain being spoofed has SPF records, so you
can see how to do those in Ruby, too.  </p>

<p style="text-align: center;">
    <strong>
        <a href="https://github.com/defuse/email-spoofing">Source Code on GitHub</a>
    </strong>
</p>
