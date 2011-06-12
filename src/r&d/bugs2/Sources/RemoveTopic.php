<?php
/**********************************************************************************
* RemoveTopic.php                                                                 *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.5                                           *
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

/*	The contents of this file handle the deletion of topics, posts, and related
	paraphernalia.  It has the following functions:

	void RemoveTopic2()
		// !!!

	void DeleteMessage()
		// !!!

	void RemoveOldTopics2()
		// !!!

	void removeTopics(array topics, bool decreasePostCount = true, bool ignoreRecycling = false)
		// !!!

	bool removeMessage(int ID_MSG, bool decreasePostCount = true)
		// !!!
*/

// Completely remove an entire topic.
function RemoveTopic2()
{
	global $ID_MEMBER, $db_prefix, $topic, $board, $sourcedir;

	// Make sure they aren't being lead around by someone. (:@)
	checkSession('get');

	// This file needs to be included for sendNotifications().
	require_once($sourcedir . '/Subs-Post.php');

	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, ms.subject
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS ms)
		WHERE t.ID_TOPIC = $topic
			AND ms.ID_MSG = t.ID_FIRST_MSG
		LIMIT 1", __FILE__, __LINE__);
	list ($starter, $subject) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($starter == $ID_MEMBER && !allowedTo('remove_any'))
		isAllowedTo('remove_own');
	else
		isAllowedTo('remove_any');

	// Notify people that this topic has been removed.
	sendNotifications($topic, 'remove');

	removeTopics($topic);

	if (allowedTo('remove_any') && (!allowedTo('remove_own') || $starter != $ID_MEMBER))
		logAction('remove', array('topic' => $topic, 'subject' => $subject, 'member' => $starter));

	redirectexit('board=' . $board . '.0');
}

