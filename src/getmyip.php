<?php
/*
 * Defuse.ca
 * Copyright (C) 2013  Taylor Hornby
 * 
 * This file is part of Defuse.
 * 
 * Defuse is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * Defuse is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
<html>
<head>
<style>
body{
	color: white;
	background-color:black;
}
</style>
</head>
<body>
<center>
<?php
$ip = $_SERVER['REMOTE_ADDR'];
echo "IP address: <br />" . $ip;
echo "<br /><br />Hostname: <br />" . GetHostByAddr($ip);
echo "<br /><br />" . $_SERVER['HTTP_USER_AGENT'];
?>
</center>
</body>
</html>
