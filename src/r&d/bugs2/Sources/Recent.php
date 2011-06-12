<?php
/**********************************************************************************
* Recent.php                                                                      *
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

/*	This file had one very clear purpose.  It is here expressly to find and
	retrieve information about recently posted topics, messages, and the like.

	array getLastPost()
		// !!!

	array getLastPosts(int number_of_posts)
		// !!!

	void RecentPosts()
		// !!!

	void UnreadTopics()
		// !!!
*/

// Get the latest post.
function getLastPost()
{
	global $db_prefix, $user_info, $scripturl, $modSettings, $func;

	// Find it by the board - better to order by board than sort the entire messages table.
	$request = db_query("
		SELECT ml.posterTime, ml.subject, ml.ID_TOPIC, ml.posterName, LEFT(ml.body, 384) AS body, ml.smileysEnabled
		FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS ml)
		WHERE ml.ID_MSG = b.ID_LAST_MSG" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != $modSettings[recycle_board]" : '') . "
			AND $user_info[query_see_board]
		ORDER BY b.ID_MSG_UPDATED DESC
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		return array();
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Censor the subject and post...
	censorText($row['subject']);
	censorText($row['body']);

	$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileysEnabled']), array('<br />' => '&#10;')));
	if ($func['strlen']($row['body']) > 128)
		$row['body'] = $func['substr']($row['body'], 0, 128) . '...';

	// Send the data.
	return array(
		'topic' => $row['ID_TOPIC'],
		'subject' => $row['subject'],
		'short_subject' => shorten_subject($row['subject'], 24),
		'preview' => $row['body'],
		'time' => timeformat($row['posterTime']),
		'timestamp' => forum_time(true, $row['posterTime']),
		'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.new;topicseen#new',
		'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.new;topicseen#new">' . $row['subject'] . '</a>'
	);
}

function getLastPosts($showlatestcount)
{
	global $scripturl, $txt, $db_prefix, $user_info, $modSettings, $func;

	// Find all the posts.  Newer ones will have higher IDs.  (assuming the last 20 * number are accessable...)
	// !!!SLOW This query is now slow, NEEDS to be fixed.  Maybe break into two?
	$request = db_query("
		SELECT
			m.posterTime, m.subject, m.ID_TOPIC, m.ID_MEMBER, m.ID_MSG,
			IFNULL(mem.realName, m.posterName) AS posterName, t.ID_BOARD, b.name AS bName,
			LEFT(m.body, 384) AS body, m.smileysEnabled
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_MSG >= " . max(0, $modSettings['maxMsgID'] - 20 * $showlatestcount) . "
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != $modSettings[recycle_board]" : '') . "
			AND $user_info[query_see_board]
		ORDER BY m.ID_MSG DESC
		LIMIT $showlatestcount", __FILE__, __LINE__);
	$posts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Censor the subject and post for the preview ;).
		censorText($row['subject']);
		censorText($row['body']);

		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
		if ($func['strlen']($row['body']) > 128)
			$row['body'] = $func['substr']($row['body'], 0, 128) . '...';

		// Build the array.
		$posts[] = array(
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['bName'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['bName'] . '</a>'
			),
			'topic' => $row['ID_TOPIC'],
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>'
			),
			'subject' => $row['subject'],
			'short_subject' => shorten_subject($row['subject'], 24),
			'preview' => $row['body'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'raw_timestamp' => $row['posterTime'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#msg' . $row['ID_MSG'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>'
		);
	}
	mysql_free_result($request);

	return $posts;
}

