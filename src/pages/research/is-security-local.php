<?php
    Upvote::render_arrows(
        "issecuritylocal",
        "defuse_pages",
        "Is Security Local?",
        "Is security a locally-verifiable property of computation?",
        "https://defuse.ca/is-security-local.htm"
    );
?>
<div class="pagedate">
May 27, 2015
</div>
<h1>Is Security Local?</h1>

<p>
In physics, locality is the notion that objects are only directly influenced by
their immediate surroundings. In the context of computer science, it is often
said that &quot;computation is local&quot; meaning that a computer program's
attention is usually focused only on one region of memory at a time. This is the
basis of caching: that small region of focus will fit into the cache, and
accesses to it will be fast. At the core, these are different notions, but on
the surface they are similar, and lead us to wonder about the locality (in the
physical sense) of various computational processes.
</p>

<p>
One can ask whether security, or correctness in general, is a local property of
a computation. Can security properties be verified by checking each invididual
step, or can they only be verified by stepping back and looking at the bigger
picture? Or if both ways work, which way is more efficient?
</p>

<p>
Let's start with a toy example. Imagine a service that requests an integer from
the user, adds 1 to that number, then gives it back to the user. The service can
process multiple requests from multiple users at the same time, and we would
like to be sure that no matter which integer Alice picks, she can't influence
Bob's result.
</p>

<p>
To model this formally, let's suppose we have some notion of a two-input,
two-output Turing Machine. It's just like a standard Turing Machine, except it
takes two inputs and produces two outputs. We could even use a standard Turing
Machine by putting a special symbol on the tape to separate the two inputs and
outputs.
</p>

<p>
Peggy gives us one of these two-input two-output Turing Machines, which we'll
call X. Peggy claims X is a secure implementation of the add-one-to-both-inputs
service, but gives us no proof of this fact. The X machine is supposed to have
the property that for any pair of integers (A, B) you give it, you get (A+1,
B+1) back. It is more correct to say that X computes some function F, and that
X is &quot;secure&quot; if F(A, B) = (A+1, B+1) for all integers A and B. I'm
using the word &quot;secure&quot; here and not &quot;correct,&quot; because
I plan to weaken the requirements later.
</p>

<p>
What is the computational complexity of checking whether X is secure? If X is an
arbitrary Turing Machine, then the problem is undecidable, since we would have
to check if X at least halts on all of its inputs. But let's suppose that we
know X halts on all of its inputs. The problem is still undecidable in general,
since given a Turing Machine M, we could decide if it halts on the empty input
by constructing X so that it produces correct results unless the first integer
in the pair encodes the precise time at which M halts on the empty input. So,
despite how simple X is supposed to be, in order to have any hope of checking X,
we will have to restrict our interest to a finite range of inputs.
</p>

<p>
Now, the problem of deciding the security of X is at least NP-hard, since given
a boolean formula, we can construct an X that is correct on all inputs unless
the first integer in the pair encodes a satisfying assignment.
</p>

<p>
It should be clear by now that deciding whether X is secure, with no helpful
proof from Peggy, and without forcing Peggy to choose some X that we can fully
understand, is extremely difficult. Can we make our task easier by, instead of
trying to verify X on its own, try to verify each computation of X as it
happens? Can we check some local property at each step in X's execution on some
input to see if it is doing the right thing, and at least return an error if we
see that it isn't?
</p>

<p>
Imagine that we have some &quot;checking&quot; Turing Machine, which we'll call
C. At each step in X's execution, C is given the transcript of X's computation,
including how its state and tape contents have changed over time. C either
accepts or rejects. If C accepts, it means X has been working properly up until
now. If C rejects, it means X is not going to produce the correct result. We can
think of C as a reference monitor for every detail in the computation. If
C accepts all the way through, X produced the correct result. If C ever rejects,
X violates the security property. We will ignore the problem of finding C, and
just assume we have found the most efficient C that exists.
</p>

<p>
What is the computational complexity of C? For this toy example of adding one,
C can be simple. All C needs to do is accept until the step just before X halts,
and then on that step, find the input pair, add one to each, and check if X got
it right. This works, but is circular. If we can be assured that C securely
computes the function we are interested in, why didn't we just use C? Why bother
with X?
</p>

<p>
In order to make the notion of a checking Turing Machine useful, we need to
limit C's power. It would be nice to say something along the lines of "C cannot
itself be used to replace X", but that can't work because C might be
Turing-complete by necessity (e.g. if the function Peggy claims X computes is
the Universal Turing Machine). Instead, let's look at how runtime security
property checking works in practice. When a process opens a file, the operating
system makes the access control decision based on the privileges of the process
and the permissions of the file. The operating system does not look at the
entire history of the computation to make the decision. The decision is made on
<em>limited information</em>, so the obvious thing to do here is to restrict C's
access to information.
</p>

<p>
How should we limit C's access to information? Giving C access to the entire
computation's history is not &quot;local&quot;, so we should at least restrict
access to some constant number (depending on X) of past steps. This is still
giving C access to the entire tape contents, which, as compared to practice, is
too much, so we may want to restrict C's access even more. But for now, let's
just restrict C to a constant number of past steps.
</p>

<p>
With C's access limited to the past K configurations, the complexity of C is
less obvious. We do know that C is no more complex than verifying X on its own
(NP-hard), since C could ignore the tape contents and just check X on its own
every time it runs. But can C be any better than that? Even for the simple
function of adding one, it is not obvious. What happens when the original inputs
are no longer available to C? When the input is gone, C can no longer just
compute the function and check the answer. Even if X is nice enough to keep the
inputs around, once C no longer has access to the initial tape contents, it
cannot be sure that X hasn't changed them to fool C. Now the question is much
more interesting.
</p>

<p>
So is security local? Is there a C that's more efficient than checking X on its
own? What happens if the function we want to compute is more complicated than
adding 1, say, involving subtle interactions between the two inputs, or if the
security property is not complete correctness but something weaker. For example
if X is said to compute (A, B) &#8614; (A + B, B), the security property we care
about might only be that no matter the value of A, the second parameter never
changes. What's the complexity of C in this case?
</p>

<p>
Here's a conjecture: <em>There exist relevant functions, and security properties
of those functions, for which there is no C that is more efficient than checking
X on its own (or proving X correct).</em> If this conjecture is true, it means
security is not local, and for those nonlocal functions and security properties,
the best we can do is prove our code correct. In particular, there will be
problems that no amount of externally-applied defense, like virtual machine
isolation or network firewalling, can solve.
</p>

<p>
The conjecture I've given is ambiguous because it's not clear what domain X is
pulled from and it's not clear if X is universally or existentially quantified.
It's probably better to formulate it as a game where Peggy is trying to trick us
into using a usually-correct-but-still-bad X (that's essentially what software
developers do, but it usually isn't intentional). Feel free to interpret it any
way you like.
</p>

<p>
What do you think?
</p>
