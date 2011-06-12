<?php
/**********************************************************************************
* Post.php                                                                        *
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

/*	The job of this file is to handle everything related to posting replies,
	new topics, quotes, and modifications to existing posts.  It also handles
	quoting posts by way of javascript.

	void Post()
		- handles showing the post screen, loading the post to be modified, and
		  loading any post quoted.
		- additionally handles previews of posts.
		- uses the Post template and language file, main sub template.
		- allows wireless access using the protocol_post sub template.
		- requires different permissions depending on the actions, but most
		  notably post_new, post_reply_own, and post_reply_any.
		- shows options for the editing and posting of calendar events and
		  attachments, as well as the posting of polls.
		- accessed from ?action=post.

	void Post2()
		- actually posts or saves the message composed with Post().
		- requires various permissions depending on the action.
		- handles attachment, post, and calendar saving.
		- sends off notifications, and allows for announcements and moderation.
		- accessed from ?action=post2.

	void AnnounceTopic()
		- handle the announce topic function (action=announce).
		- checks the topic announcement permissions and loads the announcement
		  template.
		- requires the announce_topic permission.
		- uses the ManageMembers template and Post language file.
		- call the right function based on the sub-action.

	void AnnouncementSelectMembergroup()
		- lets the user select the membergroups that will receive the topic
		  announcement.

	void AnnouncementSend()
		- splits the members to be sent a topic announcement into chunks.
		- composes notification messages in all languages needed.
		- does the actual sending of the topic announcements in chunks.
		- calculates a rough estimate of the percentage items sent.

	void notifyMembersBoard()
		- notifies members who have requested notification for new topics
		  posted on a board of said posts.
		- only sends notifications to those who can *currently* see the topic
		  (it doesn't matter if they could when they requested notification.)
		- loads the Post language file multiple times for each language if the
		  userLanguage setting is set.

	void getTopic()
		- gets a summary of the most recent posts in a topic.
		- depends on the topicSummaryPosts setting.
		- if you are editing a post, only shows posts previous to that post.

	void QuoteFast()
		- loads a post an inserts it into the current editing text box.
		- uses the Post language file.
		- uses special (sadly browser dependent) javascript to parse entities
		  for internationalization reasons.
		- accessed with ?action=quotefast.

	void JavaScriptModify()
		// !!!
*/

