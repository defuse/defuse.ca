<?php
/**********************************************************************************
* Subs-Auth.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.11                                          *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

/*	This file has functions in it to do with authentication, user handling,
	and the like.  It provides these functions:

	void setLoginCookie(int cookie_length, int ID_MEMBER, string password = '')
		- sets the SMF-style login cookie and session based on the ID_MEMBER
		  and password passed.
		- password should be already encrypted with the cookie salt.
		- logs the user out if ID_MEMBER is zero.
		- sets the cookie and session to last the number of seconds specified
		  by cookie_length.
		- when logging out, if the globalCookies setting is enabled, attempts
		  to clear the subdomain's cookie too.

	array url_parts(bool local, bool global)
		- returns the path and domain to set the cookie on.
		- normally, local and global should be the localCookies and
		  globalCookies settings, respectively.
		- uses boardurl to determine these two things.
		- returns an array with domain and path in it, in that order.

	void KickGuest()
		- throws guests out to the login screen when guest access is off.
		- sets $_SESSION['login_url'] to $_SERVER['REQUEST_URL'].
		- uses the 'kick_guest' sub template found in Login.template.php.

	void InMaintenance()
		- display a message about being in maintenance mode.
		- display a login screen with sub template 'maintenance'.

	void adminLogin()
		- double check the verity of the admin by asking for his or her
		  password.
		- loads Login.template.php and uses the admin_login sub template.
		- sends data to template so the admin is sent on to the page they
		  wanted if their password is correct, otherwise they can try
		  again.

	string adminLogin_outputPostVars(string key, string value)
		- used by the adminLogin() function.
		- returns 'hidden' HTML form fields, containing key-value-pairs.
		- if 'value' is an array, the function is called recursively.

	void show_db_error(bool loadavg = false)
		- called by db_fatal_error() function (in Errors.php.)
		- shows a complete page independent of language files or themes.
		- used only if there's no way to connect to the database or the
		  load averages are too high to do so.
		- loadavg means this is a load average problem, not a database error.
		- stops further execution of the script.

	array findMembers(array names, bool use_wildcards = false,
			bool buddies_only = false, int max = none)
		- searches for members whose username, display name, or e-mail address 
		  match the given pattern of array names.
		- accepts wildcards ? and * in the patern if use_wildcards is set.
		- retrieves a maximum of max members, if passed.
		- searches only buddies if buddies_only is set.
		- returns an array containing information about the matching members.

	void JSMembers()
		- called by index.php?action=findmember.
		- is used as a popup for searching members.
		- uses sub template find_members of the Help template.
		- also used to add members for PM's sent using wap2/imode protocol.

	void RequestMembers()
		- used by javascript to find members matching the request.
		- outputs each member name on its own line.

	void resetPassword(int ID_MEMBER, string username = null)
		- called by Profile.php when changing someone's username.
		- checks the validity of the new username.
		- generates and sets a new password for the given user.
		- mails the new password to the email address of the user.
		- if username is not set, only a new password is generated and sent.

	string validatePassword(string password, string username,
			array restrict_in = none)
		- called when registering/choosing a password.
		- checks the password obeys the current forum settings for password
		  strength.
		- if password checking is enabled, will check that none of the words
		  in redstrict_in appear in the password.
		- returns an error identifier if the password is invalid, or null.
*/

