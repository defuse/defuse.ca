<h1>Feasibility of Using Key Stretching in Web Applications</h4>

<p>
    <b>IMPORTANT NOTE:</b> The data presented here is not of scientific quality. I created this page
in the preliminary stages of some research into whether or not key stretching is as beneficial
for web applications as it is for stand-alone apps running on the user's PC.
</p>

<p>
    The testing was done on Debian 6 + Apache, serving a simple PHP script that loads one (the same)
hash out of MySQL, computes the PBKDF2 password from $_GET, and compares the hash. Obviously this is
not a realistic scenario.
</p>

<p>
So please do not take these graphs too seriously!
</p>

<center>
    <img src="images/siege/p4box-availability.png" /><br />
    <img src="images/siege/p4box-concurrency.png" /><br />
    <img src="images/siege/p4box-cpu.png" /><br />
    <img src="images/siege/p4box-rate.png" /><br />
    <img src="images/siege/p4box-shortest.png" /><br />
    <img src="images/siege/p4box-longest.png" /><br />
    <img src="images/siege/p4box-failures.png" /><br />
    <img src="images/siege/p4box-response.png" /><br />
</center>
