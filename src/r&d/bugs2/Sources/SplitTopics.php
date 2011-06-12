<?php
/**********************************************************************************
* SplitTopics.php                                                                 *
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
***********************************************************************************
* Original module by Mach8 - We'll never forget you.                              *
***********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file handles merging and splitting topics... it does this with:

	void SplitTopics()
		- splits a topic into two topics.
		- delegates to the other functions (based on the URL parameter 'sa').
		- loads the SplitTopics template.
		- requires the split_any permission.
		- is accessed with ?action=splittopics.

	void SplitIndex()
		- screen shown before the actual split.
		- is accessed with ?action=splittopics;sa=index.
		- default sub action for ?action=splittopics.
		- uses 'ask' sub template of the SplitTopics template.
		- redirects to SplitSelectTopics if the message given turns out to be
		  the first message of a topic.
		- shows the user three ways to split the current topic.

	void SplitExecute()
		- do the actual split.
		- is accessed with ?action=splittopics;sa=execute.
		- uses the main SplitTopics template.
		- supports three ways of splitting:
		   (1) only one message is split off.
		   (2) all messages after and including a given message are split off.
		   (3) select topics to split (redirects to SplitSelectTopics()).
		- uses splitTopic function to do the actual splitting.

	void SplitSelectTopics()
		- allows the user to select the messages to be split.
		- is accessed with ?action=splittopics;sa=selectTopics.
		- uses 'select' sub template of the SplitTopics template or (for
		  XMLhttp) the 'split' sub template of the Xml template.
		- supports XMLhttp for adding/removing a message to the selection.
		- uses a session variable to store the selected topics.
		- shows two independent page indexes for both the selected and
		  not-selected messages (;topic=1.x;start2=y).

	void SplitSelectionExecute()
		- do the actual split of a selection of topics.
		- is accessed with ?action=splittopics;sa=splitSelection.
		- uses the main SplitTopics template.
		- uses splitTopic function to do the actual splitting.

	int splitTopic(int topicID, array messagesToBeSplit, string newSubject)
		- general function to split off a topic.
		- creates a new topic and moves the messages with the IDs in
		  array messagesToBeSplit to the new topic.
		- the subject of the newly created topic is set to 'newSubject'.
		- marks the newly created message as read for the user splitting it.
		- updates the statistics to reflect a newly created topic.
		- logs the action in the moderation log.
		- a notification is sent to all users monitoring this topic.
		- returns the topic ID of the new split topic.

	void MergeTopics()
		- merges two or more topics into one topic.
		- delegates to the other functions (based on the URL parameter sa).
		- loads the SplitTopics template.
		- requires the merge_any permission.
		- is accessed with ?action=mergetopics.

	void MergeIndex()
		- allows to pick a topic to merge the current topic with.
		- is accessed with ?action=mergetopics;sa=index
		- default sub action for ?action=mergetopics.
		- uses 'merge' sub template of the SplitTopics template.
		- allows to set a different target board.

	void MergeExecute(array topics = request)
		- set merge options and do the actual merge of two or more topics.
		- the merge options screen:
			- shows topics to be merged and allows to set some merge options.
			- is accessed by ?action=mergetopics;sa=options.and can also
			  internally be called by QuickModeration() (Subs-Boards.php).
			- uses 'merge_extra_options' sub template of the SplitTopics
			  template.
		- the actual merge:
			- is accessed with ?action=mergetopics;sa=execute.
			- updates the statistics to reflect the merge.
			- logs the action in the moderation log.
			- sends a notification is sent to all users monitoring this topic.
			- redirects to ?action=mergetopics;sa=done.

	void MergeDone()
		- shows a 'merge completed' screen.
		- is accessed with ?action=mergetopics;sa=done.
		- uses 'merge_done' sub template of the SplitTopics template.
*/

