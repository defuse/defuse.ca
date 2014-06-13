Why Unsafe State != Deadlock
#############################
:slug: why-unsafe-state-deadlock
:author: Taylor Hornby
:date: 2012-12-10 00:00
:category: programming
:tags: threads

You have a list of processes and a list of resources, and for each process you
have:

- The number of each resource they will *ever* need at once.
- The number of each resource they are currently allocated.

When a process makes a request for a resource, you only allocate the resource if
allocating the resource does not put the system into an *unsafe state*.

What is an unsafe state? 

A system is in a safe state *only if* there exists an allocation sequence that
allows the processes to finish executing. Equivalently, if the system is in
a safe state, then there exists an allocation sequence that allows the processes
to finish executing. By contrapositive, if there is no allocation sequence that
allows the processes to finish executing, then the system is in an unsafe state.

This is *not* equivalent to the converse: "If the system is in an unsafe state,
then there is no allocation sequence that allows the processes to finish
executing."

So how can a system be in an unsafe state but have an allocation sequence that
allows all processes to finish executing? Remember the assumption that the
banker's algorithm makes: It assumes all processes will request *all* resources
they would *ever* need at once, then terminate, releasing all of the resources
they just requested and the ones they held. The assumption that can be incorrect
is that processes request *all* resources they will ever need. There might not
be an allocation sequence that allows all the processes to finish executing if
they do request *all* resources they will ever need, but if the processes
*actually don't* request all resources they will ever need (maybe one less, for
example), then it might be enough for the system to avoid getting caught in
a deadlock.

See the `Wikipedia article on Banker's Algorithm`_ for a much better
explanation. 

.. _`Wikipedia article on Banker's Algorithm`: https://en.wikipedia.org/wiki/Banker%27s_algorithm