// Actually set the login cookie...
function setLoginCookie($cookie_length, $id, $password = '')
{
	global $cookiename, $boardurl, $modSettings;

	// The cookie may already exist, and have been set with different options.
	$cookie_state = (empty($modSettings['localCookies']) ? 0 : 1) | (empty($modSettings['globalCookies']) ? 0 : 2);
	if (isset($_COOKIE[$cookiename]) && preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$cookiename]) === 1)
	{
		$array = @unserialize($_COOKIE[$cookiename]);

		// Out with the old, in with the new!
		if (isset($array[3]) && $array[3] != $cookie_state)
		{
			$cookie_url = url_parts($array[3] & 1 > 0, $array[3] & 2 > 0);
			setcookie($cookiename, serialize(array(0, '', 0)), time() - 3600, $cookie_url[1], $cookie_url[0], 0);
		}
	}

	// Get the data and path to set it on.
	$data = serialize(empty($id) ? array(0, '', 0) : array($id, $password, time() + $cookie_length, $cookie_state));
	$cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));

	// Set the cookie, $_COOKIE, and session variable.
	setcookie($cookiename, $data, time() + $cookie_length, $cookie_url[1], $cookie_url[0], 0);

	// If subdomain-independent cookies are on, unset the subdomain-dependent cookie too.
	if (empty($id) && !empty($modSettings['globalCookies']))
		setcookie($cookiename, $data, time() + $cookie_length, $cookie_url[1], '', 0);

	// Any alias URLs?  This is mainly for use with frames, etc.
	if (!empty($modSettings['forum_alias_urls']))
	{
		$aliases = explode(',', $modSettings['forum_alias_urls']);

		$temp = $boardurl;
		foreach ($aliases as $alias)
		{
			// Fake the $boardurl so we can set a different cookie.
			$alias = strtr(trim($alias), array('http://' => '', 'https://' => ''));
			$boardurl = 'http://' . $alias;

			$cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));

			if ($cookie_url[0] == '')
				$cookie_url[0] = strtok($alias, '/');

			setcookie($cookiename, $data, time() + $cookie_length, $cookie_url[1], $cookie_url[0], 0);
		}

		$boardurl = $temp;
	}

	$_COOKIE[$cookiename] = $data;

	// Make sure the user logs in with a new session ID.
	if (!isset($_SESSION['login_' . $cookiename]) || $_SESSION['login_' . $cookiename] !== $data)
	{
		// Backup and remove the old session.
		$oldSessionData = $_SESSION;
		$_SESSION = array();
		session_destroy();

		// Recreate and restore the new session.
		loadSession();
		session_regenerate_id();
		$_SESSION = $oldSessionData;

		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0 || (isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] != session_id()))
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', 0);

		$_SESSION['login_' . $cookiename] = $data;
	}
}

// PHP < 4.3.2 doesn't have this function
if (!function_exists('session_regenerate_id'))
{
	function session_regenerate_id()
	{
		// Too late to change the session now.
		if (headers_sent())
			return false;

		session_id(strtolower(md5(uniqid(mt_rand(), true))));
		return true;
	}
}

// Get the domain and path for the cookie...
function url_parts($local, $global)
{
	global $boardurl;

	// Parse the URL with PHP to make life easier.
	$parsed_url = parse_url($boardurl);

	// Is local cookies off?
	if (empty($parsed_url['path']) || !$local)
		$parsed_url['path'] = '';

	// Globalize cookies across domains (filter out IP-addresses)?
	if ($global && preg_match('~^\d{1,3}(\.\d{1,3}){3}$~', $parsed_url['host']) == 0 && preg_match('~(?:[^\.]+\.)?([^\.]{2,}\..+)\z~i', $parsed_url['host'], $parts) == 1)
			$parsed_url['host'] = '.' . $parts[1];

	// We shouldn't use a host at all if both options are off.
	elseif (!$local && !$global)
		$parsed_url['host'] = '';
		
	// The host also shouldn't be set if there aren't any dots in it.
	elseif (!isset($parsed_url['host']) || strpos($parsed_url['host'], '.') === false)
		$parsed_url['host'] = '';
	
	return array($parsed_url['host'], $parsed_url['path'] . '/');
}

// Kick out a guest when guest access is off...
function KickGuest()
{
	global $txt, $context;

	loadLanguage('Login');
	loadTemplate('Login');

	$_SESSION['login_url'] = $_SERVER['REQUEST_URL'];

	$context['sub_template'] = 'kick_guest';
	$context['page_title'] = $txt[34];
}

