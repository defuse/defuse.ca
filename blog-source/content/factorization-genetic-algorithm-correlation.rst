Factorization Correlation Experimentation Tool
###############################################
:slug: factorization-genetic-algorithm-correlation
:author: Taylor Hornby
:date: 2013-01-25 00:00
:category: programming
:tags: factorization

Here's a Ruby script to experiment with correlations that may be useful in
developing a genetic (evolutionary) algorithm for factoring the product of two
primes. The comments explain it:

.. code:: ruby

    #!/usr/bin/env ruby
    # Let C = F1 * F2, where F1 and F2 are primes.
    #
    # This script gives a visual aid to help determine if the value of some function
    # (specified by the user) on a test integer is correlated with the test
    # integer's hamming distance from min(F1, F2).
    #
    # If such a correlation exists, it may be possible to use that function to
    # calculate the 'fitness' of test integers in a genetic (evolutionary) algorithm
    # for factoring C.
    #
    # Of course the function must be efficiently computable without knowing the
    # factorization of C.
    #
    # Set the function by modifying the 'function' method below. Set the F1 and F2
    # by modifying the 'F1' and 'F2' variables below. C is calculated from F1 and
    # F2.
    #
    # Some interesting functions are:
    #   - The hamming distance between C and the test integer.
    
    # If you don't have the gnuplot gem, run `gem install gnuplot`
    require 'gnuplot'
    
    F1 = 8831
    F2 = 12479
    
    # Set this to true to make the multiplicity of points visible by adding random
    # variations in their horizontal position.
    JITTER = false
    
    F_DESC = "HammingDistance(test, C)"
    def function( composite, potentialFactor )
      hammingDistance( composite, potentialFactor )
    end
    
    # ----------------------------------------------------------------------------
    
    def hammingDistance( a, b )
      (a ^ b).to_s( 2 ).count( "1" )
    end
    
    results = []
    
    compareFactor = [F1, F2].min
    composite = F1 * F2
    
    1.upto( Math.sqrt( composite ).ceil * 4 ) do |i|
      results << [ function( composite, i ), hammingDistance( i, compareFactor ) ]
      puts i if i % 100_000 == 0
    end
    
    Gnuplot::open do |gp|
      Gnuplot::Plot.new( gp ) do |plot|
        plot.title "C = F1 * F2 where F1 and F2 are prime. \\n" +
                    "C = #{F1 * F2}; F1 = #{F1}; F2 = #{F2}."
        plot.ylabel "HammingDistance(test, min(F1, F2))"
        plot.xlabel F_DESC
        plot.xrange "[0:]"
        plot.yrange "[0:]"
    
        x = results.map { |r| r[0] }
        y = results.map { |r| r[1] }
    
        if JITTER
          x = x.map { |v| v + rand() / 10 }
        end
    
        plot.data << Gnuplot::DataSet.new( [x, y] ) do |ds|
          ds.with = JITTER ? "dots" : "points"
          ds.notitle
        end
    
      end
    end 
