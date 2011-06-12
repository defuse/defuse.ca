<?php
/**********************************************************************************
* Poll.php                                                                        *
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

/*	This file contains the functions for voting, locking, removing and editing
	polls. Note that that posting polls is done in Post.php.

	void Vote()
		- is called to register a vote in a poll.
		- must be called with a topic and option specified.
		- uses the Post language file.
		- requires the poll_vote permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=vote.

	void LockVoting()
		- is called to lock or unlock voting on a poll.
		- must be called with a topic specified in the URL.
		- an admin always has over riding permission to lock a poll.
		- if not an admin must have poll_lock_any permission.
		- otherwise must be poll starter with poll_lock_own permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=lockVoting.

	void EditPoll()
		- is called to display screen for editing or adding a poll.
		- must be called with a topic specified in the URL.
		- if the user is adding a poll to a topic, must contain the variable
		  'add' in the url.
		- uses the Post language file.
		- uses the Poll template (main sub template.).
		- user must have poll_edit_any/poll_add_any permission for the relevant
		  action.
		- otherwise must be poll starter with poll_edit_own permission for
		  editing, or be topic starter with poll_add_any permission for adding.
		- is accessed via ?action=editpoll.

	void EditPoll2()
		- is called to update the settings for a poll, or add a new one.
		- must be called with a topic specified in the URL.
		- user must have poll_edit_any/poll_add_any permission for the relevant
		  action.
		- otherwise must be poll starter with poll_edit_own permission for
		  editing, or be topic starter with poll_add_any permission for adding.
		- in the case of an error will redirect back to EditPoll and display
		  the relevant error message.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=editpoll2.

	void RemovePoll()
		- is called to remove a poll from a topic.
		- must be called with a topic specified in the URL.
		- user must have poll_remove_any permission.
		- otherwise must be poll starter with poll_remove_own permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=removepoll.
*/

