<?php
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

require_once('libs/security.php');
require_once('libs/mysql.login.php');
require_once('libs/skey.php');


//class 'PB' provides all of the backend code for PasswordBolt, such as adding, removing, changing passwords etc..
class PB
{
	//SETTINGS:

	//Add random passwords and notepads randomly to strengthen plausible deniability.
	//safe to disable it when the website is being used a lot, as the other people's data have the same effect.
	//true - on, false - off
	private static $addrandom = true;

	//enable or disable cookie encryption key change on every page load
	//disable for high load server
	//true - on, false - off
	private static $reencryptcookies = true;

	//encrypts the otp cookie encryption key in the database (provides protection of attacker has access to db and not src)
	private static $EncryptOTPInDatabase = false;

	public static function ValidateAndPrint($token, $key, $oldmaster, $newmaster)
	{
		$salt = bin2hex(security::SuperRand());
		$skey = new skey($salt);
		$validate = $skey->GetList(91, $newmaster);
		if(PB::SetupOTP($token, $key, $oldmaster, $newmaster, $salt, $validate[90]))
		{
			$skey->PrintList($validate);
			return true;
		}
		else
		{
			return false;
		}
			
	}
	//enabled is a bool, masterpass is the raw password, current is 0 or number, last is empty or filled
	private static function SetOTP($token, $key, $enabled, $skeysalt, $masterpass, $current, $last)
	{
		if($enabled)
		{
			$enabled = security::Encrypt($key, "TRUE");
		}
		else
		{
			$enabled = security::Encrypt($key, "FALSE");
		}
		$skeysalt = security::sqlsani($skeysalt);
		$masterpass = security::SecureHash($masterpass, $key); 
		$current = (int)$current;
		$last = security::sqlsani($last);
		$check = mysql_query("SELECT * FROM skey WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		if(mysql_num_rows($check) == 1)
		{

			mysql_query("UPDATE skey SET masterpass='$masterpass', enabled='$enabled', last='$last', current='$current', skeysalt='$skeysalt'  WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		}
		else
		{
			$salt = bin2hex(security::SuperRand());
			mysql_query("INSERT INTO skey (token, salt, masterpass, enabled, last, current, skeysalt) VALUES (SHA1(concat(SHA1(concat('$token','$salt')) , '$token')), '$salt', '$masterpass', '$enabled', '$last', '$current', '$skeysalt')");
		}
	}

	public static function DisableOTP($token, $key, $oldmaster)
	{
		
		if(self::RequiresOTP($token, $key, true))
		{
			$safetoken = security::sqlsani($token);
			$result = mysql_query("SELECT masterpass from skey WHERE token=SHA1(concat(SHA1(concat('$safetoken',salt)) , '$safetoken'))");
			$result = mysql_fetch_array($result);
			if($result['masterpass'] != security::SecureHash($oldmaster, $key))
			{
				return false;
			}
		}
		self::SetOTP($token, $key, false, security::get256(), security::get256(), security::get256(), 0, '');	
		return true;
	}

	public static function SetupOTP($token, $key, $oldmaster, $newmaster, $newsalt, $validate)
	{
		if(self::RequiresOTP($token, $key, true))
		{
			//TODO: refactor this so DisableOTP can use it, merge this and requiresotp into 1 by checking num rows on this query
			$safetoken = security::sqlsani($token);
			$result = mysql_query("SELECT masterpass from skey WHERE token=SHA1(concat(SHA1(concat('$safetoken',salt)) , '$safetoken'))");
			$result = mysql_fetch_array($result);
			if($result['masterpass'] != security::SecureHash($oldmaster, $key))
			{
				return false;
			}
		}
		self::SetOTP($token, $key, true, $newsalt, $newmaster, 90, $validate);
		return true;
	}

	public static function GetOTPNumber($token)
	{
		$token = security::sqlsani($token);
		$result = mysql_query("SELECT current FROM skey WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		if(mysql_num_rows($result) == 1)
		{
			$data = mysql_fetch_array($result);
			return (int)$data['current'];
		}
	}

	public static function RequiresOTP($token, $key, $validateonly = false)
	{
		$safetoken = security::sqlsani($token);
		$result = mysql_query("SELECT enabled FROM skey WHERE token=SHA1(concat(SHA1(concat('$safetoken',salt)) , '$safetoken'))");
		if(mysql_num_rows($result) == 1)
		{
			$data = mysql_fetch_array($result);
			$enabled = security::Decrypt($key, $data['enabled']);
			return $enabled != "FALSE";
		}
		else
		{
			self::SetOTP($token, $key, false, security::get256(), security::get256(), security::get256(), 0, '');

			return false;
		}
		
		//randomize here so the amount of otps are a function of how many times people have logged in, a value that isn't available even with the database contents
		if(self::$addrandom && mt_rand() % 30 == 0)
		{
			self::SetOTP(security::get256(), security::get256() . security::get256() . security::get256(), false, security::get256(), security::get256(), 0, '');
		}
	}

	public static function ValidateOTP($username, $token, $otp)
	{
		$token = security::sqlsani($token);
		$result = mysql_query("SELECT * FROM skey WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		$data = mysql_fetch_array($result);
		$last = $data['last'];
		$skeysalt = $data['skeysalt'];
		$skey = new skey($skeysalt);
		if($skey->Verify($last, $otp))
		{
			self::SetUserCookie($username, 'skey', $otp);
			$otp = security::sqlsani($otp);
			$newnumber = (int)$data['current'] - 1;
			mysql_query("UPDATE skey SET last='$otp', current='$newnumber' WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
			return true;
		}
		else
		{
			return false;
		}
	}

	//DONT USE THIS publically, make the CheckLogin function do it!
	public static function PassedOTP($username, $token, $key)
	{
		if(self::RequiresOTP($token, $key))
		{
			$token = security::sqlsani($token);
			$result = mysql_query("SELECT last FROM skey WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
			$data = mysql_fetch_array($result);
			$last = self::GetUserCookie($username, 'skey');
			$reallast = $data['last'];
			return $last == $reallast;
		}
		else
		{
			return true;
		}
	}

	//returns an array of arrays containing decrypted password information for the account token $token.
	//array indexes: id, token, description, url
	//meant to be used for the main password list
	public static function GetPasswordList($token, $key)
	{
		$token = security::sqlsani($token);
		$list = Array();
			//get the raw data from the sql db
		$result = mysql_query("SELECT id, token, description, url FROM passwords WHERE(token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')))");
		$i = 0;
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$row['description'] = security::Decrypt($key, $row['description']);
			$row['url'] = security::Decrypt($key,$row['url']);
			$list[$i++] = $row;
		}
		self::SortDescriptionIndex($list);
		return $list;
	}
	
	//merge sorts the description index of an array of arrays
	private static function SortDescriptionIndex(&$ary)
	{
		if(count($ary) <= 1) return; //base case
		$firstlen = (int)(count($ary) / 2);
		$secondlen = count($ary) - $firstlen;
		$first = Array();
		$second = Array();
		for($i = 0; $i < $firstlen; $i++)
		{
			$first[$i] = $ary[$i];
		}
		for($i = 0; $i < $secondlen; $i++)
		{
			$second[$i] = $ary[$i + $firstlen];
		}
		self::SortDescriptionIndex($first);
		self::SortDescriptionIndex($second);
		$a = 0;
		$b = 0;
		$i = 0;
		while($a < $firstlen && $b < $secondlen)
		{
			if($first[$a]['description'] < $second[$b]['description'])
			{
				$ary[$i] = $first[$a];
				$a++;
			}
			else
			{
				$ary[$i] = $second[$b];
				$b++;
			}
			$i++;
		}
		for(; $b < $secondlen; $b++)
			$ary[$i++] = $second[$b];
		for(; $a < $firstlen; $a++)
			$ary[$i++] = $first[$a];
	}
	
	//Gets a single password from the id in my database. $token is required.
	//$key is the user's encryption key
	//refrence arguments are filled with their decrypted values
	//$doublecrypt == "TRUE" when the password is encrypted with javascript encryption
	//$encryptionSalt is the salt for javascript encryption, random noise if it's not encrypted with javascript
	//$pwname - the name/description of the password
	//$username - the username associated with the password
	//$url - the url associated with the password
	//$password - the password
	public static function GetPassword($id, $token, $key, &$doublecrypt, 
						&$encryptionSalt, &$pwname, &$username, &$url, &$password)
	{
		$id = security::sqlsani($id);
		$token = security::sqlsani($token);
		$query = mysql_fetch_array(mysql_query("SELECT description, username, password, url, encryptionsalt, encrypted FROM passwords WHERE(id='$id' AND token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))) limit 1"));
		
		$doublecrypt = security::Decrypt($key,$query['encrypted']);
		$encryptionSalt = $query['encryptionsalt'];
		$username = security::Decrypt($key,$query['username']);
		$pwname = security::Decrypt($key,$query['description']);
		$url = security::Decrypt($key,$query['url']);
		$password = security::Decrypt($key,$query['password']);
	}

	//deletes a password with $id in the database, 
	//requires $token to protect against errors allowing people to delete other people's passwords
	public static function DeletePassword($id, $token)
	{
		$id = security::sqlsani($id);
		$token = security::sqlsani($token);
		mysql_query("DELETE FROM passwords WHERE(id='$id' AND token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')))");
	}

	//Adds a password to the database or updates it if $id is set.
	//$key, $token - the key and token for the user account
	//$description - description/name for the new password
	//$passuser - the username associated with the password
	//$url - the url associated with the password
	//$encryptionSalt - the javascript encryption salt -> if empty, not javascript encrypted, if not empty password is javascript encrypted
	//$norecurse - this function calls itself to randomly add a password to the database, set to true to avoid a recursive infinite loop
	public static function AddUpdatePassword($id, $key, $token, $description, $passuser, $password, $url, $encryptionsalt, $norecurse = false)
	{
		$token = security::sqlsani($token);
		$description = security::sqlsani(security::Encrypt($key,$description));

		//add http:// on the front if it doesnt already have http:// or https:// or ftp://
		if( !empty($url) && !(self::StartsWith($url,"http://") || self::StartsWith($url,"https://") || self::StartsWith($url,"ftp://") ))
		{
			$url = "http://" . $url;
		}
		
		$url = security::sqlsani(security::Encrypt($key, $url));
		$password = security::sqlsani(security::Encrypt($key,$password));
		$passuser = security::sqlsani(security::Encrypt($key,$passuser));
		if(!empty($encryptionsalt))
		{
			$encryptionSalt = security::sqlsani($encryptionsalt);
			$encrypted = security::sqlsani(security::Encrypt($key,"TRUE"));
		}
		else
		{
			$encryptionSalt = security::sqlsani(bin2hex(security::SuperRand()));
			$encrypted = security::sqlsani(security::Encrypt($key,"FALSE"));
		}
		if(!empty($id))
		{
			mysql_query("UPDATE passwords SET description='$description', url='$url', password='$password', username='$passuser', encryptionsalt='$encryptionSalt', encrypted='$encrypted' WHERE (id='$id' AND token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')))");
		}
		else
		{
			$newidsalt = security::sqlsani(bin2hex(security::SuperRand(1)));
			mysql_query("INSERT INTO passwords (token, description, url, password, username, encryptionsalt, encrypted, salt) VALUES (SHA1(concat(SHA1(concat('$token','$newidsalt')) , '$token')), '$description', '$url', '$password', '$passuser', '$encryptionSalt', '$encrypted', '$newidsalt')");
		
			//randomly add a random row so that you cant say 
			//"there are rows that you say you dont know the password to, so you must be lying"
			if(self::$addrandom && mt_rand() % 3 == 0 && !$norecurse) 
			{
				self::AddUpdatePassword("", bin2hex(security::SuperRand(3)), bin2hex(security::SuperRand()), "fakedescription", "fakeuser", "fakepassword", "fakeurl", bin2hex(security::SuperRand()), true);
			}
		}
	}

	//Changes the password for the whole account (better described as moving them from one login information to another)
	//Decrypts every password then re-encrypts them all and updates the database
	//$user - username of the account
	//$key - key for the account
	//$oldpass - current password
	//$keyfile - current keyfile (binary string or empty string)
	//$newpass - new password
	//$newkeyfile - new keyfile
	//returns the number of passwords that were re-encrypted
	public static function ChangePassword($user, $key, $oldpass, $keyfile, $newpass, $newkeyfile)
	{

			$token = Security::UserHash($user, $oldpass, $keyfile); //create the old auth token

			$safeuser = security::sqlsani($user);
			$uresult = mysql_query("SELECT * FROM accounts WHERE(username='$safeuser')"); //validate the user
		
			$uary = mysql_fetch_array($uresult);

			$nquery = mysql_query("SELECT notepad FROM notepad WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
			$nquery = mysql_fetch_array($nquery);
			$notepad = $nquery['notepad'];

			$result = mysql_query("SELECT id, description, url, password, encrypted, username FROM passwords WHERE(token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')))"); // get a list of all of the users passwords, we need to reincrypt them
			$num = mysql_num_rows($result);			
			$newhash = security::UserHash($user, $newpass, $newkeyfile);
			$newkey = security::UserKey($user, $newpass, $newkeyfile);
					
			$notepad = security::Decrypt($key,$notepad);
			$notepad = security::sqlsani(security::Encrypt($newkey, $notepad));
			
			//loop thru all of the user's passwords and reincrypt them
			while($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				//first get the old password
				$id = security::sqlsani($row['id']);
				$description = security::Decrypt($key,$row['description']);
				$url = security::Decrypt($key,$row['url']);
				$password = security::Decrypt($key,$row['password']);
				$username  = security::Decrypt($key,$row['username']);
				$encrypted = security::Decrypt($key,$row['encrypted']);

				//reincrypt it
				$description = security::sqlsani(security::Encrypt($newkey,$description));
				$url = security::sqlsani(security::Encrypt($newkey,$url));
				$password = security::sqlsani(security::Encrypt($newkey,$password));
				$username = security::sqlsani(security::Encrypt($newkey,$username));
				$encrypted = security::sqlsani(security::Encrypt($newkey,$encrypted));
						
				//update the database with the newly encrypted password.
				mysql_query("UPDATE passwords SET description='$description', url='$url', password='$password', username='$username', token=SHA1(concat(SHA1(concat('$newhash',salt)) , '$newhash')), encrypted='$encrypted' WHERE ( token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')) AND id='$id')");
			} 
					
			//update the hash in the account table to the new authentication hash
			mysql_query("UPDATE notepad SET notepad='$notepad', token=SHA1(concat(SHA1(concat('$newhash',salt)) , '$newhash')) WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");	
			//SUCCESS
			return $num;
	}

	//deletes ALL of the passwords for a certain login information
	public static function DeletePasswords($token)
	{
		$token = security::sqlsani($token);
		mysql_query("DELETE FROM passwords WHERE (token=SHA1(concat(SHA1(concat('$token',salt)) , '$token')))");
	}
	
	//Deletes the account from the database, deletes the salt so the ciphertexts left over are useless
	public static function DeleteAccount($user, $delkey)
	{
		$tokensalt = PB::GetTokenSalt($user);
		$delhash = security::sqlsani(security::ServerHash($tokensalt . $delkey));
		$safeuser = security::sqlsani($user);
		$query = mysql_query("SELECT * FROM accounts WHERE username='$safeuser' AND deletehash='$delhash'");

		if(mysql_num_rows($query) == 1)
		{
			mysql_query("DELETE FROM accounts WHERE username='$safeuser' AND deletehash='$delhash'");
			return true;	
		}
		else
		{
			return false;
		}
	}
	
	//gets the decrypted notepad text for an account
	public static function GetNotepad($token, $key)
	{
		$token = security::sqlsani($token);
		$query = mysql_query("SELECT notepad FROM notepad WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		if($notepad = mysql_fetch_array($query))
		{
			$notepad = $notepad['notepad'];
			$notepad = security::Decrypt($key,$notepad);
			return $notepad;
		}
		return "Add contents to your notepad..";
	}

	//sets the notepad text for an account
	public static function SaveNotepad($token, $key, $notepad)
	{
		$token = security::sqlsani($token);
		$cipherText = security::Encrypt($key,$notepad);
		$cipherText = security::sqlsani($cipherText);
		$query = mysql_query("SELECT notepad FROM notepad WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");
		if(mysql_num_rows($query) == 1)
		{
			mysql_query("UPDATE notepad SET notepad='$cipherText' WHERE token=SHA1(concat(SHA1(concat('$token',salt)) , '$token'))");

			if(self::$addrandom && mt_rand() % 10 == 0) //add a random notepad witha 1/10 probability
			{
				$length = mt_rand() % 20 + 2;
				$data = "";
				for($i = 0; $i < $length; $i++)
					$data += mt_rand();
				//won't get in a recursive loop because this only gets called if token already exists
				self::SaveNotepad(bin2hex(security::SuperRand(1)), bin2hex(security::SuperRand(3)), $data);
			}
		}
		else
		{
			$npsalt = security::sqlsani(bin2hex(security::SuperRand(1)));
			mysql_query("INSERT INTO notepad (notepad, salt, token) VALUES('$cipherText', '$npsalt', SHA1(concat(SHA1(concat('$token','$npsalt')) , '$token')))");
		}
	}

	//Checks if a user is logged in, returns their username if they are, returns an empty string if they arn't
	public static function CheckLogin($checkskey = true)
	{
		$user = security::GetServerCookie('username');
		if(empty($user)) return "";
		$safeuser = security::sqlsani($user);
		$result = mysql_query("SELECT * FROM accounts WHERE (username='$safeuser' )");
		if( @mysql_num_rows($result) == 1)
		{
			$userhash = self::GetToken($user);
			$ary = mysql_fetch_array($result);
			$key = self::GetKey($user);
			$logtime = (int)security::Decrypt($key,$ary['logtime']);
			$ip = $ary['userip'];
			$ipsalt = $ary['ipsalt'];
			if ( $logtime > time() && $ip == security::ServerHash($_SERVER['REMOTE_ADDR'] . $key . $ipsalt) )
			{
				if(!$checkskey || self::PassedOTP($user, $userhash, $key))
				{
					return $user;
				}
				else
				{
					return "";
				}
			}
			else
			{
				self::Logout($user);
				return "";
			}
		}
		else
		{
			self::Logout();
			return "";
		}
	}

	//Logs a user out, if $user is empty it just deletes the cookies
	public static function Logout($user = "")
	{
		security::ExpireCookie(security::ServerHash('username'));
		security::ExpireCookie(security::ServerHash('userhash'));
		security::ExpireCookie(security::ServerHash('encryptionkey'));
		security::ExpireCookie(security::ServerHash('skey'));
	
		//clear the cookie encryption key from the database
		if(!empty($user))
		{
			$user = security::sqlsani($user);
			$rand = security::sqlsani(security::get256());
			mysql_query("UPDATE accounts SET otp='$rand' WHERE username='$user'");
			header('Location: index.php');
		}
	}

	//Sets the cookies for a login session, $logtime is the amount of time the session should last for
	//returns true if success
	public static function Login($user, $pass, $keyfile, $logtime, &$token, &$key)
	{
		$user = strtolower(trim($user));  // can't sql sanitize here because it needs to be hashed
		$hash = security::sqlsani(Security::UserHash($user, $pass, $keyfile));
		$usersani = security::sqlsani($user);
		$result = mysql_query("SELECT * FROM accounts WHERE(username='$usersani')"); //check the credentials
		if( mysql_num_rows($result) == 1)
		{
		//login success
			$key = security::UserKey($user, $pass, $keyfile);
			$token = $hash;
			security::SetServerCookie('username', $user);
	
			$otp = bin2hex(security::SuperRand(3));


		
			//security::SetServerCookie('encryptionkey', security::Encrypt($otp, $key));
			//security::SetServerCookie('userhash', security::Encrypt($otp, $hash));
			//if(self::$EncryptOTPInDatabase)
				//$otp = security::ServerEncrypt($otp);
			self::SetOTPKey($user, $otp);
			self::SetUserCookie($user, 'encryptionkey', $key, $otp);
			self::SetUserCookie($user, 'userhash', $hash, $otp);

			if($logtime == 0)
				$logtime = 60; //default to an hour
			$logtime *= 60;
			$logtime += time();
			$logtime = security::sqlsani(security::Encrypt($key, $logtime));
	
			$ipsalt = security::sqlsani(bin2hex(security::SuperRand()));
			$ip = security::sqlsani(security::ServerHash($_SERVER['REMOTE_ADDR'] . $key . $ipsalt));
			mysql_query("UPDATE accounts SET logtime='$logtime', userip='$ip', ipsalt='$ipsalt' WHERE (username='$usersani')");
			return true;
		}
		else
		{ //login failed
			return false;
		}
	}

	//creates an account
	public static function CreateAccount($username, $email, $password, $keyfile) //create a new account
	{
		$delete = bin2hex(security::SuperRand(1));
		$keysalt = self::formatSalt(security::SuperRand(2));
		$tokensalt = self::formatSalt(security::SuperRand(2));
		$delhash = security::sqlsani(security::ServerHash($tokensalt . $delete));
		$username = security::sqlsani($username);
		$email = security::sqlsani($email);
	
		$keysalt = security::sqlsani($keysalt);
		$tokensalt = security::sqlsani($tokensalt);
	

		mysql_query("INSERT INTO accounts (username, email, keysalt, tokensalt, deletehash) VALUES ('$username', '$email', '$keysalt', '$tokensalt', '$delhash')");
		return $delete;
	}

	//checks if a username exists
	public static function UserExists($user)
	{
		$user = security::sqlsani($user);
		$result = mysql_query("SELECT * FROM accounts WHERE (username='$user')");
		if(mysql_num_rows($result) == 1)
		{
			return true;
		}
		return false;
	}
	
	//gets the user's encryption key that is saved in the cookie
	public static function GetKey($username) //will get the encryption key for the current user
	{
		/*$otp = self::GetOTPKey($username);
		$key = security::Decrypt($otp, security::GetServerCookie('encryptionkey'));*/
		return self::GetUserCookie($username, 'encryptionkey');
		//return $key;
	}

	//gets the token for a user account from the cookie
	public static function GetToken($username) //will get the token of the current user
	{
		/*$otp = self::GetOTPKey($username);
		$token = security::Decrypt($otp, security::GetServerCookie('userhash'));*/
		return self::GetUserCookie($username, 'userhash');
		//return $token;
	}

	//changes the 'one time' cookie encryption key
	public static function ChangeOTP($username)
	{
		if(self::$reencryptcookies && !empty($username))
		{
			$key = self::GetKey($username);
			$hash = self::GetToken($username);
			$skey = self::GetUserCookie($username, 'skey');
			if(!empty($hash) && !empty($key))
			{
			$usersani = security::sqlsani($username);
			$otp = bin2hex(security::SuperRand(3));
					self::SetUserCookie($username, 'encryptionkey', $key, $otp);
					self::SetUserCookie($username, 'userhash', $hash, $otp);
					self::SetUserCookie($username, 'skey', $skey, $otp);
					self::SetOTPKey($username, $otp);
			}
		}
	}

	//gets the salt for making a token from a username
	public static function GetTokenSalt($username)
	{
		$safeusername = security::sqlsani($username);
		$query = mysql_query("SELECT tokensalt FROM accounts WHERE username='$safeusername'");
		$result = mysql_fetch_array($query);
		$salt = $result['tokensalt'];
		return $salt;
	}

	//credit: whoever made this
	private static 	function StartsWith($Haystack, $Needle)
	{
		// Recommended version, using strpos
		return strpos(strtolower($Haystack), strtolower($Needle)) === 0;
	}
	
	//takes 512 bits of random data (binary string from security::SuperRand(2)) and turns it into salt
	private static function formatSalt($random)
	{
		$charset = "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
		return security::format($charset, $random);
	}

		//gets the user's one time key
	public static function GetOTPKey($user)
	{
		if(empty($user)) return "";
		$user = security::sqlsani($user);
		$result = mysql_query("SELECT otp FROM accounts WHERE (username='$user')");
		$ary = mysql_fetch_array($result);
		$otp = $ary['otp'];
		if(self::$EncryptOTPInDatabase)
			$otp = security::ServerDecrypt($otp);
		return $otp;
	}
	
	//sets the users one time key
	public static function SetOTPKey($user, $key)
	{
		$user = security::sqlsani($user);
		if(self::$EncryptOTPInDatabase)
			$key = security::ServerEncrypt($key);
		$key = security::sqlsani($key);
		
		mysql_query("UPDATE accounts SET otp='$key' WHERE (username='$user')");
	}

	//Sets a cookie encrypted with the user's 'one time key'
	// $username - username of the user (currently logged in)
	// $name - name of the cookie 
	// $data - plaintext data of the cookie
	public static function SetUserCookie($username, $name, $data, $key = NULL)
	{
		if(is_null($key))
			$key = self::GetOTPKey($username);
		$data = security::Encrypt($key, $data);
		setcookie(security::ServerHash($name), $data);
	}
	
	//Gets the decrypted value of a cookie encrypted with the user's 'one time key'
	// $username - the username of the user logged in
	// $name - the name of the cookie
	public static function GetUserCookie($username, $name, $key = NULL)
	{
		$name = security::ServerHash($name);
		$data = "";
		if(is_null($key))
			$key = self::GetOTPKey($username);
		if(isset($_COOKIE[$name]))
		{
			$data = $_COOKIE[$name];
			$data = security::Decrypt($key, $data);
		}
		return $data;
	}
}
?>
