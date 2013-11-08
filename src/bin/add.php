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

require_once('pastebin.php');

delete_expired_posts();

if(isset($_POST['paste']))
{
	//get the text
	$data = smartslashes($_POST['paste']);

    //Normalize the line endings
    $data = str_replace("\r\n", "\n", $data);
    $data = str_replace("\r", "\n", $data);

    $urlKey = commit_post(
        $data,
        isset($_POST['jscrypt']) && $_POST['jscrypt'] == "yes", 
        (isset($_POST['lifetime']) ? (int)$_POST['lifetime'] : 3600*24*10),
        isset($_POST['shorturl']) && $_POST['shorturl'] == "yes"
    );

	//redirect user to the view page
    $http_host = $_SERVER['HTTP_HOST'];
	header("Location: https://{$http_host}/b/{$urlKey}");
}
else
{
	die("Empty post!");
}

?>
