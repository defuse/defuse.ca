<?php
/**********************************************************************************
* LogInOut.php                                                                    *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.6                                           *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file is concerned pretty entirely, as you see from its name, with
	logging in and out members, and the validation of that.  It contains:

	void Login()
		- shows a page for the user to type in their username and password.
		- caches the referring URL in $_SESSION['login_url'].
		- uses the Login template and language file with the login sub
		  template.
		- if you are using a wireless device, uses the protocol_login sub
		  template in the Wireless template.
		- accessed from ?action=login.

	void Login2()
		- actually logs you in and checks that login was successful.
		- employs protection against a specific IP or user trying to brute
		  force a login to an account.
		- on error, uses the same templates Login() uses.
		- upgrades password encryption on login, if necessary.
		- after successful login, redirects you to $_SESSION['login_url'].
		- accessed from ?action=login2, by forms.

	void Logout(bool internal = false)
		- logs the current user out of their account.
		- requires that the session hash is sent as well, to prevent automatic
		  logouts by images or javascript.
		- doesn't check the session if internal is true.
		- redirects back to $_SESSION['logout_url'], if it exists.
		- accessed via ?action=logout;sc=...

	string md5_hmac(string data, string key)
		- old style SMF 1.0.x/YaBB SE 1.5.x hashing.
		- returns the HMAC MD5 of data with key.
*/

