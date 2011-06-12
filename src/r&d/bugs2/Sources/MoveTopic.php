<?php
/**********************************************************************************
* MoveTopic.php                                                                   *
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

/*	This file contains the functions required to move topics from one board to
	another board.

	void MoveTopic()
		- is called to allow moderator to give reason for topic move.
		- must be called with a topic specified.
		- uses the MoveTopic template and main sub template.
		- if the member is the topic starter requires the move_own permission,
		  otherwise the move_any permission.
		- is accessed via ?action=movetopic.

	void MoveTopic2()
		- is called on the submit of MoveTopic.
		- requires the use of the Subs-Post.php file.
		- logs that topics have been moved in the moderation log.
		- if the member is the topic starter requires the move_own permission,
		  otherwise requires the move_any permission.
		- upon successful completion redirects to message index.
		- is accessed via ?action=movetopic2.

	void moveTopics(array topics, int destination_board)
		- performs the changes needed to move topics to new boards.
		- topics is an array of the topics to move, and destination_board is
		  where they should be moved to.
		- updates message, topic and calendar statistics.
		- does not check permissions. (assumes they have been checked!)
*/

// Move a topic.  Give the moderator a chance to post a reason.
function MoveTopic()
{
	global $txt, $board, $topic, $db_prefix, $user_info, $context, $ID_MEMBER, $language;

	if (empty($topic))
		fatal_lang_error(1);

	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, ms.subject
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS ms)
		WHERE t.ID_TOPIC = $topic
			AND ms.ID_MSG = t.ID_FIRST_MSG
		LIMIT 1", __FILE__, __LINE__);
	list ($ID_MEMBER_STARTED, $context['subject']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Permission check!
	// !!!
	if (!allowedTo('move_any'))
	{
		if ($ID_MEMBER_STARTED == $ID_MEMBER)
		{
			isAllowedTo('move_own');
			//$boards = array_merge(boardsAllowedTo('move_own'), boardsAllowedTo('move_any'));
		}
		else
			isAllowedTo('move_any');
	}
	//else
		//$boards = boardsAllowedTo('move_any');

	loadTemplate('MoveTopic');

	// Get a list of boards this moderator can move to.
	$request = db_query("
		SELECT b.ID_BOARD, b.name, b.childLevel, c.name AS catName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE b.ID_BOARD != $board
			AND $user_info[query_see_board]" /* . (!in_array(0, $boards) ? "
			AND b.ID_BOARD IN (" . implode(', ', $boards) . ")" : '') .*/, __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'category' => $row['catName'],
			'child_level' => $row['childLevel'],
			'selected' => !empty($_SESSION['move_to_topic']) && $_SESSION['move_to_topic'] == $row['ID_BOARD']
		);
	mysql_free_result($request);

	if (empty($context['boards']))
		fatal_lang_error('moveto_noboards', false);

	$context['page_title'] = $txt[132];

	$context['back_to_topic'] = isset($_REQUEST['goback']);

	if ($user_info['language'] != $language)
	{
		loadLanguage('index', $language);
		$temp = $txt['movetopic_default'];
		loadLanguage('index');

		$txt['movetopic_default'] = $temp;
	}

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

// Exceute the move.
function MoveTopic2()
{
	global $txt, $board, $topic, $scripturl, $sourcedir, $modSettings, $context;
	global $db_prefix, $ID_MEMBER, $board, $language, $user_info, $func;

	// Make sure this form hasn't been submitted before.
	checkSubmitOnce('check');

	$request = db_query("
		SELECT ID_MEMBER_STARTED, ID_FIRST_MSG
		FROM {$db_prefix}topics
		WHERE ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	list ($ID_MEMBER_STARTED, $ID_FIRST_MSG) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Can they move topics on this board?
	if (!allowedTo('move_any'))
	{
		if ($ID_MEMBER_STARTED == $ID_MEMBER)
		{
			isAllowedTo('move_own');
			$boards = array_merge(boardsAllowedTo('move_own'), boardsAllowedTo('move_any'));
		}
		else
			isAllowedTo('move_any');
	}
	else
		$boards = boardsAllowedTo('move_any');

	checkSession();
	require_once($sourcedir . '/Subs-Post.php');

	// The destination board must be numeric.
	$_POST['toboard'] = (int) $_POST['toboard'];

	// !!!
	/*if (!in_array($_POST['toboard'], $boards) && !in_array(0, $boards))
		fatal_lang_error('smf232');*/

	// Make sure they can see the board they are trying to move to (and get whether posts count in the target board).
	$request = db_query("
		SELECT b.countPosts, b.name, m.subject
		FROM ({$db_prefix}boards AS b, {$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE $user_info[query_see_board]
			AND b.ID_BOARD = $_POST[toboard]
			AND t.ID_TOPIC = $topic
			AND m.ID_MSG = t.ID_FIRST_MSG
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
	list ($pcounter, $board_name, $subject) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Remember this for later.
	$_SESSION['move_to_topic'] = $_POST['toboard'];

	// Rename the topic...
	if (isset($_POST['reset_subject'], $_POST['custom_subject']) && $_POST['custom_subject'] != '')
	{
		$_POST['custom_subject'] = $func['htmlspecialchars']($_POST['custom_subject']);

		if (isset($_POST['enforce_subject']))
		{
			// Get a response prefix, but in the forum's default language.
			if (!isset($context['response_prefix']) && !($context['response_prefix'] = cache_get_data('response_prefix')))
			{
				if ($language === $user_info['language'])
					$context['response_prefix'] = $txt['response_prefix'];
				else
				{
					loadLanguage('index', $language, false);
					$context['response_prefix'] = $txt['response_prefix'];
					loadLanguage('index');
				}
				cache_put_data('response_prefix', $context['response_prefix'], 600);
			}

			db_query("
				UPDATE {$db_prefix}messages
				SET subject = '$context[response_prefix]$_POST[custom_subject]'
				WHERE ID_TOPIC = $topic", __FILE__, __LINE__);
		}

		db_query("
			UPDATE {$db_prefix}messages
			SET subject = '$_POST[custom_subject]'
			WHERE ID_MSG = $ID_FIRST_MSG
			LIMIT 1", __FILE__, __LINE__);

		// Fix the subject cache.
		updateStats('subject', $topic, $_POST['custom_subject']);
	}

	// Create a link to this in the old board.
	if (isset($_POST['postRedirect']))
	{
		// Should be in the boardwide language.
		if ($user_info['language'] != $language)
			loadLanguage('index', $language);

		$_POST['reason'] = $func['htmlspecialchars']($_POST['reason'], ENT_QUOTES);
		preparsecode($_POST['reason']);

		// Add a URL onto the message.
		$_POST['reason'] = strtr($_POST['reason'], array(
			$txt['movetopic_auto_board'] => '[url=' . $scripturl . '?board=' . $_POST['toboard'] . ']' . addslashes($board_name) . '[/url]',
			$txt['movetopic_auto_topic'] => '[iurl]' . $scripturl . '?topic=' . $topic . '.0[/iurl]'
		));

		$msgOptions = array(
			'subject' => addslashes($txt['smf56'] . ': ' . $subject),
			'body' => $_POST['reason'],
			'icon' => 'moved',
			'smileys_enabled' => 1,
		);
		$topicOptions = array(
			'board' => $board,
			'lock_mode' => 1,
			'mark_as_read' => true,
		);
		$posterOptions = array(
			'id' => $ID_MEMBER,
			'update_post_count' => !empty($pcounter),
		);
		createPost($msgOptions, $topicOptions, $posterOptions);
	}

	$request = db_query("
		SELECT countPosts
		FROM {$db_prefix}boards
		WHERE ID_BOARD = $board
		LIMIT 1", __FILE__, __LINE__);
	list ($pcounter_from) = mysql_fetch_row($request);
	mysql_free_result($request);

	if ($pcounter_from != $pcounter)
	{
		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = $topic", __FILE__, __LINE__);
		$posters = array();
		while ($row = mysql_fetch_assoc($request))
			$posters[] = $row['ID_MEMBER'];
		mysql_free_result($request);

		// The board we're moving from counted posts, but not to.
		if (empty($pcounter_from))
			updateMemberData($posters, array('posts' => '-'));
		// The reverse: from didn't, to did.
		else
			updateMemberData($posters, array('posts' => '+'));
	}

	// Do the move (includes statistics update needed for the redirect topic).
	moveTopics($topic, $_POST['toboard']);

	// Log that they moved this topic.
	if (!allowedTo('move_own') || $ID_MEMBER_STARTED != $ID_MEMBER)
		logAction('move', array('topic' => $topic, 'board_from' => $board, 'board_to' => $_POST['toboard']));
	// Notify people that this topic has been moved?
	sendNotifications($topic, 'move');

	// Update the cache?
	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] == 3)
		cache_put_data('topic_board-' . $topic, null, 120);

	// Why not go back to the original board in case they want to keep moving?
	if (!isset($_REQUEST['goback']))
		redirectexit('board=' . $board . '.0');
	else
		redirectexit('topic=' . $topic . '.0');
}

// Moves one or more topics to a specific board. (doesn't check permissions.)
function moveTopics($topics, $toBoard)
{
	global $db_prefix, $sourcedir, $ID_MEMBER, $user_info, $modSettings;

	// Empty array?
	if (empty($topics))
		return;
	// Only a single topic.
	elseif (is_numeric($topics))
		$condition = '= ' . $topics;
	elseif (count($topics) == 1)
		$condition = '= ' . $topics[0];
	// More than one topic.
	else
		$condition = 'IN (' . implode(', ', $topics) . ')';
	$numTopics = count($topics);
	$fromBoards = array();

	// Destination board empty or equal to 0?
	if (empty($toBoard))
		return;

	// Determine the source boards...
	$request = db_query("
		SELECT ID_BOARD, COUNT(*) AS numTopics, SUM(numReplies) AS numReplies
		FROM {$db_prefix}topics
		WHERE ID_TOPIC $condition
		GROUP BY ID_BOARD", __FILE__, __LINE__);
	// Num of rows = 0 -> no topics found. Num of rows > 1 -> topics are on multiple boards.
	if (mysql_num_rows($request) == 0)
		return;
	while ($row = mysql_fetch_assoc($request))
	{
		// Posts = (numReplies + 1) for each topic.
		$fromBoards[$row['ID_BOARD']] = array(
			'numPosts' => $row['numReplies'] + $row['numTopics'],
			'numTopics' => $row['numTopics'],
			'ID_BOARD' => $row['ID_BOARD']
		);
	}
	mysql_free_result($request);

	// Move over the mark_read data. (because it may be read and now not by some!)
	$SaveAServer = max(0, $modSettings['maxMsgID'] - 50000);
	$request = db_query("
		SELECT lmr.ID_MEMBER, lmr.ID_MSG, t.ID_TOPIC
		FROM ({$db_prefix}topics AS t, {$db_prefix}log_mark_read AS lmr)
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = lmr.ID_MEMBER)
		WHERE t.ID_TOPIC $condition
			AND lmr.ID_BOARD = t.ID_BOARD
			AND lmr.ID_MSG > t.ID_FIRST_MSG
			AND lmr.ID_MSG > $SaveAServer
			AND lmr.ID_MSG > IFNULL(lt.ID_MSG, 0)", __FILE__, __LINE__);
	$log_topics = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$log_topics[] = '(' . $row['ID_TOPIC'] . ', ' . $row['ID_MEMBER'] . ', ' . $row['ID_MSG'] . ')';

		// Prevent queries from getting too big. Taking some steam off.
		if (count($log_topics) > 500)
		{
			db_query("
				REPLACE INTO {$db_prefix}log_topics
					(ID_TOPIC, ID_MEMBER, ID_MSG)
				VALUES " . implode(',
					', $log_topics), __FILE__, __LINE__);
			$log_topics = array();
		}
	}
	mysql_free_result($request);

	// Now that we have all the topics that *should* be marked read, and by which members...
	if (!empty($log_topics))
	{
		// Insert that information into the database!
		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_TOPIC, ID_MEMBER, ID_MSG)
			VALUES " . implode(',
				', $log_topics), __FILE__, __LINE__);
	}

	// Update the number of posts on each board.
	$totalTopics = 0;
	$totalPosts = 0;
	foreach ($fromBoards as $stats)
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET 
				numPosts = IF($stats[numPosts] > numPosts, 0, numPosts - $stats[numPosts]),
				numTopics = IF($stats[numTopics] > numTopics, 0, numTopics - $stats[numTopics])
			WHERE ID_BOARD = $stats[ID_BOARD]
			LIMIT 1", __FILE__, __LINE__);
		$totalTopics += $stats['numTopics'];
		$totalPosts += $stats['numPosts'];
	}
	db_query("
		UPDATE {$db_prefix}boards
		SET 
			numTopics = numTopics + $totalTopics, 
			numPosts = numPosts + $totalPosts
		WHERE ID_BOARD = $toBoard
		LIMIT 1", __FILE__, __LINE__);

	// Move the topic.  Done.  :P
	db_query("
		UPDATE {$db_prefix}topics
		SET ID_BOARD = $toBoard
		WHERE ID_TOPIC $condition
		LIMIT $numTopics", __FILE__, __LINE__);
	db_query("
		UPDATE {$db_prefix}messages
		SET ID_BOARD = $toBoard
		WHERE ID_TOPIC $condition", __FILE__, __LINE__);
	db_query("
		UPDATE {$db_prefix}calendar
		SET ID_BOARD = $toBoard
		WHERE ID_TOPIC $condition
		LIMIT $numTopics", __FILE__, __LINE__);

	// Mark target board as seen, if it was already marked as seen before.
	$request = db_query("
		SELECT (IFNULL(lb.ID_MSG, 0) >= b.ID_MSG_UPDATED) AS isSeen
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
		WHERE b.ID_BOARD = $toBoard", __FILE__, __LINE__);
	list ($isSeen) = mysql_fetch_row($request);
	mysql_free_result($request);

	if (!empty($isSeen) && !$user_info['is_guest'])
	{
		db_query("
			REPLACE INTO {$db_prefix}log_boards
				(ID_BOARD, ID_MEMBER, ID_MSG)
			VALUES ($toBoard, $ID_MEMBER, $modSettings[maxMsgID])", __FILE__, __LINE__);
	}

	// Update 'em pesky stats.
	updateStats('topic');
	updateStats('message');
	updateStats('calendar');

	require_once($sourcedir . '/Subs-Post.php');

	$updates = array_keys($fromBoards);
	$updates[] = $toBoard;

	updateLastMessages(array_unique($updates));
}

?>