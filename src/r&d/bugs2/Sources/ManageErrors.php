<?php
/**********************************************************************************
* ManageErrors.php                                                                *
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

/* Show a list of all errors that were logged on the forum.

	void ViewErrorLog()
		- sets all the context up to show the error log for maintenance.
		- uses the Errors template and error_log sub template.
		- requires the maintain_forum permission.
		- uses the 'view_errors' administration area.
		- accessed from ?action=viewErrorLog.

	void deleteErrors()
		- deletes all or some of the errors in the error log.
		- applies any necessary filters to deletion.
		- should only be called by ViewErrorLog().
		- attempts to TRUNCATE the table to reset the auto_increment.
		- redirects back to the error log when done.
*/

// View the forum's error log.
function ViewErrorLog()
{
	global $db_prefix, $scripturl, $txt, $context, $modSettings, $user_profile, $filter;

	// Check for the administrative permission to do this.
	isAllowedTo('admin_forum');

	// Administration bar, templates, etc...
	adminIndex('view_errors');
	loadTemplate('Errors');

	// You can filter by any of the following columns:
	$filters = array(
		'ID_MEMBER' => &$txt[35],
		'ip' => &$txt['ip_address'],
		'session' => &$txt['session'],
		'url' => &$txt['error_url'],
		'message' => &$txt['error_message']
	);

	// Set up the filtering...
	if (isset($_GET['value'], $_GET['filter']) && isset($filters[$_GET['filter']]))
		$filter = array(
			'variable' => $_GET['filter'],
			'value' => array(
				'sql' => addslashes($_GET['filter'] == 'message' || $_GET['filter'] == 'url' ? base64_decode(strtr($_GET['value'], array(' ' => '+'))) : addcslashes($_GET['value'], '\\_%'))
			),
			'href' => ';filter=' . $_GET['filter'] . ';value=' . $_GET['value'],
			'entity' => $filters[$_GET['filter']]
		);

	// Deleting, are we?
	if (isset($_POST['delall']) || isset($_POST['delete']))
		deleteErrors();

	// Just how many errors are there?
	$result = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}log_errors" . (isset($filter) ? "
		WHERE $filter[variable] LIKE '{$filter['value']['sql']}'" : ''), __FILE__, __LINE__);
	list ($num_errors) = mysql_fetch_row($result);
	mysql_free_result($result);

	// If this filter is empty...
	if ($num_errors == 0 && isset($filter))
		redirectexit('action=viewErrorLog' . (isset($_REQUEST['desc']) ? ';desc' : ''));

	// Clean up start.
	if (!isset($_GET['start']) || $_GET['start'] < 0)
		$_GET['start'] = 0;

	// Do we want to reverse error listing?
	$context['sort_direction'] = isset($_REQUEST['desc']) ? 'down' : 'up';

	// Set the page listing up.
	$context['page_index'] = constructPageIndex($scripturl . '?action=viewErrorLog' . ($context['sort_direction'] == 'down' ? ';desc' : '') . (isset($filter) ? $filter['href'] : ''), $_GET['start'], $num_errors, $modSettings['defaultMaxMessages']);
	$context['start'] = $_GET['start'];

	// Find and sort out the errors.
	$request = db_query("
		SELECT ID_ERROR, ID_MEMBER, ip, url, logTime, message, session
		FROM {$db_prefix}log_errors" . (isset($filter) ? "
		WHERE $filter[variable] LIKE '{$filter['value']['sql']}'" : '') . "
		ORDER BY ID_ERROR " . ($context['sort_direction'] == 'down' ? 'DESC' : '') . "
		LIMIT $_GET[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['errors'] = array();
	$members = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$search_message = preg_replace('~&lt;span class=&quot;remove&quot;&gt;(.+?)&lt;/span&gt;~', '%', addcslashes($row['message'], '\\_%'));
		if ($search_message == $filter['value']['sql'])
			$search_message = addcslashes($row['message'], '\\_%');
		$show_message = strtr(strtr(preg_replace('~&lt;span class=&quot;remove&quot;&gt;(.+?)&lt;/span&gt;~', '$1', $row['message']), array("\r" => '', '<br />' => "\n", '<' => '&lt;', '>' => '&gt;', '"' => '&quot;')), array("\n" => '<br />'));

		$context['errors'][] = array(
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'ip' => $row['ip'],
				'session' => $row['session']
			),
			'time' => timeformat($row['logTime']),
			'timestamp' => $row['logTime'],
			'url' => array(
				'html' => htmlspecialchars($scripturl . $row['url']),
				'href' => base64_encode(addcslashes($row['url'], '\\_%'))
			),
			'message' => array(
				'html' => $show_message,
				'href' => base64_encode($search_message)
			),
			'id' => $row['ID_ERROR']
		);

		// Make a list of members to load later.
		$members[$row['ID_MEMBER']] = $row['ID_MEMBER'];
	}
	mysql_free_result($request);

	// Load the member data.
	if (!empty($members))
	{
		// Get some additional member info...
		$request = db_query("
			SELECT ID_MEMBER, memberName, realName
			FROM {$db_prefix}members
			WHERE ID_MEMBER IN (" . implode(', ', $members) . ")
			LIMIT " . count($members), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$members[$row['ID_MEMBER']] = $row;
		mysql_free_result($request);

		// This is a guest...
		$members[0] = array(
			'ID_MEMBER' => 0,
			'memberName' => '',
			'realName' => $txt[28]
		);

		// Go through each error and tack the data on.
		foreach ($context['errors'] as $id => $dummy)
		{
			$memID = $context['errors'][$id]['member']['id'];
			$context['errors'][$id]['member']['username'] = $members[$memID]['memberName'];
			$context['errors'][$id]['member']['name'] = $members[$memID]['realName'];
			$context['errors'][$id]['member']['href'] = empty($memID) ? '' : $scripturl . '?action=profile;u=' . $memID;
			$context['errors'][$id]['member']['link'] = empty($memID) ? $txt[28] : '<a href="' . $scripturl . '?action=profile;u=' . $memID . '">' . $context['errors'][$id]['member']['name'] . '</a>';
		}
	}

	// Filtering anything?
	if (isset($filter))
	{
		$context['filter'] = &$filter;

		// Set the filtering context.
		if ($filter['variable'] == 'ID_MEMBER')
		{
			$id = $filter['value']['sql'];
			loadMemberData($id, false, 'minimal');
			$context['filter']['value']['html'] = '<a href="' . $scripturl . '?action=profile;u=' . $id . '">' . $user_profile[$id]['realName'] . '</a>';
		}
		elseif ($filter['variable'] == 'url')
			$context['filter']['value']['html'] = "'" . htmlspecialchars($scripturl . stripslashes($filter['value']['sql'])) . "'";
		elseif ($filter['variable'] == 'message')
		{
			$context['filter']['value']['html'] = "'" . strtr(htmlspecialchars(stripslashes($filter['value']['sql'])), array("\n" => '<br />', '&lt;br /&gt;' => '<br />', "\t" => '&nbsp;&nbsp;&nbsp;', '\\_' => '_', '\\%' => '%', '\\\\' => '\\')) . "'";
			$context['filter']['value']['html'] = preg_replace('~&amp;lt;span class=&amp;quot;remove&amp;quot;&amp;gt;(.+?)&amp;lt;/span&amp;gt;~', '$1', $context['filter']['value']['html']);
		}
		else
			$context['filter']['value']['html'] = &$filter['value']['sql'];
	}

	// And this is pretty basic ;).
	$context['page_title'] = $txt['errlog1'];
	$context['has_filter'] = isset($filter);
	$context['sub_template'] = 'error_log';
}

