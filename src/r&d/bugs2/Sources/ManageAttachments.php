<?php
/**********************************************************************************
* ManageAttachments.php                                                           *
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

/* /!!!

	void ManageAttachments()
		- main 'Attachments and Avatars' center function.
		- entry point for index.php?action=manageattachments.
		- requires the manage_attachments permission.
		- load the ManageAttachments template.
		- uses the Admin language file.
		- uses the template layer 'manage_files' for showing the tab bar.
		- calls a function based on the sub-action.

	void ManageAttachmentSettings()
		- show/change attachment settings.
		- default sub action for the 'Attachments and Avatars' center.
		- uses the 'attachments' sub template.
		- called by index.php?action=manageattachments;sa=attachements.

	void ManageAvatarSettings()
		- show/change avatar settings.
		- called by index.php?action=manageattachments;sa=avatars.
		- uses the 'avatars' sub template.
		- show/set permissions for permissions: 'profile_server_avatar',
		  'profile_upload_avatar' and 'profile_remote_avatar'.

	void BrowseFiles()
		- show a list of attachment or avatar files.
		- called by ?action=manageattachments;sa=browse for attachments and
		  ?action=manageattachments;sa=browse;avatars for avatars.
		- uses the 'browse' sub template
		- allows sorting by name, date, size and member.
		- paginates results.

	void MaintainFiles()
		- show several file maintenance options.
		- called by ?action=manageattachments;sa=maintain.
		- uses the 'maintain' sub template.
		- calculates file statistics (total file size, number of attachments,
		  number of avatars, attachment space available).

	void MoveAvatars()
		- move avatars from or to the attachment directory.
		- called from the maintenance screen by
		  ?action=manageattachments;sa=moveAvatars.

	void RemoveAttachmentByAge()
		- remove attachments older than a given age.
		- called from the maintenance screen by
		  ?action=manageattachments;sa=byAge.
		- optionally adds a certain text to the messages the attachments were
		  removed from.

	void RemoveAttachmentBySize()
		- remove attachments larger than a given size.
		- called from the maintenance screen by
		  ?action=manageattachments;sa=bySize.
		- optionally adds a certain text to the messages the attachments were
		  removed from.

	void RemoveAttachment()
		- remove a selection of attachments or avatars.
		- called from the browse screen as submitted form by
		  ?action=manageattachments;sa=remove

	void RemoveAllAttachments()
		- removes all attachments in a single click
		- called from the maintenance screen by
		  ?action=manageattachments;sa=removeall.

	array removeAttachments(string condition, string query_type = '', bool return_affected_messages = false, bool autoThumbRemoval = true)
		- removes attachments or avatars based on a given query condition.
		- called by several remove avatar/attachment functions in this file.
		- removes attachments based that match the $condition.
		- allows query_types 'messages' and 'members', whichever is need by the
		  $condition parameter.

	void RepairAttachments()
		// !!!

	void PauseAttachmentMaintenance()
		// !!!
*/

// The main attachment management function.
function ManageAttachments()
{
	global $txt, $db_prefix, $modSettings, $scripturl, $context, $options;

	// You have to be able to moderate the forum to do this.
	isAllowedTo('manage_attachments');

	// Show the administration bar, etc.
	adminIndex('manage_attachments');

	$context['template_layers'][] = 'manage_files';

	// If they want to delete attachment(s), delete them. (otherwise fall through..)
	$subActions = array(
		'attachments' => 'ManageAttachmentSettings',
		'avatars' => 'ManageAvatarSettings',
		'browse' => 'BrowseFiles',
		'byAge' => 'RemoveAttachmentByAge',
		'bySize' => 'RemoveAttachmentBySize',
		'maintenance' => 'MaintainFiles',
		'moveAvatars' => 'MoveAvatars',
		'repair' => 'RepairAttachments',
		'remove' => 'RemoveAttachment',
		'removeall' => 'RemoveAllAttachments'
	);

	loadTemplate('ManageAttachments');

	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
	else
		ManageAttachmentSettings();
}