function Post()
{
	global $txt, $scripturl, $topic, $db_prefix, $modSettings, $board, $ID_MEMBER;
	global $user_info, $sc, $board_info, $context, $settings, $sourcedir;
	global $options, $func, $language;

	loadLanguage('Post');

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// You can't reply with a poll... hacker.
	if (isset($_REQUEST['poll']) && !empty($topic) && !isset($_REQUEST['msg']))
		unset($_REQUEST['poll']);

	// Posting an event?
	$context['make_event'] = isset($_REQUEST['calendar']);

	// You must be posting to *some* board.
	if (empty($board) && !$context['make_event'])
		fatal_lang_error('smf232', false);

	require_once($sourcedir . '/Subs-Post.php');

	if (isset($_REQUEST['xml']))
	{
		$context['sub_template'] = 'post';

		// Just in case of an earlier error...
		$context['preview_message'] = '';
		$context['preview_subject'] = '';
	}

	// No message is complete without a topic.
	if (empty($topic) && !empty($_REQUEST['msg']))
	{
		$request = db_query("
			SELECT id_topic
			FROM {$db_prefix}messages
			WHERE id_msg = " . (int) $_REQUEST['msg'], __FILE__, __LINE__);
		if (mysql_num_rows($request) != 1)
			unset($_REQUEST['msg'], $_POST['msg'], $_GET['msg']);
		else
			list($topic) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	// Check if it's locked.  It isn't locked if no topic is specified.
	if (!empty($topic))
	{
		$request = db_query("
			SELECT
				t.locked, IFNULL(ln.ID_TOPIC, 0) AS notify, t.isSticky, t.ID_POLL, t.numReplies, mf.ID_MEMBER,
				t.ID_FIRST_MSG, mf.subject, GREATEST(ml.posterTime, ml.modifiedTime) AS lastPostTime
			FROM {$db_prefix}topics AS t
				LEFT JOIN {$db_prefix}log_notify AS ln ON (ln.ID_TOPIC = t.ID_TOPIC AND ln.ID_MEMBER = $ID_MEMBER)
				LEFT JOIN {$db_prefix}messages AS mf ON (mf.ID_MSG = t.ID_FIRST_MSG)
				LEFT JOIN {$db_prefix}messages AS ml ON (ml.ID_MSG = t.ID_LAST_MSG)
			WHERE t.ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);
		list ($locked, $context['notify'], $sticky, $pollID, $context['num_replies'], $ID_MEMBER_POSTER, $ID_FIRST_MSG, $first_subject, $lastPostTime) = mysql_fetch_row($request);
		mysql_free_result($request);

		// If this topic already has a poll, they sure can't add another.
		if (isset($_REQUEST['poll']) && $pollID > 0)
			unset($_REQUEST['poll']);

		if (empty($_REQUEST['msg']))
		{
			if ($user_info['is_guest'] && !allowedTo('post_reply_any'))
				is_not_guest();

			if ($ID_MEMBER_POSTER != $ID_MEMBER)
				isAllowedTo('post_reply_any');
			elseif (!allowedTo('post_reply_any'))
				isAllowedTo('post_reply_own');
		}

		$context['can_lock'] = allowedTo('lock_any') || ($ID_MEMBER == $ID_MEMBER_POSTER && allowedTo('lock_own'));
		$context['can_sticky'] = allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']);

		$context['notify'] = !empty($context['notify']);
		$context['sticky'] = isset($_REQUEST['sticky']) ? !empty($_REQUEST['sticky']) : $sticky;
	}
	else
	{
		if ((!$context['make_event'] || !empty($board)) && (!isset($_REQUEST['poll']) || $modSettings['pollMode'] != '1'))
			isAllowedTo('post_new');

		$locked = 0;
		// !!! These won't work if you're making an event.
		$context['can_lock'] = allowedTo(array('lock_any', 'lock_own'));
		$context['can_sticky'] = allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']);

		$context['notify'] = !empty($context['notify']);
		$context['sticky'] = !empty($_REQUEST['sticky']);
	}

	// !!! These won't work if you're posting an event!
	$context['can_notify'] = allowedTo('mark_any_notify');
	$context['can_move'] = allowedTo('move_any');
	$context['can_announce'] = allowedTo('announce_topic');
	$context['locked'] = !empty($locked) || !empty($_REQUEST['lock']);

	// An array to hold all the attachments for this topic.
	$context['current_attachments'] = array();

	// Don't allow a post if it's locked and you aren't all powerful.
	if ($locked && !allowedTo('moderate_board'))
		fatal_lang_error(90, false);

	// Check the users permissions - is the user allowed to add or post a poll?
	if (isset($_REQUEST['poll']) && $modSettings['pollMode'] == '1')
	{
		// New topic, new poll.
		if (empty($topic))
			isAllowedTo('poll_post');
		// This is an old topic - but it is yours!  Can you add to it?
		elseif ($ID_MEMBER == $ID_MEMBER_POSTER && !allowedTo('poll_add_any'))
			isAllowedTo('poll_add_own');
		// If you're not the owner, can you add to any poll?
		else
			isAllowedTo('poll_add_any');

		// Set up the poll options.
		$context['poll_options'] = array(
			'max_votes' => empty($_POST['poll_max_votes']) ? '1' : max(1, $_POST['poll_max_votes']),
			'hide' => empty($_POST['poll_hide']) ? 0 : $_POST['poll_hide'],
			'expire' => !isset($_POST['poll_expire']) ? '' : $_POST['poll_expire'],
			'change_vote' => isset($_POST['poll_change_vote'])
		);

		// Make all five poll choices empty.
		$context['choices'] = array(
			array('id' => 0, 'number' => 1, 'label' => '', 'is_last' => false),
			array('id' => 1, 'number' => 2, 'label' => '', 'is_last' => false),
			array('id' => 2, 'number' => 3, 'label' => '', 'is_last' => false),
			array('id' => 3, 'number' => 4, 'label' => '', 'is_last' => false),
			array('id' => 4, 'number' => 5, 'label' => '', 'is_last' => true)
		);
	}

	if ($context['make_event'])
	{
		// They might want to pick a board.
		if (!isset($context['current_board']))
			$context['current_board'] = 0;

		// Start loading up the event info.
		$context['event'] = array();
		$context['event']['title'] = isset($_REQUEST['evtitle']) ? htmlspecialchars(stripslashes($_REQUEST['evtitle'])) : '';

		$context['event']['id'] = isset($_REQUEST['eventid']) ? (int) $_REQUEST['eventid'] : -1;
		$context['event']['new'] = $context['event']['id'] == -1;

		// Permissions check!
		isAllowedTo('calendar_post');

		// Editing an event?  (but NOT previewing!?)
		if (!$context['event']['new'] && !isset($_REQUEST['subject']))
		{
			// If the user doesn't have permission to edit the post in this topic, redirect them.
			if ($ID_MEMBER_POSTER != $ID_MEMBER || !allowedTo('modify_own') && !allowedTo('modify_any'))
			{
				require_once($sourcedir . '/Calendar.php');
				return CalendarPost();
			}

			// Get the current event information.
			$request = db_query("
				SELECT
					ID_MEMBER, title, MONTH(startDate) AS month, DAYOFMONTH(startDate) AS day,
					YEAR(startDate) AS year, (TO_DAYS(endDate) - TO_DAYS(startDate)) AS span
				FROM {$db_prefix}calendar
				WHERE ID_EVENT = " . $context['event']['id'] . "
				LIMIT 1", __FILE__, __LINE__);
			$row = mysql_fetch_assoc($request);
			mysql_free_result($request);

			// Make sure the user is allowed to edit this event.
			if ($row['ID_MEMBER'] != $ID_MEMBER)
				isAllowedTo('calendar_edit_any');
			elseif (!allowedTo('calendar_edit_any'))
				isAllowedTo('calendar_edit_own');

			$context['event']['month'] = $row['month'];
			$context['event']['day'] = $row['day'];
			$context['event']['year'] = $row['year'];
			$context['event']['title'] = $row['title'];
			$context['event']['span'] = $row['span'] + 1;
		}
		else
		{
			$today = getdate();

			// You must have a month and year specified!
			if (!isset($_REQUEST['month']))
				$_REQUEST['month'] = $today['mon'];
			if (!isset($_REQUEST['year']))
				$_REQUEST['year'] = $today['year'];

			$context['event']['month'] = (int) $_REQUEST['month'];
			$context['event']['year'] = (int) $_REQUEST['year'];
			$context['event']['day'] = isset($_REQUEST['day']) ? $_REQUEST['day'] : ($_REQUEST['month'] == $today['mon'] ? $today['mday'] : 0);
			$context['event']['span'] = isset($_REQUEST['span']) ? $_REQUEST['span'] : 1;

			// Make sure the year and month are in the valid range.
			if ($context['event']['month'] < 1 || $context['event']['month'] > 12)
				fatal_lang_error('calendar1', false);
			if ($context['event']['year'] < $modSettings['cal_minyear'] || $context['event']['year'] > $modSettings['cal_maxyear'])
				fatal_lang_error('calendar2', false);

			// Get a list of boards they can post in.
			$boards = boardsAllowedTo('post_new');
			if (empty($boards))
				fatal_lang_error('cannot_post_new');
			$request = db_query("
				SELECT c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.childLevel
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
				WHERE $user_info[query_see_board]" . (in_array(0, $boards) ? '' : "
					AND b.ID_BOARD IN (" . implode(', ', $boards) . ")"), __FILE__, __LINE__);
			$context['event']['boards'] = array();
			while ($row = mysql_fetch_assoc($request))
				$context['event']['boards'][] = array(
					'id' => $row['ID_BOARD'],
					'name' => $row['boardName'],
					'childLevel' => $row['childLevel'],
					'prefix' => str_repeat('&nbsp;', $row['childLevel'] * 3),
					'cat' => array(
						'id' => $row['ID_CAT'],
						'name' => $row['catName']
					)
				);
			mysql_free_result($request);
		}

		// Find the last day of the month.
		$context['event']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['event']['month'] == 12 ? 1 : $context['event']['month'] + 1, 0, $context['event']['month'] == 12 ? $context['event']['year'] + 1 : $context['event']['year']));

		$context['event']['board'] = !empty($board) ? $board : $modSettings['cal_defaultboard'];
	}

	if (empty($context['post_errors']))
		$context['post_errors'] = array();

	// See if any new replies have come along.
	if (empty($_REQUEST['msg']) && !empty($topic))
	{
		if (empty($options['no_new_reply_warning']) && isset($_REQUEST['num_replies']))
		{
			$newReplies = $context['num_replies'] > $_REQUEST['num_replies'] ? $context['num_replies'] - $_REQUEST['num_replies'] : 0;

			if (!empty($newReplies))
			{
				if ($newReplies == 1)
					$txt['error_new_reply'] = isset($_GET['num_replies']) ? $txt['error_new_reply_reading'] : $txt['error_new_reply'];
				else
					$txt['error_new_replies'] = sprintf(isset($_GET['num_replies']) ? $txt['error_new_replies_reading'] : $txt['error_new_replies'], $newReplies);

				// If they've come from the display page then we treat the error differently....
				if (isset($_GET['num_replies']))
					$newRepliesError = $newReplies;
				else
					$context['post_error'][$newReplies == 1 ? 'new_reply' : 'new_replies'] = true;

				$modSettings['topicSummaryPosts'] = $newReplies > $modSettings['topicSummaryPosts'] ? max($modSettings['topicSummaryPosts'], 5) : $modSettings['topicSummaryPosts'];
			}
		}
		// Check whether this is a really old post being bumped...
		if (!empty($modSettings['oldTopicDays']) && $lastPostTime + $modSettings['oldTopicDays'] * 86400 < time() && empty($sticky) && !isset($_REQUEST['subject']))
			$oldTopicError = true;
	}

	// Get a response prefix (like 'Re:') in the default forum language.
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

	// Previewing, modifying, or posting?
	if (isset($_REQUEST['message']) || !empty($context['post_error']))
	{
		// Validate inputs.
		if (empty($context['post_error']))
		{
			if ($func['htmltrim']($_REQUEST['subject']) == '')
				$context['post_error']['no_subject'] = true;
			if ($func['htmltrim']($_REQUEST['message']) == '')
				$context['post_error']['no_message'] = true;
			if (!empty($modSettings['max_messageLength']) && $func['strlen']($_REQUEST['message']) > $modSettings['max_messageLength'])
				$context['post_error']['long_message'] = true;

			// Are you... a guest?
			if ($user_info['is_guest'])
			{
				$_REQUEST['guestname'] = !isset($_REQUEST['guestname']) ? '' : trim($_REQUEST['guestname']);
				$_REQUEST['email'] = !isset($_REQUEST['email']) ? '' : trim($_REQUEST['email']);

				// Validate the name and email.
				if (!isset($_REQUEST['guestname']) || trim(strtr($_REQUEST['guestname'], '_', ' ')) == '')
					$context['post_error']['no_name'] = true;
				elseif ($func['strlen']($_REQUEST['guestname']) > 25)
					$context['post_error']['long_name'] = true;
				else
				{
					require_once($sourcedir . '/Subs-Members.php');
					if (isReservedName(htmlspecialchars($_REQUEST['guestname']), 0, true, false))
					{

						$context['post_error']['bad_name'] = true;
					}
				}

				if (empty($modSettings['guest_post_no_email']))
				{
					if (!isset($_REQUEST['email']) || $_REQUEST['email'] == '')
						$context['post_error']['no_email'] = true;
					elseif (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_REQUEST['email'])) == 0)
						$context['post_error']['bad_email'] = true;
				}
			}

			// This is self explanatory - got any questions?
			if (isset($_REQUEST['question']) && trim($_REQUEST['question']) == '')
				$context['post_error']['no_question'] = true;

			// This means they didn't click Post and get an error.
			$really_previewing = true;
		}
		else
		{
			if (!isset($_REQUEST['subject']))
				$_REQUEST['subject'] = '';
			if (!isset($_REQUEST['message']))
				$_REQUEST['message'] = '';
			if (!isset($_REQUEST['icon']))
				$_REQUEST['icon'] = 'xx';

			$really_previewing = false;
		}

		// Set up the inputs for the form.
		$form_subject = strtr($func['htmlspecialchars'](stripslashes($_REQUEST['subject'])), array("\r" => '', "\n" => '', "\t" => ''));
		$form_message = $func['htmlspecialchars'](stripslashes($_REQUEST['message']), ENT_QUOTES);

		// Make sure the subject isn't too long - taking into account special characters.
		if ($func['strlen']($form_subject) > 100)
			$form_subject = $func['substr']($form_subject, 0, 100);

		// Have we inadvertently trimmed off the subject of useful information?
		if ($func['htmltrim']($form_subject) === '')
			$context['post_error']['no_subject'] = true;

		// Any errors occurred?
		if (!empty($context['post_error']))
		{
			loadLanguage('Errors');

			$context['error_type'] = 'minor';

			$context['post_error']['messages'] = array();
			foreach ($context['post_error'] as $post_error => $dummy)
			{
				if ($post_error == 'messages')
					continue;

				$context['post_error']['messages'][] = $txt['error_' . $post_error];

				// If it's not a minor error flag it as such.
				if (!in_array($post_error, array('new_reply', 'new_replies', 'old_topic')))
					$context['error_type'] = 'serious';
			}
		}

		if (isset($_REQUEST['poll']))
		{
			$context['question'] = isset($_REQUEST['question']) ? $func['htmlspecialchars'](stripslashes(trim($_REQUEST['question']))) : '';

			$context['choices'] = array();
			$choice_id = 0;

			$_POST['options'] = empty($_POST['options']) ? array() : htmlspecialchars__recursive(stripslashes__recursive($_POST['options']));
			foreach ($_POST['options'] as $option)
			{
				if (trim($option) == '')
					continue;

				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => $option,
					'is_last' => false
				);
			}

			if (count($context['choices']) < 2)
			{
				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => '',
					'is_last' => false
				);
				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => '',
					'is_last' => false
				);
			}
			$context['choices'][count($context['choices']) - 1]['is_last'] = true;
		}

		// Are you... a guest?
		if ($user_info['is_guest'])
		{
			$_REQUEST['guestname'] = !isset($_REQUEST['guestname']) ? '' : trim($_REQUEST['guestname']);
			$_REQUEST['email'] = !isset($_REQUEST['email']) ? '' : trim($_REQUEST['email']);

			$_REQUEST['guestname'] = htmlspecialchars($_REQUEST['guestname']);
			$context['name'] = $_REQUEST['guestname'];
			$_REQUEST['email'] = htmlspecialchars($_REQUEST['email']);
			$context['email'] = $_REQUEST['email'];

			$user_info['name'] = $_REQUEST['guestname'];
		}

		// Only show the preview stuff if they hit Preview.
		if ($really_previewing == true || isset($_REQUEST['xml']))
		{
			// Set up the preview message and subject and censor them...
			$context['preview_message'] = $form_message;
			preparsecode($form_message, true);
			preparsecode($context['preview_message']);

			// Do all bulletin board code tags, with or without smileys.
			$context['preview_message'] = parse_bbc($context['preview_message'], isset($_REQUEST['ns']) ? 0 : 1);

			if ($form_subject != '')
			{
				$context['preview_subject'] = $form_subject;

				censorText($context['preview_subject']);
				censorText($context['preview_message']);
			}
			else
				$context['preview_subject'] = '<i>' . $txt[24] . '</i>';

			// Protect any CDATA blocks.
			if (isset($_REQUEST['xml']))
				$context['preview_message'] = strtr($context['preview_message'], array(']]>' => ']]]]><![CDATA[>'));
		}

		// Set up the checkboxes.
		$context['notify'] = !empty($_REQUEST['notify']);
		$context['use_smileys'] = !isset($_REQUEST['ns']);

		$context['icon'] = isset($_REQUEST['icon']) ? preg_replace('~[\./\\\\*\':"<>]~', '', $_REQUEST['icon']) : 'xx';

		// Set the destination action for submission.
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . (isset($_REQUEST['msg']) ? ';msg=' . $_REQUEST['msg'] . ';sesc=' . $sc : '') . (isset($_REQUEST['poll']) ? ';poll' : '');
		$context['submit_label'] = isset($_REQUEST['msg']) ? $txt[10] : $txt[105];

		// Previewing an edit?
		if (isset($_REQUEST['msg']) && !empty($topic))
		{
			// Get the existing message.
			$request = db_query("
				SELECT
					m.ID_MEMBER, m.modifiedTime, m.smileysEnabled, m.body,
					m.posterName, m.posterEmail, m.subject, m.icon,
					IFNULL(a.size, -1) AS filesize, a.filename, a.ID_ATTACH,
					t.ID_MEMBER_STARTED AS ID_MEMBER_POSTER, m.posterTime
				FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
					LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MSG = m.ID_MSG AND a.attachmentType = 0)
				WHERE m.ID_MSG = " . (int) $_REQUEST['msg'] . "
					AND m.ID_TOPIC = $topic
					AND t.ID_TOPIC = $topic", __FILE__, __LINE__);
			// The message they were trying to edit was most likely deleted.
			// !!! Change this error message?
			if (mysql_num_rows($request) == 0)
				fatal_lang_error('smf232', false);
			$row = mysql_fetch_assoc($request);
	
			$attachment_stuff = array($row);
			while ($row2 = mysql_fetch_assoc($request))
				$attachment_stuff[] = $row2;
			mysql_free_result($request);

			if ($row['ID_MEMBER'] == $ID_MEMBER && !allowedTo('modify_any'))
			{
				// Give an extra five minutes over the disable time threshold, so they can type.
				if (!empty($modSettings['edit_disable_time']) && $row['posterTime'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
					fatal_lang_error('modify_post_time_passed', false);
				elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_own'))
					isAllowedTo('modify_replies');
				else
					isAllowedTo('modify_own');
			}
			elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_any'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_any');

			if (!empty($modSettings['attachmentEnable']))
			{
				$request = db_query("
					SELECT IFNULL(size, -1) AS filesize, filename, ID_ATTACH
					FROM {$db_prefix}attachments
					WHERE ID_MSG = " . (int) $_REQUEST['msg'] . "
						 AND attachmentType = 0", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
				{
					if ($row['filesize'] <= 0)
						continue;
					$context['current_attachments'][] = array(
						'name' => htmlspecialchars($row['filename']),
						'id' => $row['ID_ATTACH']
					);
				}
				mysql_free_result($request);
			}

			// Allow moderators to change names....
			if (allowedTo('moderate_forum') && !empty($topic))
			{
				$request = db_query("
					SELECT ID_MEMBER, posterName, posterEmail
					FROM {$db_prefix}messages
					WHERE ID_MSG = " . (int) $_REQUEST['msg'] . "
						AND ID_TOPIC = $topic
					LIMIT 1", __FILE__, __LINE__);
				$row = mysql_fetch_assoc($request);
				mysql_free_result($request);

				if (empty($row['ID_MEMBER']))
				{
					$context['name'] = htmlspecialchars($row['posterName']);
					$context['email'] = htmlspecialchars($row['posterEmail']);
				}
			}
		}

		// No check is needed, since nothing is really posted.
		checkSubmitOnce('free');
	}
	// Editing a message...
	elseif (isset($_REQUEST['msg']) && !empty($topic))
	{
		checkSession('get');

		// Get the existing message.
		$request = db_query("
			SELECT
				m.ID_MEMBER, m.modifiedTime, m.smileysEnabled, m.body,
				m.posterName, m.posterEmail, m.subject, m.icon,
				IFNULL(a.size, -1) AS filesize, a.filename, a.ID_ATTACH,
				t.ID_MEMBER_STARTED AS ID_MEMBER_POSTER, m.posterTime
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
				LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MSG = m.ID_MSG AND a.attachmentType = 0)
			WHERE m.ID_MSG = " . (int) $_REQUEST['msg'] . "
				AND m.ID_TOPIC = $topic
				AND t.ID_TOPIC = $topic", __FILE__, __LINE__);
		// The message they were trying to edit was most likely deleted.
		// !!! Change this error message?
		if (mysql_num_rows($request) == 0)
			fatal_lang_error('smf232', false);
		$row = mysql_fetch_assoc($request);

		$attachment_stuff = array($row);
		while ($row2 = mysql_fetch_assoc($request))
			$attachment_stuff[] = $row2;
		mysql_free_result($request);

		if ($row['ID_MEMBER'] == $ID_MEMBER && !allowedTo('modify_any'))
		{
			// Give an extra five minutes over the disable time threshold, so they can type.
			if (!empty($modSettings['edit_disable_time']) && $row['posterTime'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_any'))
			isAllowedTo('modify_replies');
		else
			isAllowedTo('modify_any');

		// When was it last modified?
		if (!empty($row['modifiedTime']))
			$context['last_modified'] = timeformat($row['modifiedTime']);

		// Get the stuff ready for the form.
		$form_subject = $row['subject'];
		$form_message = un_preparsecode($row['body']);
		censorText($form_message);
		censorText($form_subject);

		// Check the boxes that should be checked.
		$context['use_smileys'] = !empty($row['smileysEnabled']);
		$context['icon'] = $row['icon'];

		// Load up 'em attachments!
		foreach ($attachment_stuff as $attachment)
		{
			if ($attachment['filesize'] >= 0 && !empty($modSettings['attachmentEnable']))
				$context['current_attachments'][] = array(
					'name' => htmlspecialchars($attachment['filename']),
					'id' => $attachment['ID_ATTACH']
				);
		}

		// Allow moderators to change names....
		if (allowedTo('moderate_forum') && empty($row['ID_MEMBER']))
		{
			$context['name'] = htmlspecialchars($row['posterName']);
			$context['email'] = htmlspecialchars($row['posterEmail']);
		}

		// Set the destinaton.
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . ';msg=' . $_REQUEST['msg'] . ';sesc=' . $sc . (isset($_REQUEST['poll']) ? ';poll' : '');
		$context['submit_label'] = $txt[10];
	}
	// Posting...
	else
	{
		// By default....
		$context['use_smileys'] = true;
		$context['icon'] = 'xx';

		if ($user_info['is_guest'])
		{
			$context['name'] = '';
			$context['email'] = '';
		}
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . (isset($_REQUEST['poll']) ? ';poll' : '');

		$context['submit_label'] = $txt[105];

		// Posting a quoted reply?
		if (!empty($topic) && !empty($_REQUEST['quote']))
		{
			checkSession('get');

			// Make sure they _can_ quote this post, and if so get it.
			$request = db_query("
				SELECT m.subject, IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime, m.body
				FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
					LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
				WHERE m.ID_MSG = " . (int) $_REQUEST['quote'] . "
					AND b.ID_BOARD = m.ID_BOARD
					AND $user_info[query_see_board]
				LIMIT 1", __FILE__, __LINE__);
			if (mysql_num_rows($request) == 0)
				fatal_lang_error('quoted_post_deleted', false);
			list ($form_subject, $mname, $mdate, $form_message) = mysql_fetch_row($request);
			mysql_free_result($request);

			// Add 'Re: ' to the front of the quoted subject.
			if (trim($context['response_prefix']) != '' && $func['strpos']($form_subject, trim($context['response_prefix'])) !== 0)
				$form_subject = $context['response_prefix'] . $form_subject;

			// Censor the message and subject.
			censorText($form_message);
			censorText($form_subject);

			$form_message = preg_replace('~<br(?: /)?' . '>~i', "\n", $form_message);

			// Remove any nested quotes, if necessary.
			if (!empty($modSettings['removeNestedQuotes']))
				$form_message = preg_replace(array('~\n?\[quote.*?\].+?\[/quote\]\n?~is', '~^\n~', '~\[/quote\]~'), '', $form_message);

			// Add a quote string on the front and end.
			$form_message = '[quote author=' . $mname . ' link=topic=' . $topic . '.msg' . (int) $_REQUEST['quote'] . '#msg' . (int) $_REQUEST['quote'] . ' date=' . $mdate . ']' . "\n" . $form_message . "\n" . '[/quote]';
		}
		// Posting a reply without a quote?
		elseif (!empty($topic) && empty($_REQUEST['quote']))
		{
			// Get the first message's subject.
			$form_subject = $first_subject;

			// Add 'Re: ' to the front of the subject.
			if (trim($context['response_prefix']) != '' && $form_subject != '' && $func['strpos']($form_subject, trim($context['response_prefix'])) !== 0)
				$form_subject = $context['response_prefix'] . $form_subject;

			// Censor the subject.
			censorText($form_subject);

			$form_message = '';
		}
		else
		{
			$form_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
			$form_message = '';
		}
	}

	// !!! This won't work if you're posting an event.
	if (allowedTo('post_attachment'))
	{
		if (empty($_SESSION['temp_attachments']))
			$_SESSION['temp_attachments'] = array();

		// If this isn't a new post, check the current attachments.
		if (isset($_REQUEST['msg']))
		{
			$request = db_query("
				SELECT COUNT(*), SUM(size)
				FROM {$db_prefix}attachments
				WHERE ID_MSG = " . (int) $_REQUEST['msg'] . "
					AND attachmentType = 0", __FILE__, __LINE__);
			list ($quantity, $total_size) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
		{
			$quantity = 0;
			$total_size = 0;
		}

		$temp_start = 0;

		if (!empty($_SESSION['temp_attachments']))
			foreach ($_SESSION['temp_attachments'] as $attachID => $name)
			{
				$temp_start++;

				if (preg_match('~^post_tmp_' . $ID_MEMBER . '_\d+$~', $attachID) == 0)
				{
					unset($_SESSION['temp_attachments'][$attachID]);
					continue;
				}

				if (!empty($_POST['attach_del']) && !in_array($attachID, $_POST['attach_del']))
				{
					$deleted_attachments = true;
					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($modSettings['attachmentUploadDir'] . '/' . $attachID);
					continue;
				}

				$quantity++;
				$total_size += filesize($modSettings['attachmentUploadDir'] . '/' . $attachID);

				$context['current_attachments'][] = array(
					'name' => $name,
					'id' => $attachID
				);
			}

		if (!empty($_POST['attach_del']))
		{
			$del_temp = array();
			foreach ($_POST['attach_del'] as $i => $dummy)
				$del_temp[$i] = (int) $dummy;

			foreach ($context['current_attachments'] as $k => $dummy)
				if (!in_array($dummy['id'], $del_temp))
				{
					$context['current_attachments'][$k]['unchecked'] = true;
					$deleted_attachments = !isset($deleted_attachments) || is_bool($deleted_attachments) ? 1 : $deleted_attachments + 1;
					$quantity--;
				}
		}

		if (!empty($_FILES['attachment']))
			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if ($_FILES['attachment']['name'][$n] == '')
					continue;

				if (!is_uploaded_file($_FILES['attachment']['tmp_name'][$n]) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['attachment']['tmp_name'][$n])))
					fatal_lang_error('smf124');

				if (!empty($modSettings['attachmentSizeLimit']) && $_FILES['attachment']['size'][$n] > $modSettings['attachmentSizeLimit'] * 1024)
					fatal_lang_error('smf122', false, array($modSettings['attachmentSizeLimit']));

				$quantity++;
				if (!empty($modSettings['attachmentNumPerPostLimit']) && $quantity > $modSettings['attachmentNumPerPostLimit'])
					fatal_lang_error('attachments_limit_per_post', false, array($modSettings['attachmentNumPerPostLimit']));

				$total_size += $_FILES['attachment']['size'][$n];
				if (!empty($modSettings['attachmentPostLimit']) && $total_size > $modSettings['attachmentPostLimit'] * 1024)
					fatal_lang_error('smf122', false, array($modSettings['attachmentPostLimit']));

				if (!empty($modSettings['attachmentCheckExtensions']))
				{
					if (!in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($modSettings['attachmentExtensions']))))
						fatal_error($_FILES['attachment']['name'][$n] . '.<br />' . $txt['smf123'] . ' ' . $modSettings['attachmentExtensions'] . '.', false);
				}

				if (!empty($modSettings['attachmentDirSizeLimit']))
				{
					// Make sure the directory isn't full.
					$dirSize = 0;
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

						$dirSize += filesize($modSettings['attachmentUploadDir'] . '/' . $file);
					}
					closedir($dir);

					// Too big!  Maybe you could zip it or something...
					if ($_FILES['attachment']['size'][$n] + $dirSize > $modSettings['attachmentDirSizeLimit'] * 1024)
						fatal_lang_error('smf126');
				}

				if (!is_writable($modSettings['attachmentUploadDir']))
					fatal_lang_error('attachments_no_write');

				$attachID = 'post_tmp_' . $ID_MEMBER . '_' . $temp_start++;
				$_SESSION['temp_attachments'][$attachID] = stripslashes(basename($_FILES['attachment']['name'][$n]));
				$context['current_attachments'][] = array(
					'name' => basename(stripslashes($_FILES['attachment']['name'][$n])),
					'id' => $attachID
				);

				$destName = $modSettings['attachmentUploadDir'] . '/' . $attachID;

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $destName))
					fatal_lang_error('smf124');
				@chmod($destName, 0644);
			}
	}

	// If we are coming here to make a reply, and someone has already replied... make a special warning message.
	if (isset($newRepliesError))
	{
		$context['post_error']['messages'][] = $newRepliesError == 1 ? $txt['error_new_reply'] : $txt['error_new_replies'];
		$context['error_type'] = 'minor';
	}

	if (isset($oldTopicError))
	{
		$context['post_error']['messages'][] = $txt['error_old_topic'];
		$context['error_type'] = 'minor';
	}

	// What are you doing?  Posting a poll, modifying, previewing, new post, or reply...
	if (isset($_REQUEST['poll']))
		$context['page_title'] = $txt['smf20'];
	elseif ($context['make_event'])
		$context['page_title'] = $context['event']['id'] == -1 ? $txt['calendar23'] : $txt['calendar20'];
	elseif (isset($_REQUEST['msg']))
		$context['page_title'] = $txt[66];
	elseif (isset($_REQUEST['subject'], $context['preview_subject']))
		$context['page_title'] = $txt[507] . ' - ' . strip_tags($context['preview_subject']);
	elseif (empty($topic))
		$context['page_title'] = $txt[33];
	else
		$context['page_title'] = $txt[25];

	// Build the link tree.
	if (empty($topic))
		$context['linktree'][] = array(
			'name' => '<i>' . $txt[33] . '</i>'
		);
	else
		$context['linktree'][] = array(
			'url' => $scripturl . '?topic=' . $topic . '.' . $_REQUEST['start'],
			'name' => $form_subject,
			'extra_before' => '<span' . ($settings['linktree_inline'] ? ' class="smalltext"' : '') . '><b class="nav">' . $context['page_title'] . ' ( </b></span>',
			'extra_after' => '<span' . ($settings['linktree_inline'] ? ' class="smalltext"' : '') . '><b class="nav"> )</b></span>'
		);

	// If they've unchecked an attachment, they may still want to attach that many more files, but don't allow more than num_allowed_attachments.
	// !!! This won't work if you're posting an event.
	$context['num_allowed_attachments'] = min($modSettings['attachmentNumPerPostLimit'] - count($context['current_attachments']) + (isset($deleted_attachments) ? $deleted_attachments : 0), $modSettings['attachmentNumPerPostLimit']);
	$context['can_post_attachment'] = !empty($modSettings['attachmentEnable']) && $modSettings['attachmentEnable'] == 1 && allowedTo('post_attachment') && $context['num_allowed_attachments'] > 0;

	$context['subject'] = addcslashes($form_subject, '"');
	$context['message'] = str_replace(array('"', '<', '>', '  '), array('&quot;', '&lt;', '&gt;', ' &nbsp;'), $form_message);
	$context['attached'] = '';
	$context['allowed_extensions'] = strtr($modSettings['attachmentExtensions'], array(',' => ', '));
	$context['make_poll'] = isset($_REQUEST['poll']);

	// Message icons - customized icons are off?
	if (empty($modSettings['messageIcons_enable']))
	{
		$context['icons'] = array(
			array('value' => 'xx', 'name' => $txt[281]),
			array('value' => 'thumbup', 'name' => $txt[282]),
			array('value' => 'thumbdown', 'name' => $txt[283]),
			array('value' => 'exclamation', 'name' => $txt[284]),
			array('value' => 'question', 'name' => $txt[285]),
			array('value' => 'lamp', 'name' => $txt[286]),
			array('value' => 'smiley', 'name' => $txt[287]),
			array('value' => 'angry', 'name' => $txt[288]),
			array('value' => 'cheesy', 'name' => $txt[289]),
			array('value' => 'grin', 'name' => $txt[293]),
			array('value' => 'sad', 'name' => $txt[291]),
			array('value' => 'wink', 'name' => $txt[292])
		);

		foreach ($context['icons'] as $k => $dummy)
		{
			$context['icons'][$k]['url'] = $settings['images_url'] . '/post/' . $dummy['value'] . '.gif';
			$context['icons'][$k]['is_last'] = false;
		}

		$context['icon_url'] = $settings['images_url'] . '/post/' . $context['icon'] . '.gif';
	}
	// Otherwise load the icons, and check we give the right image too...
	else
	{
		// Regardless of what *should* exist, let's do this properly.
		$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
		$context['icon_sources'] = array();
		foreach ($stable_icons as $icon)
			$context['icon_sources'][$icon] = 'images_url';

		// Array for all icons that need to revert to the default theme!
		$context['javascript_icons'] = array();

		if (($temp = cache_get_data('posting_icons-' . $board, 480)) == null)
		{
			$request = db_query("
				SELECT title, filename
				FROM {$db_prefix}message_icons
				WHERE ID_BOARD IN (0, $board)", __FILE__, __LINE__);
			$icon_data = array();
			while ($row = mysql_fetch_assoc($request))
				$icon_data[] = $row;
			mysql_free_result($request);

			cache_put_data('posting_icons-' . $board, $icon_data, 480);
		}
		else
			$icon_data = $temp;

		$context['icons'] = array();
		foreach ($icon_data as $icon)
		{
			if (!isset($context['icon_sources'][$icon['filename']]))
				$context['icon_sources'][$icon['filename']] = file_exists($settings['theme_dir'] . '/images/post/' . $icon['filename'] . '.gif') ? 'images_url' : 'default_images_url';

			// If the icon exists only in the default theme, ensure the javascript popup respects this.
			if ($context['icon_sources'][$icon['filename']] == 'default_images_url')
				$context['javascript_icons'][] = $icon['filename'];

			$context['icons'][] = array(
				'value' => $icon['filename'],
				'name' => $icon['title'],
				'url' => $settings[$context['icon_sources'][$icon['filename']]] . '/post/' . $icon['filename'] . '.gif',
				'is_last' => false,
			);
		}

		$context['icon_url'] = $settings[isset($context['icon_sources'][$context['icon']]) ? $context['icon_sources'][$context['icon']] : 'images_url'] . '/post/' . $context['icon'] . '.gif';
	}

	if (!empty($context['icons']))
		$context['icons'][count($context['icons']) - 1]['is_last'] = true;

	$found = false;
	for ($i = 0, $n = count($context['icons']); $i < $n; $i++)
	{
		$context['icons'][$i]['selected'] = $context['icon'] == $context['icons'][$i]['value'];
		if ($context['icons'][$i]['selected'])
			$found = true;
	}
	if (!$found)
		array_unshift($context['icons'], array(
			'value' => $context['icon'],
			'name' => $txt['current_icon'],
			'url' => $context['icon_url'],
			'is_last' => empty($context['icons']),
			'selected' => true,
		));

	if (!empty($topic))
		getTopic();

	$context['back_to_topic'] = isset($_REQUEST['goback']) || (isset($_REQUEST['msg']) && !isset($_REQUEST['subject']));
	$context['show_additional_options'] = !empty($_POST['additional_options']) || !empty($_SESSION['temp_attachments']) || !empty($deleted_attachments);

	$context['is_new_topic'] = empty($topic);
	$context['is_new_post'] = !isset($_REQUEST['msg']);
	$context['is_first_post'] = $context['is_new_topic'] || (isset($_REQUEST['msg']) && $_REQUEST['msg'] == $ID_FIRST_MSG);

	// Register this form in the session variables.
	checkSubmitOnce('register');

	// Finally, load the template.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_post';
	elseif (!isset($_REQUEST['xml']))
		loadTemplate('Post');
}

