<?php
/**********************************************************************************
* ManageMembergroups.php                                                          *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.4                                           *
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

/* This file is concerned with anything in the Manage Membergroups screen.

	void ModifyMembergroups()
		- entrance point of the 'Manage Membergroups' center.
		- called by ?action=membergroups.
		- loads the ManageMembergroups template.
		- loads the MangeMembers language file.
		- requires the manage_membergroups or the admin_forum permission.
		- calls a function based on the given subaction.
		- defaults to sub action 'index' or without manage_membergroup
		  permissions to 'settings'.

	void MembergroupIndex()
		- shows an overview of the current membergroups.
		- called by ?action=membergroups.
		- requires the manage_membergroups permission.
		- uses the main ManageMembergroups template.
		- splits the membergroups in regular ones and post count based groups.
		- also counts the number of members part of each membergroup.

	void AddMembergroup()
		- allows to add a membergroup and set some initial properties.
		- called by ?action=membergroups;sa=add.
		- requires the manage_membergroups permission.
		- uses the new_group sub template of ManageMembergroups.
		- allows to use a predefined permission profile or copy one from
		  another group.
		- redirects to action=membergroups;sa=edit;group=x.

	void DeleteMembergroup()
		- deletes a membergroup by URL.
		- called by ?action=membergroups;sa=delete;group=x;sesc=y.
		- requires the manage_membergroups permission.
		- redirects to ?action=membergroups.

	void EditMembergroup()
		- screen to edit a specific membergroup.
		- called by ?action=membergroups;sa=edit;group=x.
		- requires the manage_membergroups permission.
		- uses the edit_group sub template of ManageMembergroups.
		- also handles the delete button of the edit form.
		- redirects to ?action=membergroups.

	void MembergroupMembers()
		- show a list of members that are part of a given membergroup.
		- called by ?action=membergroups;sa=members;group=x
		- requires the manage_membergroups permission.
		- uses the group_members sub template of ManageMembergroups.
		- allows to add and remove members from the selected membergroup.
		- allows sorting on several columns.
		- redirects to itself.

	void ModifyMembergroupSettings()
		- set some general membergroup settings and permissions.
		- called by ?action=membergroups;sa=settings
		- requires the admin_forum permission (and manage_permissions for
		  changing permissions)
		- uses membergroup_settings sub template of ManageMembergroups.
		- redirects to itself.
*/

