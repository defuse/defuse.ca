<?php


//MUST 
class skey
{
	private $salt = "";

	public function skey($salt)
	{
		$this->salt = $salt;
	}

	public function GetList($n, $password)
	{
		$list = Array();
		$previous = $password;
		for($i = 0; $i < $n; $i++)
		{
			$previous = $this->HashEncode($previous , $this->salt);
			$list[$i] = $previous;
		}
		return $list;
	}

	/*//MUST be a 60 element list
	public function PrintList($ary)
	{

		$height = 15;
		$n = $height * 4;
		//$ary = $this->GetList($n, $password);
		
		echo "<table><tr>";
		for($j = 0; $j < $n; $j += $height)
		{
			echo "<td>";
			echo '<table style="text-align:center; border: solid black 1px"><tr><td></td><th>One Time Passwords&nbsp;</th></tr>';
			for($i = 0; $i < $height; $i++)
			{
				echo "<tr><td></td><td>----------------------------</td></tr>";
				echo "<tr><td>" . ($j + $i+1) . ".</td><td>" . $ary[$i + $j] . "</td></tr>";
			
			}
			echo "</table>";
			echo "</td>";
		}
		echo "</tr></table>";
		echo "<br /><br />Cut out the one time passwords. Provide them to the server from the bottom up, tearing off and discarding the bottom one after it has been used.";
	}*/
	public function PrintList($ary)
	{

		$height = 9;
		$n = $height * 10;
		//$ary = $this->GetList($n, $password);
		echo "<b>Password Bolt One Time Passwords</b>";
		echo '<table border="0" cellspacing="0" cellpadding="0" style="font-family: Courier New, Courier, monospace; font-size:large; border:solid black 1px;" >';
		$color = 0;
		for($j = $n - 1; $j > 0; $j -= $height)
		{
			if($color++ % 2 == 0)
				echo "<tr style='color:blue'>";
			else
				echo "<tr style='color:red'>";
			for($i = 0; $i < $height; $i++)
			{
				$num = $j - $i + 1;
				if($num < 10)
					$num = '0' . $num;
				echo '<td style="border-right: solid grey 1px;">' . $num . '</td><td style="padding-left: 5px; padding-right: 5px; border-right: solid black 2px;" >' . $ary[ $j - $i] . "</td>";
			
			}
			echo "</tr>";
		}
		echo "</table>";
		echo "<br /><br />You will now require these one time passwords to login. Print the page, cut out the table and keep it with you. When you run out of passwords or you lose this list, you will need your master password to generate a new one.";
	}

	public function Verify($previous, $check)
	{
		return $this->HashEncode($check, $this->salt) == $previous;		
	}

	private function HashEncode($data, $salt)
	{
		//must be 64 characters long
		$set = "!#%+23456789:=?@ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
		$result = "";
		$hash = hash('SHA512', $data . $salt, true);
		for($i = 0; $i < 6; $i++)
		{
			$result .= $set[ord($hash[$i]) % 64];
		}
		return $result;
	}
}

?>
