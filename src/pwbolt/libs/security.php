<?php //security.php is the class that contains all of the cryptographic features of pwbolt
    //This file is part of Password Bolt.

    //Password Bolt is free software: you can redistribute it and/or modify
    //it under the terms of the GNU General Public License as published by
    //the Free Software Foundation, either version 3 of the License, or
    //(at your option) any later version.

    //Password Bolt is distributed in the hope that it will be useful,
    //but WITHOUT ANY WARRANTY; without even the implied warranty of
    //MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    //GNU General Public License for more details.

    //You should have received a copy of the GNU General Public License
    //along with Password Bolt.  If not, see <http://www.gnu.org/licenses/>.
require_once('libs/mysql.login.php');
class security
{

	private static $encryptOTPInDatabase = true;

	//LOTS OF RANDOM DATA - ALL DATA HERE CAN BE IN ANY FORMAT
	
	//KEY FOR THE RANDOM DATA GENORATOR, CAN BE CHANGED OFTEN
	private static $randgen = "ZSc4F5TKQYhZkUopUZj0QrNSWAmU9Vh0pRiG5dH3O0ZJaZsn7jrnLtPbplPvyC9f";
	
	//KEY FOR SERVERENCRYPT - SHOULD BE 768 BITS (512 for encryption 256 for hash salt)
	private static $privEncryptionKey = "7AB0517D306B6ABD126657E035A622722CE1A87CC7B333C380AC68CFDDDACEB7328A8B7B035A53B554BC6E4456359AC1262E317430918A9A47D9EA0B6FCDDA57595D513B48A7EAD6B6277BEADF96D30A59BBB9DCF685BED5217BC623C1BDD981";
	
	//KEY FOR SERVERHASH
	private static $privHMACKey = "L0rfgqFl8iBsEs8GO5Ls64sK3XPTTXzMZr2t6MtPL6cUq03EhyeRdY3p7keWNN24";
	
	//SALT FOR SERVERHASH
	private static $privateSalt = "Q7iXIjm0Sy4aZfvmZ1rui9r2Kypp9h6fk79Jw3SnkUTPllD4X5UcEgw0PG6R0b3P";

	//Do not modify:
	//first 300 decimal places of PI
	private static $PI_DIGITS_F300 = "14159265358979323846264338327950288419716939937510582097494459230781640628620899862803482534211706798214808651328230664709384460955058223172535940812848111745028410270193852110555964462294895493038196442881097566593344612847564823378678316527120190914564856692346034861045432664821339360726024914127";
	//second 300 digits of PI	
	private static $PI_DIGITS_S300 = "37245870066063155881748815209209628292540917153643678925903600113305305488204665213841469519415116094330572703657595919530921861173819326117931051185480744623799627495673518857527248912279381830119491298336733624406566430860213949463952247371907021798609437027705392171762931767523846748184676694051";
	
	//WHEN USING HMAC TO GENERATE A KEY:
	//use the salt + $privateSalt as the data, and the password as the HMAC key
	//the HMAC key influences the HMAC twice, therefore it is better for the password to be the key than the salt

