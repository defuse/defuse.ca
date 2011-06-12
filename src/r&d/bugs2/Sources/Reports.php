<?php
/**********************************************************************************
* Reports.php                                                                     *
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

/*	This file is exclusively for generating reports to help assist forum
	administrators keep track of their forum configuration and state. The
	core report generation is done in two areas. Firstly, a report "generator"
	will fill context with relevant data. Secondly, the choice of sub-template
	will determine how this data is shown to the user. It has the following
	functions:

	void ReportsMain()
		- requires the admin_forum permission.
		- loads the Reports template and language files.
		- decides which type of report to generate, if this isn't passed
		  through the querystring it will set the report_type sub-template to
		  force the user to chooose which type.
		- when generating a report chooses which sub_template to use.
		- depends on the cal_enabled setting, and many of the other cal_
		  settings.
		- will call the relevant report generation function.
		- if generating report will call finishTables before returning.
		- accessed through ?action=reports.

	void xxxxxxReport()
		- functions ending with "Report" are responsible for generating data
		  for reporting.
		- they are all called from ReportsMain.
		- never access the context directly, but use the data handling
		  functions to do so.

	void newTable(string title = '', string defaultValue = '',
			string shading = 'all', string width_normal = 'auto',
			string align_normal = 'center', string width_shaded = 'auto',
			string align_shaded = 'auto')
		- the core of this file, it creates a new, but empty, table of data in
		  context, ready for filling using addData().
		- takes a lot of possible attributes, these have the following effect:
			+ title = Title to be displayed with this data table.
			+ defaultValue = Value to be displayed if a key is missing from a
			  row.
			+ shading = Should the left, top or both (all) parts of the table
			  beshaded?
			+ width_normal = width of an unshaded column (auto means not
			  defined).
			+ align_normal = alignment of data in an unshaded column.
			+ width_shaded = width of a shaded column (auto means not
			  defined).
			+ align_shaded = alignment of data in a shaded column.
		- fills the context variable current_table with the ID of the table
		  created.
		- keeps track of the current table count using context variable
		  table_count.

	void addData(array inc_data, int custom_table = null)
		- adds an array of data into an existing table.
		- if there are no existing tables, will create one with default
		  attributes.
		- if custom_table isn't specified, it will use the last table created,
		  if it is specified and doesn't exist the function will return false.
		- if a set of keys have been specified, the function will check each
		  required key is present in the incoming data. If this data is missing
		  the current tables default value will be used.
		- if any key in the incoming data begins with '#sep#', the function
		  will add a seperator accross the table at this point.
		- once the incoming data has been sanitized, it is added to the table.

	void addSeperator(string title = '', int custom_table = null)
		- adds a seperator with title given by attribute "title" after the
		  current row in the table.
		- if there are no existing tables, will create one with default
		  attributes.
		- if custom_table isn't specified, it will use the last table created,
		  if it is specified and doesn't exist the function will return false.
		- if the table is currently having data added by column this may have
		  unpredictable visual results.

	void finishTables()
		- is (unfortunately) required to create some useful variables for
		  templates.
		- foreach data table created, it will count the number of rows and
		  columns in the table.
		- will also create a max_width variable for the table, to give an
		  estimate width for the whole table - if it can.

	void setKeys(string method = 'rows', array keys = array(),
			bool reverse = false)
		- sets the current set of "keys" expected in each data array passed to
		  addData. It also sets the way we are adding data to the data table.
		- method specifies whether the data passed to addData represents a new
		  column, or a new row.
		- keys is an array whose keys are the keys for data being passed to
		  addData().
		- if reverse is set to true, then the values of the variable "keys"
		  are used as oppossed to the keys(!)
*/

