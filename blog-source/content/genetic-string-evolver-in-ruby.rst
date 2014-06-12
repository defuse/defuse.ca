[Ruby] Genetic String Evolver
###############################
:slug: genetic-string-evolver-in-ruby
:author: Taylor Hornby
:date: 2013-02-19 00:00
:category: programming
:tags: evolution

Inspired by `Richard Dawkins' weasel program`_, this is a genetic algorithm,
written in Ruby, to evolve the same "METHINKS IT IS LIKE A WEASEL" string. It's
really fun to experiment with changing the probability constants (mutation,
reproduction) and the algorithm in ``Evolver#advance``, which advances the
population of strings to the next generation. Have fun!

.. _`Richard Dawkins' weasel program`: https://en.wikipedia.org/wiki/Weasel_program

The default algorithm works as follows:

1. Initialize a population of random strings.
2. Mutate each string with probability 'mutation_probability'.
3. Breed each string with another random string from the population with
   probability 'breed_probability', keeping the newborn population separate.
4. Kill random newborns until the number of newborns is less than 1/4 of the
   size of the main population.
5. Select a random half of the main population and replace its weakest (furthest
   from the target) members with the remaining newborns.
6. Go back to Step 2.

In about 5000-10000 generations, the strongest member of the population
stabilizes to the target string.

The same algorithm can be applied to other problems, like finding the maximum of
a mathematical function, by just modifying ``Weasel.rb``.

Sample Output
==============

The left column is the population's strongest member. The right column is the
population's weakest member.

.. code:: text

    YAPXMLHQFORHPPNVWF DISTEBZEK -- vs. -- YZLHUURLMPKITAP ZYVTTSIZZCQV (Gen: 0) 
    YAPXMLHQFORHPPNVWF DISTEBZEK -- vs. -- CXZWQCVCUAZFDOUSZNRCXDQPNENR (Gen: 1) 
    YAEBXJFACES FVUTYWTLATTIACDY -- vs. -- CXZWQCVCUAZFDOUSZNRCXDQPNENR (Gen: 2) 
    YAEBXJFACES FVUTYWTLATTIACDY -- vs. -- J ICBDLWUGASSNRS YENZDLVBJ V (Gen: 3) 
    YAEBXJFACES FVUTYWTLATTIACDY -- vs. -- NJQPSUPJUBEUQGNEBPRCMHIVBJ V (Gen: 4) 
    YAEBXJFACES FVUTYWTLATTIACDY -- vs. -- NJQPSUPJUBEUQGNEBPRCMHIVBJ V (Gen: 5) 
    YAEBXJFACES FVUTYWTLATTIACDY -- vs. --  RQPSUPJUBEUQWSFJTJ OWHRTKIM (Gen: 6) 
    MDLDIGLMBUJPTV LWEC W FMNINL -- vs. -- ZXZNNKDUGWIVLKARQLMDKCIZ FGW (Gen: 7) 
    MDLDIGLMBUJPTV LWEC W FMNINL -- vs. -- ZXZNNKDUGWIVLKARQLMDKCIZ FGW (Gen: 8) 
    ...
    MDPXKNIS MVHXZ LHCC W ODACEL -- vs. -- MDLDMFQS MVHCZ LHCCGW IQACDY (Gen: 100) 
    MDPXKNIS MVHXZ LHCC W ODACEL -- vs. -- MDJDIFVS M DAS HHXC W TSACEL (Gen: 101) 
    MDPXKNIS MVHXZ LHCC W ODACEL -- vs. -- MDJDMNQS MVGXZ LHCC W FQACDL (Gen: 102) 
    MADIMNQS MVDXZ LHCE W ODACEL -- vs. -- MDJDMNQS MVGXZ LHEC W FMACIL (Gen: 103) 
    MDPXKNQS MVDAS HHCE W ODACEL -- vs. -- MDJDMNQS MVGXZ LHEC W FMACIL (Gen: 104) 
    MDPXKNQS MVDAS HHCE W ODACEL -- vs. -- MAPXYNCM MVHXZ LHTC D DQACIL (Gen: 105) 
    ...
    METHINIS ITHIS LHKE A VMAQEL -- vs. -- METHINIS IVHWS LHKEXA VMAQEL (Gen: 400) 
    METHINIS ITHIS LHKE A VMAQEL -- vs. -- METHINIS RVHIS LHKEEA VMAQEL (Gen: 401) 
    METHINIS ITHIS LHKE A VMAQEL -- vs. -- METHINIS RVHIS LHKEEA VMAQEL (Gen: 402) 
    METHINIS ITHIS LHKE A VMACEL -- vs. -- METHINIS IVHWS LHKEXA VMAQEL (Gen: 403) 
    METHINIS ITHIS LHKE A VMAQEL -- vs. -- METHFNIS RVHIS LHKE A VMAQEL (Gen: 404) 
    METHINIS ITHIS LHKE A VMAQEL -- vs. -- METHFNIS RRHIS LHKE A VMAQEL (Gen: 405) 
    ...
    METHINIS IV IS LHKE A WMAQEL -- vs. -- MVTHINIS IV IS QHKE AMVMAQEL (Gen: 800) 
    METHINIS IV IS LHKE A WMAQEL -- vs. -- METUINIS IV IS LHKE AMVMEQEL (Gen: 801) 
    METHINIS IV IS LHKE A WMAQEL -- vs. -- METUINIS IV IS LHKE AMVMEQEL (Gen: 802) 
    METHINIS IV IS LHKE A WMAQEL -- vs. -- METUINIS IV IS LHKE AMVMEQEL (Gen: 803) 
    METHINIS IV IS LHKE A WMAQEL -- vs. -- METHINIS IVQISKLHKE A VMAQEL (Gen: 804) 
    METHINIS IV IS LHKE A WMAQEL -- vs. -- METCINIS IV RS LHKK A WMAQEL (Gen: 805) 
    ...
    METHINKS IT IS LIKE A WEAWEL -- vs. -- ROTHINKS IT IS LIKE A WEAHEL (Gen: 5106) 
    METHINKS IT IS LIKE A WEAWEL -- vs. -- MOTHINKS ITSIS VIKE A WEAWEL (Gen: 5107) 
    METHINKS IT IS LIKE A WEAWEL -- vs. -- MOTHINKS ITSIS VIKE A WEAWEL (Gen: 5108) 
    METHINKS IT IS LIKE A WEAWEL -- vs. -- METHINKSOIT IS LIKE A WEAWEL (Gen: 5109) 
    METHINKS IT IS LIKE A WEASEL -- vs. -- METHINKSOIT IS LIKE A WEAWEL (Gen: 5110) 


