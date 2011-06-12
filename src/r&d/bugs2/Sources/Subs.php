<?php
/**********************************************************************************
* Subs.php                                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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

/*	This file has all the main functions in it that relate to, well,
	everything.  It provides all of the following functions:

	resource db_query(string database_query, string __FILE__, int __LINE__)
		- should always be used in place of mysql_query.
		- executes a query string, and implements needed error checking.
		- always use the magic constants __FILE__ and __LINE__.
		- returns a MySQL result resource, to be freed with mysql_free_result.

	int db_affected_rows()
		- should always be used in place of db_insert_id.
		- returns the number of affected rows by the most recently executed
		  query.
		- handles the current connection so the forum with other connections
		  active at the same time.

	int db_insert_id()
		- should always be used in place of mysql_insert_id.
		- returns the most recently generated auto_increment column.
		- handles the current connection so the forum with other connections
		  active at the same time.

	void updateStats(string statistic, string condition = '1')
		- statistic can be 'member', 'message', 'topic', 'calendar', or
		  'postgroups'.
		- parameter1 and parameter2 are optional, and are used to update only
		  those stats that need updating.
		- the 'member' statistic updates the latest member, the total member
		  count, and the number of unapproved members.
		- 'member' also only counts approved members when approval is on, but
		  is much more efficient with it off.
		- updating 'message' changes the total number of messages, and the
		  highest message id by ID_MSG - which can be parameters 1 and 2,
		  respectively.
		- 'topic' updates the total number of topics, or if parameter1 is true
		  simply increments them.
		- the 'calendar' statistic updates the cache of the calendar
		  information for a day before and after today.
		- the 'postgroups' case updates those members who match condition's
		  post-based membergroups in the database (restricted by parameter1).

	void updateMemberData(int ID_MEMBER, array data)
		- updates the columns in the members table.
		- ID_MEMBER is either an int or an array of ints to be updated.
		- data is an associative array of the columns to be updated and their
		  respective values.
		- any string values updated should be quoted and slashed.
		- the value of any column can be '+' or '-', which mean 'increment'
		  and decrement, respectively.
		- if the member's post number is updated, updates their post groups.
		- this function should be used whenever member data needs to be
		  updated in place of an UPDATE query.

	void updateSettings(array changeArray, use_update = false)
		- updates both the settings table and $modSettings array.
		- all of changeArray's indexes and values are assumed to have escaped
		  apostrophes (')!
		- if a variable is already set to what you want to change it to, that
		  variable will be skipped over; it would be unnecessary to reset.
		- if use_update is true, UPDATEs will be used instead of REPALCE.
		- when use_update is true, the value can be true or false to increment
		  or decrement it, respectively.

	string constructPageIndex(string base_url, int &start, int max_value,
			int num_per_page, bool compact_start = false)
		- builds the page list, e.g. 1 ... 6 7 [8] 9 10 ... 15.
		- compact_start caused it to use "url.page" instead of
		  "url;start=page".
		- handles any wireless settings (adding special things to URLs.)
		- very importantly, cleans up the start value passed, and forces it to
		  be a multiple of num_per_page.
		- also checks that start is not more than max_value.
		- base_url should be the URL without any start parameter on it.
		- uses the compactTopicPagesEnable and compactTopicPagesContiguous
		  settings to decide how to display the menu.
		- an example is available near the function definition.

	string comma_format(float number, int override_decimal_count = false)
		- formats a number to display in the style of the admins' choosing.
		- uses the format of number_format to decide how to format the number.
		- for example, it might display "1 234,50".
		- caches the formatting data from the setting for optimization.

	string timeformat(int time, bool show_today = true)
		- returns a pretty formated version of time based on the user's format
		  in $user_info['time_format'].
		- applies any necessary time offsets to the timestamp.
		- if todayMod is set and show_today was not not specified or true, an
		  alternate format string is used to show the date with something to
		  show it is "today" or "yesterday".
		- performs localization (more than just strftime would do alone.)

	string un_htmlspecialchars(string text)
		- removes the base entities (&lt;, &quot;, etc.) from text.
		- should be used instead of html_entity_decode for PHP version
		  compatibility reasons.
		- additionally converts &nbsp; and &#039;.
		- returns the string without entities.

	string shorten_subject(string regular_subject, int length)
		- shortens a subject so that it is either shorter than length, or that
		  length plus an ellipsis.
		- respects internationalization characters and entities as one character.
		- avoids trailing entities.
		- returns the shortened string.

	int forum_time(bool use_user_offset = true)
		- returns the current time with offsets.
		- always applies the offset in the time_offset setting.
		- if use_user_offset is true, applies the user's offset as well.
		- returns seconds since the unix epoch.

	array permute(array input)
		- calculates all the possible permutations (orders) of array.
		- should not be called on huge arrays (bugger than like 10 elements.)
		- returns an array containing each permutation.

	string doUBBC(string message, bool enableSmileys = true)
		- passes through parse_bbc(message, enableSmileys).
		- available for older implementations.

	string parse_bbc(string message, bool smileys = true, string cache_id = '')
		- this very hefty function parses bbc in message.
		- only parses bbc tags which are not disabled in disabledBBC.
		- also handles basic HTML, if enablePostHTML is on.
		- caches the from/to replace regular expressions so as not to reload
		  them every time a string is parsed.
		- only parses smileys if smileys is true.
		- does nothing if the enableBBC setting is off.
		- applies the fixLongWords magic if the setting is set to on.
		- uses the cache_id as a unique identifier to facilitate any caching
		  it may do.
		- returns the modified message.

	void parsesmileys(string &message)
		- the smiley parsing function which makes pretty faces appear :).
		- if custom smiley sets are turned off by smiley_enable, the default
		  set of smileys will be used.
		- these are specifically not parsed in code tags [url=mailto:Dad@blah.com]
		- caches the smileys from the database or array in memory.
		- doesn't return anything, but rather modifies message directly.

	string highlight_php_code(string code)
		- Uses PHP's highlight_code() to highlight PHP syntax
		- does special handling to keep the tabs in the code available.
		- used to parse PHP code from inside [code] and [php] tags.
		- returns the code with highlighted HTML.

	void writeLog(bool force = false)
		// !!!

	void redirectexit(string setLocation = '', bool use_refresh = false)
		// !!!

	void obExit(bool do_header = true, bool do_footer = do_header)
		// !!!

	void adminIndex($area)
		// !!!

	int logAction($action, $extra = array())
		// !!!

	void trackStats($stats = array())
		- caches statistics changes, and flushes them if you pass nothing.
		- if '+' is used as a value, it will be incremented.
		- does not actually commit the changes until the end of the page view.
		- depends on the trackStats setting.

	void spamProtection(string error_type)
		- attempts to protect from spammed messages and the like.
		- takes a $txt index. (not an actual string.)
		- depends on the spamWaitTime setting.

	array url_image_size(string url)
		- uses getimagesize() to determine the size of a file.
		- attempts to connect to the server first so it won't time out.
		- returns false on failure, otherwise the output of getimagesize().

	void determineTopicClass(array &topic_context)
		// !!!

	void setupThemeContext()
		// !!!

	void template_rawdata()
		// !!!

	void template_header()
		// !!!

	void theme_copyright(bool get_it = false)
		// !!!

	void template_footer()
		// !!!

	void db_debug_junk()
		// !!!

	void getAttachmentFilename(string filename, int ID_ATTACH, bool new = true)
		// !!!

	string host_from_ip(string ip_address)
		// !!!

	string create_button(string filename, string alt, string label, bool custom = '')
		// !!!
*/

// Do a query.  Takes care of errors too.
function db_query($db_string, $file, $line)
{
	global $db_cache, $db_count, $db_connection, $db_show_debug, $modSettings;

	// One more query....
	$db_count = !isset($db_count) ? 1 : $db_count + 1;

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
	{
		// Initialize $db_cache if not already initialized.
		if (!isset($db_cache))
			$db_cache = array();

		if (!empty($_SESSION['debug_redirect']))
		{
			$db_cache = array_merge($_SESSION['debug_redirect'], $db_cache);
			$db_count = count($db_cache) + 1;
			$_SESSION['debug_redirect'] = array();
		}

		$db_cache[$db_count]['q'] = $db_string;
		$db_cache[$db_count]['f'] = $file;
		$db_cache[$db_count]['l'] = $line;
		$st = microtime();
	}

	// First, we clean strings out of the query, reduce whitespace, lowercase, and trim - so we can check it over.
	if (empty($modSettings['disableQueryCheck']))
	{
		$clean = '';
		$old_pos = 0;
		$pos = -1;
		while (true)
		{
			$pos = strpos($db_string, '\'', $pos + 1);
			if ($pos === false)
				break;
			$clean .= substr($db_string, $old_pos, $pos - $old_pos);

			while (true)
			{
				$pos1 = strpos($db_string, '\'', $pos + 1);
				$pos2 = strpos($db_string, '\\', $pos + 1);
				if ($pos1 === false)
					break;
				elseif ($pos2 == false || $pos2 > $pos1)
				{
					$pos = $pos1;
					break;
				}

				$pos = $pos2 + 1;
			}
			$clean .= ' %s ';

			$old_pos = $pos + 1;
		}
		$clean .= substr($db_string, $old_pos);
		$clean = trim(strtolower(preg_replace(array('~\s+~s', '~/\*!40001 SQL_NO_CACHE \*/~', '~/\*!40000 USE INDEX \([A-Za-z\_]+?\) \*/~'), array(' ', '', ''), $clean)));

		// We don't use UNION in SMF, at least so far.  But it's useful for injections.
		if (strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		// Comments?  We don't use comments in our queries, we leave 'em outside!
		elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, ';') !== false)
			$fail = true;
		// Trying to change passwords, slow us down, or something?
		elseif (strpos($clean, 'sleep') !== false && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		elseif (strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		// Sub selects?  We don't use those either.
		elseif (preg_match('~\([^)]*?select~s', $clean) != 0)
			$fail = true;

		if (!empty($fail))
		{
			log_error('Hacking attempt...' . "\n" . $db_string, $file, $line);
			fatal_error('Hacking attempt...', false);
		}
	}

	$ret = mysql_query($db_string, $db_connection);
	if ($ret === false && $file !== false)
		$ret = db_error($db_string, $file, $line);

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
		$db_cache[$db_count]['t'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));

	return $ret;
}

function db_affected_rows()
{
	global $db_connection;

	return mysql_affected_rows($db_connection);
}

function db_insert_id()
{
	global $db_connection;

	return mysql_insert_id($db_connection);
}

