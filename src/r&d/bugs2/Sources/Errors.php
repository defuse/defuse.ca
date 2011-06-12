<?php
/**********************************************************************************
* Errors.php                                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                             *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
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

/*	The purpose of this file is... errors. (hard to guess, huh?)  It takes
	care of logging, error messages, error handling, database errors, and
	error log administration.  It does this with:

	string log_error(string error_message, string filename = none,
			int line = none)
		- logs an error, if error logging is enabled.
		- depends on the enableErrorLogging setting.
		- filename and line should be __FILE__ and __LINE__, respectively.
		- returns the error message. (ie. die(log_error($msg));)

	resource db_error(string database_query, string filename, int line)
		- logs and handles a database error, and tries to fix any broken
		  tables if it's enabled.
		- used by db_query() from Subs.php... takes its same parameters.
		- should not be used except by db_query().
		- returns a query result if it was able to recover.

	void fatal_error(string error_message, bool log = true)
		- stops execution and displays an error message.
		- logs the error message if log is missing or true.
		- uses the fatal_error sub template of the Errors template - or the
		  error sub template in the Wireless template.

	void fatal_lang_error(string error_message_key, bool log = false,
			array sprintf = array())
		- stops execution and displays an error message by key.
		- uses the string with the error_message_key key.
		- loads the Errors language file.
		- applies the sprintf information if specified.
		- the information is logged if log is true or missing.
		- uses the Errors template with the fatal_error sub template, or the
		  proper error sub template in the Wirless template.

	void error_handler(int error_level, string error_string, string filename,
			int line)
		- this is a standard PHP error handler replacement.
		- dies with fatal_error() if the error_level matches with
		  error_reporting.

	bool db_fatal_error(bool loadavg = false)
		- loads Subs-Auth.php and calls show_db_error().
		- this is used for database connection error handling.
		- loadavg means this is a load average problem, not a database error.
*/