function ManageAttachmentSettings()
{
	global $txt, $db_prefix, $modSettings, $scripturl, $context, $options, $sourcedir;

	require_once($sourcedir .'/ManagePermissions.php');

	$context['page_title'] = $txt['smf201'];
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'attachment_settings';
	$context['sub_template'] = 'attachments';

	if (!empty($_POST['attachmentSettings']))
	{
		checkSession();

		updateSettings(array(
			'attachmentEnable' => (int) $_POST['attachmentEnable'],
			'attachmentCheckExtensions' => empty($_POST['attachmentCheckExtensions']) ? '0' : '1',
			'attachmentExtensions' => $_POST['attachmentExtensions'],
			'attachmentShowImages' => empty($_POST['attachmentShowImages']) ? '0' : '1',
			'attachmentUploadDir' => $_POST['attachmentUploadDir'],
			'attachmentDirSizeLimit' => (int) $_POST['attachmentDirSizeLimit'],
			'attachmentPostLimit' => (int) $_POST['attachmentPostLimit'],
			'attachmentSizeLimit' => (int) $_POST['attachmentSizeLimit'],
			'attachmentNumPerPostLimit' => (int) $_POST['attachmentNumPerPostLimit'],
			'attachmentThumbnails' => empty($_POST['attachmentThumbnails']) ? '0' : '1',
			'attachmentThumbWidth' => (int) $_POST['attachmentThumbWidth'],
			'attachmentThumbHeight' => (int) $_POST['attachmentThumbHeight'],
		));
	}

	$context['valid_upload_dir'] = is_dir($modSettings['attachmentUploadDir']) && is_writable($modSettings['attachmentUploadDir']);
}

function ManageAvatarSettings()
{
	global $txt, $context, $db_prefix, $modSettings, $sourcedir;

	$context['page_title'] = $txt['smf201'];
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'avatar_settings';
	$context['sub_template'] = 'avatars';

	// Perform a test to see if the GD module is installed.
	$testGD = get_extension_funcs('gd');
	$context['gd_installed'] = !empty($testGD);

	// We need this file for the inline permission settings.
	require_once($sourcedir .'/ManagePermissions.php');

	// Let's save the avatar settings.
	if (!empty($_POST['avatarSettings']))
	{
		checkSession();

		// Store the changed mod settings.
		updateSettings(array(
			'avatar_directory' => $_POST['avatar_directory'],
			'avatar_url' => $_POST['avatar_url'],
			'avatar_download_external' => empty($_POST['avatar_download_external']) ? '0' : '1',
			'avatar_max_width_upload' => (int) $_POST['avatar_max_width_upload'],
			'avatar_max_height_upload' => (int) $_POST['avatar_max_height_upload'],
			'avatar_resize_upload' => empty($_POST['avatar_resize_upload']) ? '0' : '1',
			'avatar_download_png' => empty($_POST['avatar_download_png']) ? '0' : '1',
			'custom_avatar_enabled' => empty($_POST['custom_avatar_enabled']) ? '0' : '1',
		));

		// Only update these settings if they are not disabled by JavaScript.
		if (empty($_POST['avatar_download_external']))
			updateSettings(array(
				'avatar_max_width_external' => empty($_POST['avatar_max_width_external']) ? 0 : (int) $_POST['avatar_max_width_external'],
				'avatar_max_height_external' => empty($_POST['avatar_max_height_external']) ? 0 : (int) $_POST['avatar_max_height_external'],
				'avatar_action_too_large' => $_POST['avatar_action_too_large'],
			));

		if (!empty($_POST['custom_avatar_enabled']))
			updateSettings(array(
				'custom_avatar_dir' => $_POST['custom_avatar_dir'],
				'custom_avatar_url' => $_POST['custom_avatar_url'],
			));

		// Save the adjusted permissions.
		save_inline_permissions(array('profile_server_avatar', 'profile_upload_avatar', 'profile_remote_avatar'));
	}

	init_inline_permissions(array('profile_server_avatar', 'profile_upload_avatar', 'profile_remote_avatar'), array(-1));

	$context['valid_avatar_dir'] = is_dir($modSettings['avatar_directory']);
	$context['valid_custom_avatar_dir'] = empty($modSettings['custom_avatar_enabled']) || (is_dir($modSettings['custom_avatar_dir']) && is_writable($modSettings['custom_avatar_dir']));
}

