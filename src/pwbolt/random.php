<?php //generates a random 15kb keyfile.
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
require_once ('libs/security.php');


//set the headers to tell the browser to download a file.
	@ob_end_clean();
	if(ini_get('zlib.output_compression'))
  		ini_set('zlib.output_compression', 'Off');
	header('Content-Type: ' . $mime_type);
	header('Content-Disposition: attachment; filename="' . mt_rand() . ".keyfile" .'"');
	header("Content-Transfer-Encoding: binary");
	header('Accept-Ranges: bytes');
	header("Cache-control: private");
	header('Pragma: private');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Content-Length: " . (15 * 1024) ); //15KB
	
	print( security::SuperRand(480)); //get 15kb of psudorandom data and give it to the browser
	include('libs/sqlclose.php');
?>
