<?php
define("WEBROOT", "http://ossbox.com/r&d/2/");

define("ERROR_PASS_NOT_MATCH", "Passwords did not match.");
define("ERROR_NO_PASSWORD", "Please enter a password.");
define("ERROR_NO_USERNAME", "Please enter a username");
define("ERROR_NONALPHA_USERNAME", "Please use only letters in your username");
define("ERROR_EXISTING_USER", "That username is already taken, please try another.");
define("ERROR_CREATING_ACCOUNT", "There was an error creating your account. This is our fault, please try again later.");
define("ERROR_INVALID_LOGIN", "Invalid username and/or password.");

//We use this function instead of constant() so we can use $_GET values directly as names
function SafeConstant($name)
{
	switch($name)
	{
		case "ERROR_PASS_NOT_MATCH":
			return ERROR_PASS_NOT_MATCH;
		case "ERROR_NO_PASSWORD":
			return ERROR_NO_PASSWORD;
		case "ERROR_NO_USERNAME":
			return ERROR_NO_USERNAME;
		case "ERROR_NONALPHA_USERNAME":
			return ERROR_NONALPHA_USERNAME;
		case "ERROR_EXISTING_USER":
			return ERROR_EXISTING_USER;
		case "ERROR_CREATING_ACCOUNT":
			return ERROR_CREATING_ACCOUNT;
		default:
			return "Error.";
	}
}

?>
