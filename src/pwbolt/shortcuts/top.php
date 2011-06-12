<?php //stats the html GUI
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
require_once('libs/passwordbolt.php');
$user = PB::CheckLogin();
//re-encrypt the cookies
PB::ChangeOTP($user);
//to reduce overhead, the above line can be removed, it will disable re-encryption of cookies on every page load.
?>
<html>
<head>
	<title>Password Bolt</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div style="background-color:yellow">
<h2>Password Bolt is available only for peer review.
All data may be deleted at any time. Check back later for a full release!</h2>
<h4>CODERS WANTED: To help with debugging and security testing, click <a href="http://www.ossbox.com/index.php?page=passwordbolt">here.</a></h4>
</div>
<div id="all">
	<div id="header"><img src="images/header.jpg" width="700px"/></div>
	<div id="navbar" >
		<div style="float: right;">
			<a href="contact.php"  class="navlink">Contact</a>
			<a style="border:none;" href="http://www.ossbox.com/" class="navlink">Homepage</a>
		</div>
		<a href="index.php" class="navlink" >Home</a>
		<?php 
			if ($user == "") 
			{ 
				echo '<a href="createaccount.php" class="navlink"> Register</a>'; 
			}
			else 
			{
				echo '<a href="passwords.php" class="navlink" >My Passwords</a>
				<a href="notepad.php" class="navlink">&nbsp;My Notepad</a>';
			} 
		?>
		
		<?php
			if($user != "") 
			{ 
				echo '<a href="change.php" class="navlink">Settings</a>
				<a href="logout.php" class="navlink" style=" padding-left:5px;"><b>Logout</b></a>'; 
			}
		?>
	</div>
	<div id="content">
<?php
if ( !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' )
{
	$secureurl = "https://" . str_replace("www.", "", $_SERVER["SERVER_NAME"]) . $_SERVER["REQUEST_URI"];
	echo '<div  class="box">
		<div class="headerbar"><h3>No Secure Connection!</h3></div>
		<div  class="insidebox">
			<h4 style="color:red">You are not using a secure connection. <br />
			Click <a href="' . security::xsssani($secureurl) . '">here</a> to switch to a secure connection.</h4>
		</div>
	</div>';
}
?>
		