// Handling function for generating reports.
function ReportsMain()
{
	global $db_prefix, $txt, $modSettings, $context, $scripturl;

	// Only admins, only EVER admins!
	isAllowedTo('admin_forum');

	// Let's get our things running...
	loadTemplate('Reports');
	loadLanguage('Reports');

	// We want an admin menu...
	adminIndex('generate_reports');

	$context['page_title'] = $txt['generate_reports'];

	// These are the types of reports which exist - and the functions to generate them.
	$context['report_types'] = array(
		'boards' => 'BoardReport',
		'board_perms' => 'BoardPermissionsReport',
		'member_groups' => 'MemberGroupsReport',
		'group_perms' => 'GroupPermissionsReport',
		'staff' => 'StaffReport',
	);

	$is_first = 0;
	foreach ($context['report_types'] as $k => $temp)
		$context['report_types'][$k] = array(
			'id' => $k,
			'title' => isset($txt['gr_type_' . $k]) ? $txt['gr_type_' . $k] : $type['id'],
			'description' => isset($txt['gr_type_desc_' . $k]) ? $txt['gr_type_desc_' . $k] : null,
			'function' => $temp,
			'is_first' => $is_first++ == 0,
		);

	// If they haven't choosen a report type which is valid, send them off to the report type chooser!
	if (empty($_REQUEST['rt']) || !isset($context['report_types'][$_REQUEST['rt']]))
	{
		$context['sub_template'] = 'report_type';
		return;
	}
	$context['report_type'] = $_REQUEST['rt'];

	// What are valid templates for showing reports?
	$reportTemplates = array(
		'main' => array(
			'layers' => null,
		),
		'print' => array(
			'layers' => array('print'),
		),
	);

	// Specific template? Use that instead of main!
	if (isset($_REQUEST['st']) && isset($reportTemplates[$_REQUEST['st']]))
	{
		$context['sub_template'] = $_REQUEST['st'];

		// Are we disabling the other layers - print friendly for example?
		if ($reportTemplates[$_REQUEST['st']]['layers'] !== null)
			$context['template_layers'] = $reportTemplates[$_REQUEST['st']]['layers'];
	}

	// Make the page title more descriptive.
	$context['page_title'] .= ' - ' . (isset($txt['gr_type_' . $context['report_type']]) ? $txt['gr_type_' . $context['report_type']] : $context['report_type']);
	// Now generate the data.
	$context['report_types'][$context['report_type']]['function']();

	// Finish the tables before exiting - this is to help the templates a little more.
	finishTables();
}