function Post2()
{
	global $board, $topic, $txt, $db_prefix, $modSettings, $sourcedir, $context;
	global $ID_MEMBER, $user_info, $board_info, $options, $func;

	// Previewing? Go back to start.
	if (isset($_REQUEST['preview']))
		return Post();

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// No errors as yet.
	$post_errors = array();

	// If the session has timed out, let the user re-submit their form.
	if (checkSession('post', '', false) != '')
		$post_errors[] = 'session_timeout';

	require_once($sourcedir . '/Subs-Post.php');
	loadLanguage('Post');

	// Replying to a topic?
	if (!empty($topic) && !isset($_REQUEST['msg']))
	{
		$request = db_query("
			SELECT t.locked, t.isSticky, t.ID_POLL, t.numReplies, m.ID_MEMBER
			FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
			WHERE t.ID_TOPIC = $topic
				AND m.ID_MSG = t.ID_FIRST_MSG
			LIMIT 1", __FILE__, __LINE__);
		list ($tmplocked, $tmpstickied, $pollID, $numReplies, $ID_MEMBER_POSTER) = mysql_fetch_row($request);
		mysql_free_result($request);

		// Don't allow a post if it's locked.
		if ($tmplocked != 0 && !allowedTo('moderate_board'))
			fatal_lang_error(90, false);

		// Sorry, multiple polls aren't allowed... yet.  You should stop giving me ideas :P.
		if (isset($_REQUEST['poll']) && $pollID > 0)
			unset($_REQUEST['poll']);

		if ($ID_MEMBER_POSTER != $ID_MEMBER)
			isAllowedTo('post_reply_any');
		elseif (!allowedTo('post_reply_any'))
			isAllowedTo('post_reply_own');

		if (isset($_POST['lock']))
		{
			// Nothing is changed to the lock.
			if ((empty($tmplocked) && empty($_POST['lock'])) || (!empty($_POST['lock']) && !empty($tmplocked)))
				unset($_POST['lock']);
			// You're have no permission to lock this topic.
			elseif (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $ID_MEMBER != $ID_MEMBER_POSTER))
				unset($_POST['lock']);
			// You are allowed to (un)lock your own topic only.
			elseif (!allowedTo('lock_any'))
			{
				// You cannot override a moderator lock.
				if ($tmplocked == 1)
					unset($_POST['lock']);
				else
					$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
			}
			// Hail mighty moderator, (un)lock this topic immediately.
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
		}

		// So you wanna (un)sticky this...let's see.
		if (isset($_POST['sticky']) && (empty($modSettings['enableStickyTopics']) || $_POST['sticky'] == $tmpstickied || !allowedTo('make_sticky')))
			unset($_POST['sticky']);

		// If the number of replies has changed, if the setting is enabled, go back to Post() - which handles the error.
		$newReplies = isset($_POST['num_replies']) && $numReplies > $_POST['num_replies'] ? $numReplies - $_POST['num_replies'] : 0;
		if (empty($options['no_new_reply_warning']) && !empty($newReplies))
		{
			$_REQUEST['preview'] = true;
			return Post();
		}

		$posterIsGuest = $user_info['is_guest'];
	}

	// Posting a new topic.
	elseif (empty($topic))
	{
		// Now don't be silly, new topics will get their own id_msg soon enough.
		unset($_REQUEST['msg'], $_POST['msg'], $_GET['msg']);

		if (!isset($_REQUEST['poll']) || $modSettings['pollMode'] != '1')
			isAllowedTo('post_new');

		if (isset($_POST['lock']))
		{
			// New topics are by default not locked.
			if (empty($_POST['lock']))
				unset($_POST['lock']);
			// Besides, you need permission.
			elseif (!allowedTo(array('lock_any', 'lock_own')))
				unset($_POST['lock']);
			// A moderator-lock (1) can override a user-lock (2).
			else
				$_POST['lock'] = allowedTo('lock_any') ? 1 : 2;
		}

		if (isset($_POST['sticky']) && (empty($modSettings['enableStickyTopics']) || empty($_POST['sticky']) || !allowedTo('make_sticky')))
			unset($_POST['sticky']);

		$posterIsGuest = $user_info['is_guest'];
	}

	// Modifying an existing message?
	elseif (isset($_REQUEST['msg']) && !empty($topic))
	{
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		$request = db_query("
			SELECT
				m.ID_MEMBER, m.posterName, m.posterEmail, m.posterTime, 
				t.ID_FIRST_MSG, t.locked, t.isSticky, t.ID_MEMBER_STARTED AS ID_MEMBER_POSTER
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
			WHERE m.ID_MSG = $_REQUEST[msg]
				AND t.ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) == 0)
			fatal_lang_error('smf272', false);
		$row = mysql_fetch_assoc($request);
		mysql_free_result($request);

		if (!empty($row['locked']) && !allowedTo('moderate_board'))
			fatal_lang_error(90, false);

		if (isset($_POST['lock']))
		{
			// Nothing changes to the lock status.
			if ((empty($_POST['lock']) && empty($row['locked'])) || (!empty($_POST['lock']) && !empty($row['locked'])))
				unset($_POST['lock']);
			// You're simply not allowed to (un)lock this.
			elseif (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $ID_MEMBER != $row['ID_MEMBER_POSTER']))
				unset($_POST['lock']);
			// You're only allowed to lock your own topics.
			elseif (!allowedTo('lock_any'))
			{
				// You're not allowed to break a moderator's lock.
				if ($row['locked'] == 1)
					unset($_POST['lock']);
				// Lock it with a soft lock or unlock it.
				else
					$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
			}
			// You must be the moderator.
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
		}

		// Change the sticky status of this topic?
		if (isset($_POST['sticky']) && (!allowedTo('make_sticky') || $_POST['sticky'] == $row['isSticky']))
			unset($_POST['sticky']);

		if ($row['ID_MEMBER'] == $ID_MEMBER && !allowedTo('modify_any'))
		{
			if (!empty($modSettings['edit_disable_time']) && $row['posterTime'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_any'))
		{
			isAllowedTo('modify_replies');

			// If you're modifying a reply, I say it better be logged...
			$moderationAction = true;
		}
		else
		{
			isAllowedTo('modify_any');

			// Log it, assuming you're not modifying your own post.
			if ($row['ID_MEMBER'] != $ID_MEMBER)
				$moderationAction = true;
		}

		$posterIsGuest = empty($row['ID_MEMBER']);

		if (!allowedTo('moderate_forum') || !$posterIsGuest)
		{
			$_POST['guestname'] = addslashes($row['posterName']);
			$_POST['email'] = addslashes($row['posterEmail']);
		}
	}

	// If the poster is a guest evaluate the legality of name and email.
	if ($posterIsGuest)
	{
		$_POST['guestname'] = !isset($_POST['guestname']) ? '' : trim($_POST['guestname']);
		$_POST['email'] = !isset($_POST['email']) ? '' : trim($_POST['email']);

		if ($_POST['guestname'] == '' || $_POST['guestname'] == '_')
			$post_errors[] = 'no_name';
		if ($func['strlen']($_POST['guestname']) > 25)
			$post_errors[] = 'long_name';

		if (empty($modSettings['guest_post_no_email']))
		{
			// Only check if they changed it!
			if (!isset($row) || $row['posterEmail'] != $_POST['email'])
			{
				if (!allowedTo('moderate_forum') && (!isset($_POST['email']) || $_POST['email'] == ''))
					$post_errors[] = 'no_email';
				if (!allowedTo('moderate_forum') && preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_POST['email'])) == 0)
					$post_errors[] = 'bad_email';
			}

			// Now make sure this email address is not banned from posting.
			isBannedEmail($_POST['email'], 'cannot_post', sprintf($txt['you_are_post_banned'], $txt[28]));
		}
	}
	// Check the subject and message.
	if (!isset($_POST['subject']) || $func['htmltrim']($_POST['subject']) === '')
		$post_errors[] = 'no_subject';
	if (!isset($_POST['message']) || $func['htmltrim']($_POST['message']) === '')
		$post_errors[] = 'no_message';
	elseif (!empty($modSettings['max_messageLength']) && $func['strlen']($_POST['message']) > $modSettings['max_messageLength'])
		$post_errors[] = 'long_message';
	else
	{
		// Prepare the message a bit for some additional testing.
		$_POST['message'] = $func['htmlspecialchars']($_POST['message'], ENT_QUOTES);

		// Preparse code. (Zef)
		if ($user_info['is_guest'])
			$user_info['name'] = $_POST['guestname'];
		preparsecode($_POST['message']);

		// Let's see if there's still some content left without the tags.
		if ($func['htmltrim'](strip_tags(parse_bbc($_POST['message'], false), '<img>')) === '')
			$post_errors[] = 'no_message';
	}

	if (isset($_POST['calendar']) && !isset($_REQUEST['deleteevent']) && $func['htmltrim']($_POST['evtitle']) === '')
		$post_errors[] = 'no_event';
	// You are not!
	if (isset($_POST['message']) && strtolower($_POST['message']) == 'i am the administrator.' && !$user_info['is_admin'])
		fatal_error('Knave! Masquerader! Charlatan!', false);

	// Validate the poll...
	if (isset($_REQUEST['poll']) && $modSettings['pollMode'] == '1')
	{
		if (!empty($topic) && !isset($_REQUEST['msg']))
			fatal_lang_error(1, false);

		// This is a new topic... so it's a new poll.
		if (empty($topic))
			isAllowedTo('poll_post');
		// Can you add to your own topics?
		elseif ($ID_MEMBER == $row['ID_MEMBER_POSTER'] && !allowedTo('poll_add_any'))
			isAllowedTo('poll_add_own');
		// Can you add polls to any topic, then?
		else
			isAllowedTo('poll_add_any');

		if (!isset($_POST['question']) || trim($_POST['question']) == '')
			$post_errors[] = 'no_question';

		$_POST['options'] = empty($_POST['options']) ? array() : htmltrim__recursive($_POST['options']);

		// Get rid of empty ones.
		foreach ($_POST['options'] as $k => $option)
			if ($option == '')
				unset($_POST['options'][$k], $_POST['options'][$k]);

		// What are you going to vote between with one choice?!?
		if (count($_POST['options']) < 2)
			$post_errors[] = 'poll_few';
	}

	if ($posterIsGuest)
	{
		// If user is a guest, make sure the chosen name isn't taken.
		require_once($sourcedir . '/Subs-Members.php');
		if (isReservedName($_POST['guestname'], 0, true, false) && (!isset($row['posterName']) || $_POST['guestname'] != $row['posterName']))
			$post_errors[] = 'bad_name';
	}
	// If the user isn't a guest, get his or her name and email.
	elseif (!isset($_REQUEST['msg']))
	{
		$_POST['guestname'] = addslashes($user_info['username']);
		$_POST['email'] = addslashes($user_info['email']);
	}

	// Any mistakes?
	if (!empty($post_errors))
	{
		loadLanguage('Errors');
		// Previewing.
		$_REQUEST['preview'] = true;

		$context['post_error'] = array('messages' => array());
		foreach ($post_errors as $post_error)
		{
			$context['post_error'][$post_error] = true;
			$context['post_error']['messages'][] = $txt['error_' . $post_error];
		}

		return Post();
	}

	// Make sure the user isn't spamming the board.
	if (!isset($_REQUEST['msg']))
		spamProtection('spam');

	// At about this point, we're posting and that's that.
	ignore_user_abort(true);
	@set_time_limit(300);

	// Add special html entities to the subject, name, and email.
	$_POST['subject'] = strtr($func['htmlspecialchars']($_POST['subject']), array("\r" => '', "\n" => '', "\t" => ''));
	$_POST['guestname'] = htmlspecialchars($_POST['guestname']);
	$_POST['email'] = htmlspecialchars($_POST['email']);

	// At this point, we want to make sure the subject isn't too long.
	if ($func['strlen']($_POST['subject']) > 100)
		$_POST['subject'] = addslashes($func['substr'](stripslashes($_POST['subject']), 0, 100));

	// Make the poll...
	if (isset($_REQUEST['poll']))
	{
		// Make sure that the user has not entered a ridiculous number of options..
		if (empty($_POST['poll_max_votes']) || $_POST['poll_max_votes'] <= 0)
			$_POST['poll_max_votes'] = 1;
		elseif ($_POST['poll_max_votes'] > count($_POST['options']))
			$_POST['poll_max_votes'] = count($_POST['options']);
		else
			$_POST['poll_max_votes'] = (int) $_POST['poll_max_votes'];

		// Just set it to zero if it's not there..
		if (!isset($_POST['poll_hide']))
			$_POST['poll_hide'] = 0;
		else
			$_POST['poll_hide'] = (int) $_POST['poll_hide'];
		$_POST['poll_change_vote'] = isset($_POST['poll_change_vote']) ? 1 : 0;

		// If the user tries to set the poll too far in advance, don't let them.
		if (!empty($_POST['poll_expire']) && $_POST['poll_expire'] < 1)
			fatal_lang_error('poll_range_error', false);
		// Don't allow them to select option 2 for hidden results if it's not time limited.
		elseif (empty($_POST['poll_expire']) && $_POST['poll_hide'] == 2)
			$_POST['poll_hide'] = 1;

		// Clean up the question and answers.
		$_POST['question'] = $func['htmlspecialchars']($_POST['question']);
		$_POST['options'] = htmlspecialchars__recursive($_POST['options']);
	}

	// Check if they are trying to delete any current attachments....
	if (isset($_REQUEST['msg'], $_POST['attach_del']) && allowedTo('post_attachment'))
	{
		$del_temp = array();
		foreach ($_POST['attach_del'] as $i => $dummy)
			$del_temp[$i] = (int) $dummy;

		require_once($sourcedir . '/ManageAttachments.php');
		removeAttachments('a.attachmentType = 0 AND a.ID_MSG = ' . (int) $_REQUEST['msg'] . ' AND a.ID_ATTACH NOT IN (' . implode(', ', $del_temp) . ')');
	}

	// ...or attach a new file...
	if (isset($_FILES['attachment']['name']) || !empty($_SESSION['temp_attachments']))
	{
		isAllowedTo('post_attachment');

		// If this isn't a new post, check the current attachments.
		if (isset($_REQUEST['msg']))
		{
			$request = db_query("
				SELECT COUNT(*), SUM(size)
				FROM {$db_prefix}attachments
				WHERE ID_MSG = " . (int) $_REQUEST['msg'] . "
					AND attachmentType = 0", __FILE__, __LINE__);
			list ($quantity, $total_size) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
		{
			$quantity = 0;
			$total_size = 0;
		}

		if (!empty($_SESSION['temp_attachments']))
			foreach ($_SESSION['temp_attachments'] as $attachID => $name)
			{
				if (preg_match('~^post_tmp_' . $ID_MEMBER . '_\d+$~', $attachID) == 0)
					continue;

				if (!empty($_POST['attach_del']) && !in_array($attachID, $_POST['attach_del']))
				{
					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($modSettings['attachmentUploadDir'] . '/' . $attachID);
					continue;
				}

				$_FILES['attachment']['tmp_name'][] = $attachID;
				$_FILES['attachment']['name'][] = addslashes($name);
				$_FILES['attachment']['size'][] = filesize($modSettings['attachmentUploadDir'] . '/' . $attachID);
				list ($_FILES['attachment']['width'][], $_FILES['attachment']['height'][]) = @getimagesize($modSettings['attachmentUploadDir'] . '/' . $attachID);

				unset($_SESSION['temp_attachments'][$attachID]);
			}

		if (!isset($_FILES['attachment']['name']))
			$_FILES['attachment']['tmp_name'] = array();

		$attachIDs = array();
		foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
		{
			if ($_FILES['attachment']['name'][$n] == '')
				continue;

			// Have we reached the maximum number of files we are allowed?
			$quantity++;
			if (!empty($modSettings['attachmentNumPerPostLimit']) && $quantity > $modSettings['attachmentNumPerPostLimit'])
				fatal_lang_error('attachments_limit_per_post', false, array($modSettings['attachmentNumPerPostLimit']));

			// Check the total upload size for this post...
			$total_size += $_FILES['attachment']['size'][$n];
			if (!empty($modSettings['attachmentPostLimit']) && $total_size > $modSettings['attachmentPostLimit'] * 1024)
				fatal_lang_error('smf122', false, array($modSettings['attachmentPostLimit']));

			$attachmentOptions = array(
				'post' => isset($_REQUEST['msg']) ? $_REQUEST['msg'] : 0,
				'poster' => $ID_MEMBER,
				'name' => $_FILES['attachment']['name'][$n],
				'tmp_name' => $_FILES['attachment']['tmp_name'][$n],
				'size' => $_FILES['attachment']['size'][$n],
			);

			if (createAttachment($attachmentOptions))
			{
				$attachIDs[] = $attachmentOptions['id'];
				if (!empty($attachmentOptions['thumb']))
					$attachIDs[] = $attachmentOptions['thumb'];
			}
			else
			{
				if (in_array('could_not_upload', $attachmentOptions['errors']))
					fatal_lang_error('smf124');
				if (in_array('too_large', $attachmentOptions['errors']))
					fatal_lang_error('smf122', false, array($modSettings['attachmentSizeLimit']));
				if (in_array('bad_extension', $attachmentOptions['errors']))
					fatal_error($attachmentOptions['name'] . '.<br />' . $txt['smf123'] . ' ' . $modSettings['attachmentExtensions'] . '.', false);
				if (in_array('directory_full', $attachmentOptions['errors']))
					fatal_lang_error('smf126');
				if (in_array('bad_filename', $attachmentOptions['errors']))
					fatal_error(basename($attachmentOptions['name']) . '.<br />' . $txt['smf130b'] . '.');
				if (in_array('taken_filename', $attachmentOptions['errors']))
					fatal_lang_error('smf125');
			}
		}
	}

	// Make the poll...
	if (isset($_REQUEST['poll']))
	{
		// Create the poll.
		db_query("
			INSERT INTO {$db_prefix}polls
				(question, hideResults, maxVotes, expireTime, ID_MEMBER, posterName, changeVote)
			VALUES (SUBSTRING('$_POST[question]', 1, 255), $_POST[poll_hide], $_POST[poll_max_votes],
				" . (empty($_POST['poll_expire']) ? '0' : time() + $_POST['poll_expire'] * 3600 * 24) . ", $ID_MEMBER, SUBSTRING('$_POST[guestname]', 1, 255), $_POST[poll_change_vote])", __FILE__, __LINE__);
		$ID_POLL = db_insert_id();

		// Create each answer choice.
		$i = 0;
		$setString = '';
		foreach ($_POST['options'] as $option)
		{
			$setString .= "
					($ID_POLL, $i, SUBSTRING('$option', 1, 255)),";
			$i++;
		}

		db_query("
			INSERT INTO {$db_prefix}poll_choices
				(ID_POLL, ID_CHOICE, label)
			VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);
	}
	else
		$ID_POLL = 0;

	// Creating a new topic?
	$newTopic = empty($_REQUEST['msg']) && empty($topic);

	// Collect all parameters for the creation or modification of a post.
	$msgOptions = array(
		'id' => empty($_REQUEST['msg']) ? 0 : (int) $_REQUEST['msg'],
		'subject' => $_POST['subject'],
		'body' => $_POST['message'],
		'icon' => preg_replace('~[\./\\\\*\':"<>]~', '', $_POST['icon']),
		'smileys_enabled' => !isset($_POST['ns']),
		'attachments' => empty($attachIDs) ? array() : $attachIDs,
	);
	$topicOptions = array(
		'id' => empty($topic) ? 0 : $topic,
		'board' => $board,
		'poll' => isset($_REQUEST['poll']) ? $ID_POLL : null,
		'lock_mode' => isset($_POST['lock']) ? (int) $_POST['lock'] : null,
		'sticky_mode' => isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']) ? (int) $_POST['sticky'] : null,
		'mark_as_read' => true,
	);
	$posterOptions = array(
		'id' => $ID_MEMBER,
		'name' => $_POST['guestname'],
		'email' => $_POST['email'],
		'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']) && $board_info['posts_count'],
	);

	// This is an already existing message. Edit it.
	if (!empty($_REQUEST['msg']))
	{
		// Have admins allowed people to hide their screwups?
		if (time() - $row['posterTime'] > $modSettings['edit_wait_time'] || $ID_MEMBER != $row['ID_MEMBER'])
		{
			$msgOptions['modify_time'] = time();
			$msgOptions['modify_name'] = addslashes($user_info['name']);
		}

		modifyPost($msgOptions, $topicOptions, $posterOptions);
	}
	// This is a new topic or an already existing one. Save it.
	else
	{
		createPost($msgOptions, $topicOptions, $posterOptions);

		if (isset($topicOptions['id']))
			$topic = $topicOptions['id'];
	}

	// Editing or posting an event?
	if (isset($_POST['calendar']) && (!isset($_REQUEST['eventid']) || $_REQUEST['eventid'] == -1))
	{
		require_once($sourcedir . '/Calendar.php');
		calendarCanLink();
		calendarInsertEvent($board, $topic, $_POST['evtitle'], $ID_MEMBER, $_POST['month'], $_POST['day'], $_POST['year'], isset($_POST['span']) ? $_POST['span'] : null);
	}
	elseif (isset($_POST['calendar']))
	{
		$_REQUEST['eventid'] = (int) $_REQUEST['eventid'];

		// Validate the post...
		require_once($sourcedir . '/Subs-Post.php');
		calendarValidatePost();

		// If you're not allowed to edit any events, you have to be the poster.
		if (!allowedTo('calendar_edit_any'))
		{
			// Get the event's poster.
			$request = db_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}calendar
				WHERE ID_EVENT = $_REQUEST[eventid]", __FILE__, __LINE__);
			$row2 = mysql_fetch_assoc($request);
			mysql_free_result($request);

			// Silly hacker, Trix are for kids. ...probably trademarked somewhere, this is FAIR USE! (parody...)
			isAllowedTo('calendar_edit_' . ($row2['ID_MEMBER'] == $ID_MEMBER ? 'own' : 'any'));
		}

		// Delete it?
		if (isset($_REQUEST['deleteevent']))
			db_query("
				DELETE FROM {$db_prefix}calendar
				WHERE ID_EVENT = $_REQUEST[eventid]
				LIMIT 1", __FILE__, __LINE__);
		// ... or just update it?
		else
		{
			$span = !empty($modSettings['cal_allowspan']) && !empty($_REQUEST['span']) ? min((int) $modSettings['cal_maxspan'], (int) $_REQUEST['span'] - 1) : 0;
			$start_time = mktime(0, 0, 0, (int) $_REQUEST['month'], (int) $_REQUEST['day'], (int) $_REQUEST['year']);

			db_query("
				UPDATE {$db_prefix}calendar
				SET endDate = '" . strftime('%Y-%m-%d', $start_time + $span * 86400) . "',
					startDate = '" . strftime('%Y-%m-%d', $start_time) . "',
					title = '" . $func['htmlspecialchars']($_REQUEST['evtitle'], ENT_QUOTES) . "'
				WHERE ID_EVENT = $_REQUEST[eventid]
				LIMIT 1", __FILE__, __LINE__);
		}
		updateStats('calendar');
	}

	// Marking read should be done even for editing messages....
	if (!$user_info['is_guest'])
	{
		// Mark all the parents read.  (since you just posted and they will be unread.)
		if (!empty($board_info['parent_boards']))
		{
			db_query("
				UPDATE {$db_prefix}log_boards
				SET ID_MSG = $modSettings[maxMsgID]
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_BOARD IN (" . implode(',', array_keys($board_info['parent_boards'])) . ")", __FILE__, __LINE__);
		}
	}

	// Turn notification on or off.  (note this just blows smoke if it's already on or off.)
	if (!empty($_POST['notify']))
	{
		if (allowedTo('mark_any_notify'))
			db_query("
				INSERT IGNORE INTO {$db_prefix}log_notify
					(ID_MEMBER, ID_TOPIC, ID_BOARD)
				VALUES ($ID_MEMBER, $topic, 0)", __FILE__, __LINE__);
	}
	elseif (!$newTopic)
		db_query("
			DELETE FROM {$db_prefix}log_notify
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);

	// Log an act of moderation - modifying.
	if (!empty($moderationAction))
		logAction('modify', array('topic' => $topic, 'message' => (int) $_REQUEST['msg'], 'member' => $row['ID_MEMBER']));

	if (isset($_POST['lock']) && $_POST['lock'] != 2)
		logAction('lock', array('topic' => $topicOptions['id']));

	if (isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']))
		logAction('sticky', array('topic' => $topicOptions['id']));


	// Notify any members who have notification turned on for this topic.
	if ($newTopic)
		notifyMembersBoard();
	elseif (empty($_REQUEST['msg']))
		sendNotifications($topic, 'reply');

	// Returning to the topic?
	if (!empty($_REQUEST['goback']))
	{
		// Mark the board as read.... because it might get confusing otherwise.
		db_query("
			UPDATE {$db_prefix}log_boards
			SET ID_MSG = $modSettings[maxMsgID]
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_BOARD = $board", __FILE__, __LINE__);
	}

	if (!empty($_POST['announce_topic']))
		redirectexit('action=announce;sa=selectgroup;topic=' . $topic . (!empty($_POST['move']) && allowedTo('move_any') ? ';move' : '') . (empty($_REQUEST['goback']) ? '' : ';goback'));

	if (!empty($_POST['move']) && allowedTo('move_any'))
		redirectexit('action=movetopic;topic=' . $topic . '.0' . (empty($_REQUEST['goback']) ? '' : ';goback'));

	// Return to post if the mod is on.
	if (isset($_REQUEST['msg']) && !empty($_REQUEST['goback']))
		redirectexit('topic=' . $topic . '.msg' . $_REQUEST['msg'] . '#msg' . $_REQUEST['msg'], $context['browser']['is_ie']);
	elseif (!empty($_REQUEST['goback']))
		redirectexit('topic=' . $topic . '.new#new', $context['browser']['is_ie']);
	// Dut-dut-duh-duh-DUH-duh-dut-duh-duh!  *dances to the Final Fantasy Fanfare...*
	else
		redirectexit('board=' . $board . '.0');
}

// General function for topic announcements.
function AnnounceTopic()
{
	global $context, $txt;

	isAllowedTo('announce_topic');

	validateSession();

	loadLanguage('Post');
	loadTemplate('Post');

	$subActions = array(
		'selectgroup' => 'AnnouncementSelectMembergroup',
		'send' => 'AnnouncementSend',
	);

	$context['page_title'] = $txt['announce_topic'];

	// Call the function based on the sub-action.
	$subActions[isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'selectgroup']();
}

// Allow a user to chose the membergroups to send the announcement to.
function AnnouncementSelectMembergroup()
{
	global $db_prefix, $txt, $context, $topic, $board, $board_info;

	$groups = array_merge($board_info['groups'], array(1));
	foreach ($groups as $id => $group)
		$groups[$id] = (int) $group;

	$context['groups'] = array();
	if (in_array(0, $groups))
	{
		$context['groups'][0] = array(
			'id' => 0,
			'name' => $txt['announce_regular_members'],
			'member_count' => 'n/a',
		);
	}

	// Get all membergroups that have access to the board the announcement was made on.
	$request = db_query("
		SELECT mg.ID_GROUP, mg.groupName, COUNT(mem.ID_MEMBER) AS num_members
		FROM {$db_prefix}membergroups AS mg
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_GROUP = mg.ID_GROUP OR FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups) OR mg.ID_GROUP = mem.ID_POST_GROUP)
		WHERE mg.ID_GROUP IN (" . implode(', ', $groups) . ")
		GROUP BY mg.ID_GROUP
		ORDER BY mg.minPosts, IF(mg.ID_GROUP < 4, mg.ID_GROUP, 4), mg.groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'member_count' => $row['num_members'],
		);
	}
	mysql_free_result($request);

	// Get the subject of the topic we're about to announce.
	$request = db_query("
		SELECT m.subject
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE t.ID_TOPIC = $topic
			AND m.ID_MSG = t.ID_FIRST_MSG", __FILE__, __LINE__);
	list ($context['topic_subject']) = mysql_fetch_row($request);
	mysql_free_result($request);

	censorText($context['announce_topic']['subject']);

	$context['move'] = isset($_REQUEST['move']) ? 1 : 0;
	$context['go_back'] = isset($_REQUEST['goback']) ? 1 : 0;

	$context['sub_template'] = 'announce';
}

// Send the announcement in chunks.
function AnnouncementSend()
{
	global $db_prefix, $topic, $board, $board_info, $context, $modSettings;
	global $language, $scripturl, $txt, $ID_MEMBER, $sourcedir;

	checkSession();

	// !!! Might need an interface?
	$chunkSize = 50;
	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];
	$groups = array_merge($board_info['groups'], array(1));

	if (!empty($_POST['membergroups']))
		$_POST['who'] = explode(',', $_POST['membergroups']);

	// Check whether at least one membergroup was selected.
	if (empty($_POST['who']))
		fatal_lang_error('no_membergroup_selected');

	// Make sure all membergroups are integers and can access the board of the announcement.
	foreach ($_POST['who'] as $id => $mg)
		$_POST['who'][$id] = in_array((int) $mg, $groups) ? (int) $mg : 0;

	// Get the topic subject and censor it.
	$request = db_query("
		SELECT m.ID_MSG, m.subject, m.body
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE t.ID_TOPIC = $topic
			AND m.ID_MSG = t.ID_FIRST_MSG", __FILE__, __LINE__);
	list ($ID_MSG, $context['topic_subject'], $message) = mysql_fetch_row($request);
	mysql_free_result($request);

	censorText($context['topic_subject']);
	censorText($message);

	$message = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($message, false, $ID_MSG), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

	// We need this in order to be able send emails.
	require_once($sourcedir . '/Subs-Post.php');

	// Select the email addresses for this batch.
	$request = db_query("
		SELECT mem.ID_MEMBER, mem.emailAddress, mem.lngfile
		FROM {$db_prefix}members AS mem
		WHERE mem.ID_MEMBER != $ID_MEMBER" . (!empty($modSettings['allow_disableAnnounce']) ? '
			AND mem.notifyAnnouncements = 1' : '') . "
			AND mem.is_activated = 1
			AND (mem.ID_GROUP IN (" . implode(', ', $_POST['who']) . ") OR mem.ID_POST_GROUP IN (" . implode(', ', $_POST['who']) . ") OR FIND_IN_SET(" . implode(", mem.additionalGroups) OR FIND_IN_SET(", $_POST['who']) . ", mem.additionalGroups))
			AND mem.ID_MEMBER > $context[start]
		ORDER BY mem.ID_MEMBER
		LIMIT $chunkSize", __FILE__, __LINE__);

	// All members have received a mail. Go to the next screen.
	if (mysql_num_rows($request) == 0)
	{
		if (!empty($_REQUEST['move']) && allowedTo('move_any'))
			redirectexit('action=movetopic;topic=' . $topic . '.0' . (empty($_REQUEST['goback']) ? '' : ';goback'));
		elseif (!empty($_REQUEST['goback']))
			redirectexit('topic=' . $topic . '.new;boardseen#new', $context['browser']['is_ie']);
		else
			redirectexit('board=' . $board . '.0');
	}

	// Loop through all members that'll receive an announcement in this batch.
	while ($row = mysql_fetch_assoc($request))
	{
		$cur_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];

		// If the language wasn't defined yet, load it and compose a notification message.
		if (!isset($announcements[$cur_language]))
		{
			loadLanguage('Post', $cur_language, false);

			$announcements[$cur_language] = array(
				'subject' => $txt['notifyXAnn2'] . ': ' . $context['topic_subject'],
				'body' => $message . "\n\n" . $txt['notifyXAnn3'] . "\n\n" . $scripturl . '?topic=' . $topic . ".0\n\n" . $txt[130],
				'recipients' => array(),
			);
		}

		$announcements[$cur_language]['recipients'][$row['ID_MEMBER']] = $row['emailAddress'];
		$context['start'] = $row['ID_MEMBER'];
	}
	mysql_free_result($request);

	// For each language send a different mail.
	foreach ($announcements as $lang => $mail)
		sendmail($mail['recipients'], $mail['subject'], $mail['body']);

	$context['percentage_done'] = round(100 * $context['start'] / $modSettings['latestMember'], 1);

	$context['move'] = empty($_REQUEST['move']) ? 0 : 1;
	$context['go_back'] = empty($_REQUEST['goback']) ? 0 : 1;
	$context['membergroups'] = implode(',', $_POST['who']);
	$context['sub_template'] = 'announcement_send';

	// Go back to the correct language for the user ;).
	if (!empty($modSettings['userLanguage']))
		loadLanguage('Post');
}

// Notify members of a new post.
function notifyMembersBoard()
{
	global $board, $topic, $txt, $scripturl, $db_prefix, $language, $user_info;
	global $ID_MEMBER, $modSettings, $sourcedir;

	// Can't do it if there's no board. (won't happen but let's check for safety and not sending a zillion email's sake.)
	if (empty($board))
		trigger_error('notifyMembersBoard(): Can\'t send a notification without a board id!', E_USER_NOTICE);

	require_once($sourcedir . '/Subs-Post.php');

	$message = stripslashes($_POST['message']);

	// Censor the subject and body...
	censorText($_POST['subject']);
	censorText($message);

	$_POST['subject'] = un_htmlspecialchars($_POST['subject']);
	$message = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($message, false), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

	// Find the members with notification on for this board.
	$members = db_query("
		SELECT
			mem.ID_MEMBER, mem.emailAddress, mem.notifyOnce, mem.notifySendBody, mem.lngfile,
			ln.sent, mem.ID_GROUP, mem.additionalGroups, b.memberGroups, mem.ID_POST_GROUP
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}members AS mem, {$db_prefix}boards AS b)
		WHERE ln.ID_BOARD = $board
			AND b.ID_BOARD = $board
			AND mem.ID_MEMBER != $ID_MEMBER
			AND mem.is_activated = 1
			AND mem.notifyTypes != 4
			AND ln.ID_MEMBER = mem.ID_MEMBER
		GROUP BY mem.ID_MEMBER
		ORDER BY mem.lngfile", __FILE__, __LINE__);
	while ($rowmember = mysql_fetch_assoc($members))
	{
		if ($rowmember['ID_GROUP'] != 1)
		{
			$allowed = explode(',', $rowmember['memberGroups']);
			$rowmember['additionalGroups'] = explode(',', $rowmember['additionalGroups']);
			$rowmember['additionalGroups'][] = $rowmember['ID_GROUP'];
			$rowmember['additionalGroups'][] = $rowmember['ID_POST_GROUP'];

			if (count(array_intersect($allowed, $rowmember['additionalGroups'])) == 0)
				continue;
		}

		loadLanguage('Post', empty($rowmember['lngfile']) || empty($modSettings['userLanguage']) ? $language : $rowmember['lngfile'], false);

		// Setup the string for adding the body to the message, if a user wants it.
		$body_text = empty($modSettings['disallow_sendBody']) ? $txt['notification_new_topic_body'] . "\n\n" . $message . "\n\n" : '';

		$send_subject = sprintf($txt['notify_boards_subject'], $_POST['subject']);

		// Send only if once is off or it's on and it hasn't been sent.
		if (!empty($rowmember['notifyOnce']) && empty($rowmember['sent']))
			sendmail($rowmember['emailAddress'], $send_subject,
				sprintf($txt['notify_boards'], $_POST['subject'], $scripturl . '?topic=' . $topic . '.new#new', un_htmlspecialchars($user_info['name'])) .
				$txt['notify_boards_once'] . "\n\n" .
				(!empty($rowmember['notifySendBody']) ? $body_text : '') .
				$txt['notify_boardsUnsubscribe'] . ': ' . $scripturl . '?action=notifyboard;board=' . $board . ".0\n\n" .
				$txt[130], null, 't' . $topic);
		elseif (empty($rowmember['notifyOnce']))
			sendmail($rowmember['emailAddress'], $send_subject,
				sprintf($txt['notify_boards'], $_POST['subject'], $scripturl . '?topic=' . $topic . '.new#new', un_htmlspecialchars($user_info['name'])) .
				(!empty($rowmember['notifySendBody']) ? $body_text : '') .
				$txt['notify_boardsUnsubscribe'] . ': ' . $scripturl . '?action=notifyboard;board=' . $board . ".0\n\n" .
				$txt[130], null, 't' . $topic);
	}
	mysql_free_result($members);

	// Sent!
	db_query("
		UPDATE {$db_prefix}log_notify
		SET sent = 1
		WHERE ID_BOARD = $board
			AND ID_MEMBER != $ID_MEMBER", __FILE__, __LINE__);
}

// Get the topic for display purposes.
function getTopic()
{
	global $topic, $db_prefix, $modSettings, $context;

	// Calculate the amount of new replies.
	$newReplies = empty($_REQUEST['num_replies']) || $context['num_replies'] <= $_REQUEST['num_replies'] ? 0 : $context['num_replies'] - $_REQUEST['num_replies'];

	if (isset($_REQUEST['xml']))
		$limit = "
		LIMIT " . (empty($newReplies) ? '0' : $newReplies);
	else
		$limit = empty($modSettings['topicSummaryPosts']) ? '' : '
		LIMIT ' . (int) $modSettings['topicSummaryPosts'];

	// If you're modifying, get only those posts before the current one. (otherwise get all.)
	$request = db_query("
		SELECT IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime, m.body, m.smileysEnabled, m.ID_MSG
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_TOPIC = $topic" . (isset($_REQUEST['msg']) ? "
			AND m.ID_MSG < " . (int) $_REQUEST['msg'] : '') . "
		ORDER BY m.ID_MSG DESC$limit", __FILE__, __LINE__);
	$context['previous_posts'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Censor, BBC, ...
		censorText($row['body']);
		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		// ...and store.
		$context['previous_posts'][] = array(
			'poster' => $row['posterName'],
			'message' => $row['body'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'id' => $row['ID_MSG'],
			'is_new' => !empty($newReplies),
		);

		if (!empty($newReplies))
			$newReplies--;
	}
	mysql_free_result($request);
}

function QuoteFast()
{
	global $db_prefix, $modSettings, $user_info, $txt, $settings, $context;
	global $sourcedir, $func;

	loadLanguage('Post');
	if (!isset($_REQUEST['xml']))
		loadTemplate('Post');

	checkSession('get');

	include_once($sourcedir . '/Subs-Post.php');

	$moderate_boards = boardsAllowedTo('moderate_board');

	$request = db_query("
		SELECT IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime, m.body, m.ID_TOPIC, m.subject, t.locked
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b, {$db_prefix}topics AS t)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_MSG = " . (int) $_REQUEST['quote'] . "
			AND b.ID_BOARD = m.ID_BOARD
			AND t.ID_TOPIC = m.ID_TOPIC
			AND $user_info[query_see_board]" . (!isset($_REQUEST['modify']) || (!empty($moderate_boards) && $moderate_boards[0] == 0) ? '' : '
 			AND (t.locked = 0' . (empty($moderate_boards) ? '' : ' OR b.ID_BOARD IN (' . implode(', ', $moderate_boards) . ')') . ')') . "
		LIMIT 1", __FILE__, __LINE__);
	$context['close_window'] = mysql_num_rows($request) == 0;

	$context['sub_template'] = 'quotefast';
	if (mysql_num_rows($request) != 0)
	{
		$row = mysql_fetch_assoc($request);
		mysql_free_result($request);

		// Remove special formatting we don't want anymore.
		$row['body'] = un_preparsecode($row['body']);

		// Censor the message!
		censorText($row['body']);

		$row['body'] = preg_replace('~<br(?: /)?' . '>~i', "\n", $row['body']);

		// Want to modify a single message by double clicking it?
		if (isset($_REQUEST['modify']))
		{
			censorText($row['subject']);

			$context['sub_template'] = 'modifyfast';
			$context['message'] = array(
				'id' => $_REQUEST['quote'],
				'body' => $row['body'],
				'subject' => addcslashes($row['subject'], '"'),
			);
			
			return;
		}

		// Remove any nested quotes.
		if (!empty($modSettings['removeNestedQuotes']))
			$row['body'] = preg_replace(array('~\n?\[quote.*?\].+?\[/quote\]\n?~is', '~^\n~', '~\[/quote\]~'), '', $row['body']);

		// Add a quote string on the front and end.
		$context['quote']['xml'] = '[quote author=' . $row['posterName'] . ' link=topic=' . $row['ID_TOPIC'] . '.msg' . (int) $_REQUEST['quote'] . '#msg' . (int) $_REQUEST['quote'] . ' date=' . $row['posterTime'] . ']' . "\n" . $row['body'] . "\n" . '[/quote]';
		$context['quote']['text'] = strtr(un_htmlspecialchars($context['quote']['xml']), array('\'' => '\\\'', '\\' => '\\\\', "\n" => '\\n', '</script>' => '</\' + \'script>'));
		$context['quote']['xml'] = strtr($context['quote']['xml'], array('&nbsp;' => '&#160;', '<' => '&lt;', '>' => '&gt;'));

		$context['quote']['mozilla'] = strtr($func['htmlspecialchars']($context['quote']['text']), array('&quot;' => '"'));
	}
	// !!! Needs a nicer interface.
	// In case our message has been removed in the meantime.
	elseif (isset($_REQUEST['modify']))
	{
		$context['sub_template'] = 'modifyfast';
		$context['message'] = array(
			'id' => 0,
			'body' => '',
			'subject' => '',
		);
	}
	else
		$context['quote'] = array(
			'xml' => '',
			'mozilla' => '',
			'text' => '',
		);
}

function JavaScriptModify()
{
	global $db_prefix, $sourcedir, $modSettings, $board, $topic, $txt;
	global $user_info, $ID_MEMBER, $context, $func, $language;

	// We have to have a topic!
	if (empty($topic))
		obExit(false);

	checkSession('get');
	require_once($sourcedir . '/Subs-Post.php');

	// Assume the first message if no message ID was given.
	$request = db_query("
			SELECT 
				t.locked, t.numReplies, t.ID_MEMBER_STARTED, t.ID_FIRST_MSG,
				m.ID_MSG, m.ID_MEMBER, m.posterTime, m.subject, m.smileysEnabled, m.body,
				m.modifiedTime, m.modifiedName
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
			WHERE m.ID_MSG = " . (empty($_REQUEST['msg']) ? 't.ID_FIRST_MSG' : (int) $_REQUEST['msg']) . "
				AND m.ID_TOPIC = $topic
				AND t.ID_TOPIC = $topic", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232', false);
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Change either body or subject requires permissions to modify messages.
	if (isset($_POST['message']) || isset($_POST['subject']) || isset($_POST['icon']))
	{
		if (!empty($row['locked']))
			isAllowedTo('moderate_board');

		if ($row['ID_MEMBER'] == $ID_MEMBER && !allowedTo('modify_any'))
		{
			if (!empty($modSettings['edit_disable_time']) && $row['posterTime'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['ID_MEMBER_STARTED'] == $ID_MEMBER && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		// Otherwise, they're locked out; someone who can modify the replies is needed.
		elseif ($row['ID_MEMBER_STARTED'] == $ID_MEMBER && !allowedTo('modify_any'))
			isAllowedTo('modify_replies');
		else
			isAllowedTo('modify_any');

		// Only log this action if it wasn't your message.
		$moderationAction = $row['ID_MEMBER'] != $ID_MEMBER;
	}

	$post_errors = array();
	if (isset($_POST['subject']) && $func['htmltrim']($_POST['subject']) !== '')
	{
		$_POST['subject'] = strtr($func['htmlspecialchars']($_POST['subject']), array("\r" => '', "\n" => '', "\t" => ''));

		// Maximum number of characters.
		if ($func['strlen']($_POST['subject']) > 100)
			$_POST['subject'] = addslashes($func['substr'](stripslashes($_POST['subject']), 0, 100));
	}
	else
	{
		$post_errors[] = 'no_subject';
		unset($_POST['subject']);
	}

	if (isset($_POST['message']))
	{
		if ($func['htmltrim']($_POST['message']) === '')
		{
			$post_errors[] = 'no_message';
			unset($_POST['message']);
		}
		elseif (!empty($modSettings['max_messageLength']) && $func['strlen']($_POST['message']) > $modSettings['max_messageLength'])
		{
			$post_errors[] = 'long_message';
			unset($_POST['message']);
		}
		else
		{
			$_POST['message'] = $func['htmlspecialchars']($_POST['message'], ENT_QUOTES);

			preparsecode($_POST['message']);

			if ($func['htmltrim'](strip_tags(parse_bbc($_POST['message'], false), '<img>')) === '')
			{
				$post_errors[] = 'no_message';
				unset($_POST['message']);
			}
		}
	}

	if (isset($_POST['lock']))
	{
		if (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $ID_MEMBER != $row['ID_MEMBER']))
			unset($_POST['lock']);
		elseif (!allowedTo('lock_any'))
		{
			if ($row['locked'] == 1)
				unset($_POST['lock']);
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
		}
		elseif (!empty($row['locked']) && !empty($_POST['lock']) || $_POST['lock'] == $row['locked'])
			unset($_POST['lock']);
		else
			$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
	}

	if (isset($_POST['sticky']) && !allowedTo('make_sticky'))
		unset($_POST['sticky']);


	if (empty($post_errors))
	{
		$msgOptions = array(
			'id' => $row['ID_MSG'],
			'subject' => isset($_POST['subject']) ? $_POST['subject'] : null,
			'body' => isset($_POST['message']) ? $_POST['message'] : null,
			'icon' => isset($_POST['icon']) ? preg_replace('~[\./\\\\*\':"<>]~', '', $_POST['icon']) : null,
		);
		$topicOptions = array(
			'id' => $topic,
			'board' => $board,
			'lock_mode' => isset($_POST['lock']) ? (int) $_POST['lock'] : null,
			'sticky_mode' => isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']) ? (int) $_POST['sticky'] : null,
			'mark_as_read' => true,
		);
		$posterOptions = array();

		// Only consider marking as editing if they have edited the subject, message or icon.
		if ((isset($_POST['subject']) && $_POST['subject'] != $row['subject']) || (isset($_POST['message']) && $_POST['message'] != $row['body']) || (isset($_POST['icon']) && $_POST['icon'] != $row['icon']))
		{
			// And even then only if the time has passed...
			if (time() - $row['posterTime'] > $modSettings['edit_wait_time'] || $ID_MEMBER != $row['ID_MEMBER'])
			{
				$msgOptions['modify_time'] = time();
				$msgOptions['modify_name'] = addslashes($user_info['name']);
			}
		}

		modifyPost($msgOptions, $topicOptions, $posterOptions);

		// If we didn't change anything this time but had before put back the old info.
		if (!isset($msgOptions['modify_time']) && !empty($row['modifiedTime']))
		{
			$msgOptions['modify_time'] = $row['modifiedTime'];
			$msgOptions['modify_name'] = $row['modifiedName'];
		}

		// Changing the first subject updates other subjects to 'Re: new_subject'.
		if (isset($_POST['subject']) && isset($_REQUEST['change_all_subjects']) && $row['ID_FIRST_MSG'] == $row['ID_MSG'] && !empty($row['numReplies']) && (allowedTo('modify_any') || ($row['ID_MEMBER_STARTED'] == $ID_MEMBER && allowedTo('modify_replies'))))
		{
			// Get the proper (default language) response prefix first.
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
				SET subject = '$context[response_prefix]$_POST[subject]'
				WHERE ID_TOPIC = $topic
					AND ID_MSG != $row[ID_FIRST_MSG]
				LIMIT $row[numReplies]", __FILE__, __LINE__);
		}

		if ($moderationAction)
			logAction('modify', array('topic' => $topic, 'message' => $row['ID_MSG'], 'member' => $row['ID_MEMBER_STARTED']));
	}

	if (isset($_REQUEST['xml']))
	{
		$context['sub_template'] = 'modifydone';
		if (empty($post_errors) && isset($msgOptions['subject']) && isset($msgOptions['body']))
		{
			$context['message'] = array(
				'id' => $row['ID_MSG'],
				'modified' => array(
					'time' => isset($msgOptions['modify_time']) ? timeformat($msgOptions['modify_time']) : '',
					'timestamp' => isset($msgOptions['modify_time']) ? forum_time(true, $msgOptions['modify_time']) : 0,
					'name' => isset($msgOptions['modify_time']) ? stripslashes($msgOptions['modify_name']) : '',
				),
				'subject' => stripslashes($msgOptions['subject']),
				'first_in_topic' => $row['ID_MSG'] == $row['ID_FIRST_MSG'],
				'body' => strtr(stripslashes($msgOptions['body']), array(']]>' => ']]]]><![CDATA[>')),
			);

			censorText($context['message']['subject']);
			censorText($context['message']['body']);

			$context['message']['body'] = parse_bbc($context['message']['body'], $row['smileysEnabled'], $row['ID_MSG']);
		}
		// Topic?
		elseif (empty($post_errors) && isset($msgOptions['subject']))
		{
			$context['sub_template'] = 'modifytopicdone';
			$context['message'] = array(
				'id' => $row['ID_MSG'],
				'modified' => array(
					'time' => isset($msgOptions['modify_time']) ? timeformat($msgOptions['modify_time']) : '',
					'timestamp' => isset($msgOptions['modify_time']) ? forum_time(true, $msgOptions['modify_time']) : 0,
					'name' => isset($msgOptions['modify_time']) ? stripslashes($msgOptions['modify_name']) : '',
				),
				'subject' => stripslashes($msgOptions['subject']),
			);

			censorText($context['message']['subject']);
		}
		else
		{
			$context['message'] = array(
				'id' => $row['ID_MSG'],
				'errors' => array(),
				'error_in_subject' => in_array('no_subject', $post_errors),
				'error_in_body' => in_array('no_message', $post_errors) || in_array('long_message', $post_errors),
			);

			loadLanguage('Errors');
			foreach ($post_errors as $post_error)
				$context['message']['errors'][] = $txt['error_' . $post_error];
		}
	}
	else
		obExit(false);
}

?>