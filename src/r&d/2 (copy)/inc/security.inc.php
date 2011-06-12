<?php
define("SESSION_COOKIE", "session");
define("USERNAME_COOKIE", "username");
class Security
{
	//Takes a password and returns the salted hash
	//$password - the password to hash
	//returns - the hash of the password (128 hex characters)
	public static function HashPassword($password)
	{
		$salt = bin2hex(mcrypt_create_iv(32,MCRYPT_DEV_URANDOM)); //get 256 random bits in hex
		$hash = hash("sha256", $salt . $password); //prepend the salt then hash
		//store the salt and hash in the same string, so only 1 DB column is needed
		$final = $salt . $hash; 
		return $final;
	}

	//Validates a password
	//returns true if hash is the correct hash for that password
	//$hash - the hash created by HashPassword (stored in your DB)
	//$password - the password to verify
	//returns - true if the password is valid, false otherwise.
	public static function ValidatePassword($password, $correctHash)
	{
		$salt = substr($correctHash, 0, 64); //get the salt from the front of the hash
		$validHash = substr($correctHash, 64, 64); //the SHA256

		$testHash = hash("sha256", $salt . $password); //hash the password being tested
	
		//if the hashes are exactly the same, the password is valid
		return $testHash === $validHash;
	}

	public static function TryLogin($username, $password)
	{
		require_once('db.inc.php');
		
		$safe_username = mysql_real_escape_string($username);
		
		//get the real hash
		$q = mysql_query("SELECT * FROM users WHERE username='$safe_username'");
		if($q && mysql_num_rows($q) >= 1)
		{
			$info = mysql_fetch_array($q);
			$real_hash = $info['password'];
			$suc = self::ValidatePassword($password, $real_hash);
			if($suc)
			{
				$session_key = self::GenerateSessionKey();
				self::SetSessionKey($username, $session_key);
			}
			return $suc;
		}
		else
		{
			return false;
		}
	}

	private static function GenerateSessionKey()
	{
		return bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	}

	private static function SetSessionKey($username, $key)
	{
		require_once('db.inc.php');
		$safe_user = mysql_real_escape_string($username);
		$key_hash = mysql_real_escape_string(self::SecureHash($key));
		mysql_query("UPDATE users SET session='$key_hash' WHERE username='$safe_user'");
		//now give them the session key in a cookie
		setcookie(SESSION_COOKIE, $key, 0, "/"); //TODO: cookie SECURE flag
		setcookie(USERNAME_COOKIE, $username, 0, "/");
	}

	public static function GetCurrentUser()
	{	
		require_once('db.inc.php');
		$session_key = $_COOKIE[SESSION_COOKIE];
		$user = $_COOKIE[USERNAME_COOKIE];
		$safe_user = mysql_real_escape_string($user);
		
		//get the valid hash
		$q = mysql_query("SELECT * FROM users WHERE username='$safe_user'");
		if($q && mysql_num_rows($q) >= 1)
		{
			$ary = mysql_fetch_array($q);
			$valid_hash = $ary['session'];
			$check_hash = self::SecureHash($session_key);
			if($check_hash === $valid_hash)
			{
				return $user;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	private static function SecureHash($data)
	{
		return hash("sha256", $data);
	}

}
?>
