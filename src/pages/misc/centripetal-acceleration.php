<?php
    Upvote::render_arrows(
        "centripetalaccel",
        "defuse_pages",
        "Centripetal Acceleration",
        "Deriving the centripetal acceleration equation.",
        "https://defuse.ca/centripetal-acceleration.htm"
    );
?>
<h1>Centripetal Acceleration</h1>

<p>
A mass <b>M</b> is connected by a string of length <b>r</b> to a point <b>C</b>.
The mass is orbiting <b>C</b> in a circle with a constant tangential velocity
<b>v</b>.  What is the radial acceleration <b>a</b> of the mass?
</p>

<center>
<img src="/images/phys_ca_d1.png">
</center>

<p>
The well-known equation for the acceleration is <b>a = v<sup>2</sup>/r</b>. It's
usually derived by taking the second derivative of the mass's position vector
with respect to time. 
</p>

<p>
I worked out a different way to do it, which involves only basic geometry and
the average value of <b>sin(x)</b> from <b>0</b> to <b>&pi;</b>.  We find the
centripetal force by the following process:
</p>

<ol>
    <li>
        Use a simple method to find the average value of some function <b>f</b>.
        For this problem, <b>f</b> is the force in the <b>y</b> direction.
    </li>
    <li>
        Write <b>f</b> in terms of some unknown <b>x</b>. For this problem,
        <b>x</b> is the radial force.
    </li>
    <li>
        Equate the two <em>averages</em> to solve for <b>x</b>.
    </li>
</ol>

<p>
I suspect this process will prove useful for solving other problems as well.
Let's see how we can use it to solve the centripetal acceleration problem.
</p>

<p>
Consider two snapshots of the rotating system at times <b>t<sub>0</sub></b> and
<b>t<sub>1</sub></b>.
</p>

<center>
<div style="width: 300px; display: inline-block;"><strong>t<sub>0</sub></strong></div>
<div style="width: 300px; display: inline-block;"><strong>t<sub>1</sub></strong></div>
<br />
<img src="/images/phys_ca_d2.png" width="300">
<img src="/images/phys_ca_d3.png" width="300">
</center>

<p>
At <b>t<sub>0</sub></b>, the mass's momentum is completely in the <b>y</b>
direction.  There is no <b>x</b> component to the momentum. At
<b>t<sub>1</sub></b>, half a revolution later, the momentum is in the exact
opposite direction. 
</p>

<p>
If the momentum at time <b>t<sub>0</sub></b> is <b>Mv</b> and the momentum at
time <b>t<sub>1</sub></b> is <b>M(-v)</b>, then the total change in momentum is
<nobr><b>-Mv - Mv = -2Mv</b></nobr>. We can get the <em>average</em> rate of
change of the momentum in the <b>y</b> direction by dividing by the time. Since
the force in the <b>y</b> direction is the rate of change of momentum in the
<b>y</b> direction, this is also equal to the <em>average force</em> in the
<b>y</b> direction.
</p>

<center>
<img src="/images/phys_ca_1.png">
</center>

<p>
Okay, that tells us the average force in the <b>y</b> direction, but
that's not the force we want. We want the radial force. 
</p>

<p>
But look! We know how to write the force in the <b>y</b> direction as a function
of the radial force. If the radial force is <b>F<sub>s</sub></b> and
<b>&theta;</b> is the angle the string makes with the <b>y</b> axis, then:
</p>

<center>
<img src="/images/phys_ca_2.png">
</center>

<p>
So the <em>average</em> of <b>F<sub>s</sub>sin(&theta;)</b> over the half-circle
of motion will be equal to the average of <b>F<sub>y</sub></b>, which we already
know as <b>F<sub>y<sub>avg</sub></sub></b>!
</p>

<center>
<img src="/images/phys_ca_3.png">
</center>

<p>
That's it! The radial force <b>F<sub>s</sub></b> is <b>-Mv<sup>2</sup>/r</b>.
Then, since <b>F=Ma</b>, the radial acceleration is <b>a = -v<sup>2</sup>/r</b>.
It is negative because it is in the downward direction when <b>F<sub>y</sub></b>
is at its maximum. If we are only interested in the magnitude, then <b>a
= v<sup>2</sup>/r</b>.
</p>

