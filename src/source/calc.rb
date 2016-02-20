# Desktop calculator extension for IRB.
# Source: https://defuse.ca/

# TODO: quadratic eqn factoring ( or maybe even general polynomial factoring if easy)
# Integer factorial function
# x C y, x P y

# Intro message (called at the end of this script)
def intro
  puts "+----------------------------- RUBY CALCULATOR -------------------------------+"
  puts "|  0b - Binary    .b - To Binary    Constants: e, pi     ln(e), lg(2), log(10)|"
  puts "|  0x - Hex       .h - To Hex       _ gives last result                       |"
  puts "|  0  - Octal     .o - To Octal     q quits                                   |"
  puts "+-----------------------------------------------------------------------------+"
  puts "|  Window closes after 10 minutes of inactivity. Type 'keepalive' to disable. |"
  puts "+-----------------------------------------------------------------------------+"
end

# Include math so we can type sin(x) instead of Math.sin(x)
include Math
# Alias the logarithm functions so that:
# - log(x) is logarithm base 10
# - lg(x) is logarithm base 2
# - ln(x) is logarithm base Math::E
# These aliases do not affect the Math class. Math.Log(x) is still base E
# Even though ruby 1.9's Math::log takes the base as a parameter, we do it this
# way for compatibility with ruby 1.8.
alias ln log
def log(x)
  ln(x)/ln(10)
end
def lg(x)
  ln(x)/ln(2)
end 

def fact(x)
  if x < 0
    raise Exception.new("Factorial of number < 0")
  elsif x == 0
    return 1
  else
    $fact_cache ||= {}
    if $fact_cache[x]
      return $fact_cache[x]
    end
    return $fact_cache[x] = x * fact(x-1)
  end
end

def choose(n, k)
  return fact(n) / (fact(k) * fact(n - k))
end

# A module that can be mixed in to an Integer class to get easy base conversion.
module IntegerExtensions
  def toHex
    self.to_s(16)
  end
  alias tohex toHex
  alias h toHex

  def toOctal
    self.to_s(8)
  end
  alias toOct toOctal
  alias tooct toOctal
  alias tooctal toOctal
  alias o toOctal

  def toBinary
    self.to_s(2)
  end
  alias toBin toBinary
  alias tobin toBinary
  alias tobinary toBinary
  alias b toBinary

  # This is not necessary, but I have included it here to be consistent.
  # e.g. 0xFF.d => 255
  def toDecimal
    self
  end
  alias toDec toDecimal
  alias todec toDecimal
  alias todecimal toDecimal
  alias d toDecimal
end

def pi ; PI ; end
def e ; E ; end

# Extend both Fixnum and Bignum with our base conversion extensions, and make
# integer division print a warning message if information is lost.
[Fixnum, Bignum].each do |klass|
  klass.class_eval do 
    include IntegerExtensions
    alias olddiv /  
    def /(x)
      if x.is_a?(Integer) && self % x != 0
        puts "WARNING: Integer division with remainder." 
      end
      olddiv(x)
    end
  end
end

def fdiv
  Fixnum.class_eval do 
    def /(x)
      self.to_f / x.to_f
    end
  end
  puts "*** Floating point division mode ***"
end

def idiv
  Fixnum.class_eval do
    def /(x)
      if x.is_a?(Integer) && self % x != 0
        puts "WARNING: Integer division with remainder." 
      end
      olddiv(x)
    end
  end
  puts "*** Integer division mode ***"
end

# Rudimentary screen clearing
def clear
  50.times do
    puts ""
  end
  nil
end
alias cls clear # For DOS/Windows users

# Type q to quit
alias q exit!

# I always end up with 300 of these things open at the end of the day, so let's
# make it close automatically after 10 minutes of inactivity.
TIMEOUT_SECONDS = 10 * 60
$time_left = TIMEOUT_SECONDS

Thread.new() do 
  loop do
    sleep 10
    break if $time_left.nil?
    $time_left -= 10
    exit! if $time_left <= 0
  end
end

# Hook eval (used internally by irb) to detect user input
alias oldEval eval
def eval(*opts)
  $time_left = TIMEOUT_SECONDS unless $time_left.nil?
  oldEval(*opts)
end

def keepalive
  $time_left = nil
  puts "*** Automatic shutdown disabled ***"
end

# Finally, print the intro message.
intro