// Display a message about the forum being in maintenance mode, etc.
function InMaintenance()
{
	global $txt, $mtitle, $mmessage, $context;

	loadLanguage('Login');
	loadTemplate('Login');

	// Basic template stuff..
	$context['sub_template'] = 'maintenance';
	$context['title'] = &$mtitle;
	$context['description'] = &$mmessage;
	$context['page_title'] = &$txt[155];
}

function adminLogin()
{
	global $context, $scripturl, $txt;

	loadLanguage('Admin');
	loadTemplate('Login');

	// Start with nothing for get data and post data.
	$context['get_data'] = '?';
	$context['post_data'] = '';

	// Awww, darn.  The $scripturl contains GET stuff!
	$q = strpos($scripturl, '?');
	if ($q !== false)
	{
		parse_str(preg_replace('/&(\w+)(?=&|$)/', '&$1=', strtr(substr($scripturl, $q + 1), ';', '&')), $temp);

		foreach ($_GET as $k => $v)
		{
			// Only if it's not already in the $scripturl!
			if (!isset($temp[$k]))
				$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . ';';
			// If it changed, put it out there, but with an ampersand.
			elseif ($temp[$k] != $_GET[$k])
				$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . '&amp;';
		}
	}
	else
	{
		// Add up all the data from $_GET into get_data.
		foreach ($_GET as $k => $v)
			$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . ';';
	}

	$context['get_data'] = substr($context['get_data'], 0, -1);

	// They used a wrong password, log it and unset that.
	if (isset($_POST['admin_hash_pass']) || isset($_POST['admin_pass']))
	{
		log_error($txt['security_wrong']);

		if (isset($_POST['admin_hash_pass']))
			unset($_POST['admin_hash_pass']);
		if (isset($_POST['admin_pass']))
			unset($_POST['admin_pass']);
	}

	// Now go through $_POST.  Make sure the session hash is sent.
	$_POST['sc'] = $context['session_id'];
	foreach ($_POST as $k => $v)
		$context['post_data'] .= adminLogin_outputPostVars($k, $v);

	// Now we'll use the admin_login sub template of the Login template.
	$context['sub_template'] = 'admin_login';

	// And title the page something like "Login".
	if (!isset($context['page_title']))
		$context['page_title'] = $txt[34];

	obExit();

	// We MUST exit at this point, because otherwise we CANNOT KNOW that the user is privileged.
	trigger_error('Hacking attempt...', E_USER_ERROR);
}

function adminLogin_outputPostVars($k, $v)
{
	if (!is_array($v))
		return '
<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . strtr(stripslashes($v), array('"' => '&quot;', '<' => '&lt;', '>' => '&gt;')) . '" />';
	else
	{
		$ret = '';
		foreach ($v as $k2 => $v2)
			$ret .= adminLogin_outputPostVars($k . '[' . $k2 . ']', $v2);

		return $ret;
	}
}

// Show an error message for the connection problems.
function show_db_error($loadavg = false)
{
	global $sourcedir, $mbname, $maintenance, $mtitle, $mmessage, $modSettings;
	global $db_connection, $webmaster_email, $db_last_error, $db_error_send;

	// Don't cache this page!
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache');

	if ($loadavg == false)
	{
		// For our purposes, we're gonna want this on if at all possible.
		$modSettings['cache_enable'] = '1';
		if (($temp = cache_get_data('db_last_error', 600)) !== null)
			$db_last_error = max($db_last_error, $temp);

		if ($db_last_error < time() - 3600 * 24 * 3 && empty($maintenance) && !empty($db_error_send))
		{
			require_once($sourcedir . '/Admin.php');

			// Avoid writing to the Settings.php file if at all possible; use shared memory instead.
			cache_put_data('db_last_error', time(), 600);
			if (($temp = cache_get_data('db_last_error', 600)) === null)
				updateSettingsFile(array('db_last_error' => time()));

			// Language files aren't loaded yet :(.
			$mysql_error = @mysql_error($db_connection);
			@mail($webmaster_email, $mbname . ': SMF Database Error!', 'There has been a problem with the database!' . ($mysql_error == '' ? '' : "\nMySQL reported:\n" . $mysql_error) . "\n\nThis is a notice email to let you know that SMF could not connect to the database, contact your host if this continues.");
		}
	}

	if (!empty($maintenance))
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>', $mtitle, '</title>
	</head>
	<body>
		<h3>', $mtitle, '</h3>
		', $mmessage, '
	</body>
</html>';
	// If this is a load average problem, display an appropriate message (but we still don't have language files!)
	elseif ($loadavg)
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Temporarily Unavailable</title>
	</head>
	<body>
		<h3>Temporarily Unavailable</h3>
		Due to high stress on the server the forum is temporarily unavailable.  Please try again later.
	</body>
</html>';
	// What to do?  Language files haven't and can't be loaded yet...
	else
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Connection Problems</title>
	</head>
	<body>
		<h3>Connection Problems</h3>
		Sorry, SMF was unable to connect to the database.  This may be caused by the server being busy.  Please try again later.
	</body>
</html>';

	die;
}

