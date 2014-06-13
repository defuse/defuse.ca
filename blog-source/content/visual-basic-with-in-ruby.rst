Visual Basic's "With" in Ruby
##############################
:slug: visual-basic-with-in-ruby
:author: Taylor Hornby
:date: 2012-08-04 00:00
:category: programming
:tags: basic

.. code:: ruby

    def with( obj )
      yield obj
    end

    with Math do |m|
      puts m.exp( 1 )
      puts m.exp( 2 )
      puts m.exp( 3 )
    end

    # Deeply nested modules
    module Foo
      module Bar

        module Baz
          A_CONSTANT = 3
          def self.aMethod( x )
            x * A_CONSTANT + A_CONSTANT
          end
        end

        module Biz
          def self.anotherMethod( x )
            "hello #{x}"
          end
        end

      end
    end

    with Foo::Bar do |m|
      puts m::Baz.aMethod( 5 )
      puts m::Biz.anotherMethod( "bob" )
    end 