// Remove just a single post.
function DeleteMessage()
{
	global $ID_MEMBER, $db_prefix, $topic, $board, $modSettings;

	checkSession('get');

	$_REQUEST['msg'] = (int) $_REQUEST['msg'];

	// Is $topic set?
	if (empty($topic) && isset($_REQUEST['topic']))
		$topic = (int) $_REQUEST['topic'];

	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, m.ID_MEMBER, m.subject, m.posterTime
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE t.ID_TOPIC = $topic
			AND m.ID_TOPIC = $topic
			AND m.ID_MSG = $_REQUEST[msg]
		LIMIT 1", __FILE__, __LINE__);
	list ($starter, $poster, $subject, $post_time) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($poster == $ID_MEMBER)
	{
		if (!allowedTo('delete_own'))
		{
			if ($starter == $ID_MEMBER && !allowedTo('delete_any'))
				isAllowedTo('delete_replies');
			elseif (!allowedTo('delete_any'))
				isAllowedTo('delete_own');
		}
		elseif (!allowedTo('delete_any') && ($starter != $ID_MEMBER || !allowedTo('delete_replies')) && !empty($modSettings['edit_disable_time']) && $post_time + $modSettings['edit_disable_time'] * 60 < time())
			fatal_lang_error('modify_post_time_passed', false);
	}
	elseif ($starter == $ID_MEMBER && !allowedTo('delete_any'))
		isAllowedTo('delete_replies');
	else
		isAllowedTo('delete_any');

	// If the full topic was removed go back to the board.
	$full_topic = removeMessage($_REQUEST['msg']);

	if (allowedTo('delete_any') && (!allowedTo('delete_own') || $poster != $ID_MEMBER))
		logAction('delete', array('topic' => $topic, 'subject' => $subject, 'member' => $starter));

	// We want to redirect back to recent action.
	if (isset($_REQUEST['recent']))
		redirectexit('action=recent');
	elseif ($full_topic)
		redirectexit('board=' . $board . '.0');
	else
		redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// So long as you are sure... all old posts will be gone.
function RemoveOldTopics2()
{
	global $db_prefix, $modSettings;

	isAllowedTo('admin_forum');
	checkSession('post', 'maintain');

	// No boards at all?  Forget it then :/.
	if (empty($_POST['boards']))
		redirectexit('action=maintain');

	// This should exist, but we can make sure.
	$_POST['delete_type'] = isset($_POST['delete_type']) ? $_POST['delete_type'] : 'nothing';

	// Custom conditions.
	$condition = '';

	// Just moved notice topics?
	if ($_POST['delete_type'] == 'moved')
		$condition .= '
			AND m.icon = \'moved\'
			AND t.locked = 1';
	// Otherwise, maybe locked topics only?
	elseif ($_POST['delete_type'] == 'locked')
		$condition .= '
			AND t.locked = 1';

	// Exclude stickies?
	if (isset($_POST['delete_old_not_sticky']))
		$condition .= '
			AND t.isSticky = 0';

	// All we're gonna do here is grab the ID_TOPICs and send them to removeTopics().
	$request = db_query("
		SELECT t.ID_TOPIC
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE m.ID_MSG = t.ID_LAST_MSG
			AND m.posterTime < " . (time() - 3600 * 24 * $_POST['maxdays']) . "$condition
			AND t.ID_BOARD IN (" . implode(', ', array_keys($_POST['boards'])) . ')', __FILE__, __LINE__);
	$topics = array();
	while ($row = mysql_fetch_assoc($request))
		$topics[] = $row['ID_TOPIC'];
	mysql_free_result($request);

	removeTopics($topics, false, true);

	// Log an action into the moderation log.
	logAction('pruned', array('days' => $_POST['maxdays']));

	redirectexit('action=maintain;done');
}

// Removes the passed ID_TOPICs. (permissions are NOT checked here!)
function removeTopics($topics, $decreasePostCount = true, $ignoreRecycling = false)
{
	global $db_prefix, $sourcedir, $modSettings;

	// Nothing to do?
	if (empty($topics))
		return;
	// Only a single topic.
	elseif (is_numeric($topics))
	{
		$condition = '= ' . $topics;
		$topics = array($topics);
	}
	elseif (count($topics) == 1)
		$condition = '= ' . $topics[0];
	// More than one topic.
	else
		$condition = 'IN (' . implode(', ', $topics) . ')';

	// Decrease the post counts.
	if ($decreasePostCount)
	{
		$requestMembers = db_query("
			SELECT m.ID_MEMBER, COUNT(*) AS posts
			FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
			WHERE m.ID_TOPIC $condition
				AND b.ID_BOARD = m.ID_BOARD
				AND m.icon != 'recycled'
				AND b.countPosts = 0
			GROUP BY m.ID_MEMBER", __FILE__, __LINE__);
		if (mysql_num_rows($requestMembers) > 0)
		{
			while ($rowMembers = mysql_fetch_assoc($requestMembers))
				updateMemberData($rowMembers['ID_MEMBER'], array('posts' => 'posts - ' . $rowMembers['posts']));
		}
		mysql_free_result($requestMembers);
	}

	// Recycle topics that aren't in the recycle board...
	if (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 && !$ignoreRecycling)
	{
		$request = db_query("
			SELECT ID_TOPIC
			FROM {$db_prefix}topics
			WHERE ID_TOPIC $condition
				AND ID_BOARD != $modSettings[recycle_board]
			LIMIT " . count($topics), __FILE__, __LINE__);
		if (mysql_num_rows($request) > 0)
		{
			// Get topics that will be recycled.
			$recycleTopics = array();
			while ($row = mysql_fetch_assoc($request))
				$recycleTopics[] = $row['ID_TOPIC'];
			mysql_free_result($request);

			// Mark recycled topics as recycled.
			db_query("
				UPDATE {$db_prefix}messages
				SET icon = 'recycled'
				WHERE ID_TOPIC IN (" . implode(', ', $recycleTopics) . ")", __FILE__, __LINE__);

			// De-sticky and unlock topics.
			db_query("
				UPDATE {$db_prefix}topics
				SET 
					locked = 0,
					isSticky = 0
				WHERE ID_TOPIC IN (" . implode(', ', $recycleTopics) . ")", __FILE__, __LINE__);

			// Move the topics to the recycle board.
			require_once($sourcedir . '/MoveTopic.php');
			moveTopics($recycleTopics, $modSettings['recycle_board']);

			// Topics that were recycled don't need to be deleted, so subtract them.
			$topics = array_diff($topics, $recycleTopics);

			// Topic list has changed, so does the condition to select topics.
			$condition = 'IN (' . implode(', ', $topics) . ')';
		}
		else
			mysql_free_result($request);
	}

	// Still topics left to delete?
	if (empty($topics))
		return;

	$adjustBoards = array();

	// Find out how many posts we are deleting.
	$request = db_query("
		SELECT ID_BOARD, COUNT(*) AS numTopics, SUM(numReplies) AS numReplies
		FROM {$db_prefix}topics
		WHERE ID_TOPIC $condition
		GROUP BY ID_BOARD", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		// The numReplies is only the *replies*.  There're also the first posts in the topics.
		$adjustBoards[] = array(
			'numPosts' => $row['numReplies'] + $row['numTopics'],
			'numTopics' => $row['numTopics'],
			'ID_BOARD' => $row['ID_BOARD']
		);
	}
	mysql_free_result($request);

	// Decrease the posts/topics...
	foreach ($adjustBoards as $stats)
		db_query("
			UPDATE {$db_prefix}boards
			SET 
				numTopics = IF($stats[numTopics] > numTopics, 0, numTopics - $stats[numTopics]),
				numPosts = IF ($stats[numPosts] > numPosts, 0, numPosts - $stats[numPosts])
			WHERE ID_BOARD = $stats[ID_BOARD]
			LIMIT 1", __FILE__, __LINE__);

	// Remove Polls.
	$request = db_query("
		SELECT ID_POLL
		FROM {$db_prefix}topics
		WHERE ID_TOPIC $condition
			AND ID_POLL > 0
		LIMIT " . count($topics), __FILE__, __LINE__);
	$polls = array();
	while ($row = mysql_fetch_assoc($request))
		$polls[] = $row['ID_POLL'];
	mysql_free_result($request);

	if (!empty($polls))
	{
		$pollCondition = count($polls) == 1 ? '= ' . $polls[0] : 'IN (' . implode(', ', $polls) . ')';

		db_query("
			DELETE FROM {$db_prefix}polls
			WHERE ID_POLL $pollCondition
			LIMIT " . count($polls), __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}poll_choices
			WHERE ID_POLL $pollCondition", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}log_polls
			WHERE ID_POLL $pollCondition", __FILE__, __LINE__);
	}

	// Get rid of the attachment, if it exists.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments('a.attachmentType = 0 AND m.ID_TOPIC ' . $condition, 'messages');

	// Delete possible search index entries.
	if (!empty($modSettings['search_custom_index_config']))
	{
		$customIndexSettings = unserialize($modSettings['search_custom_index_config']);

		$words = array();
		$messages = array();
		$request = db_query("
			SELECT ID_MSG, body
			FROM {$db_prefix}messages
			WHERE ID_TOPIC $condition", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			$words = array_merge($words, text2words($row['body'], $customIndexSettings['bytes_per_word'], true));
			$messages[] = $row['ID_MSG'];
		}
		mysql_free_result($request);
		$words = array_unique($words);

		if (!empty($words) && !empty($messages))		
			db_query("
				DELETE FROM {$db_prefix}log_search_words
				WHERE ID_WORD IN (" . implode(', ', $words) . ")
					AND ID_MSG IN (" . implode(', ', $messages) . ')', __FILE__, __LINE__);
	}

	// Delete anything related to the topic.
	db_query("
		DELETE FROM {$db_prefix}messages
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}calendar
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_topics
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_notify
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}topics
		WHERE ID_TOPIC $condition
		LIMIT " . count($topics), __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_search_subjects
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);

	// Update the totals...
	updateStats('message');
	updateStats('topic');
	updateStats('calendar');

	require_once($sourcedir . '/Subs-Post.php');
	$updates = array();
	foreach ($adjustBoards as $stats)
		$updates[] = $stats['ID_BOARD'];
	updateLastMessages($updates);
}

// Remove a specific message (including permission checks).
function removeMessage($message, $decreasePostCount = true)
{
	global $db_prefix, $board, $sourcedir, $modSettings, $ID_MEMBER, $user_info;

	if (empty($message) || !is_numeric($message))
		return false;

	$request = db_query("
		SELECT
			m.ID_MEMBER, m.icon, m.posterTime, m.subject," . (empty($modSettings['search_custom_index_config']) ? '' : ' m.body,') . "
			t.ID_TOPIC, t.ID_FIRST_MSG, t.ID_LAST_MSG, t.numReplies, t.ID_BOARD,
			t.ID_MEMBER_STARTED AS ID_MEMBER_POSTER,
			b.countPosts
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE m.ID_MSG = $message
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		return false;
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	if (empty($board) || $row['ID_BOARD'] != $board)
	{
		$delete_any = boardsAllowedTo('delete_any');

		if (!in_array(0, $delete_any) && !in_array($row['ID_BOARD'], $delete_any))
		{
			$delete_own = boardsAllowedTo('delete_own');
			$delete_own = in_array(0, $delete_own) || in_array($row['ID_BOARD'], $delete_own);
			$delete_replies = boardsAllowedTo('delete_replies');
			$delete_replies = in_array(0, $delete_replies) || in_array($row['ID_BOARD'], $delete_replies);

			if ($row['ID_MEMBER'] == $ID_MEMBER)
			{
				if (!$delete_own)
				{
					if ($row['ID_MEMBER_POSTER'] == $ID_MEMBER)
					{
						if (!$delete_replies)
							fatal_lang_error('cannot_delete_replies');
					}
					else
						fatal_lang_error('cannot_delete_own');
				}
				elseif (($row['ID_MEMBER_POSTER'] != $ID_MEMBER || !$delete_replies) && !empty($modSettings['edit_disable_time']) && $row['posterTime'] + $modSettings['edit_disable_time'] * 60 < time())
					fatal_lang_error('modify_post_time_passed', false);
			}
			elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER)
			{
				if (!$delete_replies)
					fatal_lang_error('cannot_delete_replies');
			}
			else
				fatal_lang_error('cannot_delete_any');
		}
	}
	else
	{
		// Check permissions to delete this message.
		if ($row['ID_MEMBER'] == $ID_MEMBER)
		{
			if (!allowedTo('delete_own'))
			{
				if ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('delete_any'))
					isAllowedTo('delete_replies');
				elseif (!allowedTo('delete_any'))
					isAllowedTo('delete_own');
			}
			elseif (!allowedTo('delete_any') && ($row['ID_MEMBER_POSTER'] != $ID_MEMBER || !allowedTo('delete_replies')) && !empty($modSettings['edit_disable_time']) && $row['posterTime'] + $modSettings['edit_disable_time'] * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
		}
		elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('delete_any'))
			isAllowedTo('delete_replies');
		else
			isAllowedTo('delete_any');
	}

	// Delete the *whole* topic, but only if the topic consists of one message.
	if ($row['ID_FIRST_MSG'] == $message)
	{
		if (empty($board) || $row['ID_BOARD'] != $board)
		{
			$remove_any = boardsAllowedTo('remove_any');
			$remove_any = in_array(0, $remove_any) || in_array($row['ID_BOARD'], $remove_any);
			if (!$remove_any)
			{
				$remove_own = boardsAllowedTo('remove_own');
				$remove_own = in_array(0, $remove_own) || in_array($row['ID_BOARD'], $remove_own);
			}

			if ($row['ID_MEMBER'] != $ID_MEMBER && !$remove_any)
				fatal_lang_error('cannot_remove_any');
			elseif (!$remove_any && !$remove_own)
				fatal_lang_error('cannot_remove_own');
		}
		else
		{
			// Check permissions to delete a whole topic.
			if ($row['ID_MEMBER'] != $ID_MEMBER)
				isAllowedTo('remove_any');
			elseif (!allowedTo('remove_any'))
				isAllowedTo('remove_own');
		}

		// ...if there is only one post.
		if (!empty($row['numReplies']))
			fatal_lang_error('delFirstPost', false);

		removeTopics($row['ID_TOPIC']);
		return true;
	}

	// Default recycle to false.
	$recycle = false;

	// If recycle topics has been set, make a copy of this message in the recycle board.
	// Make sure we're not recycling messages that are already on the recycle board.
	if (!empty($modSettings['recycle_enable']) && $row['ID_BOARD'] != $modSettings['recycle_board'] && $row['icon'] != 'recycled')
	{
		// Check if the recycle board exists and if so get the read status.
		$request = db_query("
			SELECT (IFNULL(lb.ID_MSG, 0) >= b.ID_MSG_UPDATED) AS isSeen
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
			WHERE b.ID_BOARD = $modSettings[recycle_board]", __FILE__, __LINE__);
		if (mysql_num_rows($request) == 0)
			fatal_lang_error('recycle_no_valid_board');
		list ($isRead) = mysql_fetch_row($request);
		mysql_free_result($request);

		// Insert a new topic in the recycle board.
		db_query("
			INSERT INTO {$db_prefix}topics
				(ID_BOARD, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_FIRST_MSG, ID_LAST_MSG)
			VALUES ($modSettings[recycle_board], $row[ID_MEMBER], $row[ID_MEMBER], $message, $message)", __FILE__, __LINE__);

		// Capture the ID of the new topic...
		$topicID = db_insert_id();

		// If the topic creation went successful, move the message.
		if ($topicID > 0)
		{
			db_query("
				UPDATE {$db_prefix}messages
				SET 
					ID_TOPIC = $topicID,
					ID_BOARD = $modSettings[recycle_board],
					icon = 'recycled'
				WHERE ID_MSG = $message
				LIMIT 1", __FILE__, __LINE__);

			// Mark recycled topic as read.
			if (!$user_info['is_guest'])
				db_query("
					REPLACE INTO {$db_prefix}log_topics
						(ID_TOPIC, ID_MEMBER, ID_MSG)
					VALUES ($topicID, $ID_MEMBER, $modSettings[maxMsgID])", __FILE__, __LINE__);

			// Mark recycle board as seen, if it was marked as seen before.
			if (!empty($isRead) && !$user_info['is_guest'])
				db_query("
					REPLACE INTO {$db_prefix}log_boards
						(ID_BOARD, ID_MEMBER, ID_MSG)
					VALUES ($modSettings[recycle_board], $ID_MEMBER, $modSettings[maxMsgID])", __FILE__, __LINE__);

			// Add one topic and post to the recycle bin board.
			db_query("
				UPDATE {$db_prefix}boards
				SET
					numTopics = numTopics + 1,
					numPosts = numPosts + 1
				WHERE ID_BOARD = $modSettings[recycle_board]
				LIMIT 1", __FILE__, __LINE__);

			// Make sure this message isn't getting deleted later on.
			$recycle = true;

			// Make sure we update the search subject index.
			updateStats('subject', $topicID, $row['subject']);
		}
	}

	// Deleting a recycled message can not lower anyone's post count.
	if ($row['icon'] == 'recycled')
		$decreasePostCount = false;

	// This is the last post, update the last post on the board.
	if ($row['ID_LAST_MSG'] == $message)
	{
		// Find the last message, set it, and decrease the post count.
		$request = db_query("
			SELECT ID_MSG, ID_MEMBER
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = $row[ID_TOPIC]
				AND ID_MSG != $message
			ORDER BY ID_MSG DESC
			LIMIT 1", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($request);
		mysql_free_result($request);

		db_query("
			UPDATE {$db_prefix}topics
			SET
				ID_LAST_MSG = $row2[ID_MSG],
				numReplies = IF(numReplies = 0, 0, numReplies - 1),
				ID_MEMBER_UPDATED = $row2[ID_MEMBER]
			WHERE ID_TOPIC = $row[ID_TOPIC]
			LIMIT 1", __FILE__, __LINE__);
	}
	// Only decrease post counts.
	else
		db_query("
			UPDATE {$db_prefix}topics
			SET numReplies = IF(numReplies = 0, 0, numReplies - 1)
			WHERE ID_TOPIC = $row[ID_TOPIC]
			LIMIT 1", __FILE__, __LINE__);

	db_query("
		UPDATE {$db_prefix}boards
		SET numPosts = IF(numPosts = 0, 0, numPosts - 1)
		WHERE ID_BOARD = $row[ID_BOARD]
		LIMIT 1", __FILE__, __LINE__);

	// If the poster was registered and the board this message was on incremented
	// the member's posts when it was posted, decrease his or her post count.
	if (!empty($row['ID_MEMBER']) && $decreasePostCount && empty($row['countPosts']))
		updateMemberData($row['ID_MEMBER'], array('posts' => '-'));

	// Only remove posts if they're not recycled.
	if (!$recycle)
	{
		// Remove the message!
		db_query("
			DELETE FROM {$db_prefix}messages
			WHERE ID_MSG = $message
			LIMIT 1", __FILE__, __LINE__);

		if (!empty($modSettings['search_custom_index_config']))
		{
			$customIndexSettings = unserialize($modSettings['search_custom_index_config']);
			$words = text2words($row['body'], $customIndexSettings['bytes_per_word'], true);
			if (!empty($words))
				db_query("
					DELETE FROM {$db_prefix}log_search_words
					WHERE ID_WORD IN (" . implode(', ', $words) . ")
						AND ID_MSG = $message", __FILE__, __LINE__);
		}

		// Delete attachment(s) if they exist.
		require_once($sourcedir . '/ManageAttachments.php');
		removeAttachments('a.attachmentType = 0 AND a.ID_MSG = ' . $message);
	}

	// Update the pesky statistics.
	updateStats('message');
	updateStats('topic');
	updateStats('calendar');

	// And now to update the last message of each board we messed with.
	require_once($sourcedir . '/Subs-Post.php');
	if ($recycle)
		updateLastMessages(array($row['ID_BOARD'], $modSettings['recycle_board']));
	else
		updateLastMessages($row['ID_BOARD']);

	return false;
}

?>