// Update some basic statistics...
function updateStats($type, $parameter1 = null, $parameter2 = null)
{
	global $db_prefix, $sourcedir, $modSettings;

	switch ($type)
	{
	case 'member':
		$changes = array(
			'memberlist_updated' => time(),
		);

		// Are we using registration approval?
		if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 2)
		{
			// Update the latest activated member (highest ID_MEMBER) and count.
			$result = db_query("
				SELECT COUNT(*), MAX(ID_MEMBER)
				FROM {$db_prefix}members
				WHERE is_activated = 1", __FILE__, __LINE__);
			list ($changes['totalMembers'], $changes['latestMember']) = mysql_fetch_row($result);
			mysql_free_result($result);

			// Get the latest activated member's display name.
			$result = db_query("
				SELECT realName
				FROM {$db_prefix}members
				WHERE ID_MEMBER = " . (int) $changes['latestMember'] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($changes['latestRealName']) = mysql_fetch_row($result);
			mysql_free_result($result);

			// Update the amount of members awaiting approval - ignoring COPPA accounts, as you can't approve them until you get permission.
			$result = db_query("
				SELECT COUNT(*)
				FROM {$db_prefix}members
				WHERE is_activated IN (3, 4)", __FILE__, __LINE__);
			list ($changes['unapprovedMembers']) = mysql_fetch_row($result);
			mysql_free_result($result);
		}
		// If $parameter1 is a number, it's the new ID_MEMBER and #2 is the real name for a new registration.
		elseif ($parameter1 !== null && $parameter1 !== false)
		{
			$changes['latestMember'] = $parameter1;
			$changes['latestRealName'] = $parameter2;

			updateSettings(array('totalMembers' => true), true);
		}
		// If $parameter1 is false, and approval is off, we need change nothing.
		elseif ($parameter1 !== false)
		{
			// Update the latest member (highest ID_MEMBER) and count.
			$result = db_query("
				SELECT COUNT(*), MAX(ID_MEMBER)
				FROM {$db_prefix}members", __FILE__, __LINE__);
			list ($changes['totalMembers'], $changes['latestMember']) = mysql_fetch_row($result);
			mysql_free_result($result);

			// Get the latest member's display name.
			$result = db_query("
				SELECT realName
				FROM {$db_prefix}members
				WHERE ID_MEMBER = " . (int) $changes['latestMember'] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($changes['latestRealName']) = mysql_fetch_row($result);
			mysql_free_result($result);
		}

		updateSettings($changes);
		break;

	case 'message':
		if ($parameter1 === true && $parameter2 !== null)
			updateSettings(array('totalMessages' => true, 'maxMsgID' => $parameter2), true);
		else
		{
			// SUM and MAX on a smaller table is better for InnoDB tables.
			$result = db_query("
				SELECT SUM(numPosts) AS totalMessages, MAX(ID_LAST_MSG) AS maxMsgID
				FROM {$db_prefix}boards", __FILE__, __LINE__);
			$row = mysql_fetch_assoc($result);
			mysql_free_result($result);

			updateSettings(array(
				'totalMessages' => $row['totalMessages'],
				'maxMsgID' => $row['maxMsgID'] === null ? 0 : $row['maxMsgID']
			));
		}
		break;

	case 'subject':
		// Remove the previous subject (if any).
		db_query("
			DELETE FROM {$db_prefix}log_search_subjects
			WHERE ID_TOPIC = " . (int) $parameter1, __FILE__, __LINE__);

		// Insert the new subject.
		if ($parameter2 !== null)
		{
			$parameter1 = (int) $parameter1;
			$parameter2 = text2words($parameter2);

			$inserts = array();
			foreach ($parameter2 as $word)
				$inserts[] = "'$word', $parameter1";

			if (!empty($inserts))
				db_query("
					INSERT IGNORE INTO {$db_prefix}log_search_subjects
						(word, ID_TOPIC)
					VALUES (" . implode('),
						(', array_unique($inserts)) . ")", __FILE__, __LINE__);
		}
		break;

	case 'topic':
		if ($parameter1 === true)
			updateSettings(array('totalTopics' => true), true);
		else
		{
			// Get the number of topics - a SUM is better for InnoDB tables.
			// We also ignore the recycle bin here because there will probably be a bunch of one-post topics there.
			$result = db_query("
				SELECT SUM(numTopics) AS totalTopics
				FROM {$db_prefix}boards" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
				WHERE ID_BOARD != $modSettings[recycle_board]" : ''), __FILE__, __LINE__);
			$row = mysql_fetch_assoc($result);
			mysql_free_result($result);

			updateSettings(array('totalTopics' => $row['totalTopics']));
		}
		break;

	case 'calendar':
		require_once($sourcedir . '/Calendar.php');

		// Calculate the YYYY-MM-DD of the lowest and highest days.
		$low_date = strftime('%Y-%m-%d', forum_time(false) - 24 * 3600);
		$high_date = strftime('%Y-%m-%d', forum_time(false) + $modSettings['cal_days_for_index'] * 24 * 3600);

		$holidays = calendarHolidayArray($low_date, $high_date);
		$bday = calendarBirthdayArray($low_date, $high_date);
		$events = calendarEventArray($low_date, $high_date, false);

		// Cache the results in the settings.
		updateSettings(array(
			'cal_today_updated' => strftime('%Y%m%d', forum_time(false)),
			'cal_today_holiday' => addslashes(serialize($holidays)),
			'cal_today_birthday' => addslashes(serialize($bday)),
			'cal_today_event' => addslashes(serialize($events))
		));
		break;

	case 'postgroups':
		// Parameter two is the updated columns: we should check to see if we base groups off any of these.
		if ($parameter2 !== null && !in_array('posts', $parameter2))
			return;

		if (($postgroups = cache_get_data('updateStats:postgroups', 360)) == null)
		{
			// Fetch the postgroups!
			$request = db_query("
				SELECT ID_GROUP, minPosts
				FROM {$db_prefix}membergroups
				WHERE minPosts != -1", __FILE__, __LINE__);
			$postgroups = array();
			while ($row = mysql_fetch_assoc($request))
				$postgroups[$row['ID_GROUP']] = $row['minPosts'];
			mysql_free_result($request);

			// Sort them this way because if it's done with MySQL it causes a filesort :(.
			arsort($postgroups);

			cache_put_data('updateStats:postgroups', $postgroups, 360);
		}

		// Oh great, they've screwed their post groups.
		if (empty($postgroups))
			return;

		// Set all membergroups from most posts to least posts.
		$conditions = '';
		foreach ($postgroups as $id => $minPosts)
		{
			$conditions .= '
					WHEN posts >= ' . $minPosts . (!empty($lastMin) ? ' AND posts <= ' . $lastMin : '') . ' THEN ' . $id;
			$lastMin = $minPosts;
		}

		// A big fat CASE WHEN... END is faster than a zillion UPDATE's ;).
		db_query("
			UPDATE {$db_prefix}members
			SET ID_POST_GROUP = CASE$conditions
					ELSE 0
				END" . ($parameter1 != null ? "
			WHERE $parameter1" : ''), __FILE__, __LINE__);
		break;

		default:
			trigger_error('updateStats(): Invalid statistic type \'' . $type . '\'', E_USER_NOTICE);
	}
}

// Assumes the data has been slashed.
function updateMemberData($members, $data)
{
	global $db_prefix, $modSettings, $ID_MEMBER, $user_info;

	if (is_array($members))
		$condition = 'ID_MEMBER IN (' . implode(', ', $members) . ')
		LIMIT ' . count($members);
	elseif ($members === null)
		$condition = '1';
	else
		$condition = 'ID_MEMBER = ' . $members . '
		LIMIT 1';

	if (isset($modSettings['integrate_change_member_data']) && function_exists($modSettings['integrate_change_member_data']))
	{
		// Only a few member variables are really interesting for integration.
		$integration_vars = array(
			'memberName',
			'realName',
			'emailAddress',
			'ID_GROUP',
			'gender',
			'birthdate',
			'websiteTitle',
			'websiteUrl',
			'location',
			'hideEmail',
			'timeFormat',
			'timeOffset',
			'avatar',
			'lngfile',
		);
		$vars_to_integrate = array_intersect($integration_vars, array_keys($data));

		// Only proceed if there are any variables left to call the integration function.
		if (count($vars_to_integrate) != 0)
		{
			// Fetch a list of memberNames if necessary
			if ((!is_array($members) && $members === $ID_MEMBER) || (is_array($members) && count($members) == 1 && in_array($ID_MEMBER, $members)))
				$memberNames = array($user_info['username']);
			else
			{
				$memberNames = array();
				$request = db_query("
					SELECT memberName
					FROM {$db_prefix}members
					WHERE $condition", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
					$memberNames[] = $row['memberName'];
				mysql_free_result($request);
			}

			if (!empty($memberNames))
				foreach ($vars_to_integrate as $var)
					call_user_func($modSettings['integrate_change_member_data'], $memberNames, $var, stripslashes($data[$var]));
		}
	}

	foreach ($data as $var => $val)
	{
		if ($val === '+')
			$data[$var] = $var . ' + 1';
		elseif ($val === '-')
			$data[$var] = $var . ' - 1';
	}

	// Ensure posts, instantMessages, and unreadMessages never go below 0.
	foreach(array('posts', 'instantMessages', 'unreadMessages') as $type)
		if (isset($data[$type]) && preg_match('~^' . $type . ' - ([\d]+)~', $data[$type], $match) === 1)
			$data[$type] = 'CASE WHEN ' . $type . ' <= ' . $match[1] . ' THEN 0 ELSE ' . $data[$type] . ' END';

	$setString = '';
	foreach ($data as $var => $val)
	{
		$setString .= "
			$var = $val,";
	}

	db_query("
		UPDATE {$db_prefix}members
		SET" . substr($setString, 0, -1) . '
		WHERE ' . $condition, __FILE__, __LINE__);

	updateStats('postgroups', $condition, array_keys($data));

	// Clear any caching?
	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2 && !empty($members))
	{
		if (!is_array($members))
			$members = array($members);

		foreach ($members as $member)
		{
			if ($modSettings['cache_enable'] == 3)
			{
				cache_put_data('member_data-profile-' . $member, null, 120);
				cache_put_data('member_data-normal-' . $member, null, 120);
				cache_put_data('member_data-minimal-' . $member, null, 120);
			}
			cache_put_data('user_settings-' . $member, null, 60);
		}
	}
}

// Updates the settings table as well as $modSettings... only does one at a time if $update is true.
// All input variables and values are assumed to have escaped apostrophes(')!
function updateSettings($changeArray, $update = false)
{
	global $db_prefix, $modSettings;

	if (empty($changeArray) || !is_array($changeArray))
		return;

	// In some cases, this may be better and faster, but for large sets we don't want so many UPDATEs.
	if ($update)
	{
		foreach ($changeArray as $variable => $value)
		{
			db_query("
				UPDATE {$db_prefix}settings
				SET value = " . ($value === true ? 'value + 1' : ($value === false ? 'value - 1' : "'$value'")) . "
				WHERE variable = '$variable'
				LIMIT 1", __FILE__, __LINE__);
			$modSettings[$variable] = $value === true ? $modSettings[$variable] + 1 : ($value === false ? $modSettings[$variable] - 1 : stripslashes($value));
		}

		// Clean out the cache and make sure the cobwebs are gone too.
		cache_put_data('modSettings', null, 90);

		return;
	}

	$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{
		// Don't bother if it's already like that ;).
		if (isset($modSettings[$variable]) && $modSettings[$variable] == stripslashes($value))
			continue;
		// If the variable isn't set, but would only be set to nothing'ness, then don't bother setting it.
		elseif (!isset($modSettings[$variable]) && empty($value))
			continue;

		$replaceArray[] = "(SUBSTRING('$variable', 1, 255), SUBSTRING('$value', 1, 65534))";
		$modSettings[$variable] = stripslashes($value);
	}

	if (empty($replaceArray))
		return;

	db_query("
		REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES " . implode(',
			', $replaceArray), __FILE__, __LINE__);

	// Kill the cache - it needs redoing now, but we won't bother ourselves with that here.
	cache_put_data('modSettings', null, 90);
}

// Constructs a page list.
// $pageindex = constructPageIndex($scripturl . '?board=' . $board, $_REQUEST['start'], $num_messages, $maxindex, true);
function constructPageIndex($base_url, &$start, $max_value, $num_per_page, $flexible_start = false)
{
	global $modSettings;

	// Save whether $start was less than 0 or not.
	$start = (int) $start;
	$start_invalid = $start < 0;

	// Make sure $start is a proper variable - not less than 0.
	if ($start_invalid)
		$start = 0;
	// Not greater than the upper bound.
	elseif ($start >= $max_value)
		$start = max(0, (int) $max_value - (((int) $max_value % (int) $num_per_page) == 0 ? $num_per_page : ((int) $max_value % (int) $num_per_page)));
	// And it has to be a multiple of $num_per_page!
	else
		$start = max(0, (int) $start - ((int) $start % (int) $num_per_page));

	// Wireless will need the protocol on the URL somewhere.
	if (WIRELESS)
		$base_url .= ';' . WIRELESS_PROTOCOL;

	$base_link = '<a class="navPages" href="' . ($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . ';start=%d') . '">%s</a> ';

	// Compact pages is off or on?
	if (empty($modSettings['compactTopicPagesEnable']))
	{
		// Show the left arrow.
		$pageindex = $start == 0 ? ' ' : sprintf($base_link, $start - $num_per_page, '&#171;');

		// Show all the pages.
		$display_page = 1;
		for ($counter = 0; $counter < $max_value; $counter += $num_per_page)
			$pageindex .= $start == $counter && !$start_invalid ? '<b>' . $display_page++ . '</b> ' : sprintf($base_link, $counter, $display_page++);

		// Show the right arrow.
		$display_page = ($start + $num_per_page) > $max_value ? $max_value : ($start + $num_per_page);
		if ($start != $counter - $max_value && !$start_invalid)
			$pageindex .= $display_page > $counter - $num_per_page ? ' ' : sprintf($base_link, $display_page, '&#187;');
	}
	else
	{
		// If they didn't enter an odd value, pretend they did.
		$PageContiguous = (int) ($modSettings['compactTopicPagesContiguous'] - ($modSettings['compactTopicPagesContiguous'] % 2)) / 2;

		// Show the first page. (>1< ... 6 7 [8] 9 10 ... 15)
		if ($start > $num_per_page * $PageContiguous)
			$pageindex = sprintf($base_link, 0, '1');
		else
			$pageindex = '';

		// Show the ... after the first page.  (1 >...< 6 7 [8] 9 10 ... 15)
		if ($start > $num_per_page * ($PageContiguous + 1))
			$pageindex .= '<b> ... </b>';

		// Show the pages before the current one. (1 ... >6 7< [8] 9 10 ... 15)
		for ($nCont = $PageContiguous; $nCont >= 1; $nCont--)
			if ($start >= $num_per_page * $nCont)
			{
				$tmpStart = $start - $num_per_page * $nCont;
				$pageindex.= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
			}

		// Show the current page. (1 ... 6 7 >[8]< 9 10 ... 15)
		if (!$start_invalid)
			$pageindex .= '[<b>' . ($start / $num_per_page + 1) . '</b>] ';
		else
			$pageindex .= sprintf($base_link, $start, $start / $num_per_page + 1);

		// Show the pages after the current one... (1 ... 6 7 [8] >9 10< ... 15)
		$tmpMaxPages = (int) (($max_value - 1) / $num_per_page) * $num_per_page;
		for ($nCont = 1; $nCont <= $PageContiguous; $nCont++)
			if ($start + $num_per_page * $nCont <= $tmpMaxPages)
			{
				$tmpStart = $start + $num_per_page * $nCont;
				$pageindex .= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
			}

		// Show the '...' part near the end. (1 ... 6 7 [8] 9 10 >...< 15)
		if ($start + $num_per_page * ($PageContiguous + 1) < $tmpMaxPages)
			$pageindex .= '<b> ... </b>';

		// Show the last number in the list. (1 ... 6 7 [8] 9 10 ... >15<)
		if ($start + $num_per_page * $PageContiguous < $tmpMaxPages)
			$pageindex .= sprintf($base_link, $tmpMaxPages, $tmpMaxPages / $num_per_page + 1);
	}

	return $pageindex;
}

// Formats a number to display in the style of the admin's choosing.
function comma_format($number, $override_decimal_count = false)
{
	global $modSettings;
	static $thousands_separator = null, $decimal_separator = null, $decimal_count = null;

	// !!! Should, perhaps, this just be handled in the language files, and not a mod setting?
	// (French uses 1 234,00 for example... what about a multilingual forum?)

	// Cache these values...
	if ($decimal_separator === null)
	{
		// Not set for whatever reason?
		if (empty($modSettings['number_format']) || preg_match('~^1([^\d]*)?234([^\d]*)(0*?)$~', $modSettings['number_format'], $matches) != 1)
			return $number;

		// Cache these each load...
		$thousands_separator = $matches[1];
		$decimal_separator = $matches[2];
		$decimal_count = strlen($matches[3]);
	}

	// Format the string with our friend, number_format.
	return number_format($number, is_float($number) ? ($override_decimal_count === false ? $decimal_count : $override_decimal_count) : 0, $decimal_separator, $thousands_separator);
}

// Format a time to make it look purdy.
function timeformat($logTime, $show_today = true)
{
	global $user_info, $txt, $db_prefix, $modSettings, $func;

	// Offset the time.
	$time = $logTime + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600;

	// We can't have a negative date (on Windows, at least.)
	if ($time < 0)
		$time = 0;

	// Today and Yesterday?
	if ($modSettings['todayMod'] >= 1 && $show_today === true)
	{
		// Get the current time.
		$nowtime = forum_time();

		$then = @getdate($time);
		$now = @getdate($nowtime);

		// Try to make something of a time format string...
		$s = strpos($user_info['time_format'], '%S') === false ? '' : ':%S';
		if (strpos($user_info['time_format'], '%H') === false && strpos($user_info['time_format'], '%T') === false)
			$today_fmt = '%I:%M' . $s . ' %p';
		else
			$today_fmt = '%H:%M' . $s;

		// Same day of the year, same year.... Today!
		if ($then['yday'] == $now['yday'] && $then['year'] == $now['year'])
			return $txt['smf10'] . timeformat($logTime, $today_fmt);

		// Day-of-year is one less and same year, or it's the first of the year and that's the last of the year...
		if ($modSettings['todayMod'] == '2' && (($then['yday'] == $now['yday'] - 1 && $then['year'] == $now['year']) || ($now['yday'] == 0 && $then['year'] == $now['year'] - 1) && $then['mon'] == 12 && $then['mday'] == 31))
			return $txt['smf10b'] . timeformat($logTime, $today_fmt);
	}

	$str = !is_bool($show_today) ? $show_today : $user_info['time_format'];

	if (setlocale(LC_TIME, $txt['lang_locale']))
	{
		foreach (array('%a', '%A', '%b', '%B') as $token)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, $func['ucwords'](strftime($token, $time)), $str);
	}
	else
	{
		// Do-it-yourself time localization.  Fun.
		foreach (array('%a' => 'days_short', '%A' => 'days', '%b' => 'months_short', '%B' => 'months') as $token => $text_label)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, $txt[$text_label][(int) strftime($token === '%a' || $token === '%A' ? '%w' : '%m', $time)], $str);
		if (strpos($str, '%p'))
			$str = str_replace('%p', (strftime('%H', $time) < 12 ? 'am' : 'pm'), $str);
	}

	// Format any other characters..
	return strftime($str, $time);
}

// Removes special entities from strings.  Compatibility...
function un_htmlspecialchars($string)
{
	return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)) + array('&#039;' => '\'', '&nbsp;' => ' '));
}

if (!function_exists('stripos'))
{
	function stripos($haystack, $needle, $offset = 0)
	{
		return strpos(strtolower($haystack), strtolower($needle), $offset);
	}
}

// Shorten a subject + internationalization concerns.
function shorten_subject($subject, $len)
{
	global $func;

	// It was already short enough!
	if ($func['strlen']($subject) <= $len)
		return $subject;

	// Shorten it by the length it was too long, and strip off junk from the end.
	return $func['substr']($subject, 0, $len) . '...';
}

// The current time with offset.
function forum_time($use_user_offset = true, $timestamp = null)
{
	global $user_info, $modSettings;

	if ($timestamp === null)
		$timestamp = time();
	elseif ($timestamp == 0)
		return 0;

	return $timestamp + ($modSettings['time_offset'] + ($use_user_offset ? $user_info['time_offset'] : 0)) * 3600;
}

// This gets all possible permutations of an array.
function permute($array)
{
	$orders = array($array);

	$n = count($array);
	$p = range(0, $n);
	for ($i = 1; $i < $n; null)
	{
		$p[$i]--;
		$j = $i % 2 != 0 ? $p[$i] : 0;

		$temp = $array[$i];
		$array[$i] = $array[$j];
		$array[$j] = $temp;

		for ($i = 1; $p[$i] == 0; $i++)
			$p[$i] = 1;

		$orders[] = $array;
	}

	return $orders;
}

// For old stuff still using doUBBC()...
function doUBBC($message, $enableSmileys = true)
{
	return parse_bbc($message, $enableSmileys);
}

// Parse bulletin board code in a string, as well as smileys optionally.
function parse_bbc($message, $smileys = true, $cache_id = '')
{
	global $txt, $scripturl, $context, $modSettings, $user_info;
	static $bbc_codes = array(), $itemcodes = array(), $no_autolink_tags = array();
	static $disabled;

	// Never show smileys for wireless clients.  More bytes, can't see it anyway :P.
	if (WIRELESS)
		$smileys = false;
	elseif ($smileys !== null && ($smileys == '1' || $smileys == '0'))
		$smileys = (bool) $smileys;

	if (empty($modSettings['enableBBC']) && $message !== false)
	{
		if ($smileys === true)
			parsesmileys($message);

		return $message;
	}

	// Just in case it wasn't determined yet whether UTF-8 is enabled.
	if (!isset($context['utf8']))
		$context['utf8'] = (empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8';

	// Sift out the bbc for a performance improvement.
	if (empty($bbc_codes) || $message === false)
	{
		if (!empty($modSettings['disabledBBC']))
		{
			$temp = explode(',', strtolower($modSettings['disabledBBC']));

			foreach ($temp as $tag)
				$disabled[trim($tag)] = true;
		}

		if (empty($modSettings['enableEmbeddedFlash']))
			$disabled['flash'] = true;

		/* The following bbc are formatted as an array, with keys as follows:

			tag: the tag's name - should be lowercase!

			type: one of...
				- (missing): [tag]parsed content[/tag]
				- unparsed_equals: [tag=xyz]parsed content[/tag]
				- parsed_equals: [tag=parsed data]parsed content[/tag]
				- unparsed_content: [tag]unparsed content[/tag]
				- closed: [tag], [tag/], [tag /]
				- unparsed_commas: [tag=1,2,3]parsed content[/tag]
				- unparsed_commas_content: [tag=1,2,3]unparsed content[/tag]
				- unparsed_equals_content: [tag=...]unparsed content[/tag]

			parameters: an optional array of parameters, for the form
			  [tag abc=123]content[/tag].  The array is an associative array
			  where the keys are the parameter names, and the values are an
			  array which may contain the following:
				- match: a regular expression to validate and match the value.
				- quoted: true if the value should be quoted.
				- validate: callback to evaluate on the data, which is $data.
				- value: a string in which to replace $1 with the data.
				  either it or validate may be used, not both.
				- optional: true if the parameter is optional.

			test: a regular expression to test immediately after the tag's
			  '=', ' ' or ']'.  Typically, should have a \] at the end.
			  Optional.

			content: only available for unparsed_content, closed,
			  unparsed_commas_content, and unparsed_equals_content.
			  $1 is replaced with the content of  the tag.  Parameters
			  are repalced in the form {param}.  For unparsed_commas_content,
			  $2, $3, ..., $n are replaced.

			before: only when content is not used, to go before any
			  content.  For unparsed_equals, $1 is replaced with the value.
			  For unparsed_commas, $1, $2, ..., $n are replaced.

			after: similar to before in every way, except that it is used
			  when the tag is closed.

			disabled_content: used in place of content when the tag is
			  disabled.  For closed, default is '', otherwise it is '$1' if
			  block_level is false, '<div>$1</div>' elsewise.

			disabled_before: used in place of before when disabled.  Defaults
			  to '<div>' if block_level, '' if not.

			disabled_after: used in place of after when disabled.  Defaults
			  to '</div>' if block_level, '' if not.

			block_level: set to true the tag is a "block level" tag, similar
			  to HTML.  Block level tags cannot be nested inside tags that are
			  not block level, and will not be implicitly closed as easily.
			  One break following a block level tag may also be removed.

			trim: if set, and 'inside' whitespace after the begin tag will be
			  removed.  If set to 'outside', whitespace after the end tag will
			  meet the same fate.

			validate: except when type is missing or 'closed', a callback to
			  validate the data as $data.  Depending on the tag's type, $data
			  may be a string or an array of strings (corresponding to the
			  replacement.)

			quoted: when type is 'unparsed_equals' or 'parsed_equals' only,
			  may be not set, 'optional', or 'required' corresponding to if
			  the content may be quoted.  This allows the parser to read
			  [tag="abc]def[esdf]"] properly.

			require_parents: an array of tag names, or not set.  If set, the
			  enclosing tag *must* be one of the listed tags, or parsing won't
			  occur.

			require_children: similar to require_parents, if set children
			  won't be parsed if they are not in the list.

			disallow_children: similar to, but very different from,
			  require_children, if it is set the listed tags will not be
			  parsed inside the tag.
		*/

		$codes = array(
			array(
				'tag' => 'abbr',
				'type' => 'unparsed_equals',
				'before' => '<abbr title="$1">',
				'after' => '</abbr>',
				'quoted' => 'optional',
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'acronym',
				'type' => 'unparsed_equals',
				'before' => '<acronym title="$1">',
				'after' => '</acronym>',
				'quoted' => 'optional',
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'anchor',
				'type' => 'unparsed_equals',
				'test' => '[#]?([A-Za-z][A-Za-z0-9_\-]*)\]',
				'before' => '<span id="post_$1" />',
				'after' => '',
			),
			array(
				'tag' => 'b',
				'before' => '<b>',
				'after' => '</b>',
			),
			array(
				'tag' => 'black',
				'before' => '<span style="color: black;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'blue',
				'before' => '<span style="color: blue;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'br',
				'type' => 'closed',
				'content' => '<br />',
			),
			array(
				'tag' => 'code',
				'type' => 'unparsed_content',
				'content' => '<div class="codeheader">' . $txt['smf238'] . ':</div><div class="code">' . ($context['browser']['is_gecko'] ? '<pre style="margin-top: 0; display: inline;">$1</pre>' : '$1') . '</div>',
				// !!! Maybe this can be simplified?
				'validate' => isset($disabled['code']) ? null : create_function('&$tag, &$data, $disabled', '
					global $context;

					if (!isset($disabled[\'code\']))
					{
						$php_parts = preg_split(\'~(&lt;\?php|\?&gt;)~\', $data, -1, PREG_SPLIT_DELIM_CAPTURE);

						for ($php_i = 0, $php_n = count($php_parts); $php_i < $php_n; $php_i++)
						{
							// Do PHP code coloring?
							if ($php_parts[$php_i] != \'&lt;?php\')
								continue;

							$php_string = \'\';
							while ($php_i + 1 < count($php_parts) && $php_parts[$php_i] != \'?&gt;\')
							{
								$php_string .= $php_parts[$php_i];
								$php_parts[$php_i++] = \'\';
							}
							$php_parts[$php_i] = highlight_php_code($php_string . $php_parts[$php_i]);
						}

						// Fix the PHP code stuff...
						$data = str_replace("<pre style=\"display: inline;\">\t</pre>", "\t", implode(\'\', $php_parts));

						// Older browsers are annoying, aren\'t they?
						if ($context[\'browser\'][\'is_ie4\'] || $context[\'browser\'][\'is_ie5\'] || $context[\'browser\'][\'is_ie5.5\'])
							$data = str_replace("\t", "<pre style=\"display: inline;\">\t</pre>", $data);
						elseif (!$context[\'browser\'][\'is_gecko\'])
							$data = str_replace("\t", "<span style=\"white-space: pre;\">\t</span>", $data);
					}'),
				'block_level' => true,
			),
			array(
				'tag' => 'code',
				'type' => 'unparsed_equals_content',
				'content' => '<div class="codeheader">' . $txt['smf238'] . ': ($2)</div><div class="code">' . ($context['browser']['is_gecko'] ? '<pre style="margin-top: 0; display: inline;">$1</pre>' : '$1') . '</div>',
				// !!! Maybe this can be simplified?
				'validate' => isset($disabled['code']) ? null : create_function('&$tag, &$data, $disabled', '
					global $context;

					if (!isset($disabled[\'code\']))
					{
						$php_parts = preg_split(\'~(&lt;\?php|\?&gt;)~\', $data[0], -1, PREG_SPLIT_DELIM_CAPTURE);

						for ($php_i = 0, $php_n = count($php_parts); $php_i < $php_n; $php_i++)
						{
							// Do PHP code coloring?
							if ($php_parts[$php_i] != \'&lt;?php\')
								continue;

							$php_string = \'\';
							while ($php_i + 1 < count($php_parts) && $php_parts[$php_i] != \'?&gt;\')
							{
								$php_string .= $php_parts[$php_i];
								$php_parts[$php_i++] = \'\';
							}
							$php_parts[$php_i] = highlight_php_code($php_string . $php_parts[$php_i]);
						}

						// Fix the PHP code stuff...
						$data[0] = str_replace("<pre style=\"display: inline;\">\t</pre>", "\t", implode(\'\', $php_parts));

						// Older browsers are annoying, aren\'t they?
						if ($context[\'browser\'][\'is_ie4\'] || $context[\'browser\'][\'is_ie5\'] || $context[\'browser\'][\'is_ie5.5\'])
							$data = str_replace("\t", "<pre style=\"display: inline;\">\t</pre>", $data);
						elseif (!$context[\'browser\'][\'is_gecko\'])
							$data = str_replace("\t", "<span style=\"white-space: pre;\">\t</span>", $data);
					}'),
				'block_level' => true,
			),
			array(
				'tag' => 'center',
				'before' => '<div align="center">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'color',
				'type' => 'unparsed_equals',
				'test' => '(#[\da-fA-F]{3}|#[\da-fA-F]{6}|[A-Za-z]{1,12})\]',
				'before' => '<span style="color: $1;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'email',
				'type' => 'unparsed_content',
				'content' => '<a href="mailto:$1">$1</a>',
				// !!! Should this respect guest_hideContacts?
				'validate' => create_function('&$tag, &$data, $disabled', '$data = strtr($data, array(\'<br />\' => \'\'));'),
			),
			array(
				'tag' => 'email',
				'type' => 'unparsed_equals',
				'before' => '<a href="mailto:$1">',
				'after' => '</a>',
				// !!! Should this respect guest_hideContacts?
				'disallow_children' => array('email', 'ftp', 'url', 'iurl'),
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'ftp',
				'type' => 'unparsed_content',
				'content' => '<a href="$1" target="_blank">$1</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					$data = strtr($data, array(\'<br />\' => \'\'));
					if (strpos($data, \'ftp://\') !== 0 && strpos($data, \'ftps://\') !== 0)
						$data = \'ftp://\' . $data;
				'),
			),
			array(
				'tag' => 'ftp',
				'type' => 'unparsed_equals',
				'before' => '<a href="$1" target="_blank">',
				'after' => '</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					if (strpos($data, \'ftp://\') !== 0 && strpos($data, \'ftps://\') !== 0)
						$data = \'ftp://\' . $data;
				'),
				'disallow_children' => array('email', 'ftp', 'url', 'iurl'),
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'font',
				'type' => 'unparsed_equals',
				'test' => '[A-Za-z0-9_,\-\s]+?\]',
				'before' => '<span style="font-family: $1;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'flash',
				'type' => 'unparsed_commas_content',
				'test' => '\d+,\d+\]',
				'content' => ($context['browser']['is_ie'] && !$context['browser']['is_mac_ie'] ? '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="$2" height="$3"><param name="movie" value="$1" /><param name="play" value="true" /><param name="loop" value="true" /><param name="quality" value="high" /><param name="AllowScriptAccess" value="never" /><embed src="$1" width="$2" height="$3" play="true" loop="true" quality="high" AllowScriptAccess="never" /><noembed><a href="$1" target="_blank">$1</a></noembed></object>' : '<embed type="application/x-shockwave-flash" src="$1" width="$2" height="$3" play="true" loop="true" quality="high" AllowScriptAccess="never" /><noembed><a href="$1" target="_blank">$1</a></noembed>'),
				'validate' => create_function('&$tag, &$data, $disabled', '
					if (isset($disabled[\'url\']))
						$tag[\'content\'] = \'$1\';
					elseif (strpos($data[0], \'http://\') !== 0 && strpos($data[0], \'https://\') !== 0)
						$data[0] = \'http://\' . $data[0];
				'),
				'disabled_content' => '<a href="$1" target="_blank">$1</a>',
			),
			array(
				'tag' => 'green',
				'before' => '<span style="color: green;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'glow',
				'type' => 'unparsed_commas',
				'test' => '[#0-9a-zA-Z\-]{3,12},([012]\d{1,2}|\d{1,2})(,[^]]+)?\]',
				'before' => $context['browser']['is_ie'] ? '<table border="0" cellpadding="0" cellspacing="0" style="display: inline; vertical-align: middle; font: inherit;"><tr><td style="filter: Glow(color=$1, strength=$2); font: inherit;">' : '<span style="background-color: $1;">',
				'after' => $context['browser']['is_ie'] ? '</td></tr></table> ' : '</span>',
			),
			array(
				'tag' => 'hr',
				'type' => 'closed',
				'content' => '<hr />',
				'block_level' => true,
			),
			array(
				'tag' => 'html',
				'type' => 'unparsed_content',
				'content' => '$1',
				'block_level' => true,
				'disabled_content' => '$1',
			),
			array(
				'tag' => 'img',
				'type' => 'unparsed_content',
				'parameters' => array(
					'alt' => array('optional' => true),
					'width' => array('optional' => true, 'value' => ' width="$1"', 'match' => '(\d+)'),
					'height' => array('optional' => true, 'value' => ' height="$1"', 'match' => '(\d+)'),
				),
				'content' => '<img src="$1" alt="{alt}"{width}{height} border="0" />',
				'validate' => create_function('&$tag, &$data, $disabled', '
					$data = strtr($data, array(\'<br />\' => \'\'));
					if (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
				'disabled_content' => '($1)',
			),
			array(
				'tag' => 'img',
				'type' => 'unparsed_content',
				'content' => '<img src="$1" alt="" border="0" />',
				'validate' => create_function('&$tag, &$data, $disabled', '
					$data = strtr($data, array(\'<br />\' => \'\'));
					if (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
				'disabled_content' => '($1)',
			),
			array(
				'tag' => 'i',
				'before' => '<i>',
				'after' => '</i>',
			),
			array(
				'tag' => 'iurl',
				'type' => 'unparsed_content',
				'content' => '<a href="$1">$1</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					$data = strtr($data, array(\'<br />\' => \'\'));
					if (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
			),
			array(
				'tag' => 'iurl',
				'type' => 'unparsed_equals',
				'before' => '<a href="$1">',
				'after' => '</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					if (substr($data, 0, 1) == \'#\')
						$data = \'#post_\' . substr($data, 1);
					elseif (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
				'disallow_children' => array('email', 'ftp', 'url', 'iurl'),
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'li',
				'before' => '<li>',
				'after' => '</li>',
				'trim' => 'outside',
				'require_parents' => array('list'),
				'block_level' => true,
				'disabled_before' => '',
				'disabled_after' => '<br />',
			),
			array(
				'tag' => 'list',
				'before' => '<ul style="margin-top: 0; margin-bottom: 0;">',
				'after' => '</ul>',
				'trim' => 'inside',
				'require_children' => array('li'),
				'block_level' => true,
			),
			array(
				'tag' => 'list',
				'parameters' => array(
					'type' => array('match' => '(none|disc|circle|square|decimal|decimal-leading-zero|lower-roman|upper-roman|lower-alpha|upper-alpha|lower-greek|lower-latin|upper-latin|hebrew|armenian|georgian|cjk-ideographic|hiragana|katakana|hiragana-iroha|katakana-iroha)'),
				),
				'before' => '<ul style="margin-top: 0; margin-bottom: 0; list-style-type: {type};">',
				'after' => '</ul>',
				'trim' => 'inside',
				'require_children' => array('li'),
				'block_level' => true,
			),
			array(
				'tag' => 'left',
				'before' => '<div style="text-align: left;">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'ltr',
				'before' => '<div dir="ltr">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'me',
				'type' => 'unparsed_equals',
				'before' => '<div class="meaction">* $1 ',
				'after' => '</div>',
				'quoted' => 'optional',
				'block_level' => true,
				'disabled_before' => '/me ',
				'disabled_after' => '<br />',
			),
			array(
				'tag' => 'move',
				'before' => '<marquee>',
				'after' => '</marquee>',
				'block_level' => true,
			),
			array(
				'tag' => 'nobbc',
				'type' => 'unparsed_content',
				'content' => '$1',
			),
			array(
				'tag' => 'pre',
				'before' => '<pre>',
				'after' => '</pre>',
			),
			array(
				'tag' => 'php',
				'type' => 'unparsed_content',
				'content' => '<div class="phpcode">$1</div>',
				'validate' => isset($disabled['php']) ? null : create_function('&$tag, &$data, $disabled', '
					if (!isset($disabled[\'php\']))
					{
						$add_begin = substr(trim($data), 0, 5) != \'&lt;?\';
						$data = highlight_php_code($add_begin ? \'&lt;?php \' . $data . \'?&gt;\' : $data);
						if ($add_begin)
							$data = preg_replace(array(\'~^(.+?)&lt;\?.{0,40}?php(&nbsp;|\s)~\', \'~\?&gt;((?:</(font|span)>)*)$~\'), \'$1\', $data, 2);
					}'),
				'block_level' => true,
				'disabled_content' => '$1',
			),
			array(
				'tag' => 'quote',
				'before' => '<div class="quoteheader">' . $txt['smf240'] . '</div><div class="quote">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'quote',
				'parameters' => array(
					'author' => array('match' => '(.{1,192}?)', 'quoted' => true, 'validate' => 'parse_bbc'),
				),
				'before' => '<div class="quoteheader">' . $txt['smf239'] . ': {author}</div><div class="quote">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'quote',
				'type' => 'parsed_equals',
				'before' => '<div class="quoteheader">' . $txt['smf239'] . ': $1</div><div class="quote">',
				'after' => '</div>',
				'quoted' => 'optional',
				'block_level' => true,
			),
			array(
				'tag' => 'quote',
				'parameters' => array(
					'author' => array('match' => '([^<>]{1,192}?)'),
					'link' => array('match' => '(?:board=\d+;)?((?:topic|threadid)=[\dmsg#\./]{1,40}(?:;start=[\dmsg#\./]{1,40})?|action=profile;u=\d+)'),
					'date' => array('match' => '(\d+)', 'validate' => 'timeformat'),
				),
				'before' => '<div class="quoteheader"><a href="' . $scripturl . '?{link}">' . $txt['smf239'] . ': {author} ' . $txt[176] . ' {date}</a></div><div class="quote">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'quote',
				'parameters' => array(
					'author' => array('match' => '(.{1,192}?)', 'validate' => 'parse_bbc'),
				),
				'before' => '<div class="quoteheader">' . $txt['smf239'] . ': {author}</div><div class="quote">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'right',
				'before' => '<div style="text-align: right;">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 'red',
				'before' => '<span style="color: red;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'rtl',
				'before' => '<div dir="rtl">',
				'after' => '</div>',
				'block_level' => true,
			),
			array(
				'tag' => 's',
				'before' => '<del>',
				'after' => '</del>',
			),
			array(
				'tag' => 'size',
				'type' => 'unparsed_equals',
				'test' => '([1-9][\d]?p[xt]|(?:x-)?small(?:er)?|(?:x-)?large[r]?)\]',
				// !!! line-height
				'before' => '<span style="font-size: $1; line-height: 1.3em;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'size',
				'type' => 'unparsed_equals',
				'test' => '[1-9]\]',
				// !!! line-height
				'before' => '<font size="$1" style="line-height: 1.3em;">',
				'after' => '</font>',
			),
			array(
				'tag' => 'sub',
				'before' => '<sub>',
				'after' => '</sub>',
			),
			array(
				'tag' => 'sup',
				'before' => '<sup>',
				'after' => '</sup>',
			),
			array(
				'tag' => 'shadow',
				'type' => 'unparsed_commas',
				'test' => '[#0-9a-zA-Z\-]{3,12},(left|right|top|bottom|[0123]\d{0,2})\]',
				'before' => $context['browser']['is_ie'] ? '<span style="filter: Shadow(color=$1, direction=$2); height: 1.2em;\">' : '<span style="text-shadow: $1 $2">',
				'after' => '</span>',
				'validate' => $context['browser']['is_ie'] ? create_function('&$tag, &$data, $disabled', '
					if ($data[1] == \'left\')
						$data[1] = 270;
					elseif ($data[1] == \'right\')
						$data[1] = 90;
					elseif ($data[1] == \'top\')
						$data[1] = 0;
					elseif ($data[1] == \'bottom\')
						$data[1] = 180;
					else
						$data[1] = (int) $data[1];') : create_function('&$tag, &$data, $disabled', '
					if ($data[1] == \'top\' || (is_numeric($data[1]) && $data[1] < 50))
						return \'0 -2px\';
					elseif ($data[1] == \'right\' || (is_numeric($data[1]) && $data[1] < 100))
						return \'2px 0\';
					elseif ($data[1] == \'bottom\' || (is_numeric($data[1]) && $data[1] < 190))
						return \'0 2px\';
					elseif ($data[1] == \'left\' || (is_numeric($data[1]) && $data[1] < 280))
						return \'-2px 0\';
					else
						return \'0 0\';'),
			),
			array(
				'tag' => 'time',
				'type' => 'unparsed_content',
				'content' => '$1',
				'validate' => create_function('&$tag, &$data, $disabled', '
					if (is_numeric($data))
						$data = timeformat($data);
					else
						$tag[\'content\'] = \'[time]$1[/time]\';'),
			),
			array(
				'tag' => 'tt',
				'before' => '<tt>',
				'after' => '</tt>',
			),
			array(
				'tag' => 'table',
				'before' => '<table style="font: inherit; color: inherit;">',
				'after' => '</table>',
				'trim' => 'inside',
				'require_children' => array('tr'),
				'block_level' => true,
			),
			array(
				'tag' => 'tr',
				'before' => '<tr>',
				'after' => '</tr>',
				'require_parents' => array('table'),
				'require_children' => array('td'),
				'trim' => 'both',
				'block_level' => true,
				'disabled_before' => '',
				'disabled_after' => '',
			),
			array(
				'tag' => 'td',
				'before' => '<td valign="top" style="font: inherit; color: inherit;">',
				'after' => '</td>',
				'require_parents' => array('tr'),
				'trim' => 'outside',
				'block_level' => true,
				'disabled_before' => '',
				'disabled_after' => '',
			),
			array(
				'tag' => 'url',
				'type' => 'unparsed_content',
				'content' => '<a href="$1" target="_blank">$1</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					$data = strtr($data, array(\'<br />\' => \'\'));
					if (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
			),
			array(
				'tag' => 'url',
				'type' => 'unparsed_equals',
				'before' => '<a href="$1" target="_blank">',
				'after' => '</a>',
				'validate' => create_function('&$tag, &$data, $disabled', '
					if (strpos($data, \'http://\') !== 0 && strpos($data, \'https://\') !== 0)
						$data = \'http://\' . $data;
				'),
				'disallow_children' => array('email', 'ftp', 'url', 'iurl'),
				'disabled_after' => ' ($1)',
			),
			array(
				'tag' => 'u',
				'before' => '<span style="text-decoration: underline;">',
				'after' => '</span>',
			),
			array(
				'tag' => 'white',
				'before' => '<span style="color: white;">',
				'after' => '</span>',
			),
		);

		// This is mainly for the bbc manager, so it's easy to add tags above.  Custom BBC should be added above this line.
		if ($message === false)
			return $codes;

		// So the parser won't skip them.
		$itemcodes = array(
			'*' => '',
			'@' => 'disc',
			'+' => 'square',
			'x' => 'square',
			'#' => 'square',
			'o' => 'circle',
			'O' => 'circle',
			'0' => 'circle',
		);
		if (!isset($disabled['li']) && !isset($disabled['list']))
		{
			foreach ($itemcodes as $c => $dummy)
				$bbc_codes[$c] = array();
		}

		// Inside these tags autolink is not recommendable.
		$no_autolink_tags = array(
			'url',
			'iurl',
			'ftp',
			'email',
		);

		// Shhhh!
		if (!isset($disabled['color']))
		{
			$codes[] = array(
				'tag' => 'chrissy',
				'before' => '<span style="color: #CC0099;">',
				'after' => ' :-*</span>',
			);
			$codes[] = array(
				'tag' => 'kissy',
				'before' => '<span style="color: #CC0099;">',
				'after' => ' :-*</span>',
			);
		}

		foreach ($codes as $c)
			$bbc_codes[substr($c['tag'], 0, 1)][] = $c;
		$codes = null;
	}

	// Shall we take the time to cache this?
	if ($cache_id != '' && !empty($modSettings['cache_enable']) && (($modSettings['cache_enable'] >= 2 && strlen($message) > 1000) || strlen($message) > 2400))
	{
		// It's likely this will change if the message is modified.
		$cache_key = 'parse:' . $cache_id . '-' . md5(md5($message) . '-' . $smileys . (empty($disabled) ? '' : implode(',', array_keys($disabled))) . serialize($context['browser']) . $txt['lang_locale'] . $user_info['time_offset'] . $user_info['time_format']);

		if (($temp = cache_get_data($cache_key, 240)) != null)
			return $temp;

		$cache_t = microtime();
	}

	if ($smileys === 'print')
	{
		// [glow], [shadow], and [move] can't really be printed.
		$disabled['glow'] = true;
		$disabled['shadow'] = true;
		$disabled['move'] = true;

		// Colors can't well be displayed... supposed to be black and white.
		$disabled['color'] = true;
		$disabled['black'] = true;
		$disabled['blue'] = true;
		$disabled['white'] = true;
		$disabled['red'] = true;
		$disabled['green'] = true;
		$disabled['me'] = true;

		// Color coding doesn't make sense.
		$disabled['php'] = true;

		// Links are useless on paper... just show the link.
		$disabled['ftp'] = true;
		$disabled['url'] = true;
		$disabled['iurl'] = true;
		$disabled['email'] = true;
		$disabled['flash'] = true;

		// !!! Change maybe?
		if (!isset($_GET['images']))
			$disabled['img'] = true;

		// !!! Interface/setting to add more?
	}

	$open_tags = array();
	$message = strtr($message, array("\n" => '<br />'));

	// The non-breaking-space looks a bit different each time.
	$non_breaking_space = $context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{C2A0}' : chr(0xC2) . chr(0xA0)) : '\xA0';

	$pos = -1;
	while ($pos !== false)
	{
		$last_pos = isset($last_pos) ? max($pos, $last_pos) : $pos;
		$pos = strpos($message, '[', $pos + 1);

		// Failsafe.
		if ($pos === false || $last_pos > $pos)
			$pos = strlen($message) + 1;

		// Can't have a one letter smiley, URL, or email! (sorry.)
		if ($last_pos < $pos - 1)
		{
			// We want to eat one less, and one more, character (for smileys.)
			$last_pos = max($last_pos - 1, 0);
			$data = substr($message, $last_pos, $pos - $last_pos + 1);

			// Take care of some HTML!
			if (!empty($modSettings['enablePostHTML']) && strpos($data, '&lt;') !== false)
			{
				$data = preg_replace('~&lt;a\s+href=((?:&quot;)?)((?:https?://|ftps?://|mailto:)\S+?)\\1&gt;~i', '[url=$2]', $data);
				$data = preg_replace('~&lt;/a&gt;~i', '[/url]', $data);

				// <br /> should be empty.
				$empty_tags = array('br', 'hr');
				foreach ($empty_tags as $tag)
					$data = str_replace(array('&lt;' . $tag . '&gt;', '&lt;' . $tag . '/&gt;', '&lt;' . $tag . ' /&gt;'), '[' . $tag . ' /]', $data);

				// b, u, i, s, pre... basic tags.
				$closable_tags = array('b', 'u', 'i', 's', 'em', 'ins', 'del', 'pre', 'blockquote');
				foreach ($closable_tags as $tag)
				{
					$diff = substr_count($data, '&lt;' . $tag . '&gt;') - substr_count($data, '&lt;/' . $tag . '&gt;');
					$data = strtr($data, array('&lt;' . $tag . '&gt;' => '<' . $tag . '>', '&lt;/' . $tag . '&gt;' => '</' . $tag . '>'));

					if ($diff > 0)
						$data .= str_repeat('</' . $tag . '>', $diff);
				}

				// Do <img ... /> - with security... action= -> action-.
				preg_match_all('~&lt;img\s+src=((?:&quot;)?)((?:https?://|ftps?://)\S+?)\\1(?:\s+alt=(&quot;.*?&quot;|\S*?))?(?:\s?/)?&gt;~i', $data, $matches, PREG_PATTERN_ORDER);
				if (!empty($matches[0]))
				{
					$replaces = array();
					foreach ($matches[2] as $match => $imgtag)
					{
						$alt = empty($matches[3][$match]) ? '' : ' alt=' . preg_replace('~^&quot;|&quot;$~', '', $matches[3][$match]);

						// Remove action= from the URL - no funny business, now.
						if (preg_match('~action(=|%3d)(?!dlattach)~i', $imgtag) != 0)
							$imgtag = preg_replace('~action(=|%3d)(?!dlattach)~i', 'action-', $imgtag);

						// Check if the image is larger than allowed.
						if (!empty($modSettings['max_image_width']) && !empty($modSettings['max_image_height']))
						{
							list ($width, $height) = url_image_size($imgtag);

							if (!empty($modSettings['max_image_width']) && $width > $modSettings['max_image_width'])
							{
								$height = (int) (($modSettings['max_image_width'] * $height) / $width);
								$width = $modSettings['max_image_width'];
							}

							if (!empty($modSettings['max_image_height']) && $height > $modSettings['max_image_height'])
							{
								$width = (int) (($modSettings['max_image_height'] * $width) / $height);
								$height = $modSettings['max_image_height'];
							}

							// Set the new image tag.
							$replaces[$matches[0][$match]] = '[img width=' . $width . ' height=' . $height . $alt . ']' . $imgtag . '[/img]';
						}
						else
							$replaces[$matches[0][$match]] = '[img' . $alt . ']' . $imgtag . '[/img]';
					}

					$data = strtr($data, $replaces);
				}
			}

			if (!empty($modSettings['autoLinkUrls']))
			{
				// Are we inside tags that should be auto linked?
				$no_autolink_area = false;
				if (!empty($open_tags))
				{
					foreach ($open_tags as $open_tag)
						if (in_array($open_tag['tag'], $no_autolink_tags))
							$no_autolink_area = true;
				}

				// Don't go backwards.
				//!!! Don't think is the real solution....
				$lastAutoPos = isset($lastAutoPos) ? $lastAutoPos : 0;
				if ($pos < $lastAutoPos)
					$no_autolink_area = true;
				$lastAutoPos = $pos;

				if (!$no_autolink_area)
				{
					// Parse any URLs.... have to get rid of the @ problems some things cause... stupid email addresses.
					if (!isset($disabled['url']) && (strpos($data, '://') !== false || strpos($data, 'www.') !== false))
					{
						// Switch out quotes really quick because they can cause problems.
						$data = strtr($data, array('&#039;' => '\'', '&nbsp;' => $context['utf8'] ? "\xC2\xA0" : "\xA0", '&quot;' => '>">', '"' => '<"<', '&lt;' => '<lt<'));

						// Only do this if the preg survives.
						if (is_string($result = preg_replace(array(
							'~(?<=[\s>\.(;\'"]|^)((?:http|https|ftp|ftps)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i', 
							'~(?<=[\s>(\'<]|^)(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i'
						), array(
							'[url]$1[/url]',
							'[url=http://$1]$1[/url]'
						), $data)))
							$data = $result;

						$data = strtr($data, array('\'' => '&#039;', $context['utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;', '>">' => '&quot;', '<"<' => '"', '<lt<' => '&lt;'));
					}

					// Next, emails...
					if (!isset($disabled['email']) && strpos($data, '@') !== false)
					{
						$data = preg_replace('~(?<=[\?\s' . $non_breaking_space . '\[\]()*\\\;>]|^)([\w\-\.]{1,80}@[\w\-]+\.[\w\-\.]+[\w\-])(?=[?,\s' . $non_breaking_space . '\[\]()*\\\]|$|<br />|&nbsp;|&gt;|&lt;|&quot;|&#039;|\.(?:\.|;|&nbsp;|\s|$|<br />))~' . ($context['utf8'] ? 'u' : ''), '[email]$1[/email]', $data);
						$data = preg_replace('~(?<=<br />)([\w\-\.]{1,80}@[\w\-]+\.[\w\-\.]+[\w\-])(?=[?\.,;\s' . $non_breaking_space . '\[\]()*\\\]|$|<br />|&nbsp;|&gt;|&lt;|&quot;|&#039;)~' . ($context['utf8'] ? 'u' : ''), '[email]$1[/email]', $data);
					}
				}
			}

			$data = strtr($data, array("\t" => '&nbsp;&nbsp;&nbsp;'));

			if (!empty($modSettings['fixLongWords']) && $modSettings['fixLongWords'] > 5)
			{
				// This is SADLY and INCREDIBLY browser dependent.
				if ($context['browser']['is_gecko'] || $context['browser']['is_konqueror'])
					$breaker = '<span style="margin: 0 -0.5ex 0 0;"> </span>';
				// Opera...
				elseif ($context['browser']['is_opera'])
					$breaker = '<span style="margin: 0 -0.65ex 0 -1px;"> </span>';
				// Internet Explorer...
				else
					$breaker = '<span style="width: 0; margin: 0 -0.6ex 0 -1px;"> </span>';

				// PCRE will not be happy if we don't give it a short.
				$modSettings['fixLongWords'] = (int) min(65535, $modSettings['fixLongWords']);

				// The idea is, find words xx long, and then replace them with xx + space + more.
				if (strlen($data) > $modSettings['fixLongWords'])
				{
					// This is done in a roundabout way because $breaker has "long words" :P.
					$data = strtr($data, array($breaker => '< >', '&nbsp;' => $context['utf8'] ? "\xC2\xA0" : "\xA0"));
					$data = preg_replace(
						'~(?<=[>;:!? ' . $non_breaking_space . '\]()]|^)([\w\.]{' . $modSettings['fixLongWords'] . ',})~e' . ($context['utf8'] ? 'u' : ''),
						"preg_replace('/(.{" . ($modSettings['fixLongWords'] - 1) . '})/' . ($context['utf8'] ? 'u' : '') . "', '\\\$1< >', '\$1')",
						$data);
					$data = strtr($data, array('< >' => $breaker, $context['utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;'));
				}
			}

			// Do any smileys!
			if ($smileys === true)
				parsesmileys($data);

			// If it wasn't changed, no copying or other boring stuff has to happen!
			if ($data != substr($message, $last_pos, $pos - $last_pos + 1))
			{
				$message = substr($message, 0, $last_pos) . $data . substr($message, $pos + 1);

				// Since we changed it, look again incase we added or removed a tag.  But we don't want to skip any.
				$old_pos = strlen($data) + $last_pos - 1;
				$pos = strpos($message, '[', $last_pos);
				$pos = $pos === false ? $old_pos : min($pos, $old_pos);
			}
		}

		// Are we there yet?  Are we there yet?
		if ($pos >= strlen($message) - 1)
			break;

		$tags = strtolower(substr($message, $pos + 1, 1));

		if ($tags == '/' && !empty($open_tags))
		{
			$pos2 = strpos($message, ']', $pos + 1);
			if ($pos2 == $pos + 2)
				continue;
			$look_for = strtolower(substr($message, $pos + 2, $pos2 - $pos - 2));

			$to_close = array();
			$block_level = null;
			do
			{
				$tag = array_pop($open_tags);
				if (!$tag)
					break;

				if (!empty($tag['block_level']))
				{
					// Only find out if we need to.
					if ($block_level === false)
					{
						array_push($open_tags, $tag);
						break;
					}

					// The idea is, if we are LOOKING for a block level tag, we can close them on the way.
					if (strlen($look_for) > 0 && isset($bbc_codes[$look_for{0}]))
					{
						foreach ($bbc_codes[$look_for{0}] as $temp)
							if ($temp['tag'] == $look_for)
							{
								$block_level = !empty($temp['block_level']);
								break;
							}
					}

					if ($block_level !== true)
					{
						$block_level = false;
						array_push($open_tags, $tag);
						break;
					}
				}

				$to_close[] = $tag;
			}
			while ($tag['tag'] != $look_for);

			// Did we just eat through everything and not find it?
			if ((empty($open_tags) && (empty($tag) || $tag['tag'] != $look_for)))
			{
				$open_tags = $to_close;
				continue;
			}
			elseif (!empty($to_close) && $tag['tag'] != $look_for)
			{
				if ($block_level === null && isset($look_for{0}, $bbc_codes[$look_for{0}]))
				{
					foreach ($bbc_codes[$look_for{0}] as $temp)
						if ($temp['tag'] == $look_for)
						{
							$block_level = !empty($temp['block_level']);
							break;
						}
				}

				// We're not looking for a block level tag (or maybe even a tag that exists...)
				if (!$block_level)
				{
					foreach ($to_close as $tag)
						array_push($open_tags, $tag);
					continue;
				}
			}

			foreach ($to_close as $tag)
			{
				$message = substr($message, 0, $pos) . $tag['after'] . substr($message, $pos2 + 1);
				$pos += strlen($tag['after']);
				$pos2 = $pos - 1;

				// See the comment at the end of the big loop - just eating whitespace ;).
				if (!empty($tag['block_level']) && substr($message, $pos, 6) == '<br />')
					$message = substr($message, 0, $pos) . substr($message, $pos + 6);
				if (!empty($tag['trim']) && $tag['trim'] != 'inside' && preg_match('~(<br />|&nbsp;|\s)*~', substr($message, $pos), $matches) != 0)
					$message = substr($message, 0, $pos) . substr($message, $pos + strlen($matches[0]));
			}

			if (!empty($to_close))
			{
				$to_close = array();
				$pos--;
			}

			continue;
		}

		// No tags for this character, so just keep going (fastest possible course.)
		if (!isset($bbc_codes[$tags]))
			continue;

		$inside = empty($open_tags) ? null : $open_tags[count($open_tags) - 1];
		$tag = null;
		foreach ($bbc_codes[$tags] as $possible)
		{
			// Not a match?
			if (strtolower(substr($message, $pos + 1, strlen($possible['tag']))) != $possible['tag'])
				continue;

			$next_c = substr($message, $pos + 1 + strlen($possible['tag']), 1);

			// A test validation?
			if (isset($possible['test']) && preg_match('~^' . $possible['test'] . '~', substr($message, $pos + 1 + strlen($possible['tag']) + 1)) == 0)
				continue;
			// Do we want parameters?
			elseif (!empty($possible['parameters']))
			{
				if ($next_c != ' ')
					continue;
			}
			elseif (isset($possible['type']))
			{
				// Do we need an equal sign?
				if (in_array($possible['type'], array('unparsed_equals', 'unparsed_commas', 'unparsed_commas_content', 'unparsed_equals_content', 'parsed_equals')) && $next_c != '=')
					continue;
				// Maybe we just want a /...
				if ($possible['type'] == 'closed' && $next_c != ']' && substr($message, $pos + 1 + strlen($possible['tag']), 2) != '/]' && substr($message, $pos + 1 + strlen($possible['tag']), 3) != ' /]')
					continue;
				// An immediate ]?
				if ($possible['type'] == 'unparsed_content' && $next_c != ']')
					continue;
			}
			// No type means 'parsed_content', which demands an immediate ] without parameters!
			elseif ($next_c != ']')
				continue;

			// Check allowed tree?
			if (isset($possible['require_parents']) && ($inside === null || !in_array($inside['tag'], $possible['require_parents'])))
				continue;
			elseif (isset($inside['require_children']) && !in_array($possible['tag'], $inside['require_children']))
				continue;
			// If this is in the list of disallowed child tags, don't parse it.
			elseif (isset($inside['disallow_children']) && in_array($possible['tag'], $inside['disallow_children']))
				continue;

			$pos1 = $pos + 1 + strlen($possible['tag']) + 1;

			// This is long, but it makes things much easier and cleaner.
			if (!empty($possible['parameters']))
			{
				$preg = array();
				foreach ($possible['parameters'] as $p => $info)
					$preg[] = '(\s+' . $p . '=' . (empty($info['quoted']) ? '' : '&quot;') . (isset($info['match']) ? $info['match'] : '(.+?)') . (empty($info['quoted']) ? '' : '&quot;') . ')' . (empty($info['optional']) ? '' : '?');

				// Okay, this may look ugly and it is, but it's not going to happen much and it is the best way of allowing any order of parameters but still parsing them right.
				$match = false;
				$orders = permute($preg);
				foreach ($orders as $p)
					if (preg_match('~^' . implode('', $p) . '\]~i', substr($message, $pos1 - 1), $matches) != 0)
					{
						$match = true;
						break;
					}

				// Didn't match our parameter list, try the next possible.
				if (!$match)
					continue;

				$params = array();
				for ($i = 1, $n = count($matches); $i < $n; $i += 2)
				{
					$key = strtok(ltrim($matches[$i]), '=');
					if (isset($possible['parameters'][$key]['value']))
						$params['{' . $key . '}'] = strtr($possible['parameters'][$key]['value'], array('$1' => $matches[$i + 1]));
					elseif (isset($possible['parameters'][$key]['validate']))
						$params['{' . $key . '}'] = $possible['parameters'][$key]['validate']($matches[$i + 1]);
					else
						$params['{' . $key . '}'] = $matches[$i + 1];

					// Just to make sure: replace any $ or { so they can't interpolate wrongly.
					$params['{' . $key . '}'] = strtr($params['{' . $key . '}'], array('$' => '&#036;', '{' => '&#123;'));
				}

				foreach ($possible['parameters'] as $p => $info)
				{
					if (!isset($params['{' . $p . '}']))
						$params['{' . $p . '}'] = '';
				}

				$tag = $possible;

				// Put the parameters into the string.
				if (isset($tag['before']))
					$tag['before'] = strtr($tag['before'], $params);
				if (isset($tag['after']))
					$tag['after'] = strtr($tag['after'], $params);
				if (isset($tag['content']))
					$tag['content'] = strtr($tag['content'], $params);

				$pos1 += strlen($matches[0]) - 1;
			}
			else
				$tag = $possible;
			break;
		}

		// Item codes are complicated buggers... they are implicit [li]s and can make [list]s!
		if ($smileys !== false && $tag === null && isset($itemcodes[substr($message, $pos + 1, 1)]) && substr($message, $pos + 2, 1) == ']' && !isset($disabled['list']) && !isset($disabled['li']))
		{
			if (substr($message, $pos + 1, 1) == '0' && !in_array(substr($message, $pos - 1, 1), array(';', ' ', "\t", '>')))
				continue;
			$tag = $itemcodes[substr($message, $pos + 1, 1)];

			// First let's set up the tree: it needs to be in a list, or after an li.
			if ($inside === null || ($inside['tag'] != 'list' && $inside['tag'] != 'li'))
			{
				$open_tags[] = array(
					'tag' => 'list',
					'after' => '</ul>',
					'block_level' => true,
					'require_children' => array('li'),
					'disallow_children' => isset($inside['disallow_children']) ? $inside['disallow_children'] : null,
				);
				$code = '<ul style="margin-top: 0; margin-bottom: 0;">';
			}
			// We're in a list item already: another itemcode?  Close it first.
			elseif ($inside['tag'] == 'li')
			{
				array_pop($open_tags);
				$code = '</li>';
			}
			else
				$code = '';

			// Now we open a new tag.
			$open_tags[] = array(
				'tag' => 'li',
				'after' => '</li>',
				'trim' => 'outside',
				'block_level' => true,
				'disallow_children' => isset($inside['disallow_children']) ? $inside['disallow_children'] : null,
			);

			// First, open the tag...
			$code .= '<li' . ($tag == '' ? '' : ' type="' . $tag . '"') . '>';
			$message = substr($message, 0, $pos) . $code . substr($message, $pos + 3);
			$pos += strlen($code) - 1;

			// Next, find the next break (if any.)  If there's more itemcode after it, keep it going - otherwise close!
			$pos2 = strpos($message, '<br />', $pos);
			$pos3 = strpos($message, '[/', $pos);
			if ($pos2 !== false && ($pos2 <= $pos3 || $pos3 === false))
			{
				preg_match('~^(<br />|&nbsp;|\s|\[)+~', substr($message, $pos2 + 6), $matches);
				$message = substr($message, 0, $pos2) . (!empty($matches[0]) && substr($matches[0], -1) == '[' ? '[/li]' : '[/li][/list]') . substr($message, $pos2);

				$open_tags[count($open_tags) - 2]['after'] = '</ul>';
			}
			// Tell the [list] that it needs to close specially.
			else
			{
				// Move the li over, because we're not sure what we'll hit.
				$open_tags[count($open_tags) - 1]['after'] = '';
				$open_tags[count($open_tags) - 2]['after'] = '</li></ul>';
			}

			continue;
		}

		// Implicitly close lists and tables if something other than what's required is in them.  This is needed for itemcode.
		if ($tag === null && $inside !== null && !empty($inside['require_children']))
		{
			array_pop($open_tags);

			$message = substr($message, 0, $pos) . $inside['after'] . substr($message, $pos);
			$pos += strlen($inside['after']) - 1;
		}

		// No tag?  Keep looking, then.  Silly people using brackets without actual tags.
		if ($tag === null)
			continue;

		// Propagate the list to the child (so wrapping the disallowed tag won't work either.)
		if (isset($inside['disallow_children']))
			$tag['disallow_children'] = isset($tag['disallow_children']) ? array_unique(array_merge($tag['disallow_children'], $inside['disallow_children'])) : $inside['disallow_children'];

		// Is this tag disabled?
		if (isset($disabled[$tag['tag']]))
		{
			if (!isset($tag['disabled_before']) && !isset($tag['disabled_after']) && !isset($tag['disabled_content']))
			{
				$tag['before'] = !empty($tag['block_level']) ? '<div>' : '';
				$tag['after'] = !empty($tag['block_level']) ? '</div>' : '';
				$tag['content'] = isset($tag['type']) && $tag['type'] == 'closed' ? '' : (!empty($tag['block_level']) ? '<div>$1</div>' : '$1');
			}
			elseif (isset($tag['disabled_before']) || isset($tag['disabled_after']))
			{
				$tag['before'] = isset($tag['disabled_before']) ? $tag['disabled_before'] : (!empty($tag['block_level']) ? '<div>' : '');
				$tag['after'] = isset($tag['disabled_after']) ? $tag['disabled_after'] : (!empty($tag['block_level']) ? '</div>' : '');
			}
			else
				$tag['content'] = $tag['disabled_content'];
		}

		// The only special case is 'html', which doesn't need to close things.
		if (!empty($tag['block_level']) && $tag['tag'] != 'html' && empty($inside['block_level']))
		{
			$n = count($open_tags) - 1;
			while (empty($open_tags[$n]['block_level']) && $n >= 0)
				$n--;

			// Close all the non block level tags so this tag isn't surrounded by them.
			for ($i = count($open_tags) - 1; $i > $n; $i--)
			{
				$message = substr($message, 0, $pos) . $open_tags[$i]['after'] . substr($message, $pos);
				$pos += strlen($open_tags[$i]['after']);
				$pos1 += strlen($open_tags[$i]['after']);

				// Trim or eat trailing stuff... see comment at the end of the big loop.
				if (!empty($open_tags[$i]['block_level']) && substr($message, $pos, 6) == '<br />')
					$message = substr($message, 0, $pos) . substr($message, $pos + 6);
				if (!empty($open_tags[$i]['trim']) && $tag['trim'] != 'inside' && preg_match('~(<br />|&nbsp;|\s)*~', substr($message, $pos), $matches) != 0)
					$message = substr($message, 0, $pos) . substr($message, $pos + strlen($matches[0]));

				array_pop($open_tags);
			}
		}

		// No type means 'parsed_content'.
		if (!isset($tag['type']))
		{
			// !!! Check for end tag first, so people can say "I like that [i] tag"?
			$open_tags[] = $tag;
			$message = substr($message, 0, $pos) . $tag['before'] . substr($message, $pos1);
			$pos += strlen($tag['before']) - 1;
		}
		// Don't parse the content, just skip it.
		elseif ($tag['type'] == 'unparsed_content')
		{
			$pos2 = stripos($message, '[/' . substr($message, $pos + 1, strlen($tag['tag'])) . ']', $pos1);
			if ($pos2 === false)
				continue;

			$data = substr($message, $pos1, $pos2 - $pos1);

			if (!empty($tag['block_level']) && substr($data, 0, 6) == '<br />')
				$data = substr($data, 6);

			if (isset($tag['validate']))
				$tag['validate']($tag, $data, $disabled);

			$code = strtr($tag['content'], array('$1' => $data));
			$message = substr($message, 0, $pos) . $code . substr($message, $pos2 + 3 + strlen($tag['tag']));
			$pos += strlen($code) - 1;
		}
		// Don't parse the content, just skip it.
		elseif ($tag['type'] == 'unparsed_equals_content')
		{
			// The value may be quoted for some tags - check.
			if (isset($tag['quoted']))
			{
				$quoted = substr($message, $pos1, 6) == '&quot;';
				if ($tag['quoted'] != 'optional' && !$quoted)
					continue;

				if ($quoted)
					$pos1 += 6;
			}
			else
				$quoted = false;

			$pos2 = strpos($message, $quoted == false ? ']' : '&quot;]', $pos1);
			if ($pos2 === false)
				continue;
			$pos3 = stripos($message, '[/' . substr($message, $pos + 1, strlen($tag['tag'])) . ']', $pos2);
			if ($pos3 === false)
				continue;

			$data = array(
				substr($message, $pos2 + ($quoted == false ? 1 : 7), $pos3 - ($pos2 + ($quoted == false ? 1 : 7))),
				substr($message, $pos1, $pos2 - $pos1)
			);

			if (!empty($tag['block_level']) && substr($data[0], 0, 6) == '<br />')
				$data[0] = substr($data[0], 6);

			// Validation for my parking, please!
			if (isset($tag['validate']))
				$tag['validate']($tag, $data, $disabled);

			$code = strtr($tag['content'], array('$1' => $data[0], '$2' => $data[1]));
			$message = substr($message, 0, $pos) . $code . substr($message, $pos3 + 3 + strlen($tag['tag']));
			$pos += strlen($code) - 1;
		}
		// A closed tag, with no content or value.
		elseif ($tag['type'] == 'closed')
		{
			$pos2 = strpos($message, ']', $pos);
			$message = substr($message, 0, $pos) . $tag['content'] . substr($message, $pos2 + 1);
			$pos += strlen($tag['content']) - 1;
		}
		// This one is sorta ugly... :/.  Unforunately, it's needed for flash.
		elseif ($tag['type'] == 'unparsed_commas_content')
		{
			$pos2 = strpos($message, ']', $pos1);
			if ($pos2 === false)
				continue;
			$pos3 = stripos($message, '[/' . substr($message, $pos + 1, strlen($tag['tag'])) . ']', $pos2);
			if ($pos3 === false)
				continue;

			// We want $1 to be the content, and the rest to be csv.
			$data = explode(',', ',' . substr($message, $pos1, $pos2 - $pos1));
			$data[0] = substr($message, $pos2 + 1, $pos3 - $pos2 - 1);

			if (isset($tag['validate']))
				$tag['validate']($tag, $data, $disabled);

			$code = $tag['content'];
			foreach ($data as $k => $d)
				$code = strtr($code, array('$' . ($k + 1) => trim($d)));
			$message = substr($message, 0, $pos) . $code . substr($message, $pos3 + 3 + strlen($tag['tag']));
			$pos += strlen($code) - 1;
		}
		// This has parsed content, and a csv value which is unparsed.
		elseif ($tag['type'] == 'unparsed_commas')
		{
			$pos2 = strpos($message, ']', $pos1);
			if ($pos2 === false)
				continue;

			$data = explode(',', substr($message, $pos1, $pos2 - $pos1));

			if (isset($tag['validate']))
				$tag['validate']($tag, $data, $disabled);

			// Fix after, for disabled code mainly.
			foreach ($data as $k => $d)
				$tag['after'] = strtr($tag['after'], array('$' . ($k + 1) => trim($d)));

			$open_tags[] = $tag;

			// Replace them out, $1, $2, $3, $4, etc.
			$code = $tag['before'];
			foreach ($data as $k => $d)
				$code = strtr($code, array('$' . ($k + 1) => trim($d)));
			$message = substr($message, 0, $pos) . $code . substr($message, $pos2 + 1);
			$pos += strlen($code) - 1;
		}
		// A tag set to a value, parsed or not.
		elseif ($tag['type'] == 'unparsed_equals' || $tag['type'] == 'parsed_equals')
		{
			// The value may be quoted for some tags - check.
			if (isset($tag['quoted']))
			{
				$quoted = substr($message, $pos1, 6) == '&quot;';
				if ($tag['quoted'] != 'optional' && !$quoted)
					continue;

				if ($quoted)
					$pos1 += 6;
			}
			else
				$quoted = false;

			$pos2 = strpos($message, $quoted == false ? ']' : '&quot;]', $pos1);
			if ($pos2 === false)
				continue;

			$data = substr($message, $pos1, $pos2 - $pos1);

			// Validation for my parking, please!
			if (isset($tag['validate']))
				$tag['validate']($tag, $data, $disabled);

			// For parsed content, we must recurse to avoid security problems.
			if ($tag['type'] != 'unparsed_equals')
				$data = parse_bbc($data);

			$tag['after'] = strtr($tag['after'], array('$1' => $data));

			$open_tags[] = $tag;

			$code = strtr($tag['before'], array('$1' => $data));
			$message = substr($message, 0, $pos) . $code . substr($message, $pos2 + ($quoted == false ? 1 : 7));
			$pos += strlen($code) - 1;
		}

		// If this is block level, eat any breaks after it.
		if (!empty($tag['block_level']) && substr($message, $pos + 1, 6) == '<br />')
			$message = substr($message, 0, $pos + 1) . substr($message, $pos + 7);

		// Are we trimming outside this tag?
		if (!empty($tag['trim']) && $tag['trim'] != 'outside' && preg_match('~(<br />|&nbsp;|\s)*~', substr($message, $pos + 1), $matches) != 0)
			$message = substr($message, 0, $pos + 1) . substr($message, $pos + 1 + strlen($matches[0]));
	}

	// Close any remaining tags.
	while ($tag = array_pop($open_tags))
		$message .= $tag['after'];

	if (substr($message, 0, 1) == ' ')
		$message = '&nbsp;' . substr($message, 1);

	// Cleanup whitespace.
	$message = strtr($message, array('  ' => ' &nbsp;', "\r" => '', "\n" => '<br />', '<br /> ' => '<br />&nbsp;', '&#13;' => "\n"));

	// Cache the output if it took some time...
	if (isset($cache_key, $cache_t) && array_sum(explode(' ', microtime())) - array_sum(explode(' ', $cache_t)) > 0.05)
		cache_put_data($cache_key, $message, 240);

	return $message;
}

// Parse smileys in the passed message.
function parsesmileys(&$message)
{
	global $modSettings, $db_prefix, $txt, $user_info, $context;
	static $smileyfromcache = array(), $smileytocache = array();

	// No smiley set at all?!
	if ($user_info['smiley_set'] == 'none')
		return;

	// If the smiley array hasn't been set, do it now.
	if (empty($smileyfromcache))
	{
		// Use the default smileys if it is disabled. (better for "portability" of smileys.)
		if (empty($modSettings['smiley_enable']))
		{
			$smileysfrom = array('>:D', ':D', '::)', '>:(', ':)', ';)', ';D', ':(', ':o', '8)', ':P', '???', ':-[', ':-X', ':-*', ':\'(', ':-\\', '^-^', 'O0', 'C:-)', '0:)');
			$smileysto = array('evil.gif', 'cheesy.gif', 'rolleyes.gif', 'angry.gif', 'smiley.gif', 'wink.gif', 'grin.gif', 'sad.gif', 'shocked.gif', 'cool.gif', 'tongue.gif', 'huh.gif', 'embarrassed.gif', 'lipsrsealed.gif', 'kiss.gif', 'cry.gif', 'undecided.gif', 'azn.gif', 'afro.gif', 'police.gif', 'angel.gif');
			$smileysdescs = array('', $txt[289], $txt[450], $txt[288], $txt[287], $txt[292], $txt[293], $txt[291], $txt[294], $txt[295], $txt[451], $txt[296], $txt[526], $txt[527], $txt[529], $txt[530], $txt[528], '', '', '', '');
		}
		else
		{
			// Load the smileys in reverse order by length so they don't get parsed wrong.
			if (($temp = cache_get_data('parsing_smileys', 480)) == null)
			{
				$result = db_query("
					SELECT code, filename, description
					FROM {$db_prefix}smileys", __FILE__, __LINE__);
				$smileysfrom = array();
				$smileysto = array();
				$smileysdescs = array();
				while ($row = mysql_fetch_assoc($result))
				{
					$smileysfrom[] = $row['code'];
					$smileysto[] = $row['filename'];
					$smileysdescs[] = $row['description'];
				}
				mysql_free_result($result);

				cache_put_data('parsing_smileys', array($smileysfrom, $smileysto, $smileysdescs), 480);
			}
			else
				list ($smileysfrom, $smileysto, $smileysdescs) = $temp;
		}

		// The non-breaking-space is a complex thing...
		$non_breaking_space = $context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{A0}' : pack('C*', 0xC2, 0xA0)) : '\xA0';

		// This smiley regex makes sure it doesn't parse smileys within code tags (so [url=mailto:David@bla.com] doesn't parse the :D smiley)
		for ($i = 0, $n = count($smileysfrom); $i < $n; $i++)
		{
			$smileyfromcache[] = '/(?<=[>:\?\.\s' . $non_breaking_space . '[\]()*\\\;]|^)(' . preg_quote($smileysfrom[$i], '/') . '|' . preg_quote(htmlspecialchars($smileysfrom[$i], ENT_QUOTES), '/') . ')(?=[^[:alpha:]0-9]|$)/' . ($context['utf8'] ? 'u' : '');
			// Escape a bunch of smiley-related characters in the description so it doesn't get a double dose :P.
			$smileytocache[] = '<img src="' . htmlspecialchars($modSettings['smileys_url'] . '/' . $user_info['smiley_set'] . '/' . $smileysto[$i]) . '" alt="' . strtr(htmlspecialchars($smileysdescs[$i]), array(':' => '&#58;', '(' => '&#40;', ')' => '&#41;', '$' => '&#36;', '[' => '&#091;')) . '" border="0" />';
		}
	}

	// Replace away!
	// !!! There must be a way to speed this up.
	$message = preg_replace($smileyfromcache, $smileytocache, $message);
}

// Highlight any code...
function highlight_php_code($code)
{
	global $context;

	// Remove special characters.
	$code = un_htmlspecialchars(strtr($code, array('<br />' => "\n", "\t" => 'SMF_TAB();', '&#91;' => '[')));

	$oldlevel = error_reporting(0);

	// It's easier in 4.2.x+.
	if (@version_compare(PHP_VERSION, '4.2.0') == -1)
	{
		ob_start();
		@highlight_string($code);
		$buffer = str_replace(array("\n", "\r"), '', ob_get_contents());
		ob_end_clean();
	}
	else
		$buffer = str_replace(array("\n", "\r"), '', @highlight_string($code, true));

	error_reporting($oldlevel);

	// Yes, I know this is kludging it, but this is the best way to preserve tabs from PHP :P.
	$buffer = preg_replace('~SMF_TAB(</(font|span)><(font color|span style)="[^"]*?">)?\(\);~', "<pre style=\"display: inline;\">\t</pre>", $buffer);

	return strtr($buffer, array('\'' => '&#039;', '<code>' => '', '</code>' => ''));
}

// Put this user in the online log.
function writeLog($force = false)
{
	global $db_prefix, $ID_MEMBER, $user_info, $user_settings, $sc, $modSettings, $settings, $topic, $board;

	// If we are showing who is viewing a topic, let's see if we are, and force an update if so - to make it accurate.
	if (!empty($settings['display_who_viewing']) && ($topic || $board))
	{
		// Take the opposite approach!
		$force = true;
		// Don't update for every page - this isn't wholly accurate but who cares.
		if ($topic)
		{
			if (isset($_SESSION['last_topic_id']) && $_SESSION['last_topic_id'] == $topic)
				$force = false;
			$_SESSION['last_topic_id'] = $topic;
		}
	}

	// Don't mark them as online more than every so often.
	if (!empty($_SESSION['log_time']) && $_SESSION['log_time'] >= (time() - 8) && !$force)
		return;

	if (!empty($modSettings['who_enabled']))
	{
		$serialized = $_GET + array('USER_AGENT' => $_SERVER['HTTP_USER_AGENT']);
		unset($serialized['sesc']);
		$serialized = addslashes(serialize($serialized));
	}
	else
		$serialized = '';

	// Guests use 0, members use their session ID.
	$session_id = $user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id();

	// Grab the last all-of-SMF-specific log_online deletion time.
	$do_delete = cache_get_data('log_online-update', 10) < time() - 10;

	// If the last click wasn't a long time ago, and there was a last click...
	if (!empty($_SESSION['log_time']) && $_SESSION['log_time'] >= time() - $modSettings['lastActive'] * 20)
	{
		if ($do_delete)
		{
			db_query("
				DELETE FROM {$db_prefix}log_online
				WHERE logTime < NOW() - INTERVAL " . ($modSettings['lastActive'] * 60) . " SECOND
					AND session != '$session_id'", __FILE__, __LINE__);
			cache_put_data('log_online-update', time(), 10);
		}

		db_query("
			UPDATE {$db_prefix}log_online
			SET logTime = NOW(), ip = IFNULL(INET_ATON('$user_info[ip]'), 0), url = '$serialized'
			WHERE session = '$session_id'
			LIMIT 1", __FILE__, __LINE__);

		// Guess it got deleted.
		if (db_affected_rows() == 0)
			$_SESSION['log_time'] = 0;
	}
	else
		$_SESSION['log_time'] = 0;

	// Otherwise, we have to delete and insert.
	if (empty($_SESSION['log_time']))
	{
		if ($do_delete || !empty($ID_MEMBER))
			db_query("
				DELETE FROM {$db_prefix}log_online
				WHERE " . ($do_delete ? "logTime < NOW() - INTERVAL " . ($modSettings['lastActive'] * 60) . ' SECOND' : '') . ($do_delete && !empty($ID_MEMBER) ? ' OR ' : '') . (empty($ID_MEMBER) ? '' : "ID_MEMBER = $ID_MEMBER"), __FILE__, __LINE__);

		db_query("
			" . ($do_delete ? 'INSERT IGNORE' : 'REPLACE') . " INTO {$db_prefix}log_online
				(session, ID_MEMBER, logTime, ip, url)
			VALUES ('$session_id', $ID_MEMBER, NOW(), IFNULL(INET_ATON('$user_info[ip]'), 0), '$serialized')", __FILE__, __LINE__);
	}

	// Mark your session as being logged.
	$_SESSION['log_time'] = time();

	// Well, they are online now.
	if (empty($_SESSION['timeOnlineUpdated']))
		$_SESSION['timeOnlineUpdated'] = time();

	// Set their login time, if not already done within the last minute.
	if (SMF != 'SSI' && !empty($user_info['last_login']) && $user_info['last_login'] < time() - 60)
	{
		// Don't count longer than 15 minutes.
		if (time() - $_SESSION['timeOnlineUpdated'] > 60 * 15)
			$_SESSION['timeOnlineUpdated'] = time();

		$user_settings['totalTimeLoggedIn'] += time() - $_SESSION['timeOnlineUpdated'];
		updateMemberData($ID_MEMBER, array('lastLogin' => time(), 'memberIP' => '\'' . $user_info['ip'] . '\'', 'memberIP2' => '\'' . $_SERVER['BAN_CHECK_IP'] . '\'', 'totalTimeLoggedIn' => $user_settings['totalTimeLoggedIn']));

		if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
			cache_put_data('user_settings-' . $ID_MEMBER, $user_settings, 60);

		$user_info['total_time_logged_in'] += time() - $_SESSION['timeOnlineUpdated'];
		$_SESSION['timeOnlineUpdated'] = time();
	}
}

// Make sure the browser doesn't come back and repost the form data.  Should be used whenever anything is posted.
function redirectexit($setLocation = '', $refresh = false)
{
	global $scripturl, $context, $modSettings, $db_show_debug;

	$add = preg_match('~^(ftp|http)[s]?://~', $setLocation) == 0 && substr($setLocation, 0, 6) != 'about:';

	if (WIRELESS)
	{
		// Add the scripturl on if needed.
		if ($add)
			$setLocation = $scripturl . '?' . $setLocation;

		$char = strpos($setLocation, '?') === false ? '?' : ';';

		if (strpos($setLocation, '#') ==! false)
			$setLocation = strtr($setLocation, array('#' => $char . WIRELESS_PROTOCOL . '#'));
		else
			$setLocation .= $char . WIRELESS_PROTOCOL;
	}
	elseif ($add)
		$setLocation = $scripturl . ($setLocation != '' ? '?' . $setLocation : '');

	// Put the session ID in.
	if (defined('SID') && SID != '')
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '(?!\?' . preg_quote(SID, '/') . ')(\?)?/', $scripturl . '?' . SID . ';', $setLocation);
	// Keep that debug in their for template debugging!
	elseif (isset($_GET['debug']))
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '(\?)?/', $scripturl . '?debug;', $setLocation);

	if (!empty($modSettings['queryless_urls']) && (empty($context['server']['is_cgi']) || @ini_get('cgi.fix_pathinfo') == 1) && !empty($context['server']['is_apache']))
	{
		if (defined('SID') && SID != '')
			$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '\?(?:' . SID . ';)((?:board|topic)=[^#]+?)(#[^"]*?)?$/e', "\$scripturl . '/' . strtr('\$1', '&;=', '//,') . '.html\$2?' . SID", $setLocation);
		else
			$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '\?((?:board|topic)=[^#"]+?)(#[^"]*?)?$/e', "\$scripturl . '/' . strtr('\$1', '&;=', '//,') . '.html\$2'", $setLocation);
	}

	if (isset($modSettings['integrate_redirect']) && function_exists($modSettings['integrate_redirect']))
		$modSettings['integrate_redirect']($setLocation, $refresh);

	// We send a Refresh header only in special cases because Location looks better. (and is quicker...)
	if ($refresh && !WIRELESS)
		header('Refresh: 0; URL=' . strtr($setLocation, array(' ' => '%20', ';' => '%3b')));
	else
		header('Location: ' . str_replace(' ', '%20', $setLocation));

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
		$_SESSION['debug_redirect'] = &$GLOBALS['db_cache'];

	obExit(false);
}

// Ends execution.  Takes care of template loading and remembering the previous URL.
function obExit($header = null, $do_footer = null, $from_index = false)
{
	global $context, $settings, $modSettings, $txt;
	static $header_done = false, $footer_done = false;

	// Clear out the stat cache.
	trackStats();

	$do_header = $header === null ? !$header_done : $header;
	if ($do_footer === null)
		$do_footer = $do_header;

	// Has the template/header been done yet?
	if ($do_header)
	{
		// Start up the session URL fixer.
		ob_start('ob_sessrewrite');

		// Just in case we have anything bad already in there...
		if ((isset($_REQUEST['debug']) || isset($_REQUEST['xml']) || (WIRELESS && WIRELESS_PROTOCOL == 'wap')) && in_array($txt['lang_locale'], array('UTF-8', 'ISO-8859-1')))
			ob_start('validate_unicode__recursive');

		if (!empty($settings['output_buffers']) && is_string($settings['output_buffers']))
			$buffers = explode(',', $settings['output_buffers']);
		elseif (!empty($settings['output_buffers']))
			$buffers = $settings['output_buffers'];
		else
			$buffers = array();

		if (isset($modSettings['integrate_buffer']))
			$buffers = array_merge(explode(',', $modSettings['integrate_buffer']), $buffers);

		if (!empty($buffers))
			foreach ($buffers as $buffer_function)
			{
				if (function_exists(trim($buffer_function)))
					ob_start(trim($buffer_function));
			}

		// Display the screen in the logical order.
		template_header();
		$header_done = true;
	}
	if ($do_footer)
	{
		if (WIRELESS && !isset($context['sub_template']))
			fatal_lang_error('wireless_error_notyet', false);

		// Just show the footer, then.
		loadSubTemplate(isset($context['sub_template']) ? $context['sub_template'] : 'main');

		// Just so we don't get caught in an endless loop of errors from the footer...
		if (!$footer_done)
		{
			$footer_done = true;
			template_footer();

			// (since this is just debugging... it's okay that it's after </html>.)
			if (!isset($_REQUEST['xml']))
				db_debug_junk();
		}
	}

	// Remember this URL in case someone doesn't like sending HTTP_REFERER.
	if (strpos($_SERVER['REQUEST_URL'], 'action=dlattach') === false)
		$_SESSION['old_url'] = $_SERVER['REQUEST_URL'];

	// For session check verfication.... don't switch browsers...
	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

	// Hand off the output to the portal, etc. we're integrated with.
	if (isset($modSettings['integrate_exit'], $context['template_layers']) && in_array('main', $context['template_layers']) && function_exists($modSettings['integrate_exit']))
		call_user_func($modSettings['integrate_exit'], $do_footer && !WIRELESS);

	// Don't exit if we're coming from index.php; that will pass through normally.
	if (!$from_index || WIRELESS)
		exit;
}

// Set up the administration sections.
function adminIndex($area)
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings;

	// Load the language and templates....
	loadLanguage('Admin');
	loadTemplate('Admin');

	// Admin area 'Main'.
	$context['admin_areas']['forum'] = array(
		'title' => $txt[427],
		'areas' => array(
			'index' => '<a href="' . $scripturl . '?action=admin">' . $txt[208] . '</a>',
			'credits' => '<a href="' . $scripturl . '?action=admin;credits">' . $txt['support_credits_title'] . '</a>',
		)
	);

	if (allowedTo(array('edit_news', 'send_mail', 'admin_forum')))
		$context['admin_areas']['forum']['areas']['news'] = '<a href="' . $scripturl . '?action=news">' . $txt['news_title'] . '</a>';

	if (allowedTo('admin_forum'))
		$context['admin_areas']['forum']['areas']['manage_packages'] =  '<a href="' . $scripturl . '?action=packages">' . $txt['package1'] . '</a>';

	// Admin area 'Configuration'.
	if (allowedTo('admin_forum'))
	{
		$context['admin_areas']['config'] = array(
			'title' => $txt[428],
			'areas' => array(
				'edit_mods_settings' => '<a href="' . $scripturl . '?action=featuresettings">' . $txt['modSettings_title'] . '</a>',
				'edit_settings' => '<a href="' . $scripturl . '?action=serversettings;sesc=' . $sc . '">' . $txt[222] . '</a>',
				'edit_theme_settings' => '<a href="' . $scripturl . '?action=theme;sa=settings;th=' . $settings['theme_id'] . ';sesc=' . $sc . '">' . $txt['theme_current_settings'] . '</a>',
				'manage_themes' => '<a href="' . $scripturl . '?action=theme;sa=admin;sesc=' . $sc . '">' . $txt['theme_admin'] . '</a>',
			)
		);
	}

	// Admin area 'Forum'.
	if (allowedTo(array('manage_boards', 'admin_forum', 'manage_smileys', 'manage_attachments', 'moderate_forum')))
	{
		$context['admin_areas']['layout'] = array(
			'title' => $txt['layout_controls'],
			'areas' => array()
		);

		if (allowedTo('manage_boards'))
			$context['admin_areas']['layout']['areas']['manage_boards'] =  '<a href="' . $scripturl . '?action=manageboards">' . $txt[4] . '</a>';

		if (allowedTo(array('admin_forum', 'moderate_forum')))
			$context['admin_areas']['layout']['areas']['posts_and_topics'] = '<a href="' . $scripturl . '?action=postsettings">' . $txt['manageposts'] . '</a>';
		if (allowedTo('admin_forum'))
		{
			$context['admin_areas']['layout']['areas']['manage_calendar'] = '<a href="' . $scripturl . '?action=managecalendar">' . $txt['manage_calendar'] . '</a>';
			$context['admin_areas']['layout']['areas']['manage_search'] = '<a href="' . $scripturl . '?action=managesearch">' . $txt['manage_search'] . '</a>';
		}
		if (allowedTo('manage_smileys'))
			$context['admin_areas']['layout']['areas']['manage_smileys'] = '<a href="' . $scripturl . '?action=smileys">' . $txt['smileys_manage'] . '</a>';

		if (allowedTo('manage_attachments'))
			$context['admin_areas']['layout']['areas']['manage_attachments'] = '<a href="' . $scripturl . '?action=manageattachments">' . $txt['smf201'] . '</a>';
	}

	// Admin area 'Members'.
	if (allowedTo(array('moderate_forum', 'manage_membergroups', 'manage_bans', 'manage_permissions', 'admin_forum')))
	{
		$context['admin_areas']['members'] = array(
			'title' => $txt[426],
			'areas' => array()
		);

		if (allowedTo('moderate_forum'))
			$context['admin_areas']['members']['areas']['view_members'] = '<a href="' . $scripturl . '?action=viewmembers">' . $txt[5] . '</a>';

		if (allowedTo('manage_membergroups'))
			$context['admin_areas']['members']['areas']['edit_groups'] = '<a href="' . $scripturl . '?action=membergroups;">' . $txt[8] . '</a>';

		if (allowedTo('manage_permissions'))
			$context['admin_areas']['members']['areas']['edit_permissions'] = '<a href="' . $scripturl . '?action=permissions">' . $txt['edit_permissions'] . '</a>';

		if (allowedTo(array('admin_forum', 'moderate_forum')))
			$context['admin_areas']['members']['areas']['registration_center'] = '<a href="' . $scripturl . '?action=regcenter">' . $txt['registration_center'] . '</a>';

		if (allowedTo('manage_bans'))
			$context['admin_areas']['members']['areas']['ban_members'] = '<a href="' . $scripturl . '?action=ban">' . $txt['ban_title'] . '</a>';
	}

	// Admin area 'Maintenance Controls'.
	if (allowedTo('admin_forum'))
	{
		$context['admin_areas']['maintenance'] = array(
			'title' => $txt[501],
			'areas' => array(
				'maintain_forum' => '<a href="' . $scripturl . '?action=maintain">' . $txt['maintain_title'] . '</a>',
				'generate_reports' => '<a href="' . $scripturl . '?action=reports">' . $txt['generate_reports'] . '</a>',
				// !!! Why?  Don't you want to take care of errors in the order they happened, like normal people and programmers?  Seeing them backwards helps no one, and only increases confusion.
				// !!! You know I've argued this before.
				'view_errors' => '<a href="' . $scripturl . '?action=viewErrorLog;desc">' . $txt['errlog1'] . '</a>'
			)
		);

		if (!empty($modSettings['modlog_enabled']))
			$context['admin_areas']['maintenance']['areas']['view_moderation_log'] = '<a href="' . $scripturl . '?action=modlog">' . $txt['modlog_view'] . '</a>';
	}

	// Make sure the administrator has a valid session...
	validateSession();

	// Figure out which one we're in now...
	foreach ($context['admin_areas'] as $id => $section)
		if (isset($section[$area]))
			$context['admin_section'] = $id;
	$context['admin_area'] = $area;

	// obExit will know what to do!
	$context['template_layers'][] = 'admin';
}

// Usage: logAction('remove', array('starter' => $ID_MEMBER_STARTED));
function logAction($action, $extra = array())
{
	global $db_prefix, $ID_MEMBER, $modSettings, $user_info;

	if (!is_array($extra))
		trigger_error('logAction(): data is not an array with action \'' . $action . '\'', E_USER_NOTICE);

	if (isset($extra['topic']) && !is_numeric($extra['topic']))
		trigger_error('logAction(): data\'s topic is not an number', E_USER_NOTICE);
	if (isset($extra['member']) && !is_numeric($extra['member']))
		trigger_error('logAction(): data\'s member is not an number', E_USER_NOTICE);

	if (!empty($modSettings['modlog_enabled']))
	{
		db_query("
			INSERT INTO {$db_prefix}log_actions
				(logTime, ID_MEMBER, ip, action, extra)
			VALUES (" . time() . ", $ID_MEMBER, SUBSTRING('$user_info[ip]', 1, 16), SUBSTRING('$action', 1, 30),
				SUBSTRING('" . addslashes(serialize($extra)) . "', 1, 65534))", __FILE__, __LINE__);

		return db_insert_id();
	}

	return false;
}

// Track Statistics.
function trackStats($stats = array())
{
	global $db_prefix, $modSettings;
	static $cache_stats = array();

	if (empty($modSettings['trackStats']))
		return false;
	if (!empty($stats))
		return $cache_stats = array_merge($cache_stats, $stats);
	elseif (empty($cache_stats))
		return false;

	$setStringUpdate = '';
	foreach ($cache_stats as $field => $change)
	{
		$setStringUpdate .= '
			' . $field . ' = ' . ($change === '+' ? $field . ' + 1' : $change) . ',';

		if ($change === '+')
			$cache_stats[$field] = 1;
	}

	$date = strftime('%Y-%m-%d', forum_time(false));
	db_query("
		UPDATE {$db_prefix}log_activity
		SET" . substr($setStringUpdate, 0, -1) . "
		WHERE date = '$date'
		LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows() == 0)
	{
		db_query("
			INSERT IGNORE INTO {$db_prefix}log_activity
				(date, " . implode(', ', array_keys($cache_stats)) . ")
			VALUES ('$date', " . implode(', ', $cache_stats) . ')', __FILE__, __LINE__);
	}

	// Don't do this again.
	$cache_stats = array();

	return true;
}

// Make sure the user isn't posting over and over again.
function spamProtection($error_type)
{
	global $modSettings, $txt, $db_prefix, $user_info;

	// Delete old entries... if you can moderate this board or this is login, override spamWaitTime with 2.
	if ($error_type == 'spam' && !allowedTo('moderate_board'))
		db_query("
			DELETE FROM {$db_prefix}log_floodcontrol
			WHERE logTime < " . (time() - $modSettings['spamWaitTime']), __FILE__, __LINE__);
	else
		db_query("
			DELETE FROM {$db_prefix}log_floodcontrol
			WHERE (logTime < " . (time() - 2) . " AND ip = '$user_info[ip]')
				OR logTime < " . (time() - $modSettings['spamWaitTime']), __FILE__, __LINE__);

	// Add a new entry, deleting the old if necessary.
	db_query("
		REPLACE INTO {$db_prefix}log_floodcontrol
			(ip, logTime)
		VALUES (SUBSTRING('$user_info[ip]', 1, 16), " . time() . ")", __FILE__, __LINE__);
	// If affected is 0 or 2, it was there already.
	if (db_affected_rows() != 1)
	{
		// Spammer!  You only have to wait a *few* seconds!
		fatal_lang_error($error_type . 'WaitTime_broken', false, array($modSettings['spamWaitTime']));
		return true;
	}

	// They haven't posted within the limit.
	return false;
}

// Get the size of a specified image with better error handling.
function url_image_size($url)
{
	global $sourcedir;

	// Can we pull this from the cache... please please?
	if (($temp = cache_get_data('url_image_size-' . md5($url), 240)) !== null)
		return $temp;
	$t = microtime();

	// Get the host to pester...
	preg_match('~^\w+://(.+?)/(.*)$~', $url, $match);

	// Can't figure it out, just try the image size.
	if ($url == '' || $url == 'http://' || $url == 'https://')
		return false;
	elseif (!isset($match[1]))
		$size = @getimagesize($url);
	else
	{
		// Try to connect to the server... give it half a second.
		$temp = 0;
		$fp = @fsockopen($match[1], 80, $temp, $temp, 0.5);

		// Successful?  Continue...
		if ($fp != false)
		{
			// Send the HEAD request (since we don't have to worry about chunked, HTTP/1.1 is fine here.)
			fwrite($fp, 'HEAD /' . $match[2] . ' HTTP/1.1' . "\r\n" . 'Host: ' . $match[1] . "\r\n" . 'User-Agent: PHP/SMF' . "\r\n" . 'Connection: close' . "\r\n\r\n");

			// Read in the HTTP/1.1 or whatever.
			$test = substr(fgets($fp, 11), -1);
			fclose($fp);

			// See if it returned a 404/403 or something.
			if ($test < 4)
			{
				$size = @getimagesize($url);

				// This probably means allow_url_fopen is off, let's try GD.
				if ($size === false && function_exists('imagecreatefromstring'))
				{
					include_once($sourcedir . '/Subs-Package.php');

					// It's going to hate us for doing this, but another request...
					$image = @imagecreatefromstring(fetch_web_data($url));
					if ($image !== false)
					{
						$size = array(imagesx($image), imagesy($image));
						imagedestroy($image);
					}
				}
			}
		}
	}

	// If we didn't get it, we failed.
	if (!isset($size))
		$size = false;

	// If this took a long time, we may never have to do it again, but then again we might...
	if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $t)) > 0.8)
		cache_put_data('url_image_size-' . md5($url), $size, 240);

	// Didn't work.
	return $size;
}

function determineTopicClass(&$topic_context)
{
	// Set topic class depending on locked status and number of replies.
	if ($topic_context['is_very_hot'])
		$topic_context['class'] = 'veryhot';
	elseif ($topic_context['is_hot'])
		$topic_context['class'] = 'hot';
	else
		$topic_context['class'] = 'normal';

	$topic_context['class'] .= $topic_context['is_poll'] ? '_poll' : '_post';

	if ($topic_context['is_locked'])
		$topic_context['class'] .= '_locked';

	if ($topic_context['is_sticky'])
		$topic_context['class'] .= '_sticky';

	// This is so old themes will still work.
	$topic_context['extended_class'] = &$topic_context['class'];
}

// Sets up the basic theme context stuff.
function setupThemeContext()
{
	global $modSettings, $user_info, $scripturl, $context, $settings, $options, $txt, $maintenance;

	// Get some news...
	$context['news_lines'] = explode("\n", str_replace("\r", '', trim(addslashes($modSettings['news']))));
	$context['fader_news_lines'] = array();
	for ($i = 0, $n = count($context['news_lines']); $i < $n; $i++)
	{
		if (trim($context['news_lines'][$i]) == '')
			continue;

		// Clean it up for presentation ;).
		$context['news_lines'][$i] = parse_bbc(stripslashes(trim($context['news_lines'][$i])), true, 'news' . $i);

		// Gotta be special for the javascript.
		$context['fader_news_lines'][$i] = strtr(addslashes($context['news_lines'][$i]), array('/' => '\/', '<a href=' => '<a hre" + "f='));
	}
	$context['random_news_line'] = $context['news_lines'][mt_rand(0, count($context['news_lines']) - 1)];

	if (!$user_info['is_guest'])
	{
		$context['user']['messages'] = &$user_info['messages'];
		$context['user']['unread_messages'] = &$user_info['unread_messages'];

		// Personal message popup...
		if ($user_info['unread_messages'] > (isset($_SESSION['unread_messages']) ? $_SESSION['unread_messages'] : 0))
			$context['user']['popup_messages'] = true;
		else
			$context['user']['popup_messages'] = false;
		$_SESSION['unread_messages'] = $user_info['unread_messages'];

		if (allowedTo('moderate_forum'))
			$context['unapproved_members'] = !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 2 ? $modSettings['unapprovedMembers'] : 0;

		$context['user']['avatar'] = array();

		// Figure out the avatar... uploaded?
		if ($user_info['avatar']['url'] == '' && !empty($user_info['avatar']['ID_ATTACH']))
			$context['user']['avatar']['href'] = $user_info['avatar']['custom_dir'] ? $modSettings['custom_avatar_url'] . '/' . $user_info['avatar']['filename'] : $scripturl . '?action=dlattach;attach=' . $user_info['avatar']['ID_ATTACH'] . ';type=avatar';
		// Full URL?
		elseif (substr($user_info['avatar']['url'], 0, 7) == 'http://')
		{
			$context['user']['avatar']['href'] = $user_info['avatar']['url'];

			if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
			{
				if (!empty($modSettings['avatar_max_width_external']))
					$context['user']['avatar']['width'] = $modSettings['avatar_max_width_external'];
				if (!empty($modSettings['avatar_max_height_external']))
					$context['user']['avatar']['height'] = $modSettings['avatar_max_height_external'];
			}
		}
		// Otherwise we assume it's server stored?
		elseif ($user_info['avatar']['url'] != '')
			$context['user']['avatar']['href'] = $modSettings['avatar_url'] . '/' . htmlspecialchars($user_info['avatar']['url']);

		if (!empty($context['user']['avatar']))
			$context['user']['avatar']['image'] = '<img src="' . $context['user']['avatar']['href'] . '"' . (isset($context['user']['avatar']['width']) ? ' width="' . $context['user']['avatar']['width'] . '"' : '') . (isset($context['user']['avatar']['height']) ? ' height="' . $context['user']['avatar']['height'] . '"' : '') . ' alt="" class="avatar" border="0" />';

		// Figure out how long they've been logged in.
		$context['user']['total_time_logged_in'] = array(
			'days' => floor($user_info['total_time_logged_in'] / 86400),
			'hours' => floor(($user_info['total_time_logged_in'] % 86400) / 3600),
			'minutes' => floor(($user_info['total_time_logged_in'] % 3600) / 60)
		);
	}
	else
	{
		$context['user']['messages'] = 0;
		$context['user']['unread_messages'] = 0;
		$context['user']['avatar'] = array();
		$context['user']['total_time_logged_in'] = array('days' => 0, 'hours' => 0, 'minutes' => 0);
		$context['user']['popup_messages'] = false;

		if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1)
			$txt['welcome_guest'] .= $txt['welcome_guest_activate'];

		// If we've upgraded recently, go easy on the passwords.
		if (!empty($modSettings['disableHashTime']) && ($modSettings['disableHashTime'] == 1 || time() < $modSettings['disableHashTime']))
			$context['disable_login_hashing'] = true;
		elseif ($context['browser']['is_ie5'] || $context['browser']['is_ie5.5'])
			$context['disable_login_hashing'] = true;
	}

	// Set up the menu privileges.
	$context['allow_search'] = allowedTo('search_posts');
	$context['allow_admin'] = allowedTo(array('admin_forum', 'manage_boards', 'manage_permissions', 'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news', 'manage_attachments', 'manage_smileys'));
	$context['allow_edit_profile'] = !$user_info['is_guest'] && allowedTo(array('profile_view_own', 'profile_view_any', 'profile_identity_own', 'profile_identity_any', 'profile_extra_own', 'profile_extra_any', 'profile_remove_own', 'profile_remove_any', 'moderate_forum', 'manage_membergroups'));
	$context['allow_memberlist'] = allowedTo('view_mlist');
	$context['allow_calendar'] = allowedTo('calendar_view') && !empty($modSettings['cal_enabled']);

	$context['allow_pm'] = allowedTo('pm_read');

	$context['in_maintenance'] = !empty($maintenance);
	$context['current_time'] = timeformat(time(), false);
	$context['current_action'] = isset($_GET['action']) ? $_GET['action'] : '';
	$context['show_quick_login'] = !empty($modSettings['enableVBStyleLogin']) && $user_info['is_guest'];

	if (empty($settings['theme_version']))
		$context['show_vBlogin'] = $context['show_quick_login'];

	// This is here because old index templates might still use it.
	$context['show_news'] = !empty($settings['enable_news']);

	// This is done to make it easier to add to all themes...
	if ($context['user']['popup_messages'] && !empty($options['popup_messages']) && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'pm'))
	{
		$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		if (confirm("' . $txt['show_personal_messages'] . '"))
			window.open("' . $scripturl . '?action=pm");
	// ]]></script>';
	}

	// Resize avatars the fancy, but non-GD requiring way.
	if ($modSettings['avatar_action_too_large'] == 'option_js_resize' && (!empty($modSettings['avatar_max_width_external']) || !empty($modSettings['avatar_max_height_external'])))
	{
		$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_avatarMaxWidth = ' . (int) $modSettings['avatar_max_width_external'] . ';
		var smf_avatarMaxHeight = ' . (int) $modSettings['avatar_max_height_external'] . ';';

		if (!$context['browser']['is_ie'] && !$context['browser']['is_mac_ie'])
			$context['html_headers'] .= '
	window.addEventListener("load", smf_avatarResize, false);';
		else
			$context['html_headers'] .= '
	var window_oldAvatarOnload = window.onload;
	window.onload = smf_avatarResize;';

		// !!! Move this over to script.js?
		$context['html_headers'] .= '
	// ]]></script>';
	}

	// This looks weird, but it's because BoardIndex.php references the variable.
	$context['common_stats']['latest_member'] = array(
		'id' => $modSettings['latestMember'],
		'name' => $modSettings['latestRealName'],
		'href' => $scripturl . '?action=profile;u=' . $modSettings['latestMember'],
		'link' => '<a href="' . $scripturl . '?action=profile;u=' . $modSettings['latestMember'] . '">' . $modSettings['latestRealName'] . '</a>',
	);
	$context['common_stats'] = array(
		'total_posts' => comma_format($modSettings['totalMessages']),
		'total_topics' => comma_format($modSettings['totalTopics']),
		'total_members' => comma_format($modSettings['totalMembers']),
		'latest_member' => $context['common_stats']['latest_member'],
	);

	if (empty($settings['theme_version']))
		$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_scripturl = "' . $scripturl . '";
	// ]]></script>';

	if (!isset($context['page_title']))
		$context['page_title'] = '';
}

// This is the only template included in the sources...
function template_rawdata()
{
	global $context;

	echo $context['raw_data'];
}

function template_header()
{
	global $txt, $modSettings, $context, $settings, $user_info, $boarddir;

	setupThemeContext();

	// Print stuff to prevent caching of pages (except on attachment errors, etc.)
	if (empty($context['no_last_modified']))
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		// Are we debugging the template/html content?
		if (!isset($_REQUEST['xml']) && isset($_GET['debug']) && !$context['browser']['is_ie'] && !WIRELESS)
			header('Content-Type: application/xhtml+xml');
		elseif (!isset($_REQUEST['xml']) && !WIRELESS)
			header('Content-Type: text/html; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	}

	header('Content-Type: text/' . (isset($_REQUEST['xml']) ? 'xml' : 'html') . '; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	foreach ($context['template_layers'] as $layer)
	{
		loadSubTemplate($layer . '_above', true);

		// May seem contrived, but this is done in case the main layer isn't there...
		if ($layer == 'main' && allowedTo('admin_forum') && !$user_info['is_guest'])
		{
			$securityFiles = array('install.php', 'webinstall.php', 'upgrade.php', 'convert.php', 'repair_paths.php', 'repair_settings.php');
			foreach ($securityFiles as $i => $securityFile)
			{
				if (!file_exists($boarddir . '/' . $securityFile))
					unset($securityFiles[$i]);
			}

			if (!empty($securityFiles))
			{
				echo '
		<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9;">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">', $txt['smf299'], '</b><br />
			<div style="padding-left: 6ex;">';

				foreach ($securityFiles as $securityFile)
					echo '
			', $txt['smf300'], '<b>', $securityFile, '</b>!<br />';

				echo '
			</div>
		</div>';
			}
		}
		// If the user is banned from posting inform them of it.
		elseif ($layer == 'main' && isset($_SESSION['ban']['cannot_post']))
		{
			echo '
				<div class="windowbg" style="margin: 2ex; padding: 2ex; border: 2px dashed red; color: red;">
					', sprintf($txt['you_are_post_banned'], $user_info['is_guest'] ? $txt[28] : $user_info['name']);

			if (!empty($_SESSION['ban']['cannot_post']['reason']))
				echo '
					<div style="padding-left: 4ex; padding-top: 1ex;">', $_SESSION['ban']['cannot_post']['reason'], '</div>';

			echo '
				</div>';
		}
	}

	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $settings['default_theme_url'];
		$settings['images_url'] = $settings['default_images_url'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}
}

// Show the copyright...
function theme_copyright($get_it = false)
{
	global $forum_copyright, $context, $boardurl, $forum_version, $txt, $modSettings;
	static $found = false;

	// DO NOT MODIFY THIS FUNCTION.  DO NOT REMOVE YOUR COPYRIGHT.
	// DOING SO VOIDS YOUR LICENSE AND IS ILLEGAL.

	// Meaning, this is the footer checking in..
	if ($get_it === true)
		return $found;

	// Naughty, naughty.
	if (mt_rand(0, 2) == 1)
	{
		$temporary = preg_replace('~<!--.+?-->~s', '', ob_get_contents());
		if (strpos($temporary, '<!--') !== false)
			echo '-->';
	}

	// For SSI and other things, detect the version.
	if (!isset($forum_version) || strpos($forum_version, 'SMF') === false || isset($_GET['checkcopyright']))
	{
		$data = substr(file_get_contents(__FILE__), 0, 4096);
		if (preg_match('~\*\s*Software\s+Version:\s+(SMF\s+.+?)[\s]{2}~i', $data, $match) == 0)
			$match = array('', 'SMF');
		$forum_copyright = preg_replace('~(<a href="http://www.simplemachines.org/"[^>]+>)</a>~', '$1' . $match[1] . '</a>', $forum_copyright);
	}

	// Lewis Media no longer holds the copyright.
	$forum_copyright = str_replace(array('Lewis Media', 'href="http://www.lewismedia.com/"', '2001-'), array('Simple Machines LLC', 'href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software"', ''), $forum_copyright);

	echo '
		<span class="smalltext" style="display: inline; visibility: visible; font-family: Verdana, Arial, sans-serif;">';

	if ($get_it == 'none')
	{
		$found = true;
		echo '
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2009 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
	}
	// If it's in the copyright, and we are outputting it... it's been found.
	elseif (isset($modSettings['copyright_key']) && sha1($modSettings['copyright_key'] . 'banjo') == '1d01885ece7a9355bdeb22ed107f0ffa8c323026'){$found = true; return;}elseif ((strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" onclick="this.href += \'referer.php?forum=' . urlencode($context['forum_name'] . '|' . $boardurl . '|' . $forum_version) . '\';" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">SMF') !== false)&&((strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy;') !== false && (strpos($forum_copyright, 'Lewis Media</a>') !== false || strpos($forum_copyright, 'Simple Machines LLC</a>') !== false)) || strpos($forum_copyright, '<a href="http://www.lewismedia.com/">Lewis Media</a>') !== false || strpos($forum_copyright, '<a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>') !== false || (strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php"') !== false &&	strpos($forum_copyright, 'Simple Machines LLC') !== false))){$found = true; echo $forum_copyright;}

	echo '
		</span>';
}

function template_footer()
{
	global $context, $settings, $modSettings, $time_start, $db_count;

	// Show the load time?  (only makes sense for the footer.)
	$context['show_load_time'] = !empty($modSettings['timeLoadPageEnable']);
	$context['load_time'] = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 3);
	$context['load_queries'] = $db_count;

	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $settings['actual_theme_url'];
		$settings['images_url'] = $settings['actual_images_url'];
		$settings['theme_dir'] = $settings['actual_theme_dir'];
	}

	foreach (array_reverse($context['template_layers']) as $layer)
		loadSubTemplate($layer . '_below', true);

	// Do not remove hard-coded text - it's in here so users cannot change the text easily. (as if it were in language file)
	if (!theme_copyright(true) && !empty($context['template_layers']) && SMF !== 'SSI' && !WIRELESS)
	{
		// DO NOT MODIFY THIS SECTION.  DO NOT REMOVE YOUR COPYRIGHT.
		// DOING SO VOIDS YOUR LICENSE AND IS ILLEGAL.

		echo '
			<div style="text-align: center !important; display: block !important; visibility: visible !important; font-size: large !important; font-weight: bold; color: black !important; background-color: white !important;">
				Sorry, the copyright must be in the template.<br />
				Please notify this forum\'s administrator that this site is missing the copyright message for <a href="http://www.simplemachines.org/" style="color: black !important; font-size: large !important;">SMF</a> so they can rectify the situation. Display of copyright is a <a href="http://www.simplemachines.org/about/license.php" style="color: red;">legal requirement</a>. For more information on this please visit the <a href="http://www.simplemachines.org">Simple Machines</a> website.', empty($context['user']['is_admin']) ? '' : '<br />
				Not sure why this message is appearing?  <a href="http://www.simplemachines.org/redirect/index.php?copyright_error">Take a look at some common causes.</a>', '
			</div>';

		log_error('Copyright removed!!');
	}
}

// Debugging.
function db_debug_junk()
{
	global $context, $scripturl, $boarddir, $modSettings;
	global $db_cache, $db_count, $db_show_debug, $cache_count, $cache_hits;

	// Add to Settings.php if you want to show the debugging information.
	if (!isset($db_show_debug) || $db_show_debug !== true || (isset($_GET['action']) && $_GET['action'] == 'viewquery') || WIRELESS)
		return;

	if (empty($_SESSION['view_queries']))
		$_SESSION['view_queries'] = 0;
	if (empty($context['debug']['language_files']))
		$context['debug']['language_files'] = array();

	$files = get_included_files();
	$total_size = 0;
	for ($i = 0, $n = count($files); $i < $n; $i++)
	{
		$total_size += filesize($files[$i]);
		$files[$i] = strtr($files[$i], array($boarddir => '.'));
	}

	$warnings = 0;
	foreach ($db_cache as $q => $qq)
	{
		if (!empty($qq['w']))
			$warnings += count($qq['w']);
	}

	$_SESSION['debug'] = &$db_cache;

	// Gotta have valid HTML ;).
	$temp = ob_get_contents();
	if (function_exists('ob_clean'))
		ob_clean();
	else
	{
		ob_end_clean();
		ob_start('ob_sessrewrite');
	}

	echo preg_replace('~</body>\s*</html>~', '', $temp), '
<div class="smalltext" style="text-align: left; margin: 1ex;">
	Templates: ', count($context['debug']['templates']), ': <i>', implode('</i>, <i>', $context['debug']['templates']), '</i>.<br />
	Sub templates: ', count($context['debug']['sub_templates']), ': <i>', implode('</i>, <i>', $context['debug']['sub_templates']), '</i>.<br />
	Language files: ', count($context['debug']['language_files']), ': <i>', implode('</i>, <i>', $context['debug']['language_files']), '</i>.<br />
	Files included: ', count($files), ' - ', round($total_size / 1024), 'KB. (<a href="javascript:void(0);" onclick="document.getElementById(\'debug_include_info\').style.display = \'inline\'; this.style.display = \'none\'; return false;">show</a><span id="debug_include_info" style="display: none;"><i>', implode('</i>, <i>', $files), '</i></span>)<br />';

	if (!empty($modSettings['cache_enable']) && !empty($cache_hits))
	{
		$entries = array();
		$total_t = 0;
		$total_s = 0;
		foreach ($cache_hits as $h)
		{
			$entries[] = $h['d'] . ' ' . $h['k'] . ': ' . comma_format($h['t'], 5) . ' - ' . $h['s'] . ' bytes';
			$total_t += $h['t'];
			$total_s += $h['s'];
		}

		echo '
	Cache hits: ', $cache_count, ': ', comma_format($total_t, 5), 's for ', comma_format($total_s), ' bytes (<a href="javascript:void(0);" onclick="document.getElementById(\'debug_cache_info\').style.display = \'inline\'; this.style.display = \'none\'; return false;">show</a><span id="debug_cache_info" style="display: none;"><i>', implode('</i>, <i>', $entries), '</i></span>)<br />';
	}

	echo '
	<a href="', $scripturl, '?action=viewquery" target="_blank">Queries used: ', $db_count, $warnings == 0 ? '' : ', ' . $warnings . ' warning(s)', '</a>.<br />
	<br />';

	if ($_SESSION['view_queries'] == 1)
		foreach ($db_cache as $q => $qq)
		{
			$is_select = substr(trim($qq['q']), 0, 6) == 'SELECT' || preg_match('~^INSERT(?: IGNORE)? INTO \w+(?:\s+\([^)]+\))?\s+SELECT .+$~s', trim($qq['q'])) != 0;

			echo '
	<b>', $is_select ? '<a href="' . $scripturl . '?action=viewquery;qq=' . ($q + 1) . '#qq' . $q . '" target="_blank" style="text-decoration: none;">' : '', nl2br(str_replace("\t", '&nbsp;&nbsp;&nbsp;', htmlspecialchars(ltrim($qq['q'], "\n\r")))) . ($is_select ? '</a></b>' : '</b>') . '<br />
	&nbsp;&nbsp;&nbsp;';
			if (!empty($qq['f']) && !empty($qq['l']))
				echo 'in <i>' . $qq['f'] . '</i> line <i>' . $qq['l'] . '</i>, ';
			echo 'which took ' . round($qq['t'], 8) . ' seconds.<br />
	<br />';
		}

	echo '
	<a href="' . $scripturl . '?action=viewquery;sa=hide">[' . (empty($_SESSION['view_queries']) ? 'show' : 'hide') . ' queries]</a>
</div></body></html>';
}

// Get an attachment's encrypted filename.  If $new is true, won't check for file existence.
function getAttachmentFilename($filename, $attachment_id, $new = false, $file_hash = '')
{
	global $modSettings, $db_prefix;

	// Just make up a nice hash...
	if ($new)
		return sha1(md5($filename . time()) . mt_rand());

	// Grab the file hash if it wasn't added.
	if ($file_hash === '')
	{
		$request = db_query("
			SELECT file_hash
			FROM {$db_prefix}attachments
			WHERE ID_ATTACH = " . (int) $attachment_id, __FILE__, __LINE__);

		if (mysql_num_rows($request) === 0)
			return false;

		list ($file_hash) = mysql_fetch_row($request);

		mysql_free_result($request);
	}

	// In case of files from the old system, do a legacy call.
	if (empty($file_hash))
		return getLegacyAttachmentFilename($filename, $attachment_id, $new);

	return $modSettings['attachmentUploadDir'] . '/' . $attachment_id . '_' . $file_hash;
}

function getLegacyAttachmentFilename($filename, $attachment_id, $new = false)
{
	global $modSettings;

	// Remove special accented characters - ie. sí.
	$clean_name = strtr($filename, 'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ', 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy');
	$clean_name = strtr($clean_name, array('Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u'));

	// Sorry, no spaces, dots, or anything else but letters allowed.
	$clean_name = preg_replace(array('/\s/', '/[^\w_\.\-]/'), array('_', ''), $clean_name);

	$enc_name = $attachment_id . '_' . strtr($clean_name, '.', '_') . md5($clean_name);
	$clean_name = preg_replace('~\.[\.]+~', '.', $clean_name);

	if ($attachment_id == false || ($new && empty($modSettings['attachmentEncryptFilenames'])))
		return $clean_name;
	elseif ($new)
		return $enc_name;

	if (file_exists($modSettings['attachmentUploadDir'] . '/' . $enc_name))
		$filename = $modSettings['attachmentUploadDir'] . '/' . $enc_name;
	else
		$filename = $modSettings['attachmentUploadDir'] . '/' . $clean_name;

	return $filename;
}

// Lookup an IP; try shell_exec first because we can do a timeout on it.
function host_from_ip($ip)
{
	global $modSettings;

	if (($host = cache_get_data('hostlookup-' . $ip, 600)) !== null)
		return $host;
	$t = microtime();

	// If we can't access nslookup/host, PHP 4.1.x might just crash.
	if (@version_compare(PHP_VERSION, '4.2.0') == -1)
		$host = false;

	// Try the Linux host command, perhaps?
	if (!isset($host) && (strpos(strtolower(PHP_OS), 'win') === false || strpos(strtolower(PHP_OS), 'darwin') !== false) && mt_rand(0, 1) == 1)
	{
		if (!isset($modSettings['host_to_dis']))
			$test = @shell_exec('host -W 1 ' . @escapeshellarg($ip));
		else
			$test = @shell_exec('host ' . @escapeshellarg($ip));

		// Did host say it didn't find anything?
		if (strpos($test, 'not found') !== false)
			$host = '';
		// Invalid server option?
		elseif ((strpos($test, 'invalid option') || strpos($test, 'Invalid query name 1')) && !isset($modSettings['host_to_dis']))
			updateSettings(array('host_to_dis' => 1));
		// Maybe it found something, after all?
		elseif (preg_match('~\s([^\s]+?)\.\s~', $test, $match) == 1)
			$host = $match[1];
	}

	// This is nslookup; usually only Windows, but possibly some Unix?
	if (!isset($host) && strpos(strtolower(PHP_OS), 'win') !== false && strpos(strtolower(PHP_OS), 'darwin') === false && mt_rand(0, 1) == 1)
	{
		$test = @shell_exec('nslookup -timeout=1 ' . @escapeshellarg($ip));
		if (strpos($test, 'Non-existent domain') !== false)
			$host = '';
		elseif (preg_match('~Name:\s+([^\s]+)~', $test, $match) == 1)
			$host = $match[1];
	}

	// This is the last try :/.
	if (!isset($host) || $host === false)
		$host = @gethostbyaddr($ip);

	// It took a long time, so let's cache it!
	if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $t)) > 0.5)
		cache_put_data('hostlookup-' . $ip, $host, 600);

	return $host;
}

// Chops a string into words and prepares them to be inserted into (or searched from) the database.
function text2words($text, $max_chars = 20, $encrypt = false)
{
	global $func, $context;

	// Step 1: Remove entities/things we don't consider words:
	$words = preg_replace('~([\x0B\0' . ($context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{A0}' : pack('C*', 0xC2, 0xA0)) : '\xA0') . '\t\r\s\n(){}\\[\\]<>!@$%^*.,:+=`\~\?/\\\\]|&(amp|lt|gt|quot);)+~' . ($context['utf8'] ? 'u' : ''), ' ', strtr($text, array('<br />' => ' ')));

	// Step 2: Entities we left to letters, where applicable, lowercase.
	$words = un_htmlspecialchars($func['strtolower']($words));

	// Step 3: Ready to split apart and index!
	$words = explode(' ', $words);

	if ($encrypt)
	{
		$possible_chars = array_flip(array_merge(range(46, 57), range(65, 90), range(97, 122)));
		$returned_ints = array();
		foreach ($words as $word)
		{
			if (($word = trim($word, '-_\'')) !== '')
			{
				$encrypted = substr(crypt($word, 'uk'), 2, $max_chars);
				$total = 0;
				for ($i = 0; $i < $max_chars; $i++)
					$total += $possible_chars[ord($encrypted{$i})] * pow(63, $i);
				$returned_ints[] = $max_chars == 4 ? min($total, 16777215) : $total;
			}
		}
		return array_unique($returned_ints);
	}
	else
	{
		// Trim characters before and after and add slashes for database insertion.
		$returned_words = array();
		foreach ($words as $word)
			if (($word = trim($word, '-_\'')) !== '')
				$returned_words[] = addslashes($max_chars === null ? $word : substr($word, 0, $max_chars));

		// Filter out all words that occur more than once.
		return array_unique($returned_words);
	}
}

// Creates an image/text button
function create_button($name, $alt, $label = '', $custom = '')
{
	global $settings, $txt, $context;

	if (!$settings['use_image_buttons'])
		return $txt[$alt];
	elseif (!empty($settings['use_buttons']))
		return '<img src="' . $settings['images_url'] . '/buttons/' . $name . '" alt="' . $txt[$alt] . '" ' . $custom . ' />' . ($label != '' ? '<b>' . $txt[$label] . '</b>' : '');
	else
		return '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/' . $name . '" alt="' . $txt[$alt] . '" ' . $custom . ' />';
}

// Generate a random seed and ensure it's stored in settings.
function smf_seed_generator()
{
	global $modSettings;

	// Never existed?
	if (empty($modSettings['rand_seed']))
	{
		$modSettings['rand_seed'] = microtime() * 1000000;
		updateSettings(array('rand_seed' => $modSettings['rand_seed']));
	}

	if (@version_compare(PHP_VERSION, '4.2.0') == -1)
	{
		$seed = ($modSettings['rand_seed'] + ((double) microtime() * 1000003)) & 0x7fffffff;
		mt_srand($seed);
	}

	// Change the seed.
	updateSettings(array('rand_seed' => mt_rand()));
}

?>