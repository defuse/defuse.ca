<?php
/**********************************************************************************
* ManageNews.php                                                                  *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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

/*
	void ManageNews()
		- the entrance point for all News and Newsletter screens.
		- called by ?action=news.
		- does the permission checks.
		- calls the appropriate function based on the requested sub-action.

	void EditNews()
		- changes the current news items for the forum.
		- uses the ManageNews template and edit_news sub template.
		- called by ?action=news.
		- requires the edit_news permission.
		- writes an entry into the moderation log.
		- uses the edit_news administration area.
		- can be accessed with ?action=editnews.

	void SelectMailingMembers()
		- allows a user to select the membergroups to send their mailing to.
		- uses the ManageNews template and email_members sub template.
		- called by ?action=news;sa=mailingmembers.
		- requires the send_mail permission.
		- form is submitted to ?action=news;mailingcompose.

	void ComposeMailing()
		- shows a form to edit a forum mailing and its recipients.
		- uses the ManageNews template and email_members_compose sub template.
		- called by ?action=news;sa=mailingcompose.
		- requires the send_mail permission.
		- form is submitted to ?action=news;sa=mailingsend.

	void SendMailing()
		- handles the sending of the forum mailing in batches.
		- uses the ManageNews template and email_members_send sub template.
		- called by ?action=news;sa=mailingsend
		- requires the send_mail permission.
		- redirects to itself when more batches need to be sent.
		- redirects to ?action=admin after everything has been sent.

	void NewsSettings()
		- set general news and newsletter settings and permissions.
		- uses the ManageNews template and news_settings sub template.
		- called by ?action=news;sa=settings.
		- requires the forum_admin permission.
*/

