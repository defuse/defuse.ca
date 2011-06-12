<?php
require_once('db.inc.php');
class StubShare
{

	public static function GetUserBalance($username)
	{
		$safe_user = mysql_real_escape_string($username);
		$q = mysql_query("SELECT balance FROM users WHERE username='$safe_user'");
		if($q && mysql_num_rows($q) >= 1)
		{
			$ary = mysql_fetch_array($q);
			return $ary['balance'];
		}
		else
		{
			return -1;
		}
	}

	public static function AddUserBalance($username, $toAdd)
	{
		$current = self::GetUserBalance($username);
		$newBalance = $current + $toAdd;
		
		$safe_user = mysql_real_escape_string($username);
		$safe_bal = mysql_real_escape_string($newBalance);
		
		mysql_query("UPDATE users SET balance='$safe_bal' WHERE username='$safe_user'");
	}

	public static function AddProduct($username, $productName, $description, $price, $owner_pct, $sharer_pct, $charity_pct, $stubshare_pct, $url)
	{
		$userID = (int)self::GetUserID($username);
		
		if($userID == -1)
			return;

		$safe_name = mysql_real_escape_string($productName);
		$safe_desc = mysql_real_escape_string($description);
		$safe_price = mysql_real_escape_string($price);
		$safe_url = mysql_real_escape_string($url);
		$owner_pct = (double)$owner_pct;
		$sharer_pct = (double)$sharer_pct;
		$charity_pct = (double)$charity_pct;
		$stubshare_pct = (double)$stubshare_pct;
		
		mysql_query("INSERT INTO products (owner, name, description, url, price, owner_pct, sharer_pct, charity_pct, stubshare_pct) VALUES('$userID', '$safe_name', '$safe_desc', '$safe_url', '$safe_price', '$owner_pct', '$sharer_pct', '$charity_pct', '$stubshare_pct')");
		echo mysql_error();
	}

	public static function GetUserID($username)
	{
		$safe_user = mysql_real_escape_string($username);
		$getID = mysql_query("SELECT id FROM users WHERE username='$safe_user'");
	
		if($getID && mysql_num_rows($getID) >= 1)
		{
			$ary = mysql_fetch_array($getID);
			return $ary['id'];
		}
		else
		{
			return false;
		}
	}

	public static function GetUsernameFromID($id)
	{
		$safe_id = mysql_real_escape_string($id);
		$q = mysql_query("SELECT username FROM users WHERE id='$safe_id'");
	
		if($q && mysql_num_rows($q) > 0)
		{
			$ary = mysql_fetch_array($q);
			return $ary['username'];
		}
		else
		{
			return false;
		}
	}

	public static function GetProductInfo($id)
	{
		$safe_id = mysql_real_escape_string($id);
		$q = mysql_query("SELECT * FROM products WHERE id='$safe_id'");
		if($q && mysql_num_rows($q) > 0)
		{
			return mysql_fetch_array($q);
		}
		else
		{
			return false;
		}
	}

	public static function GetCharityName($id)
	{
		$safe_id = mysql_real_escape_string($id);
		$q = mysql_query("SELECT name FROM charities WHERE id='$safe_id'");
		if($q && mysql_num_rows($q) > 0)
		{
			$ary = mysql_fetch_array($q);
			return $ary['name'];
		}
		else
		{
			return false;
		}
	}


}

?>
