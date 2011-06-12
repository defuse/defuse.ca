<?php
/* 
 * Memorable Password Generator
 * By: FireXware (http://ossbox.com/)
 * Contact: firexware@gmail.com
 * License: Do whatever you want. Attribution would be nice though.
 * 
 * This is an experimental script that attempts to generate
 * memorable passwords. It does so by using a list of "memorable sequences"
 * to create padding that gets attached to a SHORT random password.
 *
 * The idea is that if an attacker did not know this script was used to
 * generate the password, they would have to brute force through the 
 * enitre set of ASCII passwords of whatever length.
 *
 * This was also developed to see if it is possible to programatically
 * generate memorable passwords that are secure even when an attacker
 * knows this script was used to create the password. Theoretically, we can 
 * calculate the exact number of passwords this script can output, giving 
 * us the keyspace an attacker would have to search if he knew this script 
 * was used.
 *
 * This script estimates the security of a password by monitoring the calls to
 * the random number generator while a password is being created. If the random
 * number is different, the password will be different, so estimating the search
 * space can be done by multiplying togeather the amount of possible return values
 * for each RNG call. The script outputs "security" as the log base 2 of the result
 * of this multiplication, so it can be easily compared with block cipher key sizes.
 *
 * Since the number of calls to the RNG depend upon previous calls to the RNG, 
 * estimations are much different than the actual number of passwords this 
 * script can generate. 
 *
 * This script also computes the "exact security" which is the maximum number of passwords
 * the script can possibly output. It may be larger than the actual number because it doesn't
 * account for duplicate passwords, but it's the closest approximation that's possible.
 *
 * The "ideal security" this script outputs is the security based on a brute force with
 * ASCII characters on the length of the password.
 *
 * The security of the passwords output by this script can be changed by increasing 
 * the amount of memorable strings and tweaking constants.
 */
 
 //DEFAULT MEMORABLE SEQUENCES
 /*
`1234567890
=-0987654321`
qwertyuiop[]
asdfghjkl;'
zxcvbnm,./
~!@#$%^&*()_+
QWERTYUIOP{}|
ASDFGHJKL:"
ZXCVBNM<>?
abcdefghijklmnopqrstuvwxyz
ABCDEFGHIJKLMNOPQRSTUVWXYZ
 */
 
ini_set("error_reporting", "E_ALL");
ini_set("display_errors", 1);

//NOTE: There is no sanity checking on ANY of the constants.

//The "padding blocks" before the random string
define("PADDING_BEFORE_MIN", 2);
define("PADDING_BEFORE_MAX", 3);

//The padding blocks after the random string
define("PADDING_AFTER_MIN", 2);
define("PADDING_AFTER_MAX", 3);

//The max pattern length when creating a block
define("PATTERN_LENGTH_MAX", 6);
define("PATTERN_LENGTH_MIN", 2); //be careful: all the memorable strings must be at least this long

//The number of times to repeat each character in the pattern
define("CHAR_REPEAT_MIN", 1);
define("CHAR_REPEAT_MAX", 5);

//Size of the random string in the middle of the padding
define("RANDOM_MIN", 5);
define("RANDOM_MAX", 8);

//Number of passwords to generate
define("NUM_PASSWORDS", 15);

//File containing memorable sequences
define("SEQUENCES_PATH", "memorable.txt");

//Load a file of line-separated memorable sequences
$stringsfile = file_get_contents(SEQUENCES_PATH);
$memorable_strings = explode("\r\n", $stringsfile);

//Ignore empty lines
for($i = 0; $i < count($memorable_strings); $i++)
{
	if(empty($memorable_strings[$i]))
	{
		unset($memorable_strings[$i]);
	}
}

$memorable_strings = array_values($memorable_strings);

$rand_int_security = 1.0;
$security_total = 0;
echo "<pre>";
for($i = 0; $i < NUM_PASSWORDS; $i++) //generate NUM_PASSWORDS passwords
{
	$password = "";
	
	//Decide how many "padding blocks" go before and after the random string
	$padding_before = RandInt(PADDING_BEFORE_MIN, PADDING_BEFORE_MAX);
	$padding_after = RandInt(PADDING_AFTER_MIN, PADDING_AFTER_MAX);
	
	//apply padding before the random string
	for($p = 0; $p < $padding_before; $p++)
	{
		$password .= GetPadding($memorable_strings);
	}
	
	$password .= GetRandom();
	
	//apply padding after the random string
	for($p = 0; $p < $padding_after; $p++)
	{
		$password .= GetPadding($memorable_strings);
	}
	print htmlspecialchars($password, ENT_QUOTES) . "\n";
	$effectivebits = log($rand_int_security) / log(2);
	$security_total += $effectivebits;
	$idealbits = strlen($password) * log(94) / log(2); //log_2(94^length)
	print "SECURITY BITS: $effectivebits\nIDEAL BITS: $idealbits \n\n" ;
	$rand_int_security = 1.0;
}
$avgbits = $security_total / NUM_PASSWORDS;
$exactbits = log(ExactSecurity($memorable_strings))/log(2);
echo "<b>AVG SECURITY BITS: $avgbits</b>\n";
echo "<b>\"EXACT\" SECURITY BITS: $exactbits</b>\n";
echo "</pre>";

