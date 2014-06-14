Awesome Desktop Calculator using Ruby's IRB
############################################
:slug: awesome-desktop-calculator-using-rubys-irb
:author: Taylor Hornby
:date: 2012-06-09 00:00
:category: linux
:tags: calculator

Ruby is an awesome programming language. Its metaprogramming capabilities make
it easy to turn the interactive ruby interpreter (irb) into an awesome desktop
calculator. To do so, we can make irb execute a ruby script, containing some
calculator extensions before it starts, with the following command: 

.. code:: bash

    irb -r /path/to/the/file.rb

Of course, you will want to make an alias or keyboard shortcut for this. In
bash, this can be done by adding something like the following to ~/.bashrc:

.. code:: bash

    alias rcalc='irb -r /path/to/the/file.rb' 

Here's a link to my `custom ruby calculator script`_. Here's what it does: 

.. _`custom ruby calculator script`: https://defuse.ca/source/calc.rb

.. code:: text

    +----------------------------- RUBY CALCULATOR -------------------------------+
    |  0b - Binary    .b - To Binary      Constants: E, PI                        |
    |  0x - Hex       .h - To Hex         _ gives last result                     |
    |  0  - Octal     .o - To Octal       q quits                                 |
    +-----------------------------------------------------------------------------+
    |  Window closes after 10 minutes of inactivity. Type 'keepalive' to disable. |
    +-----------------------------------------------------------------------------+
    irb(main):001:0> 0b11111111
    => 255
    irb(main):002:0> _.h
    => "ff"
    irb(main):003:0> PI
    => 3.14159265358979
    irb(main):004:0> E
    => 2.71828182845905
    irb(main):005:0> 2**256 / 3**5
    WARNING: Integer division with remainder.
    => 476510655297597512031156316908180690754197467759837712096533267522276253662
    irb(main):006:0> 6/3
    => 2
    irb(main):007:0> 5.0/2
    => 2.5
    irb(main):012:0> sin(PI/2)
    => 1.0
    irb(main):008:0>

I will be adding functionality to this script as I need it. Some things I'm
planning are:

- Unit conversions
- Easier way to convert strings in different bases to integers
- Efficient factorial function
- Common sequences such as Fibonacci
- Common formulas
- Common constants (c, G, etc.) 

If you have any ideas, please leave a comment and I'll add it to the list. 
