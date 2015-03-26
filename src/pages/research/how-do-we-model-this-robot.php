<?php
    Upvote::render_arrows(
        "howdowemodelthisrobot",
        "defuse_pages",
        "How do we model this robot?",
        "The difference between Computational Ability and Informational Ability",
        "https://defuse.ca/how-do-we-model-this-robot.htm"
    );
?>
<div class="pagedate">
March 25, 2015
</div>
<h1>How do we model this robot?</h1>

<p>
Automata Theory and Complexity Theory model <em>what machines can compute</em>,
but these models say little about <em>what machines are actually capable of
doing</em>. Let's call the things a machine can compute its <b>Computational
Ability</b> and the things a machine can do its <b>Informational Ability</b>. An
analogy to robots will make the difference clear.
</p>

<h2>Robots of two kinds</h2>

<p>
There is a robot. The robot is humanoid. It has arms and legs with motors so
that it can move around, and a camera sensor to "see." This robot exists in
a universe where true Turing Machines can actually be built. However, the robot
comes with no computer. It is up to the owner to install one. Fresh out of the
robot factory, the robot has an empty socket with some pins wired up to its
motors and camera.
</p>

<div style="text-align: center; padding: 20px; color: #777777;">
<img src="/images/bender.png">
<br />
Figure 1: Bender Bending Rodriguez
</div>

<p>
Let's install a finite state machine into the robot. We will give the state
machine connections to the robot's camera and motors, so that it gets the camera
feed as its input stream and can send signals to move by entering special
states. Our robot's brain is finite-state, so it will not be good at doing math
(after the equations reach a certain size, it won't even be able to keep its
parentheses balanced), but nevertheless it can make some rudimentary
observations about the world and can move around. It happens to be made of
titanium, and the motors are pretty powerful, so it's wise to keep our distance.
</p>

<div style="text-align: center; padding: 20px; color: #777777;">
<img src="/images/deepthought.jpg" width="400">
<br />
Figure 2: Deep Thought
</div>

<p>
Now let's do something different. Instead of giving the robot a finite-state
brain, we will give it a Turing Machine brain. The robot is excited at the
prospect of being able to do math, but it will be disappointed because we have
done something truly evil. All of the pins that ordinarily would connect the
Turing Machine to the motors and camera have been disconnected. The Turing
Machine fits snugly into the socket, but no matter how hard it tries it cannot
put any voltage onto the pins. The robot's brain can simulate a clone of the
entire universe, wherein the simulated copy of itself can move, but there is no
way the Turing Machine will ever get the robot to move in the real world.
</p>

<p>
The Finite State Robot is a machine with low Computational Ability but high
Informational Ability. It's bad at computing, but it has all the tools it needs
to observe and affect the state of things beyond itself. The Turing Machine
Robot has high Computational Ability but zero Informational Ability. It can
compute any computable function, yet it cannot learn a single bit about the real
world, nor can it meaningfully change the outside world (so long as nobody opens
it up and observes its state).
</p>

<p>
"Informational Ability" is so named because it seems to be the ability for
information to cross boundaries. When the finite state machine is looking at the
robot's camera, information is flowing from outside in. When the finite state
machine is moving the robot's arms, information is flowing from the inside to
the out.
</p>

<p>
The two notions of ability are orthogonal but tightly intertwined. Only after
both types of capability have been specified can we begin to predict what the
robot will do. If we only know the Informational Ability, we may know that it
controls enough hardware to kill a human, but without knowing the Computational
Ability, can we find out if it has motive to kill? Given the Computational
Ability we can put bounds on the robot's intelligence, but without knowing the
Informational Ability we are not sure if we should even care about its
intelligence. Both notions seem to be equally important in determining the
robot's behavior.
</p>

<h2>Should we fear the robot?</h2>

<p>
So far we have been talking about robots, but this is really about computer
security. What is the Computational Ability and Informational Ability of, say,
a JPEG image being viewed in a web browser? To find the answer to "Can
a maliciously crafted JPEG read the user's email?" we need to know both the
Computational Ability <em>and</em> Informational Ability of the pairing of
a JPEG file with the web browser.
</p>

<div style="text-align: center; padding: 20px; color: #777777;">
<img src="/images/chomsky.svg" width="300">
<br />
Figure 3: The Chomsky Hierarchy
</div>

<p>
We have great models of Computational Ability. Studying DFAs, NFAs, NPDAs,
Context-Free Grammars, and Turing Machines has given us a clear sense of which
functions can be computed by the different types of machines. Complexity theory
even aims to tell us how long it takes to compute a function.
</p>

<p>
On the other side, our models of Informational Ability <em>really</em> suck. All
we have are simplistic black-and-white access control models. Access is divided
into two or three types (read, write, execute), and permissions are either
"yes", "no", or "temporarily." To see how these models fall short of being
a theory for Informational Ability, consider the example of a cache-timing
covert channel between processes. How do we model that? Surely access control is
"broken." But how broken is it, and why is it broken? The covert channel is just
a counterexample to the hypothesis that the access control mechanism is secure,
and nothing more. We can come up with ad-hoc ways to quantify the attack, like
measuring the channel's maximum bit rate, but if we have to come up with new
ad-hoc measures for every class of attack, we don't really have a theory.
</p>

<p>
To understand security, we need good formal models of <em>both</em>
Computational Ability and Informational Ability. <a
href="http://langsec.org/">LangSec</a> is making headway applying Computational
Ability models to security, but we are still far from a workable model of
Informational Ability.
</p>

</p>
Informational Ability is <em>not</em> a topic in the field of computer security.
Rather, it is a fundamental unsolved problem of computer science. I do not
expect it to be easily solved (try defining a "boundary" for "information" to
"cross"), but the lack of a model is a clear problem. Now that we know what we
are missing, we can start looking.
</p>

<p style="color: #777777;">
Image credit: Chomsky hierarchy image by J. Finkelstein, CC-BY-SA. Bender image
from a Comedy Central promotional release, and Deep Thought image from
Touchstone Pictures (via The Guardian).
</p>
