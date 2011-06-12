<?php
/**********************************************************************************
* Who.php                                                                         *
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

/*	This file is mainly concerned, or that is to say only concerned, with the
	Who's Online list.  It contains only the following functions:

	void Who()
		- prepares the who's online data for the Who template.
		- uses the Who template (main sub template.) and language file.
		- requires the who_view permission.
		- is enabled with the who_enabled setting.
		- is accessed via ?action=who.

	array determineActions(array urls)
		- determine the actions of the members passed in urls.
		- urls should be a single url (string) or an array of arrays, each
		  inner array being (serialized request data, ID_MEMBER).
	    - returns an array of descriptions if you passed an array, otherwise
		  the string describing their current location.

	Adding actions to the Who's Online list:
	---------------------------------------------------------------------------
		Adding actions to this list is actually relatively easy....
		 - for actions anyone should be able to see, just add a string named
		   whoall_ACTION.  (where ACTION is the action used in index.php.)
		 - for actions that have a subaction which should be represented
		   differently, use whoall_ACTION_SUBACTION.
		 - for actions that include a topic, and should be restricted, use
		   whotopic_ACTION.
		 - for actions that use a message, by msg or quote, use whopost_ACTION.
		 - for administrator-only actions, use whoadmin_ACTION.
		 - for actions that should be viewable only with certain permissions,
		   use whoallow_ACTION and add a list of possible permissions to the
		   $allowedActions array, using ACTION as the key.
*/

// Who's online, and what are they doing?
function Who()
{
	global $db_prefix, $context, $scripturl, $user_info, $txt, $modSettings, $ID_MEMBER, $memberContext;

	// Permissions, permissions, permissions.
	isAllowedTo('who_view');

	// You can't do anything if this is off.
	if (empty($modSettings['who_enabled']))
		fatal_lang_error('who_off', false);

	// Load the 'Who' template.
	loadTemplate('Who');

	// Sort out... the column sorting.
	$sort_methods = array(
		'user' => 'mem.realName',
		'time' => 'lo.logTime'
	);

	// By default order by last time online.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'time';
		$_REQUEST['sort'] = 'lo.logTime';
	}
	// Otherwise default to ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$_REQUEST['sort'] = $sort_methods[$_REQUEST['sort']];
	}

	$context['sort_direction'] = isset($_REQUEST['asc']) ? 'up' : 'down';

	// Get the total amount of members online.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (lo.ID_MEMBER = mem.ID_MEMBER)" . (!allowedTo('moderate_forum') ? "
		WHERE IFNULL(mem.showOnline, 1) = 1" : ''), __FILE__, __LINE__);
	list ($totalMembers) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Prepare some page index variables.
	$context['page_index'] = constructPageIndex($scripturl . '?action=who;sort=' . $context['sort_by'] . (isset($_REQUEST['asc']) ? ';asc' : ''), $_REQUEST['start'], $totalMembers, $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];

	// Look for people online, provided they don't mind if you see they are.
	$request = db_query("
		SELECT
			(UNIX_TIMESTAMP(lo.logTime) - UNIX_TIMESTAMP() + " . time() . ") AS logTime,
			lo.ID_MEMBER, lo.url, INET_NTOA(lo.ip) AS ip, mem.realName, lo.session,
			mg.onlineColor, IFNULL(mem.showOnline, 1) AS showOnline
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (lo.ID_MEMBER = mem.ID_MEMBER)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))" . (!allowedTo('moderate_forum') ? "
		WHERE IFNULL(mem.showOnline, 1) = 1" : '') . "
		ORDER BY $_REQUEST[sort] " . (isset($_REQUEST['asc']) ? 'ASC' : 'DESC') . "
		LIMIT $context[start], $modSettings[defaultMaxMembers]", __FILE__, __LINE__);
	$context['members'] = array();
	$member_ids = array();
	$url_data = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$actions = @unserialize($row['url']);
		if ($actions === false)
			continue;

		// Send the information to the template.
		$context['members'][$row['session']] = array(
			'id' => $row['ID_MEMBER'],
			'ip' => allowedTo('moderate_forum') ? $row['ip'] : '',
			// It is *going* to be today or yesterday, so why keep that information in there?
			'time' => strtr(timeformat($row['logTime']), array($txt['smf10'] => '', $txt['smf10b'] => '')),
			'timestamp' => forum_time(true, $row['logTime']),
			'query' => $actions,
			'is_hidden' => $row['showOnline'] == 0,
			'color' => empty($row['onlineColor']) ? '' : $row['onlineColor']
		);

		$url_data[$row['session']] = array($row['url'], $row['ID_MEMBER']);
		$member_ids[] = $row['ID_MEMBER'];
	}
	mysql_free_result($request);

	// Load the user data for these members.
	loadMemberData($member_ids);

	// Load up the guest user.
	$memberContext[0] = array(
		'id' => 0,
		'name' => $txt[28],
		'group' => $txt[28],
		'href' => '',
		'link' => $txt[28],
		'email' => $txt[28],
		'is_guest' => true
	);

	$url_data = determineActions($url_data);

	// Setup the linktree and page title (do it down here because the language files are now loaded..)
	$context['page_title'] = $txt['who_title'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=who',
		'name' => $txt['who_title']
	);

	// Put it in the context variables.
	foreach ($context['members'] as $i => $member)
	{
		if ($member['id'] != 0)
			$member['id'] = loadMemberContext($member['id']) ? $member['id'] : 0;

		// Keep the IP that came from the database.
		$memberContext[$member['id']]['ip'] = $member['ip'];
		$context['members'][$i]['action'] = isset($url_data[$i]) ? $url_data[$i] : $txt['who_hidden'];
		$context['members'][$i] += $memberContext[$member['id']];
	}

	// Some people can't send personal messages...
	$context['can_send_pm'] = allowedTo('pm_send');
}

