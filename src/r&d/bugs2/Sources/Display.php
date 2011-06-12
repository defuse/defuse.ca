<?php
/**********************************************************************************
* Display.php                                                                     *
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

/*	This is perhaps the most important and probably most accessed files in all
	of SMF.  This file controls topic, message, and attachment display.  It
	does so with the following functions:

	void Display()
		- loads the posts in a topic up so they can be displayed.
		- supports wireless, using wap/wap2/imode and the Wireless templates.
		- uses the main sub template of the Display template.
		- requires a topic, and can go to the previous or next topic from it.
		- jumps to the correct post depending on a number/time/IS_MSG passed.
		- depends on the defaultMaxMessages and enableAllMessages settings.
		- is accessed by ?topic=ID_TOPIC.START.

	array prepareDisplayContext(bool reset = false)
		- actually gets and prepares the message context.
		- starts over from the beginning if reset is set to true, which is
		  useful for showing an index before or after the posts.

	void Download()
		- downloads an attachment or avatar, and increments the downloads.
		- requires the view_attachments permission. (not for avatars!)
		- disables the session parser, and clears any previous output.
		- depends on the attachmentUploadDir setting being correct.
		- is accessed via the query string ?action=dlattach.
		- views to attachments and avatars do not increase hits and are not
		  logged in the "Who's Online" log.

	array loadAttachmentContext(int ID_MSG)
		- loads an attachment's contextual data including, most importantly,
		  its size if it is an image.
		- expects the $attachments array to have been filled with the proper
		  attachment data, as Display() does.
		- requires the view_attachments permission to calculate image size.
		- attempts to keep the "aspect ratio" of the posted image in line,
		  even if it has to be resized by the max_image_width and
		  max_image_height settings.
*/