// Delete errors from the database.
function deleteErrors()
{
	global $db_prefix, $filter;

	// Make sure the session exists and is correct; otherwise, might be a hacker.
	checkSession();

	// Delete all or just some?
	if (isset($_POST['delall']) && !isset($filter))
		db_query("
			TRUNCATE {$db_prefix}log_errors", __FILE__, __LINE__);
	// Deleting all with a filter?
	elseif (isset($_POST['delall']) && isset($filter))
		db_query("
			DELETE FROM {$db_prefix}log_errors
			WHERE $filter[variable] LIKE '" . $filter['value']['sql'] . "'", __FILE__, __LINE__);
	// Just specific errors?
	elseif (!empty($_POST['delete']))
	{
		db_query("
			DELETE FROM {$db_prefix}log_errors
			WHERE ID_ERROR IN (" . implode(',', array_unique($_POST['delete'])) . ')', __FILE__, __LINE__);

		// Go back to where we were.
		redirectexit('action=viewErrorLog' . (isset($_REQUEST['desc']) ? ';desc' : '') . ';start=' . $_GET['start'] . (isset($filter) ? ';filter=' . $_GET['filter'] . ';value=' . $_GET['value'] : ''));
	}

	// Back to the error log!
	redirectexit('action=viewErrorLog' . (isset($_REQUEST['desc']) ? ';desc' : ''));
}

?>