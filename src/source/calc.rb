# Desktop calculator extension for IRB.
# Source: https://defuse.ca/

# Intro message (called at the end of this script)
def intro
  puts "+----------------------------- RUBY CALCULATOR -------------------------------+"
  puts "|  0b - Binary    .b - To Binary      Constants: E, PI                        |"
  puts "|  0x - Hex       .h - To Hex         _ gives last result                     |"
  puts "|  0  - Octal     .o - To Octal       q quits                                 |"
  puts "+-----------------------------------------------------------------------------+"
  puts "|  Window closes after 5 minutes of inactivity. Type 'keepalive' to disable.  |"
  puts "+-----------------------------------------------------------------------------+"
end

# Include math so we can type sin(x) instead of Math.sin(x)
include Math

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
end

# Finally, print the intro message.
intro
