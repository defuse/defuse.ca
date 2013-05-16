<?php
    Upvote::render_arrows(
        "skypemitm",
        "defuse_pages",
        "Confirmed: Microsoft Visits Links You Send In Skype",
        "I sent a link through Skype and Microsoft visted it, confirming The H's claims.",
        "https://defuse.ca/microsoft-reads-your-skype-messages.htm"
    );
?>
<h1>Confirmed: Microsoft Visits Links You Send In Skype</h1>

<p>
I have independently verified <a
href="http://www.h-online.com/security/news/item/Skype-with-care-Microsoft-is-reading-everything-you-write-1862870.html">
The H's claim that Microsoft can read everything you send in Skype.</a> Last
night, me and <a href="http://dicesoft.net/">a friend</a> (<a
href="https://twitter.com/redragonx">@RedragonX</a>) planned to have a fake
conversation over Skype, discussing a nonexistent Internet Explorer 0day exploit
(I figured we might as well trip some DHS keywords). Halfway through the
conversation, I shared a URL. 
</p>

<p>
Here's what we said:
</p>

<?php
    $str = <<<EOT
[9:36:33 PM] Winston Smith: Hey man
[9:37:00 PM] RedragonX: hey, hat is up
[9:37:07 PM] RedragonX: lol
[9:37:13 PM] Winston Smith: i found an IE8 0day want it?
[9:37:27 PM] RedragonX: hmm. ya right .....
[9:37:34 PM] Winston Smith: seriously look her: https://defuse.ca/zvpebfbsg.htm
[9:39:08 PM] RedragonX: u didnt bypass  aslr tho?
[9:39:25 PM] Winston Smith: that's only part of it, i have a rop exploit, ill email the whole thing to you 1sec
[9:39:34 PM] RedragonX: hmm  ok
[9:39:38 PM] RedragonX: ty
[9:40:17 PM] Winston Smith: np i gtg now but have a look at it and tell me what you think.. try running it on some of your bots to see how reliable it is plz
[9:40:22 PM] Winston Smith: ttyl
[9:40:46 PM] RedragonX: ttyl
EOT;
    printHlString($str, "text", true);
?>


<p>
This morning, I checked my logs and found this:
</p>

<?php
    $str = <<<'EOT'
65.52.100.214 - - [15/May/2013:23:03:54 -0600] "HEAD /zvpebfbsg.htm HTTP/1.1" 200 3930 "-" "-"
EOT;
    printHlString($str, "text", true);
?>

<p>
Someone ran a HEAD query on the URL 1 hour and 26 minutes after I sent it
through Skype. Running a reverse DNS on this IP reveals that it does indeed have
something to do with Microsoft:
</p>

<?php
    $str = <<<'EOT'
52.65.in-addr.arpa.	3600	IN	SOA	ns1.msft.net. msnhst.microsoft.com. 2013051301 1800 900 7200000 3600
EOT;
    printHlString($str, "text", true);
?>

<p>
This shows that Microsoft has the ability to read Skype messages, and the hour
of delay between the sending of the URL and Microsoft's request shows that they
are (at least) storing some messages for over an hour.
</p>

<p>
I am running Skype version 4.0.0.8 (Linux). My friend is running Skype version
4.1 (Linux). 
</p>

<p>
If you are looking for an alternative secure instant messaging service, I highly
recommend using <a href="http://www.pidgin.im/">Pidgin</a> with the <a
href="http://www.cypherpunks.ca/otr/">Off-the-Record Messaging Plugin</a>.
</p>