function determineActions($urls)
{
	global $txt, $db_prefix, $user_info, $ID_MEMBER, $modSettings;

	if (!allowedTo('who_view'))
		return array();
	loadLanguage('Who');

	// Actions that require a specific permission level.
	$allowedActions = array(
		'admin' => array('moderate_forum', 'manage_membergroups', 'manage_bans', 'admin_forum', 'manage_permissions', 'send_mail', 'manage_attachments', 'manage_smileys', 'manage_boards', 'edit_news'),
		'ban' => array('manage_bans'),
		'boardrecount' => array('admin_forum'),
		'calendar' => array('calendar_view'),
		'editnews' => array('edit_news'),
		'mailing' => array('send_mail'),
		'maintain' => array('admin_forum'),
		'manageattachments' => array('manage_attachments'),
		'manageboards' => array('manage_boards'),
		'mlist' => array('view_mlist'),
		'optimizetables' => array('admin_forum'),
		'repairboards' => array('admin_forum'),
		'search' => array('search_posts'),
		'search2' => array('search_posts'),
		'setcensor' => array('moderate_forum'),
		'setreserve' => array('moderate_forum'),
		'stats' => array('view_stats'),
		'viewErrorLog' => array('admin_forum'),
		'viewmembers' => array('moderate_forum'),
	);

	if (!is_array($urls))
		$url_list = array(array($urls, $ID_MEMBER));
	else
		$url_list = $urls;

	// These are done to later query these in large chunks. (instead of one by one.)
	$topic_ids = array();
	$profile_ids = array();
	$board_ids = array();

	$data = array();
	foreach ($url_list as $k => $url)
	{
		// Get the request parameters..
		$actions = @unserialize($url[0]);
		if ($actions === false)
			continue;

		// Check if there was no action or the action is display.
		if (!isset($actions['action']) || $actions['action'] == 'display')
		{
			// It's a topic!  Must be!
			if (isset($actions['topic']))
			{
				// Assume they can't view it, and queue it up for later.
				$data[$k] = $txt['who_hidden'];
				$topic_ids[(int) $actions['topic']][$k] = $txt['who_topic'];
			}
			// It's a board!
			elseif (isset($actions['board']))
			{
				// Hide first, show later.
				$data[$k] = $txt['who_hidden'];
				$board_ids[$actions['board']][$k] = $txt['who_board'];
			}
			// It's the board index!!  It must be!
			else 
			{	
				$data[$k] = $txt['who_index'];
				// ...or maybe it's just integrated into another system...
				if (isset($modSettings['integrate_whos_online']) && function_exists($modSettings['integrate_whos_online']))
					$data[$k] = $modSettings['integrate_whos_online']($actions);
			}
		}
		// Probably an error or some goon?
		elseif ($actions['action'] == '')
			$data[$k] = $txt['who_index'];

		// Some other normal action...?
		else
		{
			// Viewing/editing a profile.
			if ($actions['action'] == 'profile' || $actions['action'] == 'profile2')
			{
				// Whose?  Their own?
				if (empty($actions['u']))
					$actions['u'] = $url[1];

				$data[$k] = $txt['who_hidden'];
				$profile_ids[(int) $actions['u']][$k] = $actions['action'] == 'profile' ? $txt['who_viewprofile'] : $txt['who_profile'];
			}
			elseif (($actions['action'] == 'post' || $actions['action'] == 'post2') && empty($actions['topic']) && isset($actions['board']))
			{
				$data[$k] = $txt['who_hidden'];
				$board_ids[(int) $actions['board']][$k] = isset($actions['poll']) ? $txt['who_poll'] : $txt['who_post'];
			}
			// A subaction anyone can view... if the language string is there, show it.
			elseif (isset($actions['sa']) && isset($txt['whoall_' . $actions['action'] . '_' . $actions['sa']]))
				$data[$k] = $txt['whoall_' . $actions['action'] . '_' . $actions['sa']];
			// An action any old fellow can look at. (if ['whoall_' . $action] exists, we know everyone can see it.)
			elseif (isset($txt['whoall_' . $actions['action']]))
				$data[$k] = $txt['whoall_' . $actions['action']];
			// Viewable if and only if they can see the board...
			elseif (isset($txt['whotopic_' . $actions['action']]))
			{
				// Find out what topic they are accessing.
				$topic = (int) (isset($actions['topic']) ? $actions['topic'] : (isset($actions['from']) ? $actions['from'] : 0));

				$data[$k] = $txt['who_hidden'];
				$topic_ids[$topic][$k] = $txt['whotopic_' . $actions['action']];
			}
			elseif (isset($txt['whopost_' . $actions['action']]))
			{
				// Find out what message they are accessing.
				$msgid = (int) (isset($actions['msg']) ? $actions['msg'] : (isset($actions['quote']) ? $actions['quote'] : 0));

				$result = db_query("
					SELECT m.ID_TOPIC, m.subject
					FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m)
					WHERE $user_info[query_see_board]
						AND m.ID_MSG = $msgid
						AND m.ID_BOARD = b.ID_BOARD
					LIMIT 1", __FILE__, __LINE__);
				list ($ID_TOPIC, $subject) = mysql_fetch_row($result);
				$data[$k] = sprintf($txt['whopost_' . $actions['action']], $ID_TOPIC, $subject);
				mysql_free_result($result);

				if (empty($ID_TOPIC))
					$data[$k] = $txt['who_hidden'];
			}
			// Viewable only by administrators.. (if it starts with whoadmin, it's admin only!)
			elseif (allowedTo('moderate_forum') && isset($txt['whoadmin_' . $actions['action']]))
				$data[$k] = $txt['whoadmin_' . $actions['action']];
			// Viewable by permission level.
			elseif (isset($allowedActions[$actions['action']]))
			{
				if (allowedTo($allowedActions[$actions['action']]))
					$data[$k] = $txt['whoallow_' . $actions['action']];
				else
					$data[$k] = $txt['who_hidden'];
			}
			// Unlisted or unknown action.
			else
				$data[$k] = $txt['who_unknown'];
		}
	}

	// Load topic names.
	if (!empty($topic_ids))
	{
		$result = db_query("
			SELECT t.ID_TOPIC, m.subject
			FROM ({$db_prefix}boards AS b, {$db_prefix}topics AS t, {$db_prefix}messages AS m)
			WHERE $user_info[query_see_board]
				AND t.ID_TOPIC IN (" . implode(', ', array_keys($topic_ids)) . ")
				AND t.ID_BOARD = b.ID_BOARD
				AND m.ID_MSG = t.ID_FIRST_MSG
			LIMIT " . count($topic_ids), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
		{
			// Show the topic's subject for each of the actions.
			foreach ($topic_ids[$row['ID_TOPIC']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['ID_TOPIC'], censorText($row['subject']));
		}
		mysql_free_result($result);
	}

	// Load board names.
	if (!empty($board_ids))
	{
		$result = db_query("
			SELECT b.ID_BOARD, b.name
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b.ID_BOARD IN (" . implode(', ', array_keys($board_ids)) . ")
			LIMIT " . count($board_ids), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
		{
			// Put the board name into the string for each member...
			foreach ($board_ids[$row['ID_BOARD']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['ID_BOARD'], $row['name']);
		}
		mysql_free_result($result);
	}

	// Load member names for the profile.
	if (!empty($profile_ids) && (allowedTo('profile_view_any') || allowedTo('profile_view_own')))
	{
		$result = db_query("
			SELECT ID_MEMBER, realName
			FROM {$db_prefix}members
			WHERE ID_MEMBER IN (" . implode(', ', array_keys($profile_ids)) . ")
			LIMIT " . count($profile_ids), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
		{
			// If they aren't allowed to view this person's profile, skip it.
			if (!allowedTo('profile_view_any') && $ID_MEMBER != $row['ID_MEMBER'])
				continue;

			// Set their action on each - session/text to sprintf.
			foreach ($profile_ids[$row['ID_MEMBER']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['ID_MEMBER'], $row['realName']);
		}
		mysql_free_result($result);
	}

	if (!is_array($urls))
		return isset($data[0]) ? $data[0] : false;
	else
		return $data;
}

?>