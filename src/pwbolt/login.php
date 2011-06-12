<?php //this page is used to authenticate the user and it's login info, it will set the cookies
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
	require_once ('libs/mysql.login.php');
	require_once ('libs/security.php');
	require_once('libs/passwordbolt.php'); 

	$user = security::smartslashes($_POST['user']);
	$pass = security::smartslashes($_POST['pass']);
	$filename = security::smartslashes($_FILES["file"]["tmp_name"]);
	
	//get the keyfile
	$keyfile = "";
	if($_FILES["file"]["error"] == 0 && isset($_FILES["file"]["tmp_name"]))
	{
		//this protects against file size, but what if file size is greater then 2^32?
		if(filesize($filename) < (1024 * 1024 * 10))
		{
			$keyfile = file_get_contents($filename); 
		}

	}
	$token = "";
	$key = "";
	if(PB::Login($user, $pass, $keyfile, (int)$_POST['time'], $token, $key)) //check if login success
	{
		if(PB::RequiresOTP($token, $key))
		{
			Header( 'Location: skey.php');
		}
		else
		{
			Header( 'Location: passwords.php');
		}
	}
	else
	{
		Header( 'Location: index.php?invalid=true' );
	}

  
	include ('libs/sqlclose.php');
?> 