// Ask them for their login information.
function Login()
{
	global $txt, $context;

	// In wireless?  If so, use the correct sub template.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_login';
	// Otherwise, we need to load the Login template/language file.
	else
	{
		loadLanguage('Login');
		loadTemplate('Login');
		$context['sub_template'] = 'login';
	}

	// Get the template ready.... not really much else to do.
	$context['page_title'] = $txt[34];
	$context['default_username'] = &$_REQUEST['u'];
	$context['default_password'] = '';
	$context['never_expire'] = false;

	// Set the login URL - will be used when the login process is done.
	if (isset($_SESSION['old_url']) && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
		$_SESSION['login_url'] = $_SESSION['old_url'];
	else
		unset($_SESSION['login_url']);
}

// Perform the actual logging-in.
function Login2()
{
	global $txt, $db_prefix, $scripturl, $user_info, $user_settings;
	global $cookiename, $maintenance, $ID_MEMBER, $modSettings, $context, $sc;
	global $sourcedir;

	// Load cookie authentication stuff.
	require_once($sourcedir . '/Subs-Auth.php');

	if (isset($_GET['sa']) && $_GET['sa'] == 'salt' && !$user_info['is_guest'])
	{
		if (isset($_COOKIE[$cookiename]) && preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$cookiename]) === 1)
			list (, , $timeout) = @unserialize($_COOKIE[$cookiename]);
		elseif (isset($_SESSION['login_' . $cookiename]))
			list (, , $timeout) = @unserialize(stripslashes($_SESSION['login_' . $cookiename]));
		else
			trigger_error('Login2(): Cannot be logged in without a session or cookie', E_USER_ERROR);

		$user_settings['passwordSalt'] = substr(md5(mt_rand()), 0, 4);
		updateMemberData($ID_MEMBER, array('passwordSalt' => '\'' . $user_settings['passwordSalt'] . '\''));

		setLoginCookie($timeout - time(), $ID_MEMBER, sha1($user_settings['passwd'] . $user_settings['passwordSalt']));

		redirectexit('action=login2;sa=check;member=' . $ID_MEMBER, $context['server']['needs_login_fix']);
	}
	// Double check the cookie...
	elseif (isset($_GET['sa']) && $_GET['sa'] == 'check')
	{
		// Strike!  You're outta there!
		if ($_GET['member'] != $ID_MEMBER)
			fatal_lang_error('login_cookie_error', false);

		// Some whitelisting for login_url...
		if (empty($_SESSION['login_url']))
			redirectexit();
		else
		{
			// Best not to clutter the session data too much...
			$temp = $_SESSION['login_url'];
			unset($_SESSION['login_url']);

			redirectexit($temp);
		}
	}

	// Beyond this point you are assumed to be a guest trying to login.
	if (!$user_info['is_guest'])
		redirectexit();

	// Set the login_url if it's not already set.
	if (empty($_SESSION['login_url']) && isset($_SESSION['old_url']) && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
		$_SESSION['login_url'] = $_SESSION['old_url'];

	// Are you guessing with a script that doesn't keep the session id?
	spamProtection('login');

	// Been guessing a lot, haven't we?
	if (isset($_SESSION['failed_login']) && $_SESSION['failed_login'] >= $modSettings['failed_login_threshold'] * 3)
		fatal_lang_error('login_threshold_fail');

	// Set up the cookie length.  (if it's invalid, just fall through and use the default.)
	if (isset($_POST['cookieneverexp']) || (!empty($_POST['cookielength']) && $_POST['cookielength'] == -1))
		$modSettings['cookieTime'] = 3153600;
	elseif (!empty($_POST['cookielength']) && ($_POST['cookielength'] >= 1 || $_POST['cookielength'] <= 525600))
		$modSettings['cookieTime'] = (int) $_POST['cookielength'];

	// Set things up in case an error occurs.
	if (!empty($maintenance) || empty($modSettings['allow_guestAccess']))
		$context['sub_template'] = 'kick_guest';

	loadLanguage('Login');
	// Load the template stuff - wireless or normal.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_login';
	else
	{
		loadTemplate('Login');
		$context['sub_template'] = 'login';
	}

	// Set up the default/fallback stuff.
	$context['default_username'] = isset($_REQUEST['user']) ? htmlspecialchars(stripslashes($_REQUEST['user'])) : '';
	$context['default_password'] = '';
	$context['never_expire'] = $modSettings['cookieTime'] == 525600 || $modSettings['cookieTime'] == 3153600;
	$context['login_error'] = &$txt[106];
	$context['page_title'] = $txt[34];

	// You forgot to type your username, dummy!
	if (!isset($_REQUEST['user']) || $_REQUEST['user'] == '')
	{
		$context['login_error'] = &$txt[37];
		return;
	}

	// Hmm... maybe 'admin' will login with no password. Uhh... NO!
	if ((!isset($_REQUEST['passwrd']) || $_REQUEST['passwrd'] == '') && (!isset($_REQUEST['hash_passwrd']) || strlen($_REQUEST['hash_passwrd']) != 40))
	{
		$context['login_error'] = &$txt[38];
		return;
	}

	// No funky symbols either.
	if (preg_match('~[<>&"\'=\\\]~', $_REQUEST['user']) != 0)
	{
		$context['login_error'] = &$txt[240];
		return;
	}

	// Are we using any sort of integration to validate the login?
	if (isset($modSettings['integrate_validate_login']) && function_exists($modSettings['integrate_validate_login']))
		if (call_user_func($modSettings['integrate_validate_login'], $_REQUEST['user'], isset($_REQUEST['hash_passwrd']) && strlen($_REQUEST['hash_passwrd']) == 40 ? $_REQUEST['hash_passwrd'] : null, $modSettings['cookieTime']) == 'retry')
		{
			$context['login_error'] = $txt['login_hash_error'];
			$context['disable_login_hashing'] = true;
			return;
		}

	// Load the data up!
	$request = db_query("
		SELECT passwd, ID_MEMBER, ID_GROUP, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt
		FROM {$db_prefix}members
		WHERE memberName = '$_REQUEST[user]'
		LIMIT 1", __FILE__, __LINE__);
	// Probably mistyped or their email, try it as an email address. (memberName first, though!)
	if (mysql_num_rows($request) == 0)
	{
		mysql_free_result($request);

		$request = db_query("
			SELECT passwd, ID_MEMBER, ID_GROUP, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt
			FROM {$db_prefix}members
			WHERE emailAddress = '$_REQUEST[user]'
			LIMIT 1", __FILE__, __LINE__);
		// Let them try again, it didn't match anything...
		if (mysql_num_rows($request) == 0)
		{
			$context['login_error'] = &$txt[40];
			return;
		}
	}

	$user_settings = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// What is the true activation status of this account?
	$activation_status = $user_settings['is_activated'] > 10 ? $user_settings['is_activated'] - 10 : $user_settings['is_activated'];

	// Check if the account is activated - COPPA first...
	if ($activation_status == 5)
	{
		$context['login_error'] = $txt['coppa_not_completed1'] . ' <a href="' . $scripturl . '?action=coppa;member=' . $user_settings['ID_MEMBER'] . '">' . $txt['coppa_not_completed2'] . '</a>';
		return;
	}
	// Awaiting approval still?
	elseif ($activation_status == 3)
		fatal_lang_error('still_awaiting_approval');
	// Awaiting deletion, changed their mind?
	elseif ($activation_status == 4)
	{
		// Display an error if we haven't decided to undelete.
		if (!isset($_REQUEST['undelete']))
		{
			$context['login_error'] = $txt['awaiting_delete_account'];
			$context['login_show_undelete'] = true;
			return;
		}
		// Otherwise reactivate!
		else
		{
			updateMemberData($user_settings['ID_MEMBER'], array('is_activated' => 1));
			updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > 0 ? $modSettings['unapprovedMembers'] - 1 : 0)));
		}
	}
	// Standard activation?
	elseif ($activation_status != 1)
	{
		log_error($txt['activate_not_completed1'] . ' - <span class="remove">' . $user_settings['memberName'] . '</span>', false);

		$context['login_error'] = $txt['activate_not_completed1'] . ' <a href="' . $scripturl . '?action=activate;sa=resend;u=' . $user_settings['ID_MEMBER'] . '">' . $txt['activate_not_completed2'] . '</a>';
		return;
	}

	// Figure out the password using SMF's encryption - if what they typed is right.
	if (isset($_REQUEST['hash_passwrd']) && strlen($_REQUEST['hash_passwrd']) == 40)
	{
		// Needs upgrading?
		if (strlen($user_settings['passwd']) != 40)
		{
			$context['login_error'] = $txt['login_hash_error'];
			$context['disable_login_hashing'] = true;
			return;
		}
		// Challenge passed.
		elseif ($_REQUEST['hash_passwrd'] == sha1($user_settings['passwd'] . $sc))
			$sha_passwd = $user_settings['passwd'];
		else
		{
			$_SESSION['failed_login'] = @$_SESSION['failed_login'] + 1;

			if ($_SESSION['failed_login'] >= $modSettings['failed_login_threshold'])
				redirectexit('action=reminder');
			else
			{
				log_error($txt[39] . ' - <span class="remove">' . $user_settings['memberName'] . '</span>');

				$context['disable_login_hashing'] = true;
				$context['login_error'] = $txt[39];
				return;
			}
		}
	}
	else
		$sha_passwd = sha1(strtolower($user_settings['memberName']) . un_htmlspecialchars(stripslashes($_REQUEST['passwrd'])));

	// Bad password!  Thought you could fool the database?!
	if ($user_settings['passwd'] != $sha_passwd)
	{
		// Maybe we were too hasty... let's try some other authentication methods.
		$other_passwords = array();

		// None of the below cases will be used most of the time (because the salt is normally set.)
		if ($user_settings['passwordSalt'] == '')
		{
			// YaBB SE, Discus, MD5 (used a lot), SHA-1 (used some), SMF 1.0.x, IkonBoard, and none at all.
			$other_passwords[] = crypt($_REQUEST['passwrd'], substr($_REQUEST['passwrd'], 0, 2));
			$other_passwords[] = crypt($_REQUEST['passwrd'], substr($user_settings['passwd'], 0, 2));
			$other_passwords[] = md5($_REQUEST['passwrd']);
			$other_passwords[] = sha1($_REQUEST['passwrd']);
			$other_passwords[] = md5_hmac($_REQUEST['passwrd'], strtolower($user_settings['memberName']));
			$other_passwords[] = md5($_REQUEST['passwrd'] . strtolower($user_settings['memberName']));
			$other_passwords[] = $_REQUEST['passwrd'];

			// This one is a strange one... MyPHP, crypt() on the MD5 hash.
			$other_passwords[] = crypt(md5($_REQUEST['passwrd']), md5($_REQUEST['passwrd']));

			// Snitz style - SHA-256.  Technically, this is a downgrade, but most PHP configurations don't support sha256 anyway.
			if (strlen($user_settings['passwd']) == 64 && function_exists('mhash') && defined('MHASH_SHA256'))
				$other_passwords[] = bin2hex(mhash(MHASH_SHA256, $_REQUEST['passwrd']));
		}
		// The hash should be 40 if it's SHA-1, so we're safe with more here too.
		elseif (strlen($user_settings['passwd']) == 32)
		{
			// vBulletin 3 style hashing?  Let's welcome them with open arms \o/.
			$other_passwords[] = md5(md5($_REQUEST['passwrd']) . $user_settings['passwordSalt']);
			// Hmm.. p'raps it's Invision 2 style?
			$other_passwords[] = md5(md5($user_settings['passwordSalt']) . md5($_REQUEST['passwrd']));
		}

		// Maybe they are using a hash from before the password fix.
		$other_passwords[] = sha1(strtolower($user_settings['memberName']) . addslashes(un_htmlspecialchars(stripslashes($_REQUEST['passwrd']))));

		// SMF's sha1 function can give a funny result on Linux (Not our fault!). If we've now got the real one let the old one be valid!
		require_once($sourcedir . '/Subs-Compat.php');
		$other_passwords[] = sha1_smf(strtolower($user_settings['memberName']) . un_htmlspecialchars(stripslashes($_REQUEST['passwrd'])));

		// Whichever encryption it was using, let's make it use SMF's now ;).
		if (in_array($user_settings['passwd'], $other_passwords))
		{
			$user_settings['passwd'] = $sha_passwd;
			$user_settings['passwordSalt'] = substr(md5(mt_rand()), 0, 4);

			// Update the password and set up the hash.
			updateMemberData($user_settings['ID_MEMBER'], array('passwd' => '\'' . $user_settings['passwd'] . '\'', 'passwordSalt' => '\'' . $user_settings['passwordSalt'] . '\''));
		}
		// Okay, they for sure didn't enter the password!
		else
		{
			// They've messed up again - keep a count to see if they need a hand.
			$_SESSION['failed_login'] = @$_SESSION['failed_login'] + 1;

			// Hmm... don't remember it, do you?  Here, try the password reminder ;).
			if ($_SESSION['failed_login'] >= $modSettings['failed_login_threshold'])
				redirectexit('action=reminder');
			// We'll give you another chance...
			else
			{
				// Log an error so we know that it didn't go well in the error log.
				log_error($txt[39] . ' - <span class="remove">' . $user_settings['memberName'] . '</span>');

				$context['login_error'] = $txt[39];
				return;
			}
		}
	}
	// Correct password, but they've got no salt; fix it!
	elseif ($user_settings['passwordSalt'] == '')
	{
		$user_settings['passwordSalt'] = substr(md5(mt_rand()), 0, 4);
		updateMemberData($user_settings['ID_MEMBER'], array('passwordSalt' => '\'' . $user_settings['passwordSalt'] . '\''));
	}

	if (isset($modSettings['integrate_login']) && function_exists($modSettings['integrate_login']))
		$modSettings['integrate_login']($user_settings['memberName'], isset($_REQUEST['hash_passwrd']) && strlen($_REQUEST['hash_passwrd']) == 40 ? $_REQUEST['hash_passwrd'] : null, $modSettings['cookieTime']);

	// Get ready to set the cookie...
	$username = $user_settings['memberName'];
	$ID_MEMBER = $user_settings['ID_MEMBER'];

	// Bam!  Cookie set.  A session too, just incase.
	setLoginCookie(60 * $modSettings['cookieTime'], $user_settings['ID_MEMBER'], sha1($user_settings['passwd'] . $user_settings['passwordSalt']));

	// Reset the login threshold.
	if (isset($_SESSION['failed_login']))
		unset($_SESSION['failed_login']);

	$user_info['is_guest'] = false;
	$user_settings['additionalGroups'] = explode(',', $user_settings['additionalGroups']);
	$user_info['is_admin'] = $user_settings['ID_GROUP'] == 1 || in_array(1, $user_settings['additionalGroups']);

	// Are you banned?
	is_not_banned(true);

	// An administrator, set up the login so they don't have to type it again.
	if ($user_info['is_admin'])
	{
		$_SESSION['admin_time'] = time();
		unset($_SESSION['just_registered']);
	}

	// Don't stick the language or theme after this point.
	unset($_SESSION['language']);
	unset($_SESSION['ID_THEME']);

	// You've logged in, haven't you?
	updateMemberData($ID_MEMBER, array('lastLogin' => time(), 'memberIP' => '\'' . $user_info['ip'] . '\'', 'memberIP2' => '\'' . $_SERVER['BAN_CHECK_IP'] . '\''));

	// Get rid of the online entry for that old guest....
	db_query("
		DELETE FROM {$db_prefix}log_online
		WHERE session = 'ip$user_info[ip]'
		LIMIT 1", __FILE__, __LINE__);
	$_SESSION['log_time'] = 0;

	// Just log you back out if it's in maintenance mode and you AREN'T an admin.
	if (empty($maintenance) || allowedTo('admin_forum'))
		redirectexit('action=login2;sa=check;member=' . $ID_MEMBER, $context['server']['needs_login_fix']);
	else
		redirectexit('action=logout;sesc=' . $sc, $context['server']['needs_login_fix']);
}

