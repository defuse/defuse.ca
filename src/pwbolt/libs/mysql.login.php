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

//so that sqlclose.php doesn't try to close the connection when there isnt one
$PWRrjcT2CJ = "MYSQL_CONNECTED"; 

//TODO: change this information.
$username="firexware_pwbolt";
$password="4dZcaA4IITAt";
$database="firexware_pwbolt";
mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

?>
