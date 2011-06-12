<?php
/**********************************************************************************
* Notify.php                                                                      *
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

/*	This file contains just the functions that turn on and off notifications to
	topics or boards. The following two functions are included:

	void Notify()
		- is called to turn off/on notification for a particular topic.
		- must be called with a topic specified in the URL.
		- uses the Notify template (main sub template.) when called with no sa.
		- the sub action can be 'on', 'off', or nothing for what to do.
		- requires the mark_any_notify permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=notify.

	void BoardNotify()
		- is called to turn off/on notification for a particular board.
		- must be called with a board specified in the URL.
		- uses the Notify template. (notify_board sub template.)
		- only uses the template if no sub action is used. (on/off)
		- requires the mark_notify permission.
		- redirects the user back to the board after it is done.
		- is accessed via ?action=notifyboard.
*/

// Turn on/off notifications...
function Notify()
{
	global $db_prefix, $scripturl, $txt, $topic, $ID_MEMBER, $context;

	// Make sure they aren't a guest or something - guests can't really receive notifications!
	is_not_guest();
	isAllowedTo('mark_any_notify');

	// Make sure the topic has been specified.
	if (empty($topic))
		fatal_lang_error(472, false);

	// What do we do?  Better ask if they didn't say..
	if (empty($_GET['sa']))
	{
		// Load the template, but only if it is needed.
		loadTemplate('Notify');

		// Find out if they have notification set for this topic already.
		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}log_notify
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);
		$context['notification_set'] = mysql_num_rows($request) != 0;
		mysql_free_result($request);

		// Set the template variables...
		$context['topic_href'] = $scripturl . '?topic=' . $topic . '.' . $_REQUEST['start'];
		$context['start'] = $_REQUEST['start'];
		$context['page_title'] = $txt[418];

		return;
	}
	elseif ($_GET['sa'] == 'on')
	{
		checkSession('get');

		// Attempt to turn notifications on.
		db_query("
			INSERT IGNORE INTO {$db_prefix}log_notify
				(ID_MEMBER, ID_TOPIC)
			VALUES ($ID_MEMBER, $topic)", __FILE__, __LINE__);
	}
	else
	{
		checkSession('get');

		// Just turn notifications off.
		db_query("
			DELETE FROM {$db_prefix}log_notify
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_TOPIC = $topic
			LIMIT 1", __FILE__, __LINE__);
	}

	// Send them back to the topic.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

function BoardNotify()
{
	global $db_prefix, $scripturl, $txt, $board, $ID_MEMBER, $user_info, $context;

	// Permissions are an important part of anything ;).
	is_not_guest();
	isAllowedTo('mark_notify');

	// You have to specify a board to turn notifications on!
	if (empty($board))
		fatal_lang_error('smf232', false);

	// No subaction: find out what to do.
	if (empty($_GET['sa']))
	{
		// We're gonna need the notify template...
		loadTemplate('Notify');

		// Find out if they have notification set for this topic already.
		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}log_notify
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_BOARD = $board
			LIMIT 1", __FILE__, __LINE__);
		$context['notification_set'] = mysql_num_rows($request) != 0;
		mysql_free_result($request);

		// Set the template variables...
		$context['board_href'] = $scripturl . '?board=' . $board . '.' . $_REQUEST['start'];
		$context['start'] = $_REQUEST['start'];
		$context['page_title'] = $txt[418];
		$context['sub_template'] = 'notify_board';

		return;
	}
	// Turn the board level notification on....
	elseif ($_GET['sa'] == 'on')
	{
		checkSession('get');

		// Turn notification on.  (note this just blows smoke if it's already on.)
		db_query("
			INSERT IGNORE INTO {$db_prefix}log_notify
				(ID_MEMBER, ID_BOARD)
			VALUES ($ID_MEMBER, $board)", __FILE__, __LINE__);
	}
	// ...or off?
	else
	{
		checkSession('get');

		// Turn notification off for this board.
		db_query("
			DELETE FROM {$db_prefix}log_notify
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_BOARD = $board
			LIMIT 1", __FILE__, __LINE__);
	}

	// Back to the board!
	redirectexit('board=' . $board . '.' . $_REQUEST['start']);
}

?>