// Find members by email address, username, or real name.
function findMembers($names, $use_wildcards = false, $buddies_only = false, $max = null)
{
	global $db_prefix, $scripturl, $user_info, $modSettings, $func;

	// If it's not already an array, make it one.
	if (!is_array($names))
		$names = explode(',', $names);

	$maybe_email = false;
	foreach ($names as $i => $name)
	{
		// Add slashes, trim, and fix wildcards for each name.
		$names[$i] = addslashes(trim($func['strtolower']($name)));

		$maybe_email |= strpos($name, '@') !== false;

		// Make it so standard wildcards will work. (* and ?)
		if ($use_wildcards)
			$names[$i] = strtr($names[$i], array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '\\\'' => '&#039;'));
		else
			$names[$i] = strtr($names[$i], array('\\\'' => '&#039;'));
	}

	// What are we using to compare?
	$comparison = $use_wildcards ? 'LIKE' : '=';

	// Nothing found yet.
	$results = array();

	// This ensures you can't search someones email address if you can't see it.
	$email_condition = $user_info['is_admin'] || empty($modSettings['allow_hideEmail']) ? '' : 'hideEmail = 0 AND ';

	if ($use_wildcards || $maybe_email)
		$email_condition = "
			OR (" . $email_condition . "emailAddress $comparison '" . implode("') OR ($email_condition emailAddress $comparison '", $names) . "')";
	else
		$email_condition = '';

	// Search by username, display name, and email address.
	$request = db_query("
		SELECT ID_MEMBER, memberName, realName, emailAddress, hideEmail
		FROM {$db_prefix}members
		WHERE (memberName $comparison '" . implode("' OR memberName $comparison '", $names) . "'
			OR realName $comparison '" . implode("' OR realName $comparison '", $names) . "'$email_condition)
			" . ($buddies_only ? 'AND ID_MEMBER IN (' . implode(', ', $user_info['buddies']) . ')' : '') . "
			AND is_activated IN (1, 11)" . ($max == null ? '' : "
		LIMIT " . (int) $max), __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		$results[$row['ID_MEMBER']] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'username' => $row['memberName'],
			'email' => empty($row['hideEmail']) || empty($modSettings['allow_hideEmail']) || $user_info['is_admin'] ? $row['emailAddress'] : '',
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>'
		);
	}
	mysql_free_result($request);

	// Return all the results.
	return $results;
}