// The central part of the board - topic display.
function Display()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $settings;
	global $options, $sourcedir, $user_info, $ID_MEMBER, $board_info, $topic;
	global $board, $attachments, $messages_request, $language;

	// What are you gonna display if these are empty?!
	if (empty($topic))
		fatal_lang_error('smf232', false);

	// Load the proper template and/or sub template.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_display';
	else
		loadTemplate('Display');

	// Not only does a prefetch make things slower for the server, but it makes it impossible to know if they read it.
	if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	{
		ob_end_clean();
		header('HTTP/1.1 403 Prefetch Forbidden');
		die;
	}

	// Find the previous or next topic.  Make a fuss if there are no more.
	if (isset($_REQUEST['prev_next']) && ($_REQUEST['prev_next'] == 'prev' || $_REQUEST['prev_next'] == 'next'))
	{
		// No use in calculating the next topic if there's only one.
		if ($board_info['num_topics'] > 1)
		{
			// Just prepare some variables that are used in the query.
			$gt_lt = $_REQUEST['prev_next'] == 'prev' ? '>' : '<';
			$order = $_REQUEST['prev_next'] == 'prev' ? '' : ' DESC';

			$request = db_query("
				SELECT t2.ID_TOPIC
				FROM ({$db_prefix}topics AS t, {$db_prefix}topics AS t2)
				WHERE t.ID_TOPIC = $topic" . (empty($modSettings['enableStickyTopics']) ? "
					AND t2.ID_LAST_MSG $gt_lt t.ID_LAST_MSG" : "
					AND ((t2.ID_LAST_MSG $gt_lt t.ID_LAST_MSG AND t2.isSticky $gt_lt= t.isSticky) OR t2.isSticky $gt_lt t.isSticky)") . "
					AND t2.ID_BOARD = $board
				ORDER BY" . (empty($modSettings['enableStickyTopics']) ? '' : " t2.isSticky$order,") . " t2.ID_LAST_MSG$order
				LIMIT 1", __FILE__, __LINE__);

			// No more left.
			if (mysql_num_rows($request) == 0)
			{
				mysql_free_result($request);

				// Roll over - if we're going prev, get the last - otherwise the first.
				$request = db_query("
					SELECT ID_TOPIC
					FROM {$db_prefix}topics
					WHERE ID_BOARD = $board
					ORDER BY" . (empty($modSettings['enableStickyTopics']) ? '' : " isSticky$order,") . " ID_LAST_MSG$order
					LIMIT 1", __FILE__, __LINE__);
			}

			// Now you can be sure $topic is the ID_TOPIC to view.
			list ($topic) = mysql_fetch_row($request);
			mysql_free_result($request);

			$context['current_topic'] = $topic;
		}

		// Go to the newest message on this topic.
		$_REQUEST['start'] = 'new';

 		// Duplicate link!  Tell the robots not to link this.
 		$context['robot_no_index'] = true;
	}

	// Add 1 to the number of views of this topic.
	if (empty($_SESSION['last_read_topic']) || $_SESSION['last_read_topic'] != $topic)
	{
		db_query("
			UPDATE {$db_prefix}topics
			SET numViews = numViews + 1
			WHERE ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);

		$_SESSION['last_read_topic'] = $topic;
	}

	// Get all the important topic info.
	$request = db_query("
		SELECT
			t.numReplies, t.numViews, t.locked, ms.subject, t.isSticky, t.ID_POLL,
			t.ID_MEMBER_STARTED, t.ID_FIRST_MSG, t.ID_LAST_MSG,
			" . ($user_info['is_guest'] ? '0' : 'IFNULL(lt.ID_MSG, -1) + 1') . " AS new_from
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS ms)" . ($user_info['is_guest'] ? '' : "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = $topic AND lt.ID_MEMBER = $ID_MEMBER)") ."
		WHERE t.ID_TOPIC = $topic
			AND ms.ID_MSG = t.ID_FIRST_MSG
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error(472, false);
	$topicinfo = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// The start isn't a number; it's information about what to do, where to go.
	if (!is_numeric($_REQUEST['start']))
	{
		// Redirect to the page and post with new messages, originally by Omar Bazavilvazo.
		if ($_REQUEST['start'] == 'new')
		{
			// Guests automatically go to the last topic.
			if ($user_info['is_guest'])
			{
				$context['start_from'] = $topicinfo['numReplies'];
				$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : 0;
			}
			else
			{
				// Find the earliest unread message in the topic. (the use of topics here is just for both tables.)
				$request = db_query("
					SELECT IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from
					FROM {$db_prefix}topics AS t
						LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = $topic AND lt.ID_MEMBER = $ID_MEMBER)
						LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = $board AND lmr.ID_MEMBER = $ID_MEMBER)
					WHERE t.ID_TOPIC = $topic
					LIMIT 1", __FILE__, __LINE__);
				list ($new_from) = mysql_fetch_row($request);
				mysql_free_result($request);

				// Fall through to the next if statement.
				$_REQUEST['start'] = 'msg' . $new_from;
			}
		}

		// Start from a certain time index, not a message.
		if (substr($_REQUEST['start'], 0, 4) == 'from')
		{
			$timestamp = (int) substr($_REQUEST['start'], 4);
			if ($timestamp === 0)
				$_REQUEST['start'] = 0;
			else
			{
				// Find the number of messages posted before said time...
				$request = db_query("
					SELECT COUNT(*)
					FROM {$db_prefix}messages
					WHERE posterTime < $timestamp
						AND ID_TOPIC = $topic", __FILE__, __LINE__);
				list ($context['start_from']) = mysql_fetch_row($request);
				mysql_free_result($request);

				// Handle view_newest_first options, and get the correct start value.
				$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : $topicinfo['numReplies'] - $context['start_from'];
			}
		}
			
		// Link to a message...
		elseif (substr($_REQUEST['start'], 0, 3) == 'msg')
		{
			$virtual_msg = (int) substr($_REQUEST['start'], 3);
			if ($virtual_msg >= $topicinfo['ID_LAST_MSG'])
				$context['start_from'] = $topicinfo['numReplies'];
			elseif ($virtual_msg <= $topicinfo['ID_FIRST_MSG'])
				$context['start_from'] = 0;
			else
			{
				// Find the start value for that message......
				$request = db_query("
					SELECT COUNT(*)
					FROM {$db_prefix}messages
					WHERE ID_MSG < $virtual_msg
						AND ID_TOPIC = $topic", __FILE__, __LINE__);
				list ($context['start_from']) = mysql_fetch_row($request);
				mysql_free_result($request);
			}
			
			// We need to reverse the start as well in this case.
			$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : $topicinfo['numReplies'] - $context['start_from'];

			$context['robot_no_index'] = true;
		}
	}

	// Create a previous next string if the selected theme has it as a selected option.
	$context['previous_next'] = $modSettings['enablePreviousNext'] ? '<a href="' . $scripturl . '?topic=' . $topic . '.0;prev_next=prev#new">' . $txt['previous_next_back'] . '</a> <a href="' . $scripturl . '?topic=' . $topic . '.0;prev_next=next#new">' . $txt['previous_next_forward'] . '</a>' : '';

	// Check if spellchecking is both enabled and actually working. (for quick reply.)
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Censor the title...
	censorText($topicinfo['subject']);
	$context['page_title'] = $topicinfo['subject'];

	$context['num_replies'] = $topicinfo['numReplies'];
	$context['topic_first_message'] = $topicinfo['ID_FIRST_MSG'];

	// Is this topic sticky, or can it even be?
	$topicinfo['isSticky'] = empty($modSettings['enableStickyTopics']) ? '0' : $topicinfo['isSticky'];

	// Default this topic to not marked for notifications... of course...
	$context['is_marked_notify'] = false;

	// Guests can't mark topics read or for notifications, just can't sorry.
	if (!$user_info['is_guest'])
	{
		// Mark the topic as read :)
		if (!empty($topicinfo['new_from']))
		{
			db_query("
				UPDATE {$db_prefix}log_topics
				SET ID_MSG = $modSettings[maxMsgID]
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_TOPIC = $topic
				LIMIT 1", __FILE__, __LINE__);

			$flag = db_affected_rows() !== 0;
		}

		if (empty($flag))
			db_query("
				REPLACE INTO {$db_prefix}log_topics
					(ID_MSG, ID_MEMBER, ID_TOPIC)
				VALUES ($modSettings[maxMsgID], $ID_MEMBER, $topic)", __FILE__, __LINE__);

		// Check for notifications on this topic OR board.
		$request = db_query("
			SELECT sent, ID_TOPIC
			FROM {$db_prefix}log_notify
			WHERE (ID_TOPIC = $topic OR ID_BOARD = $board)
				AND ID_MEMBER = $ID_MEMBER
			LIMIT 2", __FILE__, __LINE__);
		$do_once = true;
		while ($row = mysql_fetch_assoc($request))
		{
			// Find if this topic is marked for notification...
			if (!empty($row['ID_TOPIC']))
				$context['is_marked_notify'] = true;

			// Only do this once, but mark the notifications as "not sent yet" for next time.
			if (!empty($row['sent']) && $do_once)
			{
				db_query("
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE (ID_TOPIC = $topic OR ID_BOARD = $board)
						AND ID_MEMBER = $ID_MEMBER
					LIMIT 2", __FILE__, __LINE__);
				$do_once = false;
			}
		}

		// Have we recently cached the number of new topics in this board, and it's still a lot?
		if (isset($_REQUEST['topicseen']) && isset($_SESSION['topicseen_cache'][$board]) && $_SESSION['topicseen_cache'][$board] > 5)
			$_SESSION['topicseen_cache'][$board]--;
		// Mark board as seen if this is the only new topic.
		elseif (isset($_REQUEST['topicseen']))
		{
			// Use the mark read tables... and the last visit to figure out if this should be read or not.
			$request = db_query("
				SELECT COUNT(*)
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = $board AND lb.ID_MEMBER = $ID_MEMBER)
					LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
				WHERE t.ID_BOARD = $board
					AND t.ID_LAST_MSG > IFNULL(lb.ID_MSG, 0)
					AND t.ID_LAST_MSG > IFNULL(lt.ID_MSG, 0)" . (empty($_SESSION['ID_MSG_LAST_VISIT']) ? '' : "
					AND t.ID_LAST_MSG > $_SESSION[ID_MSG_LAST_VISIT]"), __FILE__, __LINE__);
			list ($numNewTopics) = mysql_fetch_row($request);
			mysql_free_result($request);

			// If there're no real new topics in this board, mark the board as seen.
			if (empty($numNewTopics))
				$_REQUEST['boardseen'] = true;
			else
				$_SESSION['topicseen_cache'][$board] = $numNewTopics;
		}
		// Probably one less topic - maybe not, but even if we decrease this too fast it will only make us look more often.
		elseif (isset($_SESSION['topicseen_cache'][$board]))
			$_SESSION['topicseen_cache'][$board]--;

		// Mark board as seen if we came using last post link from BoardIndex. (or other places...)
		if (isset($_REQUEST['boardseen']))
		{
			db_query("
				REPLACE INTO {$db_prefix}log_boards
					(ID_MSG, ID_MEMBER, ID_BOARD)
				VALUES ($modSettings[maxMsgID], $ID_MEMBER, $board)", __FILE__, __LINE__);
		}
	}

	// Let's get nosey, who is viewing this topic?
	if (!empty($settings['display_who_viewing']))
	{
		// Start out with no one at all viewing it.
		$context['view_members'] = array();
		$context['view_members_list'] = array();
		$context['view_num_hidden'] = 0;

		// Search for members who have this topic set in their GET data.
		$request = db_query("
			SELECT
				lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
				mg.onlineColor, mg.ID_GROUP, mg.groupName
			FROM {$db_prefix}log_online AS lo
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
			WHERE INSTR(lo.url, 's:5:\"topic\";i:$topic;') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (empty($row['ID_MEMBER']))
				continue;

			if (!empty($row['onlineColor']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

			$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			// Add them both to the list and to the more detailed list.
			if (!empty($row['showOnline']) || allowedTo('moderate_forum'))
				$context['view_members_list'][$row['logTime'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;
			$context['view_members'][$row['logTime'] . $row['memberName']] = array(
				'id' => $row['ID_MEMBER'],
				'username' => $row['memberName'],
				'name' => $row['realName'],
				'group' => $row['ID_GROUP'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => $link,
				'is_buddy' => $is_buddy,
				'hidden' => empty($row['showOnline']),
			);

			if (empty($row['showOnline']))
				$context['view_num_hidden']++;
		}

		// The number of guests is equal to the rows minus the ones we actually used ;).
		$context['view_num_guests'] = mysql_num_rows($request) - count($context['view_members']);
		mysql_free_result($request);

		// Sort the list.
		krsort($context['view_members']);
		krsort($context['view_members_list']);
	}

	// If all is set, but not allowed... just unset it.
	if (isset($_REQUEST['all']) && empty($modSettings['enableAllMessages']))
		unset($_REQUEST['all']);
	// Otherwise, it must be allowed... so pretend start was -1.
	elseif (isset($_REQUEST['all']))
		$_REQUEST['start'] = -1;

	// Construct the page index, allowing for the .START method...
	$context['page_index'] = constructPageIndex($scripturl . '?topic=' . $topic . '.%d', $_REQUEST['start'], $topicinfo['numReplies'] + 1, $modSettings['defaultMaxMessages'], true);
	$context['start'] = $_REQUEST['start'];

	// This is information about which page is current, and which page we're on - in case you don't like the constructed page index. (again, wireles..)
	$context['page_info'] = array(
		'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxMessages'] + 1,
		'num_pages' => floor($topicinfo['numReplies'] / $modSettings['defaultMaxMessages']) + 1
	);

	// Figure out all the link to the next/prev/first/last/etc. for wireless mainly.
	$context['links'] = array(
		'first' => $_REQUEST['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?topic=' . $topic . '.0' : '',
		'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?topic=' . $topic . '.' . ($_REQUEST['start'] - $modSettings['defaultMaxMessages']) : '',
		'next' => $_REQUEST['start'] + $modSettings['defaultMaxMessages'] < $topicinfo['numReplies'] + 1 ? $scripturl . '?topic=' . $topic. '.' . ($_REQUEST['start'] + $modSettings['defaultMaxMessages']) : '',
		'last' => $_REQUEST['start'] + $modSettings['defaultMaxMessages'] < $topicinfo['numReplies'] + 1 ? $scripturl . '?topic=' . $topic. '.' . (floor($topicinfo['numReplies'] / $modSettings['defaultMaxMessages']) * $modSettings['defaultMaxMessages']) : '',
		'up' => $scripturl . '?board=' . $board . '.0'
	);

	// If they are viewing all the posts, show all the posts, otherwise limit the number.
	if (!empty($modSettings['enableAllMessages']) && $topicinfo['numReplies'] + 1 > $modSettings['defaultMaxMessages'] && $topicinfo['numReplies'] + 1 < $modSettings['enableAllMessages'])
	{
		if (isset($_REQUEST['all']))
		{
			// No limit! (actually, there is a limit, but...)
			$modSettings['defaultMaxMessages'] = -1;
			$context['page_index'] .= empty($modSettings['compactTopicPagesEnable']) ? '<b>' . $txt[190] . '</b> ' : '[<b>' . $txt[190] . '</b>] ';

			// Set start back to 0...
			$_REQUEST['start'] = 0;
		}
		// They aren't using it, but the *option* is there, at least.
		else
			$context['page_index'] .= '&nbsp;<a href="' . $scripturl . '?topic=' . $topic . '.0;all">' . $txt[190] . '</a> ';
	}

	// Build the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?topic=' . $topic . '.0',
		'name' => $topicinfo['subject'],
		'extra_before' => $settings['linktree_inline'] ? $txt[118] . ': ' : ''
	);

	// Build a list of this board's moderators.
	$context['moderators'] = &$board_info['moderators'];
	$context['link_moderators'] = array();
	if (!empty($board_info['moderators']))
	{
		// Add a link for each moderator...
		foreach ($board_info['moderators'] as $mod)
			$context['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $mod['id'] . '" title="' . $txt[62] . '">' . $mod['name'] . '</a>';

		// And show it after the board's name.
		$context['linktree'][count($context['linktree']) - 2]['extra_after'] = ' (' . (count($context['link_moderators']) == 1 ? $txt[298] : $txt[299]) . ': ' . implode(', ', $context['link_moderators']) . ')';
	}

	// Information about the current topic...
	$context['is_locked'] = $topicinfo['locked'];
	$context['is_sticky'] = $topicinfo['isSticky'];
	$context['is_very_hot'] = $topicinfo['numReplies'] >= $modSettings['hotTopicVeryPosts'];
	$context['is_hot'] = $topicinfo['numReplies'] >= $modSettings['hotTopicPosts'];

	// We don't want to show the poll icon in the topic class here, so pretend it's not one.
	$context['is_poll'] = false;
	determineTopicClass($context);

	$context['is_poll'] = $topicinfo['ID_POLL'] > 0 && $modSettings['pollMode'] == '1' && allowedTo('poll_view');

	// Did this user start the topic or not?
	$context['user']['started'] = $ID_MEMBER == $topicinfo['ID_MEMBER_STARTED'] && !$user_info['is_guest'];
	$context['topic_starter_id'] = $topicinfo['ID_MEMBER_STARTED'];

	// Set the topic's information for the template.
	$context['subject'] = $topicinfo['subject'];
	$context['num_views'] = $topicinfo['numViews'];
	$context['mark_unread_time'] = $topicinfo['new_from'];

	// For quick reply we need a response prefix in the default forum language.
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

	// If we want to show event information in the topic, prepare the data.
	if (allowedTo('calendar_view') && !empty($modSettings['cal_showInTopic']) && !empty($modSettings['cal_enabled']))
	{
		// First, try create a better time format, ignoring the "time" elements.
		if (preg_match('~%[AaBbCcDdeGghjmuYy](?:[^%]*%[AaBbCcDdeGghjmuYy])*~', $user_info['time_format'], $matches) == 0 || empty($matches[0]))
			$date_string = $user_info['time_format'];
		else
			$date_string = $matches[0];

		// Any calendar information for this topic?
		$request = db_query("
			SELECT cal.ID_EVENT, cal.startDate, cal.endDate, cal.title, cal.ID_MEMBER, mem.realName
			FROM {$db_prefix}calendar AS cal
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = cal.ID_MEMBER)
			WHERE cal.ID_TOPIC = $topic
			ORDER BY startDate", __FILE__, __LINE__);
		$context['linked_calendar_events'] = array();
		while ($row = mysql_fetch_assoc($request))
		{
			// Prepare the dates for being formatted.
			$startDate = sscanf($row['startDate'], '%04d-%02d-%02d');
			$startDate = mktime(12, 0, 0, $startDate[1], $startDate[2], $startDate[0]);
			$endDate = sscanf($row['endDate'], '%04d-%02d-%02d');
			$endDate = mktime(12, 0, 0, $endDate[1], $endDate[2], $endDate[0]);

			$context['linked_calendar_events'][] = array(
				'id' => $row['ID_EVENT'],
				'title' => $row['title'],
				'can_edit' => allowedTo('calendar_edit_any') || ($row['ID_MEMBER'] == $ID_MEMBER && allowedTo('calendar_edit_own')),
				'modify_href' => $scripturl . '?action=post;msg=' . $topicinfo['ID_FIRST_MSG'] . ';topic=' . $topic . '.0;calendar;eventid=' . $row['ID_EVENT'] . ';sesc=' . $context['session_id'],
				'start_date' => timeformat($startDate, $date_string),
				'start_timestamp' => forum_time(true, $startDate),
				'end_date' => timeformat($endDate, $date_string),
				'end_timestamp' => forum_time(true, $startDate),
				'is_last' => false
			);
		}
		mysql_free_result($request);

		if (!empty($context['linked_calendar_events']))
			$context['linked_calendar_events'][count($context['linked_calendar_events']) - 1]['is_last'] = true;
	}

	// Create the poll info if it exists.
	if ($context['is_poll'])
	{
		// Get the question and if it's locked.
		$request = db_query("
			SELECT
				p.question, p.votingLocked, p.hideResults, p.expireTime, p.maxVotes, p.changeVote,
				p.ID_MEMBER, IFNULL(mem.realName, p.posterName) AS posterName,
				COUNT(DISTINCT lp.ID_MEMBER) AS total
			FROM {$db_prefix}polls AS p
				LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.ID_POLL = p.ID_POLL)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = p.ID_MEMBER)
			WHERE p.ID_POLL = $topicinfo[ID_POLL]
			GROUP BY p.ID_POLL
			LIMIT 1", __FILE__, __LINE__);
		$pollinfo = mysql_fetch_assoc($request);
		mysql_free_result($request);

		// Get all the options, and calculate the total votes.
		$request = db_query("
			SELECT pc.ID_CHOICE, pc.label, pc.votes, IFNULL(lp.ID_CHOICE, -1) AS votedThis
			FROM {$db_prefix}poll_choices AS pc
				LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.ID_CHOICE = pc.ID_CHOICE AND lp.ID_POLL = $topicinfo[ID_POLL] AND lp.ID_MEMBER = $ID_MEMBER)
			WHERE pc.ID_POLL = $topicinfo[ID_POLL]", __FILE__, __LINE__);
		$pollOptions = array();
		$realtotal = 0;
		$pollinfo['has_voted'] = false;
		while ($row = mysql_fetch_assoc($request))
		{
			censorText($row['label']);
			$pollOptions[$row['ID_CHOICE']] = $row;
			$realtotal += $row['votes'];
			$pollinfo['has_voted'] |= $row['votedThis'] != -1;
		}
		mysql_free_result($request);

		// Set up the basic poll information.
		$context['poll'] = array(
			'id' => $topicinfo['ID_POLL'],
			'image' => 'normal_' . (empty($pollinfo['votingLocked']) ? 'poll' : 'locked_poll'),
			'question' => parse_bbc($pollinfo['question']),
			'total_votes' => $pollinfo['total'],
			'change_vote' => !empty($pollinfo['changeVote']),
			'is_locked' => !empty($pollinfo['votingLocked']),
			'options' => array(),
			'lock' => allowedTo('poll_lock_any') || ($context['user']['started'] && allowedTo('poll_lock_own')),
			'edit' => allowedTo('poll_edit_any') || ($context['user']['started'] && allowedTo('poll_edit_own')),
			'allowed_warning' => $pollinfo['maxVotes'] > 1 ? sprintf($txt['poll_options6'], $pollinfo['maxVotes']) : '',
			'is_expired' => !empty($pollinfo['expireTime']) && $pollinfo['expireTime'] < time(),
			'expire_time' => !empty($pollinfo['expireTime']) ? timeformat($pollinfo['expireTime']) : 0,
			'has_voted' => !empty($pollinfo['has_voted']),
			'starter' => array(
				'id' => $pollinfo['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => $pollinfo['ID_MEMBER'] == 0 ? '' : $scripturl . '?action=profile;u=' . $pollinfo['ID_MEMBER'],
				'link' => $pollinfo['ID_MEMBER'] == 0 ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $pollinfo['ID_MEMBER'] . '">' . $row['posterName'] . '</a>'
			)
		);

		// You're allowed to vote if:
		// 1. the poll did not expire, and
		// 2. you're not a guest... and
		// 3. you're not trying to view the results, and
		// 4. the poll is not locked, and
		// 5. you have the proper permissions, and
		// 6. you haven't already voted before.
		$context['allow_vote'] = !$context['poll']['is_expired'] && !$user_info['is_guest'] && empty($pollinfo['votingLocked']) && allowedTo('poll_vote') && !$context['poll']['has_voted'];

		// You're allowed to view the results if:
		// 1. you're just a super-nice-guy, or
		// 2. anyone can see them (hideResults == 0), or
		// 3. you can see them after you voted (hideResults == 1), or
		// 4. you've waited long enough for the poll to expire. (whether hideResults is 1 or 2.)
		$context['allow_poll_view'] = allowedTo('moderate_board') || $pollinfo['hideResults'] == 0 || ($pollinfo['hideResults'] == 1 && $context['poll']['has_voted']) || $context['poll']['is_expired'];
		$context['poll']['show_results'] = $context['allow_poll_view'] && isset($_REQUEST['viewResults']);

		// You're allowed to change your vote if:
		// 1. the poll did not expire, and
		// 2. you're not a guest... and
		// 3. the poll is not locked, and
		// 4. you have the proper permissions, and
		// 5. you have already voted, and
		// 6. the poll creator has said you can!
		$context['allow_change_vote'] = !$context['poll']['is_expired'] && !$user_info['is_guest'] && empty($pollinfo['votingLocked']) && allowedTo('poll_vote') && $context['poll']['has_voted'] && $context['poll']['change_vote'];

		// Calculate the percentages and bar lengths...
		$divisor = $realtotal == 0 ? 1 : $realtotal;

		// Determine if a decimal point is needed in order for the options to add to 100%.
		$precision = $realtotal == 100 ? 0 : 1;

		// Now look through each option, and...
		foreach ($pollOptions as $i => $option)
		{
			// First calculate the percentage, and then the width of the bar...
			$bar = round(($option['votes'] * 100) / $divisor, $precision);
			$barWide = $bar == 0 ? 1 : floor(($bar * 8) / 3);

			// Now add it to the poll's contextual theme data.
			$context['poll']['options'][$i] = array(
				'id' => 'options-' . $i,
				'percent' => $bar,
				'votes' => $option['votes'],
				'voted_this' => $option['votedThis'] != -1,
				'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
				'bar_width' => $barWide,
				'option' => parse_bbc($option['label']),
				'vote_button' => '<input type="' . ($pollinfo['maxVotes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
			);
		}
	}

	// Calculate the fastest way to get the messages!
	$ascending = empty($options['view_newest_first']);
	$start = $_REQUEST['start'];
	$limit = $modSettings['defaultMaxMessages'];
	$firstIndex = 0;
	if ($start > $topicinfo['numReplies'] / 2 && $modSettings['defaultMaxMessages'] != -1)
	{
		$ascending = !$ascending;
		$limit = $topicinfo['numReplies'] < $start + $limit ? $topicinfo['numReplies'] - $start + 1 : $limit;
		$start = $topicinfo['numReplies'] < $start + $limit ? 0 : $topicinfo['numReplies'] - $start - $limit + 1;
		$firstIndex = $limit - 1;
	}

	// Get each post and poster in this topic.
	$request = db_query("
		SELECT ID_MSG, ID_MEMBER
		FROM {$db_prefix}messages
		WHERE ID_TOPIC = $topic
		ORDER BY ID_MSG " . ($ascending ? '' : 'DESC') . ($modSettings['defaultMaxMessages'] == -1 ? '' : "
		LIMIT $start, $limit"), __FILE__, __LINE__);

	$messages = array();
	$posters = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!empty($row['ID_MEMBER']))
			$posters[] = $row['ID_MEMBER'];
		$messages[] = $row['ID_MSG'];
	}
	mysql_free_result($request);
	$posters = array_unique($posters);

	$attachments = array();

	// If there _are_ messages here... (probably an error otherwise :!)
	if (!empty($messages))
	{
		// Fetch attachments.
		if (!empty($modSettings['attachmentEnable']) && allowedTo('view_attachments'))
		{
			$request = db_query("
				SELECT
					a.ID_ATTACH, a.ID_MSG, a.filename, a.file_hash, IFNULL(a.size, 0) AS filesize, a.downloads,
					a.width, a.height" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : ",
					IFNULL(thumb.ID_ATTACH, 0) AS ID_THUMB, thumb.width AS thumb_width, thumb.height AS thumb_height") . "
				FROM {$db_prefix}attachments AS a" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : "
					LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.ID_ATTACH = a.ID_THUMB)") . "
				WHERE a.ID_MSG IN (" . implode(',', $messages) . ")
					AND a.attachmentType = 0", __FILE__, __LINE__);
			$temp = array();
			while ($row = mysql_fetch_assoc($request))
			{
				$temp[$row['ID_ATTACH']] = $row;

				if (!isset($attachments[$row['ID_MSG']]))
					$attachments[$row['ID_MSG']] = array();
			}
			mysql_free_result($request);

			// This is better than sorting it with the query...
			ksort($temp);

			foreach ($temp as $row)
				$attachments[$row['ID_MSG']][] = $row;
		}

		// What?  It's not like it *couldn't* be only guests in this topic...
		if (!empty($posters))
			loadMemberData($posters);
		$messages_request = db_query("
			SELECT
				ID_MSG, icon, subject, posterTime, posterIP, ID_MEMBER, modifiedTime, modifiedName, body,
				smileysEnabled, posterName, posterEmail,
				ID_MSG_MODIFIED < $topicinfo[new_from] AS isRead
			FROM {$db_prefix}messages
			WHERE ID_MSG IN (" . implode(',', $messages) . ")
			ORDER BY ID_MSG" . (empty($options['view_newest_first']) ? '' : ' DESC'), __FILE__, __LINE__);

		// Go to the last message if the given time is beyond the time of the last message.
		if (isset($context['start_from']) && $context['start_from'] >= $topicinfo['numReplies'])
			$context['start_from'] = $topicinfo['numReplies'];

		// Since the anchor information is needed on the top of the page we load these variables beforehand.
		$context['first_message'] = isset($messages[$firstIndex]) ? $messages[$firstIndex] : $messages[0];
		if (empty($options['view_newest_first']))
			$context['first_new_message'] = isset($context['start_from']) && $_REQUEST['start'] == $context['start_from'];
		else
			$context['first_new_message'] = isset($context['start_from']) && $_REQUEST['start'] == $topicinfo['numReplies'] - $context['start_from'];
	}
	else
	{
		$messages_request = false;
		$context['first_message'] = 0;
		$context['first_new_message'] = false;
	}

	// Load the "Jump to" list...
	loadJumpTo();

	// Set the callback.  (do you REALIZE how much memory all the messages would take?!?)
	$context['get_message'] = 'prepareDisplayContext';

	// Basic settings.... may be converted over at some point.
	$context['allow_hide_email'] = !empty($modSettings['allow_hideEmail']) || ($user_info['is_guest'] && !empty($modSettings['guest_hideContacts']));

	// Now set all the wonderful, wonderful permissions... like moderation ones...
	$common_permissions = array(
		'can_sticky' => 'make_sticky',
		'can_merge' => 'merge_any',
		'can_split' => 'split_any',
		'calendar_post' => 'calendar_post',
		'can_mark_notify' => 'mark_any_notify',
		'can_send_topic' => 'send_topic',
		'can_send_pm' => 'pm_send',
		'can_report_moderator' => 'report_any',
		'can_moderate_forum' => 'moderate_forum'
	);
	foreach ($common_permissions as $contextual => $perm)
		$context[$contextual] = allowedTo($perm);

	// Permissions with _any/_own versions.  $context[YYY] => ZZZ_any/_own.
	$anyown_permissions = array(
		'can_move' => 'move',
		'can_lock' => 'lock',
		'can_delete' => 'remove',
		'can_add_poll' => 'poll_add',
		'can_remove_poll' => 'poll_remove',
		'can_reply' => 'post_reply',
	);
	foreach ($anyown_permissions as $contextual => $perm)
		$context[$contextual] = allowedTo($perm . '_any') || ($context['user']['started'] && allowedTo($perm . '_own'));

	// Cleanup all the permissions with extra stuff...
	$context['can_mark_notify'] &= !$context['user']['is_guest'];
	$context['can_sticky'] &= !empty($modSettings['enableStickyTopics']);
	$context['calendar_post'] &= !empty($modSettings['cal_enabled']);
	$context['can_add_poll'] &= $modSettings['pollMode'] == '1' && $topicinfo['ID_POLL'] <= 0;
	$context['can_remove_poll'] &= $modSettings['pollMode'] == '1' && $topicinfo['ID_POLL'] > 0;
	$context['can_reply'] &= empty($topicinfo['locked']) || allowedTo('moderate_board');

	$board_count = 0;
	foreach ($context['jump_to'] as $id => $cat)
		$board_count += count($context['jump_to'][$id]['boards']);
	$context['can_move'] &= $board_count > 1;

	// Start this off for quick moderation - it will be or'd for each post.
	$context['can_remove_post'] = allowedTo('delete_any') || (allowedTo('delete_replies') && $context['user']['started']);

	// Load up the "double post" sequencing magic.
	if (!empty($options['display_quick_reply']))
		checkSubmitOnce('register');
}

// Callback for the message display.
function prepareDisplayContext($reset = false)
{
	global $settings, $txt, $modSettings, $scripturl, $options, $user_info;
	global $memberContext, $context, $messages_request, $topic, $ID_MEMBER, $attachments;

	static $counter = null;

	// If the query returned false, bail.
	if ($messages_request == false)
		return false;

	// Remember which message this is.  (ie. reply #83)
	if ($counter === null || $reset)
		$counter = empty($options['view_newest_first']) ? $context['start'] : $context['num_replies'] - $context['start'];

	// Start from the beginning...
	if ($reset)
		return @mysql_data_seek($messages_request, 0);

	// Attempt to get the next message.
	$message = mysql_fetch_assoc($messages_request);
	if (!$message)
		return false;

	// $context['icon_sources'] says where each icon should come from - here we set up the ones which will always exist!
	if (empty($context['icon_sources']))
	{
		$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
		$context['icon_sources'] = array();
		foreach ($stable_icons as $icon)
			$context['icon_sources'][$icon] = 'images_url';
	}

	// Message Icon Management... check the images exist.
	if (empty($modSettings['messageIconChecks_disable']))
	{
		// If the current icon isn't known, then we need to do something...
		if (!isset($context['icon_sources'][$message['icon']]))
			$context['icon_sources'][$message['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $message['icon'] . '.gif') ? 'images_url' : 'default_images_url';
	}
	elseif (!isset($context['icon_sources'][$message['icon']]))
		$context['icon_sources'][$message['icon']] = 'images_url';

	// If you're a lazy bum, you probably didn't give a subject...
	$message['subject'] = $message['subject'] != '' ? $message['subject'] : $txt[24];

	// Are you allowed to remove at least a single reply?
	$context['can_remove_post'] |= allowedTo('delete_own') && (empty($modSettings['edit_disable_time']) || $message['posterTime'] + $modSettings['edit_disable_time'] * 60 >= time()) && $message['ID_MEMBER'] == $ID_MEMBER;

	// If it couldn't load, or the user was a guest.... someday may be done with a guest table.
	if (!loadMemberContext($message['ID_MEMBER']))
	{
		// Notice this information isn't used anywhere else....
		$memberContext[$message['ID_MEMBER']]['name'] = $message['posterName'];
		$memberContext[$message['ID_MEMBER']]['id'] = 0;
		$memberContext[$message['ID_MEMBER']]['group'] = $txt[28];
		$memberContext[$message['ID_MEMBER']]['link'] = $message['posterName'];
		$memberContext[$message['ID_MEMBER']]['email'] = $message['posterEmail'];
		$memberContext[$message['ID_MEMBER']]['hide_email'] = $message['posterEmail'] == '' || (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']);
		$memberContext[$message['ID_MEMBER']]['is_guest'] = true;
	}
	else
	{
		$memberContext[$message['ID_MEMBER']]['can_view_profile'] = allowedTo('profile_view_any') || ($message['ID_MEMBER'] == $ID_MEMBER && allowedTo('profile_view_own'));
		$memberContext[$message['ID_MEMBER']]['is_topic_starter'] = $message['ID_MEMBER'] == $context['topic_starter_id'];
	}

	$memberContext[$message['ID_MEMBER']]['ip'] = $message['posterIP'];

	// Do the censor thang.
	censorText($message['body']);
	censorText($message['subject']);

	// Run BBC interpreter on the message.
	$message['body'] = parse_bbc($message['body'], $message['smileysEnabled'], $message['ID_MSG']);

	// Compose the memory eat- I mean message array.
	$output = array(
		'attachment' => loadAttachmentContext($message['ID_MSG']),
		'alternate' => $counter % 2,
		'id' => $message['ID_MSG'],
		'href' => $scripturl . '?topic=' . $topic . '.msg' . $message['ID_MSG'] . '#msg' . $message['ID_MSG'],
		'link' => '<a href="' . $scripturl . '?topic=' . $topic . '.msg' . $message['ID_MSG'] . '#msg' . $message['ID_MSG'] . '">' . $message['subject'] . '</a>',
		'member' => &$memberContext[$message['ID_MEMBER']],
		'icon' => $message['icon'],
		'icon_url' => $settings[$context['icon_sources'][$message['icon']]] . '/post/' . $message['icon'] . '.gif',
		'subject' => $message['subject'],
		'time' => timeformat($message['posterTime']),
		'timestamp' => forum_time(true, $message['posterTime']),
		'counter' => $counter,
		'modified' => array(
			'time' => timeformat($message['modifiedTime']),
			'timestamp' => forum_time(true, $message['modifiedTime']),
			'name' => $message['modifiedName']
		),
		'body' => $message['body'],
		'new' => empty($message['isRead']),
		'first_new' => isset($context['start_from']) && $context['start_from'] == $counter,
		'can_modify' => (!$context['is_locked'] || allowedTo('moderate_board')) && (allowedTo('modify_any') || (allowedTo('modify_replies') && $context['user']['started']) || (allowedTo('modify_own') && $message['ID_MEMBER'] == $ID_MEMBER && (empty($modSettings['edit_disable_time']) || $message['posterTime'] + $modSettings['edit_disable_time'] * 60 > time()))),
		'can_remove' => allowedTo('delete_any') || (allowedTo('delete_replies') && $context['user']['started']) || (allowedTo('delete_own') && $message['ID_MEMBER'] == $ID_MEMBER && (empty($modSettings['edit_disable_time']) || $message['posterTime'] + $modSettings['edit_disable_time'] * 60 > time())),
		'can_see_ip' => allowedTo('moderate_forum') || ($message['ID_MEMBER'] == $ID_MEMBER && !empty($ID_MEMBER)),
	);

	if (empty($options['view_newest_first']))
		$counter++;
	else
		$counter--;

	return $output;
}

// Download an attachment.
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context, $sourcedir, $topic;

	$context['no_last_modified'] = true;

	// Make sure some attachment was requested!
	if (!isset($_REQUEST['attach']) && !isset($_REQUEST['id']))
		fatal_lang_error(1, false);

	$_REQUEST['attach'] = isset($_REQUEST['attach']) ? (int) $_REQUEST['attach'] : (int) $_REQUEST['id'];

	if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'avatar')
	{
		$request = db_query("
			SELECT filename, ID_ATTACH, attachmentType, file_hash
			FROM {$db_prefix}attachments
			WHERE ID_ATTACH = $_REQUEST[attach]
				AND ID_MEMBER > 0
			LIMIT 1", __FILE__, __LINE__);
		$_REQUEST['image'] = true;
	}
	// This is just a regular attachment...
	else
	{
		// This checks only the current board for $board/$topic's permissions.
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
		// NOTE: We must verify that $topic is the attachment's topic, or else the permission check above is broken.
		$request = db_query("
			SELECT a.filename, a.ID_ATTACH, a.attachmentType, a.file_hash
			FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m, {$db_prefix}attachments AS a)
			WHERE b.ID_BOARD = m.ID_BOARD
				AND $user_info[query_see_board]
				AND m.ID_MSG = a.ID_MSG
				AND m.ID_TOPIC = $topic
				AND a.ID_ATTACH = $_REQUEST[attach]
			LIMIT 1", __FILE__, __LINE__);
	}
	if (mysql_num_rows($request) == 0)
		fatal_lang_error(1, false);
	list ($real_filename, $ID_ATTACH, $attachmentType, $file_hash) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Update the download counter (unless it's a thumbnail).
	if ($attachmentType != 3)
		db_query("
			UPDATE LOW_PRIORITY {$db_prefix}attachments
			SET downloads = downloads + 1
			WHERE ID_ATTACH = $ID_ATTACH
			LIMIT 1", __FILE__, __LINE__);

	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], false, $file_hash);

	// This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// No point in a nicer message, because this is supposed to be an attachment anyway...
	if (!file_exists($filename))
	{
		loadLanguage('Errors');

		header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
		header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['attachment_not_found']);
	}

	// If it hasn't been modified since the last time this attachement was retrieved, there's no need to display it again.
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (strtotime($modified_since) >= filemtime($filename))
		{
			ob_end_clean();

			// Answer the question - no, it hasn't been modified ;).
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}

	// Check whether the ETag was sent back, and cache based on that...
	$file_md5 = '"' . md5_file($filename) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $file_md5) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');

	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Set-Cookie:');
	header('Connection: close');
	header('ETag: ' . $file_md5);

	// IE 6 just doesn't play nice. As dirty as this seems, it works.
	if ($context['browser']['is_ie6'] && isset($_REQUEST['image']))
		unset($_REQUEST['image']);

	elseif (filesize($filename) != 0)
	{
		$size = @getimagesize($filename);
		if (!empty($size))
		{
			// What headers are valid?
			$validTypes = array(
				1 => 'gif',
				2 => 'jpeg',
				3 => 'png',
				5 => 'psd',
				6 => 'x-ms-bmp',
				7 => 'tiff',
				8 => 'tiff',
				9 => 'jpeg',
				14 => 'iff',
			);

			// Do we have a mime type we can simpy use?
			if (!empty($size['mime']) && !in_array($size[2], array(4, 13)))
				header('Content-Type: ' . strtr($size['mime'], array('image/bmp' => 'image/x-ms-bmp')));
			elseif (isset($validTypes[$size[2]]))
				header('Content-Type: image/' . $validTypes[$size[2]]);
			// Otherwise - let's think safety first... it might not be an image...
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}
		// Once again - safe!
		elseif (isset($_REQUEST['image']))
			unset($_REQUEST['image']);
	}

	header('Content-Disposition: ' . (isset($_REQUEST['image']) ? 'inline' : 'attachment') . '; filename="' . $real_filename . '"');
	if (!isset($_REQUEST['image']))
		header('Content-Type: application/octet-stream');

	// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
	if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.gif', '.jpg', '.bmp', '.png', 'jpeg', 'tiff')))
		header('Cache-Control: no-cache'); 
   	else
		header('Cache-Control: max-age=' . (525600 * 60) . ', private');

	if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		header('Content-Length: ' . filesize($filename));

	// Try to buy some time...
	@set_time_limit(0);

	// For text files.....
	if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.txt', '.css', '.htm', '.php', '.xml')))
	{
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
			$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r\n", $buffer);');
		elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
			$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r", $buffer);');
		else
			$callback = create_function('$buffer', 'return preg_replace(\'~\r~\', "\r\n", $buffer);');
	}

	// Since we don't do output compression for files this large...
	if (filesize($filename) > 4194304)
	{
		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);
	}
	// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
	elseif (isset($callback) || @readfile($filename) == null)
		echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

	obExit(false);
}

function loadAttachmentContext($ID_MSG)
{
	global $attachments, $modSettings, $txt, $scripturl, $topic, $db_prefix, $sourcedir;

	// Set up the attachment info - based on code by Meriadoc.
	$attachmentData = array();
	if (isset($attachments[$ID_MSG]) && !empty($modSettings['attachmentEnable']))
	{
		foreach ($attachments[$ID_MSG] as $i => $attachment)
		{
			$attachmentData[$i] = array(
				'id' => $attachment['ID_ATTACH'],
				'name' => htmlspecialchars($attachment['filename']),
				'downloads' => $attachment['downloads'],
				'size' => round($attachment['filesize'] / 1024, 2) . ' ' . $txt['smf211'],
				'byte_size' => $attachment['filesize'],
				'href' => $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['ID_ATTACH'],
				'link' => '<a href="' . $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['ID_ATTACH'] . '">' . htmlspecialchars($attachment['filename']) . '</a>',
				'is_image' => !empty($attachment['width']) && !empty($attachment['height']) && !empty($modSettings['attachmentShowImages'])
			);

			if (!$attachmentData[$i]['is_image'])
				continue;

			$attachmentData[$i]['real_width'] = $attachment['width'];
			$attachmentData[$i]['width'] = $attachment['width'];
			$attachmentData[$i]['real_height'] = $attachment['height'];
			$attachmentData[$i]['height'] = $attachment['height'];

			// Let's see, do we want thumbs?
			if (!empty($modSettings['attachmentThumbnails']) && !empty($modSettings['attachmentThumbWidth']) && !empty($modSettings['attachmentThumbHeight']) && ($attachment['width'] > $modSettings['attachmentThumbWidth'] || $attachment['height'] > $modSettings['attachmentThumbHeight']) && strlen($attachment['filename']) < 249)
			{
				// A proper thumb doesn't exist yet? Create one!
				if (empty($attachment['ID_THUMB']) || $attachment['thumb_width'] > $modSettings['attachmentThumbWidth'] || $attachment['thumb_height'] > $modSettings['attachmentThumbHeight'] || ($attachment['thumb_width'] < $modSettings['attachmentThumbWidth'] && $attachment['thumb_height'] < $modSettings['attachmentThumbHeight']))
				{
					$filename = getAttachmentFilename($attachment['filename'], $attachment['ID_ATTACH'], false, $attachment['file_hash']);

					require_once($sourcedir . '/Subs-Graphics.php');
					if (createThumbnail($filename, $modSettings['attachmentThumbWidth'], $modSettings['attachmentThumbHeight']))
					{
						// Calculate the size of the created thumbnail.
						list ($attachment['thumb_width'], $attachment['thumb_height']) = @getimagesize($filename . '_thumb');
						$thumb_size = filesize($filename . '_thumb');

						$thumb_filename = addslashes($attachment['filename'] . '_thumb');

						// Add this beauty to the database.
						$thumb_hash = getAttachmentFilename($thumb_filename, false, true);
						db_query("
							INSERT INTO {$db_prefix}attachments
								(ID_MSG, attachmentType, filename, file_hash, size, width, height)
							VALUES ($ID_MSG, 3, '$thumb_filename', '$thumb_hash', " . (int) $thumb_size . ", " . (int) $attachment['thumb_width'] . ", " . (int) $attachment['thumb_height'] . ")", __FILE__, __LINE__);
						$attachment['ID_THUMB'] = db_insert_id();
						if (!empty($attachment['ID_THUMB']))
						{
							db_query("
								UPDATE {$db_prefix}attachments
								SET ID_THUMB = $attachment[ID_THUMB]
								WHERE ID_ATTACH = $attachment[ID_ATTACH]
								LIMIT 1", __FILE__, __LINE__);

							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['ID_THUMB'], false, $thumb_hash);
							rename($filename . '_thumb', $thumb_realname);
						}
					}
				}

				$attachmentData[$i]['width'] = $attachment['thumb_width'];
				$attachmentData[$i]['height'] = $attachment['thumb_height'];
			}

			if (!empty($attachment['ID_THUMB']))
				$attachmentData[$i]['thumbnail'] = array(
					'id' => $attachment['ID_THUMB'],
					'href' => $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['ID_THUMB'] . ';image',
				);
			$attachmentData[$i]['thumbnail']['has_thumb'] = !empty($attachment['ID_THUMB']);

			// If thumbnails are disabled, check the maximum size of the image.
			if (!$attachmentData[$i]['thumbnail']['has_thumb'] && ((!empty($modSettings['max_image_width']) && $attachment['width'] > $modSettings['max_image_width']) || (!empty($modSettings['max_image_height']) && $attachment['height'] > $modSettings['max_image_height'])))
			{
				if (!empty($modSettings['max_image_width']) && (empty($modSettings['max_image_height']) || $attachment['height'] * $modSettings['max_image_width'] / $attachment['width'] <= $modSettings['max_image_height']))
				{
					$attachmentData[$i]['width'] = $modSettings['max_image_width'];
					$attachmentData[$i]['height'] = floor($attachment['height'] * $modSettings['max_image_width'] / $attachment['width']);
				}
				elseif (!empty($modSettings['max_image_width']))
				{
					$attachmentData[$i]['width'] = floor($attachment['width'] * $modSettings['max_image_height'] / $attachment['height']);
					$attachmentData[$i]['height'] = $modSettings['max_image_height'];
				}
			}
			elseif ($attachmentData[$i]['thumbnail']['has_thumb'])
			{
				// If the image is too large to show inline, make it a popup.
				if (((!empty($modSettings['max_image_width']) && $attachmentData[$i]['real_width'] > $modSettings['max_image_width']) || (!empty($modSettings['max_image_height']) && $attachmentData[$i]['real_height'] > $modSettings['max_image_height'])))
					$attachmentData[$i]['thumbnail']['javascript'] = "return reqWin('" . $attachmentData[$i]['href'] . ";image', " . ($attachment['width'] + 20) . ', ' . ($attachment['height'] + 20) . ', true);';
				else
					$attachmentData[$i]['thumbnail']['javascript'] = 'return expandThumb(' . $attachment['ID_ATTACH'] . ');';
			}

			if (!$attachmentData[$i]['thumbnail']['has_thumb'])
				$attachmentData[$i]['downloads']++;
		}
	}
	return $attachmentData;
}

?>