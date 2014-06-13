
x86 Stack Diagrams
##########################################
:slug: x86-stack-diagrams
:author: Taylor Hornby
:date: 2012-08-14 00:00
:category: programming
:tags: x86, assembly


All of the x86 stack diagrams I could find are either cluttered with too much
information, can't be read from a distance, or put the high address on top (I'm
sure there are people who prefer that but... I'm not one of them). So I made my
own.

**With low address on top (stack growing up, strcpy going down):**

.. image:: https://defuse.ca/images/stack-low-high.gif
    :alt: x86 Stack with low addresses on top

**With high address on top (stack growing down, strcpy going up):**

.. image:: https://defuse.ca/images/stack-high-low.gif
    :alt: x86 Stack with high addresses on top