// The controller; doesn't do anything, just delegates.
function ManageNews()
{
	global $context, $txt, $scripturl;

	// First, let's do a quick permissions check for the best error message possible.
	isAllowedTo(array('edit_news', 'send_mail', 'admin_forum'));

	// Administrative side bar, here we come!
	adminIndex('news');

	loadTemplate('ManageNews');

	// Format: 'sub-action' => array('function', 'permission')
	$subActions = array(
		'editnews' => array('EditNews', 'edit_news'),
		'mailingmembers' => array('SelectMailingMembers', 'send_mail'),
		'mailingcompose' => array('ComposeMailing', 'send_mail'),
		'mailingsend' =>  array('SendMailing', 'send_mail'),
		'settings' => array('ModifyNewsSettings', 'admin_forum'),
	);

	// Default to sub action 'main' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('edit_news') ? 'editnews' : (allowedTo('send_mail') ? 'mailingmembers' : 'settings'));

	// Have you got the proper permissions?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['news_title'],
		'help' => 'edit_news',
		'description' => $txt[670],
		'tabs' => array(),
	);
	if (allowedTo('edit_news'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt[7],
			'description' => $txt[670],
			'href' => $scripturl . '?action=news',
			'is_selected' => $_REQUEST['sa'] == 'editnews',
		);
	if (allowedTo('send_mail'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt[6],
			'description' => $txt['news_mailing_desc'],
			'href' => $scripturl . '?action=news;sa=mailingmembers',
			'is_selected' => substr($_REQUEST['sa'], 0, 7) == 'mailing',
		);
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['settings'],
			'description' => $txt['news_settings_desc'],
			'href' => $scripturl . '?action=news;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;

	$subActions[$_REQUEST['sa']][0]();
}

// Let the administrator(s) edit the news.
function EditNews()
{
	global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
	global $func;

	require_once($sourcedir . '/Subs-Post.php');

	// The 'remove selected' button was pressed.
	if (!empty($_POST['delete_selection']) && !empty($_POST['remove']))
	{
		checkSession();

		// Store the news temporarily in this array.
		$temp_news = explode("\n", $modSettings['news']);

		// Remove the items that were selected.
		foreach ($temp_news as $i => $news)
			if (in_array($i, $_POST['remove']))
				unset($temp_news[$i]);

		// Update the database.
		updateSettings(array('news' => addslashes(implode("\n", $temp_news))));

		logAction('news');
	}
	// The 'Save' button was pressed.
	elseif (!empty($_POST['save_items']))
	{
		checkSession();

		foreach ($_POST['news'] as $i => $news)
		{
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
			{
				$_POST['news'][$i] = $func['htmlspecialchars']($_POST['news'][$i], ENT_QUOTES);
				preparsecode($_POST['news'][$i]);
			}
		}

		// Send the new news to the database.
		updateSettings(array('news' => implode("\n", $_POST['news'])));

		// Log this into the moderation log.
		logAction('news');
	}

	// Ready the current news.
	foreach (explode("\n", $modSettings['news']) as $id => $line)
		$context['admin_current_news'][$id] = array(
			'id' => $id,
			'unparsed' => un_preparsecode($line),
			'parsed' => preg_replace('~<([/]?)form[^>]*?[>]*>~i', '<em class="smalltext">&lt;$1form&gt;</em>', parse_bbc($line)),
		);

	$context['sub_template'] = 'edit_news';
	$context['page_title'] = $txt[7];
}

function SelectMailingMembers()
{
	global $txt, $db_prefix, $context, $modSettings;

	$context['page_title'] = $txt[6];

	$context['sub_template'] = 'email_members';

	$context['groups'] = array();
	$postGroups = array();
	$normalGroups = array();

	// If we have post groups disabled then we need to give a "ungrouped members" option.
	if (empty($modSettings['permission_enable_postgroups']))
	{
		$context['groups'][0] = array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'member_count' => 0,
		);
		$normalGroups[0] = 0;
	}

	// Get all the extra groups as well as Administrator and Global Moderator.
	$request = db_query("
		SELECT mg.ID_GROUP, mg.groupName, mg.minPosts
		FROM {$db_prefix}membergroups AS mg" . (empty($modSettings['permission_enable_postgroups']) ? "
		WHERE mg.minPosts = -1" : '') . "
		GROUP BY mg.ID_GROUP
		ORDER BY mg.minPosts, IF(mg.ID_GROUP < 4, mg.ID_GROUP, 4), mg.groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'member_count' => 0,
		);

		if ($row['minPosts'] == -1)
			$normalGroups[$row['ID_GROUP']] = $row['ID_GROUP'];
		else
			$postGroups[$row['ID_GROUP']] = $row['ID_GROUP'];
	}
	mysql_free_result($request);

	// If we have post groups, let's count the number of members...
	if (!empty($postGroups))
	{
		$query = db_query("
			SELECT mem.ID_POST_GROUP AS ID_GROUP, COUNT(*) AS member_count
			FROM {$db_prefix}members AS mem
			WHERE mem.ID_POST_GROUP IN (" . implode(', ', $postGroups) . ")
			GROUP BY mem.ID_POST_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['member_count'] += $row['member_count'];
		mysql_free_result($query);
	}

	if (!empty($normalGroups))
	{
		// Find people who are members of this group...
		$query = db_query("
			SELECT ID_GROUP, COUNT(*) AS member_count
			FROM {$db_prefix}members
			WHERE ID_GROUP IN (" . implode(',', $normalGroups) . ")
			GROUP BY ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['member_count'] += $row['member_count'];
		mysql_free_result($query);

		// Also do those who have it as an additional membergroup - this ones more yucky...
		$query = db_query("
			SELECT mg.ID_GROUP, COUNT(*) AS member_count
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.ID_GROUP IN (" . implode(',', $normalGroups) . ")
				AND mem.additionalGroups != ''
				AND mem.ID_GROUP != mg.ID_GROUP
				AND FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups)
			GROUP BY mg.ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['member_count'] += $row['member_count'];
		mysql_free_result($query);
	}

	// Any moderators?
	$request = db_query("
		SELECT COUNT(DISTINCT ID_MEMBER) AS num_distinct_mods
		FROM {$db_prefix}moderators
		LIMIT 1", __FILE__, __LINE__);
	list ($context['groups'][3]['member_count']) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['can_send_pm'] = allowedTo('pm_send');
}

// Email your members...
function ComposeMailing()
{
	global $txt, $db_prefix, $sourcedir, $context;

	$list = array();
	$do_pm = !empty($_POST['sendPM']);

	// Opt-out?
	$condition = isset($_POST['email_force']) ? '' : '
				AND mem.notifyAnnouncements = 1';


	// Get a list of all full banned users.  Use their Username and email to find them.  Only get the ones that can't login to turn off notification.
	$request = db_query("
		SELECT DISTINCT mem.ID_MEMBER
		FROM {$db_prefix}ban_groups AS bg
		INNER JOIN {$db_prefix}ban_items AS bi ON (bg.ID_BAN_GROUP = bi.ID_BAN_GROUP)
		INNER JOIN {$db_prefix}members AS mem ON (bi.ID_MEMBER = mem.ID_MEMBER)
		WHERE (bg.cannot_access = 1 OR bg.cannot_login = 1)
			AND (ISNULL(bg.expire_time) OR bg.expire_time > " . time() . ")", __FILE__, __LINE__);
	$condition_array = array();
	$members = array();
	while ($row = mysql_fetch_assoc($request))
		$members[] = $row['ID_MEMBER'];
	if (!empty($members))
		$condition_array[] = 'mem.ID_MEMBER NOT IN (' . implode(', ', $members) . ')';

	$request = db_query("
		SELECT DISTINCT bi.email_address
		FROM {$db_prefix}ban_items AS bi
		INNER JOIN {$db_prefix}ban_groups AS bg ON (bg.ID_BAN_GROUP = bi.ID_BAN_GROUP)
		WHERE (bg.cannot_access = 1 OR bg.cannot_login = 1)
			AND (ISNULL(bg.expire_time) OR bg.expire_time > " . time() . ")
			AND bi.email_address != ''", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$condition_array[] = "mem.emailAddress NOT LIKE '" . $row['email_address'] . "'";

	if (!empty($condition_array))
		$condition .= '
				AND ' . implode('
				AND ', $condition_array);

	// Did they select moderators too?
	if (!empty($_POST['who']) && in_array(3, $_POST['who']))
	{
		$request = db_query("
			SELECT DISTINCT " . ($do_pm ? 'mem.memberName' : 'mem.emailAddress') . " AS identifier
			FROM ({$db_prefix}members AS mem, {$db_prefix}moderators AS mods)
			WHERE mem.ID_MEMBER = mods.ID_MEMBER
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$list[] = $row['identifier'];
		mysql_free_result($request);

		unset($_POST['who'][3], $_POST['who'][3]);
	}

	// How about regular members?
	if (!empty($_POST['who']) && in_array(0, $_POST['who']))
	{
		$request = db_query("
			SELECT " . ($do_pm ? 'mem.memberName' : 'mem.emailAddress') . " AS identifier
			FROM {$db_prefix}members AS mem
			WHERE mem.ID_GROUP = 0
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$list[] = $row['identifier'];
		mysql_free_result($request);

		unset($_POST['who'][0], $_POST['who'][0]);
	}

	// Load all the other groups.
	if (!empty($_POST['who']))
	{
		foreach ($_POST['who'] as $k => $v)
			$_POST['who'][$k] = (int) $v;

		$request = db_query("
			SELECT " . ($do_pm ? 'mem.memberName' : 'mem.emailAddress') . " AS identifier
			FROM ({$db_prefix}members AS mem, {$db_prefix}membergroups AS mg)
			WHERE (mg.ID_GROUP = mem.ID_GROUP OR FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups) OR mg.ID_GROUP = mem.ID_POST_GROUP)
				AND mg.ID_GROUP IN (" . implode(',', $_POST['who']) . ")
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$list[] = $row['identifier'];
		mysql_free_result($request);
	}

	// Tear out duplicates....
	$list = array_unique($list);

	// Sending as a personal message?
	if ($do_pm)
	{
		require_once($sourcedir . '/PersonalMessage.php');
		require_once($sourcedir . '/Subs-Post.php');
		$_REQUEST['bcc'] = implode(',', $list);
		MessagePost();
	}
	else
	{
		$context['page_title'] = $txt[6];

		// Just send the to list to the template.
		$context['addresses'] = implode('; ', $list);
		$context['default_subject'] = $context['forum_name'] . ': ' . $txt[70];
		$context['default_message'] = $txt[72] . "\n\n" . $txt[130] . "\n\n{\$board_url}";

		$context['sub_template'] = 'email_members_compose';
	}
}

function SendMailing()
{
	global $txt, $db_prefix, $sourcedir, $context;
	global $scripturl, $modSettings, $user_info;

	checkSession();

	require_once($sourcedir . '/Subs-Post.php');

	// How many to send at once?
	$num_at_once = 60;

	// Get all the receivers.
	$addressed = array_unique(explode(';', stripslashes($_POST['emails'])));
	$cleanlist = array();
	foreach ($addressed as $curmem)
	{
		$curmem = trim($curmem);
		if ($curmem != '')
			$cleanlist[$curmem] = $curmem;
	}

	$context['emails'] = implode(';', $cleanlist);
	$context['subject'] = htmlspecialchars(stripslashes($_POST['subject']));
	$context['message'] = htmlspecialchars(stripslashes($_POST['message']));
	$context['send_html'] = !empty($_POST['send_html']) ? '1' : '0';
	$context['parse_html'] = !empty($_POST['parse_html']) ? '1' : '0';
	$context['start'] = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;

	$send_list = array();
	$i = 0;
	foreach ($cleanlist as $email)
	{
		if (++$i <= $context['start'])
			continue;
		if ($i > $context['start'] + $num_at_once)
			break;

		$send_list[$email] = $email;
	}

	$context['start'] += $num_at_once;
	$context['percentage_done'] = round(($context['start'] * 100) / count($cleanlist), 2);

	// Prepare the message for HTML.
	if (!empty($_POST['send_html']) && !empty($_POST['parse_html']))
		$_POST['message'] = str_replace(array("\n", '  '), array("<br />\n", '&nbsp; '), stripslashes($_POST['message']));
	else
		$_POST['message'] = stripslashes($_POST['message']);

	// Use the default time format.
	$user_info['time_format'] = $modSettings['time_format'];

	$variables = array(
		'{$board_url}',
		'{$current_time}',
		'{$latest_member.link}',
		'{$latest_member.id}',
		'{$latest_member.name}'
	);

	// Replace in all the standard things.
	$_POST['message'] = str_replace($variables,
		array(
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '">' . $scripturl . '</a>' : $scripturl,
			timeformat(forum_time(), false),
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '?action=profile;u=' . $modSettings['latestMember'] . '">' . $modSettings['latestRealName'] . '</a>' : $modSettings['latestRealName'],
			$modSettings['latestMember'],
			$modSettings['latestRealName']
		), $_POST['message']);
	$_POST['subject'] = str_replace($variables,
		array(
			$scripturl,
			timeformat(forum_time(), false),
			$modSettings['latestRealName'],
			$modSettings['latestMember'],
			$modSettings['latestRealName']
		), stripslashes($_POST['subject']));

	$from_member = array(
		'{$member.email}',
		'{$member.link}',
		'{$member.id}',
		'{$member.name}'
	);

	// This is here to prevent spam filters from tagging this as spam.
	if (!empty($_POST['send_html']) && preg_match('~\<html~i', $_POST['message']) == 0)
	{
		if (preg_match('~\<body~i', $_POST['message']) == 0)
			$_POST['message'] = '<html><head><title>' . $_POST['subject'] . '</title></head>' . "\n" . '<body>' . $_POST['message'] . '</body></html>';
		else
			$_POST['message'] = '<html>' . $_POST['message'] . '</html>';
	}

	$result = db_query("
		SELECT realName, memberName, ID_MEMBER, emailAddress
		FROM {$db_prefix}members
		WHERE emailAddress IN ('" . implode("', '", addslashes__recursive($send_list)) . "')
			AND is_activated = 1", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
	{
		unset($send_list[$row['emailAddress']]);

		$to_member = array(
			$row['emailAddress'],
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			$row['ID_MEMBER'],
			$row['realName']
		);

		// Send the actual email off, replacing the member dependent variables.
		sendmail($row['emailAddress'], str_replace($from_member, $to_member, addslashes($_POST['subject'])), str_replace($from_member, $to_member, addslashes($_POST['message'])), null, null, !empty($_POST['send_html']));
	}
	mysql_free_result($result);

	// Send the emails to people who weren't members....
	if (!empty($send_list))
		foreach ($send_list as $email)
		{
			$to_member = array(
				$email,
				!empty($_POST['send_html']) ? '<a href="mailto:' . $email . '">' . $email . '</a>' : $email,
				'??',
				$email
			);

			sendmail($email, str_replace($from_member, $to_member, addslashes($_POST['subject'])), str_replace($from_member, $to_member, addslashes($_POST['message'])), null, null, !empty($_POST['send_html']));
		}

	// Still more to do?
	if (count($cleanlist) > $context['start'])
	{
		$context['page_title'] = $txt[6];

		$context['sub_template'] = 'email_members_send';
		return;
	}

	redirectexit('action=admin');
}

function ModifyNewsSettings()
{
	global $context, $db_prefix, $sourcedir, $modSettings, $txt;

	$context['page_title'] = $txt[7] . ' - ' . $txt['settings'];
	$context['sub_template'] = 'news_settings';

	// Needed for the inline permission functions.
	require_once($sourcedir . '/ManagePermissions.php');

	if (!empty($_POST['save_settings']))
	{
		checkSession();

		updateSettings(array(
			'xmlnews_enable' => empty($_POST['xmlnews_enable']) ? '0' : '1',
			'xmlnews_maxlen' => (int) $_POST['xmlnews_maxlen'],
		));

		// Save the permissions.
		save_inline_permissions(array('edit_news', 'send_mail'));
	}

	// Initialize permissions.
	init_inline_permissions(array('edit_news', 'send_mail'), array(-1));
}

?>