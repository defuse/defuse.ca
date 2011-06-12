<?php
/**********************************************************************************
* Load.php                                                                        *
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

/*	This file has the hefty job of loading information for the forum.  It uses
	the following functions:

	void reloadSettings()
		- loads or reloads the $modSettings array.
		- takes care of mysql_set_mode, if set.
		- loads any integration settings, SMF_INTEGRATION_SETTINGS, etc.
		- optimizes tables based on autoOptDatabase, autoOptLastOpt, and
		  autoOptMaxOnline.

	void loadUserSettings()
        - sets up the $user_info array
        - assigns $user_info['query_see_board'] for what boards the user can see.
        - first checks for cookie or intergration validation.
        - uses the current session if no integration function or cookie is found.
        - checks password length, if member is activated and the login span isn't over.
        - if validation fails for the user, $ID_MEMBER is set to 0.
        - updates the last visit time when needed.

    void loadBoard()
        - sets up the $board_info array for current board information.
        - if cache is enabled, the $board_info array is stored in cache.	
        - is only used when inside a topic or board.
        - determines the local moderators for the board.
        - adds group id 3 if the user is a local moderator for the board they are in.
        - prevents access if user is not in proper group nor a local moderator of the board.

	void loadPermissions()
		// !!!

	array loadMemberData(array members, bool is_name = false, string set = 'normal')
		// !!!

	bool loadMemberContext(int ID_MEMBER)
		// !!!

	void loadTheme(int ID_THEME = auto_detect)
		// !!!

	void loadTemplate(string template_name, bool fatal = true)
		- loads a template file with the name template_name from the current,
		  default, or base theme.
		- uses the template_include() function to include the file.
		- detects a wrong default theme directory and tries to work around it.
		- if fatal is true, dies with an error message if the template cannot
		  be found.

	void loadSubTemplate(string sub_template_name, bool fatal = false)
		- loads the sub template specified by sub_template_name, which must be
		  in an already-loaded template.
		- if ?debug is in the query string, shows administrators a marker after
		  every sub template for debugging purposes.

	string loadLanguage(string template_name, string language = default, bool fatal = true)
		// !!!

	array getBoardParents(int ID_PARENT)
		- finds all the parents of ID_PARENT, and that board itself.
		- additionally detects the moderators of said boards.
		- returns an array of information about the boards found.

	string &censorText(string &text)
		- censors the passed string.
		- if the theme setting allow_no_censored is on, and the theme option
		  show_no_censored is enabled, does not censor.
		- caches the list of censored words to reduce parsing.

	void loadJumpTo()
		// !!!

	void template_include(string filename, bool only_once = false)
		- loads the template or language file specified by filename.
		- if once is true, only includes the file once (like include_once.)
		- uses eval unless disableTemplateEval is enabled.
		- outputs a parse error if the file did not exist or contained errors.
		- attempts to detect the error and line, and show detailed information.

	void loadSession()
		// !!!

	bool sessionOpen(string session_save_path, string session_name)
	bool sessionClose()
	bool sessionRead(string session_id)
	bool sessionWrite(string session_id, string data)
	bool sessionDestroy(string session_id)
	bool sessionGC(int max_lifetime)
		- implementations of PHP's session API.
		- handle the session data in the database (more scalable.)
		- use the databaseSession_lifetime setting for garbage collection.
		- set by loadSession().

	void cache_put_data(string key, mixed value, int ttl = 120)
		- puts value in the cache under key for ttl seconds.
		- may "miss" so shouldn't be depended on, and may go to any of many
		  various caching servers.
		- supports eAccelerator, Turck MMCache, ZPS, and memcached.

	mixed cache_get_data(string key, int ttl = 120)
		- gets the value from the cache specified by key, so long as it is not
		  older than ttl seconds.
		- may often "miss", so shouldn't be depended on.
		- supports the same as cache_put_data().

	void get_memcached_server(int recursion_level = 3)
		- used by cache_get_data() and cache_put_data().
		- attempts to connect to a random server in the cache_memcached
		  setting.
		- recursively calls itself up to recursion_level times.
*/