// Log the user out.
function Logout($internal = false)
{
	global $db_prefix, $sourcedir, $ID_MEMBER, $user_info, $user_settings, $context, $modSettings;

	// Make sure they aren't being auto-logged out.
	if (!$internal)
		checkSession('get');

	require_once($sourcedir . '/Subs-Auth.php');

	if (isset($_SESSION['pack_ftp']))
		$_SESSION['pack_ftp'] = null;

	// Just ensure they aren't a guest!
	if (!$user_info['is_guest'])
	{
		if (isset($modSettings['integrate_logout']) && function_exists($modSettings['integrate_logout']))
			call_user_func($modSettings['integrate_logout'], $user_settings['memberName']);
	
		// If you log out, you aren't online anymore :P.
		db_query("
			DELETE FROM {$db_prefix}log_online
			WHERE ID_MEMBER = $ID_MEMBER
			LIMIT 1", __FILE__, __LINE__);
	}

	$_SESSION['log_time'] = 0;

	// Empty the cookie! (set it in the past, and for ID_MEMBER = 0)
	setLoginCookie(-3600, 0);

	// Off to the merry board index we go!
	if (empty($_SESSION['logout_url']))
		redirectexit('', $context['server']['needs_login_fix']);
	else
	{
		$temp = $_SESSION['logout_url'];
		unset($_SESSION['logout_url']);

		redirectexit($temp, $context['server']['needs_login_fix']);
	}
}

// MD5 Encryption used for older passwords.
function md5_hmac($data, $key)
{
	$key = str_pad(strlen($key) <= 64 ? $key : pack('H*', md5($key)), 64, chr(0x00));
	return md5(($key ^ str_repeat(chr(0x5c), 64)) . pack('H*', md5(($key ^ str_repeat(chr(0x36), 64)) . $data)));
}

?>