// Split a topic into two separate topics... in case it got offtopic, etc.
function SplitTopics()
{
	global $topic, $sourcedir;

	// And... which topic were you splitting, again?
	if (empty($topic))
		fatal_lang_error(337, false);

	// Are you allowed to split topics?
	isAllowedTo('split_any');

	// Load up the "dependencies" - the template, getMsgMemberID(), and sendNotifications().
	if (!isset($_REQUEST['xml']))
		loadTemplate('SplitTopics');
	require_once($sourcedir . '/Subs-Boards.php');
	require_once($sourcedir . '/Subs-Post.php');

	$subActions = array(
		'selectTopics' => 'SplitSelectTopics',
		'execute' => 'SplitExecute',
		'index' => 'SplitIndex',
		'splitSelection' => 'SplitSelectionExecute',
	);

	// ?action=splittopics;sa=LETSBREAKIT won't work, sorry.
	if (empty($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
		SplitIndex();
	else
		$subActions[$_REQUEST['sa']]();
}

// Part 1: General stuff.
function SplitIndex()
{
	global $txt, $topic, $db_prefix, $context;

	// Validate "at".
	if (empty($_GET['at']))
		fatal_lang_error(337, false);
	$_GET['at'] = (int) $_GET['at'];

	// Retrieve the subject and stuff of the specific topic/message.
	$request = db_query("
		SELECT m.subject, t.numReplies, t.ID_FIRST_MSG
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE m.ID_MSG = $_GET[at]
			AND m.ID_TOPIC = $topic
			AND t.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf272');
	list ($_REQUEST['subname'], $numReplies, $ID_FIRST_MSG) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Check if there is more than one message in the topic.  (there should be.)
	if ($numReplies < 1)
		fatal_lang_error('smf270', false);

	// Check if this is the first message in the topic (if so, the first and second option won't be available)
	if ($ID_FIRST_MSG == $_GET['at'])
		return SplitSelectTopics();

	// Basic template information....
	$context['message'] = array(
		'id' => $_GET['at'],
		'subject' => $_REQUEST['subname']
	);
	$context['sub_template'] = 'ask';
	$context['page_title'] = $txt['smf251'];
}

// Alright, you've decided what you want to do with it.... now to do it.
function SplitExecute()
{
	global $txt, $board, $topic, $db_prefix, $context, $ID_MEMBER, $user_info;

	// They blanked the subject name.
	if (!isset($_POST['subname']) || $_POST['subname'] == '')
		$_POST['subname'] = $txt['smf258'];

	// Redirect to the selector if they chose selective.
	if ($_POST['step2'] == 'selective')
	{
		$_REQUEST['subname'] = $_POST['subname'];
		return SplitSelectTopics();
	}

	// Check the session to make sure they meant to do this.
	checkSession();

	$_POST['at'] = (int) $_POST['at'];
	$messagesToBeSplit = array();

	if ($_POST['step2'] == 'afterthis')
	{
		// Fetch the message IDs of the topic that are at or after the message.
		$request = db_query("
			SELECT ID_MSG
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = $topic
				AND ID_MSG >= $_POST[at]", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$messagesToBeSplit[] = $row['ID_MSG'];
		mysql_free_result($request);
	}
	// Only the selected message has to be split. That should be easy.
	elseif ($_POST['step2'] == 'onlythis')
		$messagesToBeSplit[] = $_POST['at'];
	// There's another action?!
	else
		fatal_lang_error(1, false);

	$context['old_topic'] = $topic;
	$context['new_topic'] = splitTopic($topic, $messagesToBeSplit, $_POST['subname']);
	$context['page_title'] = $txt['smf251'];
}

// Get a selective list of topics...
function SplitSelectTopics()
{
	global $txt, $scripturl, $topic, $db_prefix, $context, $modSettings, $original_msgs;

	$context['page_title'] = $txt['smf251'] . ' - ' . $txt['smf257'];

	// Haven't selected anything have we?
	$_SESSION['split_selection'][$topic] = empty($_SESSION['split_selection'][$topic]) ? array() : $_SESSION['split_selection'][$topic];

	$context['not_selected'] = array(
		'num_messages' => 0,
		'start' => empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'],
		'messages' => array(),
	);

	$context['selected'] = array(
		'num_messages' => 0,
		'start' => empty($_REQUEST['start2']) ? 0 : (int) $_REQUEST['start2'],
		'messages' => array(),
	);

	$context['topic'] = array(
		'id' => $topic,
		'subject' => urlencode($_REQUEST['subname']),
	);

	// Some stuff for our favorite template.
	$context['new_subject'] = stripslashes($_REQUEST['subname']);

	// Using the "select" sub template.
	$context['sub_template'] = isset($_REQUEST['xml']) ? 'split' : 'select';

	// Get the message ID's from before the move.
	if (isset($_REQUEST['xml']))
	{
		$original_msgs = array(
			'not_selected' => array(),
			'selected' => array(),
		);
		$request = db_query("
			SELECT ID_MSG
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = $topic" . (empty($_SESSION['split_selection'][$topic]) ? '' : "
				AND ID_MSG NOT IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ')') . "
			ORDER BY ID_MSG DESC
			LIMIT " . $context['not_selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
		// You can't split the last message off.
		if (empty($context['not_selected']['start']) && mysql_num_rows($request) <= 1 && $_REQUEST['move'] == 'down')
			$_REQUEST['move'] = '';
		while ($row = mysql_fetch_assoc($request))
			$original_msgs['not_selected'][] = $row['ID_MSG'];
		mysql_free_result($request);
		if (!empty($_SESSION['split_selection'][$topic]))
		{
			$request = db_query("
				SELECT ID_MSG
				FROM {$db_prefix}messages
				WHERE ID_TOPIC = $topic
					AND ID_MSG IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ")
				ORDER BY ID_MSG DESC
				LIMIT " . $context['selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				$original_msgs['selected'][] = $row['ID_MSG'];
			mysql_free_result($request);
		}
	}

	// (De)select a message..
	if (!empty($_REQUEST['move']))
	{
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		if ($_REQUEST['move'] == 'reset')
			$_SESSION['split_selection'][$topic] = array();
		elseif ($_REQUEST['move'] == 'up')
			$_SESSION['split_selection'][$topic] = array_diff($_SESSION['split_selection'][$topic], array($_REQUEST['msg']));
		else
			$_SESSION['split_selection'][$topic][] = $_REQUEST['msg'];
	}

	// Make sure the selection is still accurate.
	if (!empty($_SESSION['split_selection'][$topic]))
	{
		$request = db_query("
			SELECT ID_MSG
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = $topic
				AND ID_MSG IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ')', __FILE__, __LINE__);
		$_SESSION['split_selection'][$topic] = array();
		while ($row = mysql_fetch_assoc($request))
			$_SESSION['split_selection'][$topic][] = $row['ID_MSG'];
		mysql_free_result($request);
	}

	// Get the number of messages (not) selected to be split.
	$request = db_query("
		SELECT " . (empty($_SESSION['split_selection'][$topic]) ? '0' : 'm.ID_MSG IN (' .implode(', ', $_SESSION['split_selection'][$topic]) . ')') . " AS is_selected, COUNT(*) AS num_messages
		FROM {$db_prefix}messages AS m
		WHERE m.ID_TOPIC = $topic
		GROUP BY is_selected", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context[empty($row['is_selected']) ? 'not_selected' : 'selected']['num_messages'] = $row['num_messages'];
	mysql_free_result($request);

	// Fix an oversized starting page (to make sure both pageindexes are properly set).
	if ($context['selected']['start'] >= $context['selected']['num_messages'])
		$context['selected']['start'] = $context['selected']['num_messages'] <= $modSettings['defaultMaxMessages'] ? 0 : ($context['selected']['num_messages'] - (($context['selected']['num_messages'] % $modSettings['defaultMaxMessages']) == 0 ? $modSettings['defaultMaxMessages'] : ($context['selected']['num_messages'] % $modSettings['defaultMaxMessages'])));

	// Build a page list of the not-selected topics...
	$context['not_selected']['page_index'] = constructPageIndex($scripturl . '?action=splittopics;sa=selectTopics;subname=' . strtr(urlencode($_REQUEST['subname']), array('%' => '%%')) . ';topic=' . $topic . '.%d;start2=' . $context['selected']['start'], $context['not_selected']['start'], $context['not_selected']['num_messages'], $modSettings['defaultMaxMessages'], true);
	// ...and one of the selected topics.
	$context['selected']['page_index'] = constructPageIndex($scripturl . '?action=splittopics;sa=selectTopics;subname=' . strtr(urlencode($_REQUEST['subname']), array('%' => '%%')) . ';topic=' . $topic . '.' . $context['not_selected']['start'] . ';start2=%d', $context['selected']['start'], $context['selected']['num_messages'], $modSettings['defaultMaxMessages'], true);

	// Get the messages and stick them into an array.
	$request = db_query("
		SELECT m.subject, IFNULL(mem.realName, m.posterName) AS realName, m.body, m.ID_MSG, m.smileysEnabled
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_TOPIC = $topic" . (empty($_SESSION['split_selection'][$topic]) ? '' : "
			AND ID_MSG NOT IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ')') . "
		ORDER BY m.ID_MSG DESC
		LIMIT " . $context['not_selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['messages'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		censorText($row['subject']);
		censorText($row['body']);

		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		$context['not_selected']['messages'][$row['ID_MSG']] = array(
			'id' => $row['ID_MSG'],
			'subject' => $row['subject'],
			'body' => $row['body'],
			'poster' => $row['realName'],
		);
	}
	mysql_free_result($request);

	// Now get the selected messages.
	if (!empty($_SESSION['split_selection'][$topic]))
	{
		// Get the messages and stick them into an array.
		$request = db_query("
			SELECT m.subject, IFNULL(mem.realName, m.posterName) AS realName, m.body, m.ID_MSG, m.smileysEnabled
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			WHERE m.ID_TOPIC = $topic
				AND ID_MSG IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ")
			ORDER BY m.ID_MSG DESC
			LIMIT " . $context['selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
		$context['messages'] = array();
		while ($row = mysql_fetch_assoc($request))
		{
			censorText($row['subject']);
			censorText($row['body']);

			$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

			$context['selected']['messages'][$row['ID_MSG']] = array(
				'id' => $row['ID_MSG'],
				'subject' => $row['subject'],
				'body' => $row['body'],
				'poster' => $row['realName']
			);
		}
		mysql_free_result($request);
	}

	// The XMLhttp method only needs the stuff that changed, so let's compare.
	if (isset($_REQUEST['xml']))
	{
		$changes = array(
			'remove' => array(
				'not_selected' => array_diff($original_msgs['not_selected'], array_keys($context['not_selected']['messages'])),
				'selected' => array_diff($original_msgs['selected'], array_keys($context['selected']['messages'])),
			),
			'insert' => array(
				'not_selected' => array_diff(array_keys($context['not_selected']['messages']), $original_msgs['not_selected']),
				'selected' => array_diff(array_keys($context['selected']['messages']), $original_msgs['selected']),
			),
		);

		$context['changes'] = array();
		foreach ($changes as $change_type => $change_array)
			foreach ($change_array as $section => $msg_array)
			{
				if (empty($msg_array))
					continue;

				foreach ($msg_array as $ID_MSG)
				{
					$context['changes'][$change_type . $ID_MSG] = array(
						'id' => $ID_MSG,
						'type' => $change_type,
						'section' => $section,
					);
					if ($change_type == 'insert')
						$context['changes']['insert' . $ID_MSG]['insert_value'] = $context[$section]['messages'][$ID_MSG];
				}
			}
	}
}

// Actually and selectively split the topics out.
function SplitSelectionExecute()
{
	global $txt, $board, $topic, $db_prefix, $context, $ID_MEMBER, $user_info;

	// Make sure the session id was passed with post.
	checkSession();

	// Default the subject in case it's blank.
	if (!isset($_POST['subname']) || $_POST['subname'] == '')
		$_POST['subname'] = $txt['smf258'];

	// The old topic's ID is the current one.
	$split1_ID_TOPIC = $topic;

	// You must've selected some messages!  Can't split out none!
	if (empty($_SESSION['split_selection'][$topic]))
		fatal_lang_error('smf271', false);

	$context['old_topic'] = $topic;
	$context['new_topic'] = splitTopic($topic, $_SESSION['split_selection'][$topic], $_POST['subname']);
	$context['page_title'] = $txt['smf251'];
}

// Split a topic in two topics.
function splitTopic($split1_ID_TOPIC, $splitMessages, $new_subject)
{
	global $db_prefix, $ID_MEMBER, $user_info, $topic, $board, $modSettings;
	global $func;

	// Nothing to split?
	if (empty($splitMessages))
		fatal_lang_error('smf271', false);

	// No sense in imploding it over and over again.
	$postList = implode(',', $splitMessages);

	if ($split1_ID_TOPIC == $topic)
		$ID_BOARD = $board;
	else
	{
		$request = db_query("
			SELECT ID_BOARD
			FROM {$db_prefix}topics
			WHERE ID_TOPIC = $split1_ID_TOPIC
			LIMIT 1", __FILE__, __LINE__);
		list ($ID_BOARD) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	// Find the new first and last not in the list. (old topic)
	$request = db_query("
		SELECT MIN(m.ID_MSG) AS myID_FIRST_MSG, MAX(m.ID_MSG) AS myID_LAST_MSG, COUNT(*) - 1 AS myNumReplies, t.isSticky
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE m.ID_MSG NOT IN ($postList)
			AND m.ID_TOPIC = $split1_ID_TOPIC
			AND t.ID_TOPIC = $split1_ID_TOPIC
		GROUP BY m.ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);
	// You can't select ALL the messages!
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf271b', false);
	list ($split1_firstMsg, $split1_lastMsg, $split1_replies, $isSticky) = mysql_fetch_row($request);
	mysql_free_result($request);
	$split1_firstMem = getMsgMemberID($split1_firstMsg);
	$split1_lastMem = getMsgMemberID($split1_lastMsg);

	// Find the first and last in the list. (new topic)
	$result = db_query("
		SELECT MIN(ID_MSG) AS myID_FIRST_MSG, MAX(ID_MSG) AS myID_LAST_MSG, COUNT(*) - 1 AS myNumReplies
		FROM {$db_prefix}messages
		WHERE ID_MSG IN ($postList)
			AND ID_TOPIC = $split1_ID_TOPIC
		GROUP BY ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);
	list ($split2_firstMsg, $split2_lastMsg, $split2_replies) = mysql_fetch_row($result);
	mysql_free_result($result);
	$split2_firstMem = getMsgMemberID($split2_firstMsg);
	$split2_lastMem = getMsgMemberID($split2_lastMsg);

	// No database changes yet, so let's double check to see if everything makes at least a little sense.
	if ($split1_firstMsg <= 0 || $split1_lastMsg <= 0 || $split2_firstMsg <= 0 || $split2_lastMsg <= 0 || $split1_replies < 0 || $split2_replies < 0)
		fatal_lang_error('smf272');

	// You cannot split of the first message of a topic.
	if ($split1_firstMsg > $split2_firstMsg)
		fatal_lang_error('smf268', false);

	// We're off to insert the new topic!  Use 0 for now to avoid UNIQUE errors.
	db_query("
		INSERT INTO {$db_prefix}topics
			(ID_BOARD, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_FIRST_MSG, ID_LAST_MSG, numReplies, isSticky)
		VALUES ($ID_BOARD, $split2_firstMem, $split2_lastMem, 0, 0, $split2_replies, $isSticky)", __FILE__, __LINE__);
	$split2_ID_TOPIC = db_insert_id();
	if ($split2_ID_TOPIC <= 0)
		fatal_lang_error('smf273');

	// Move the messages over to the other topic.
	$new_subject = $func['htmlspecialchars']($new_subject);
	db_query("
		UPDATE {$db_prefix}messages
		SET
			ID_TOPIC = $split2_ID_TOPIC,
			subject = '$new_subject'
		WHERE ID_MSG IN ($postList)
		LIMIT " . ($split2_replies + 1), __FILE__, __LINE__);

	// Cache the new topics subject... we can do it now as all the subjects are the same!
	updateStats('subject', $split2_ID_TOPIC, $new_subject);

	// Mess with the old topic's first, last, and number of messages.
	db_query("
		UPDATE {$db_prefix}topics
		SET
			numReplies = $split1_replies,
			ID_FIRST_MSG = $split1_firstMsg,
			ID_LAST_MSG = $split1_lastMsg,
			ID_MEMBER_STARTED = $split1_firstMem,
			ID_MEMBER_UPDATED = $split1_lastMem
		WHERE ID_TOPIC = $split1_ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);

	// Now, put the first/last message back to what they should be.
	db_query("
		UPDATE {$db_prefix}topics
		SET
			ID_FIRST_MSG = $split2_firstMsg,
			ID_LAST_MSG = $split2_lastMsg
		WHERE ID_TOPIC = $split2_ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);

	// The board has more topics now.
	db_query("
		UPDATE {$db_prefix}boards
		SET numTopics = numTopics + 1
		WHERE ID_BOARD = $ID_BOARD
		LIMIT 1", __FILE__, __LINE__);

	// We're going to assume they bothered to read it before splitting it.
	if (!$user_info['is_guest'])
		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_MSG, ID_MEMBER, ID_TOPIC)
			VALUES ($modSettings[maxMsgID], $ID_MEMBER, $split2_ID_TOPIC)", __FILE__, __LINE__);

	// Housekeeping.
	updateStats('topic');
	updateLastMessages($ID_BOARD);

	logAction('split', array('topic' => $split1_ID_TOPIC, 'new_topic' => $split2_ID_TOPIC));

	// Notify people that this topic has been split?
	sendNotifications($split1_ID_TOPIC, 'split');

	// Return the ID of the newly created topic.
	return $split2_ID_TOPIC;
}

// Merge two topics into one topic... useful if they have the same basic subject.
function MergeTopics()
{
	// Load the template....
	loadTemplate('SplitTopics');

	$subActions = array(
		'done' => 'MergeDone',
		'execute' => 'MergeExecute',
		'index' => 'MergeIndex',
		'options' => 'MergeExecute',
	);

	// ?action=mergetopics;sa=LETSBREAKIT won't work, sorry.
	if (empty($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
		MergeIndex();
	else
		$subActions[$_REQUEST['sa']]();
}

// Merge two topics together.
function MergeIndex()
{
	global $txt, $board, $context;
	global $scripturl, $topic, $db_prefix, $user_info, $modSettings;

	$_REQUEST['targetboard'] = isset($_REQUEST['targetboard']) ? (int) $_REQUEST['targetboard'] : $board;
	$context['target_board'] = $_REQUEST['targetboard'];

	if (!isset($_GET['from']))
		fatal_lang_error(1);
	$_GET['from'] = (int) $_GET['from'];

	// How many topics are on this board?  (used for paging.)
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}topics
		WHERE ID_BOARD = $_REQUEST[targetboard]", __FILE__, __LINE__);
	list ($topiccount) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Make the page list.
	$context['page_index'] = constructPageIndex($scripturl . '?action=mergetopics;from=' . $_GET['from'] . ';targetboard=' . $_REQUEST['targetboard'] . ';board=' . $board . '.%d', $_REQUEST['start'], $topiccount, $modSettings['defaultMaxTopics'], true);

	// Get the topic's subject.
	$request = db_query("
		SELECT m.subject
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE m.ID_MSG = t.ID_FIRST_MSG
			AND t.ID_TOPIC = $_GET[from]
			AND t.ID_BOARD = $board
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
	list ($subject) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Tell the template a few things..
	$context['origin_topic'] = $_GET['from'];
	$context['origin_subject'] = $subject;
	$context['origin_js_subject'] = addcslashes(addslashes($subject), '/');
	$context['page_title'] = $txt['smf252'];

	// Check which boards you have merge permissions on.
	$merge_boards = boardsAllowedTo('merge_any');

	if (empty($merge_boards))
		fatal_lang_error('cannot_merge_any');

	// Get a list of boards they can navigate to to merge.
	$request = db_query("
		SELECT b.ID_BOARD, b.name AS bName, c.name AS cName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]" . (!in_array(0, $merge_boards) ? "
			AND b.ID_BOARD IN (" . implode(', ', $merge_boards) . ")" : ''), __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['bName'],
			'category' => $row['cName']
		);
	mysql_free_result($request);

	// Get some topics to merge it with.
	$request = db_query("
		SELECT t.ID_TOPIC, m.subject, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterName
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_MSG = t.ID_FIRST_MSG
			AND t.ID_BOARD = $_REQUEST[targetboard]
			AND t.ID_TOPIC != $_GET[from]
		ORDER BY " . (!empty($modSettings['enableStickyTopics']) ? 't.isSticky DESC, ' : '') . "t.ID_LAST_MSG DESC
		LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
	$context['topics'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		censorText($row['subject']);

		$context['topics'][] = array(
			'id' => $row['ID_TOPIC'],
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" target="_blank">' . $row['posterName'] . '</a>'
			),
			'subject' => $row['subject'],
			'js_subject' => addcslashes(addslashes($row['subject']), '/')
		);
	}
	mysql_free_result($request);

	if (empty($context['topics']) && count($context['boards']) <= 1)
		fatal_lang_error('merge_need_more_topics');

	$context['sub_template'] = 'merge';
}

// Now that the topic IDs are known, do the proper merging.
function MergeExecute($topics = array())
{
	global $db_prefix, $user_info, $txt, $context, $scripturl, $sourcedir;
	global $func, $language, $modSettings;

	// The parameters of MergeExecute were set, so this must've been an internal call.
	if (!empty($topics))
	{
		isAllowedTo('merge_any');
		loadTemplate('SplitTopics');
	}
	checkSession('request');

	// Handle URLs from MergeIndex.
	if (!empty($_GET['from']) && !empty($_GET['to']))
		$topics = array((int) $_GET['from'], (int) $_GET['to']);

	// If we came from a form, the topic IDs came by post.
	if (!empty($_POST['topics']) && is_array($_POST['topics']))
		$topics = $_POST['topics'];

	// There's nothing to merge with just one topic...
	if (empty($topics) || !is_array($topics) || count($topics) == 1)
		fatal_lang_error('merge_need_more_topics');

	// Make sure every topic is numeric, or some nasty things could be done with the DB.
	foreach ($topics as $id => $topic)
		$topics[$id] = (int) $topic;

	// Get info about the topics and polls that will be merged.
	$request = db_query("
		SELECT
			t.ID_TOPIC, t.ID_BOARD, t.ID_POLL, t.numViews, t.isSticky,
			m1.subject, m1.posterTime AS time_started, IFNULL(mem1.ID_MEMBER, 0) AS ID_MEMBER_STARTED, IFNULL(mem1.realName, m1.posterName) AS name_started,
			m2.posterTime AS time_updated, IFNULL(mem2.ID_MEMBER, 0) AS ID_MEMBER_UPDATED, IFNULL(mem2.realName, m2.posterName) AS name_updated
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m1, {$db_prefix}messages AS m2)
			LEFT JOIN {$db_prefix}members AS mem1 ON (mem1.ID_MEMBER = m1.ID_MEMBER)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.ID_MEMBER = m2.ID_MEMBER)
		WHERE t.ID_TOPIC IN (" . implode(', ', $topics) . ")
			AND m1.ID_MSG = t.ID_FIRST_MSG
			AND m2.ID_MSG = t.ID_LAST_MSG
		ORDER BY t.ID_FIRST_MSG
		LIMIT " . count($topics), __FILE__, __LINE__);
	if (mysql_num_rows($request) < 2)
		fatal_lang_error('smf263');
	$num_views = 0;
	$isSticky = 0;
	$boards = array();
	$polls = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$topic_data[$row['ID_TOPIC']] = array(
			'id' => $row['ID_TOPIC'],
			'board' => $row['ID_BOARD'],
			'poll' => $row['ID_POLL'],
			'numViews' => $row['numViews'],
			'subject' => $row['subject'],
			'started' => array(
				'time' => timeformat($row['time_started']),
				'timestamp' => forum_time(true, $row['time_started']),
				'href' => empty($row['ID_MEMBER_STARTED']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER_STARTED'],
				'link' => empty($row['ID_MEMBER_STARTED']) ? $row['name_started'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER_STARTED'] . '">' . $row['name_started'] . '</a>'
			),
			'updated' => array(
				'time' => timeformat($row['time_updated']),
				'timestamp' => forum_time(true, $row['time_updated']),
				'href' => empty($row['ID_MEMBER_UPDATED']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER_UPDATED'],
				'link' => empty($row['ID_MEMBER_UPDATED']) ? $row['name_updated'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER_UPDATED'] . '">' . $row['name_updated'] . '</a>'
			)
		);
		$num_views += $row['numViews'];
		$boards[] = $row['ID_BOARD'];

		// If there's no poll, ID_POLL == 0...
		if ($row['ID_POLL'] > 0)
			$polls[] = $row['ID_POLL'];
		// Store the ID_TOPIC with the lowest ID_FIRST_MSG.
		if (empty($firstTopic))
			$firstTopic = $row['ID_TOPIC'];

		$isSticky = max($isSticky, $row['isSticky']);
	}
	mysql_free_result($request);

	$boards = array_values(array_unique($boards));

	// Get the boards a user is allowed to merge in.
	$merge_boards = boardsAllowedTo('merge_any');
	if (empty($merge_boards))
		fatal_lang_error('cannot_merge_any');

	// Make sure they can see all boards....
	$request = db_query("
		SELECT b.ID_BOARD
		FROM {$db_prefix}boards AS b
		WHERE b.ID_BOARD IN (" . implode(', ', $boards) . ")
			AND $user_info[query_see_board]" . (!in_array(0, $merge_boards) ? "
			AND b.ID_BOARD IN (" . implode(', ', $merge_boards) . ")" : '') . "
		LIMIT " . count($boards), __FILE__, __LINE__);
	// If the number of boards that's in the output isn't exactly the same as we've put in there, you're in trouble.
	if (mysql_num_rows($request) != count($boards))
		fatal_lang_error('smf232');
	mysql_free_result($request);

	if (empty($_REQUEST['sa']) || $_REQUEST['sa'] == 'options')
	{
		if (count($polls) > 1)
		{
			$request = db_query("
				SELECT t.ID_TOPIC, t.ID_POLL, m.subject, p.question
				FROM ({$db_prefix}polls AS p, {$db_prefix}topics AS t, {$db_prefix}messages AS m)
				WHERE p.ID_POLL IN (" . implode(', ', $polls) . ")
					AND t.ID_POLL = p.ID_POLL
					AND m.ID_MSG = t.ID_FIRST_MSG
				LIMIT " . count($polls), __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				$context['polls'][] = array(
					'id' => $row['ID_POLL'],
					'topic' => array(
						'id' => $row['ID_TOPIC'],
						'subject' => $row['subject']
					),
					'question' => $row['question'],
					'selected' => $row['ID_TOPIC'] == $firstTopic
				);
			mysql_free_result($request);
		}
		if (count($boards) > 1)
		{
			$request = db_query("
				SELECT ID_BOARD, name
				FROM {$db_prefix}boards
				WHERE ID_BOARD IN (" . implode(', ', $boards) . ")
				ORDER BY name
				LIMIT " . count($boards), __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				$context['boards'][] = array(
					'id' => $row['ID_BOARD'],
					'name' => $row['name'],
					'selected' => $row['ID_BOARD'] == $topic_data[$firstTopic]['board']
				);
			mysql_free_result($request);
		}

		$context['topics'] = $topic_data;
		foreach ($topic_data as $id => $topic)
			$context['topics'][$id]['selected'] = $topic['id'] == $firstTopic;

		$context['page_title'] = $txt['smf252'];
		$context['sub_template'] = 'merge_extra_options';
		return;
	}

	// Determine target board.
	$target_board = count($boards) > 1 ? (int) $_REQUEST['board'] : $boards[0];
	if (!in_array($target_board, $boards))
		fatal_lang_error('smf232');

	// Determine which poll will survive and which polls won't.
	$target_poll = count($polls) > 1 ? (int) $_POST['poll'] : (count($polls) == 1 ? $polls[0] : 0);
	if ($target_poll > 0 && !in_array($target_poll, $polls))
		fatal_lang_error(1, false);
	$deleted_polls = empty($target_poll) ? $polls : array_diff($polls, array($target_poll));

	// Determine the subject of the newly merged topic - was a custom subject specified?
	if (empty($_POST['subject']) && isset($_POST['custom_subject']) && $_POST['custom_subject'] != '')
		$target_subject = $func['htmlspecialchars']($_POST['custom_subject']);
	// A subject was selected from the list.
	elseif (!empty($topic_data[(int) $_POST['subject']]['subject']))
		$target_subject = addslashes($topic_data[(int) $_POST['subject']]['subject']);
	// Nothing worked? Just take the subject of the first message.
	else
		$target_subject = addslashes($topic_data[$firstTopic]['subject']);

	// Get the first and last message and the number of messages....
	$request = db_query("
		SELECT MIN(ID_MSG), MAX(ID_MSG), COUNT(ID_MSG) - 1
		FROM {$db_prefix}messages
		WHERE ID_TOPIC IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);
	list ($first_msg, $last_msg, $num_replies) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Get the member ID of the first and last message.
	$request = db_query("
		SELECT ID_MEMBER
		FROM {$db_prefix}messages
		WHERE ID_MSG IN ($first_msg, $last_msg)
		ORDER BY ID_MSG
		LIMIT 2", __FILE__, __LINE__);
	list ($member_started) = mysql_fetch_row($request);
	list ($member_updated) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Assign the first topic ID to be the merged topic.
	$ID_TOPIC = min($topics);

	// Delete the remaining topics.
	$deleted_topics = array_diff($topics, array($ID_TOPIC));
	db_query("
		DELETE FROM {$db_prefix}topics
		WHERE ID_TOPIC IN (" . implode(', ', $deleted_topics) . ")
		LIMIT " . count($deleted_topics), __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_search_subjects
		WHERE ID_TOPIC IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);

	// Asssign the properties of the newly merged topic.
	db_query("
		UPDATE {$db_prefix}topics
		SET
			ID_BOARD = $target_board,
			ID_MEMBER_STARTED = $member_started,
			ID_MEMBER_UPDATED = $member_updated,
			ID_FIRST_MSG = $first_msg,
			ID_LAST_MSG = $last_msg,
			ID_POLL = $target_poll,
			numReplies = $num_replies,
			numViews = $num_views,
			isSticky = $isSticky
		WHERE ID_TOPIC = $ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);

	// Grab the response prefix (like 'Re: ') in the default forum language.	
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

	// Change the topic IDs of all messages that will be merged.  Also adjust subjects if 'enforce subject' was checked.
	db_query("
		UPDATE {$db_prefix}messages
		SET
			ID_TOPIC = $ID_TOPIC,
			ID_BOARD = $target_board" . (!empty($_POST['enforce_subject']) ? ",
			subject = '$context[response_prefix]$target_subject'" : '') . "
		WHERE ID_TOPIC IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);

	// Change the subject of the first message...
	db_query("
		UPDATE {$db_prefix}messages
		SET subject = '$target_subject'
		WHERE ID_MSG = $first_msg
		LIMIT 1", __FILE__, __LINE__);

	// Adjust all calendar events to point to the new topic.
	db_query("
		UPDATE {$db_prefix}calendar
		SET
			ID_TOPIC = $ID_TOPIC,
			ID_BOARD = $target_board
		WHERE ID_TOPIC IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);

	// Merge log topic entries.
	$request = db_query("
		SELECT ID_MEMBER, MIN(ID_MSG) AS new_ID_MSG
		FROM {$db_prefix}log_topics
		WHERE ID_TOPIC IN (" . implode(', ', $topics) . ")
		GROUP BY ID_MEMBER", __FILE__, __LINE__);
	if (mysql_num_rows($request) > 0)
	{
		$replaceEntries = array();
		while ($row = mysql_fetch_assoc($request))
			$replaceEntries[] = "($row[ID_MEMBER], $ID_TOPIC, $row[new_ID_MSG])";

		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_MEMBER, ID_TOPIC, ID_MSG)
			VALUES " . implode(', ', $replaceEntries), __FILE__, __LINE__);
		unset($replaceEntries);

		// Get rid of the old log entries.
		db_query("
			DELETE FROM {$db_prefix}log_topics
			WHERE ID_TOPIC IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);
	}
	mysql_free_result($request);

	// Merge topic notifications.
	if (!empty($_POST['notifications']) && is_array($_POST['notifications']))
	{
		// Check if the notification array contains valid topics.
		if (count(array_diff($_POST['notifications'], $topics)) > 0)
			fatal_lang_error('smf232');
		$request = db_query("
			SELECT ID_MEMBER, MAX(sent) AS sent
			FROM {$db_prefix}log_notify
			WHERE ID_TOPIC IN (" . implode(', ', $_POST['notifications']) . ")
			GROUP BY ID_MEMBER", __FILE__, __LINE__);
		if (mysql_num_rows($request) > 0)
		{
			$replaceEntries = array();
			while ($row = mysql_fetch_assoc($request))
				$replaceEntries[] = "($row[ID_MEMBER], $ID_TOPIC, 0, $row[sent])";

			db_query("
				REPLACE INTO {$db_prefix}log_notify
					(ID_MEMBER, ID_TOPIC, ID_BOARD, sent)
				VALUES " . implode(', ', $replaceEntries), __FILE__, __LINE__);
			unset($replaceEntries);

			db_query("
				DELETE FROM {$db_prefix}log_topics
				WHERE ID_TOPIC IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);
		}
		mysql_free_result($request);
	}

	// Get rid of the redundant polls.
	if (!empty($deleted_polls))
	{
		db_query("
			DELETE FROM {$db_prefix}polls
			WHERE ID_POLL IN (" . implode(', ', $deleted_polls) . ")
			LIMIT 1", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}poll_choices
			WHERE ID_POLL IN (" . implode(', ', $deleted_polls) . ")", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}log_polls
			WHERE ID_POLL IN (" . implode(', ', $deleted_polls) . ")", __FILE__, __LINE__);
	}

	// Fix the board totals.
	if (count($boards) > 1)
	{
		$request = db_query("
			SELECT ID_BOARD, COUNT(*) AS numTopics, SUM(numReplies) + COUNT(*) AS numPosts
			FROM {$db_prefix}topics
			WHERE ID_BOARD IN (" . implode(', ', $boards) . ")
			GROUP BY ID_BOARD
			LIMIT " . count($boards), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			db_query("
				UPDATE {$db_prefix}boards
				SET
					numPosts = $row[numPosts],
					numTopics = $row[numTopics]
				WHERE ID_BOARD = $row[ID_BOARD]
				LIMIT 1", __FILE__, __LINE__);
		mysql_free_result($request);
	}
	else
		db_query("
			UPDATE {$db_prefix}boards
			SET numTopics = IF(" . (count($topics) - 1) . " > numTopics, 0, numTopics - " . (count($topics) - 1) . ")
			WHERE ID_BOARD = $target_board
			LIMIT 1", __FILE__, __LINE__);

	require_once($sourcedir . '/Subs-Post.php');

	// Update all the statistics.
	updateStats('topic');
	updateStats('subject', $ID_TOPIC, $target_subject);
	updateLastMessages($boards);

	logAction('merge', array('topic' => $ID_TOPIC));

	// Notify people that these topics have been merged?
	sendNotifications($ID_TOPIC, 'merge');

	// Send them to the all done page.
	redirectexit('action=mergetopics;sa=done;to=' . $ID_TOPIC . ';targetboard=' . $target_board);
}

// Tell the user the move was done properly.
function MergeDone()
{
	global $txt, $context;

	// Make sure the template knows everything...
	$context['target_board'] = (int) $_GET['targetboard'];
	$context['target_topic'] = (int) $_GET['to'];

	$context['page_title'] = $txt['smf252'];
	$context['sub_template'] = 'merge_done';
}

?>