<?php
    Upvote::render_arrows(
        "advicetocs",
        "defuse_pages",
        "Advice to Aspiring Computer Security Engineers/Scientists",
        "Some advice for new computer science students based on my experience.",
        "https://defuse.ca/advice-to-aspiring-computer-engineers.htm"
    );
?>
<div class="pagedate">
February 16, 2015
</div>
<h1>Advice to Aspiring Computer Security Engineers/Scientists</h1>

<p>
I'm graduating this year, so I'm going to take a bit of time out of my life to
write some advice to the people following behind me. This advice is written for
someone who is still in high school and is interested in computers and computer
security. If you are older, just interested in computers as a hobby, or not
interested in computers at all, I think you might still get something out of it.
</p>

<p>
First, a warning: Don't follow this advice to the letter. This is all based on
my own experience, so it definitely won't apply to everyone. I'm not a famous
computer scientist or anything like that, so there's no real reason to listen to
my advice over theirs. I guess that's the first bit of advice: Don't strictly
follow anyone's advice. Tailor your experience to your interests.
</p>


<p>
With that said, here are the main points, in no particular order.
</p>

<ul>

<li>
<h3>Don't focus solely on computers or your &quot;career&quot;.</h3>

<p>
Computers are amazing, but there is so much more out there. Look at this picture
and click on it to open the full-resolution file:
</p>

<center>
    <a target="_blank" href="/images/hubble-extreme-deep.png">
    <img src="/images/hubble-extreme-deep-thumb.png">
    </a>
</center>

<p>
This is the so-called <a
href="http://www.nasa.gov/mission_pages/hubble/science/xdf.html">Hubble Extreme
Deep Field</a>. It was created by exposing the Hubble Telescope to less than
0.04 degrees of the sky (almost 10,000 of these images would fit into a complete
360&deg;). <b>Every point of light in that picture is an entire galaxy, some as
they looked 13.2 billion years ago, each one made of millions or billions of
stars</b>.
</p>

<p>
The immensity of the cosmos dwarf everything that happens on Earth. Of course
earthly things are more relevant to you and your life, but never forget that the
cosmos is there. <em>When you are in that boring business meeting or stressing
over some homework, all of that is still there waiting, begging, demanding to be
explored.</em>
</p>
</li>

<li>
<h3>Learn how to learn.</h3>

<p>
My greatest skill by far is being able to teach myself anything I want to learn.
Almost everything is simple once you understand it. It took rare geniuses to
develop our mathematics, physics, biology, and other sciences, but billions of
average human minds like yours and mine have been able to understand the ideas
after discovery.
</p>

<p>
The best way to get this skill is to learn a lot of different things in lots of
different fields. There is a lot of &quot;idea overlap&quot; between fields, so
if I'm trying to learn something from Field A, it's often useful to use
something I know well from Field B as a crutch to understanding the thing from
Field A.
</p>

<p>
When you don't understand something, try to figure out <em>why you don't
understand it</em>. If you can turn that fuzzy &quot;I'm confused.&quot; feeling
into something you can Google search for, you can learn a lot quicker. Practice
this. Eventually you'll be able to phrase your confusion as "It's either A, B,
C, D, or E." and now all you have to do is figure out which option is the right
one.
</p>

</li>


<li>
<h3>See formal education as debt vs. interest.</h3>

<p>
Formal education (university) is not personalized and runs at a fixed pace. You
will need to be able to keep up while still leaving time to do fun stuff on the
side (and fun stuff on the side is <em>crucial</em>; university doesn't teach
you everything you need to know). Think of it like financial debt and interest:
The more ahead you are, the more ahead you get. Conversely, the more behind you
are, the more behind you get. Start out well ahead and you will be way ahead at
the end. Start out too far behind, and you will struggle.
</p>

<p>
In practical terms: When you enter a computer science degree, you should already
know how to program and should be familiar with Object-Oriented Programming.
</p>

</li>


<li>
<h3>Build your own computer.</h3>

<p>
Instead of buying a Dell or HP, go through the process of picking out components
(CPU, motherboard, RAM, power supply, hard drives, disc drive, etc.), buying
them individually, and then putting them all together.
</p>

<p>
If you've never done this before, it sounds hard and you'll be worried about
breaking a lot of expensive hardware. It's actually not difficult at all. Except
for installing the CPU fan, where you need to squeeze out a bit of thermal
paste, it's literally just taking the parts out of their boxes, plugging them
in, and then turning on the power.
</p>

<p>
If you do this, you will probably end up with a better system for the cost, and
you will have a better understanding of what that thing sitting on your desk
actually is. If you are interested in overclocking, that's good too, because to
get the best performance you will have to learn some things about hardware!
</p>

</li>

<li>
<h3>Run your own Web, DNS, and email servers.</h3>

<p>
Buy a domain name and start a website. Don't just use shared (or
&quot;managed&quot;) hosting. Make it a learning opportunity. Rent a cheap Linux
VPS and set up your own website (Apache, NGINX, etc.), host your own DNS (BIND),
and run your own email server (Dovecot and Postfix).
</p>

<p>
I will not lie: doing this for the first time (and even the second and third
time) is a huge pain in the ass. Documentation for the server software is
unreadable and it will take you hours to get the configuration right.
Thankfully, once you get all of the configuration files correct, it will usually
keep working, so it's not too much of a time sink.
</p>