// Log an error, if the option is on.
function log_error($error_message, $file = null, $line = null)
{
	global $db_prefix, $txt, $modSettings, $ID_MEMBER, $sc, $user_info;

	// Check if error logging is actually on.
	if (empty($modSettings['enableErrorLogging']))
		return $error_message;

	// Basically, htmlspecialchars it minus &. (for entities!)
	$error_message = strtr($error_message, array('<' => '&lt;', '>' => '&gt;', '"' => '&quot;'));
	$error_message = strtr($error_message, array('&lt;br /&gt;' => '<br />', '&lt;b&gt;' => '<b>', '&lt;/b&gt;' => '</b>', "\n" => '<br />'));

	// Add a file and line to the error message?  (remember, $txt may not exist yet!!)
	if ($file != null)
		$error_message .= '<br />' . (isset($txt[1003]) ? $txt[1003] . ': ' : '') . $file;
	if ($line != null)
		$error_message .= '<br />' . (isset($txt[1004]) ? $txt[1004] . ': ' : '') . $line;

	// Just in case there's no ID_MEMBER or IP set yet.
	if (empty($ID_MEMBER))
		$ID_MEMBER = 0;
	if (empty($user_info['ip']))
		$user_info['ip'] = '';

	// Don't log the session hash in the url twice, it's a waste.
	$query_string = empty($_SERVER['QUERY_STRING']) ? '' : addslashes(htmlspecialchars('?' . preg_replace(array('~;sesc=[^&;]+~', '~' . session_name() . '=' . session_id() . '[&;]~'), array(';sesc', ''), $_SERVER['QUERY_STRING'])));

	// Just so we know what board error messages are from.
	if (isset($_POST['board']) && !isset($_GET['board']))
		$query_string .= ($query_string == '' ? 'board=' : ';board=') . $_POST['board'];

	// Insert the error into the database.
	db_query("
		INSERT INTO {$db_prefix}log_errors
			(ID_MEMBER, logTime, ip, url, message, session)
		VALUES ($ID_MEMBER, " . time() . ", SUBSTRING('$user_info[ip]', 1, 16), SUBSTRING('$query_string', 1, 65534), SUBSTRING('" . addslashes($error_message) . "', 1, 65534), '$sc')", false, false) or die($error_message);

	// Return the message to make things simpler.
	return $error_message;
}

// Database error!
function db_error($db_string, $file, $line)
{
	global $txt, $context, $sourcedir, $webmaster_email, $modSettings;
	global $forum_version, $db_connection, $db_last_error, $db_persist;
	global $db_server, $db_user, $db_passwd, $db_name, $db_show_debug;

	// This is the error message...
	$query_error = mysql_error($db_connection);
	$query_errno = mysql_errno($db_connection);

	// Error numbers:
	//    1016: Can't open file '....MYI'
	//    1030: Got error ??? from table handler.
	//    1034: Incorrect key file for table.
	//    1035: Old key file for table.
	//    1205: Lock wait timeout exceeded.
	//    1213: Deadlock found.
	//    2006: Server has gone away.
	//    2013: Lost connection to server during query.

	// Log the error.
	if ($query_errno != 1213 && $query_errno != 1205)
		log_error($txt[1001] . ': ' . $query_error, $file, $line);

	// Database error auto fixing ;).
	if (!isset($modSettings['autoFixDatabase']) || $modSettings['autoFixDatabase'] == '1')
	{
		// Force caching on, just for the error checking.
		$old_cache = @$modSettings['cache_enable'];
		$modSettings['cache_enable'] = '1';

		if (($temp = cache_get_data('db_last_error', 600)) !== null)
			$db_last_error = max(@$db_last_error, $temp);

		if (@$db_last_error < time() - 3600 * 24 * 3)
		{
			// We know there's a problem... but what?  Try to auto detect.
			if ($query_errno == 1030 && strpos($query_error, ' 127 ') !== false)
			{
				preg_match_all('~(?:[\n\r]|^)[^\']+?(?:FROM|JOIN|UPDATE|TABLE) ((?:[^\n\r(]+?(?:, )?)*)~s', $db_string, $matches);

				$fix_tables = array();
				foreach ($matches[1] as $tables)
				{
					$tables = array_unique(explode(',', $tables));
					foreach ($tables as $table)
					{
						// Now, it's still theoretically possible this could be an injection.  So backtick it!
						if (trim($table) != '')
							$fix_tables[] = '`' . strtr(trim($table), array('`' => '')) . '`';
					}
				}

				$fix_tables = array_unique($fix_tables);
			}
			// Table crashed.  Let's try to fix it.
			elseif ($query_errno == 1016)
			{
				if (preg_match('~\'([^\.\']+)~', $query_error, $match) != 0)
					$fix_tables = array('`' . $match[1] . '`');
			}
			// Indexes crashed.  Should be easy to fix!
			elseif ($query_errno == 1034 || $query_errno == 1035)
			{
				preg_match('~\'([^\']+?)\'~', $query_error, $match);
				$fix_tables = array('`' . $match[1] . '`');
			}
		}

		// Check for errors like 145... only fix it once every three days, and send an email. (can't use empty because it might not be set yet...)
		if (!empty($fix_tables))
		{
			// Admin.php for updateSettingsFile(), Subs-Post.php for sendmail().
			require_once($sourcedir . '/Admin.php');
			require_once($sourcedir . '/Subs-Post.php');

			// Make a note of the REPAIR...
			cache_put_data('db_last_error', time(), 600);
			if (($temp = cache_get_data('db_last_error', 600)) === null)
				updateSettingsFile(array('db_last_error' => time()));

			// Attempt to find and repair the broken table.
			foreach ($fix_tables as $table)
				db_query("
					REPAIR TABLE $table", false, false);

			// And send off an email!
			sendmail($webmaster_email, $txt[1001], $txt[1005]);

			$modSettings['cache_enable'] = $old_cache;

			// Try the query again...?
			$ret = db_query($db_string, false, false);
			if ($ret !== false)
				return $ret;
		}
		else
			$modSettings['cache_enable'] = $old_cache;

		// Check for the "lost connection" or "deadlock found" errors - and try it just one more time.
		if (in_array($query_errno, array(1205, 1213, 2006, 2013)))
		{
			if (in_array($query_errno, array(2006, 2013)))
			{
				if (empty($db_persist))
					$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
				else
					$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

				if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
					$db_connection = false;
			}

			if ($db_connection)
			{
				// Try a deadlock more than once more.
				for ($n = 0; $n < 4; $n++)
				{
					$ret = db_query($db_string, false, false);

					$new_errno = mysql_errno($db_connection);
					if ($ret !== false || in_array($new_errno, array(1205, 1213)))
						break;
				}

				// If it failed again, shucks to be you... we're not trying it over and over.
				if ($ret !== false)
					return $ret;
			}
		}
		// Are they out of space, perhaps?
		elseif ($query_errno == 1030 && (strpos($query_error, ' -1 ') !== false || strpos($query_error, ' 28 ') !== false || strpos($query_error, ' 12 ') !== false))
		{
			if (!isset($txt))
				$query_error .= ' - check database storage space.';
			else
			{
				if (!isset($txt['mysql_error_space']))
					loadLanguage('Errors');

				$query_error .= !isset($txt['mysql_error_space']) ? ' - check database storage space.' : $txt['mysql_error_space'];
			}
		}
	}

	// Nothing's defined yet... just die with it.
	if (empty($context) || empty($txt))
		die($query_error);

	// Show an error message, if possible.
	$context['error_title'] = $txt[1001];
	if (allowedTo('admin_forum'))
		$context['error_message'] = nl2br($query_error) . '<br />' . $txt[1003] . ': ' . $file . '<br />' . $txt[1004] . ': ' . $line;
	else
		$context['error_message'] = $txt[1002];

	// A database error is often the sign of a database in need of updgrade.  Check forum versions, and if not identical suggest an upgrade... (not for Demo/CVS versions!)
	if (allowedTo('admin_forum') && !empty($forum_version) && $forum_version != 'SMF ' . @$modSettings['smfVersion'] && strpos($forum_version, 'Demo') === false && strpos($forum_version, 'CVS') === false)
		$context['error_message'] .= '<br /><br />' . $txt['database_error_versions'];

	if (allowedTo('admin_forum') && isset($db_show_debug) && $db_show_debug === true)
	{
		$context['error_message'] .= '<br /><br />' . nl2br($db_string);
	}

	// It's already been logged... don't log it again.
	fatal_error($context['error_message'], false);
}

// An irrecoverable error.
function fatal_error($error, $log = true)
{
	global $txt, $context, $modSettings;

	// We don't have $txt yet, but that's okay...
	if (empty($txt))
		die($error);

	// Log the error and set up the template.
	if (!isset($context['error_title']))
	{
		$context['error_title'] = $txt[106];
		$context['error_message'] = $log || (!empty($modSettings['enableErrorLogging']) && $modSettings['enableErrorLogging'] == 2) ? log_error($error) : $error;
	}

	// If there's not a page title yet, set one.
	if (!isset($context['page_title']))
		$context['page_title'] = $context['error_title'];

	// Display the error message - wireless?
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_error';
	// Load the template and set the sub template.
	else
	{
		loadTemplate('Errors');
		$context['sub_template'] = 'fatal_error';
	}

	// We want whatever for the header, and a footer. (footer includes sub template!)
	obExit(null, true);

	/* DO NOT IGNORE:
		If you are creating a bridge to SMF or modifying this function, you MUST
		make ABSOLUTELY SURE that this function quits and DOES NOT RETURN TO NORMAL
		PROGRAM FLOW.  Otherwise, security error messages will not be shown, and
		your forum will be in a very easily hackable state.
	*/
	trigger_error('Hacking attempt...', E_USER_ERROR);
}

// A fatal error with a message stored in the language file.
function fatal_lang_error($error, $log = true, $sprintf = array())
{
	global $txt;

	// Load the language file...
	loadLanguage('Errors');

	// Are we formatting anything?
	if (empty($sprintf))
		fatal_error($txt[$error], $log);
	else
		fatal_error(vsprintf($txt[$error], $sprintf), $log);
}

// Handler for standard error messages.
function error_handler($error_level, $error_string, $file, $line)
{
	global $settings, $modSettings, $db_show_debug;

	// Ignore errors if we're ignoring them or they are strict notices from PHP 5 (which cannot be solved without breaking PHP 4.)
	if (error_reporting() == 0 || (defined('E_STRICT') && $error_level == E_STRICT && (empty($modSettings['enableErrorLogging']) || $modSettings['enableErrorLogging'] != 2)))
		return;

	if (strpos($file, 'eval()') !== false && !empty($settings['current_include_filename']))
	{
		if (function_exists('debug_backtrace'))
		{
			$array = debug_backtrace();
			for ($i = 0; $i < count($array); $i++)
			{
				if ($array[$i]['function'] != 'loadSubTemplate')
					continue;

				// This is a bug in PHP, with eval, it seems!
				if (empty($array[$i]['args']))
					$i++;
				break;
			}

			if (isset($array[$i]) && !empty($array[$i]['args']))
				$file = realpath($settings['current_include_filename']) . ' (' . $array[$i]['args'][0] . ' sub template - eval?)';
			else
				$file = realpath($settings['current_include_filename']) . ' (eval?)';
		}
		else
			$file = realpath($settings['current_include_filename']) . ' (eval?)';
	}

	if (isset($db_show_debug) && $db_show_debug === true)
	{
		// Commonly, undefined indexes will occur inside attributes; try to show them anyway!
		if ($error_level % 255 != E_ERROR)
		{
			$temporary = ob_get_contents();
			if (substr($temporary, -2) == '="')
				echo '"';
		}

		// Debugging!  This should look like a PHP error message.
		echo '<br />
<b>', $error_level % 255 == E_ERROR ? 'Error' : ($error_level % 255 == E_WARNING ? 'Warning' : 'Notice'), '</b>: ', $error_string, ' in <b>', $file, '</b> on line <b>', $line, '</b><br />';
	}

	$message = log_error($error_level . ': ' . $error_string, $file, $line);

	// Dying on these errors only causes MORE problems (blank pages!)
	if ($file == 'Unknown')
		return;

	// If this is an E_ERROR or E_USER_ERROR.... die.  Violently so.
	if ($error_level % 255 == E_ERROR)
		obExit(false);
	else
		return;

	// If this is an E_ERROR, E_USER_ERROR, E_WARNING, or E_USER_WARNING.... die.  Violently so.
	if ($error_level % 255 == E_ERROR || $error_level % 255 == E_WARNING)
		fatal_error(allowedTo('admin_forum') ? $message : $error_string, false);

	// We should NEVER get to this point.  Any fatal error MUST quit, or very bad things can happen.
	if ($error_level % 255 == E_ERROR)
		die('Hacking attempt...');
}

// Just wrap it so we don't take up time and space here in Errors.php.
function db_fatal_error($loadavg = false)
{
	global $sourcedir;

	// Just load the other file and run it.
	require_once($sourcedir . '/Subs-Auth.php');
	show_db_error($loadavg);

	// Since we use "or db_fatal_error();" this is needed...
	return false;
}

?>