Main.rb
========

.. code:: ruby

    require './Evolver.rb'
    require './Weasel.rb'
    
    evolver = Evolver.new( Weasel )
    evolver.setup
    
    i = 0
    while 1
      print "#{evolver.best} -- vs. -- #{evolver.worst} (Gen: #{i}) \r"
      evolver.advance
      i += 1
    end 

Evolver.rb
===========

.. code:: ruby

    # WARNING: I have never even read `On the Origin of Species` so do not mistake
    # this horrible code to be anything like what happens in nature.
    
    class Evolver
    
      attr_reader :population
      attr_accessor :population_size, :mutation_probability, :breed_probability
    
      def initialize( klass )
        @entity = klass
        @population = []
        @population_size = 100
        @mutation_probability = 0.07
        @breed_probability = 0.3
      end
    
      def setup
        @population_size.times do |i|
          @population << @entity.random
        end
      end
    
      def advance
        @population.each do |subject|
          if rand() <= @mutation_probability
            subject.mutate
          end
        end
    
        newborns = []
        @population.each do |subject|
          if rand() <= @breed_probability
            newborns << subject.breed( @population[rand(population.length)] )
          end
        end
    
        # Limit the number of newborns to at most 1/4 of the population size.
        if newborns.length * 4 > @population.length
          newborns.shuffle!
          newborns = newborns.first( @population.length / 4 )
        end
    
        # Select a random half of the population to be candidates for dying.
        killed = @population.shuffle.first( @population.length / 2 )
        # Sort them by goodness value in increasing order.
        killed.sort! do |a,b|
          if a.goodness < b.goodness
            -1
          elsif b.goodness > a.goodness
            1
          else
            0
          end
        end
    
        # Replace the weakest death candidates with the newborns.
        newborns.each_with_index do |subj,k|
          idx = @population.index( killed[k] )
          @population[idx] = subj
        end
      end
    
      def best
        @population.inject do |a,b|
          ( a.goodness > b.goodness ) ? a : b
        end
      end
    
      def worst
        @population.inject do |a,b|
          ( a.goodness > b.goodness ) ? b : a
        end
      end
    
    end 

Weasel.rb
===========

.. code:: ruby

    class Weasel
      attr_reader :str
    
      ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ "
      TARGET = "METHINKS IT IS LIKE A WEASEL"
    
      def self.random
        str = ""
        TARGET.length.times do |i|
          str << ALPHABET[rand(ALPHABET.length)]
        end
        return Weasel.new( str )
      end
    
      def initialize( str )
        @str = str
      end
    
      def mutate
        @str[rand(@str.length)] = ALPHABET[rand(ALPHABET.length)]
      end
    
      def breed( organism )
        split = rand(@str.length)
        front = @str[0...split]
        back = organism.str[split...organism.str.length]
        return Weasel.new( front + back )
      end
    
      def goodness
        hamming = 0
        0.upto( @str.length - 1 ) do |i|
          hamming += (@str[i].ord ^ TARGET[i].ord).to_s(2).count("1")
        end
        return -hamming
      end
    
      def to_s
        @str
      end
    
    end 