	//generic data decryption
	// $key - the password in any form, should be >=512 bits
	// $data - ciphertext to decrypt
	// returns - plaintext
	public static function Decrypt($key, $data)
	{
		if(strlen($key) != 768 / 4)
		{
			echo "Error: invalid key: " . strlen($key);
			die();
		}
		$tfkey = substr($key, 0, 64);
		$aeskey = substr($key, 64, 64);
		$mackey = substr($key, 128, 64);
		if(strlen($data) < 64)
		{
			return "";
		}
		$validation = substr($data, 0, 64);
		$IV = substr($data, 64, 64);
		$data = substr($data, 128);
		$cthmac = self::SecureHash($IV . $data , $mackey);
		if($cthmac == $validation)
		{
			//@ - suppress no IV warning
			$data = @mcrypt_decrypt(MCRYPT_TWOFISH , self::hex2bin($tfkey), self::hex2bin($data), MCRYPT_MODE_ECB);
			return str_replace(chr(0), '', mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::hex2bin($aeskey), $data, MCRYPT_MODE_CBC, self::hex2bin($IV)));
		}
		else //this should NEVER happen under normal operation, so it's safe to do this. 
		{
			//return "WARNING: POSSIBLE CIPHERTEXT TAMPERING! please report to an administrator. Contact information can be found on the contact page. This should never happen under normal operation of Password Bolt, it could mean that the database has been compromised.";
			//this event should be saved to a logfile, as it would mean the database has been compromised.
			return "";
			//return "WARNING: CIPHERTEXT TAMPERING! please report to administrator.";
			//die();
		}	
	}
	
	//generic data encryption
	// $key - the password in any form, should be >=512 bits
	// $data - plaintext to encrypt
	// returns - ciphertext
	public static function Encrypt($key, $data)
	{
		if(strlen($key) != 768 / 4)
		{
			echo "Error: invalid key: " . strlen($key);
			die();
		}
		$tfkey = substr($key, 0, 64);
		$aeskey = substr($key, 64, 64);
		$mackey = substr($key, 128, 64);
		//I use AES-BLOWFISH encryption because a *theoretical* weakness in AES could reduce the brute force time of a 256 bit key down to 2^119
		$IV = self::get256(); //iv in hex
		$ct =  mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::hex2bin($aeskey), $data, MCRYPT_MODE_CBC, self::hex2bin($IV));
		// @ - suppress no IV warning
		$ct =  $IV . bin2hex(@mcrypt_encrypt(MCRYPT_TWOFISH, self::hex2bin($tfkey), $ct, MCRYPT_MODE_ECB));
		$total = self::SecureHash($ct , $mackey) . $ct;
		return $total;
	}

	//Encrypt data with the server's private key
	// $data - data to encrypt
	// returns - ciphertext
	public static function ServerEncrypt($data)
	{
		return self::Encrypt(self::$privEncryptionKey, $data);
	}

	//Decrypt data with the server's private key
	// $data - data to decrypt, that was encrypted with ServerEncrypt
	// returns - ciphertext
	public static function ServerDecrypt($data)
	{
		return self::Decrypt(self::$privEncryptionKey, $data);
	}
	
	// GRC.com's perfect password system
	// $rounds - the number of 256 bit blocks random data you want
	// returns - the amount of random data you requested in binary format.
	public static function SuperRand($rounds = 1)
	{
		$rndkey = self::$randgen;
		$result = mysql_query("SELECT IV, counter FROM random");
		$data = mysql_fetch_array($result);
		$IV = $data['IV'];
		$counter = $data['counter'];
		$return = "";
				
		for($i = 0; $i <= $rounds; $i++) //extra round so the stuff in the database isn't the last thing returned
		{
			$counter = self::hexIncrement($counter);
			
			//I chose to do it this way so that anyone with the database contents could not reverse the algorithm
			$IV =  self::ServerHash($rndkey . $IV . $counter . mt_rand() . mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
			//$counter = self::String_Increment($counter, 7) . mt_rand());
			
			if($i != $rounds) //don't add the last round.
			{
				$return .= self::hex2bin($IV);
			}
		}
		
		mysql_query("UPDATE random SET IV='$IV', counter='$counter'");
		return $return;
	}
	
	//Hashes data with the server's secret salt
	// $data - the data to hash
	// returns - 256 bit hex string of the hash result
	public static function ServerHash($data)
	{
		return self::SecureHash($data, self::$privHMACKey);
	}
	
	//Generic hashing function salted with the server's salt
	// $data - data to hash
	// $salt - salt to be hashed alongside the data
	// returns - 256 bit hex string hash
	public static function SecureHash($data, $key)
	{
		return hash_hmac('sha256', $data . self::$privateSalt, $key);
	}
	
	//Hash with different salts to create the user's validation token
	// $username - username of the user
	// $password - the password of the user
	// $keyfile - the keyfile of the user (binary)
	// returns - 256 bit hex string token
	public static function UserHash($username, $password, $keyfile, $salt = "")
	{
		if(empty($salt))
		{
			$safeusername = self::sqlsani($username);
			$query = mysql_query("SELECT tokensalt FROM accounts WHERE username='$safeusername'");
			$result = mysql_fetch_array($query);
			$salt = $result['tokensalt'];
		}
		return self::SecureHash($salt, $username . $password . $keyfile);
	}
	
	//Create the user's key
	// $username - the user's username
	// $password - their password
	// $keyfile - their keyfile
	// returns - 256 bit hex string key
	public static function UserKey($username, $password, $keyfile, $salt = "")
	{
		if(empty($salt))
		{
			$safeusername = self::sqlsani($username);
			$query = mysql_query("SELECT keysalt FROM accounts WHERE username='$safeusername'");
			$result = mysql_fetch_array($query);
			$salt = $result['keysalt'];
		}
		return self::makeKey($salt, $username . $password . $keyfile);
	}
	
	//a more secure way to create a key from a password, 
	//uses HMAC-SHA512 and HMAC-WHIRLPOOL xored into a 768 bit key in hex. Meant for the Encrypt function
	//this type of hash is not needed for authentication as SHA256 is strong enough
	//returns a 768 bit key if $fulllength is true
	public static function makeKey($salt, $password, $fulllength = false)
	{
		//W - the respective byte of the whirlpool hmac with first 300 pi digits.
		//S - the respective byte of the SHA512 hmac wih first 300 pi digits
		//X - the respective byte of whirlpool hmac with second 300 pi digits.
		//T - the respective byte of SHA512 hmac with the second 300 pi digits.
		//|WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|
		//                                              xor
		//|SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT|
		
		//FIRST 512 BITS
		$whirlpool = hash_hmac('whirlpool', $salt . self::$privateSalt . self::$PI_DIGITS_F300, $password, true);
		$sha512 = hash_hmac('SHA512', $salt . self::$privateSalt . self::$PI_DIGITS_F300, $password, true);
		$key = "";

		for($i = 0; $i < 64; $i++)
		{
			$key .= chr(ord($whirlpool[$i]) ^ ord($sha512[$i]));
		}

		//LAST 256 BITS
		//since both hashes use the same algorithms and data, 
		//the difference between the first 300 digits of PI and the second 300 digits of pi create the difference.
		$mackey1 = hash_hmac('whirlpool', $salt . self::$privateSalt . self::$PI_DIGITS_S300, $password, true);
		$mackey2 = hash_hmac('SHA512', $salt . self::$privateSalt . self::$PI_DIGITS_S300, $password, true);
		for($i = 0; $i < 32; $i++)
		{
			$key .= chr(ord($mackey1[$i]) ^ ord($mackey2[$i]));
		}

		return bin2hex($key);
	}

	public static function smartslashes($data)
	{
		if(get_magic_quotes_gpc())
		{
			return stripslashes($data);
		}
		else
		{
			return $data;
		}
	}
	
	//Converts a hex string to binary string
	// $h - the hex string to convert
	// returns - binary data
	public static function hex2bin($h)
	{
		if (!is_string($h)) return null;
		$r='';
		for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
		return $r;
	}
	
	//Gets a cookie encrypted with the server's private key
	// $name - name of the cookie
	// returns - the plaintext value of the cookie
	public static function GetServerCookie($name)
	{
		if(isset($_COOKIE[self::ServerHash($name)]))
			return self::ServerDecrypt($_COOKIE[self::ServerHash($name)]);
	}
	
	//Sets a cookie encrypted with the server's private key
	// $name - the name of the cookie
	// $data the plaintext data of the cookie
	public static function SetServerCookie($name, $data)
	{
		setcookie(self::ServerHash($name), self::ServerEncrypt($data));
	}
	
	
	//Expires a cookie, to expire cookies created with SetServerCookie, the name must be security::ServerHash'ed first.
	public static function ExpireCookie($name)
	{
		setcookie($name, "", 1);
	}
	
	//protects against sqli
	public static function sqlsani($data)
	{
		return mysql_real_escape_string($data);
	}

	public static function format($charset, $random)
	{
		$mods = "";
		for($i = 0; $i < 64; $i++)
		{
			$remainder = 0;
			$total = 0;
			$quotient = "";
			$divisor = strlen($charset);
			for($j = 0; $j < strlen($random); $j++)
			{
				$total = ($remainder * 256 + ord(substr($random, $j, 1)));
				$quotient .= chr($total / $divisor);
				$remainder = $total % $divisor;	
			}
			$random = $quotient;
			$mods .= substr($charset, $remainder, 1);
		}
		return $mods;
	}
	
	//protects against XSS
	public static function xsssani($data)
	{
		return htmlspecialchars($data, ENT_QUOTES);
	}
	
	
	//increments a hex string by 7 (backwards but it works for our purpose)
	//for security, you can change the number it increments by but it doesnt really matter.
	private static function hexIncrement($hexstring)
	{
		$index = strlen($hexstring) - 1;
		$total = 7;
		while($total > 0)
		{
			$val = hexdec( substr($hexstring, $index,1));
			$val += $total;
			if($val > 15)
			{
				$val = $val % 16;
				$total = 1;
			}
			else
			{
				$total = 0;
			}
			$fpart = substr($hexstring, 0, $index);
			$lpart = substr($hexstring, $index + 1, strlen($hexstring) - $index - 1);
			$hexstring = $fpart . dechex($val) . $lpart;
			$index--;
		}
		return $hexstring;
	}
	
	//returns 256 bit hex string of weak random data.
	public static function get256()
	{
		$hex = "0123456789abcdef";
		$rand = "";
		for($i = 0; $i < 64; $i++)
		{
			$num = mt_rand() % 16;
			$r = substr($hex,$num,1);
			$rand .= $r;
		}
		return $rand;
		//Warning: self::SuperRand(1) being called here breaks the login system.
		////return bin2hex(self::SuperRand(1));
	}

}
?>
