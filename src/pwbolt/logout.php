<?php //redirect to this page to log the user out. It will redirect to index.php
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
	require_once('libs/security.php');
	require_once('libs/passwordbolt.php');
	$user = PB::CheckLogin();
	PB::Logout($user);
	include ('libs/sqlclose.php');
?>
