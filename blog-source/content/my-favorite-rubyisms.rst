My Favorite 'Rubyisms'
#######################
:slug: my-favorite-rubyisms
:author: Taylor Hornby
:date: 2012-05-16 00:00
:category: programming
:tags: ruby

I've been using Ruby at work to write `watir`_ tests, and I am constantly amazed
at how elegant it is. Here are some of my favorite techniques:

.. _`watir`: http://watir.com/

**Pass a default value block to Hash.new to implement a memoized recursive
function:**

.. code:: ruby

    fibonacci = Hash.new do |h,k|
        h[k] = k >= 2 ? h[k-1] + h[k-2] : 1
    end
    0.upto(100) { |i| puts fibonacci[i] }

**Use an anonymous array to perform some operation on a constant set of things
without repeating yourself:**

.. code:: ruby

    ['golf', 'baseball', 'hockey'].each do |sport|
        play(sport)
        stopPlaying(sport)
    end

Instead of:

.. code:: ruby

    play('golf')
    stopPlaying('golf')
    play('baseball')
    stopPlaying('baseball')
    play('hockey')
    stopPlaying('hockey')

**Use an anonymous array to check of some variable is equal to any of a set of things:**

.. code:: ruby

    raise 'Invalid sport' unless ['golf', 'baseball', 'hockey'].include? sport

Instead of:

.. code:: ruby

    raise 'Invalid sport' if sport != 'golf' && sport != 'baseball' && sport != 'hockey'

**Initialize an array using a range and map, and use rand() with to_s to
generate random strings:**

.. code:: ruby

    randomStrings = Array.new(100) { rand(2**64).to_s(36) }
    users = (30..50).to_a.map { |x| "testUser%d" % x }