//returns a random ASCII string
//length ranging from RANDOM_MIN to RANDOM_MAX inclusive
function GetRandom()
{
	$ascii = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~1!2@3#4$5%6^7&8*9(0)-_=+{[}]|\\;:\'"<,>.?/';
	$pass = "";
	$len = RandInt(RANDOM_MIN, RANDOM_MAX);
	for($i = 0; $i < $len; $i++)
	{
		$pass .= substr($ascii, mt_rand() % strlen($ascii),  1);
	}
	return $pass;
}

//Generates a block of padding made from a memorable sequence
//The memorable sequence is chosen randomly from $memorable_strings
//Returns the padding block
function GetPadding($memorable_strings, $idx = -1) //$idx is for debugging and testing
{
	//NOTE: if this code is changed, the code in ExactSecurityBits must be changed too!
	$padding = "";
	
	//choose a random memorable sequence
	if($idx == -1)
		$idx = RandInt(0, count($memorable_strings) - 1);
	$basetext = $memorable_strings[$idx];
	
	//choose the size of the pattern and starting position
	$maxpat = (strlen($basetext) > PATTERN_LENGTH_MAX) ? PATTERN_LENGTH_MAX : strlen($basetext);

	$pattern_length = RandInt(PATTERN_LENGTH_MIN, $maxpat);
	$start_idx = RandInt(0, strlen($basetext) - $pattern_length);

	//choose how many times to repeat each character in the pattern
	$char_repeat = RandInt(CHAR_REPEAT_MIN, CHAR_REPEAT_MAX);
	
	//create the padding block
	for($i = 0; $i < $pattern_length; $i++) //over each character in the pattern
	{
		$curchar = substr($basetext, $start_idx + $i, 1);
		for($c = 0; $c < $char_repeat; $c++) //repeating each character
		{
			$padding .= $curchar;
		}
	}
	return $padding;
}

//Generates a random integer using the built in CSPRNG (/dev/urandom on *nix)
//Returns random integer between min and max both INCLUSIVE
function RandInt($min, $max) 
{
	global $rand_int_security;
	$rand_int_security *= ($max - $min + 1);
	$rand = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
	$randInt = hexdec(substr(bin2hex($rand), 0, 7)); //less 4 bits because php will interpret it as negative in the following line???

	return ($randInt % ($max - $min + 1)) + $min;
}

//Computes the "EXACT" possible passwords this entire script can return
function ExactSecurity($memorable_strings)
{
	//NOTE: This function is HEAVILY dependant upon the code in GetPadding()
	//		It must be updated if the code in GetPadding() is changed.
	
	$security = 0;
	
	//all sizes of middle random password
	for($i = RANDOM_MIN; $i <= RANDOM_MAX; $i++)
		$security += pow(94, $i);
		
	//every possible # of padding blocks
	$security *= PADDING_BEFORE_MAX - PADDING_BEFORE_MIN + 1;
	$security *= PADDING_AFTER_MAX - PADDING_AFTER_MIN + 1;
	
	//each padding block is made of one of the memorable strings
	$security *= count($memorable_strings);

	//calculate number of possible combinations of a specific padding block 
	for($i = 0; $i < count($memorable_strings); $i++) //each padding block is based on one memorable string
	{
		$basetext = $memorable_strings[$i];
		//the algorithm first choses the pattern size
		$maxpat = (strlen($basetext) > PATTERN_LENGTH_MAX) ? PATTERN_LENGTH_MAX : strlen($basetext);
		$security *= $maxpat - PATTERN_LENGTH_MIN + 1;
		
		//it then chooses the starting index based on the pattern size
		//so for each substring size there is a different number of possible starting index
		for($patlen = PATTERN_LENGTH_MIN; $patlen <= $maxpat; $patlen++) //each pattern size
		{
			$max_start_index = (strlen($basetext) - $patlen) ; 
			$security *= $max_start_index + 1; //number of possible starting index based on current pattern size
		}
		$security *= CHAR_REPEAT_MAX - CHAR_REPEAT_MIN + 1; //each padding block also decides how many times each char is repeated
	}
	return $security;
}
?>