// The entrance point for all 'Manage Membergroup' actions.
function ModifyMembergroups()
{
	global $context, $txt, $scripturl;

	$subActions = array(
		'add' => array('AddMembergroup', 'manage_membergroups'),
		'delete' => array('DeleteMembergroup', 'manage_membergroups'),
		'edit' => array('EditMembergroup', 'manage_membergroups'),
		'index' => array('MembergroupIndex', 'manage_membergroups'),
		'members' => array('MembergroupMembers', 'manage_membergroups'),
		'settings' => array('ModifyMembergroupSettings', 'admin_forum'),
	);

	// Default to sub action 'index' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('manage_membergroups') ? 'index' : 'settings');

	// Do the permission check, you might not be allowed her.
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// We're in the admin screen, so show a nice side bar.
	adminIndex('edit_groups');

	// Language and template stuff, the usual.
	loadLanguage('ManageMembers');
	loadTemplate('ManageMembergroups');

	// Setup the admin tabs.
	$context['admin_tabs'] = array(
		'title' => $txt['membergroups_title'],
		'help' => 'membergroups',
		'description' => $txt['membergroups_description'],
		'tabs' => array(),
	);
	if (allowedTo('manage_membergroups'))
	{
		$context['admin_tabs']['tabs']['index'] = array(
			'title' => $txt['membergroups_edit_groups'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=membergroups',
			'is_selected' => $_REQUEST['sa'] != 'add' && $_REQUEST['sa'] != 'settings',
		);
		$context['admin_tabs']['tabs']['add_cat'] = array(
			'title' => $txt['membergroups_new_group'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=membergroups;sa=add',
			'is_selected' => $_REQUEST['sa'] == 'add',
			'is_last' => !allowedTo('admin_forum'),
		);
	}
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs']['settings'] = array(
			'title' => $txt['settings'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=membergroups;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
			'is_last' => true,
		);

	// Call the right function.
	$subActions[$_REQUEST['sa']][0]();
}

// An overview of the current membergroups.
function MembergroupIndex()
{
	global $db_prefix, $txt, $scripturl, $context, $settings;

	$context['page_title'] = $txt['membergroups_title'];

	$context['groups'] = array(
		'regular' => array(),
		'post' => array()
	);

	$query = db_query("
		SELECT ID_GROUP, groupName, minPosts, onlineColor, stars
		FROM {$db_prefix}membergroups
		ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($query))
	{
		$row['stars'] = explode('#', $row['stars']);
		$context['groups'][$row['minPosts'] == -1 ? 'regular' : 'post'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'num_members' => $row['ID_GROUP'] != 3 ? 0 : $txt['membergroups_guests_na'],
			'allow_delete' => $row['ID_GROUP'] > 4,
			'can_search' => $row['ID_GROUP'] != 3,
			'href' => $scripturl . '?action=membergroups;sa=members;group=' . $row['ID_GROUP'],
			'is_post_group' => $row['minPosts'] != -1,
			'min_posts' => $row['minPosts'] == -1 ? '-' : $row['minPosts'],
			'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);
	}
	mysql_free_result($query);

	if (!empty($context['groups']['post']))
	{
		$query = db_query("
			SELECT ID_POST_GROUP AS ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_POST_GROUP IN (" . implode(', ', array_keys($context['groups']['post'])) . ")
			GROUP BY ID_POST_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['post'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
	}

	if (!empty($context['groups']['regular']))
	{
		$query = db_query("
			SELECT ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_GROUP IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
			GROUP BY ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['regular'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);

		$query = db_query("
			SELECT mg.ID_GROUP, COUNT(*) AS num_members
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.ID_GROUP IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
				AND mem.additionalGroups != ''
				AND mem.ID_GROUP != mg.ID_GROUP
				AND FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups)
			GROUP BY mg.ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['regular'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
	}

	foreach ($context['groups'] as $temp => $dummy)
		foreach ($dummy as $id => $data)
		{
			if ($data['href'] != '')
				$context['groups'][$temp][$id]['link'] = '<a href="' . $data['href'] . '">' . $data['name'] . '</a>';
			else
				$context['groups'][$temp][$id]['link'] = '';
		}
}

// Add a membergroup.
function AddMembergroup()
{
	global $db_prefix, $context, $txt, $sourcedir, $modSettings;

	// A form was submitted, we can start adding.
	if (!empty($_POST['group_name']))
	{
		checkSession();

		$postCountBasedGroup = isset($_POST['min_posts']) && (!isset($_POST['postgroup_based']) || !empty($_POST['postgroup_based']));

		// !!! Check for members with same name too?

		$request = db_query("
			SELECT MAX(ID_GROUP)
			FROM {$db_prefix}membergroups", __FILE__, __LINE__);
		list ($ID_GROUP) = mysql_fetch_row($request);
		mysql_free_result($request);
		$ID_GROUP++;

		db_query("
			INSERT INTO {$db_prefix}membergroups
				(ID_GROUP, groupName, minPosts, stars, onlineColor)
			VALUES ($ID_GROUP, SUBSTRING('$_POST[group_name]', 1, 80), " . ($postCountBasedGroup ? (int) $_POST['min_posts'] : '-1') . ", '1#star.gif', '')", __FILE__, __LINE__);

		// Update the post groups now, if this is a post group!
		if (isset($_POST['min_posts']))
			updateStats('postgroups');

		// You cannot set permissions for post groups if they are disabled.
		if ($postCountBasedGroup && empty($modSettings['permission_enable_postgroups']))
			$_POST['perm_type'] = '';

		if ($_POST['perm_type'] == 'predefined')
		{
			// Set default permission level.
			require_once($sourcedir . '/ManagePermissions.php');
			setPermissionLevel($_POST['level'], $ID_GROUP, 'null');
		}
		// Copy the permissions!
		elseif ($_POST['perm_type'] == 'copy')
		{
			$_POST['copyperm'] = (int) $_POST['copyperm'];

			// Don't allow copying of a real priviledged person!
			require_once($sourcedir . '/ManagePermissions.php');
			loadIllegalPermissions();

			$request = db_query("
				SELECT permission, addDeny
				FROM {$db_prefix}permissions
				WHERE ID_GROUP = $_POST[copyperm]", __FILE__, __LINE__);
			$setString = '';
			while ($row = mysql_fetch_assoc($request))
			{
				if (empty($context['illegal_permissions']) || !in_array($row['permission'], $context['illegal_permissions']))
					$setString .= "
						($ID_GROUP, '$row[permission]', $row[addDeny]),";
			}
			mysql_free_result($request);

			if (!empty($setString))
				db_query("
					INSERT INTO {$db_prefix}permissions
						(ID_GROUP, permission, addDeny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			$request = db_query("
				SELECT ID_BOARD, permission, addDeny
				FROM {$db_prefix}board_permissions
				WHERE ID_GROUP = $_POST[copyperm]" . (empty($modSettings['permission_enable_by_board']) ? "
					AND ID_BOARD = 0" : ''), __FILE__, __LINE__);
			$setString = '';
			while ($row = mysql_fetch_assoc($request))
				$setString .= "
					($ID_GROUP, $row[ID_BOARD], '$row[permission]', $row[addDeny]),";
			mysql_free_result($request);

			if (!empty($setString))
				db_query("
					INSERT INTO {$db_prefix}board_permissions
						(ID_GROUP, ID_BOARD, permission, addDeny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			// Also get some membergroup information if we're not copying from guests...
			if ($_POST['copyperm'] > 0)
			{
				$request = db_query("
					SELECT onlineColor, maxMessages, stars
					FROM {$db_prefix}membergroups
					WHERE ID_GROUP = $_POST[copyperm]
					LIMIT 1", __FILE__, __LINE__);
				$group_info = mysql_fetch_assoc($request);
				mysql_free_result($request);

				// ...and update the new membergroup with it.
				db_query("
					UPDATE {$db_prefix}membergroups
					SET
						onlineColor = '$group_info[onlineColor]',
						maxMessages = $group_info[maxMessages],
						stars = '$group_info[stars]'
					WHERE ID_GROUP = $ID_GROUP
					LIMIT 1", __FILE__, __LINE__);
			}
		}

		// Make sure all boards selected are stored in a proper array.
		$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
		foreach ($_POST['boardaccess'] as $key => $value)
			$_POST['boardaccess'][$key] = (int) $value;

		// Only do this if they have special access requirements.
		if (!empty($_POST['boardaccess']))
			db_query("
				UPDATE {$db_prefix}boards
				SET memberGroups = IF(memberGroups = '', '$ID_GROUP', CONCAT(memberGroups, ',$ID_GROUP'))
				WHERE ID_BOARD IN (" . implode(', ', $_POST['boardaccess']) . ")
				LIMIT " . count($_POST['boardaccess']), __FILE__, __LINE__);

		// Go change some more settings.
		redirectexit('action=membergroups;sa=edit;group=' . $ID_GROUP);
	}

	// Just show the 'add membergroup' screen.
	$context['page_title'] = $txt['membergroups_new_group'];
	$context['sub_template'] = 'new_group';
	$context['post_group'] = !empty($_REQUEST['postgroup']);
	$context['undefined_group'] = empty($_REQUEST['postgroup']) && empty($_REQUEST['generalgroup']);

	$result = db_query("
		SELECT ID_GROUP, groupName
		FROM {$db_prefix}membergroups
		WHERE (ID_GROUP > 3 OR ID_GROUP = 2)" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND minPosts = -1" : '') . "
		ORDER BY minPosts, ID_GROUP != 2, groupName", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = mysql_fetch_assoc($result))
		$context['groups'][] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName']
		);
	mysql_free_result($result);

	$result = db_query("
		SELECT ID_BOARD, name, childLevel
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($result))
		$context['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'child_level' => $row['childLevel'],
			'selected' => false
		);
	mysql_free_result($result);
}

// Deleting a membergroup by URL (not implemented).
function DeleteMembergroup()
{
	global $sourcedir;

	checkSession('get');

	require_once($sourcedir . '/Subs-Members.php');
	deleteMembergroups((int) $_REQUEST['group']);

	// Go back to the membergroup index.
	redirectexit('action=membergroups;');
}

// Editing a membergroup.
function EditMembergroup()
{
	global $db_prefix, $context, $txt, $sourcedir;

	// Make sure this group is editable.
	if (empty($_REQUEST['group']) || (int) $_REQUEST['group'] < 1)
		fatal_lang_error('membergroup_does_not_exist', false);
	$_REQUEST['group'] = (int) $_REQUEST['group'];

	// The delete this membergroup button was pressed.
	if (isset($_POST['delete']))
	{
		checkSession();

		require_once($sourcedir . '/Subs-Members.php');
		deleteMembergroups($_REQUEST['group']);

		redirectexit('action=membergroups;');
	}
	// A form was submitted with the new membergroup settings.
	elseif (isset($_POST['submit']))
	{
		// Validate the session.
		checkSession();

		// Set variables to their proper value.
		$_POST['max_messages'] = isset($_POST['max_messages']) ? (int) $_POST['max_messages'] : 0;
		$_POST['min_posts'] = isset($_POST['min_posts']) && $_POST['post_group'] == '1' && $_REQUEST['group'] > 3 ? abs($_POST['min_posts']) : ($_REQUEST['group'] == 4 ? 0 : -1);
		$_POST['stars'] = (empty($_POST['star_count']) || $_POST['star_count'] < 0) ? '' : min((int) $_POST['star_count'], 99) . '#' . $_POST['star_image'];

		// !!! Don't set onlineColor for the Moderators group?

		// Do the update of the membergroup settings.
		db_query("
			UPDATE {$db_prefix}membergroups
			SET groupName = '$_POST[group_name]', onlineColor = '$_POST[online_color]',
				maxMessages = $_POST[max_messages], minPosts = $_POST[min_posts], stars = '$_POST[stars]'
			WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
			LIMIT 1", __FILE__, __LINE__);

		// Time to update the boards this membergroup has access to.
		if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
		{
			$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
			foreach ($_POST['boardaccess'] as $key => $value)
				$_POST['boardaccess'][$key] = (int) $value;

			// Find all board this group is in, but shouldn't be in.
			$request = db_query("
				SELECT ID_BOARD, memberGroups
				FROM {$db_prefix}boards
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups)" . (empty($_POST['boardaccess']) ? '' : "
					AND ID_BOARD NOT IN (" . implode(', ', $_POST['boardaccess']) . ')'), __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				db_query("
					UPDATE {$db_prefix}boards
					SET memberGroups = '" . implode(',', array_diff(explode(',', $row['memberGroups']), array($_REQUEST['group']))) . "'
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			mysql_free_result($request);

			// Add the membergroup to all boards that hadn't been set yet.
			if (!empty($_POST['boardaccess']))
				db_query("
					UPDATE {$db_prefix}boards
					SET memberGroups = IF(memberGroups = '', '" . (int) $_REQUEST['group'] . "', CONCAT(memberGroups, '," . (int) $_REQUEST['group'] . "'))
					WHERE ID_BOARD IN (" . implode(', ', $_POST['boardaccess']) . ")
						AND NOT FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups)", __FILE__, __LINE__);
		}

		// Remove everyone from this group!
		if ($_POST['min_posts'] != -1)
		{
			db_query("
				UPDATE {$db_prefix}members
				SET ID_GROUP = 0
				WHERE ID_GROUP = " . (int) $_REQUEST['group'], __FILE__, __LINE__);

			$request = db_query("
				SELECT ID_MEMBER, additionalGroups
				FROM {$db_prefix}members
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additionalGroups)", __FILE__, __LINE__);
			$updates = array();
			while ($row = mysql_fetch_assoc($request))
				$updates[$row['additionalGroups']][] = $row['ID_MEMBER'];
			mysql_free_result($request);

			foreach ($updates as $additionalGroups => $memberArray)
				updateMemberData($memberArray, array('additionalGroups' => '\'' . implode(',', array_diff(explode(',', $additionalGroups), array((int) $_REQUEST['group']))) . '\''));
		}

		// There might have been some post group changes.
		updateStats('postgroups');

		redirectexit('action=membergroups');
	}

	// Fetch the current group information.
	$request = db_query("
		SELECT groupName, minPosts, onlineColor, maxMessages, stars
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	$row['stars'] = explode('#', $row['stars']);

	$context['group'] = array(
		'id' => $_REQUEST['group'],
		'name' => $row['groupName'],
		'editable_name' => htmlspecialchars($row['groupName']),
		'color' => $row['onlineColor'],
		'min_posts' => $row['minPosts'],
		'max_messages' => $row['maxMessages'],
		'star_count' => (int) $row['stars'][0],
		'star_image' => isset($row['stars'][1]) ? $row['stars'][1] : '',
		'is_post_group' => $row['minPosts'] != -1,
		'allow_post_group' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4,
		'allow_delete' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4
	);

	// Get a list of boards this membergroup is allowed to see.
	$context['boards'] = array();
	if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
	{
		$result = db_query("
			SELECT ID_BOARD, name, childLevel, FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups) AS can_access
			FROM {$db_prefix}boards", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			$context['boards'][] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'child_level' => $row['childLevel'],
				'selected' => !empty($row['can_access']),
			);
		mysql_free_result($result);
	}
	$context['sub_template'] = 'edit_group';
	$context['page_title'] = $txt['membergroups_edit_group'];
}

// Display members of a group, and allow adding of members to a group. Silly function name though ;)
function MembergroupMembers()
{
	global $txt, $scripturl, $db_prefix, $context, $modSettings, $sourcedir, $func;

	$_REQUEST['group'] = (int) $_REQUEST['group'];

	// No browsing of guests, membergroup 0 or moderators.
	if (in_array($_REQUEST['group'], array(-1, 0, 3)))
		fatal_lang_error('membergroup_does_not_exist', false);

	// Load up the group details - and ensure this ISN'T a post group ;)
	$request = db_query("
		SELECT ID_GROUP AS id, groupName AS name, minPosts = -1 AS assignable, minPosts != -1 AS is_post_group
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
		LIMIT 1", __FILE__, __LINE__);
	// Not really possible...
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$context['group'] = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Non-admins cannot assign admins.
	if ($context['group']['id'] == 1 && !allowedTo('admin_forum'))
		$context['group']['assignable'] = 0;

	// Removing member from group?
	if (isset($_POST['remove']) && !empty($_REQUEST['rem']) && is_array($_REQUEST['rem']) && $context['group']['assignable'])
	{
		checkSession();

		require_once($sourcedir . '/Subs-Members.php');
		removeMembersFromGroups($_REQUEST['rem'], $_REQUEST['group']);
	}
	// Must be adding new members to the group...
	elseif (isset($_REQUEST['add']) && !empty($_REQUEST['toAdd']) && $context['group']['assignable'])
	{
		checkSession();

		// Get all the members to be added... taking into account names can be quoted ;)
		$_REQUEST['toAdd'] = strtr(addslashes($func['htmlspecialchars'](stripslashes($_REQUEST['toAdd']), ENT_QUOTES)), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_REQUEST['toAdd'], $matches);
		$memberNames = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['toAdd']))));

		foreach ($memberNames as $index => $memberName)
		{
			$memberNames[$index] = trim($func['strtolower']($memberNames[$index]));

			if (strlen($memberNames[$index]) == 0)
				unset($memberNames[$index]);
		}

		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE LOWER(memberName) IN ('" . implode("', '", $memberNames) . "') OR LOWER(realName) IN ('" . implode("', '", $memberNames) . "')
			LIMIT " . count($memberNames), __FILE__, __LINE__);
		$members = array();
		while ($row = mysql_fetch_assoc($request))
			$members[] = $row['ID_MEMBER'];
		mysql_free_result($request);

		// !!! Add $_POST['additional'] to templates!

		// Do the updates...
		require_once($sourcedir . '/Subs-Members.php');
		addMembersToGroup($members, $_REQUEST['group'], isset($_POST['additional']) ? 'only_additional' : 'auto');
	}

	// Sort out the sorting!
	$sort_methods = array(
		'name' => 'realName',
		'email' => 'emailAddress',
		'active' => 'lastLogin',
		'registered' => 'dateRegistered',
		'posts' => 'posts',
	);

	// They didn't pick one, default to by name..
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'name';
		$querySort = 'realName';
	}
	// Otherwise default to ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$querySort = $sort_methods[$_REQUEST['sort']];
	}

	$context['sort_direction'] = isset($_REQUEST['desc']) ? 'down' : 'up';

	// Count members of the group.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}members
		WHERE " . (empty($context['group']['is_post_group']) ? "ID_GROUP = " . (int) $_REQUEST['group'] . " OR FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additionalGroups)" : "ID_POST_GROUP = " . (int) $_REQUEST['group']), __FILE__, __LINE__);
	list ($context['total_members']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=membergroups;sa=members;group=' . $_REQUEST['group'] . ';sort=' . $context['sort_by'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['total_members'], $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];

	// Load up all members of this group.
	$request = db_query("
		SELECT ID_MEMBER, memberName, realName, emailAddress, memberIP, dateRegistered, lastLogin, posts, is_activated
		FROM {$db_prefix}members
		WHERE " . (empty($context['group']['is_post_group']) ? "ID_GROUP = " . (int) $_REQUEST['group'] . " OR FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additionalGroups)" : "ID_POST_GROUP = " . (int) $_REQUEST['group']) . "
		ORDER BY $querySort " . ($context['sort_direction'] == 'down' ? 'DESC' : 'ASC') . "
		LIMIT $context[start], $modSettings[defaultMaxMembers]", __FILE__, __LINE__);
	$context['members'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$last_online = empty($row['lastLogin']) ? $txt['never'] : timeformat($row['lastLogin']);

		// Italicize the online note if they aren't activated.
		if ($row['is_activated'] % 10 != 1)
			$last_online = '<i title="' . $txt['not_activated'] . '">' . $last_online . '</i>';

		$context['members'][] = array(
			'id' => $row['ID_MEMBER'],
			'name' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'email' => '<a href="mailto:' . $row['emailAddress'] . '">' . $row['emailAddress'] . '</a>',
			'ip' => '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['memberIP'] . '">' . $row['memberIP'] . '</a>',
			'registered' => timeformat($row['dateRegistered']),
			'last_online' => $last_online,
			'posts' => $row['posts'],
			'is_activated' => $row['is_activated'] % 10 == 1,
		);
	}
	mysql_free_result($request);

	// Select the template.
	$context['sub_template'] = 'group_members';
	$context['page_title'] = $txt['membergroups_members_title'] . ': ' . $context['group']['name'];
}

// Set general membergroup settings.
function ModifyMembergroupSettings()
{
	global $context, $db_prefix, $sourcedir, $modSettings, $txt;

	$context['sub_template'] = 'membergroup_settings';
	$context['page_title'] = $txt['membergroups_settings'];

	// Needed for the inline permission functions.
	require_once($sourcedir . '/ManagePermissions.php');

	if (!empty($_POST['save_settings']))
	{
		checkSession();

		// Save the permissions.
		save_inline_permissions(array('manage_membergroups'));
	}

	// Initialize permissions.
	init_inline_permissions(array('manage_membergroups'), array(-1));
}

?>