function JSMembers()
{
	global $context, $scripturl, $user_info, $func;

	checkSession('get');

	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_pm';
	else
	{
		// Why is this in the Help template, you ask?  Well, erm... it helps you.  Does that work?
		loadTemplate('Help');

		$context['template_layers'] = array();
		$context['sub_template'] = 'find_members';
	}

	if (isset($_REQUEST['search']))
		$context['last_search'] = $func['htmlspecialchars'](stripslashes($_REQUEST['search']), ENT_QUOTES);
	else
		$_REQUEST['start'] = 0;

	// Allow the user to pass the input to be added to to the box.
	$context['input_box_name'] = isset($_REQUEST['input']) && preg_match('~^[\w-]+$~', $_REQUEST['input']) === 1 ? $_REQUEST['input'] : 'to';

	// Take the delimiter over GET in case it's \n or something.
	$context['delimiter'] = isset($_REQUEST['delim']) ? $func['htmlspecialchars'](stripslashes($_REQUEST['delim'])) : ', ';
	$context['quote_results'] = !empty($_REQUEST['quote']);

	// List all the results.
	$context['results'] = array();

	// Some buddy related settings ;)
	$context['show_buddies'] = !empty($user_info['buddies']);
	$context['buddy_search'] = isset($_REQUEST['buddies']);

	// If the user has done a search, well - search.
	if (isset($_REQUEST['search']))
	{
		$_REQUEST['search'] = $func['htmlspecialchars'](stripslashes($_REQUEST['search']), ENT_QUOTES);

		$context['results'] = findMembers(array($_REQUEST['search']), true, $context['buddy_search']);
		$total_results = count($context['results']);

		$context['page_index'] = constructPageIndex($scripturl . '?action=findmember;search=' . $context['last_search'] . ';sesc=' . $context['session_id'] . ';input=' . $context['input_box_name'] . ($context['quote_results'] ? ';quote=1' : '') . ($context['buddy_search'] ? ';buddies' : ''), $_REQUEST['start'], $total_results, 7);

		// Determine the navigation context (especially useful for the wireless template).
		$base_url = $scripturl . '?action=findmember;search=' . urlencode($context['last_search']) . (empty($_REQUEST['u']) ? '' : ';u=' . $_REQUEST['u']) . ';sesc=' . $context['session_id'];
		$context['links'] = array(
			'first' => $_REQUEST['start'] >= 7 ? $base_url . ';start=0' : '',
			'prev' => $_REQUEST['start'] >= 7 ? $base_url . ';start=' . ($_REQUEST['start'] - 7) : '',
			'next' => $_REQUEST['start'] + 7 < $total_results ? $base_url . ';start=' . ($_REQUEST['start'] + 7) : '',
			'last' => $_REQUEST['start'] + 7 < $total_results ? $base_url . ';start=' . (floor(($total_results - 1) / 7) * 7) : '',
			'up' => $scripturl . '?action=pm;sa=send' . (empty($_REQUEST['u']) ? '' : ';u=' . $_REQUEST['u']),
		);
		$context['page_info'] = array(
			'current_page' => $_REQUEST['start'] / 7 + 1,
			'num_pages' => floor(($total_results - 1) / 7) + 1
		);

		$context['results'] = array_slice($context['results'], $_REQUEST['start'], 7);
	}
	else
		$context['links']['up'] = $scripturl . '?action=pm;sa=send' . (empty($_REQUEST['u']) ? '' : ';u=' . $_REQUEST['u']);
}

