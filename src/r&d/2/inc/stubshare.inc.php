<?php
require_once('db.inc.php');
require_once('constants.inc.php');
class StubShare
{

	public static function EncodeStubImage($stubID)
	{
		$html = "<a href=\"" . WEBROOT . "stub.php?id=$stubID\">\r\n<img style=\"border:none\" src=\"" . WEBROOT . "stubview.php?id=$stubID\" />\r\n</a>";
		return $html;
	}
	
	public static function EncodeStubBBC($stubID)
	{
		return "TODO";
	}

	public static function ShareStub($username, $stubID)
	{
		$q = mysql_query("SELECT * FROM stubs WHERE id='$stubID'");
		if($q && mysql_num_rows($q) > 0)
		{
			$stub_info = mysql_fetch_array($q);
			$product = (int)$stub_info['product'];
			$userid = self::GetUserID($username);
			self::AddStub($product, $userid);
		}
	}

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

	public static function ProcessPurchase($username, $stub_id)
	{
		$stubInfo = self::GetStubInfo($stub_id);
		$productInfo = self::GetProductInfo($stubInfo['product']);
		$userInfo = self::GetUserInfo($username);

		$userid = (int)$userInfo['id'];
		$productid = (int)$productInfo['id'];

		if(self::UserOwns($userid, $productid))
		{
			return $productInfo['url'];
		}

		$cost = (double)$productInfo['price'];
		$balance = (double)$userInfo['balance'];

		if($cost <= $balance)
		{
			$balance = $balance - $cost;
			mysql_query("UPDATE users SET balance='$balance' WHERE id='$userid'");
			mysql_query("INSERT INTO purchases (user, product) VALUES('$userid', '$productid')");

			//now distribute the money
			$owner_pct = (double)$productInfo['owner_pct'];
			$sharer_pct = (double)$productInfo['sharer_pct'];
			$charity_pct = (double)$productInfo['charity_pct'];
			$stubshare_pct = (double)$productInfo['stubshare_pct'];
			$profit_donate = (double)$userInfo['profit_for_charity'];

			$owner_total = $cost * $owner_pct / 100;
			$stubshare_total = $cost * $stubshare_pct / 100;
			$sharer_total = $cost * $sharer_pct / 100;
			$charity_total = $cost * $charity_pct / 100;

			$profit_to_charity = $sharer_total * $profit_donate / 100;
			$charity_total += $profit_to_charity;
			$sharer_total -= $profit_to_charity;

			$ownerID = (int)$productInfo['owner'];
			$charityID = (int)$userInfo['charity'];

			mysql_query("UPDATE users SET balance = balance + $owner_total WHERE id='$ownerID'");
			mysql_query("UPDATE charities SET balance = balance + $charity_total WHERE id='$charityID'");
			mysql_query("UPDATE profit SET balance = balance + $stubshare_total WHERE name='stubshare'");
			
			return $productInfo['url'];
		}
		else
		{
			return false;
		}
	}

	public static function UserOwns($userid, $product)
	{
		$owns = false;
		$userid = (int)$userid;
		$product = (int)$product;
		$q = mysql_query("SELECT * FROM purchases WHERE user='$userid' AND product='$product'");
		if( $q && mysql_num_rows($q) > 0) $owns = true;
		
		$q = mysql_query("SELECT * FROM products WHERE id='$product' AND owner='$userid'");
		if($q && mysql_num_rows($q) > 0) $owns = true;

		if($owns)
		{
			$q = mysql_query("SELECT url FROM products WHERE id='$product'");
			if($q && mysql_num_rows($q) > 0)
			{
				$info = mysql_fetch_array($q);
				return $info['url'];
			}
		}
		else
		{
			return false;
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

		$prod_id = mysql_insert_id();
		
		//Create default stub
		$main_stub_id = self::AddStub($prod_id, $userID);
		mysql_query("UPDATE products SET mainstub='$main_stub_id' WHERE id='$prod_id'");
	}

	public static function AddStub($product, $stub_owner_id)
	{
		if(self::GetProductInfo($product) === false)
			return;

		$product = mysql_real_escape_string($product);
		$stub_owner_id = mysql_real_escape_string($stub_owner_id);

		//avoid duplicates
		$q = mysql_query("SELECT * FROM stubs WHERE owner='$stub_owner_id' AND product='$product'");
		if($q && mysql_num_rows($q) > 0)
			return;

		mysql_query("INSERT INTO stubs (product, owner) VALUES('$product', '$stub_owner_id')");
		return mysql_insert_id();
	}

	public static function GetStubInfo($stubID)
	{
		$stubID = mysql_real_escape_string($stubID);
		$q = mysql_query("SELECT * FROM stubs WHERE id='$stubID'");
		if($q && mysql_num_rows($q) > 0)
		{
			return mysql_fetch_array($q);
		}
		else
		{
			return false;
		}
	}

	public static function GetUserInfo($username)
	{
		$safe_user = mysql_real_escape_string($username);
		$q = mysql_query("SELECT * FROM users WHERE username='$safe_user'");
	
		if($q&& mysql_num_rows($q) >= 1)
		{
			return mysql_fetch_array($q);
		}
		else
		{
			return false;
		}
	}

	public static function GetUserID($username)
	{
		$info = self::GetUserInfo($username);
		return $info['id'];
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