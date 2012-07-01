<?php
/*
 * This file is part of Defuse Cyber-Security's Secure Pastebin
 * Find updates at: https://defuse.ca/pastebin.htm
 * Developer contact: havoc AT defuse.ca
 * This code is in the public domain. There is no warranty.
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
        isset($_POST['shorturl']) && $_POST['shorturl'] == "yes"
    );

	//redirect user to the view page
	header("Location: https://bin.defuse.ca/$urlKey");
}
else
{
	die("Empty post!");
}

?>
