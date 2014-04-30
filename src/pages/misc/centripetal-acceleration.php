<h1>Centripetal Acceleration</h1>


<p>
Suppose a mass $M$ is connected by a string of length $r$ to a point $C$, and
that $M$ is orbiting $C$ with a constant tangential velocity $v$ (in a circle).
What is the acceleration of the mass?
</p>

\textbf{PICTURE HERE}

<p>
The famous equation for the acceleration is $a = v^2/r$. It's usually
derived by taking the second derivative of the position vector with respect to
time. I figured out a different way to do it, which involves only basic geometry
and computing the average value of $\sin(x)$ with an integral. 
</p>

<p>
Consider two snapshots of the rotating system at times $t_0$ and $t_1$.
</p>

\textbf{IMAGE FOR t0}

\textbf{IMAGE FOR t1}

<p>
At $t_0$, the mass's momentum is completely in the $y$ direction. There is no
$x$ component to the momentum. At $t_1$, half a revolution later, the momentum
is in exactly the opposite direction. 
</p>

<p>
If the momentum at time $t_0$ is $Mv$ and the momentum at time $t_1$ is $M(-v)$,
then the total change in momentum is $-MV - MV = -2MV$. We can get the
\emph{average} change in momentum, \emph{which is the same as the average force
in the $y$ direction} by dividing by the time.
</p>

<center>
<img src="/images/phys_ca_1.png">
</center>

<p>
Okay, that tells us the \emph{average} force in the $y$ direction, but that's
not the force we want. We want the radial force. But look! We know how to write
the force in the $y$ direction as a function of the radial force. If the radial
force is $F_s$ and $\theta$ is the angle the string makes with the $y$ axis,
then:
</p>

<center>
<img src="/images/phys_ca_2.png">
</center>

<p>
So the \emph{average} of $F_s\sin(\theta)$ over the half-circle of motion will
be equal to the average of $F_y$, which we already know as $F_{y_{avg}}$!
</p>

<center>
<img src="/images/phys_ca_3.png">
</center>

<p>
That's it! The radial force $F_s$ is $-Mv^2/r$. Because $F=ma$, the radial
acceleration is therefore $a = -v^2/r$. It is negative because it is towards the
center of the circle. If we are only interested in the magnitude, then $a
= v^2/r$.
</p>

<p>
Here's a summary of what we did. The general process might be applicable to
other problems.
</p>

<ol>
    <li>Use a simple method to find the average value of some function $f$ (in this example, the average force).</li>
    <li>Write $f$ in terms of some unknown $x$ (in this example, the radial force).</li>
    <li>Equate the two \emph{averages} to solve for $x$.</li>
</ol>