// Find the ten most recent posts.
function RecentPosts()
{
	global $txt, $scripturl, $db_prefix, $user_info, $context, $ID_MEMBER, $modSettings, $sourcedir, $board;

	loadTemplate('Recent');
	$context['page_title'] = $txt[214];

	if (isset($_REQUEST['start']) && $_REQUEST['start'] > 95)
		$_REQUEST['start'] = 95;

	if (!empty($_REQUEST['c']) && empty($board))
	{
		$_REQUEST['c'] = explode(',', $_REQUEST['c']);
		foreach ($_REQUEST['c'] as $i => $c)
			$_REQUEST['c'][$i] = (int) $c;

		if (count($_REQUEST['c']) == 1)
		{
			$request = db_query("
				SELECT name
				FROM {$db_prefix}categories
				WHERE ID_CAT = " . $_REQUEST['c'][0] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($name) = mysql_fetch_row($request);
			mysql_free_result($request);

			if (empty($name))
				fatal_lang_error(1, false);

			$context['linktree'][] = array(
				'url' => $scripturl . '#' . (int) $_REQUEST['c'],
				'name' => $name
			);
		}

		$request = db_query("
			SELECT b.ID_BOARD, b.numPosts
			FROM {$db_prefix}boards AS b
			WHERE b.ID_CAT IN (" . implode(', ', $_REQUEST['c']) . ")
				AND $user_info[query_see_board]", __FILE__, __LINE__);
		$total_cat_posts = 0;
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$boards[] = $row['ID_BOARD'];
			$total_cat_posts += $row['numPosts'];
		}
		mysql_free_result($request);

		if (empty($boards))
			fatal_lang_error('error_no_boards_selected', false);

		$query_this_board = 'b.ID_BOARD IN (' . implode(', ', $boards) . ')';

		// If this category has a significant number of posts in it...
		if ($total_cat_posts > 100 && $total_cat_posts > $modSettings['totalMessages'] / 15)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 400 - $_REQUEST['start'] * 7);

		$context['page_index'] = constructPageIndex($scripturl . '?action=recent;c=' . implode(',', $_REQUEST['c']), $_REQUEST['start'], min(100, $total_cat_posts), 10, false);
	}
	elseif (!empty($_REQUEST['boards']))
	{
		$_REQUEST['boards'] = explode(',', $_REQUEST['boards']);
		foreach ($_REQUEST['boards'] as $i => $b)
			$_REQUEST['boards'][$i] = (int) $b;

		$request = db_query("
			SELECT b.ID_BOARD, b.numPosts
			FROM {$db_prefix}boards AS b
			WHERE b.ID_BOARD IN (" . implode(', ', $_REQUEST['boards']) . ")
				AND $user_info[query_see_board]
			LIMIT " . count($_REQUEST['boards']), __FILE__, __LINE__);
		$total_posts = 0;
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$boards[] = $row['ID_BOARD'];
			$total_posts += $row['numPosts'];
		}
		mysql_free_result($request);

		if (empty($boards))
			fatal_lang_error('error_no_boards_selected', false);

		$query_this_board = 'b.ID_BOARD IN (' . implode(', ', $boards) . ')';

		// If these boards have a significant number of posts in them...
		if ($total_posts > 100 && $total_posts > $modSettings['totalMessages'] / 12)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 500 - $_REQUEST['start'] * 9);

		$context['page_index'] = constructPageIndex($scripturl . '?action=recent;boards=' . implode(',', $_REQUEST['boards']), $_REQUEST['start'], min(100, $total_posts), 10, false);
	}
	elseif (!empty($board))
	{
		$request = db_query("
			SELECT numPosts
			FROM {$db_prefix}boards
			WHERE ID_BOARD = $board
			LIMIT 1", __FILE__, __LINE__);
		list ($total_posts) = mysql_fetch_row($request);
		mysql_free_result($request);

		$query_this_board = 'b.ID_BOARD = ' . $board;

		// If this board has a significant number of posts in it...
		if ($total_posts > 80 && $total_posts > $modSettings['totalMessages'] / 10)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 600 - $_REQUEST['start'] * 10);

		$context['page_index'] = constructPageIndex($scripturl . '?action=recent;board=' . $board . '.%d', $_REQUEST['start'], min(100, $total_posts), 10, true);
	}
	else
	{
		$query_this_board = $user_info['query_see_board'] . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != $modSettings[recycle_board]" : ''). '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 100 - $_REQUEST['start'] * 6);

		// !!! This isn't accurate because we ignore the recycle bin.
		$context['page_index'] = constructPageIndex($scripturl . '?action=recent', $_REQUEST['start'], min(100, $modSettings['totalMessages']), 10, false);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=recent' . (empty($board) ? (empty($_REQUEST['c']) ? '' : ';c=' . (int) $_REQUEST['c']) : ';board=' . $board . '.0'),
		'name' => $context['page_title']
	);

	// Find the 10 most recent messages they can *view*.
	// !!!SLOW This query is really slow still, probably?
	$request = db_query("
		SELECT m.ID_MSG
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE b.ID_BOARD = m.ID_BOARD
			AND $query_this_board
		ORDER BY m.ID_MSG DESC
		LIMIT $_REQUEST[start], 10", __FILE__, __LINE__);
	$messages = array();
	while ($row = mysql_fetch_assoc($request))
		$messages[] = $row['ID_MSG'];
	mysql_free_result($request);

	// Looks like nothin's happen here... or, at least, nothin' you can see...
	if (empty($messages))
	{
		$context['posts'] = array();
		return;
	}

	// Get all the most recent posts.
	$request = db_query("
		SELECT
			m.ID_MSG, m.subject, m.smileysEnabled, m.posterTime, m.body, m.ID_TOPIC, t.ID_BOARD, b.ID_CAT,
			b.name AS bname, c.name AS cname, t.numReplies, m.ID_MEMBER, m2.ID_MEMBER AS ID_FIRST_MEMBER,
			IFNULL(mem2.realName, m2.posterName) AS firstPosterName, t.ID_FIRST_MSG,
			IFNULL(mem.realName, m.posterName) AS posterName, t.ID_LAST_MSG
		FROM ({$db_prefix}messages AS m, {$db_prefix}messages AS m2, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}categories AS c)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.ID_MEMBER = m2.ID_MEMBER)
		WHERE m2.ID_MSG = t.ID_FIRST_MSG
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
			AND c.ID_CAT = b.ID_CAT
			AND m.ID_MSG IN (" . implode(', ', $messages) . ")
		ORDER BY m.ID_MSG DESC
		LIMIT " . count($messages), __FILE__, __LINE__);
	$counter = $_REQUEST['start'] + 1;
	$context['posts'] = array();
	$board_ids = array('own' => array(), 'any' => array());
	while ($row = mysql_fetch_assoc($request))
	{
		// Censor everything.
		censorText($row['body']);
		censorText($row['subject']);

		// BBC-atize the message.
		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		// And build the array.
		$context['posts'][$row['ID_MSG']] = array(
			'id' => $row['ID_MSG'],
			'counter' => $counter++,
			'category' => array(
				'id' => $row['ID_CAT'],
				'name' => $row['cname'],
				'href' => $scripturl . '#' . $row['ID_CAT'],
				'link' => '<a href="' . $scripturl . '#' . $row['ID_CAT'] . '">' . $row['cname'] . '</a>'
			),
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['bname'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['bname'] . '</a>'
			),
			'topic' => $row['ID_TOPIC'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
			'start' => $row['numReplies'],
			'subject' => $row['subject'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'first_poster' => array(
				'id' => $row['ID_FIRST_MEMBER'],
				'name' => $row['firstPosterName'],
				'href' => empty($row['ID_FIRST_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_FIRST_MEMBER'],
				'link' => empty($row['ID_FIRST_MEMBER']) ? $row['firstPosterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_FIRST_MEMBER'] . '">' . $row['firstPosterName'] . '</a>'
			),
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['posterName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>'
			),
			'message' => $row['body'],
			'can_reply' => false,
			'can_mark_notify' => false,
			'can_delete' => false,
			'delete_possible' => ($row['ID_FIRST_MSG'] != $row['ID_MSG'] || $row['ID_LAST_MSG'] == $row['ID_MSG']) && (empty($modSettings['edit_disable_time']) || $row['posterTime'] + $modSettings['edit_disable_time'] * 60 >= time()),
		);

		if ($ID_MEMBER == $row['ID_FIRST_MEMBER'])
			$board_ids['own'][$row['ID_BOARD']][] = $row['ID_MSG'];
		$board_ids['any'][$row['ID_BOARD']][] = $row['ID_MSG'];
	}
	mysql_free_result($request);

	// There might be - and are - different permissions between any and own.
	$permissions = array(
		'own' => array(
			'post_reply_own' => 'can_reply',
			'delete_own' => 'can_delete',
		),
		'any' => array(
			'post_reply_any' => 'can_reply',
			'mark_any_notify' => 'can_mark_notify',
			'delete_any' => 'can_delete',
		)
	);

	// Now go through all the permissions, looking for boards they can do it on.
	foreach ($permissions as $type => $list)
	{
		foreach ($list as $permission => $allowed)
		{
			// They can do it on these boards...
			$boards = boardsAllowedTo($permission);

			// If 0 is the only thing in the array, they can do it everywhere!
			if (!empty($boards) && $boards[0] == 0)
				$boards = array_keys($board_ids[$type]);

			// Go through the boards, and look for posts they can do this on.
			foreach ($boards as $board_id)
			{
				// Hmm, they have permission, but there are no topics from that board on this page.
				if (!isset($board_ids[$type][$board_id]))
					continue;

				// Okay, looks like they can do it for these posts.
				foreach ($board_ids[$type][$board_id] as $counter)
					if ($type == 'any' || $context['posts'][$counter]['poster']['id'] == $ID_MEMBER)
						$context['posts'][$counter][$allowed] = true;
			}
		}
	}

	// Some posts - the first posts - can't just be deleted.
	foreach ($context['posts'] as $counter => $dummy)
		$context['posts'][$counter]['can_delete'] &= $context['posts'][$counter]['delete_possible'];
}

// Find unread topics and replies.
function UnreadTopics()
{
	global $board, $txt, $scripturl, $db_prefix, $sourcedir;
	global $ID_MEMBER, $user_info, $context, $settings, $modSettings, $func;

	// Guests can't have unread things, we don't know anything about them.
	is_not_guest();

	// Prefetching + lots of MySQL work = bad mojo.
	if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	{
		ob_end_clean();
		header('HTTP/1.1 403 Forbidden');
		die;
	}

	$context['showing_all_topics'] = isset($_GET['all']);
	if ($_REQUEST['action'] == 'unread')
		$context['page_title'] = $context['showing_all_topics'] ? $txt['unread_topics_all'] : $txt['unread_topics_visit'];
	else
		$context['page_title'] = $txt['unread_replies'];

	if ($context['showing_all_topics'] && !empty($context['load_average']) && !empty($modSettings['loadavg_allunread']) && $context['load_average'] >= $modSettings['loadavg_allunread'])
		fatal_lang_error('loadavg_allunread_disabled', false);
	elseif ($_REQUEST['action'] != 'unread' && !empty($context['load_average']) && !empty($modSettings['loadavg_unreadreplies']) && $context['load_average'] >= $modSettings['loadavg_unreadreplies'])
		fatal_lang_error('loadavg_unreadreplies_disabled', false);

	// Are we specifying any specific board?
	if (!empty($board))
	{
		$query_this_board = 'ID_BOARD = ' . $board;
		$context['querystring_board_limits'] = ';board=' . $board . '.%d';
	}
	elseif (!empty($_REQUEST['boards']))
	{
		$_REQUEST['boards'] = explode(',', $_REQUEST['boards']);
		foreach ($_REQUEST['boards'] as $i => $b)
			$_REQUEST['boards'][$i] = (int) $b;

		$request = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b.ID_BOARD IN (" . implode(', ', $_REQUEST['boards']) . ")", __FILE__, __LINE__);
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
			$boards[] = $row['ID_BOARD'];
		mysql_free_result($request);

		if (empty($boards))
			fatal_lang_error('error_no_boards_selected');

		$query_this_board = 'ID_BOARD IN (' . implode(', ', $boards) . ')';
		$context['querystring_board_limits'] = ';boards=' . implode(',', $boards) . ';start=%d';
	}
	elseif (!empty($_REQUEST['c']))
	{
		$_REQUEST['c'] = explode(',', $_REQUEST['c']);
		foreach ($_REQUEST['c'] as $i => $c)
			$_REQUEST['c'][$i] = (int) $c;

		$request = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b.ID_CAT IN (" . implode(', ', $_REQUEST['c']) . ")", __FILE__, __LINE__);
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
			$boards[] = $row['ID_BOARD'];
		mysql_free_result($request);

		if (empty($boards))
			fatal_lang_error('error_no_boards_selected');

		$query_this_board = 'ID_BOARD IN (' . implode(', ', $boards) . ')';
		$context['querystring_board_limits'] = ';c=' . implode(',', $_REQUEST['c']) . ';start=%d';
	}
	else
	{
		// Don't bother to show deleted posts!
		$request = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
				AND b.ID_BOARD != " . (int) $modSettings['recycle_board'] : ''), __FILE__, __LINE__);
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
			$boards[] = $row['ID_BOARD'];
		mysql_free_result($request);

		if (empty($boards))
			fatal_lang_error('error_no_boards_selected');

		$query_this_board = 'ID_BOARD IN (' . implode(', ', $boards) . ')';
		$context['querystring_board_limits'] = ';start=%d';
		$context['no_board_limits'] = true;
	}

	$sort_methods = array(
		'subject' => 'ms.subject',
		'starter' => 'IFNULL(mems.realName, ms.posterName)',
		'replies' => 't.numReplies',
		'views' => 't.numViews',
		'first_post' => 't.ID_TOPIC',
		'last_post' => 't.ID_LAST_MSG'
	);

	// The default is the most logical: newest first.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'last_post';
		$_REQUEST['sort'] = 't.ID_LAST_MSG';
		$ascending = isset($_REQUEST['asc']);

		$context['querystring_sort_limits'] = $ascending ? ';asc' : '';
	}
	// But, for other methods the default sort is ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$_REQUEST['sort'] = $sort_methods[$_REQUEST['sort']];
		$ascending = !isset($_REQUEST['desc']);

		$context['querystring_sort_limits'] = ';sort=' . $context['sort_by'] . ($ascending ? '' : ';desc');
	}
	$context['sort_direction'] = $ascending ? 'up' : 'down';

	if (!empty($_REQUEST['c']) && is_array($_REQUEST['c']) && count($_REQUEST['c']) == 1)
	{
		$request = db_query("
			SELECT name
			FROM {$db_prefix}categories
			WHERE ID_CAT = " . (int) $_REQUEST['c'][0] . "
			LIMIT 1", __FILE__, __LINE__);
		list ($name) = mysql_fetch_row($request);
		mysql_free_result($request);

		$context['linktree'][] = array(
			'url' => $scripturl . '#' . (int) $_REQUEST['c'][0],
			'name' => $name
		);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=' . $_REQUEST['action'] . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits'],
		'name' => $_REQUEST['action'] == 'unread' ? $txt['unread_topics_visit'] : $txt['unread_replies']
	);

	if ($context['showing_all_topics'])
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=' . $_REQUEST['action'] . ';all' . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits'],
			'name' => $txt['unread_topics_all']
		);
	else
		$txt['unread_topics_visit_none'] = strtr($txt['unread_topics_visit_none'], array('?action=unread;all' => '?action=unread;all' . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits']));

	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_recent';
	else
	{
		loadTemplate('Recent');
		$context['sub_template'] = $_REQUEST['action'] == 'unread' ? 'unread' : 'replies';
	}

	// Setup the default topic icons... for checking they exist and the like ;)
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$context['icon_sources'] = array();
	foreach ($stable_icons as $icon)
		$context['icon_sources'][$icon] = 'images_url';

	$is_topics = $_REQUEST['action'] == 'unread';

	// This part is the same for each query.
	$select_clause = '
				ms.subject AS firstSubject, ms.posterTime AS firstPosterTime, ms.ID_TOPIC, t.ID_BOARD, b.name AS bname,
				t.numReplies, t.numViews, ms.ID_MEMBER AS ID_FIRST_MEMBER, ml.ID_MEMBER AS ID_LAST_MEMBER,
				ml.posterTime AS lastPosterTime, IFNULL(mems.realName, ms.posterName) AS firstPosterName,
				IFNULL(meml.realName, ml.posterName) AS lastPosterName, ml.subject AS lastSubject,
				ml.icon AS lastIcon, ms.icon AS firstIcon, t.ID_POLL, t.isSticky, t.locked, ml.modifiedTime AS lastModifiedTime,
				IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from, LEFT(ml.body, 384) AS lastBody, LEFT(ms.body, 384) AS firstBody,
				ml.smileysEnabled AS lastSmileys, ms.smileysEnabled AS firstSmileys, t.ID_FIRST_MSG, t.ID_LAST_MSG';

	if ($context['showing_all_topics'])
	{
		if (!empty($board))
		{
			$request = db_query("
				SELECT MIN(ID_MSG)
				FROM {$db_prefix}log_mark_read
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_BOARD = $board", __FILE__, __LINE__);
			list ($earliest_msg) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
		{
			$request = db_query("
				SELECT MIN(lmr.ID_MSG)
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = b.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
				WHERE $user_info[query_see_board]", __FILE__, __LINE__);
			list ($earliest_msg) = mysql_fetch_row($request);
			mysql_free_result($request);
		}

		// This is needed in case of topics marked unread.
		if (empty($earliest_msg))
			$earliest_msg = 0;
		else
		{
			// Using caching, when possible, to ignore the below slow query.
			if (isset($_SESSION['cached_log_time']) && $_SESSION['cached_log_time'][0] + 45 > time())
				$earliest_msg2 = $_SESSION['cached_log_time'][1];
			else
			{
				// This query is pretty slow, but it's needed to ensure nothing crucial is ignored.
				$request = db_query("
					SELECT MIN(ID_MSG)
					FROM {$db_prefix}log_topics
					WHERE ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
				list ($earliest_msg2) = mysql_fetch_row($request);
				mysql_free_result($request);

				// In theory this could be zero, if the first ever post is unread, so fudge it ;)
				if ($earliest_msg2 == 0)
					$earliest_msg2 = -1;

				$_SESSION['cached_log_time'] = array(time(), $earliest_msg2);
			}

			$earliest_msg = min($earliest_msg2, $earliest_msg);
		}
	}

	// !!! Add modifiedTime in for logTime check?

	if ($modSettings['totalMessages'] > 100000 && $context['showing_all_topics'])
	{
		db_query("
			DROP TABLE IF EXISTS {$db_prefix}log_topics_unread", false, false);

		// Let's copy things out of the log_topics table, to reduce searching.
		$have_temp_table = db_query("
			CREATE TEMPORARY TABLE {$db_prefix}log_topics_unread (
				PRIMARY KEY (ID_TOPIC)
			)
			SELECT lt.ID_TOPIC, lt.ID_MSG
			FROM ({$db_prefix}topics AS t, {$db_prefix}log_topics AS lt)
			WHERE lt.ID_TOPIC = t.ID_TOPIC
				AND lt.ID_MEMBER = $ID_MEMBER
				AND t.$query_this_board" . (!empty($earliest_msg) ? "
				AND t.ID_LAST_MSG > $earliest_msg" : ''), false, false) !== false;
	}
	else
		$have_temp_table = false;

	if ($context['showing_all_topics'] && $have_temp_table)
	{
		$request = db_query("
			SELECT COUNT(*), MIN(t.ID_LAST_MSG)
			FROM {$db_prefix}topics AS t
				LEFT JOIN {$db_prefix}log_topics_unread AS lt ON (lt.ID_TOPIC = t.ID_TOPIC)
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
			WHERE t.$query_this_board" . ($context['showing_all_topics'] && !empty($earliest_msg) ? "
				AND t.ID_LAST_MSG > $earliest_msg" : "
				AND t.ID_LAST_MSG > $_SESSION[ID_MSG_LAST_VISIT]") . "
				AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < t.ID_LAST_MSG", __FILE__, __LINE__);
		list ($num_topics, $min_message) = mysql_fetch_row($request);
		mysql_free_result($request);

		// Make sure the starting place makes sense and construct the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . $context['querystring_board_limits'] . $context['querystring_sort_limits'], $_REQUEST['start'], $num_topics, $modSettings['defaultMaxTopics'], true);
		$context['current_page'] = (int) $_REQUEST['start'] / $modSettings['defaultMaxTopics'];

		$context['links'] = array(
			'first' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits'] : '',
			'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] - $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'next' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] + $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'last' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) * $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'up' => $scripturl,
		);
		$context['page_info'] = array(
			'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxTopics'] + 1,
			'num_pages' => floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) + 1
		);

		if ($num_topics == 0)
		{
			$context['topics'] = array();
			if ($context['querystring_board_limits'] == ';start=%d')
				$context['querystring_board_limits'] = '';
			else
				$context['querystring_board_limits'] = sprintf($context['querystring_board_limits'], $_REQUEST['start']);
			return;
		}
		else
			$min_message = (int) $min_message;

		$request = db_query("
			SELECT $select_clause
			FROM ({$db_prefix}messages AS ms, {$db_prefix}messages AS ml, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
				LEFT JOIN {$db_prefix}members AS mems ON (mems.ID_MEMBER = ms.ID_MEMBER)
				LEFT JOIN {$db_prefix}members AS meml ON (meml.ID_MEMBER = ml.ID_MEMBER)
				LEFT JOIN {$db_prefix}log_topics_unread AS lt ON (lt.ID_TOPIC = t.ID_TOPIC)
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
			WHERE t.ID_TOPIC = ms.ID_TOPIC
				AND b.ID_BOARD = t.ID_BOARD
				AND t.$query_this_board
				AND ms.ID_MSG = t.ID_FIRST_MSG
				AND ml.ID_MSG = t.ID_LAST_MSG
				AND t.ID_LAST_MSG >= $min_message
				AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < t.ID_LAST_MSG
			ORDER BY " . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
			LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
	}
	elseif ($is_topics)
	{
		$request = db_query("
			SELECT COUNT(*), MIN(t.ID_LAST_MSG)
			FROM {$db_prefix}topics AS t" . ($have_temp_table ? "
				LEFT JOIN {$db_prefix}log_topics_unread AS lt ON (lt.ID_TOPIC = t.ID_TOPIC)" : "
				LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)") . "
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
			WHERE t.$query_this_board" . ($context['showing_all_topics'] && !empty($earliest_msg) ? "
				AND t.ID_LAST_MSG > $earliest_msg" : "
				AND t.ID_LAST_MSG > $_SESSION[ID_MSG_LAST_VISIT]") . "
				AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < t.ID_LAST_MSG", __FILE__, __LINE__);
		list ($num_topics, $min_message) = mysql_fetch_row($request);
		mysql_free_result($request);

		// Make sure the starting place makes sense and construct the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . $context['querystring_board_limits'] . $context['querystring_sort_limits'], $_REQUEST['start'], $num_topics, $modSettings['defaultMaxTopics'], true);
		$context['current_page'] = (int) $_REQUEST['start'] / $modSettings['defaultMaxTopics'];

		$context['links'] = array(
			'first' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits'] : '',
			'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] - $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'next' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] + $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'last' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) * $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'up' => $scripturl,
		);
		$context['page_info'] = array(
			'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxTopics'] + 1,
			'num_pages' => floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) + 1
		);

		if ($num_topics == 0)
		{
			$context['topics'] = array();
			if ($context['querystring_board_limits'] == ';start=%d')
				$context['querystring_board_limits'] = '';
			else
				$context['querystring_board_limits'] = sprintf($context['querystring_board_limits'], $_REQUEST['start']);
			return;
		}
		else
			$min_message = (int) $min_message;

		$request = db_query("
			SELECT $select_clause
			FROM ({$db_prefix}messages AS ms, {$db_prefix}messages AS ml, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
				LEFT JOIN {$db_prefix}members AS mems ON (mems.ID_MEMBER = ms.ID_MEMBER)
				LEFT JOIN {$db_prefix}members AS meml ON (meml.ID_MEMBER = ml.ID_MEMBER)" . ($have_temp_table ? "
				LEFT JOIN {$db_prefix}log_topics_unread AS lt ON (lt.ID_TOPIC = t.ID_TOPIC)" : "
				LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)") . "
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
			WHERE t.ID_TOPIC = ms.ID_TOPIC
				AND b.ID_BOARD = t.ID_BOARD
				AND t.$query_this_board
				AND ms.ID_MSG = t.ID_FIRST_MSG
				AND ml.ID_MSG = t.ID_LAST_MSG
				AND t.ID_LAST_MSG >= $min_message
				AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < ml.ID_MSG
			ORDER BY " . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
			LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
	}
	else
	{
		if ($modSettings['totalMessages'] > 100000)
		{
			db_query("
				DROP TABLE IF EXISTS {$db_prefix}topics_posted_in", false, false);

			db_query("
				DROP TABLE IF EXISTS {$db_prefix}log_topics_posted_in", false, false);

			$sortKey_joins = array(
				'ms.subject' => "
					INNER JOIN {$db_prefix}messages AS ms ON (ms.ID_MSG = t.ID_FIRST_MSG)",
				'IFNULL(mems.realName, ms.posterName)' => "
					INNER JOIN {$db_prefix}messages AS ms ON (ms.ID_MSG = t.ID_FIRST_MSG)
					LEFT JOIN {$db_prefix}members AS mems ON (mems.ID_MEMBER = ms.ID_MEMBER)",
			);

			// The main benefit of this temporary table is not that it's faster; it's that it avoids locks later.
			$have_temp_table = db_query("
				CREATE TEMPORARY TABLE {$db_prefix}topics_posted_in (
					ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
					ID_BOARD smallint(5) unsigned NOT NULL default '0',
					ID_LAST_MSG int(10) unsigned NOT NULL default '0',
					ID_MSG int(10) unsigned NOT NULL default '0',
					PRIMARY KEY (ID_TOPIC)
				)
				SELECT t.ID_TOPIC, t.ID_BOARD, t.ID_LAST_MSG, IFNULL(lmr.ID_MSG, 0) AS ID_MSG" . (!in_array($_REQUEST['sort'], array('t.ID_LAST_MSG', 't.ID_TOPIC')) ? ", $_REQUEST[sort] AS sortKey" : '') . "
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
					LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)" . (isset($sortKey_joins[$_REQUEST['sort']]) ? $sortKey_joins[$_REQUEST['sort']] : '') . "
				WHERE m.ID_MEMBER = $ID_MEMBER
					AND t.ID_TOPIC = m.ID_TOPIC" . (!empty($board) ? "
					AND t.ID_BOARD = $board" : '') . "
				GROUP BY m.ID_TOPIC", false, false) !== false;

			// If that worked, create a sample of the log_topics table too.
			if ($have_temp_table)
				$have_temp_table = db_query("
					CREATE TEMPORARY TABLE {$db_prefix}log_topics_posted_in (
						PRIMARY KEY (ID_TOPIC)
					)
					SELECT lt.ID_TOPIC, lt.ID_MSG
					FROM ({$db_prefix}log_topics AS lt, {$db_prefix}topics_posted_in AS pi)
					WHERE lt.ID_MEMBER = $ID_MEMBER
						AND lt.ID_TOPIC = pi.ID_TOPIC", false, false) !== false;
		}

		if ($have_temp_table)
		{
			$request = db_query("
				SELECT COUNT(*)
				FROM ({$db_prefix}topics_posted_in AS pi)
					LEFT JOIN {$db_prefix}log_topics_posted_in AS lt ON (lt.ID_TOPIC = pi.ID_TOPIC)
				WHERE pi.$query_this_board
					AND IFNULL(lt.ID_MSG, pi.ID_MSG) < pi.ID_LAST_MSG", __FILE__, __LINE__);
			list ($num_topics) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
		{
			$request = db_query("
				SELECT COUNT(DISTINCT t.ID_TOPIC), MIN(t.ID_LAST_MSG)
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
					LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
					LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
				WHERE t.$query_this_board
					AND m.ID_TOPIC = t.ID_TOPIC
					AND m.ID_MEMBER = $ID_MEMBER
					AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < t.ID_LAST_MSG", __FILE__, __LINE__);
			list ($num_topics, $min_message) = mysql_fetch_row($request);
			mysql_free_result($request);
		}

		// Make sure the starting place makes sense and construct the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=' . $_REQUEST['action'] . $context['querystring_board_limits'] . $context['querystring_sort_limits'], $_REQUEST['start'], $num_topics, $modSettings['defaultMaxTopics'], true);
		$context['current_page'] = (int) $_REQUEST['start'] / $modSettings['defaultMaxTopics'];

		$context['links'] = array(
			'first' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], 0) . $context['querystring_sort_limits'] : '',
			'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] - $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'next' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], $_REQUEST['start'] + $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'last' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $num_topics ? $scripturl . '?action=' . $_REQUEST['action'] . ($context['showing_all_topics'] ? ';all' : '') . sprintf($context['querystring_board_limits'], floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) * $modSettings['defaultMaxTopics']) . $context['querystring_sort_limits'] : '',
			'up' => $scripturl,
		);
		$context['page_info'] = array(
			'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxTopics'] + 1,
			'num_pages' => floor(($num_topics - 1) / $modSettings['defaultMaxTopics']) + 1
		);

		if ($num_topics == 0)
		{
			$context['topics'] = array();
			if ($context['querystring_board_limits'] == ';start=%d')
				$context['querystring_board_limits'] = '';
			else
				$context['querystring_board_limits'] = sprintf($context['querystring_board_limits'], $_REQUEST['start']);
			return;
		}

		if ($have_temp_table)
			$request = db_query("
				SELECT t.ID_TOPIC
				FROM {$db_prefix}topics_posted_in AS t
					LEFT JOIN {$db_prefix}log_topics_posted_in AS lt ON (lt.ID_TOPIC = t.ID_TOPIC)
				WHERE t.$query_this_board
					AND IFNULL(lt.ID_MSG, t.ID_MSG) < t.ID_LAST_MSG
				ORDER BY " . (in_array($_REQUEST['sort'], array('t.ID_LAST_MSG', 't.ID_TOPIC')) ? $_REQUEST['sort'] : 't.sortKey') . ($ascending ? '' : ' DESC') . "
				LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
		else
			$request = db_query("
				SELECT DISTINCT t.ID_TOPIC
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m" . (strpos($_REQUEST['sort'], 'ms.') === false ? '' : ", {$db_prefix}messages AS ms") . ')' . (strpos($_REQUEST['sort'], 'mems.') === false ? '' : "
					LEFT JOIN {$db_prefix}members AS mems ON (mems.ID_MEMBER = ms.ID_MEMBER)") ."
					LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
					LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
				WHERE m.ID_TOPIC = t.ID_TOPIC
					AND m.ID_MEMBER = $ID_MEMBER
					AND t.$query_this_board
					AND t.ID_LAST_MSG >= " . (int) $min_message . "
					AND IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) < t.ID_LAST_MSG" . (strpos($_REQUEST['sort'], 'ms.') !== false ? "
					AND ms.ID_MSG = t.ID_FIRST_MSG" : '') . "
				ORDER BY " . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
				LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);

		$topics = array();
		while ($row = mysql_fetch_assoc($request))
			$topics[] = $row['ID_TOPIC'];
		mysql_free_result($request);

		// Sanity... where have you gone?
		if (empty($topics))
		{
			$context['topics'] = array();
			if ($context['querystring_board_limits'] == ';start=%d')
				$context['querystring_board_limits'] = '';
			else
				$context['querystring_board_limits'] = sprintf($context['querystring_board_limits'], $_REQUEST['start']);
			return;
		}

		$request = db_query("
			SELECT $select_clause
			FROM ({$db_prefix}messages AS ms, {$db_prefix}messages AS ml, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
				LEFT JOIN {$db_prefix}members AS mems ON (mems.ID_MEMBER = ms.ID_MEMBER)
				LEFT JOIN {$db_prefix}members AS meml ON (meml.ID_MEMBER = ml.ID_MEMBER)
				LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = t.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
			WHERE t.ID_TOPIC IN (" . implode(', ', $topics) . ")
				AND t.ID_TOPIC = ms.ID_TOPIC
				AND b.ID_BOARD = t.ID_BOARD
				AND ms.ID_MSG = t.ID_FIRST_MSG
				AND ml.ID_MSG = t.ID_LAST_MSG
			ORDER BY " . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
			LIMIT " . count($topics), __FILE__, __LINE__);
	}

	$context['topics'] = array();
	$topic_ids = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if ($row['ID_POLL'] > 0 && $modSettings['pollMode'] == '0')
			continue;

		$topic_ids[] = $row['ID_TOPIC'];

		// Clip the strings first because censoring is slow :/. (for some reason?)
		$row['firstBody'] = strip_tags(strtr(parse_bbc($row['firstBody'], $row['firstSmileys'], $row['ID_FIRST_MSG']), array('<br />' => '&#10;')));
		if ($func['strlen']($row['firstBody']) > 128)
			$row['firstBody'] = $func['substr']($row['firstBody'], 0, 128) . '...';
		$row['lastBody'] = strip_tags(strtr(parse_bbc($row['lastBody'], $row['lastSmileys'], $row['ID_LAST_MSG']), array('<br />' => '&#10;')));
		if ($func['strlen']($row['lastBody']) > 128)
			$row['lastBody'] = $func['substr']($row['lastBody'], 0, 128) . '...';

		// Do a bit of censoring...
		censorText($row['firstSubject']);
		censorText($row['firstBody']);

		// But don't do it twice, it can be a slow ordeal!
		if ($row['ID_FIRST_MSG'] == $row['ID_LAST_MSG'])
		{
			$row['lastSubject'] = $row['firstSubject'];
			$row['lastBody'] = $row['firstBody'];
		}
		else
		{
			censorText($row['lastSubject']);
			censorText($row['lastBody']);
		}

		// Decide how many pages the topic should have.
		$topic_length = $row['numReplies'] + 1;
		if ($topic_length > $modSettings['defaultMaxMessages'])
		{
			$tmppages = array();
			$tmpa = 1;
			for ($tmpb = 0; $tmpb < $topic_length; $tmpb += $modSettings['defaultMaxMessages'])
			{
				$tmppages[] = '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.' . $tmpb . ';topicseen">' . $tmpa . '</a>';
				$tmpa++;
			}
			// Show links to all the pages?
			if (count($tmppages) <= 5)
				$pages = '&#171; ' . implode(' ', $tmppages);
			// Or skip a few?
			else
				$pages = '&#171; ' . $tmppages[0] . ' ' . $tmppages[1] . ' ... ' . $tmppages[count($tmppages) - 2] . ' ' . $tmppages[count($tmppages) - 1];

			if (!empty($modSettings['enableAllMessages']) && $topic_length < $modSettings['enableAllMessages'])
				$pages .= ' &nbsp;<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0;all">' . $txt[190] . '</a>';
			$pages .= ' &#187;';
		}
		else
			$pages = '';

		// We need to check the topic icons exist... you can never be too sure!
		if (empty($modSettings['messageIconChecks_disable']))
		{
			// First icon first... as you'd expect.
			if (!isset($context['icon_sources'][$row['firstIcon']]))
				$context['icon_sources'][$row['firstIcon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['firstIcon'] . '.gif') ? 'images_url' : 'default_images_url';
			// Last icon... last... duh.
			if (!isset($context['icon_sources'][$row['lastIcon']]))
				$context['icon_sources'][$row['lastIcon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['lastIcon'] . '.gif') ? 'images_url' : 'default_images_url';
		}

		// And build the array.
		$context['topics'][$row['ID_TOPIC']] = array(
			'id' => $row['ID_TOPIC'],
			'first_post' => array(
				'id' => $row['ID_FIRST_MSG'],
				'member' => array(
					'name' => $row['firstPosterName'],
					'id' => $row['ID_FIRST_MEMBER'],
					'href' => $scripturl . '?action=profile;u=' . $row['ID_FIRST_MEMBER'],
					'link' => !empty($row['ID_FIRST_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_FIRST_MEMBER'] . '" title="' . $txt[92] . ' ' . $row['firstPosterName'] . '">' . $row['firstPosterName'] . '</a>' : $row['firstPosterName']
				),
				'time' => timeformat($row['firstPosterTime']),
				'timestamp' => forum_time(true, $row['firstPosterTime']),
				'subject' => $row['firstSubject'],
				'preview' => $row['firstBody'],
				'icon' => $row['firstIcon'],
				'icon_url' => $settings[$context['icon_sources'][$row['firstIcon']]] . '/post/' . $row['firstIcon'] . '.gif',
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0;topicseen',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0;topicseen">' . $row['firstSubject'] . '</a>'
			),
			'last_post' => array(
				'id' => $row['ID_LAST_MSG'],
				'member' => array(
					'name' => $row['lastPosterName'],
					'id' => $row['ID_LAST_MEMBER'],
					'href' => $scripturl . '?action=profile;u=' . $row['ID_LAST_MEMBER'],
					'link' => !empty($row['ID_LAST_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_LAST_MEMBER'] . '">' . $row['lastPosterName'] . '</a>' : $row['lastPosterName']
				),
				'time' => timeformat($row['lastPosterTime']),
				'timestamp' => forum_time(true, $row['lastPosterTime']),
				'subject' => $row['lastSubject'],
				'preview' => $row['lastBody'],
				'icon' => $row['lastIcon'],
				'icon_url' => $settings[$context['icon_sources'][$row['lastIcon']]] . '/post/' . $row['lastIcon'] . '.gif',
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['ID_LAST_MSG']) . ';topicseen#msg' . $row['ID_LAST_MSG'],
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['ID_LAST_MSG']) . ';topicseen#msg' . $row['ID_LAST_MSG'] . '">' . $row['lastSubject'] . '</a>'
			),
			'new_from' => $row['new_from'],
			'new_href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['new_from'] . ';topicseen#new',
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['new_from']) . ';topicseen' . ($row['numReplies'] == 0 ? '' : 'new'),
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['new_from']) . ';topicseen#msg' . $row['new_from'] . '">' . $row['firstSubject'] . '</a>',
			'is_sticky' => !empty($modSettings['enableStickyTopics']) && !empty($row['isSticky']),
			'is_locked' => !empty($row['locked']),
			'is_poll' => $modSettings['pollMode'] == '1' && $row['ID_POLL'] > 0,
			'is_hot' => $row['numReplies'] >= $modSettings['hotTopicPosts'],
			'is_very_hot' => $row['numReplies'] >= $modSettings['hotTopicVeryPosts'],
			'is_posted_in' => false,
			'icon' => $row['firstIcon'],
			'icon_url' => $settings[$context['icon_sources'][$row['firstIcon']]] . '/post/' . $row['firstIcon'] . '.gif',
			'subject' => $row['firstSubject'],
			'pages' => $pages,
			'replies' => $row['numReplies'],
			'views' => $row['numViews'],
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['bname'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['bname'] . '</a>'
			)
		);

		determineTopicClass($context['topics'][$row['ID_TOPIC']]);
	}
	mysql_free_result($request);

	if ($is_topics && !empty($modSettings['enableParticipation']) && !empty($topic_ids))
	{
		$result = db_query("
			SELECT ID_TOPIC
			FROM {$db_prefix}messages
			WHERE ID_TOPIC IN (" . implode(', ', $topic_ids) . ")
				AND ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
		{
			if (empty($context['topics'][$row['ID_TOPIC']]['is_posted_in']))
			{
				$context['topics'][$row['ID_TOPIC']]['is_posted_in'] = true;
				$context['topics'][$row['ID_TOPIC']]['class'] = 'my_' . $context['topics'][$row['ID_TOPIC']]['class'];
			}
		}
		mysql_free_result($result);
	}

	$context['querystring_board_limits'] = sprintf($context['querystring_board_limits'], $_REQUEST['start']);
	$context['topics_to_mark'] = implode('-', $topic_ids);
}

?>