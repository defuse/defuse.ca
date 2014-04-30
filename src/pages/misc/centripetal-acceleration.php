<h1>Centripetal Acceleration</h1>


<p>
A mass <i>M</i> is connected by a string of length <i>r</i> to a point <i>C</i>.
The mass is orbiting <i>C</i> in a circle with a constant tangential velocity
<i>v</i>.  What is the radial acceleration <i>a</i> of the mass?
</p>

<center>
<img src="/images/phys_ca_d1.png">
</center>

<p>
The well-known equation for the acceleration is <i>a = v<sup>2</sup>/r</i>. It's
usually derived by taking the second derivative of <i>M</i>'s position vector
with respect to time. 
</p>

<p>
I worked out a different way to do it, which involves only basic geometry and
the average value of <i>sin(x)</i> from <i>0</i> to <i>&pi;</i>.  We find the
centripetal force by the following process:
</p>

<ol>
    <li>
        Use a simple method to find the average value of some function <i>f</i>.
        For this problem, <i>f</i> is the force in the <i>y</i> direction.
    </li>
    <li>
        Write <i>f</i> in terms of some unknown <i>x</i>. For this problem,
        <i>x</i> is the radial force.
    </li>
    <li>
        Equate the two <em>averages</em> to solve for <i>x</i>.
    </li>
</ol>

<p>
I suspect this process will prove useful for solving other problems as well.
Let's see how we can use it to solve the centripetal acceleration problem.
</p>

<p>
Consider two snapshots of the rotating system at times <i>t<sub>0</sub></i> and
<i>t<sub>1</sub></i>.
</p>

<center>
<div style="width: 300px; display: inline-block;"><strong>t<sub>0</sub></strong></div>
<div style="width: 300px; display: inline-block;"><strong>t<sub>1</sub></strong></div>
<br />
<img src="/images/phys_ca_d2.png" width="300">
<img src="/images/phys_ca_d3.png" width="300">
</center>

<p>
At <i>t<sub>0</sub></i>, the mass's momentum is completely in the <i>y</i>
direction.  There is no <i>x</i> component to the momentum. At
<i>t<sub>1</sub></i>, half a revolution later, the momentum is in the exact
opposite direction. 
</p>

<p>
If the momentum at time <i>t<sub>0</sub></i> is <i>Mv</i> and the momentum at
time <i>t<sub>1</sub></i> is <i>M(-v)</i>, then the total change in momentum is
<i>-Mv - Mv = -2Mv</i>. We can get the <em>average</em> rate of change of the
momentum in the <i>y</i> direction by dividing by the time. Since the force in
the <i>y</i> direction is the rate of change of momentum in the <i>y</i>
direction, this is also equal to the <em>average force</em> in the <i>y</i>
direction.
</p>

<center>
<img src="/images/phys_ca_1.png">
</center>

<p>
Okay, that tells us the average force in the <i>y</i> direction, but
that's not the force we want. We want the radial force. 
</p>

<p>
But look! We know how to write the force in the <i>y</i> direction as a function
of the radial force. If the radial force is <i>F<sub>s</sub></i> and
<i>&theta;</i> is the angle the string makes with the <i>y</i> axis, then:
</p>

<center>
<img src="/images/phys_ca_2.png">
</center>

<p>
So the <em>average</em> of <i>F<sub>s</sub>sin(&theta;)</i> over the half-circle
of motion will be equal to the average of <i>F<sub>y</sub></i>, which we already
know as <i>F<sub>y<sub>avg</sub></sub></i>!
</p>

<center>
<img src="/images/phys_ca_3.png">
</center>

<p>
That's it! The radial force <i>F<sub>s</sub></i> is <i>-Mv<sup>2</sup>/r</i>.
Then, since <i>F=ma</i>, the radial acceleration is <i>a = -v<sup>2</sup>/r</i>.
It is negative because it is towards the center of the circle. If we are only
interested in the magnitude, then <i>a = v<sup>2</sup>/r</i>.
</p>

