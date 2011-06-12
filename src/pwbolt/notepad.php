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
	require_once ('libs/mysql.login.php');
	require_once ('libs/passwordbolt.php');

	if(($user = PB::CheckLogin()) == "")
	{
		header( 'Location: index.php' ) ;
	}
	else
	{
		$key = PB::GetKey($user);
		$token = PB::GetToken($user);
	}

	include('shortcuts/top.php');
	$saved = false;
	if(isset($_POST['save']))
	{
		$notepad = security::smartslashes($_POST['notepad']);

		PB::SaveNotepad($token, $key, $notepad);
		$saved = true;
	}
	
?>
<div  class="box">
<div class="headerbar"><h3>Encrypted Notepad</h3></div>
	<div  class="insidebox">
	<?php if($saved == true) { echo '<center>Notepad saved.</center>'; } ?>
		<form action="notepad.php" method="post" >
			<textarea style="width:100%; height: 500px;" name="notepad"><?php
				echo security::xsssani( PB::GetNotepad($token, $key));
			?></textarea><br />
			<input type="submit" value="Save" name="save" />
		</form>
	</div>
</div>


<?php
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