// Allow the user to vote.
function Vote()
{
	global $topic, $txt, $db_prefix, $ID_MEMBER, $user_info;

	// Make sure you can vote.
	isAllowedTo('poll_vote');

	// Even with poll_vote permission we would never be able to register you.
	if ($user_info['is_guest'])
		fatal_lang_error('cannot_poll_vote');

	loadLanguage('Post');

	// Check if they have already voted, or voting is locked.
	$request = db_query("
		SELECT IFNULL(lp.ID_CHOICE, -1) AS selected, p.votingLocked, p.ID_POLL, p.expireTime, p.maxVotes, p.changeVote
		FROM ({$db_prefix}polls AS p, {$db_prefix}topics AS t)
			LEFT JOIN {$db_prefix}log_polls AS lp ON (p.ID_POLL = lp.ID_POLL AND lp.ID_MEMBER = $ID_MEMBER)
		WHERE p.ID_POLL = t.ID_POLL
			AND t.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf27', false);
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Is voting locked or has it expired?
	if (!empty($row['votingLocked']) || (!empty($row['expireTime']) && time() > $row['expireTime']))
		fatal_lang_error('smf27', false);

	// If they have already voted and aren't allowed to change their vote - hence they are outta here!
	if ($row['selected'] != -1 && empty($row['changeVote']))
		fatal_lang_error('smf27', false);
	// Otherwise if they can change their vote yet they haven't sent any options... remove their vote and redirect.
	elseif (!empty($row['changeVote']))
	{
		checkSession('request');
		$pollOptions = array();

		// Find out what they voted for before.
		$request = db_query("
			SELECT ID_CHOICE
			FROM {$db_prefix}log_polls
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
		while ($choice = mysql_fetch_row($request))
			$pollOptions[] = $choice[0];
		mysql_free_result($request);

		// Just skip it if they had voted for nothing before.
		if (!empty($pollOptions))
		{
			// Update the poll totals.
			db_query("
				UPDATE {$db_prefix}poll_choices
				SET votes = votes - 1
				WHERE ID_POLL = $row[ID_POLL]
					AND ID_CHOICE IN (" . implode(', ', $pollOptions) . ")
					AND votes > 0
				LIMIT " . count($pollOptions), __FILE__, __LINE__);

			// Delete off the log.
			db_query("
				DELETE FROM {$db_prefix}log_polls
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
		}

		// Redirect back to the topic so the user can vote again!
		if (empty($_POST['options']))
			redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
	}

	// Make sure the option(s) are valid.
	if (empty($_POST['options']))
		fatal_lang_error('smf26', false);

	// Too many options checked!
	if (count($_REQUEST['options']) > $row['maxVotes'])
		fatal_lang_error('poll_error1', false, array($row['maxVotes']));

	$pollOptions = array();
	$setString = '';
	foreach ($_REQUEST['options'] as $id)
	{
		$id = (int) $id;

		$pollOptions[] = $id;
		$setString .= "
				($row[ID_POLL], $ID_MEMBER, $id),";
	}
	$setString = substr($setString, 0, -1);

	// Add their vote to the tally.
	db_query("
		INSERT IGNORE INTO {$db_prefix}log_polls
			(ID_POLL, ID_MEMBER, ID_CHOICE)
		VALUES $setString", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
		db_query("
			UPDATE {$db_prefix}poll_choices
			SET votes = votes + 1
			WHERE ID_POLL = $row[ID_POLL]
				AND ID_CHOICE IN (" . implode(', ', $pollOptions) . ")
			LIMIT " . count($pollOptions), __FILE__, __LINE__);

	// Return to the post...
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Lock the voting for a poll.
function LockVoting()
{
	global $topic, $ID_MEMBER, $db_prefix, $user_info;

	checkSession('get');

	// Get the poll starter, ID, and whether or not it is locked.
	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, t.ID_POLL, p.votingLocked
		FROM ({$db_prefix}topics AS t, {$db_prefix}polls AS p)
		WHERE t.ID_TOPIC = $topic
			AND p.ID_POLL = t.ID_POLL
		LIMIT 1", __FILE__, __LINE__);
	list ($memberID, $pollID, $votingLocked) = mysql_fetch_row($request);

	// If the user _can_ modify the poll....
	if (!allowedTo('poll_lock_any'))
		isAllowedTo('poll_lock_' . ($ID_MEMBER == $memberID ? 'own' : 'any'));

	// It's been locked by a non-moderator.
	if ($votingLocked == '1')
		$votingLocked = '0';
	// Locked by a moderator, and this is a moderator.
	elseif ($votingLocked == '2' && allowedTo('moderate_board'))
		$votingLocked = '0';
	// Sorry, a moderator locked it.
	elseif ($votingLocked == '2' && !allowedTo('moderate_board'))
		fatal_lang_error('smf31');
	// A moderator *is* locking it.
	elseif ($votingLocked == '0' && allowedTo('moderate_board'))
		$votingLocked = '2';
	// Well, it's gonna be locked one way or another otherwise...
	else
		$votingLocked = '1';

	// Lock!  *Poof* - no one can vote.
	db_query("
		UPDATE {$db_prefix}polls
		SET votingLocked = $votingLocked
		WHERE ID_POLL = $pollID
		LIMIT 1", __FILE__, __LINE__);

	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Ask what to change in a poll.
function EditPoll()
{
	global $txt, $ID_MEMBER, $db_prefix;
	global $user_info, $context, $topic, $func;

	if (empty($topic))
		fatal_lang_error(1, false);

	loadLanguage('Post');
	loadTemplate('Poll');

	$context['can_moderate_poll'] = allowedTo('moderate_board');
	$context['start'] = (int) $_REQUEST['start'];
	$context['is_edit'] = isset($_REQUEST['add']) ? 0 : 1;

	// Check if a poll currently exists on this topic, and get the id, question and starter.
	$request = db_query("
		SELECT
			t.ID_MEMBER_STARTED, p.ID_POLL, p.question, p.hideResults, p.expireTime, p.maxVotes, p.changeVote,
			p.ID_MEMBER AS pollStarter
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}polls AS p ON (p.ID_POLL = t.ID_POLL)
		WHERE t.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);

	// Assume the the topic exists, right?
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
	// Get the poll information.
	$pollinfo = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// If we are adding a new poll - make sure that there isn't already a poll there.
	if (!$context['is_edit'] && !empty($pollinfo['ID_POLL']))
		fatal_lang_error('poll_already_exists');
	// Otherwise, if we're editing it, it does exist I assume?
	elseif ($context['is_edit'] && empty($pollinfo['ID_POLL']))
		fatal_lang_error('poll_not_found');

	// Can you do this?
	if ($context['is_edit'] && !allowedTo('poll_edit_any'))
		isAllowedTo('poll_edit_' . ($ID_MEMBER == $pollinfo['ID_MEMBER_STARTED'] || ($pollinfo['pollStarter'] != 0 && $ID_MEMBER == $pollinfo['pollStarter']) ? 'own' : 'any'));
	elseif (!$context['is_edit'] && !allowedTo('poll_add_any'))
		isAllowedTo('poll_add_' . ($ID_MEMBER == $pollinfo['ID_MEMBER_STARTED'] ? 'own' : 'any'));

	// Want to make sure before you actually submit?  Must be a lot of options, or something.
	if (isset($_POST['preview']))
	{
		$question = $func['htmlspecialchars'](stripslashes($_POST['question']));

		// Basic theme info...
		$context['poll'] = array(
			'id' => $pollinfo['ID_POLL'],
			'question' => $question,
			'hide_results' => empty($_POST['poll_hide']) ? 0 : $_POST['poll_hide'],
			'change_vote' => isset($_POST['poll_change_vote']),
			'max_votes' => empty($_POST['poll_max_votes']) ? '1' : max(1, $_POST['poll_max_votes']),
		);

		// Start at number one with no last id to speak of.
		$number = 1;
		$last_id = 0;

		// Get all the choices - if this is an edit.
		if ($context['is_edit'])
		{
			$request = db_query("
				SELECT label, votes, ID_CHOICE
				FROM {$db_prefix}poll_choices
				WHERE ID_POLL = $pollinfo[ID_POLL]", __FILE__, __LINE__);
			$context['choices'] = array();
			while ($row = mysql_fetch_assoc($request))
			{
				// Get the highest id so we can add more without reusing.
				if ($row['ID_CHOICE'] >= $last_id)
					$last_id = $row['ID_CHOICE'] + 1;

				// They cleared this by either omitting it or emptying it.
				if (!isset($_POST['options'][$row['ID_CHOICE']]) || $_POST['options'][$row['ID_CHOICE']] == '')
					continue;

				censorText($row['label']);

				// Add the choice!
				$context['choices'][$row['ID_CHOICE']] = array(
					'id' => $row['ID_CHOICE'],
					'number' => $number++,
					'votes' => $row['votes'],
					'label' => $row['label'],
					'is_last' => false
				);
			}
			mysql_free_result($request);
		}

		// Work out how many options we have, so we get the 'is_last' field right...
		$totalPostOptions = 0;
		foreach ($_POST['options'] as $id => $label)
			if ($label != '')
				$totalPostOptions++;

		$count = 1;
		// If an option exists, update it.  If it is new, add it - but don't reuse ids!
		foreach ($_POST['options'] as $id => $label)
		{
			$label = stripslashes($func['htmlspecialchars']($label));
			censorText($label);

			if (isset($context['choices'][$id]))
				$context['choices'][$id]['label'] = $label;
			elseif ($label != '')
				$context['choices'][] = array(
					'id' => $last_id++,
					'number' => $number++,
					'label' => $label,
					'votes' => -1,
					'is_last' => $count++ == $totalPostOptions && $totalPostOptions > 1 ? true : false,
				);
		}

		// Make sure we have two choices for sure!
		if ($totalPostOptions < 2)
		{
			// Need two?
			if ($totalPostOptions == 0)
				$context['choices'][] = array(
					'id' => $last_id++,
					'number' => $number++,
					'label' => '',
					'votes' => -1,
					'is_last' => false
				);
			$poll_errors[] = 'poll_few';
		}

		// Always show one extra box...
		$context['choices'][] = array(
			'id' => $last_id++,
			'number' => $number++,
			'label' => '',
			'votes' => -1,
			'is_last' => true
		);

		if (allowedTo('moderate_board'))
			$context['poll']['expiration'] = $_POST['poll_expire'];

		// Check the question/option count for errors.
		if (trim($_POST['question']) == '' && empty($context['poll_error']))
			$poll_errors[] = 'no_question';

		// No check is needed, since nothing is really posted.
		checkSubmitOnce('free');

		// Take a check for any errors... assuming we haven't already done so!
		if (!empty($poll_errors) && empty($context['poll_error']))
		{
			loadLanguage('Errors');

			$context['poll_error'] = array('messages' => array());
			foreach ($poll_errors as $poll_error)
			{
				$context['poll_error'][$poll_error] = true;
				$context['poll_error']['messages'][] = $txt['error_' . $poll_error];
			}
		}
	}
	else
	{
		// Basic theme info...
		$context['poll'] = array(
			'id' => $pollinfo['ID_POLL'],
			'question' => $pollinfo['question'],
			'hide_results' => $pollinfo['hideResults'],
			'max_votes' => $pollinfo['maxVotes'],
			'change_vote' => !empty($pollinfo['changeVote']),
		);

		// Poll expiration time?
		$context['poll']['expiration'] = empty($pollinfo['expireTime']) || !allowedTo('moderate_board') ? '' : ceil($pollinfo['expireTime'] <= time() ? -1 : ($pollinfo['expireTime'] - time()) / (3600 * 24));

		// Get all the choices - if this is an edit.
		if ($context['is_edit'])
		{
			$request = db_query("
				SELECT label, votes, ID_CHOICE
				FROM {$db_prefix}poll_choices
				WHERE ID_POLL = $pollinfo[ID_POLL]", __FILE__, __LINE__);
			$context['choices'] = array();
			$number = 1;
			while ($row = mysql_fetch_assoc($request))
			{
				censorText($row['label']);

				$context['choices'][$row['ID_CHOICE']] = array(
					'id' => $row['ID_CHOICE'],
					'number' => $number++,
					'votes' => $row['votes'],
					'label' => $row['label'],
					'is_last' => false
				);
			}
			mysql_free_result($request);

			$last_id = max(array_keys($context['choices'])) + 1;

			// Add an extra choice...
			$context['choices'][] = array(
				'id' => $last_id,
				'number' => $number,
				'votes' => -1,
				'label' => '',
				'is_last' => true
			);
		}
		// New poll?
		else
		{
			// Setup the default poll options.
			$context['poll'] = array(
				'id' => 0,
				'question' => '',
				'hide_results' => 0,
				'max_votes' => 1,
				'change_vote' => 0,
				'expiration' => '',
			);

			// Make all five poll choices empty.
			$context['choices'] = array(
				array('id' => 0, 'number' => 1, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 1, 'number' => 2, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 2, 'number' => 3, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 3, 'number' => 4, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 4, 'number' => 5, 'votes' => -1, 'label' => '', 'is_last' => true)
			);
		}
	}
	$context['page_title'] = $context['is_edit'] ? $txt['smf39'] : $txt['add_poll'];

	// Build the link tree.
	$context['linktree'][] = array(
		'name' => $context['page_title']
	);

	// Register this form in the session variables.
	checkSubmitOnce('register');
}

// Change a poll...
function EditPoll2()
{
	global $txt, $topic, $board, $ID_MEMBER, $db_prefix, $context;
	global $modSettings, $user_info, $func;

	if (checkSession('post', '', false) != '')
		$poll_errors[] = 'session_timeout';

	if (isset($_POST['preview']))
		return EditPoll();

	// HACKERS (!!) can't edit :P.
	if (empty($topic))
		fatal_lang_error(1, false);

	// Is this a new poll, or editing an existing?
	$isEdit = isset($_REQUEST['add']) ? 0 : 1;

	// Get the starter and the poll's ID - if it's an edit.
	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, t.ID_POLL, p.ID_MEMBER AS pollStarter
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}polls AS p ON (p.ID_POLL = t.ID_POLL)
		WHERE t.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
	$bcinfo = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Check their adding/editing is valid.
	if (!$isEdit && !empty($bcinfo['ID_POLL']))
		fatal_lang_error('poll_already_exists');
	// Are we editing a poll which doesn't exist?
	elseif ($isEdit && empty($bcinfo['ID_POLL']))
		fatal_lang_error('poll_not_found');

	// Check if they have the power to add or edit the poll.
	if ($isEdit && !allowedTo('poll_edit_any'))
		isAllowedTo('poll_edit_' . ($ID_MEMBER == $bcinfo['ID_MEMBER_STARTED'] || ($bcinfo['pollStarter'] != 0 && $ID_MEMBER == $bcinfo['pollStarter']) ? 'own' : 'any'));
	elseif (!$isEdit && !allowedTo('poll_add_any'))
		isAllowedTo('poll_add_' . ($ID_MEMBER == $bcinfo['ID_MEMBER_STARTED'] ? 'own' : 'any'));

	$optionCount = 0;
	// Ensure the user is leaving a valid amount of options - there must be at least two.
	foreach ($_POST['options'] as $k => $option)
	{
		if (trim($option) != '')
			$optionCount++;
	}
	if ($optionCount < 2)
		$poll_errors[] = 'poll_few';

	// Also - ensure they are not removing the question.
	if (trim($_POST['question']) == '')
		$poll_errors[] = 'no_question';

	// Got any errors to report?
	if (!empty($poll_errors))
	{
		loadLanguage('Errors');
		// Previewing.
		$_POST['preview'] = true;

		$context['poll_error'] = array('messages' => array());
		foreach ($poll_errors as $poll_error)
		{
			$context['poll_error'][$poll_error] = true;
			$context['poll_error']['messages'][] = $txt['error_' . $poll_error];
		}

		return EditPoll();
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// Now we've done all our error checking, let's get the core poll information cleaned... question first.
	$_POST['question'] = $func['htmlspecialchars']($_POST['question']);

	$_POST['poll_hide'] = (int) $_POST['poll_hide'];
	$_POST['poll_change_vote'] = isset($_POST['poll_change_vote']) ? 1 : 0;

	// Ensure that the number options allowed makes sense, and the expiration date is valid.
	if (!$isEdit || allowedTo('moderate_board'))
	{
		if (empty($_POST['poll_expire']) && $_POST['poll_hide'] == 2)
			$_POST['poll_hide'] = 1;
		else
			$_POST['poll_expire'] = empty($_POST['poll_expire']) ? '0' : time() + $_POST['poll_expire'] * 3600 * 24;

		if (empty($_POST['poll_max_votes']) || $_POST['poll_max_votes'] <= 0)
			$_POST['poll_max_votes'] = 1;
		else
			$_POST['poll_max_votes'] = (int) $_POST['poll_max_votes'];
	}

	// If we're editing, let's commit the changes.
	if ($isEdit)
	{
		db_query("
			UPDATE {$db_prefix}polls
			SET question = '$_POST[question]', changeVote = $_POST[poll_change_vote]," . (allowedTo('moderate_board') ? "
				hideResults = $_POST[poll_hide], expireTime = $_POST[poll_expire], maxVotes = $_POST[poll_max_votes]" : "
				hideResults = IF(expireTime = 0 AND $_POST[poll_hide] = 2, 1, $_POST[poll_hide])") . "
			WHERE ID_POLL = $bcinfo[ID_POLL]
		LIMIT 1", __FILE__, __LINE__);
	}
	// Otherwise, let's get our poll going!
	else
	{
		// Create the poll.
		db_query("
			INSERT INTO {$db_prefix}polls
				(question, hideResults, maxVotes, expireTime, ID_MEMBER, posterName, changeVote)
			VALUES (SUBSTRING('$_POST[question]', 1, 255), $_POST[poll_hide], $_POST[poll_max_votes], $_POST[poll_expire], $ID_MEMBER, SUBSTRING('$user_info[username]', 1, 255), $_POST[poll_change_vote])", __FILE__, __LINE__);

		// Set the poll ID.
		$bcinfo['ID_POLL'] = db_insert_id();

		// Link the poll to the topic
		db_query("
			UPDATE {$db_prefix}topics
			SET ID_POLL = $bcinfo[ID_POLL]
			WHERE ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);
	}

	// Get all the choices.  (no better way to remove all emptied and add previously non-existent ones.)
	$request = db_query("
		SELECT ID_CHOICE
		FROM {$db_prefix}poll_choices
		WHERE ID_POLL = $bcinfo[ID_POLL]", __FILE__, __LINE__);
	$choices = array();
	while ($row = mysql_fetch_assoc($request))
		$choices[] = $row['ID_CHOICE'];
	mysql_free_result($request);

	$delete_options = array();
	foreach ($_POST['options'] as $k => $option)
	{
		// Make sure the key is numeric for sanity's sake.
		$k = (int) $k;

		// They've cleared the box.  Either they want it deleted, or it never existed.
		if (trim($option) == '')
		{
			// They want it deleted.  Bye.
			if (in_array($k, $choices))
				$delete_options[] = $k;

			// Skip the rest...
			continue;
		}

		// Dress the option up for its big date with the database.
		$option = $func['htmlspecialchars']($option);

		// If it's already there, update it.  If it's not... add it.
		if (in_array($k, $choices))
			db_query("
				UPDATE {$db_prefix}poll_choices
				SET label = '$option'
				WHERE ID_POLL = $bcinfo[ID_POLL]
					AND ID_CHOICE = $k
				LIMIT 1", __FILE__, __LINE__);
		else
			db_query("
				INSERT INTO {$db_prefix}poll_choices
					(ID_POLL, ID_CHOICE, label, votes)
				VALUES ($bcinfo[ID_POLL], $k, SUBSTRING('$option', 1, 255), 0)", __FILE__, __LINE__);
	}

	// I'm sorry, but... well, no one was choosing you.  Poor options, I'll put you out of your misery.
	if (!empty($delete_options))
	{
		db_query("
			DELETE FROM {$db_prefix}log_polls
			WHERE ID_POLL = $bcinfo[ID_POLL]
				AND ID_CHOICE IN (" . implode(', ', $delete_options) . ")", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}poll_choices
			WHERE ID_POLL = $bcinfo[ID_POLL]
				AND ID_CHOICE IN (" . implode(', ', $delete_options) . ")", __FILE__, __LINE__);
	}

	// Shall I reset the vote count, sir?
	if (isset($_POST['resetVoteCount']))
	{
		db_query("
			UPDATE {$db_prefix}poll_choices
			SET votes = 0
			WHERE ID_POLL = $bcinfo[ID_POLL]", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}log_polls
			WHERE ID_POLL = $bcinfo[ID_POLL]", __FILE__, __LINE__);
	}

	// Off we go.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Remove a poll from a topic without removing the topic.
function RemovePoll()
{
	global $topic, $db_prefix, $user_info, $ID_MEMBER;

	// Make sure the topic is not empty.
	if (empty($topic))
		fatal_lang_error(1, false);

	checkSession('get');

	// Check permissions.
	if (!allowedTo('poll_remove_any'))
	{
		$request = db_query("
			SELECT t.ID_MEMBER_STARTED, p.ID_MEMBER AS pollStarter
			FROM ({$db_prefix}topics AS t, {$db_prefix}polls AS p)
			WHERE t.ID_TOPIC = $topic
				AND p.ID_POLL = t.ID_POLL
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) == 0)
			fatal_lang_error(1);
		list ($topicStarter, $pollStarter) = mysql_fetch_row($request);
		mysql_free_result($request);

		isAllowedTo('poll_remove_' . ($topicStarter == $ID_MEMBER || ($pollStarter != 0 && $ID_MEMBER == $pollStarter) ? 'own' : 'any'));
	}

	// Retrieve the poll ID.
	$request = db_query("
		SELECT ID_POLL
		FROM {$db_prefix}topics
		WHERE ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	list ($pollID) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Remove all user logs for this poll.
	db_query("
		DELETE FROM {$db_prefix}log_polls
		WHERE ID_POLL = $pollID", __FILE__, __LINE__);
	// Remove all poll choices.
	db_query("
		DELETE FROM {$db_prefix}poll_choices
		WHERE ID_POLL = $pollID", __FILE__, __LINE__);
	// Remove the poll itself.
	db_query("
		DELETE FROM {$db_prefix}polls
		WHERE ID_POLL = $pollID
		LIMIT 1", __FILE__, __LINE__);
	// Finally set the topic poll ID back to 0!
	db_query("
		UPDATE {$db_prefix}topics
		SET ID_POLL = 0
		WHERE ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);

	// Take the moderator back to the topic.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

?>