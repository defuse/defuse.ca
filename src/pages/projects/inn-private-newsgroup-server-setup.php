<h1>How to Set Up a Private Newsgroup Server with INN2 on Debian</h1>

<p>
This guide explains how to set up the InterNetNews (INN) newsgroup server to
host &quot;local&quot; or &quot;private&quot; newsgroups. This means groups that
are hosted locally and aren't part of
<a href="https://en.wikipedia.org/wiki/Usenet">Usenet</a>. This is useful, for
example, if you want to set up a private on-line community, or private
discussion boards at a business.
</p>

<p>
This guide assumes no prior familiarity with the INN software or the NNTP
protocol, but basic familiarity with using and configuring GNU/Linux is assumed.
The guide is written for Debian Squeeze (6.0), but should be easy to adapt to
other GNU/Linux distributions.
</p>

<h2>Goals</h2>

<p>
This tutorial will result in a news server with the following features. The
group prefix &quot;l3vel&quot;, and group names &quot;l3vel.general&quot;,
&quot;l3vel.test&quot;, and &quot;l3vel.private&quot; are placeholders for the ones you
choose.
</p>

<ul>
    <li>
        Everyone on the Internet has read-only access to the groups
        l3vel.general, l3vel.test, but not l3vel.private.
    </li>
    <li>
        Posts to l3vel.test expire after 7 days.
    </li>
    <li>
        Everyone on the Internet can register a CECIL-ID (explained later) so
        they can post to l3vel.general and l3vel.test, as well as cancel
        messages they post. Users cannot cancel messages posted by other users.
    </li>
    <li>
        Users with Linux accounts on the server can read and post to 
        l3vel.general, l3vel.test, <em>and</em> l3vel.private.
    </li>
</ul>

<p>
In the above goals, there are three levels of authentication. Users who have
neither an account on the server nor a CECIL-ID are called <em>public
users</em>. Users who have a CECIL-ID, but not an account on the server are
called <em>cecil users</em>. Users who have an account on the server are called
<em>private users</em>.
</p>

<h2>Installing InterNetNews</h2>

<p>
Installing INN 2 on Debian Squeeze is easy. 
</p>

<div class="code">
apt-get update<br />
apt-get install inn2
</div>

<p>On other systems, you may have to compile it yourself. If you do, be sure to
include the perl hooks functionality.</p>

<h2>InterNetNews Overview</h2>

<p>INN is actually composed of multiple daemons. The ones we need to be
concerned about are:</p>

<ul>
<li>
    <h3>nnrpd</h3>
    <p>NNRPD is the daemon that communicates with clients. It does
    user authentication, accepts posts, distributes posts, etc. (<code>man
    nnrpd</code>).</p>

    <p>
    An NNRPD process is spawned when a client connects. It only reads the config
    files once, when it starts, so in order to force config changes to take
    effect for connected clients, you have to kill all of the nnrpd processes
    (<code>killall nnrpd</code>).
    </p>
</li>
<li>
    <h3>innd</h3>
    <p>INND is the main InterNetNews daemon. It handles all incoming
    connections, coordinates storage, retransmission, communicates with nnrpd,
    etc. (<code>man nnrpd</code>).
</li>
</ul>

<p>The following files and folders are important to INN.</p>

<ul>
<li>
    <h3>/etc/news/</h3>
    <p>The main configuration folder.</p>
    <ul>
    <li><strong>/etc/news/inn.conf</strong> &mdash; The main configuration file (<code>man inn.conf</code>).</li>
    <li><strong>/etc/news/readers.conf</strong> &mdash; User authentication configuration (<code>man readers.conf</code>).</li>
    <li>
        <strong>/etc/news/filter/</strong> &mdash; The perl scripts used to implement <a href="http://www.eyrie.org/~eagle/software/inn/docs/hook-perl.html">authentication and filtering hooks</a>.
    </li>
    </ul>
</li>
<li>
    <h3>/var/lib/news</h3>
    <p>Current newsgroup information.</p>
    <ul>
    <li><strong>/var/lib/news/newsgroups</strong> &mdash; Newsgroup descriptions (<code>man newsgroups)</code>.</li>
    <li><strong>/var/lib/news/active</strong> &mdash; List of newsgroups carried by the server (<code>man active</code>).</li>
    </ul>
</li>
<li>
    <h3>/usr/lib/news/bin/</h3>
    <p>INN configuration programs (this is not normally in PATH).</p>
    <ul>
    <li><strong>/usr/lib/news/bin/ctlinnd</strong> &mdash; A program for controlling and configuring INN (<code>man ctlinnd</code>).</li>
    </ul>
</li>
<li>
    <h3>/var/log/news/</h3>
    <p>INN logs. You can look in /var/log/syslog as well.</p>
</li>
</ul>

<h2>Creating Newsgroups</h2>

<p>Before we add newsgroups, we need to tell INN what our organization, domain
name, etc. is. Set the following options in inn.conf.</p>

<div class="code">
organization: <em>example-organization</em><br />
pathhost: <em>news.example.com</em><br />
domain: <em>example.com</em><br />
</div>

<p>Now we can add the newsgroups. This takes two steps. First, we use the ctlinnd
program to create the groups, then give them descriptions in
/var/lib/news/newsgroups.</p>

<div class="code">
# ctlinnd newgroup l3vel.general <br />
Ok <br />
# ctlinnd newgroup l3vel.test <br />
Ok <br />
# ctlinnd newgroup l3vel.private <br />
Ok
</div>

<p>The same could be accomplished by editing the /usr/lib/news/active file, but it
is safer to use the tool.</p>

<p>Now give them descriptions in /var/lib/news/newsgroups.</p>

<div class="code">
l3vel.general &nbsp; &nbsp;General discussion<br />
l3vel.test &nbsp; &nbsp; &nbsp; A newsgroup for testing<br />
l3vel.private &nbsp; &nbsp;Private discussion<br />
</div>

<p>There must be at least one <b>tab</b> between the group name and the
description, so make sure your editor isn't expanding tabs to spaces.</p>

<p>That's all there is to it! The newsgroups now exist are ready to be read and
posted to, but by default, only localhost has permission to do so, so next, we
set up user authentication.</p>

<h2>CECIL-ID</h2>


- cancel-enabling cryptographic identification lock

<h2>Linux Authentication &amp; Private Groups</h2>