// Standard report about what settings the boards have.
function BoardReport()
{
	global $context, $db_prefix, $txt;

	// Get every moderator.
	$request = db_query("
		SELECT mods.ID_BOARD, mods.ID_MEMBER, mem.realName
		FROM ({$db_prefix}moderators AS mods, {$db_prefix}members AS mem)
		WHERE mem.ID_MEMBER = mods.ID_MEMBER", __FILE__, __LINE__);
	$moderators = array();
	while ($row = mysql_fetch_assoc($request))
		$moderators[$row['ID_BOARD']][] = $row['realName'];
	mysql_free_result($request);

	// Get all the possible membergroups!
	$request = db_query("
		SELECT ID_GROUP, groupName, onlineColor
		FROM {$db_prefix}membergroups", __FILE__, __LINE__);
	$groups = array(-1 => $txt[28], 0 => $txt['full_member']);
	while ($row = mysql_fetch_assoc($request))
		$groups[$row['ID_GROUP']] = empty($row['onlineColor']) ? $row['groupName'] : '<span style="color: ' . $row['onlineColor'] . '">' . $row['groupName'] . '</span>';
	mysql_free_result($request);

	// All the fields we'll show.
	$boardSettings = array(
		'category' => $txt['board_category'],
		'parent' => $txt['board_parent'],
		'num_topics' => $txt['board_num_topics'],
		'num_posts' => $txt['board_num_posts'],
		'count_posts' => $txt['board_count_posts'],
		'theme' => $txt['board_theme'],
		'override_theme' => $txt['board_override_theme'],
		'moderators' => $txt['board_moderators'],
		'groups' => $txt['board_groups'],
	);

	// Do it in columns, it's just easier.
	setKeys('cols');

	// Go through each board!
	$request = db_query("
		SELECT b.ID_BOARD, b.name, b.numPosts, b.numTopics, b.countPosts, b.memberGroups, b.override_theme, b.permission_mode,
			c.name AS catName, IFNULL(par.name, '$txt[none]') AS parentName, IFNULL(th.value, '$txt[none]') AS themeName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
			LEFT JOIN {$db_prefix}boards AS par ON (par.ID_BOARD = b.ID_PARENT)
			LEFT JOIN {$db_prefix}themes AS th ON (th.ID_THEME = b.ID_THEME AND variable = 'name')", __FILE__, __LINE__);
	$boards = array(0 => array('name' => $txt['global_boards'], 'local_perms' => 1));
	while ($row = mysql_fetch_assoc($request))
	{
		// Each board has it's own table.
		newTable($row['name'], '', 'left', 'auto', 'left', 200, 'left');

		// First off, add in the side key.
		addData($boardSettings);

		// Create the main data array.
		$boardData = array(
			'category' => $row['catName'],
			'parent' => $row['parentName'],
			'num_posts' => $row['numPosts'],
			'num_topics' => $row['numTopics'],
			'count_posts' => empty($row['countPosts']) ? $txt['yes'] : $txt['no'],
			'theme' => $row['themeName'],
			'override_theme' => $row['override_theme'] ? $txt['yes'] : $txt['no'],
			'moderators' => empty($moderators[$row['ID_BOARD']]) ? $txt['none'] : implode(', ', $moderators[$row['ID_BOARD']]),
		);

		// Work out the membergroups who can access it.
		$allowedGroups = explode(',', $row['memberGroups']);
		foreach ($allowedGroups as $key => $group)
		{
			if (isset($groups[$group]))
				$allowedGroups[$key] = $groups[$group];
			else
				unset($allowedGroups[$key]);
		}
		$boardData['groups'] = implode(', ', $allowedGroups);

		// Next add the main data.
		addData($boardData);
	}
	mysql_free_result($request);
}

// Generate a report on the current permissions by board and membergroup.
function BoardPermissionsReport()
{
	global $context, $db_prefix, $txt, $modSettings;

	if (isset($_REQUEST['boards']))
	{
		if (!is_array($_REQUEST['boards']))
			$_REQUEST['boards'] = explode(',', $_REQUEST['boards']);
		foreach ($_REQUEST['boards'] as $k => $dummy)
			$_REQUEST['boards'][$k] = (int) $dummy;

		$board_clause = 'ID_BOARD IN (' . implode(', ', $_REQUEST['boards']) . ')';
	}
	else
		$board_clause = '1';

	if (isset($_REQUEST['groups']))
	{
		if (!is_array($_REQUEST['groups']))
			$_REQUEST['groups'] = explode(',', $_REQUEST['groups']);
		foreach ($_REQUEST['groups'] as $k => $dummy)
			$_REQUEST['groups'][$k] = (int) $dummy;

		$group_clause = 'ID_GROUP IN (' . implode(', ', $_REQUEST['groups']) . ')';
	}
	else
		$group_clause = '1';

	// Fetch all the board names.
	$request = db_query("
		SELECT ID_BOARD, name, permission_mode
		FROM {$db_prefix}boards
		WHERE $board_clause", __FILE__, __LINE__);
	$boards = array(0 => array('name' => $txt['global_boards'], 'local_perms' => 1));
	while ($row = mysql_fetch_assoc($request))
		$boards[$row['ID_BOARD']] = array(
			'name' => $row['name'],
			'local_perms' => !empty($modSettings['permission_enable_by_board']) && $row['permission_mode'] == 1,
			'permission_mode' => empty($modSettings['permission_enable_by_board']) ? (empty($row['permission_mode']) ? 'normal' : ($row['permission_mode'] == 2 ? 'no_polls' : ($row['permission_mode'] == 3 ? 'reply_only' : 'read_only'))) : 'normal',
		);
	mysql_free_result($request);

	// Get all the possible membergroups, except for admin!
	$request = db_query("
		SELECT ID_GROUP, groupName
		FROM {$db_prefix}membergroups
		WHERE $group_clause
			AND ID_GROUP != 1" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND minPosts = -1" : '') . "
		ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	if (!isset($_REQUEST['groups']) || in_array(-1, $_REQUEST['groups']) || in_array(0, $_REQUEST['groups']))
		$memberGroups = array('col' => '', -1 => $txt['membergroups_guests'], 0 => $txt['membergroups_members']);
	else
		$memberGroups = array('col' => '');
	while ($row = mysql_fetch_assoc($request))
		$memberGroups[$row['ID_GROUP']] = $row['groupName'];
	mysql_free_result($request);

	// Make sure that every group is represented - plus in rows!
	setKeys('rows', $memberGroups);

	// Cache every permission setting, to make sure we don't miss any allows.
	$permissions = array();
	$board_permissions = array();
	$request = db_query("
		SELECT ID_BOARD, ID_GROUP, addDeny, permission
		FROM {$db_prefix}board_permissions
		WHERE $board_clause
			AND $group_clause" . (empty($modSettings['permission_enable_deny']) ? "
			AND addDeny = 1" : '') . "
		ORDER BY ID_BOARD, permission", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		$board_permissions[$row['ID_BOARD']][$row['ID_GROUP']][$row['permission']] = $row['addDeny'];

		// Make sure we get every permission.
		if (!isset($permissions[$row['permission']]))
		{
			// This will be reused on other boards.
			$permissions[$row['permission']] = array(
				'title' => isset($txt['board_perms_name_' . $row['permission']]) ? $txt['board_perms_name_' . $row['permission']] : $row['permission'],
			);
		}
	}
	mysql_free_result($request);

	// Now cycle through the board permissions array... lots to do ;)
	foreach ($board_permissions as $board => $groups)
	{
		// If it's not using local permissions don't show any!
		if ($board != 0 && !$boards[$board]['local_perms'])
			continue;

		// Create the table for this board first.
		newTable($boards[$board]['name'], 'x', 'all', 100, 'center', 200, 'left');

		// Add the header row - shows all the membergroups.
		addData($memberGroups);

		// Add the seperator.
		addSeperator($txt['board_perms_permission']);

		// Here cycle through all the detected permissions.
		foreach ($permissions as $ID_PERM => $perm_info)
		{
			// Is this identical to the global?
			$identicalGlobal = $board == 0 ? false : true;

			// Default data for this row.
			$curData = array('col' => $perm_info['title']);

			// Now cycle each membergroup in this set of permissions.
			foreach ($memberGroups as $ID_GROUP => $name)
			{
				// Don't overwrite the key column!
				if ($ID_GROUP === 'col')
					continue;

				$group_permissions = isset($groups[$ID_GROUP]) ? $groups[$ID_GROUP] : array();

				// Do we have any data for this group?
				if (isset($group_permissions[$ID_PERM]))
				{
					// Set the data for this group to be the local permission.
					$curData[$ID_GROUP] = $group_permissions[$ID_PERM];

					// If it's different than the global - then this permission needs to be shown in diff view.
					if (!isset($board_permissions[0][$ID_GROUP][$ID_PERM]) || $board_permissions[0][$ID_GROUP][$ID_PERM] != $group_permissions[$ID_PERM])
						$identicalGlobal = false;
				}
				// Otherwise means it's set to disallow..
				else
				{
					$curData[$ID_GROUP] = 'x';
					if (isset($board_permissions[0][$ID_GROUP][$ID_PERM]) && $board_permissions[0][$ID_GROUP][$ID_PERM] != 'x')
						$identicalGlobal = false;
				}

				// Now actually make the data for the group look right.
				if (empty($curData[$ID_GROUP]))
					$curData[$ID_GROUP] = '<span style="color: red;">' . $txt['board_perms_deny'] . '</span>';
				elseif ($curData[$ID_GROUP] == 1)
					$curData[$ID_GROUP] = '<span style="color: darkgreen;">' . $txt['board_perms_allow'] . '</span>';
				else
					$curData[$ID_GROUP] = 'x';

				// Embolden those permissions different from global (makes it a lot easier!)
				if (@$board_permissions[0][$ID_GROUP][$ID_PERM] != @$group_permissions[$ID_PERM])
					$curData[$ID_GROUP] = '<b>' . $curData[$ID_GROUP] . '</b>';
			}

			// Now add the data for this permission.
			//!!! Make an option for changing the view here!
			if (!$identicalGlobal || !isset($_REQUEST['show_differences']))
				addData($curData);
		}
	}

	// We'll do a little bit of seperate stuff for boards using "simple" local permissions.
	setKeys('rows');
	foreach ($boards as $id => $board)
	{
		if ($id != 0 && !empty($board['permission_mode']) && $board['permission_mode'] != 'normal')
		{
			newTable($board['name'], 'x', 'top');

			// Just add a description of the permission type.
			addData(array('<b>' . $txt['board_perms_group_' . $board['permission_mode']] . '</b>'));
		}
	}
}

// Show what the membergroups are made of.
function MemberGroupsReport()
{
	global $context, $db_prefix, $txt, $settings, $modSettings;

	// Fetch all the board names.
	$request = db_query("
		SELECT ID_BOARD, name, memberGroups, permission_mode
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$boards[$row['ID_BOARD']] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'local_perms' => !empty($modSettings['permission_enable_by_board']) && $row['permission_mode'] == 1,
			'permission_mode' => empty($modSettings['permission_enable_by_board']) ? (empty($row['permission_mode']) ? $txt['permission_mode_normal'] : ($row['permission_mode'] == 2 ? $txt['permission_mode_no_polls'] : ($row['permission_mode'] == 3 ? $txt['permission_mode_reply_only'] : $txt['permission_mode_read_only']))) : $txt['permission_mode_normal'],
			'groups' => array_merge(array(1,3), explode(',', $row['memberGroups'])),
		);
	mysql_free_result($request);

	// Standard settings.
	$mgSettings = array(
		'name' => '',
		'#sep#1' => $txt['member_group_settings'],
		'color' => $txt['member_group_color'],
		'minPosts' => $txt['member_group_minPosts'],
		'maxMessages' => $txt['member_group_maxMessages'],
		'stars' => $txt['member_group_stars'],
		'#sep#2' => $txt['member_group_access'],
	);

	// Add on the boards!
	foreach ($boards as $board)
		$mgSettings['board_' . $board['id']] = $board['name'];

	// Add all the membergroup settings, plus we'll be adding in columns!
	setKeys('cols', $mgSettings);

	// Only one table this time!
	newTable($txt['gr_type_member_groups'], '-', 'all', 100, 'center', 200, 'left');

	// Get the shaded column in.
	addData($mgSettings);

	// Now start cycling the membergroups!
	$request = db_query("
		SELECT mg.ID_GROUP, mg.groupName, mg.onlineColor, mg.minPosts, mg.maxMessages, mg.stars" . (empty($modSettings['permission_enable_by_board']) ? ", IF(bp.permission IS NOT NULL OR mg.ID_GROUP = 1, 1, 0) AS can_moderate" : '') . "
		FROM {$db_prefix}membergroups AS mg" . (empty($modSettings['permission_enable_by_board']) ? "
			LEFT JOIN {$db_prefix}board_permissions AS bp ON (bp.ID_GROUP = mg.ID_GROUP AND bp.ID_BOARD = 0 AND bp.permission = 'moderate_board')" : '') . "
		ORDER BY mg.minPosts, IF(mg.ID_GROUP < 4, mg.ID_GROUP, 4), mg.groupName", __FILE__, __LINE__);

	// Cache them so we get regular members too.
	$rows = array(
		array(
			'ID_GROUP' => -1,
			'groupName' => $txt['membergroups_guests'],
			'onlineColor' => '-',
			'minPosts' => -1,
			'maxMessages' => null,
			'stars' => ''
		),
		array(
			'ID_GROUP' => 0,
			'groupName' => $txt['membergroups_members'],
			'onlineColor' => '-',
			'minPosts' => -1,
			'maxMessages' => null,
			'stars' => ''
		),
	);
	while ($row = mysql_fetch_assoc($request))
		$rows[] = $row;
	mysql_free_result($request);

	foreach ($rows as $row)
	{
		$row['stars'] = explode('#', $row['stars']);

		$group = array(
			'name' => $row['groupName'],
			'color' => empty($row['onlineColor']) ? '-' : '<span style="color: ' . $row['onlineColor'] . ';">' . $row['onlineColor'] . '</span>',
			'minPosts' => $row['minPosts'] == -1 ? 'N/A' : $row['minPosts'],
			'maxMessages' => $row['maxMessages'],
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);

		// Board permissions.
		foreach ($boards as $board)
			$group['board_' . $board['id']] = in_array($row['ID_GROUP'], $board['groups']) ? '<span style="color: darkgreen;">' . (empty($modSettings['permission_enable_by_board']) ? (empty($row['can_moderate']) ? $board['permission_mode'] : $txt['permission_mode_normal']) : $txt['board_perms_allow']) . '</span>' : 'x';

		addData($group);
	}
}

// Show the large variety of group permissions assigned to each membergroup.
function GroupPermissionsReport()
{
	global $context, $db_prefix, $txt, $modSettings;

	if (isset($_REQUEST['groups']))
	{
		if (!is_array($_REQUEST['groups']))
			$_REQUEST['groups'] = explode(',', $_REQUEST['groups']);
		foreach ($_REQUEST['groups'] as $k => $dummy)
			$_REQUEST['groups'][$k] = (int) $dummy;
		$_REQUEST['groups'] = array_diff($_REQUEST['groups'], array(3));

		$clause = 'ID_GROUP IN (' . implode(', ', $_REQUEST['groups']) . ')';
	}
	else
		$clause = 'ID_GROUP != 3';

	// Get all the possible membergroups, except for admin!
	$request = db_query("
		SELECT ID_GROUP, groupName
		FROM {$db_prefix}membergroups
		WHERE $clause
			AND ID_GROUP != 1" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND minPosts = -1" : '') . "
		ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	if (!isset($_REQUEST['groups']) || in_array(-1, $_REQUEST['groups']) || in_array(0, $_REQUEST['groups']))
		$groups = array('col' => '', -1 => $txt['membergroups_guests'], 0 => $txt['membergroups_members']);
	else
		$groups = array('col' => '');
	while ($row = mysql_fetch_assoc($request))
		$groups[$row['ID_GROUP']] = $row['groupName'];
	mysql_free_result($request);

	// Make sure that every group is represented!
	setKeys('rows', $groups);

	// Create the table first.
	newTable($txt['gr_type_group_perms'], '-', 'all', 100, 'center', 200, 'left');

	// Show all the groups
	addData($groups);

	// Add a seperator
	addSeperator($txt['board_perms_permission']);

	// Now the big permission fetch!
	$request = db_query("
		SELECT ID_GROUP, addDeny, permission
		FROM {$db_prefix}permissions
		WHERE $clause" . (empty($modSettings['permission_enable_deny']) ? "
			AND addDeny = 1" : '') . "
		ORDER BY permission", __FILE__, __LINE__);
	$lastPermission = null;
	while ($row = mysql_fetch_assoc($request))
	{
		// If this is a new permission flush the last row.
		if ($row['permission'] != $lastPermission)
		{
			// Send the data!
			if ($lastPermission !== null)
				addData($curData);

			// Add the permission name in the left column.
			$curData = array('col' => isset($txt['group_perms_name_' . $row['permission']]) ? $txt['group_perms_name_' . $row['permission']] : $row['permission']);

			$lastPermission = $row['permission'];
		}

		// Good stuff - add the permission to the list!
		if ($row['addDeny'])
			$curData[$row['ID_GROUP']] = '<span style="color: darkgreen;">' . $txt['board_perms_allow'] . '</span>';
		else
			$curData[$row['ID_GROUP']] = '<span style="color: red;">' . $txt['board_perms_deny'] . '</span>';
	}
	mysql_free_result($request);

	// Flush the last data!
	addData($curData);
}

// Report for showing all the forum staff members - quite a feat!
function StaffReport()
{
	global $sourcedir, $context, $db_prefix, $txt;

	require_once($sourcedir . '/Subs-Members.php');

	// Fetch all the board names.
	$request = db_query("
		SELECT ID_BOARD, name
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	$boards = array();
	while ($row = mysql_fetch_assoc($request))
		$boards[$row['ID_BOARD']] = $row['name'];
	mysql_free_result($request);

	// Get every moderator.
	$request = db_query("
		SELECT mods.ID_BOARD, mods.ID_MEMBER
		FROM {$db_prefix}moderators AS mods", __FILE__, __LINE__);
	$moderators = array();
	$local_mods = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$moderators[$row['ID_MEMBER']][] = $row['ID_BOARD'];
		$local_mods[$row['ID_MEMBER']] = $row['ID_MEMBER'];
	}
	mysql_free_result($request);

	// Get a list of global moderators (i.e. members with moderation powers).
	$global_mods = array_intersect(membersAllowedTo('moderate_board', 0), membersAllowedTo('post_new', 0), membersAllowedTo('remove_any', 0), membersAllowedTo('modify_any', 0));

	// How about anyone else who is special?
	$allStaff = array_merge(membersAllowedTo('admin_forum'), membersAllowedTo('manage_membergroups'), membersAllowedTo('manage_permissions'), $local_mods, $global_mods);

	// Make sure everyone is there once - no admin less important than any other!
	$allStaff = array_unique($allStaff);

	// This is a bit of a cop out - but we're protecting their forum, really!
	if (count($allStaff) > 300)
		fatal_lang_error('report_error_too_many_staff');

	// Get all the possible membergroups!
	$request = db_query("
		SELECT ID_GROUP, groupName, onlineColor
		FROM {$db_prefix}membergroups", __FILE__, __LINE__);
	$groups = array(0 => $txt['full_member']);
	while ($row = mysql_fetch_assoc($request))
		$groups[$row['ID_GROUP']] = empty($row['onlineColor']) ? $row['groupName'] : '<span style="color: ' . $row['onlineColor'] . '">' . $row['groupName'] . '</span>';
	mysql_free_result($request);

	// All the fields we'll show.
	$staffSettings = array(
		'position' => $txt['report_staff_position'],
		'moderates' => $txt['report_staff_moderates'],
		'posts' => $txt['report_staff_posts'],
		'last_login' => $txt['report_staff_last_login'],
	);

	// Do it in columns, it's just easier.
	setKeys('cols');

	// Get each member!
	$request = db_query("
		SELECT ID_MEMBER, realName, ID_GROUP, posts, lastLogin
		FROM {$db_prefix}members
		WHERE ID_MEMBER IN (" . implode(',', $allStaff) . ")
		ORDER BY realName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		// Each member gets their own table!.
		newTable($row['realName'], '', 'left', 'auto', 'left', 200, 'center');

		// First off, add in the side key.
		addData($staffSettings);

		// Create the main data array.
		$staffData = array(
			'position' => isset($groups[$row['ID_GROUP']]) ? $groups[$row['ID_GROUP']] : $groups[0],
			'posts' => $row['posts'],
			'last_login' => timeformat($row['lastLogin']),
			'moderates' => array(),
		);

		// What do they moderate?
		if (in_array($row['ID_MEMBER'], $global_mods))
			$staffData['moderates'] = '<i>' . $txt['report_staff_all_boards'] . '</i>';
		elseif (isset($moderators[$row['ID_MEMBER']]))
		{
			// Get the names
			foreach ($moderators[$row['ID_MEMBER']] as $board)
				if (isset($boards[$board]))
					$staffData['moderates'][] = $boards[$board];

			$staffData['moderates'] = implode(', ', $staffData['moderates']);
		}
		else
			$staffData['moderates'] = '<i>' . $txt['report_staff_no_boards'] . '</i>';

		// Next add the main data.
		addData($staffData);
	}
	mysql_free_result($request);
}

// This function creates a new table of data, most functions will only use it once.
function newTable($title = '', $defaultValue = '', $shading = 'all', $width_normal = 'auto', $align_normal = 'center', $width_shaded = 'auto', $align_shaded = 'auto')
{
	global $context;

	// Set the table count if needed.
	if (empty($context['table_count']))
		$context['table_count'] = 0;

	// Create the table!
	$context['tables'][$context['table_count']] = array(
		'title' => $title,
		'default_value' => $defaultValue,
		'shading' => array(
			'left' => $shading == 'all' || $shading == 'left',
			'top' => $shading == 'all' || $shading == 'top',
		),
		'width' => array(
			'normal' => $width_normal,
			'shaded' => $width_shaded,
		),
		'align' => array(
			'normal' => $align_normal,
			'shaded' => $align_shaded,
		),
		'data' => array(),
	);

	$context['current_table'] = $context['table_count'];

	// Increment the count...
	$context['table_count']++;
}

// Add an extra slice of data to the table
function addData($inc_data, $custom_table = null)
{
	global $context;

	// No tables? Create one even though we are probably already in a bad state!
	if (empty($context['table_count']))
		newTable();

	// Specific table?
	if ($custom_table !== null && !isset($context['tables'][$custom_table]))
		return false;
	elseif ($custom_table !== null)
		$table = $custom_table;
	else
		$table = $context['current_table'];

	// If we have keys, sanitise the data...
	if (!empty($context['keys']))
	{
		// Basically, check every key exists!
		foreach ($context['keys'] as $key => $dummy)
			$data[$key] = array(
				'value' => empty($inc_data[$key]) ? $context['tables'][$table]['default_value'] : $inc_data[$key],
				// Special "hack" the adding seperators when doing data by column.
				'seperator' => substr($key, 0, 5) == '#sep#' ? true : false,
			);
	}
	else
	{
		$data = $inc_data;
		foreach ($data as $key => $value)
			$data[$key] = array(
				'value' => $value,
				'seperator' => substr($key, 0, 5) == '#sep#' ? true : false,
			);
	}

	// Is it by row?
	if (empty($context['key_method']) || $context['key_method'] == 'rows')
	{
		// Add the data!
		$context['tables'][$table]['data'][] = $data;
	}
	// Otherwise, tricky!
	else
	{
		foreach ($data as $key => $item)
			$context['tables'][$table]['data'][$key][] = $item;
	}
}

// Add a seperator row, only really used when adding data by rows.
function addSeperator($title = '', $custom_table = null)
{
	global $context;

	// No tables - return?
	if (empty($context['table_count']))
		return;

	// Specific table?
	if ($custom_table !== null && !isset($context['tables'][$table]))
		return false;
	elseif ($custom_table !== null)
		$table = $custom_table;
	else
		$table = $context['current_table'];

	// Plumb in the seperator
	$context['tables'][$table]['data'][] = array(0 => array(
		'seperator' => true,
		'value' => $title
	));
}

// This does the necessary count of table data before displaying them.
function finishTables()
{
	global $context;

	if (empty($context['tables']))
		return;

	// Loop through each table counting up some basic values, to help with the templating.
	foreach ($context['tables'] as $id => $table)
	{
		$context['tables'][$id]['id'] = $id;
		$context['tables'][$id]['row_count'] = count($table['data']);
		$curElement = current($table['data']);
		$context['tables'][$id]['column_count'] = count($curElement);

		// Work out the rough width - for templates like the print template. Without this we might get funny tables.
		if ($table['shading']['left'] && $table['width']['shaded'] != 'auto' && $table['width']['normal'] != 'auto')
			$context['tables'][$id]['max_width'] = $table['width']['shaded'] + ($context['tables'][$id]['column_count'] - 1) * $table['width']['normal'];
		elseif ($table['width']['normal'] != 'auto')
			$context['tables'][$id]['max_width'] = $context['tables'][$id]['column_count'] * $table['width']['normal'];
		else
			$context['tables'][$id]['max_width'] = 'auto';
	}
}

// Set the keys in use by the tables - these ensure entries MUST exist if the data isn't sent.
function setKeys($method = 'rows', $keys = array(), $reverse = false)
{
	global $context;

	// Do we want to use the keys of the keys as the keys? :P
	if ($reverse)
		$context['keys'] = array_flip($keys);
	else
		$context['keys'] = $keys;

	// Rows or columns?
	$context['key_method'] = $method == 'rows' ? 'rows' : 'cols';
}

?>