// Load the $modSettings array.
function reloadSettings()
{
	global $modSettings, $db_prefix, $boarddir, $func, $txt, $db_character_set;
	global $mysql_set_mode, $context;

	// This makes it possible to have SMF automatically change the sql_mode and autocommit if needed.
	if (isset($mysql_set_mode) && $mysql_set_mode === true)
		db_query("SET sql_mode='', AUTOCOMMIT=1", false, false);

	// Most database systems have not set UTF-8 as their default input charset.
	if (isset($db_character_set) && preg_match('~^\w+$~', $db_character_set) === 1)
		db_query("
			SET NAMES $db_character_set", __FILE__, __LINE__);

	// Try to load it from the cache first; it'll never get cached if the setting is off.
	if (($modSettings = cache_get_data('modSettings', 90)) == null)
	{
		$request = db_query("
			SELECT variable, value
			FROM {$db_prefix}settings", false, false);
		$modSettings = array();
		if (!$request)
			db_fatal_error();
		while ($row = mysql_fetch_row($request))
			$modSettings[$row[0]] = $row[1];
		mysql_free_result($request);


		// Do a few things to protect against missing settings or settings with invalid values...
		if (empty($modSettings['defaultMaxTopics']) || $modSettings['defaultMaxTopics'] <= 0 || $modSettings['defaultMaxTopics'] > 999)
			$modSettings['defaultMaxTopics'] = 20;
		if (empty($modSettings['defaultMaxMessages']) || $modSettings['defaultMaxMessages'] <= 0 || $modSettings['defaultMaxMessages'] > 999)
			$modSettings['defaultMaxMessages'] = 15;
		if (empty($modSettings['defaultMaxMembers']) || $modSettings['defaultMaxMembers'] <= 0 || $modSettings['defaultMaxMembers'] > 999)
			$modSettings['defaultMaxMembers'] = 30;

		if (!empty($modSettings['cache_enable']))
			cache_put_data('modSettings', $modSettings, 90);
	}

	// UTF-8 in regular expressions is unsupported on PHP(win) versions < 4.2.3.
	$utf8 = (empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8' && (strpos(strtolower(PHP_OS), 'win') === false || @version_compare(PHP_VERSION, '4.2.3') != -1);

	// Set a list of common functions.
	$ent_list = empty($modSettings['disableEntityCheck']) ? '&(#\d{1,7}|quot|amp|lt|gt|nbsp);' : '&(#021|quot|amp|lt|gt|nbsp);';
	$ent_check = empty($modSettings['disableEntityCheck']) ? array('preg_replace(\'~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e\', \'$func[\\\'entity_fix\\\'](\\\'\\2\\\')\', ', ')') : array('', '');

	// Preg_replace can handle complex characters only for higher PHP versions.
	$space_chars = $utf8 ? (@version_compare(PHP_VERSION, '4.3.3') != -1 ? '\x{A0}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}' : pack('C*', 0xC2, 0xA0, 0xE2, 0x80, 0x80) . '-' . pack('C*', 0xE2, 0x80, 0x8F, 0xE2, 0x80, 0x9F, 0xE2, 0x80, 0xAF, 0xE2, 0x80, 0x9F, 0xE3, 0x80, 0x80, 0xEF, 0xBB, 0xBF)) : '\xA0';
	
	$func = array(
		'entity_fix' => create_function('$string', '
			$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;
			return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : \'&#\' . $num . \';\';'),
		'substr' => create_function('$string, $start, $length = null', '
			global $func;
			$ent_arr = preg_split(\'~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : '') . '\', ' . implode('$string', $ent_check) . ', -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			return $length === null ? implode(\'\', array_slice($ent_arr, $start)) : implode(\'\', array_slice($ent_arr, $start, $length));'),
		'strlen' => create_function('$string', '
			global $func;
			return strlen(preg_replace(\'~' . $ent_list . ($utf8 ? '|.~u' : '~') . '\', \'_\', ' . implode('$string', $ent_check) . '));'),
		'strpos' => create_function('$haystack, $needle, $offset = 0', '
			global $func;
			$haystack_arr = preg_split(\'~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : '') . '\', ' . implode('$haystack', $ent_check) . ', -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$haystack_size = count($haystack_arr);
			if (strlen($needle) === 1)
			{
				$result = array_search($needle, array_slice($haystack_arr, $offset));
				return is_int($result) ? $result + $offset : false;
			}
			else
			{
				$needle_arr = preg_split(\'~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : '') . '\',  ' . implode('$needle', $ent_check) . ', -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				$needle_size = count($needle_arr);

				$result = array_search($needle_arr[0], array_slice($haystack_arr, $offset));
				while (is_int($result))
				{
					$offset += $result;
					if (array_slice($haystack_arr, $offset, $needle_size) === $needle_arr)
						return $offset;
					$result = array_search($needle_arr[0], array_slice($haystack_arr, ++$offset));
				}
				return false;
			}'),
		'htmlspecialchars' => create_function('$string, $quote_style = ENT_COMPAT, $charset = \'ISO-8859-1\'', '
			global $func;
			return ' . strtr($ent_check[0], array('&' => '&amp;'))  . 'htmlspecialchars($string, $quote_style, ' . ($utf8 ? '\'UTF-8\'' : '$charset') . ')' . $ent_check[1] . ';'),
		'htmltrim' => create_function('$string', '
			global $func;
			return preg_replace(\'~^([ \t\n\r\x0B\x00' . $space_chars . ']|&nbsp;)+|([ \t\n\r\x0B\x00' . $space_chars . ']|&nbsp;)+$~' . ($utf8 ? 'u' : '') . '\', \'\', ' . implode('$string', $ent_check) . ');'),
		'truncate' => create_function('$string, $length', (empty($modSettings['disableEntityCheck']) ? '
			global $func;
			$string = ' . implode('$string', $ent_check) . ';' : '') . '
			preg_match(\'~^(' . $ent_list . '|.){\' . $func[\'strlen\'](substr($string, 0, $length)) . \'}~'.  ($utf8 ? 'u' : '') . '\', $string, $matches);
			$string = $matches[0];
			while (strlen($string) > $length)
				$string = preg_replace(\'~(' . $ent_list . '|.)$~'.  ($utf8 ? 'u' : '') . '\', \'\', $string);
			return $string;'),
		'strtolower' => $utf8 ? (function_exists('mb_strtolower') ? create_function('$string', '
			return mb_strtolower($string, \'UTF-8\');') : create_function('$string', '
			global $sourcedir;
			require_once($sourcedir . \'/Subs-Charset.php\');
			return utf8_strtolower($string);')) : 'strtolower',
		'strtoupper' => $utf8 ? (function_exists('mb_strtoupper') ? create_function('$string', '
			return mb_strtoupper($string, \'UTF-8\');') : create_function('$string', '
			global $sourcedir;
			require_once($sourcedir . \'/Subs-Charset.php\');
			return utf8_strtoupper($string);')) : 'strtoupper',
		'ucfirst' => $utf8 ? create_function('$string', '
			global $func;
			return $func[\'strtoupper\']($func[\'substr\']($string, 0, 1)) . $func[\'substr\']($string, 1);') : 'ucfirst',
		'ucwords' => $utf8 ? (function_exists('mb_convert_case') ? create_function('$string', '
			return mb_convert_case($string, MB_CASE_TITLE, \'UTF-8\');') : create_function('$string', '
			global $func;
			$words = preg_split(\'~([\s\r\n\t]+)~\', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0, $n = count($words); $i < $n; $i += 2)
				$words[$i] = $func[\'ucfirst\']($words[$i]);
			return implode(\'\', $words);')) : 'ucwords',
	);

	// Setting the timezone is a requirement for some functions in PHP >= 5.1.
	if (isset($modSettings['default_timezone']) && function_exists('date_default_timezone_set'))
		date_default_timezone_set($modSettings['default_timezone']);

	// Check the load averages?
	if (!empty($modSettings['loadavg_enable']))
	{
		if (($modSettings['load_average'] = cache_get_data('loadavg', 90)) == null)
		{
			$modSettings['load_average'] = @file_get_contents('/proc/loadavg');
			if (!empty($modSettings['load_average']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $modSettings['load_average'], $matches) != 0)
				$modSettings['load_average'] = (float) $matches[1];
			elseif (($modSettings['load_average'] = @`uptime`) != null && preg_match('~load average[s]?: (\d+\.\d+), (\d+\.\d+), (\d+\.\d+)~i', $modSettings['load_average'], $matches) != 0)
				$modSettings['load_average'] = (float) $matches[1];
			else
				unset($modSettings['load_average']);

			if (!empty($modSettings['load_average']))
				cache_put_data('loadavg', $modSettings['load_average'], 90);
		}

		if (!empty($modSettings['loadavg_forum']) && !empty($modSettings['load_average']) && $modSettings['load_average'] >= $modSettings['loadavg_forum'])
			db_fatal_error(true);
	}

	// Integration is cool.
	if (defined('SMF_INTEGRATION_SETTINGS'))
		$modSettings = unserialize(SMF_INTEGRATION_SETTINGS) + $modSettings;
		
	if (isset($modSettings['integrate_pre_include']) && file_exists(strtr($modSettings['integrate_pre_include'], array('$boarddir' => $boarddir))))
		require_once(strtr($modSettings['integrate_pre_include'], array('$boarddir' => $boarddir)));
		
	if (isset($modSettings['integrate_pre_load']) && function_exists($modSettings['integrate_pre_load']))
		call_user_func($modSettings['integrate_pre_load']);

	// Is it time again to optimize the database?
	if (empty($modSettings['autoOptDatabase']) || $modSettings['autoOptLastOpt'] + $modSettings['autoOptDatabase'] * 3600 * 24 >= time() || SMF == 'SSI')
		return;

	if (!empty($modSettings['load_average']) && !empty($modSettings['loadavg_auto_opt']) && $modSettings['load_average'] >= $modSettings['loadavg_auto_opt'])
		return;

	if (!empty($modSettings['autoOptMaxOnline']))
	{
		$request = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}log_online", __FILE__, __LINE__);
		list ($dont_do_it) = mysql_fetch_row($request);
		mysql_free_result($request);

		if ($dont_do_it > $modSettings['autoOptMaxOnline'])
			return;
	}

	// Handle if things are prefixed with a database name.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
	{
		$request = db_query("
			SHOW TABLES
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'", __FILE__, __LINE__);
	}
	else
	{
		$request = db_query("
			SHOW TABLES
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'", __FILE__, __LINE__);
	}

	$tables = array();
	while ($row = mysql_fetch_row($request))
		$tables[] = $row[0];
	mysql_free_result($request);

	updateSettings(array('autoOptLastOpt' => time()));

	// Don't bail if the user does.
	ignore_user_abort(true);

	// Do them one at a time for locking reasons...
	foreach ($tables as $table)
		db_query("
			OPTIMIZE TABLE `$table`", __FILE__, __LINE__);
}

// Load all the important user information...
function loadUserSettings()
{
	global $modSettings, $user_settings;
	global $ID_MEMBER, $db_prefix, $cookiename, $user_info, $language;

	// Check first the integration, then the cookie, and last the session.
	if (isset($modSettings['integrate_verify_user']) && function_exists($modSettings['integrate_verify_user']))
	{
		$ID_MEMBER = (int) call_user_func($modSettings['integrate_verify_user']);
		$already_verified = $ID_MEMBER > 0;
	}
	else
		$ID_MEMBER = 0;

	if (empty($ID_MEMBER) && isset($_COOKIE[$cookiename]))
	{
		$_COOKIE[$cookiename] = stripslashes($_COOKIE[$cookiename]);

		// Fix a security hole in PHP 4.3.9 and below...
		if (preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$cookiename]) == 1)
		{
			list ($ID_MEMBER, $password) = @unserialize($_COOKIE[$cookiename]);
			$ID_MEMBER = !empty($ID_MEMBER) && strlen($password) > 0 ? (int) $ID_MEMBER : 0;
		}
		else
			$ID_MEMBER = 0;
	}
	elseif (empty($ID_MEMBER) && isset($_SESSION['login_' . $cookiename]) && ($_SESSION['USER_AGENT'] == $_SERVER['HTTP_USER_AGENT'] || !empty($modSettings['disableCheckUA'])))
	{
		// !!! Perhaps we can do some more checking on this, such as on the first octet of the IP?
		list ($ID_MEMBER, $password, $login_span) = @unserialize(stripslashes($_SESSION['login_' . $cookiename]));
		$ID_MEMBER = !empty($ID_MEMBER) && strlen($password) == 40 && $login_span > time() ? (int) $ID_MEMBER : 0;
	}

	// Only load this stuff if the user isn't a guest.
	if ($ID_MEMBER != 0)
	{
		// Is the member data cached?
		if (empty($modSettings['cache_enable']) || $modSettings['cache_enable'] < 2 || ($user_settings = cache_get_data('user_settings-' . $ID_MEMBER, 60)) == null)
		{
			$request = db_query("
				SELECT mem.*, IFNULL(a.ID_ATTACH, 0) AS ID_ATTACH, a.filename, a.attachmentType
				FROM {$db_prefix}members AS mem
					LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = $ID_MEMBER)
				WHERE mem.ID_MEMBER = $ID_MEMBER
				LIMIT 1", __FILE__, __LINE__);
			$user_settings = mysql_fetch_assoc($request);
			mysql_free_result($request);

			if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
				cache_put_data('user_settings-' . $ID_MEMBER, $user_settings, 60);
		}

		// Did we find 'im?  If not, junk it.
		if (!empty($user_settings))
		{
			// As much as the password should be right, we can assume the integration set things up.
			if (!empty($already_verified) && $already_verified === true)
				$check = true;
			// SHA-1 passwords should be 40 characters long.
			elseif (strlen($password) == 40)
				$check = sha1($user_settings['passwd'] . $user_settings['passwordSalt']) == $password;
			else
				$check = false;

			// Wrong password or not activated - either way, you're going nowhere.
			$ID_MEMBER = $check && ($user_settings['is_activated'] == 1 || $user_settings['is_activated'] == 11) ? $user_settings['ID_MEMBER'] : 0;
		}
		else
			$ID_MEMBER = 0;
	}

	// Found 'im, let's set up the variables.
	if ($ID_MEMBER != 0)
	{
		// Let's not update the last visit time in these cases...
		// 1. SSI doesn't count as visiting the forum.
		// 2. RSS feeds and XMLHTTP requests don't count either.
		// 3. If it was set within this session, no need to set it again.
		// 4. New session, yet updated < five hours ago? Maybe cache can help.
		if (SMF != 'SSI' && !isset($_REQUEST['xml']) && (!isset($_REQUEST['action']) || $_REQUEST['action'] != '.xml') && empty($_SESSION['ID_MSG_LAST_VISIT']) && (empty($modSettings['cache_enable']) || ($_SESSION['ID_MSG_LAST_VISIT'] = cache_get_data('user_last_visit-' . $ID_MEMBER, 5 * 3600)) === null))
		{
			// Do a quick query to make sure this isn't a mistake.
			$result = db_query("
				SELECT posterTime
				FROM {$db_prefix}messages
				WHERE ID_MSG = $user_settings[ID_MSG_LAST_VISIT]
				LIMIT 1", __FILE__, __LINE__);
			list ($visitTime) = mysql_fetch_row($result);
			mysql_free_result($result);

			$_SESSION['ID_MSG_LAST_VISIT'] = $user_settings['ID_MSG_LAST_VISIT'];

			// If it was *at least* five hours ago...
			if ($visitTime < time() - 5 * 3600)
			{
				updateMemberData($ID_MEMBER, array('ID_MSG_LAST_VISIT' => (int) $modSettings['maxMsgID'], 'lastLogin' => time(), 'memberIP' => '\'' . $_SERVER['REMOTE_ADDR'] . '\'', 'memberIP2' => '\'' . $_SERVER['BAN_CHECK_IP'] . '\''));
				$user_settings['lastLogin'] = time();

				if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
					cache_put_data('user_settings-' . $ID_MEMBER, $user_settings, 60);

				if (!empty($modSettings['cache_enable']))
					cache_put_data('user_last_visit-' . $ID_MEMBER, $_SESSION['ID_MSG_LAST_VISIT'], 5 * 3600);
			}
		}
		elseif (empty($_SESSION['ID_MSG_LAST_VISIT']))
			$_SESSION['ID_MSG_LAST_VISIT'] = $user_settings['ID_MSG_LAST_VISIT'];

		$username = $user_settings['memberName'];

		if (empty($user_settings['additionalGroups']))
			$user_info = array(
				'groups' => array($user_settings['ID_GROUP'], $user_settings['ID_POST_GROUP'])
			);
		else
			$user_info = array(
				'groups' => array_merge(
					array($user_settings['ID_GROUP'], $user_settings['ID_POST_GROUP']),
					explode(',', $user_settings['additionalGroups'])
				)
			);
	}
	// If the user is a guest, initialize all the critial user settings.
	else
	{
		// This is what a guest's variables should be.
		$username = '';
		$user_info = array('groups' => array(-1));
		$user_settings = array();

		if (isset($_COOKIE[$cookiename]))
			$_COOKIE[$cookiename] = '';
	}

	// Set up the $user_info array.
	$user_info += array(
		'username' => $username,
		'name' => isset($user_settings['realName']) ? $user_settings['realName'] : '',
		'email' => isset($user_settings['emailAddress']) ? $user_settings['emailAddress'] : '',
		'passwd' => isset($user_settings['passwd']) ? $user_settings['passwd'] : '',
		'language' => empty($user_settings['lngfile']) || empty($modSettings['userLanguage']) ? $language : $user_settings['lngfile'],
		'is_guest' => $ID_MEMBER == 0,
		'is_admin' => in_array(1, $user_info['groups']),
		'theme' => empty($user_settings['ID_THEME']) ? 0 : $user_settings['ID_THEME'],
		'last_login' => empty($user_settings['lastLogin']) ? 0 : $user_settings['lastLogin'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'ip2' => $_SERVER['BAN_CHECK_IP'],
		'posts' => empty($user_settings['posts']) ? 0 : $user_settings['posts'],
		'time_format' => empty($user_settings['timeFormat']) ? $modSettings['time_format'] : $user_settings['timeFormat'],
		'time_offset' => empty($user_settings['timeOffset']) ? 0 : $user_settings['timeOffset'],
		'avatar' => array(
			'url' => isset($user_settings['avatar']) ? $user_settings['avatar'] : '',
			'filename' => empty($user_settings['filename']) ? '' : $user_settings['filename'],
			'custom_dir' => !empty($user_settings['attachmentType']) && $user_settings['attachmentType'] == 1,
			'ID_ATTACH' => isset($user_settings['ID_ATTACH']) ? $user_settings['ID_ATTACH'] : 0
		),
		'smiley_set' => isset($user_settings['smileySet']) ? $user_settings['smileySet'] : '',
		'messages' => empty($user_settings['instantMessages']) ? 0 : $user_settings['instantMessages'],
		'unread_messages' => empty($user_settings['unreadMessages']) ? 0 : $user_settings['unreadMessages'],
		'total_time_logged_in' => empty($user_settings['totalTimeLoggedIn']) ? 0 : $user_settings['totalTimeLoggedIn'],
		'buddies' => !empty($modSettings['enable_buddylist']) && !empty($user_settings['buddy_list']) ? explode(',', $user_settings['buddy_list']) : array(),
		'permissions' => array()
	);
	$user_info['groups'] = array_unique($user_info['groups']);

	if (!empty($modSettings['userLanguage']) && !empty($_REQUEST['language']))
	{
		$user_info['language'] = strtr($_REQUEST['language'], './\\:', '____');
		$_SESSION['language'] = $user_info['language'];
	}
	elseif (!empty($modSettings['userLanguage']) && !empty($_SESSION['language']))
		$user_info['language'] = strtr($_SESSION['language'], './\\:', '____');

	// Just build this here, it makes it easier to change/use.
	if ($user_info['is_guest'])
		$user_info['query_see_board'] = 'FIND_IN_SET(-1, b.memberGroups)';
	// Administrators can see all boards.
	elseif ($user_info['is_admin'])
		$user_info['query_see_board'] = '1';
	// Registered user.... just the groups in $user_info['groups'].
	else
		$user_info['query_see_board'] = '(FIND_IN_SET(' . implode(', b.memberGroups) OR FIND_IN_SET(', $user_info['groups']) . ', b.memberGroups))';
}

// Check for moderators and see if they have access to the board.
function loadBoard()
{
	global $txt, $db_prefix, $scripturl, $context, $modSettings;
	global $board_info, $board, $topic, $ID_MEMBER, $user_info;

	// Assume they are not a moderator.
	$user_info['is_mod'] = false;
	$context['user']['is_mod'] = &$user_info['is_mod'];

	// Start the linktree off empty..
	$context['linktree'] = array();

	// Load this board only if the it is specified.
	if (empty($board) && empty($topic))
	{
		$board_info = array('moderators' => array());
		return;
	}

	if (!empty($modSettings['cache_enable']) && (empty($topic) || $modSettings['cache_enable'] == 3))
	{
		// !!! SLOW?
		if (!empty($topic))
			$temp = cache_get_data('topic_board-' . $topic, 120);
		else
			$temp = cache_get_data('board-' . $board, 120);

		if (!empty($temp))
		{
			$board_info = $temp;
			$board = $board_info['id'];
		}
	}

	if (empty($temp))
	{
		$request = db_query("
			SELECT
				c.ID_CAT, b.name AS bname, b.description, b.numTopics, b.memberGroups,
				b.ID_PARENT, c.name AS cname, IFNULL(mem.ID_MEMBER, 0) AS ID_MODERATOR,
				mem.realName" . (!empty($topic) ? ", b.ID_BOARD" : '') . ", b.childLevel,
				b.ID_THEME, b.override_theme, b.permission_mode, b.countPosts
			FROM ({$db_prefix}boards AS b" . (!empty($topic) ? ", {$db_prefix}topics AS t" : '') . ")
				LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
				LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = " . (empty($topic) ? $board : 't.ID_BOARD') . ")
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = mods.ID_MEMBER)
			WHERE b.ID_BOARD = " . (empty($topic) ? $board : "t.ID_BOARD
				AND t.ID_TOPIC = $topic"), __FILE__, __LINE__);
		// If there aren't any, skip.
		if (mysql_num_rows($request) > 0)
		{
			$row = mysql_fetch_assoc($request);

			// Set the current board.
			if (!empty($row['ID_BOARD']))
				$board = $row['ID_BOARD'];

			// Basic operating information. (globals... :/)
			$board_info = array(
				'id' => $board,
				'moderators' => array(),
				'cat' => array(
					'id' => $row['ID_CAT'],
					'name' => $row['cname']
				),
				'name' => $row['bname'],
				'description' => $row['description'],
				'num_topics' => $row['numTopics'],
				'parent_boards' => getBoardParents($row['ID_PARENT']),
				'parent' => $row['ID_PARENT'],
				'child_level' => $row['childLevel'],
				'theme' => $row['ID_THEME'],
				'override_theme' => !empty($row['override_theme']),
				'use_local_permissions' => !empty($modSettings['permission_enable_by_board']) && $row['permission_mode'] == 1,
				'permission_mode' => empty($modSettings['permission_enable_by_board']) ? (empty($row['permission_mode']) ? 'normal' : ($row['permission_mode'] == 2 ? 'no_polls' : ($row['permission_mode'] == 3 ? 'reply_only' : 'read_only'))) : 'normal',
				'posts_count' => empty($row['countPosts']),
			);

			// Load the membergroups allowed, and check permissions.
			$board_info['groups'] = $row['memberGroups'] == '' ? array() : explode(',', $row['memberGroups']);

			do
			{
				if (!empty($row['ID_MODERATOR']))
					$board_info['moderators'][$row['ID_MODERATOR']] = array(
						'id' => $row['ID_MODERATOR'],
						'name' => $row['realName'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MODERATOR'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row['realName'] . '</a>'
					);
			}
			while ($row = mysql_fetch_assoc($request));

			if (!empty($modSettings['cache_enable']) && (empty($topic) || $modSettings['cache_enable'] == 3))
			{
				// !!! SLOW?
				if (!empty($topic))
					cache_put_data('topic_board-' . $topic, $board_info, 120);
				cache_put_data('board-' . $board, $board_info, 120);
			}
		}
		else
		{
			// Otherwise the topic is invalid, there are no moderators, etc.
			$board_info = array(
				'moderators' => array(),
				'error' => 'exist'
			);
			$topic = null;
			$board = 0;
		}
		mysql_free_result($request);
	}

	if (!empty($topic))
		$_GET['board'] = (int) $board;

	if (!empty($board))
	{
		// Now check if the user is a moderator.
		$user_info['is_mod'] = isset($board_info['moderators'][$ID_MEMBER]);

		if (count(array_intersect($user_info['groups'], $board_info['groups'])) == 0 && !$user_info['is_admin'])
			$board_info['error'] = 'access';

		// Build up the linktree.
		$context['linktree'] = array_merge(
			$context['linktree'],
			array(array(
				'url' => $scripturl . '#' . $board_info['cat']['id'],
				'name' => $board_info['cat']['name']
			)),
			array_reverse($board_info['parent_boards']),
			array(array(
				'url' => $scripturl . '?board=' . $board . '.0',
				'name' => $board_info['name']
			))
		);
	}

	// Set the template contextual information.
	$context['user']['is_mod'] = &$user_info['is_mod'];
	$context['current_topic'] = $topic;
	$context['current_board'] = $board;

	// Hacker... you can't see this topic, I'll tell you that. (but moderators can!)
	if (!empty($board_info['error']) && ($board_info['error'] != 'access' || !$user_info['is_mod']))
	{
		// The permissions and theme need loading, just to make sure everything goes smoothly.
		loadPermissions();
		loadTheme();

		$_GET['board'] = '';
		$_GET['topic'] = '';

		// If it's a prefetching agent, just make clear they're not allowed.
		if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
		{
			ob_end_clean();
			header('HTTP/1.1 403 Forbidden');
			die;
		}
		elseif ($user_info['is_guest'])
		{
			loadLanguage('Errors');
			is_not_guest($txt['topic_gone']);
		}
		else
			fatal_lang_error('topic_gone', false);
	}

	if ($user_info['is_mod'])
		$user_info['groups'][] = 3;
}

// Load this user's permissions.
function loadPermissions()
{
	global $user_info, $db_prefix, $board, $board_info, $modSettings;

	$user_info['permissions'] = array();

	if ($user_info['is_admin'])
		return;

	if (!empty($modSettings['cache_enable']))
	{
		$cache_groups = $user_info['groups'];
		asort($cache_groups);
		$cache_groups = implode(',', $cache_groups);

		if ($modSettings['cache_enable'] >= 2 && !empty($board) && ($temp = cache_get_data('permissions:' . $cache_groups . ':' . $board, 240)) != null)
		{
			list ($user_info['permissions']) = $temp;
			banPermissions();

			return;
		}
		elseif (($temp = cache_get_data('permissions:' . $cache_groups, 240)) != null)
			list ($user_info['permissions'], $removals) = $temp;
	}

	if (empty($user_info['permissions']))
	{
		// Get the general permissions.
		$request = db_query("
			SELECT permission, addDeny
			FROM {$db_prefix}permissions
			WHERE ID_GROUP IN (" . implode(', ', $user_info['groups']) . ')', __FILE__, __LINE__);
		$removals = array();
		while ($row = mysql_fetch_assoc($request))
		{
			if (empty($row['addDeny']))
				$removals[] = $row['permission'];
			else
				$user_info['permissions'][] = $row['permission'];
		}
		mysql_free_result($request);

		if (isset($cache_groups))
			cache_put_data('permissions:' . $cache_groups, array($user_info['permissions'], $removals), 240);
	}

	// Get the board permissions.
	if (!empty($board))
	{
		// Make sure the board (if any) has been loaded by loadBoard().
		if (!isset($board_info['use_local_permissions']))
			fatal_lang_error('smf232');

		$request = db_query("
			SELECT permission, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_GROUP IN (" . implode(', ', $user_info['groups']) . ")
				AND ID_BOARD = " . ($board_info['use_local_permissions'] ? $board : '0'), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (empty($row['addDeny']))
				$removals[] = $row['permission'];
			else
				$user_info['permissions'][] = $row['permission'];
		}
		mysql_free_result($request);
	}

	// Remove all the permissions they shouldn't have ;).
	if (!empty($modSettings['permission_enable_deny']))
		$user_info['permissions'] = array_diff($user_info['permissions'], $removals);

	// Remove some board permissions if the board is read-only or reply-only.
	if (empty($modSettings['permission_enable_by_board']) && !empty($board) && $board_info['permission_mode'] != 'normal' && !allowedTo('moderate_board'))
	{
		$permission_mode = array(
			'read_only' => array(
				'post_new',
				'poll_post',
				'post_reply_own',
				'post_reply_any',
			),
			'reply_only' => array(
				'post_new',
				'poll_post',
			),
			'no_polls' => array(
				'poll_post',
			),
		);
		$user_info['permissions'] = array_diff($user_info['permissions'], $permission_mode[$board_info['permission_mode']]);
	}

	if (isset($cache_groups) && !empty($board) && $modSettings['cache_enable'] >= 2)
		cache_put_data('permissions:' . $cache_groups . ':' . $board, array($user_info['permissions'], null), 240);

	// Banned?  Watch, don't touch..
	banPermissions();
}

// Loads an array of users' data by ID or memberName.
function loadMemberData($users, $is_name = false, $set = 'normal')
{
	global $user_profile, $db_prefix, $modSettings, $board_info;

	// Can't just look for no users :P.
	if (empty($users))
		return false;

	// Make sure it's an array.
	$users = !is_array($users) ? array($users) : array_unique($users);
	$loaded_ids = array();

	if (!$is_name && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] == 3)
	{
		$users = array_values($users);
		for ($i = 0, $n = count($users); $i < $n; $i++)
		{
			$data = cache_get_data('member_data-' . $set . '-' . $users[$i], 240);
			if ($data == null)
				continue;

			$loaded_ids[] = $data['ID_MEMBER'];
			$user_profile[$data['ID_MEMBER']] = $data;
			unset($users[$i]);
		}
	}

	if ($set == 'normal')
	{
		$select_columns = "
			IFNULL(lo.logTime, 0) AS isOnline, IFNULL(a.ID_ATTACH, 0) AS ID_ATTACH, a.filename, a.attachmentType,
			mem.signature, mem.personalText, mem.location, mem.gender, mem.avatar, mem.ID_MEMBER, mem.memberName,
			mem.realName, mem.emailAddress, mem.hideEmail, mem.dateRegistered, mem.websiteTitle, mem.websiteUrl,
			mem.birthdate, mem.memberIP, mem.memberIP2, mem.ICQ, mem.AIM, mem.YIM, mem.MSN, mem.posts, mem.lastLogin,
			mem.karmaGood, mem.ID_POST_GROUP, mem.karmaBad, mem.lngfile, mem.ID_GROUP, mem.timeOffset, mem.showOnline,
			mem.buddy_list, mg.onlineColor AS member_group_color, IFNULL(mg.groupName, '') AS member_group,
			pg.onlineColor AS post_group_color, IFNULL(pg.groupName, '') AS post_group, mem.is_activated,
			IF(mem.ID_GROUP = 0 OR mg.stars = '', pg.stars, mg.stars) AS stars" . (!empty($modSettings['titlesEnable']) ? ',
			mem.usertitle' : '');
		$select_tables = "
			LEFT JOIN {$db_prefix}log_online AS lo ON (lo.ID_MEMBER = mem.ID_MEMBER)
			LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = mem.ID_MEMBER)
			LEFT JOIN {$db_prefix}membergroups AS pg ON (pg.ID_GROUP = mem.ID_POST_GROUP)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = mem.ID_GROUP)";
	}
	elseif ($set == 'profile')
	{
		$select_columns = "
			IFNULL(lo.logTime, 0) AS isOnline, IFNULL(a.ID_ATTACH, 0) AS ID_ATTACH, a.filename, a.attachmentType,
			mem.signature, mem.personalText, mem.location, mem.gender, mem.avatar, mem.ID_MEMBER, mem.memberName,
			mem.realName, mem.emailAddress, mem.hideEmail, mem.dateRegistered, mem.websiteTitle, mem.websiteUrl,
			mem.birthdate, mem.ICQ, mem.AIM, mem.YIM, mem.MSN, mem.posts, mem.lastLogin, mem.karmaGood,
			mem.karmaBad, mem.memberIP, mem.memberIP2, mem.lngfile, mem.ID_GROUP, mem.ID_THEME, mem.buddy_list, mem.pm_ignore_list,
			mem.pm_email_notify, mem.timeOffset" . (!empty($modSettings['titlesEnable']) ? ', mem.usertitle' : '') . ",
			mem.timeFormat, mem.secretQuestion, mem.is_activated, mem.additionalGroups, mem.smileySet, mem.showOnline,
			mem.totalTimeLoggedIn, mem.ID_POST_GROUP, mem.notifyAnnouncements, mem.notifyOnce, mem.notifySendBody,
			mem.notifyTypes, lo.url, mg.onlineColor AS member_group_color, IFNULL(mg.groupName, '') AS member_group,
			pg.onlineColor AS post_group_color, IFNULL(pg.groupName, '') AS post_group,
			IF(mem.ID_GROUP = 0 OR mg.stars = '', pg.stars, mg.stars) AS stars, mem.passwordSalt";
		$select_tables = "
			LEFT JOIN {$db_prefix}log_online AS lo ON (lo.ID_MEMBER = mem.ID_MEMBER)
			LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MEMBER = mem.ID_MEMBER)
			LEFT JOIN {$db_prefix}membergroups AS pg ON (pg.ID_GROUP = mem.ID_POST_GROUP)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = mem.ID_GROUP)";
	}
	elseif ($set == 'minimal')
	{
		$select_columns = '
			mem.ID_MEMBER, mem.memberName, mem.realName, mem.emailAddress, mem.hideEmail, mem.dateRegistered,
			mem.posts, mem.lastLogin, mem.memberIP, mem.memberIP2, mem.lngfile, mem.ID_GROUP';
		$select_tables = '';
	}
	else
		trigger_error('loadMemberData(): Invalid member data set \'' . $set . '\'', E_USER_WARNING);

	if (!empty($users))
	{
		// Load the member's data.
		$request = db_query("
			SELECT$select_columns
			FROM {$db_prefix}members AS mem$select_tables
			WHERE mem." . ($is_name ? 'memberName' : 'ID_MEMBER') . (count($users) == 1 ? " = '" . current($users) . "'" : " IN ('" . implode("', '", $users) . "')"), __FILE__, __LINE__);
		$new_loaded_ids = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$new_loaded_ids[] = $row['ID_MEMBER'];
			$loaded_ids[] = $row['ID_MEMBER'];
			$row['options'] = array();
			$user_profile[$row['ID_MEMBER']] = $row;
		}
		mysql_free_result($request);
	}

	if (!empty($new_loaded_ids) && $set !== 'minimal')
	{
		$request = db_query("
			SELECT *
			FROM {$db_prefix}themes
			WHERE ID_MEMBER" . (count($new_loaded_ids) == 1 ? ' = ' . $new_loaded_ids[0] : ' IN (' . implode(', ', $new_loaded_ids) . ')'), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$user_profile[$row['ID_MEMBER']]['options'][$row['variable']] = $row['value'];
		mysql_free_result($request);
	}

	if (!empty($new_loaded_ids) && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] == 3)
	{
		for ($i = 0, $n = count($new_loaded_ids); $i < $n; $i++)
			cache_put_data('member_data-' . $set . '-' . $new_loaded_ids[$i], $user_profile[$new_loaded_ids[$i]], 240);
	}

	// Are we loading any moderators?  If so, fix their group data...
	if (!empty($loaded_ids) && !empty($board_info['moderators']) && $set === 'normal' && count($temp_mods = array_intersect($loaded_ids, array_keys($board_info['moderators']))) !== 0)
	{
		if (($row = cache_get_data('moderator_group_info', 480)) == null)
		{
			$request = db_query("
				SELECT groupName AS member_group, onlineColor AS member_group_color, stars
				FROM {$db_prefix}membergroups
				WHERE ID_GROUP = 3
				LIMIT 1", __FILE__, __LINE__);
			$row = mysql_fetch_assoc($request);
			mysql_free_result($request);

			cache_put_data('moderator_group_info', $row, 480);
		}

		foreach ($temp_mods as $id)
		{
			// By popular demand, don't show admins or global moderators as moderators.
			if ($user_profile[$id]['ID_GROUP'] != 1 && $user_profile[$id]['ID_GROUP'] != 2)
				$user_profile[$id]['member_group'] = $row['member_group'];

			// If the Moderator group has no color or stars, but their group does... don't overwrite.
			if (!empty($row['stars']))
				$user_profile[$id]['stars'] = $row['stars'];
			if (!empty($row['member_group_color']))
				$user_profile[$id]['member_group_color'] = $row['member_group_color'];
		}
	}

	return empty($loaded_ids) ? false : $loaded_ids;
}

// Loads the user's basic values... meant for template/theme usage.
function loadMemberContext($user)
{
	global $memberContext, $user_profile, $txt, $scripturl, $user_info;
	global $context, $modSettings, $ID_MEMBER, $board_info, $settings;
	global $db_prefix, $func;
	static $dataLoaded = array();

	// If this person's data is already loaded, skip it.
	if (isset($dataLoaded[$user]))
		return true;

	// We can't load guests or members not loaded by loadMemberData()!
	if ($user == 0)
		return false;
	if (!isset($user_profile[$user]))
	{
		trigger_error('loadMemberContext(): member id ' . $user . ' not previously loaded by loadMemberData()', E_USER_WARNING);
		return false;
	}

	// Well, it's loaded now anyhow.
	$dataLoaded[$user] = true;
	$profile = $user_profile[$user];

	// Censor everything.
	censorText($profile['signature']);
	censorText($profile['personalText']);
	censorText($profile['location']);

	// Set things up to be used before hand.
	$gendertxt = $profile['gender'] == 2 ? $txt[239] : ($profile['gender'] == 1 ? $txt[238] : '');
	$profile['signature'] = str_replace(array("\n", "\r"), array('<br />', ''), $profile['signature']);
	$profile['signature'] = parse_bbc($profile['signature'], true, 'sig' . $profile['ID_MEMBER']);

	$profile['is_online'] = (!empty($profile['showOnline']) || allowedTo('moderate_forum')) && $profile['isOnline'] > 0;
	$profile['stars'] = empty($profile['stars']) ? array('', '') : explode('#', $profile['stars']);
	// Setup the buddy status here (One whole in_array call saved :P)
	$profile['buddy'] = in_array($profile['ID_MEMBER'], $user_info['buddies']);
	$buddy_list = !empty($profile['buddy_list']) ? explode(',', $profile['buddy_list']) : array();

	// If we're always html resizing, assume it's too large.
	if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
	{
		$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
		$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
	}
	else
	{
		$avatar_width = '';
		$avatar_height = '';
	}

	// What a monstrous array...
	$memberContext[$user] = array(
		'username' => &$profile['memberName'],
		'name' => &$profile['realName'],
		'id' => &$profile['ID_MEMBER'],
		'is_guest' => $profile['ID_MEMBER'] == 0,
		'is_buddy' => $profile['buddy'],
		'is_reverse_buddy' => in_array($ID_MEMBER, $buddy_list),
		'buddies' => $buddy_list,
		'title' => !empty($modSettings['titlesEnable']) ? $profile['usertitle'] : '',
		'href' => $scripturl . '?action=profile;u=' . $profile['ID_MEMBER'],
		'link' => '<a href="' . $scripturl . '?action=profile;u=' . $profile['ID_MEMBER'] . '" title="' . $txt[92] . ' ' . $profile['realName'] . '">' . $profile['realName'] . '</a>',
		'email' => &$profile['emailAddress'],
		'hide_email' => $profile['emailAddress'] == '' || (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']) || (!empty($profile['hideEmail']) && !empty($modSettings['allow_hideEmail']) && !allowedTo('moderate_forum') && $ID_MEMBER != $profile['ID_MEMBER']),
		'email_public' => (empty($profile['hideEmail']) || empty($modSettings['allow_hideEmail'])) && (empty($modSettings['guest_hideContacts']) || !$user_info['is_guest']),
		'registered' => empty($profile['dateRegistered']) ? $txt[470] : timeformat($profile['dateRegistered']),
		'registered_timestamp' => empty($profile['dateRegistered']) ? 0 : forum_time(true, $profile['dateRegistered']),
		'blurb' => &$profile['personalText'],
		'gender' => array(
			'name' => $gendertxt,
			'image' => !empty($profile['gender']) ? '<img src="' . $settings['images_url'] . '/' . ($profile['gender'] == 1 ? 'Male' : 'Female') . '.gif" alt="' . $gendertxt . '" border="0" />' : ''
		),
		'website' => array(
			'title' => &$profile['websiteTitle'],
			'url' => &$profile['websiteUrl'],
		),
		'birth_date' => empty($profile['birthdate']) || $profile['birthdate'] === '0001-01-01' ? '0000-00-00' : (substr($profile['birthdate'], 0, 4) === '0004' ? '0000' . substr($profile['birthdate'], 4) : $profile['birthdate']),
		'signature' => &$profile['signature'],
		'location' => &$profile['location'],
		'icq' => $profile['ICQ'] != '' && (empty($modSettings['guest_hideContacts']) || !$user_info['is_guest']) ? array(
			'name' => &$profile['ICQ'],
			'href' => 'http://www.icq.com/whitepages/about_me.php?uin=' . $profile['ICQ'],
			'link' => '<a href="http://www.icq.com/whitepages/about_me.php?uin=' . $profile['ICQ'] . '" target="_blank"><img src="http://status.icq.com/online.gif?img=5&amp;icq=' . $profile['ICQ'] . '" alt="' . $profile['ICQ'] . '" width="18" height="18" border="0" /></a>',
			'link_text' => '<a href="http://www.icq.com/whitepages/about_me.php?uin=' . $profile['ICQ'] . '" target="_blank">' . $profile['ICQ'] . '</a>',
		) : array('name' => '', 'add' => '', 'href' => '', 'link' => '', 'link_text' => ''),
		'aim' => $profile['AIM'] != '' && (empty($modSettings['guest_hideContacts']) || !$user_info['is_guest']) ? array(
			'name' => &$profile['AIM'],
			'href' => 'aim:goim?screenname=' . urlencode(strtr($profile['AIM'], array(' ' => '%20'))) . '&amp;message=' . $txt['aim_default_message'],
			'link' => '<a href="aim:goim?screenname=' . urlencode(strtr($profile['AIM'], array(' ' => '%20'))) . '&amp;message=' . $txt['aim_default_message'] . '"><img src="' . $settings['images_url'] . '/aim.gif" alt="' . $profile['AIM'] . '" border="0" /></a>',
			'link_text' => '<a href="aim:goim?screenname=' . urlencode(strtr($profile['AIM'], array(' ' => '%20'))) . '&amp;message=' . $txt['aim_default_message'] . '">' . $profile['AIM'] . '</a>'
		) : array('name' => '', 'href' => '', 'link' => '', 'link_text' => ''),
		'yim' => $profile['YIM'] != '' && (empty($modSettings['guest_hideContacts']) || !$user_info['is_guest']) ? array(
			'name' => &$profile['YIM'],
			'href' => 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($profile['YIM']),
			'link' => '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($profile['YIM']) . '"><img src="http://opi.yahoo.com/online?u=' . urlencode($profile['YIM']) . '&amp;m=g&amp;t=0" alt="' . $profile['YIM'] . '" border="0" /></a>',
			'link_text' => '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($profile['YIM']) . '">' . $profile['YIM'] . '</a>'
		) : array('name' => '', 'href' => '', 'link' => '', 'link_text' => ''),
		'msn' => $profile['MSN'] !='' && (empty($modSettings['guest_hideContacts']) || !$user_info['is_guest']) ? array(
			'name' => &$profile['MSN'],
			'href' => 'http://members.msn.com/' . $profile['MSN'],
			'link' => '<a href="http://members.msn.com/' . $profile['MSN'] . '" target="_blank"><img src="' . $settings['images_url'] . '/msntalk.gif" alt="' . $profile['MSN'] . '" border="0" /></a>',
			'link_text' => '<a href="http://members.msn.com/' . $profile['MSN'] . '" target="_blank">' . $profile['MSN'] . '</a>'
		) : array('name' => '', 'href' => '', 'link' => '', 'link_text' => ''),
		'real_posts' => $profile['posts'],
		'posts' => $profile['posts'] > 100000 ? $txt[683] : ($profile['posts'] == 1337 ? 'leet' : comma_format($profile['posts'])),
		'avatar' => array(
			'name' => &$profile['avatar'],
			'image' => $profile['avatar'] == '' ? ($profile['ID_ATTACH'] > 0 ? '<img src="' . (empty($profile['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $profile['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $profile['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($profile['avatar'], 'http://') ? '<img src="' . $profile['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($profile['avatar']) . '" alt="" class="avatar" border="0" />'),
			'href' => $profile['avatar'] == '' ? ($profile['ID_ATTACH'] > 0 ? (empty($profile['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $profile['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $profile['filename']) : '') : (stristr($profile['avatar'], 'http://') ? $profile['avatar'] : $modSettings['avatar_url'] . '/' . $profile['avatar']),
			'url' => $profile['avatar'] == '' ? '' : (stristr($profile['avatar'], 'http://') ? $profile['avatar'] : $modSettings['avatar_url'] . '/' . $profile['avatar'])
		),
		'last_login' => empty($profile['lastLogin']) ? $txt['never'] : timeformat($profile['lastLogin']),
		'last_login_timestamp' => empty($profile['lastLogin']) ? 0 : forum_time(0, $profile['lastLogin']),
		'karma' => array(
			'good' => &$profile['karmaGood'],
			'bad' => &$profile['karmaBad'],
			'allow' => !$user_info['is_guest'] && $user_info['posts'] >= $modSettings['karmaMinPosts'] && allowedTo('karma_edit') && !empty($modSettings['karmaMode']) && $ID_MEMBER != $user
		),
		'ip' => htmlspecialchars($profile['memberIP']),
		'ip2' => htmlspecialchars($profile['memberIP2']),
		'online' => array(
			'is_online' => $profile['is_online'],
			'text' => &$txt[$profile['is_online'] ? 'online2' : 'online3'],
			'href' => $scripturl . '?action=pm;sa=send;u=' . $profile['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $profile['ID_MEMBER'] . '">' . $txt[$profile['is_online'] ? 'online2' : 'online3'] . '</a>',
			'image_href' => $settings['images_url'] . '/' . ($profile['buddy'] ? 'buddy_' : '') . ($profile['is_online'] ? 'useron' : 'useroff') . '.gif',
			'label' => &$txt[$profile['is_online'] ? 'online4' : 'online5']
		),
		'language' => $func['ucwords'](strtr($profile['lngfile'], array('_' => ' ', '-utf8' => ''))),
		'is_activated' => isset($profile['is_activated']) ? $profile['is_activated'] : 1,
		'is_banned' => isset($profile['is_activated']) ? $profile['is_activated'] >= 10 : 0,
		'options' => $profile['options'],
		'is_guest' => false,
		'group' => $profile['member_group'],
		'group_color' => $profile['member_group_color'],
		'group_id' => $profile['ID_GROUP'],
		'post_group' => $profile['post_group'],
		'post_group_color' => $profile['post_group_color'],
		'group_stars' => str_repeat('<img src="' . str_replace('$language', $context['user']['language'], isset($profile['stars'][1]) ? $settings['images_url'] . '/' . $profile['stars'][1] : '') . '" alt="*" border="0" />', empty($profile['stars'][0]) || empty($profile['stars'][1]) ? 0 : $profile['stars'][0]),
		'local_time' => timeformat(time() + ($profile['timeOffset'] - $user_info['time_offset']) * 3600, false),
	);

	return true;
}

// Load a theme, by ID.
function loadTheme($ID_THEME = 0, $initialize = true)
{
	global $ID_MEMBER, $user_info, $board_info, $sc;
	global $db_prefix, $txt, $boardurl, $scripturl, $mbname, $modSettings;
	global $context, $settings, $options;

	// The theme was specified by parameter.
	if (!empty($ID_THEME))
		$ID_THEME = (int) $ID_THEME;
	// Use the board's specific theme.
	elseif (!empty($board_info['theme']) && $board_info['override_theme'])
		$ID_THEME = $board_info['theme'];
	// The theme was specified by REQUEST.
	elseif (!empty($_REQUEST['theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
	{
		$ID_THEME = (int) $_REQUEST['theme'];
		$_SESSION['ID_THEME'] = $ID_THEME;
	}
	// The theme was specified by REQUEST... previously.
	elseif (!empty($_SESSION['ID_THEME']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$ID_THEME = (int) $_SESSION['ID_THEME'];
	// The theme is just the user's choice. (might use ?board=1;theme=0 to force board theme.)
	elseif (!empty($user_info['theme']) && !isset($_REQUEST['theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$ID_THEME = $user_info['theme'];
	// The theme was specified by the board.
	elseif (!empty($board_info['theme']))
		$ID_THEME = $board_info['theme'];
	// The theme is the forum's default.
	else
		$ID_THEME = $modSettings['theme_guests'];

	// Verify the ID_THEME... no foul play.
	if (empty($modSettings['theme_default']) && $ID_THEME == 1 && !allowedTo('admin_forum'))
		$ID_THEME = $modSettings['theme_guests'];
	elseif (!empty($modSettings['knownThemes']) && !empty($modSettings['theme_allow']) && !allowedTo('admin_forum'))
	{
		$themes = explode(',', $modSettings['knownThemes']);
		if (!in_array($ID_THEME, $themes))
			$ID_THEME = $modSettings['theme_guests'];
		else
			$ID_THEME = (int) $ID_THEME;
	}
	else
		$ID_THEME = (int) $ID_THEME;

	$member = empty($ID_MEMBER) ? -1 : $ID_MEMBER;

	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2 && ($temp = cache_get_data('theme_settings-' . $ID_THEME . ':' . $member, 60)) != null)
	{
		$themeData = $temp;
		$flag = true;
	}
	elseif (($temp = cache_get_data('theme_settings-' . $ID_THEME, 90)) != null)
		$themeData = $temp + array($member => array());
	else
		$themeData = array(-1 => array(), 0 => array(), $member => array());

	if (empty($flag))
	{
		// Load variables from the current or default theme, global or this user's.
		$result = db_query("
			SELECT variable, value, ID_MEMBER, ID_THEME
			FROM {$db_prefix}themes
			WHERE ID_MEMBER" . (empty($themeData[0]) ? " IN (-1, 0, $member)" : " = $member") . "
				AND ID_THEME" . ($ID_THEME == 1 ? ' = 1' : " IN ($ID_THEME, 1)"), __FILE__, __LINE__);
		// Pick between $settings and $options depending on whose data it is.
		while ($row = mysql_fetch_assoc($result))
		{
			// There are just things we shouldn't be able to change as members.
			if ($row['ID_MEMBER'] != 0 && in_array($row['variable'], array('actual_theme_url', 'actual_images_url', 'base_theme_dir', 'base_theme_url', 'default_images_url', 'default_theme_dir', 'default_theme_url', 'default_template', 'images_url', 'number_recent_posts', 'smiley_sets_default', 'theme_dir', 'theme_id', 'theme_layers', 'theme_templates', 'theme_url')))
				continue;

			// If this is the theme_dir of the default theme, store it.
			if (in_array($row['variable'], array('theme_dir', 'theme_url', 'images_url')) && $row['ID_THEME'] == '1' && empty($row['ID_MEMBER']))
				$themeData[0]['default_' . $row['variable']] = $row['value'];

			// If this isn't set yet, is a theme option, or is not the default theme..
			if (!isset($themeData[$row['ID_MEMBER']][$row['variable']]) || $row['ID_THEME'] != '1')
				$themeData[$row['ID_MEMBER']][$row['variable']] = substr($row['variable'], 0, 5) == 'show_' ? $row['value'] == '1' : $row['value'];
		}
		mysql_free_result($result);

		if (!empty($themeData[-1]))
			foreach ($themeData[-1] as $k => $v)
			{
				if (!isset($themeData[$member][$k]))
					$themeData[$member][$k] = $v;
			}

		if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
			cache_put_data('theme_settings-' . $ID_THEME . ':' . $member, $themeData, 60);
		// Only if we didn't already load that part of the cache...
		elseif (!isset($temp))
			cache_put_data('theme_settings-' . $ID_THEME, array(-1 => $themeData[-1], 0 => $themeData[0]), 90);
	}

	$settings = $themeData[0];
	$options = $themeData[$member];

	$settings['theme_id'] = $ID_THEME;

	$settings['actual_theme_url'] = $settings['theme_url'];
	$settings['actual_images_url'] = $settings['images_url'];
	$settings['actual_theme_dir'] = $settings['theme_dir'];

	if (!$initialize)
		return;

	// Check to see if they're accessing it from the wrong place.
	if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']))
	{
		$detected_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https://' : 'http://';
		$detected_url .= empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];
		$temp = preg_replace('~/' . basename($scripturl) . '(/.+)?$~', '', strtr(dirname($_SERVER['PHP_SELF']), '\\', '/'));
		if ($temp != '/')
			$detected_url .= $temp;
	}
	if (isset($detected_url) && $detected_url != $boardurl)
	{
		// Try #1 - check if it's in a list of alias addresses.
		if (!empty($modSettings['forum_alias_urls']))
		{
			$aliases = explode(',', $modSettings['forum_alias_urls']);

			foreach ($aliases as $alias)
			{
				// Rip off all the boring parts, spaces, etc.
				if ($detected_url == trim($alias) || strtr($detected_url, array('http://' => '', 'https://' => '')) == trim($alias))
					$do_fix = true;
			}
		}

		// Hmm... check #2 - is it just different by a www?  Send them to the correct place!!
		if (empty($do_fix) && strtr($detected_url, array('://' => '://www.')) == $boardurl && (empty($_GET) || count($_GET) == 1))
		{
			// Okay, this seems weird, but we don't want an endless loop - this will make $_GET not empty ;).
			if (empty($_GET))
				redirectexit('www');
			else
			{
				list ($k, $v) = each($_GET);

				if ($k != 'www')
					redirectexit('www;' . $k . '=' . $v);
			}
		}

		// #3 is just a check for SSL...
		if (strtr($detected_url, array('https://' => 'http://')) == $boardurl)
			$do_fix = true;

		// Okay, #4 - perhaps it's an IP address?  We're gonna want to use that one, then. (assuming it's the IP or something...)
		if (!empty($do_fix) || preg_match('~^http[s]://[\d\.:]+($|/)~', $detected_url) == 1)
		{
			// Caching is good ;).
			$oldurl = $boardurl;

			// Fix $boardurl and $scripturl.
			$boardurl = $detected_url;
			$scripturl = strtr($scripturl, array($oldurl => $boardurl));
			$_SERVER['REQUEST_URL'] = strtr($_SERVER['REQUEST_URL'], array($oldurl => $boardurl));

			// Fix the theme urls...
			$settings['theme_url'] = strtr($settings['theme_url'], array($oldurl => $boardurl));
			$settings['default_theme_url'] = strtr($settings['default_theme_url'], array($oldurl => $boardurl));
			$settings['actual_theme_url'] = strtr($settings['actual_theme_url'], array($oldurl => $boardurl));
			$settings['images_url'] = strtr($settings['images_url'], array($oldurl => $boardurl));
			$settings['default_images_url'] = strtr($settings['default_images_url'], array($oldurl => $boardurl));
			$settings['actual_images_url'] = strtr($settings['actual_images_url'], array($oldurl => $boardurl));

			// And just a few mod settings :).
			$modSettings['smileys_url'] = strtr($modSettings['smileys_url'], array($oldurl => $boardurl));
			$modSettings['avatar_url'] = strtr($modSettings['avatar_url'], array($oldurl => $boardurl));

			// Clean up after loadBoard().
			foreach ($board_info['moderators'] as $k => $dummy)
			{
				$board_info['moderators'][$k]['href'] = strtr($dummy['href'], array($oldurl => $boardurl));;
				$board_info['moderators'][$k]['link'] = strtr($dummy['link'], array('"' . $oldurl => '"' . $boardurl));;
			}
			foreach ($context['linktree'] as $k => $dummy)
				$context['linktree'][$k]['url'] = strtr($dummy['url'], array($oldurl => $boardurl));
		}
	}

	// Set up the contextual user array.
	$context['user'] = array(
		'id' => &$ID_MEMBER,
		'is_logged' => !$user_info['is_guest'],
		'is_guest' => &$user_info['is_guest'],
		'is_admin' => &$user_info['is_admin'],
		'is_mod' => false,
		'username' => &$user_info['username'],
		'language' => &$user_info['language'],
		'email' => &$user_info['email']
	);
	if ($context['user']['is_guest'])
		$context['user']['name'] = &$txt[28];
	else
		$context['user']['name'] = &$user_info['name'];

	// Determine the current smiley set.
	$user_info['smiley_set'] = (!in_array($user_info['smiley_set'], explode(',', $modSettings['smiley_sets_known'])) && $user_info['smiley_set'] != 'none') || empty($modSettings['smiley_sets_enable']) ? (!empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default']) : $user_info['smiley_set'];
	$context['user']['smiley_set'] = &$user_info['smiley_set'];

	// Some basic information...
	if (!isset($context['html_headers']))
		$context['html_headers'] = '';
	$context['menu_separator'] = !empty($settings['use_image_buttons']) ? ' ' : ' | ';
	$context['session_id'] = &$sc;
	$context['forum_name'] = &$mbname;
	$context['current_action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
	$context['current_subaction'] = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : null;
	if (isset($modSettings['load_average']))
		$context['load_average'] = $modSettings['load_average'];

	// This determines the server... not used in many places, except for login fixing.
	$context['server'] = array(
		'is_iis' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false,
		'is_apache' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false,
		'is_cgi' => isset($_SERVER['SERVER_SOFTWARE']) && strpos(php_sapi_name(), 'cgi') !== false,
		'is_windows' => stristr(PHP_OS, 'WIN') !== false,
		'iso_case_folding' => ord(strtolower(chr(138))) === 154,
		'complex_preg_chars' => @version_compare(PHP_VERSION, '4.3.3') != -1,
	);
	// A bug in some versions of IIS under CGI (older ones) makes cookie setting not work with Location: headers.
	$context['server']['needs_login_fix'] = $context['server']['is_cgi'];

	// The following determines the user agent (browser) as best it can.
	$context['browser'] = array(
		'is_opera' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false,
		'is_opera6' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 6') !== false,
		'is_opera7' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 7') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera/7') !== false,
		'is_opera8' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 8') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera/8') !== false,
		'is_ie4' => strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 4') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WebTV') === false,
		'is_safari' => strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false,
		'is_mac_ie' => strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false,
		'is_web_tv' => strpos($_SERVER['HTTP_USER_AGENT'], 'WebTV') !== false,
		'is_konqueror' => strpos($_SERVER['HTTP_USER_AGENT'], 'Konqueror') !== false,
		'is_firefox' => strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false,
		'is_firefox1' => strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox/1.') !== false,
		'is_firefox2' => strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox/2.') !== false,
	);

	$context['browser']['is_gecko'] = strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false && !$context['browser']['is_safari'] && !$context['browser']['is_konqueror'];

	// Internet Explorer 5 and 6 are often "emulated".
	$context['browser']['is_ie7'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false && !$context['browser']['is_opera'] && !$context['browser']['is_gecko'] && !$context['browser']['is_web_tv'];
	$context['browser']['is_ie6'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false && !$context['browser']['is_opera'] && !$context['browser']['is_gecko'] && !$context['browser']['is_web_tv'];
	$context['browser']['is_ie5.5'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.5') !== false && !$context['browser']['is_opera'] && !$context['browser']['is_gecko'] && !$context['browser']['is_web_tv'];
	$context['browser']['is_ie5'] = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.0') !== false && !$context['browser']['is_opera'] && !$context['browser']['is_gecko'] && !$context['browser']['is_web_tv'];

	$context['browser']['is_ie'] = $context['browser']['is_ie4'] || $context['browser']['is_ie5'] || $context['browser']['is_ie5.5'] || $context['browser']['is_ie6'] || $context['browser']['is_ie7'];
	$context['browser']['needs_size_fix'] = ($context['browser']['is_ie5'] || $context['browser']['is_ie5.5'] || $context['browser']['is_ie4'] || $context['browser']['is_opera6']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') === false;

	// 1.1.x doesn't detect IE8, so render as IE7.
	$context['html_headers'] .= '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';

	// This isn't meant to be reliable, it's just meant to catch most bots to prevent PHPSESSID from showing up.
	$ci_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$context['browser']['possibly_robot'] = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') === false) || strpos($ci_user_agent, 'googlebot') !== false || strpos($ci_user_agent, 'slurp') !== false || strpos($ci_user_agent, 'crawl') !== false;

	// Robots shouldn't be logging in or registering.  So, they aren't a bot.  Better to be wrong than sorry (or people won't be able to log in!), anyway.
	if ((isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('login', 'login2', 'register'))) || !$context['user']['is_guest'])
		$context['browser']['possibly_robot'] = false;

	// Set the top level linktree up.
	array_unshift($context['linktree'], array(
		'url' => &$scripturl,
		'name' => &$context['forum_name']
	));

	$txt = array();
	$simpleActions = array(
		'findmember',
		'helpadmin',
		'printpage',
		'quotefast',
		'spellcheck',
	);

	// Wireless mode?  Load up the wireless stuff.
	if (WIRELESS)
	{
		$context['template_layers'] = array(WIRELESS_PROTOCOL);
		loadTemplate('Wireless');
		loadLanguage('Wireless');
		loadLanguage('index');
	}
	// Output is fully XML, so no need for the index template.
	elseif (isset($_REQUEST['xml']))
	{
		loadLanguage('index');
		loadTemplate('Xml');
		$context['template_layers'] = array();
	}
	// These actions don't require the index template at all.
	elseif (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $simpleActions))
	{
		loadLanguage('index');
		$context['template_layers'] = array();
	}
	else
	{
		// Custom templates to load, or just default?
		if (isset($settings['theme_templates']))
			$templates = explode(',', $settings['theme_templates']);
		else
			$templates = array('index');

		// Custom template layers?
		if (isset($settings['theme_layers']))
			$context['template_layers'] = explode(',', $settings['theme_layers']);
		else
			$context['template_layers'] = array('main');

		// Load each template.... and attempt to load its associated language file.
		foreach ($templates as $template)
		{
			loadTemplate($template);
			loadLanguage($template, '', false);
		}
	}

	// Load the Modifications language file, always ;). (but don't sweat it if it doesn't exist.)
	loadLanguage('Modifications', '', false);

	// Initialize the theme.
	loadSubTemplate('init', 'ignore');

	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'always')
	{
		$settings['theme_url'] = $settings['default_theme_url'];
		$settings['images_url'] = $settings['default_images_url'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}

	// Set the character set from the template.
	$context['character_set'] = empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set'];
	$context['utf8'] = $context['character_set'] === 'UTF-8' && (strpos(strtolower(PHP_OS), 'win') === false || @version_compare(PHP_VERSION, '4.2.3') != -1);
	$context['right_to_left'] = !empty($txt['lang_rtl']);

	$context['tabindex'] = 1;

	// Fix font size with HTML 4.01, etc.
	if (isset($settings['doctype']))
		$context['browser']['needs_size_fix'] |= $settings['doctype'] == 'html' && $context['browser']['is_ie6'];

	// Compatibility.
	if (!isset($settings['theme_version']))
		$modSettings['memberCount'] = $modSettings['totalMembers'];
}

// Load a template - if the theme doesn't include it, use the default.
function loadTemplate($template_name, $fatal = true)
{
	global $context, $settings, $txt, $scripturl, $boarddir, $db_show_debug;

	// Try the current theme's first.
	if (file_exists($settings['theme_dir'] . '/' . $template_name . '.template.php'))
	{
		template_include($settings['theme_dir'] . '/' . $template_name . '.template.php', true);
		$template_name .= ' (' . basename($settings['theme_dir']) . ')';
	}
	// Are we using a base theme?  If so, does it have the template?
	elseif (isset($settings['base_theme_dir']) && file_exists($settings['base_theme_dir'] . '/' . $template_name . '.template.php'))
	{
		template_include($settings['base_theme_dir'] . '/' . $template_name . '.template.php', true);
		$template_name .= ' (' . basename($settings['base_theme_dir']) . ')';
	}
	// Perhaps we'll just use the default template, then...
	elseif (file_exists($settings['default_theme_dir'] . '/' . $template_name . '.template.php'))
	{
		// Make it known that this template uses different directories...
		$settings['default_template'] = true;
		template_include($settings['default_theme_dir'] . '/' . $template_name . '.template.php', true);
		$template_name .= ' (' . basename($settings['default_theme_dir']) . ')';
	}
	// Hmmm... doesn't exist?!  I don't suppose the directory is wrong, is it?
	elseif (!file_exists($settings['default_theme_dir']) && file_exists($boarddir . '/Themes/default'))
	{
		$settings['default_theme_dir'] = $boarddir . '/Themes/default';

		if (!empty($context['user']['is_admin']) && !isset($_GET['th']))
		{
			loadLanguage('Errors');
			echo '
<div style="color: red; padding: 2ex; background-color: white; border: 2px dashed;">
	<a href="', $scripturl . '?action=theme;sa=settings;th=1;sesc=' . $context['session_id'], '" style="color: red;">', $txt['theme_dir_wrong'], '</a>
</div>';
		}

		loadTemplate($template_name);
	}
	// Cause an error otherwise.
	elseif ($template_name != 'Errors' && $template_name != 'index' && $fatal)
		fatal_lang_error('theme_template_error', true, array((string) $template_name));
	elseif ($fatal)
		die(log_error(sprintf(isset($txt['theme_template_error']) ? $txt['theme_template_error'] : 'Unable to load Themes/default/%s.template.php!', (string) $template_name)));
	else
		return false;

	// For compatibility reasons, if this is the index template without new functions, include compatible stuff.
	if (substr($template_name, 0, 5) == 'index' && !function_exists('template_button_strip'))
		loadTemplate('Combat');

	if ($db_show_debug === true)
		$context['debug']['templates'][] = $template_name;
}

// Load a sub template... fatal is for templates that shouldn't get a 'pretty' error screen.
function loadSubTemplate($sub_template_name, $fatal = false)
{
	global $context, $settings, $options, $txt, $db_show_debug;

	if ($db_show_debug === true)
		$context['debug']['sub_templates'][] = $sub_template_name;

	// Figure out what the template function is named.
	$theme_function = 'template_' . $sub_template_name;
	if (function_exists($theme_function))
		$theme_function();
	elseif ($fatal === false)
		fatal_lang_error('theme_template_error', true, array((string) $sub_template_name));
	elseif ($fatal !== 'ignore')
		die(log_error(sprintf(isset($txt['theme_template_error']) ? $txt['theme_template_error'] : 'Unable to load the %s sub template!', (string) $sub_template_name)));

	// Are we showing debugging for templates?  Just make sure not to do it before the doctype...
	if (allowedTo('admin_forum') && isset($_REQUEST['debug']) && !in_array($sub_template_name, array('init', 'main_below')) && ob_get_length() > 0 && !isset($_REQUEST['xml']))
	{
		echo '
<div style="font-size: 8pt; border: 1px dashed red; background: orange; text-align: center; font-weight: bold;">---- ', $sub_template_name, ' ends ----</div>';
	}
}

// Load a language file.  Tries the current and default themes as well as the user and global languages.
function loadLanguage($template_name, $lang = '', $fatal = true)
{
	global $boarddir, $boardurl, $user_info, $language_dir, $language, $settings, $context, $txt, $db_show_debug;
	static $already_loaded = array();

	// Default to the user's language.
	if ($lang == '')
		$lang = $user_info['language'];

	// Obviously, the current theme is most important to check.
	$attempts = array(
		array($settings['theme_dir'], $template_name, $lang, $settings['theme_url']),
		array($settings['theme_dir'], $template_name, $language, $settings['theme_url']),
	);

	// Do we have a base theme to worry about?
	if (isset($settings['base_theme_dir']))
	{
		$attempts[] = array($settings['base_theme_dir'], $template_name, $lang, $settings['base_theme_url']);
		$attempts[] = array($settings['base_theme_dir'], $template_name, $language, $settings['base_theme_url']);
	}

	// Fallback on the default theme if necessary.
	$attempts[] = array($settings['default_theme_dir'], $template_name, $lang, $settings['default_theme_url']);
	$attempts[] = array($settings['default_theme_dir'], $template_name, $language, $settings['default_theme_url']);

	// Try to include the language file.
	foreach ($attempts as $k => $file)
		if (file_exists($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php'))
		{
			$language_dir = $file[0] . '/languages';
			$lang = $file[2];
			// Hmmm... do we really still need this?
			$language_url = $file[3];
			template_include($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php');

			break;
		}

	// That couldn't be found!  Log the error, but *try* to continue normally.
	if (!isset($language_url))
	{
		if ($fatal)
			log_error(sprintf($txt['theme_language_error'], $template_name . '.' . $lang));
		return false;
	}

	if ($db_show_debug === true)
		$context['debug']['language_files'][] = $template_name . '.' . $lang . ' (' . basename($language_url) . ')';

	// Return the language actually loaded.
	return $lang;
}

// Get all parent boards (requires first parent as parameter)
function getBoardParents($id_parent)
{
	global $db_prefix, $scripturl, $txt;

	$boards = array();

	// Loop while the parent is non-zero.
	while ($id_parent != 0)
	{
		$result = db_query("
			SELECT
				b.ID_PARENT, b.name, $id_parent AS ID_BOARD, IFNULL(mem.ID_MEMBER, 0) AS ID_MODERATOR,
				mem.realName, b.childLevel
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = mods.ID_MEMBER)
			WHERE b.ID_BOARD = $id_parent", __FILE__, __LINE__);
		// In the EXTREMELY unlikely event this happens, give an error message.
		if (mysql_num_rows($result) == 0)
			fatal_lang_error('parent_not_found');
		while ($row = mysql_fetch_assoc($result))
		{
			if (!isset($boards[$row['ID_BOARD']]))
			{
				$id_parent = $row['ID_PARENT'];
				$boards[$row['ID_BOARD']] = array(
					'url' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
					'name' => $row['name'],
					'level' => $row['childLevel'],
					'moderators' => array()
				);
			}
			// If a moderator exists for this board, add that moderator for all children too.
			if (!empty($row['ID_MODERATOR']))
				foreach ($boards as $id => $dummy)
				{
					$boards[$id]['moderators'][$row['ID_MODERATOR']] = array(
						'id' => $row['ID_MODERATOR'],
						'name' => $row['realName'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MODERATOR'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row['realName'] . '</a>'
					);
				}
		}
		mysql_free_result($result);
	}

	return $boards;
}

// Replace all vulgar words with respective proper words. (substring or whole words..)
function &censorText(&$text)
{
	global $modSettings, $options, $settings, $txt;
	static $censor_vulgar = null, $censor_proper;

	if ((!empty($options['show_no_censored']) && $settings['allow_no_censored']) || empty($modSettings['censor_vulgar']))
		return $text;

	// If they haven't yet been loaded, load them.
	if ($censor_vulgar == null)
	{
		$censor_vulgar = explode("\n", $modSettings['censor_vulgar']);
		$censor_proper = explode("\n", $modSettings['censor_proper']);

		// Quote them for use in regular expressions.
		for ($i = 0, $n = count($censor_vulgar); $i < $n; $i++)
		{
			$censor_vulgar[$i] = strtr(preg_quote($censor_vulgar[$i], '/'), array('\\\\\\*' => '[*]', '\\*' => '[^\s]*?', '&' => '&amp;'));
			$censor_vulgar[$i] = (empty($modSettings['censorWholeWord']) ? '/' . $censor_vulgar[$i] . '/' : '/(?<=^|\W)' . $censor_vulgar[$i] . '(?=$|\W)/') . (empty($modSettings['censorIgnoreCase']) ? '' : 'i') . ((empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8' ? 'u' : '');

			if (strpos($censor_vulgar[$i], '\'') !== false)
			{
				$censor_proper[count($censor_vulgar)] = $censor_proper[$i];
				$censor_vulgar[count($censor_vulgar)] = strtr($censor_vulgar[$i], array('\'' => '&#039;'));
			}
		}
	}

	// Censoring isn't so very complicated :P.
	$text = preg_replace($censor_vulgar, $censor_proper, $text);
	return $text;
}

// Create a little jumpto box.
function loadJumpTo()
{
	global $db_prefix, $context, $user_info;

	if (isset($context['jump_to']))
		return;

	// Find the boards/cateogories they can see.
	$request = db_query("
		SELECT c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.childLevel
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['jump_to'] = array();
	$this_cat = array('id' => -1);
	while ($row = mysql_fetch_assoc($request))
	{
		if ($this_cat['id'] != $row['ID_CAT'])
		{
			$this_cat = &$context['jump_to'][];
			$this_cat['id'] = $row['ID_CAT'];
			$this_cat['name'] = $row['catName'];
			$this_cat['boards'] = array();
		}

		$this_cat['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['boardName'],
			'child_level' => $row['childLevel'],
			'is_current' => isset($context['current_board']) && $row['ID_BOARD'] == $context['current_board']
		);
	}
	mysql_free_result($request);
}

// Load the template/language file using eval or require? (with eval we can show an error message!)
function template_include($filename, $once = false)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	global $language_dir, $user_info, $boardurl, $boarddir, $sourcedir;
	global $maintenance, $mtitle, $mmessage;
	static $templates = array();

	// We want to be able to figure out any errors...
	@ini_set('track_errors', '1');

	// Don't include the file more than once, if $once is true.
	if ($once && in_array($filename, $templates))
		return;
	// Add this file to the include list, whether $once is true or not.
	else
		$templates[] = $filename;

	// Are we going to use eval?
	if (empty($modSettings['disableTemplateEval']))
	{
		$file_found = file_exists($filename) && eval('?' . '>' . rtrim(file_get_contents($filename))) !== false;
		$settings['current_include_filename'] = $filename;
	}
	else
	{
		$file_found = file_exists($filename);

		if ($once && $file_found)
			require_once($filename);
		elseif ($file_found)
			require($filename);
	}

	if ($file_found !== true)
	{
		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
		else
			ob_start();

		if (isset($_GET['debug']) && !WIRELESS)
			header('Content-Type: application/xhtml+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// Don't cache error pages!!
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache');

		if (!isset($txt['template_parse_error']))
		{
			$txt['template_parse_error'] = 'Template Parse Error!';
			$txt['template_parse_error_message'] = 'It seems something has gone sour on the forum with the template system.  This problem should only be temporary, so please come back later and try again.  If you continue to see this message, please contact the administrator.<br /><br />You can also try <a href="javascript:location.reload();">refreshing this page</a>.';
			$txt['template_parse_error_details'] = 'There was a problem loading the <tt><b>%1$s</b></tt> template or language file.  Please check the syntax and try again - remember, single quotes (<tt>\'</tt>) often have to be escaped with a slash (<tt>\\</tt>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s" target="_blank">accessing the file directly</a>.<br /><br />You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';
		}

		// First, let's get the doctype and language information out of the way.
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', !empty($context['right_to_left']) ? ' dir="rtl"' : '', '>
	<head>';
		if (isset($context['character_set']))
			echo '
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />';

		if (!empty($maintenance) && !allowedTo('admin_forum'))
			echo '
		<title>', $mtitle, '</title>
	</head>
	<body>
		<h3>', $mtitle, '</h3>
		', $mmessage, '
	</body>
</html>';
		elseif (!allowedTo('admin_forum'))
			echo '
		<title>', $txt['template_parse_error'], '</title>
	</head>
	<body>
		<h3>', $txt['template_parse_error'], '</h3>
		', $txt['template_parse_error_message'], '
	</body>
</html>';
		else
		{
			require_once($sourcedir . '/Subs-Package.php');

			$error = fetch_web_data($boardurl . strtr($filename, array($boarddir => '', strtr($boarddir, '\\', '/') => '')));
			if (empty($error))
				$error = $php_errormsg;

			echo '
		<title>', $txt['template_parse_error'], '</title>
	</head>
	<body>
		<h3>', $txt['template_parse_error'], '</h3>
		', sprintf($txt['template_parse_error_details'], strtr($filename, array($boarddir => '', strtr($boarddir, '\\', '/') => '')));

			if (!empty($error))
				echo '
		<hr />

		<div style="margin: 0 20px;"><tt>', strtr(strtr($error, array('<b>' . $boarddir => '<b>...', '<b>' . strtr($boarddir, '\\', '/') => '<b>...')), '\\', '/'), '</tt></div>';

			// I know, I know... this is VERY COMPLICATED.  Still, it's good.
			if (preg_match('~ <b>(\d+)</b><br( /)?' . '>$~i', $error, $match) != 0)
			{
				$data = file($filename);
				$data2 = highlight_php_code(implode('', $data));
				$data2 = preg_split('~\<br( /)?\>~', $data2);

				// Fix the PHP code stuff...
				if ($context['browser']['is_ie4'] || $context['browser']['is_ie5'] || $context['browser']['is_ie5.5'])
					$data2 = str_replace("\t", "<pre style=\"display: inline;\">\t</pre>", $data2);
				elseif (!$context['browser']['is_gecko'])
					$data2 = str_replace("\t", "<span style=\"white-space: pre;\">\t</span>", $data2);
				else
					$data2 = str_replace("<pre style=\"display: inline;\">\t</pre>", "\t", $data2);

				// Now we get to work around a bug in PHP where it doesn't escape <br />s!
				$j = -1;
				foreach ($data as $line)
				{
					$j++;

					if (substr_count($line, '<br />') == 0)
						continue;

					$n = substr_count($line, '<br />');
					for ($i = 0; $i < $n; $i++)
					{
						$data2[$j] .= '&lt;br /&gt;' . $data2[$j + $i + 1];
						unset($data2[$j + $i + 1]);
					}
					$j += $n;
				}
				$data2 = array_values($data2);
				array_unshift($data2, '');

				echo '
		<div style="margin: 2ex 20px; width: 96%; overflow: auto;"><pre style="margin: 0;">';

				// Figure out what the color coding was before...
				$line = max($match[1] - 9, 1);
				$last_line = '';
				for ($line2 = $line - 1; $line2 > 1; $line2--)
					if (strpos($data2[$line2], '<') !== false)
					{
						if (preg_match('~(<[^/>]+>)[^<]*$~', $data2[$line2], $color_match) != 0)
							$last_line = $color_match[1];
						break;
					}

				// Show the relevant lines...
				for ($n = min($match[1] + 4, count($data2) + 1); $line <= $n; $line++)
				{
					if ($line == $match[1])
						echo '</pre><div style="background-color: #ffb0b5;"><pre style="margin: 0;">';

					echo '<span style="color: black;">', sprintf('%' . strlen($n) . 's', $line), ':</span> ';
					if ($data2[$line] != '')
						echo substr($data2[$line], 0, 2) == '</' ? preg_replace('~^</[^>]+>~', '', $data2[$line]) : $last_line . $data2[$line];

					if (preg_match('~(<[^/>]+>)[^<]*$~', $data2[$line], $color_match) != 0)
					{
						$last_line = $color_match[1];
						echo '</', substr($last_line, 1, 4), '>';
					}
					elseif ($last_line != '' && strpos($data2[$line], '<') !== false)
						$last_line = '';
					elseif ($last_line != '' && $data2[$line] != '')
						echo '</', substr($last_line, 1, 4), '>';

					if ($line == $match[1])
						echo '</pre></div><pre style="margin: 0;">';
					else
						echo "\n";
				}

				echo '</pre></div>';
			}

			echo '
	</body>
</html>';
		}

		die;
	}
}

// Attempt to start the session, unless it already has been.
function loadSession()
{
	global $HTTP_SESSION_VARS, $modSettings, $boardurl, $sc;

	// Attempt to change a few PHP settings.
	@ini_set('session.use_cookies', true);
	@ini_set('session.use_only_cookies', false);
	@ini_set('url_rewriter.tags', '');
	@ini_set('session.use_trans_sid', false);
	@ini_set('arg_separator.output', '&amp;');

	if (!empty($modSettings['globalCookies']))
	{
		$parsed_url = parse_url($boardurl);

		if (preg_match('~^\d{1,3}(\.\d{1,3}){3}$~', $parsed_url['host']) == 0 && preg_match('~(?:[^\.]+\.)?([^\.]{2,}\..+)\z~i', $parsed_url['host'], $parts) == 1)
			@ini_set('session.cookie_domain', '.' . $parts[1]);
	}
	// !!! Set the session cookie path?

	// If it's already been started... probably best to skip this.
	if ((@ini_get('session.auto_start') == 1 && !empty($modSettings['databaseSession_enable'])) || session_id() == '')
	{
		// Attempt to end the already-started session.
		if (@ini_get('session.auto_start') == 1)
			@session_write_close();

		// This is here to stop people from using bad junky PHPSESSIDs.
		if (isset($_REQUEST[session_name()]) && preg_match('~^[A-Za-z0-9]{16,32}$~', $_REQUEST[session_name()]) == 0 && !isset($_COOKIE[session_name()]))
		{
			$_REQUEST[session_name()] = md5(md5('smf_sess_' . time()) . mt_rand());
			$_GET[session_name()] = md5(md5('smf_sess_' . time()) . mt_rand());
			$_POST[session_name()] = md5(md5('smf_sess_' . time()) . mt_rand());
		}

		// Use database sessions? (they don't work in 4.1.x!)
		if (!empty($modSettings['databaseSession_enable']) && @version_compare(PHP_VERSION, '4.2.0') != -1)
		{
			session_set_save_handler('sessionOpen', 'sessionClose', 'sessionRead', 'sessionWrite', 'sessionDestroy', 'sessionGC');
			@ini_set('session.gc_probability', '1');
		}
		elseif (@ini_get('session.gc_maxlifetime') <= 1440 && !empty($modSettings['databaseSession_lifetime']))
			@ini_set('session.gc_maxlifetime', max($modSettings['databaseSession_lifetime'], 60));

		// Use cache setting sessions?
		if (empty($modSettings['databaseSession_enable']) && !empty($modSettings['cache_enable']) && php_sapi_name() != 'cli')
		{
			if (function_exists('mmcache_set_session_handlers'))
				mmcache_set_session_handlers();
			elseif (function_exists('eaccelerator_set_session_handlers'))
				eaccelerator_set_session_handlers();
		}

		session_start();

		// Change it so the cache settings are a little looser than default.
		if (!empty($modSettings['databaseSession_loose']))
			header('Cache-Control: private');
	}

	// Set the randomly generated code.
	if (!isset($_SESSION['rand_code']))
		$_SESSION['rand_code'] = md5(session_id() . mt_rand() . (string) microtime() . $modSettings['rand_seed']);
	$sc = $_SESSION['rand_code'];

	// While PHP 4.1.x should use $_SESSION, it seems to need this to do it right. Also reseed the random generator.
	if (@version_compare(PHP_VERSION, '4.2.0') == -1)
	{
		$HTTP_SESSION_VARS['php_412_bugfix'] = true;
		mt_srand((float) microtime() * 10000010 + $modSettings['rand_seed']);
	}
	else
		mt_srand();
}

function sessionOpen($save_path, $session_name)
{
	return true;
}

function sessionClose()
{
	return true;
}

function sessionRead($session_id)
{
	global $db_prefix;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// Look for it in the database.
	$result = db_query("
		SELECT data
		FROM {$db_prefix}sessions
		WHERE session_id = '" . addslashes($session_id) . "'
		LIMIT 1", __FILE__, __LINE__);
	list ($sess_data) = mysql_fetch_row($result);
	mysql_free_result($result);

	return $sess_data;
}

function sessionWrite($session_id, $data)
{
	global $db_prefix;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// First try to update an existing row...
	$result = db_query("
		UPDATE {$db_prefix}sessions
		SET data = '" . addslashes($data) . "', last_update = " . time() . "
		WHERE session_id = '" . addslashes($session_id) . "'
		LIMIT 1", __FILE__, __LINE__);

	// If that didn't work, try inserting a new one.
	if (db_affected_rows() == 0)
		$result = db_query("
			INSERT IGNORE INTO {$db_prefix}sessions
				(session_id, data, last_update)
			VALUES ('" . addslashes($session_id) . "', '" . addslashes($data) . "', " . time() . ")", __FILE__, __LINE__);

	return $result;
}

function sessionDestroy($session_id)
{
	global $db_prefix;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// Just delete the row...
	return db_query("
		DELETE FROM {$db_prefix}sessions
		WHERE session_id = '" . addslashes($session_id) . "'
		LIMIT 1", __FILE__, __LINE__);
}

function sessionGC($max_lifetime)
{
	global $db_prefix, $modSettings;

	// Just set to the default or lower?  Ignore it for a higher value. (hopefully)
	if (!empty($modSettings['databaseSession_lifetime']) && ($max_lifetime <= 1440 || $modSettings['databaseSession_lifetime'] > $max_lifetime))
		$max_lifetime = max($modSettings['databaseSession_lifetime'], 60);

	// Clean up ;).
	return db_query("
		DELETE FROM {$db_prefix}sessions
		WHERE last_update < " . (time() - $max_lifetime), __FILE__, __LINE__);
}

function cache_put_data($key, $value, $ttl = 120)
{
	global $boardurl, $sourcedir, $modSettings, $memcached;
	global $cache_hits, $cache_count, $db_show_debug;

	if (empty($modSettings['cache_enable']) && !empty($modSettings))
		return;

	$cache_count = isset($cache_count) ? $cache_count + 1 : 1;
	if (isset($db_show_debug) && $db_show_debug === true)
	{
		$cache_hits[$cache_count] = array('k' => $key, 'd' => 'put', 's' => $value === null ? 0 : strlen(serialize($value)));
		$st = microtime();
	}

	$key = md5($boardurl . filemtime($sourcedir . '/Load.php')) . '-SMF-' . $key;
	$value = $value === null ? null : serialize($value);

	// The simple yet efficient memcached.
	if (function_exists('memcache_set') && isset($modSettings['cache_memcached']) && trim($modSettings['cache_memcached']) != '')
	{
		// Not connected yet?
		if (empty($memcached))
			get_memcached_server();
		if (!$memcached)
			return;

		memcache_set($memcached, $key, $value, 0, $ttl);
	}
	// eAccelerator...
	elseif (function_exists('eaccelerator_put'))
	{
		if (mt_rand(0, 10) == 1)
			eaccelerator_gc();

		if ($value === null)
			@eaccelerator_rm($key);
		else
			eaccelerator_put($key, $value, $ttl);
	}
	// Turck MMCache?
	elseif (function_exists('mmcache_put'))
	{
		if (mt_rand(0, 10) == 1)
			mmcache_gc();

		if ($value === null)
			@mmcache_rm($key);
		else
			mmcache_put($key, $value, $ttl);
	}
	// Alternative PHP Cache, ahoy!
	elseif (function_exists('apc_store'))
	{
		// An extended key is needed to counteract a bug in APC.
		if ($value === null)
			apc_delete($key . 'smf');
		else
			apc_store($key . 'smf', $value, $ttl);
	}
	// Zend Platform/ZPS/etc.
	elseif (function_exists('output_cache_put'))
		output_cache_put($key, $value);

	if (isset($db_show_debug) && $db_show_debug === true)
		$cache_hits[$cache_count]['t'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));
}

function cache_get_data($key, $ttl = 120)
{
	global $boardurl, $sourcedir, $modSettings, $memcached;
	global $cache_hits, $cache_count, $db_show_debug;

	if (empty($modSettings['cache_enable']) && !empty($modSettings))
		return;

	$cache_count = isset($cache_count) ? $cache_count + 1 : 1;
	if (isset($db_show_debug) && $db_show_debug === true)
	{
		$cache_hits[$cache_count] = array('k' => $key, 'd' => 'get');
		$st = microtime();
	}

	$key = md5($boardurl . filemtime($sourcedir . '/Load.php')) . '-SMF-' . $key;

	// Okay, let's go for it memcached!
	if (function_exists('memcache_get') && isset($modSettings['cache_memcached']) && trim($modSettings['cache_memcached']) != '')
	{
		// Not connected yet?
		if (empty($memcached))
			get_memcached_server();
		if (!$memcached)
			return;

		$value = memcache_get($memcached, $key);
	}
	// Again, eAccelerator.
	elseif (function_exists('eaccelerator_get'))
		$value = eaccelerator_get($key);
	// The older, but ever-stable, Turck MMCache...
	elseif (function_exists('mmcache_get'))
		$value = mmcache_get($key);
	// This is the free APC from PECL.
	elseif (function_exists('apc_fetch'))
		$value = apc_fetch($key . 'smf');
	// Zend's pricey stuff.
	elseif (function_exists('output_cache_get'))
		$value = output_cache_get($key, $ttl);

	if (isset($db_show_debug) && $db_show_debug === true)
	{
		$cache_hits[$cache_count]['t'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));
		$cache_hits[$cache_count]['s'] = isset($value) ? strlen($value) : 0;
	}

	if (empty($value))
		return null;
	// If it's broke, it's broke... so give up on it.
	else
		return @unserialize($value);
}

function get_memcached_server($level = 3)
{
	global $modSettings, $memcached, $db_persist;

	$servers = explode(',', $modSettings['cache_memcached']);
	$server = explode(':', trim($servers[array_rand($servers)]));

	// Don't try more times than we have servers!
	$level = min(count($servers), $level);

	// Don't wait too long: yes, we want the server, but we might be able to run the query faster!
	if (empty($db_persist))
		$memcached = memcache_connect($server[0], empty($server[1]) ? 11211 : $server[1]);
	else
		$memcached = memcache_pconnect($server[0], empty($server[1]) ? 11211 : $server[1]);

	if (!$memcached && $level > 0)
		get_memcached_server($level - 1);
}

?>