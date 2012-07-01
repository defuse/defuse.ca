<?php

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
