<?php //This page displays privacy information.
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
//include ('libs/loginsys.php');
	include ('shortcuts/top.php');
?>
<!--Privacy info box-->
<div class="box">
<div class="headerbar"><h3>Security Tips</h3></div>
	<div class="insidebox">

	Password Bolt is secure as long as three conditions are met. The server has not been compromised, the client's computer has not been compromised, and the connection between the client and server is secure. If all of those conditions are met, Password Bolt's security comes directly from the strength of the user's password and keyfile.
	<br /><br />
	- Read the user manual and understand how Pasword Bolt works. <br />
	- <b>NEVER use Password Bolt on a computer that may have viruses.</b><br />
	- <b>NEVER use Password Bolt from a public computer.</b> (ie. hotel lobby)<br />
	- ALWAYS connect using SSL.<br />
	- Take the time to memorize a very strong password.<br />
	- Use a keyfile.<br />
	- For optimal security, host password bolt on your own computer.<br />

	</div>
</div>
<?php
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