function RequestMembers()
{
	global $user_info, $db_prefix, $txt, $func;

	checkSession('get');

	$_REQUEST['search'] = $func['htmlspecialchars'](stripslashes($_REQUEST['search'])) . '*';
	$_REQUEST['search'] = addslashes(trim($func['strtolower']($_REQUEST['search'])));
	$_REQUEST['search'] = strtr($_REQUEST['search'], array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));

	if (function_exists('iconv'))
		header('Content-Type: text/plain; charset=UTF-8');

	$request = db_query("
		SELECT realName
		FROM {$db_prefix}members
		WHERE realName LIKE '$_REQUEST[search]'" . (isset($_REQUEST['buddies']) ? '
			AND ID_MEMBER IN (' . implode(', ', $user_info['buddies']) . ')' : '') . "
			AND is_activated IN (1, 11)
		LIMIT " . (strlen($_REQUEST['search']) <= 2 ? '100' : '800'), __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		if (function_exists('iconv'))
		{
			$utf8 = iconv($txt['lang_character_set'], 'UTF-8', $row['realName']);
			if ($utf8)
				$row['realName'] = $utf8;
		}

		$row['realName'] = strtr($row['realName'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

		if (preg_match('~&#\d+;~', $row['realName']) != 0)
		{
			$fixchar = create_function('$n', '
				if ($n < 128)
					return chr($n);
				elseif ($n < 2048)
					return chr(192 | $n >> 6) . chr(128 | $n & 63);
				elseif ($n < 65536)
					return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				else
					return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

			$row['realName'] = preg_replace('~&#(\d+);~e', '$fixchar(\'$1\')', $row['realName']);
		}

		echo $row['realName'], "\n";
	}
	mysql_free_result($request);

	obExit(false);
}

// This function generates a random password for a user and emails it to them.
function resetPassword($memID, $username = null)
{
	global $db_prefix, $scripturl, $context, $txt, $sourcedir, $modSettings;

	// Language... and a required file.
	loadLanguage('Login');
	require_once($sourcedir . '/Subs-Post.php');

	// Get some important details.
	$request = db_query("
		SELECT memberName, emailAddress
		FROM {$db_prefix}members
		WHERE ID_MEMBER = $memID", __FILE__, __LINE__);
	list ($user, $email) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($username !== null)
	{
		$old_user = $user;
		$user = trim($username);
	}

	// Generate a random password.
	require_once($sourcedir . '/Subs-Members.php');
	$newPassword = generateValidationCode();
	$newPassword_sha1 = sha1(strtolower($user) . $newPassword);

	// Do some checks on the username if needed.
	if ($username !== null)
	{
		// No name?!  How can you register with no name?
		if ($user == '')
			fatal_lang_error(37, false);

		// Only these characters are permitted.
		if (in_array($user, array('_', '|')) || preg_match('~[<>&"\'=\\\]~', $user) != 0 || strpos($user, '[code') !== false || strpos($user, '[/code') !== false)
			fatal_lang_error(240, false);

		if (stristr($user, $txt[28]) !== false)
			fatal_lang_error(244, true, array($txt[28]));

		require_once($sourcedir . '/Subs-Members.php');
		if (isReservedName($user, $memID, false))
			fatal_error('(' . htmlspecialchars($user) . ') ' . $txt[473], false);

		// Update the database...
		updateMemberData($memID, array('memberName' => '\'' . $user . '\'', 'passwd' => '\'' . $newPassword_sha1 . '\''));
	}
	else
		updateMemberData($memID, array('passwd' => '\'' . $newPassword_sha1 . '\''));

	if (isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
		call_user_func($modSettings['integrate_reset_pass'], $old_user, $user, $newPassword);

	// Send them the email informing them of the change - then we're done!
	sendmail($email, $txt['change_password'],
		"$txt[hello_member] $user!\n\n" .
		"$txt[change_password_1] $context[forum_name] $txt[change_password_2]\n\n" .
		"$txt[719]$user, $txt[492] $newPassword\n\n" .
		"$txt[701]\n" .
		"$scripturl?action=profile\n\n" .
		$txt[130]);
}

// This function simply checks whether a password meets the current forum rules.
function validatePassword($password, $username, $restrict_in = array())
{
	global $modSettings, $func;

	// Perform basic requirements first.
	if (strlen($password) < (empty($modSettings['password_strength']) ? 4 : 8))
		return 'short';

	// Is this enough?
	if (empty($modSettings['password_strength']))
		return null;

	// Otherwise, perform the medium strength test - checking if password appears in the restricted string.
	if (preg_match('~\b' . preg_quote($password, '~') . '\b~', implode(' ', $restrict_in)) != 0)
		return 'restricted_words';
	elseif ($func['strpos']($password, $username) !== false)
		return 'restricted_words';

	// !!! If pspell is available, use it on the word, and return restricted_words if it doesn't give "bad spelling"?

	// If just medium, we're done.
	if ($modSettings['password_strength'] == 1)
		return null;

	// Otherwise, hard test next, check for numbers and letters, uppercase too.
	$good = preg_match('~(\D\d|\d\D)~', $password) != 0;
	$good &= $func['strtolower']($password) != $password;

	return $good ? null : 'chars';
}

?>