function BrowseFiles()
{
	global $context, $db_prefix, $txt, $scripturl, $options, $modSettings;

	$context['page_title'] = $txt['smf201'];
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'browse';
	$context['sub_template'] = 'browse';

	// Attachments or avatars?
	$context['browse_type'] = isset($_REQUEST['avatars']) ? 'avatars' : (isset($_REQUEST['thumbs']) ? 'thumbs' : 'attachments');

	// Get the number of attachments.
	$context['num_attachments'] = 0;
	$context['num_thumbs'] = 0;
	$request = db_query("
		SELECT attachmentType, COUNT(*) AS num_attach
		FROM {$db_prefix}attachments
		WHERE attachmentType IN (0, 3)
			AND ID_MEMBER = 0
		GROUP BY attachmentType", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context[empty($row['attachmentType']) ? 'num_attachments' : 'num_thumbs'] = $row['num_attach'];
	mysql_free_result($request);

	// Also get the avatar amount.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE ID_MEMBER != 0", __FILE__, __LINE__);
	list ($context['num_avatars']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Allow for sorting of each column...
	$sort_methods = array(
		'name' => 'a.filename',
		'date' => $context['browse_type'] == 'avatars' ? 'mem.lastLogin' : 'm.ID_MSG',
		'size' => 'a.size',
		'member' => 'mem.realName'
	);

	// Set up the importantant sorting variables... if they picked one...
	if (!isset($_GET['sort']) || !isset($sort_methods[$_GET['sort']]))
	{
		$_GET['sort'] = 'date';
		$descending = !empty($options['view_newest_first']);
	}
	// ... and if they didn't...
	else
		$descending = isset($_GET['desc']);

	$context['sort_by'] = $_GET['sort'];
	$_GET['sort'] = $sort_methods[$_GET['sort']];
	$context['sort_direction'] = $descending ? 'down' : 'up';

	// Get the page index ready......
	if (!isset($_REQUEST['start']) || $_REQUEST['start'] < 0)
		$_REQUEST['start'] = 0;

	$context['page_index'] = constructPageIndex($scripturl . '?action=manageattachments;sa=' . $_REQUEST['sa'] . ($context['browse_type'] == 'attachments' ? '' : ';' . $context['browse_type']) . ';sort=' . $context['sort_by'] . ($context['sort_direction'] == 'down' ? ';desc' : ''), $_REQUEST['start'], $context['num_' . $context['browse_type']], $modSettings['defaultMaxMessages']);
	$context['start'] = $_REQUEST['start'];

	// Choose a query depending on what we are viewing.
	if ($context['browse_type'] == 'avatars')
		$request = db_query("
			SELECT
				'' AS ID_MSG, IFNULL(mem.realName, '$txt[470]') AS posterName, mem.lastLogin AS posterTime, 0 AS ID_TOPIC, a.ID_MEMBER,
				a.ID_ATTACH, a.filename, a.attachmentType, a.size, a.width, a.height, a.downloads, '' AS subject, 0 AS ID_BOARD
			FROM {$db_prefix}attachments AS a
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = a.ID_MEMBER)
			WHERE a.ID_MEMBER != 0
			ORDER BY $_GET[sort] " . ($descending ? 'DESC' : 'ASC') . "
			LIMIT $context[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	else
		$request = db_query("
			SELECT
				m.ID_MSG, IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime, m.ID_TOPIC, m.ID_MEMBER,
				a.ID_ATTACH, a.filename, a.attachmentType, a.size, a.width, a.height, a.downloads, mf.subject, t.ID_BOARD
			FROM ({$db_prefix}attachments AS a, {$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}messages AS mf)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			WHERE a.ID_MSG = m.ID_MSG
				AND a.attachmentType = " . ($context['browse_type'] == 'attachments' ? '0' : '3') . "
				AND t.ID_TOPIC = m.ID_TOPIC
				AND mf.ID_MSG = t.ID_FIRST_MSG
			ORDER BY $_GET[sort] " . ($descending ? 'DESC' : 'ASC') . "
			LIMIT $context[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['posts'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['posts'][] = array(
			'id' => $row['ID_MSG'],
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>'
			),
			'time' => empty($row['posterTime']) ? $txt['never'] : timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'attachment' => array(
				'id' => $row['ID_ATTACH'],
				'size' => round($row['size'] / 1024, 2),
				'width' => $row['width'],
				'height' => $row['height'],
				'name' => htmlspecialchars($row['filename']),
				'downloads' => $row['downloads'],
				'href' => $row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH']),
				'link' => '<a href="' . ($row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \'' . ($row['attachmentType'] == 1 ? '' : ';image') . '\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . htmlspecialchars($row['filename']) . '</a>'
			),
			'topic' => $row['ID_TOPIC'],
			'subject' => $row['subject'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>'
		);
	mysql_free_result($request);
}

function MaintainFiles()
{
	global $db_prefix, $context, $modSettings, $txt;

	$context['page_title'] = $txt['smf201'];
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'maintenance';
	$context['sub_template'] = 'maintenance';

	// Get the number of attachments....
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE attachmentType = 0
			AND ID_MEMBER = 0", __FILE__, __LINE__);
	list ($context['num_attachments']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Also get the avatar amount....
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE ID_MEMBER != 0", __FILE__, __LINE__);
	list ($context['num_avatars']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Find out how big the directory is.
	$attachmentDirSize = 0;
	$dir = @opendir($modSettings['attachmentUploadDir']) or fatal_lang_error('smf115b');
	while ($file = readdir($dir))
	{
		if (substr($file, 0, -1) == '.')
			continue;

		if (preg_match('~^post_tmp_\d+_\d+$~', $file) != 0)
		{
			// Temp file is more than 5 hours old!
			if (filemtime($modSettings['attachmentUploadDir'] . '/' . $file) < time() - 18000)
				@unlink($modSettings['attachmentUploadDir'] . '/' . $file);
			continue;
		}

		$attachmentDirSize += filesize($modSettings['attachmentUploadDir'] . '/' . $file);
	}
	closedir($dir);
	// Divide it into kilobytes.
	$attachmentDirSize /= 1024;

	// If they specified a limit only....
	if (!empty($modSettings['attachmentDirSizeLimit']))
		$context['attachment_space'] = max(round($modSettings['attachmentDirSizeLimit'] - $attachmentDirSize, 2), 0);
	$context['attachment_total_size'] = round($attachmentDirSize, 2);
}

// !!! Not implemented yet.
function MoveAvatars()
{
	global $db_prefix, $modSettings;

	// First make sure the custom avatar dir is writable.
	if (!is_writable($modSettings['custom_avatar_dir']))
	{
		// Try to fix it.
		@chmod($modSettings['custom_avatar_dir'], 0777);

		// Guess that didn't work :/?
		if (!is_writable($modSettings['custom_avatar_dir']))
			fatal_lang_error('attachments_no_write');
	}

	$request = db_query("
		SELECT ID_ATTACH, ID_MEMBER, filename, file_hash
		FROM {$db_prefix}attachments
		WHERE attachmentType = 0
			AND ID_MEMBER > 0", __FILE__, __LINE__);
	$updatedAvatars = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);

		if (rename($filename, $modSettings['custom_avatar_dir'] . '/' . $row['filename']))
			$updatedAvatars[] = $row['ID_ATTACH'];
	}
	mysql_free_result($request);

	if (!empty($updatedAvatars))
		db_query("
			UPDATE {$db_prefix}attachments
			SET attachmentType = 1
			WHERE ID_ATTACH IN (" . implode(', ', $updatedAvatars) . ')', __FILE__, __LINE__);

	redirectexit('action=manageattachments;sa=maintenance');
}

function RemoveAttachmentByAge()
{
	global $db_prefix, $modSettings;

	checkSession('post', 'manageattachments');

	// !!! Ignore messages in topics that are stickied?

	// Deleting an attachment?
	if ($_REQUEST['type'] != 'avatars')
	{
		// Get all the old attachments.
		$messages = removeAttachments('a.attachmentType = 0 AND m.posterTime < ' . (time() - 24 * 60 * 60 * $_POST['age']), 'messages', true);

		// Update the messages to reflect the change.
		if (!empty($messages))
			db_query("
				UPDATE {$db_prefix}messages
				SET body = " . (!empty($_POST['notice']) ? "CONCAT(body, '<br /><br />$_POST[notice]')" : '') . "
				WHERE ID_MSG IN (" . implode(', ', $messages) . ")
				LIMIT " . count($messages), __FILE__, __LINE__);
	}
	else
	{
		// Remove all the old avatars.
		removeAttachments('a.ID_MEMBER != 0 AND mem.lastLogin < ' . (time() - 24 * 60 * 60 * $_POST['age']), 'members');
	}
	redirectexit('action=manageattachments' . (empty($_REQUEST['avatars']) ? '' : ';avatars'));
}

function RemoveAttachmentBySize()
{
	global $db_prefix, $modSettings;

	checkSession('post', 'manageattachments');

	// Find humungous attachments.
	$messages = removeAttachments('a.attachmentType = 0 AND a.size > ' . (1024 * $_POST['size']), 'messages', true);

	// And make a note on the post.
	if (!empty($messages))
		db_query("
			UPDATE {$db_prefix}messages
			SET body = " . (!empty($_POST['notice']) ? "CONCAT(body, '<br /><br />$_POST[notice]')" : '') . "
			WHERE ID_MSG IN (" . implode(',', $messages) . ")
			LIMIT " . count($messages), __FILE__, __LINE__);

	redirectexit('action=manageattachments;sa=maintenance');
}

function RemoveAttachment()
{
	global $db_prefix, $modSettings, $txt;

	checkSession('post');

	if (!empty($_POST['remove']))
	{
		$attachments = array();
		// There must be a quicker way to pass this safety test??
		foreach ($_POST['remove'] as $removeID => $dummy)
			$attachments[] = (int) $removeID;

		if ($_REQUEST['type'] == 'avatars' && !empty($attachments))
			removeAttachments('a.ID_ATTACH IN (' . implode(', ', $attachments) . ')');
		else if (!empty($attachments))
		{
			$messages = removeAttachments('a.ID_ATTACH IN (' . implode(', ', $attachments) . ')', 'messages', true);

			// And change the message to reflect this.
			if (!empty($messages))
				db_query("
					UPDATE {$db_prefix}messages
					SET body = CONCAT(body, '<br /><br />" . addslashes($txt['smf216']) . "')
					WHERE ID_MSG IN (" . implode(', ', $messages) . ")
					LIMIT " . count($messages), __FILE__, __LINE__);
		}
	}

	$_GET['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'date';
	redirectexit('action=manageattachments;sa=browse;' . $_REQUEST['type'] . ';sort=' . $_GET['sort'] . (isset($_GET['desc']) ? ';desc' : '') . ';start=' . $_REQUEST['start']);
}

// !!! Not implemented (yet?)
function RemoveAllAttachments()
{
	global $db_prefix, $txt;

	checkSession('get', 'manageattachments');

	$messages = removeAttachments('a.attachmentType = 0', '', true);

	if (!isset($_POST['notice']))
		$_POST['notice'] = $txt['smf216'];

	// Add the notice on the end of the changed messages.
	if (!empty($messages))
		db_query("
			UPDATE {$db_prefix}messages
			SET body = CONCAT(body, '<br /><br />$_POST[notice]')
			WHERE ID_MSG IN (" . implode(',', $messages) . ")
			LIMIT " . count($messages), __FILE__, __LINE__);

	redirectexit('action=manageattachments;sa=maintenance');
}

// Removes attachments - allowed query_types: '', 'messages', 'members'
function removeAttachments($condition, $query_type = '', $return_affected_messages = false, $autoThumbRemoval = true)
{
	global $db_prefix, $modSettings;

	// Delete it only if it exists...
	$msgs = array();
	$attach = array();
	$parents = array();

	// Get all the attachment names and ID_MSGs.
	$request = db_query("
		SELECT
			a.filename, a.file_hash, a.attachmentType, a.ID_ATTACH, a.ID_MEMBER" . ($query_type == 'messages' ? ', m.ID_MSG' : ', a.ID_MSG') . ",
			IFNULL(thumb.ID_ATTACH, 0) AS ID_THUMB, thumb.filename AS thumb_filename, thumb_parent.ID_ATTACH AS ID_PARENT,
			thumb.file_hash as thumb_file_hash
		FROM ({$db_prefix}attachments AS a" .($query_type == 'members' ? ", {$db_prefix}members AS mem" : ($query_type == 'messages' ? ", {$db_prefix}messages AS m" : '')) . ")
			LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.ID_ATTACH = a.ID_THUMB)
			LEFT JOIN {$db_prefix}attachments AS thumb_parent ON (a.attachmentType = 3 AND thumb_parent.ID_THUMB = a.ID_ATTACH)
		WHERE $condition" . ($query_type == 'messages' ? '
			AND m.ID_MSG = a.ID_MSG' : '') . ($query_type == 'members' ? '
			AND mem.ID_MEMBER = a.ID_MEMBER' : ''), __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		// Figure out the "encrypted" filename and unlink it ;).
		if ($row['attachmentType'] == 1)
			@unlink($modSettings['custom_avatar_dir'] . '/' . $row['filename']);
		else
		{
			$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
			@unlink($filename);

			// If this was a thumb, the parent attachment should know about it.
			if (!empty($row['ID_PARENT']))
				$parents[] = $row['ID_PARENT'];

			// If this attachments has a thumb, remove it as well.
			if (!empty($row['ID_THUMB']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['ID_THUMB'], false, $row['thumb_file_hash']);
				@unlink($thumb_filename);
				$attach[] = $row['ID_THUMB'];
			}
		}

		// Make a list.
		if ($return_affected_messages && empty($row['attachmentType']))
			$msgs[] = $row['ID_MSG'];
		$attach[] = $row['ID_ATTACH'];
	}
	mysql_free_result($request);

	// Removed attachments don't have to be updated anymore.
	$parents = array_diff($parents, $attach);
	if (!empty($parents))
		db_query("
			UPDATE {$db_prefix}attachments
			SET ID_THUMB = 0
			WHERE ID_ATTACH IN (" . implode(', ', $parents) . ")
			LIMIT " . count($parents), __FILE__, __LINE__);

	if (!empty($attach))
		db_query("
			DELETE FROM {$db_prefix}attachments
			WHERE ID_ATTACH IN (" . implode(', ', $attach) . ")
			LIMIT " . count($attach), __FILE__, __LINE__);

	if ($return_affected_messages)
		return array_unique($msgs);
}

// This function should find attachments in the database that no longer exist and clear them, and fix filesize issues.
function RepairAttachments()
{
	global $db_prefix, $modSettings, $context, $txt;

	$context['page_title'] = $txt['repair_attachments'];
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'maintenance';
	$context['sub_template'] = 'attachment_repair';

	checkSession('get');

	// If we choose cancel, redirect right back.
	if (isset($_POST['cancel']))
		redirectexit('action=manageattachments;sa=maintenance');

	// Try give us a while to sort this out...
	@set_time_limit(600);

	$_GET['step'] = empty($_GET['step']) ? 0 : (int) $_GET['step'];
	$_GET['substep'] = empty($_GET['substep']) ? 0 : (int) $_GET['substep'];

	// Don't recall the session just incase.
	if ($_GET['step'] == 0 && $_GET['substep'] == 0)
	{
		unset($_SESSION['attachments_to_fix']);
		unset($_SESSION['attachments_to_fix2']);

		// If we're actually fixing stuff - work out what.
		if (isset($_GET['fixErrors']))
		{
			// Nothing?
			if (empty($_POST['to_fix']))
				redirectexit('action=manageattachments;sa=maintenance');
	
			$_SESSION['attachments_to_fix'] = array();
			//!!! No need to do this I think.
			foreach ($_POST['to_fix'] as $key => $value)
				$_SESSION['attachments_to_fix'][] = $value;
		}
	}
	
	$to_fix = !empty($_SESSION['attachments_to_fix']) ? $_SESSION['attachments_to_fix'] : array();
	$context['repair_errors'] = isset($_SESSION['attachments_to_fix2']) ? $_SESSION['attachments_to_fix2'] : array();
	$fix_errors = isset($_GET['fixErrors']) ? true : false;

	// All the valid problems are here:
	$context['repair_errors'] = array(
		'missing_thumbnail_parent' => 0,
		'parent_missing_thumbnail' => 0,
		'file_missing_on_disk' => 0,
		'file_wrong_size' => 0,
		'file_size_of_zero' => 0,
		'attachment_no_msg' => 0,
		'avatar_no_member' => 0,
	);

	// Get stranded thumbnails.
	if ($_GET['step'] <= 0)
	{
		$result = db_query("
			SELECT MAX(ID_ATTACH)
			FROM {$db_prefix}attachments
			WHERE attachmentType = 3", __FILE__, __LINE__);
		list ($thumbnails) = mysql_fetch_row($result);
		mysql_free_result($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = db_query("
				SELECT thumb.ID_ATTACH, thumb.filename, thumb.file_hash
				FROM {$db_prefix}attachments AS thumb
					LEFT JOIN {$db_prefix}attachments AS tparent ON (tparent.ID_THUMB = thumb.ID_ATTACH)
				WHERE thumb.ID_ATTACH BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND thumb.attachmentType = 3
					AND tparent.ID_ATTACH IS NULL
				GROUP BY thumb.ID_ATTACH", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				$to_remove[] = $row['ID_ATTACH'];
				$context['repair_errors']['missing_thumbnail_parent']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
					@unlink($filename);
				}
			}
			if (mysql_num_rows($result) != 0)
				$to_fix[] = 'missing_thumbnail_parent';
			mysql_free_result($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('missing_thumbnail_parent', $to_fix))
				db_query("
					DELETE FROM {$db_prefix}attachments
					WHERE ID_ATTACH IN (" . implode(', ', $to_remove) . ")
						AND attachmentType = 3", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 1;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Find parents which think they have thumbnails, but actually, don't.
	if ($_GET['step'] <= 1)
	{
		$result = db_query("
			SELECT MAX(ID_ATTACH)
			FROM {$db_prefix}attachments
			WHERE ID_THUMB != 0", __FILE__, __LINE__);
		list ($thumbnails) = mysql_fetch_row($result);
		mysql_free_result($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_update = array();

			$result = db_query("
				SELECT a.ID_ATTACH
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.ID_ATTACH = a.ID_THUMB)
				WHERE a.ID_ATTACH BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.ID_THUMB != 0
					AND thumb.ID_ATTACH IS NULL", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				$to_update[] = $row['ID_ATTACH'];
				$context['repair_errors']['parent_missing_thumbnail']++;
			}
			if (mysql_num_rows($result) != 0)
				$to_fix[] = 'parent_missing_thumbnail';
			mysql_free_result($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_update) && in_array('parent_missing_thumbnail', $to_fix))
				db_query("
					UPDATE {$db_prefix}attachments
					SET ID_THUMB = 0
					WHERE ID_ATTACH IN (" . implode(', ', $to_update) . ")", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 2;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// This may take forever I'm afraid, but life sucks... recount EVERY attachments!
	if ($_GET['step'] <= 2)
	{
		$result = db_query("
			SELECT MAX(ID_ATTACH)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = mysql_fetch_row($result);
		mysql_free_result($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 250)
		{
			$to_remove = array();
			$errors_found = array();

			$result = db_query("
				SELECT ID_ATTACH, filename, file_hash, size, attachmentType
				FROM {$db_prefix}attachments
				WHERE ID_ATTACH BETWEEN $_GET[substep] AND $_GET[substep] + 249", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				// Get the filename.
				if ($row['attachmentType'] == 1)
					$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
				else
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);

				// File doesn't exist?
				if (!file_exists($filename))
				{
					$to_remove[] = $row['ID_ATTACH'];
					$context['repair_errors']['file_missing_on_disk']++;
					$errors_found[] = 'file_missing_on_disk';

					// Are we fixing this?
					if ($fix_errors && in_array('file_missing_on_disk', $to_fix))
						$to_remove[] = $row['ID_ATTACH'];

				}
				elseif (filesize($filename) == 0)
				{
					$context['repair_errors']['file_size_of_zero']++;
					$errors_found[] = 'file_size_of_zero';

					// Fixing?
					if ($fix_errors && in_array('file_size_of_zero', $to_fix))
					{
						$to_remove[] = $row['ID_ATTACH'];
						@unlink($filename);
					}
				}
				elseif (filesize($filename) != $row['size'])
				{
					$context['repair_errors']['file_wrong_size']++;
					$errors_found[] = 'file_wrong_size';

					// Fix it here?
					if ($fix_errors && in_array('file_wrong_size', $to_fix))
					{
						db_query("
							UPDATE {$db_prefix}attachments
							SET size = " . filesize($filename) . "
							WHERE ID_ATTACH = $row[ID_ATTACH]
							LIMIT 1", __FILE__, __LINE__);
					}
				}
			}

			if (in_array('file_missing_on_disk', $errors_found))
				$to_fix[] = 'file_missing_on_disk';
			if (in_array('file_size_of_zero', $errors_found))
				$to_fix[] = 'file_size_of_zero';
			if (in_array('file_wrong_size', $errors_found))
				$to_fix[] = 'file_wrong_size';
			mysql_free_result($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove))
			{
				db_query("
					DELETE FROM {$db_prefix}attachments
					WHERE ID_ATTACH IN (" . implode(', ', $to_remove) . ")", __FILE__, __LINE__);
				db_query("
					UPDATE {$db_prefix}attachments
					SET ID_THUMB = 0
					WHERE ID_THUMB IN (" . implode(', ', $to_remove) . ")", __FILE__, __LINE__);
			}
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 3;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Get avatars with no members associated with them.
	if ($_GET['step'] <= 3)
	{
		$result = db_query("
			SELECT MAX(ID_ATTACH)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = mysql_fetch_row($result);
		mysql_free_result($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = db_query("
				SELECT a.ID_ATTACH, a.filename, a.file_hash, a.attachmentType
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = a.ID_MEMBER)
				WHERE a.ID_ATTACH BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.ID_MEMBER != 0
					AND a.ID_MSG = 0
					AND mem.ID_MEMBER IS NULL", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				$to_remove[] = $row['ID_ATTACH'];
				$context['repair_errors']['avatar_no_member']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('avatar_no_member', $to_fix))
				{
					if ($row['attachmentType'] == 1)
						$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
					else
						$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
					@unlink($filename);
				}
			}
			if (mysql_num_rows($result) != 0)
				$to_fix[] = 'avatar_no_member';
			mysql_free_result($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('avatar_no_member', $to_fix))
				db_query("
					DELETE FROM {$db_prefix}attachments
					WHERE ID_ATTACH IN (" . implode(', ', $to_remove) . ")
						AND ID_MEMBER != 0
						AND ID_MSG = 0", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 4;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// What about attachments, who are missing a message :'(
	if ($_GET['step'] <= 4)
	{
		$result = db_query("
			SELECT MAX(ID_ATTACH)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = mysql_fetch_row($result);
		mysql_free_result($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = db_query("
				SELECT a.ID_ATTACH, a.filename, a.file_hash
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG = a.ID_MSG)
				WHERE a.ID_ATTACH BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.ID_MEMBER = 0
					AND a.ID_MSG != 0
					AND m.ID_MSG IS NULL", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				$to_remove[] = $row['ID_ATTACH'];
				$context['repair_errors']['attachment_no_msg']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
					@unlink($filename);
				}
			}
			if (mysql_num_rows($result) != 0)
				$to_fix[] = 'attachment_no_msg';
			mysql_free_result($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('attachment_no_msg', $to_fix))
				db_query("
					DELETE FROM {$db_prefix}attachments
					WHERE ID_ATTACH IN (" . implode(', ', $to_remove) . ")
						AND ID_MEMBER = 0
						AND ID_MSG != 0", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 5;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Got here we must be doing well :D
	$context['completed'] = $fix_errors ? true : false;
	$context['errors_found'] = !empty($to_fix) ? true : false;
	
}

function pauseAttachmentMaintenance($to_fix, $max_substep = 0)
{
	global $context, $txt, $time_start;

	// Try get more time...
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	// Have we already used our maximum time?
	if (time() - array_sum(explode(' ', $time_start)) < 3)
		return;

	// Specific stuff to not break this template!
	$context['page_title'] = $txt['not_done_title'];
	$context['sub_template'] = 'not_done';
	$context['description'] = $txt['smf202'];
	$context['selected'] = 'maintenance';

	$context['continue_get_data'] = '?action=manageattachments;sa=repair' . (isset($_GET['fixErrors']) ? ';fixErrors' : '') . ';step=' . $_GET['step'] . ';substep=' . $_GET['substep'] . ';sesc=' . $context['session_id'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '2';

	// Change these two if more steps are added!
	if (empty($max_substep))
		$context['continue_percent'] = round(($_GET['step'] * 100) / 25);
	else
		$context['continue_percent'] = round(($_GET['step'] * 100 + ($_GET['substep'] * 100) / $max_substep) / 25);

	// Never more than 100%!
	$context['continue_percent'] = min($context['continue_percent'], 100);

	$_SESSION['attachments_to_fix'] = $to_fix;
	$_SESSION['attachments_to_fix2'] = $context['repair_errors'];

	obExit();
}

?>