<p>
Pay attention to security as you're doing this. Make sure your mail server isn't
an open relay and make sure your DNS isn't an open resolver and doesn't answer
AXFR queries, among other things.
</p>

<p>
Perservering through the frustration is worth it because you will come out of it
with a good understanding of Internet protocols (DNS, HTTP, SMTP, IMAP, etc.).
This is a lot better than what you will get from your university's networking
course, since you will know the real thing, and not the watered-down abstraction
they (usually) feed you.
</p>

</li>

<li>
<h3>Read science papers that interest you.</h3>

<p>
Academic papers look terrifying at first. Some use words you've never heard and
symbols you've never seen. Once you get used to the style, they are actually
very readable. If math isn't your thing, there are lots of papers that don't use
math at all. If you have an interest in a topic, try searching <a
href="http://scholar.google.com/">Google Scholar</a>. There is a lot of great
knowledge in academia that isn't available anywhere else, so take the time to
get to know it.
</p>
</li>

<li>
<h3>Watch security conference talks and learn the culture.</h3>
<p>
The computer security community shares knowledge through talks at conferences.
Usually, recordings of the talks are posted on YouTube immediately after the
event. The top conferences are Defcon, Blackhat, and Chaos Communication
Congress (usually stylized as 31C3 where "31" means the 31st run of the
conference). Watch as many talks as you reasonably can!
</p>

<p>
The security community has a certain &quot;culture&quot;, and it helps to know
the culture if you want to fit in well. Watching conference talks is a great way
to get exposed to it.
</p>
</li>


<li>
<h3>Listen to a podcast for security news and education.</h3>
<p>
My favorite security podcast is <a
href="https://www.grc.com/securitynow.htm">Security Now</a>, hosted by Steve
Gibson and Leo Laporte. It's actually what gave me my start and taught me a lot
of what I know about security. Check out the older episodes; there's lots of
good stuff there. But keep your critical thinking skills sharp! Even the best
podcast hosts make mistakes and explain things wrongly sometimes.
</p>
</li>

<li>
<h3>Read a lot (of books).</h3>

<p>
Get comfortable with reading. Most useful, actionable, information is in written
form. If you can make yourself enjoy reading, your access to this information
will be limited only by how much time you have.
</p>

<p>
This is easier said than done. All can do is report my own experience. I used to
hate reading and would fall asleep after a few pages. Then I read G&ouml;del
Escher Bach by Douglas Hofstadter and the problem went away. I think it is just
a matter of forcing yourself to pay attention for a while, and then it becomes
natural.
</p>

<p>
I specifically mentioned books in the title of this section. That's because it's
tempting to only read web pages, blog posts, and Wikipedia articles. Those are
good resources, but reading a whole book about a topic lets you see the
information all put together in one place, giving you a better, deeper,
understanding. Books are not dead.
</p>

<p>
If you are interested in low-level operating systems or exploit development
stuff, read the <a
href="http://www.intel.com/content/www/us/en/processors/architectures-software-developer-manuals.html">Intel
Software Developer Manuals</a>. Reading those will give you a <em>much</em>
better understanding of what's really going on than any high-level operating
systems textbook will.
</p>
</li>

<li>
<h3>Typeset your assignments with LaTeX.</h3>
<p>
Typeset your written and math assignments in LaTeX. This serves two purposes.
First, your assignments will be beautiful, so you can take pride in them and you
will probably get better grades as a result (the time it takes to typeset forces
you to finish them early, too, helping you avoid procrastination). Second, LaTeX
is a super useful skill if you want to be a scientist, since pretty much every
scientific paper is written in LaTeX.
</p>
</li>

<li>
<h3>Use Vim or Emacs, not an IDE.</h3>

<p>
Learn to use a text editor like Vim or Emacs. These are special editors that let
you edit text a lot faster than you can in a regular notepad-style editor. It
takes some time to learn how to use them, but once you do, writing on a computer
will be a more enjoyable experience. Both Vim and Emacs are programmable, so you
can customize them to do lots of cool things.
</p>

<p>
Integrated Development Environments (IDEs) make programming easier by hiding the
details of the process that turns your source code into a program. If you only
want to develop software, then maybe you don't need to understand that process.
As an engineer, especially a security engineer, it is important to understand
how your tools work.
</p>
</li>

<li>
<h3>Use Linux or Unix as your main operating system</h3>
<p>
Windows and Mac are <em>consumer</em> operating systems, meaning they hide as
many details of what's going on as possible. If you want to learn about
computers, using a consumer operating system is not a good idea. Make the switch
to Linux or Unix, or at the very least get comfortable with one of them in
a virtual machine. It's not easy to switch, but you can do it. Honestly, a lot
of the things computer experts do on a daily basis are actually <em>way</em>
easier on Linux. Don't give up!
</p>
</li>

<li>
<h3>Read &quot;How to Win Friends and Influence People.&quot;</h3>
</li>

<p>
This is basically an old self-help book about, well, winning friends and
influencing people. The big thing I got out of it was that being confrontational
rarely changes anyone's mind, as well as a bunch of other techniques for being
a better person. As far as I know, none of it is supported by scientific
evidence, but it completely changed the way I thought about interacting with
people. I highly recommend reading it.
</p>

</ul>

<p>
If you read this page and think it's useful, bookmark it or print it out.
I can't count the number of advice posts I've read and then completely forgotten
about. I hope this advice helps!
</p>
