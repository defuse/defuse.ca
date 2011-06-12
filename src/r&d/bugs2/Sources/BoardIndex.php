<?php
/**********************************************************************************
* BoardIndex.php                                                                  *
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

/*	The single function this file contains is used to display the main
	board index.  It uses just the following functions:

	void BoardIndex()
		- shows the board index.
		- uses the BoardIndex template, and main sub template.
		- may use the boardindex subtemplate for wireless support.
		- updates the most online statistics.
		- is accessed by ?action=boardindex.

	bool calendarDoIndex()
		- prepares the calendar data for the board index.
		- takes care of caching it for speed.
		- depends upon these settings: cal_showeventsonindex,
		  cal_showbdaysonindex, cal_showholidaysonindex.
		- returns whether there is anything to display.
*/

// Show the board index!
function BoardIndex()
{
	global $txt, $scripturl, $db_prefix, $ID_MEMBER, $user_info, $sourcedir;
	global $modSettings, $context, $settings;

	// For wireless, we use the Wireless template...
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_boardindex';
	else
		loadTemplate('BoardIndex');

	// Remember the most recent topic for optimizing the recent posts feature.
	$most_recent_topic = array(
		'timestamp' => 0,
		'ref' => null
	);

	// Find all boards and categories, as well as related information.  This will be sorted by the natural order of boards and categories, which we control.
	$result_boards = db_query("
		SELECT
			c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.description,
			b.numPosts, b.numTopics, b.ID_PARENT, IFNULL(m.posterTime, 0) AS posterTime,
			IFNULL(mem.memberName, m.posterName) AS posterName, m.subject, m.ID_TOPIC,
			IFNULL(mem.realName, m.posterName) AS realName," . ($user_info['is_guest'] ? "
			1 AS isRead, 0 AS new_from" : "
			(IFNULL(lb.ID_MSG, 0) >= b.ID_MSG_UPDATED) AS isRead, IFNULL(lb.ID_MSG, -1) + 1 AS new_from,
			c.canCollapse, IFNULL(cc.ID_MEMBER, 0) AS isCollapsed") . ",
			IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER, m.ID_MSG,
			IFNULL(mods_mem.ID_MEMBER, 0) AS ID_MODERATOR, mods_mem.realName AS modRealName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
			LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG = b.ID_LAST_MSG)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
			LEFT JOIN {$db_prefix}collapsed_categories AS cc ON (cc.ID_CAT = c.ID_CAT AND cc.ID_MEMBER = $ID_MEMBER)" : '') . "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mods_mem ON (mods_mem.ID_MEMBER = mods.ID_MEMBER)
		WHERE $user_info[query_see_board]" . (empty($modSettings['countChildPosts']) ? "
			AND b.childLevel <= 1" : ''), __FILE__, __LINE__);

	// Run through the categories and boards....
	$context['categories'] = array();
	while ($row_board = mysql_fetch_assoc($result_boards))
	{
		// Haven't set this category yet.
		if (empty($context['categories'][$row_board['ID_CAT']]))
		{
			$context['categories'][$row_board['ID_CAT']] = array(
				'id' => $row_board['ID_CAT'],
				'name' => $row_board['catName'],
				'is_collapsed' => isset($row_board['canCollapse']) && $row_board['canCollapse'] == 1 && $row_board['isCollapsed'] > 0,
				'can_collapse' => isset($row_board['canCollapse']) && $row_board['canCollapse'] == 1,
				'collapse_href' => isset($row_board['canCollapse']) ? $scripturl . '?action=collapse;c=' . $row_board['ID_CAT'] . ';sa=' . ($row_board['isCollapsed'] > 0 ? 'expand' : 'collapse') . ';sesc=' . $context['session_id'] . '#' . $row_board['ID_CAT'] : '',
				'collapse_image' => isset($row_board['canCollapse']) ? '<img src="' . $settings['images_url'] . '/' . ($row_board['isCollapsed'] > 0 ? 'expand.gif" alt="+"' : 'collapse.gif" alt="-"') . ' border="0" />' : '',
				'href' => $scripturl . '#' . $row_board['ID_CAT'],
				'boards' => array(),
				'new' => false
			);
			$context['categories'][$row_board['ID_CAT']]['link'] = '<a name="' . $row_board['ID_CAT'] . '" href="' . (isset($row_board['canCollapse']) ? $context['categories'][$row_board['ID_CAT']]['collapse_href'] : $context['categories'][$row_board['ID_CAT']]['href']) . '">' . $row_board['catName'] . '</a>';
		}

		// If this board has new posts in it (and isn't the recycle bin!) then the category is new.
		if (empty($modSettings['recycle_enable']) || $modSettings['recycle_board'] != $row_board['ID_BOARD'])
			$context['categories'][$row_board['ID_CAT']]['new'] |= empty($row_board['isRead']) && $row_board['posterName'] != '';

		// Collapsed category - don't do any of this.
		if ($context['categories'][$row_board['ID_CAT']]['is_collapsed'])
			continue;

		// Let's save some typing.  Climbing the array might be slower, anyhow.
		$this_category = &$context['categories'][$row_board['ID_CAT']]['boards'];

		// This is a parent board.
		if (empty($row_board['ID_PARENT']))
		{
			// Is this a new board, or just another moderator?
			if (!isset($this_category[$row_board['ID_BOARD']]))
			{
				// Not a child.
				$isChild = false;

				$this_category[$row_board['ID_BOARD']] = array(
					'new' => empty($row_board['isRead']),
					'id' => $row_board['ID_BOARD'],
					'name' => $row_board['boardName'],
					'description' => $row_board['description'],
					'moderators' => array(),
					'link_moderators' => array(),
					'children' => array(),
					'link_children' => array(),
					'children_new' => false,
					'topics' => $row_board['numTopics'],
					'posts' => $row_board['numPosts'],
					'href' => $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0',
					'link' => '<a href="' . $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0">' . $row_board['boardName'] . '</a>'
				);
			}
			if (!empty($row_board['ID_MODERATOR']))
			{
				$this_category[$row_board['ID_BOARD']]['moderators'][$row_board['ID_MODERATOR']] = array(
					'id' => $row_board['ID_MODERATOR'],
					'name' => $row_board['modRealName'],
					'href' => $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row_board['modRealName'] . '</a>'
				);
				$this_category[$row_board['ID_BOARD']]['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row_board['modRealName'] . '</a>';
			}
		}
		// Found a child board.... make sure we've found its parent and the child hasn't been set already.
		elseif (isset($this_category[$row_board['ID_PARENT']]['children']) && !isset($this_category[$row_board['ID_PARENT']]['children'][$row_board['ID_BOARD']]))
		{
			// A valid child!
			$isChild = true;

			$this_category[$row_board['ID_PARENT']]['children'][$row_board['ID_BOARD']] = array(
				'id' => $row_board['ID_BOARD'],
				'name' => $row_board['boardName'],
				'description' => $row_board['description'],
				'new' => empty($row_board['isRead']) && $row_board['posterName'] != '',
				'topics' => $row_board['numTopics'],
				'posts' => $row_board['numPosts'],
				'href' => $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0">' . $row_board['boardName'] . '</a>'
			);

			// Counting child board posts is... slow :/.
			if (!empty($modSettings['countChildPosts']))
			{
				$this_category[$row_board['ID_PARENT']]['posts'] += $row_board['numPosts'];
				$this_category[$row_board['ID_PARENT']]['topics'] += $row_board['numTopics'];
			}

			// Does this board contain new boards?
			$this_category[$row_board['ID_PARENT']]['children_new'] |= empty($row_board['isRead']);

			// This is easier to use in many cases for the theme....
			$this_category[$row_board['ID_PARENT']]['link_children'][] = &$this_category[$row_board['ID_PARENT']]['children'][$row_board['ID_BOARD']]['link'];
		}
		// Child of a child... just add it on...
		elseif (!empty($modSettings['countChildPosts']))
		{
			if (!isset($parent_map))
				$parent_map = array();

			if (!isset($parent_map[$row_board['ID_PARENT']]))
				foreach ($this_category as $id => $board)
				{
					if (!isset($board['children'][$row_board['ID_PARENT']]))
						continue;

					$parent_map[$row_board['ID_PARENT']] = array(&$this_category[$id], &$this_category[$id]['children'][$row_board['ID_PARENT']]);
					$parent_map[$row_board['ID_BOARD']] = array(&$this_category[$id], &$this_category[$id]['children'][$row_board['ID_PARENT']]);

					break;
				}

			if (isset($parent_map[$row_board['ID_PARENT']]))
			{
				$parent_map[$row_board['ID_PARENT']][0]['posts'] += $row_board['numPosts'];
				$parent_map[$row_board['ID_PARENT']][0]['topics'] += $row_board['numTopics'];
				$parent_map[$row_board['ID_PARENT']][1]['posts'] += $row_board['numPosts'];
				$parent_map[$row_board['ID_PARENT']][1]['topics'] += $row_board['numTopics'];

				continue;
			}

			continue;
		}
		// Found a child of a child - skip.
		else
			continue;

		// Prepare the subject, and make sure it's not too long.
		censorText($row_board['subject']);
		$row_board['short_subject'] = shorten_subject($row_board['subject'], 24);
		$this_last_post = array(
			'id' => $row_board['ID_MSG'],
			'time' => $row_board['posterTime'] > 0 ? timeformat($row_board['posterTime']) : $txt[470],
			'timestamp' => forum_time(true, $row_board['posterTime']),
			'subject' => $row_board['short_subject'],
			'member' => array(
				'id' => $row_board['ID_MEMBER'],
				'username' => $row_board['posterName'] != '' ? $row_board['posterName'] : $txt[470],
				'name' => $row_board['realName'],
				'href' => $row_board['posterName'] != '' && !empty($row_board['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row_board['ID_MEMBER'] : '',
				'link' => $row_board['posterName'] != '' ? (!empty($row_board['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MEMBER'] . '">' . $row_board['realName'] . '</a>' : $row_board['realName']) : $txt[470],
			),
			'start' => 'msg' . $row_board['new_from'],
			'topic' => $row_board['ID_TOPIC']
		);

		// Provide the href and link.
		if ($row_board['subject'] != '')
		{
			$this_last_post['href'] = $scripturl . '?topic=' . $row_board['ID_TOPIC'] . '.msg' . ($user_info['is_guest'] ? $modSettings['maxMsgID'] : $row_board['new_from']) . (empty($row_board['isRead']) ? ';boardseen' : '') . '#new';
			$this_last_post['link'] = '<a href="' . $this_last_post['href'] . '" title="' . $row_board['subject'] . '">' . $row_board['short_subject'] . '</a>';
		}
		else
		{
			$this_last_post['href'] = '';
			$this_last_post['link'] = $txt[470];
		}

		// Set the last post in the parent board.
		if (empty($row_board['ID_PARENT']) || ($isChild && !empty($row_board['posterTime']) && $this_category[$row_board['ID_PARENT']]['last_post']['timestamp'] < forum_time(true, $row_board['posterTime'])))
			$this_category[$isChild ? $row_board['ID_PARENT'] : $row_board['ID_BOARD']]['last_post'] = $this_last_post;
		// Just in the child...?
		if ($isChild)
		{
			$this_category[$row_board['ID_PARENT']]['children'][$row_board['ID_BOARD']]['last_post'] = $this_last_post;

			// If there are no posts in this board, it really can't be new...
			$this_category[$row_board['ID_PARENT']]['children'][$row_board['ID_BOARD']]['new'] &= $row_board['posterName'] != '';
		}
		// No last post for this board?  It's not new then, is it..?
		elseif ($row_board['posterName'] == '')
			$this_category[$row_board['ID_BOARD']]['new'] = false;

		// Determine a global most recent topic.
		if (!empty($row_board['posterTime']) && forum_time(true, $row_board['posterTime']) > $most_recent_topic['timestamp'])
			$most_recent_topic = array(
				'timestamp' => forum_time(true, $row_board['posterTime']),
				'ref' => &$this_category[$isChild ? $row_board['ID_PARENT'] : $row_board['ID_BOARD']]['last_post'],
			);
	}
	mysql_free_result($result_boards);

	// Load the users online right now.
	$result = db_query("
		SELECT
			lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
			mg.onlineColor, mg.ID_GROUP, mg.groupName
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))", __FILE__, __LINE__);

	$context['users_online'] = array();
	$context['list_users_online'] = array();
	$context['online_groups'] = array();
	$context['num_guests'] = 0;
	$context['num_buddies'] = 0;
	$context['num_users_hidden'] = 0;

	$context['show_buddies'] = !empty($user_info['buddies']);

	while ($row = mysql_fetch_assoc($result))
	{
		if (empty($row['realName']))
		{
			$context['num_guests']++;
			continue;
		}
		elseif (empty($row['showOnline']) && !allowedTo('moderate_forum'))
		{
			$context['num_users_hidden']++;
			continue;
		}

		// Some basic color coding...
		if (!empty($row['onlineColor']))
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
		else
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

		$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
		if ($is_buddy)
		{
			$context['num_buddies']++;
			$link = '<b>' . $link . '</b>';
		}

		$context['users_online'][$row['logTime'] . $row['memberName']] = array(
			'id' => $row['ID_MEMBER'],
			'username' => $row['memberName'],
			'name' => $row['realName'],
			'group' => $row['ID_GROUP'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => $link,
			'is_buddy' => $is_buddy,
			'hidden' => empty($row['showOnline']),
		);

		$context['list_users_online'][$row['logTime'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;

		if (!isset($context['online_groups'][$row['ID_GROUP']]))
			$context['online_groups'][$row['ID_GROUP']] = array(
				'id' => $row['ID_GROUP'],
				'name' => $row['groupName'],
				'color' => $row['onlineColor']
			);
	}
	mysql_free_result($result);

	krsort($context['users_online']);
	krsort($context['list_users_online']);
	ksort($context['online_groups']);

	$context['num_users_online'] = count($context['users_online']) + $context['num_users_hidden'];

	// Track most online statistics?
	if (!empty($modSettings['trackStats']))
	{
		// Determine the most users online - both all time and per day.
		$total_users = $context['num_guests'] + $context['num_users_online'];

		// More members on now than ever were?  Update it!
		if (!isset($modSettings['mostOnline']) || $total_users >= $modSettings['mostOnline'])
			updateSettings(array('mostOnline' => $total_users, 'mostDate' => time()));

		$date = strftime('%Y-%m-%d', forum_time(false));

		// One or more stats are not up-to-date?
		if (!isset($modSettings['mostOnlineUpdated']) || $modSettings['mostOnlineUpdated'] != $date)
		{
			$request = db_query("
				SELECT mostOn
				FROM {$db_prefix}log_activity
				WHERE date = '$date'
				LIMIT 1", __FILE__, __LINE__);

			// The log_activity hasn't got an entry for today?
			if (mysql_num_rows($request) == 0)
			{
				db_query("
					INSERT IGNORE INTO {$db_prefix}log_activity
						(date, mostOn)
					VALUES ('$date', $total_users)", __FILE__, __LINE__);
			}
			// There's an entry in log_activity on today...
			else
			{
				list ($modSettings['mostOnlineToday']) = mysql_fetch_row($request);

				if ($total_users > $modSettings['mostOnlineToday'])
					trackStats(array('mostOn' => $total_users));

				$total_users = max($total_users, $modSettings['mostOnlineToday']);
			}
			mysql_free_result($request);

			updateSettings(array('mostOnlineUpdated' => $date, 'mostOnlineToday' => $total_users));
		}
		// Highest number of users online today?
		elseif ($total_users > $modSettings['mostOnlineToday'])
		{
			trackStats(array('mostOn' => $total_users));
			updateSettings(array('mostOnlineUpdated' => $date, 'mostOnlineToday' => $total_users));
		}
	}

	// Set the latest member.
	$context['latest_member'] = &$context['common_stats']['latest_member'];

	// Load the most recent post?
	if ((!empty($settings['number_recent_posts']) && $settings['number_recent_posts'] == 1) || $settings['show_sp1_info'])
		$context['latest_post'] = $most_recent_topic['ref'];

	if (!empty($settings['number_recent_posts']) && $settings['number_recent_posts'] > 1)
	{
		require_once($sourcedir . '/Recent.php');

		if (($context['latest_posts'] = cache_get_data('boardindex-latest_posts:' . md5($user_info['query_see_board'] . $user_info['language']), 180)) == null)
		{
			$context['latest_posts'] = getLastPosts($settings['number_recent_posts']);
			cache_put_data('boardindex-latest_posts:' . md5($user_info['query_see_board'] . $user_info['language']), $context['latest_posts'], 180);
		}

		// We have to clean up the cached data a bit.
		foreach ($context['latest_posts'] as $k => $post)
		{
			$context['latest_posts'][$k]['time'] = timeformat($post['raw_timestamp']);
			$context['latest_posts'][$k]['timestamp'] = forum_time(true, $post['raw_timestamp']);
		}
	}

	$settings['display_recent_bar'] = !empty($settings['number_recent_posts']) ? $settings['number_recent_posts'] : 0;
	$settings['show_member_bar'] &= allowedTo('view_mlist');
	$context['show_stats'] = allowedTo('view_stats') && !empty($modSettings['trackStats']);
	$context['show_member_list'] = allowedTo('view_mlist');
	$context['show_who'] = allowedTo('who_view') && !empty($modSettings['who_enabled']);

	// Set some permission related settings.
	$context['show_login_bar'] = $user_info['is_guest'] && !empty($modSettings['enableVBStyleLogin']);
	$context['show_calendar'] = allowedTo('calendar_view') && !empty($modSettings['cal_enabled']);

	// Load the calendar?
	if ($context['show_calendar'])
		$context['show_calendar'] = calendarDoIndex();

	$context['page_title'] = $txt[18];
}

// Called from the BoardIndex to display the current day's events on the board index.
function calendarDoIndex()
{
	global $modSettings, $context, $user_info, $scripturl, $sc, $ID_MEMBER;

	// Make sure at least one of the options is checked.
	if (empty($modSettings['cal_showeventsonindex']) && empty($modSettings['cal_showbdaysonindex']) && empty($modSettings['cal_showholidaysonindex']))
		return false;

	// Get the current forum time and check whether the statistics are up to date.
	if (empty($modSettings['cal_today_updated']) || $modSettings['cal_today_updated'] != strftime('%Y%m%d', forum_time(false)))
		updateStats('calendar');

	// Load the holidays for today, ...
	if (!empty($modSettings['cal_showholidaysonindex']) && isset($modSettings['cal_today_holiday']))
		$holidays = unserialize($modSettings['cal_today_holiday']);
	// ... the birthdays for today, ...
	if (!empty($modSettings['cal_showbdaysonindex']) && isset($modSettings['cal_today_birthday']))
		$bday = unserialize($modSettings['cal_today_birthday']);
	// ... and the events for today.
	if (!empty($modSettings['cal_showeventsonindex']) && isset($modSettings['cal_today_event']))
		$events = unserialize($modSettings['cal_today_event']);

	// No events, birthdays, or holidays... don't show anything.  Simple.
	if (empty($holidays) && empty($bday) && empty($events))
		return false;

	// This shouldn't be less than one!
	if (empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1)
		$days_for_index = 86400;
	else
		$days_for_index = $modSettings['cal_days_for_index'] * 86400;

	$context['calendar_only_today'] = $modSettings['cal_days_for_index'] == 1;

	// Get the current member time/date.
	$now = forum_time();

	// This is used to show the "how-do-I-edit" help.
	$context['calendar_can_edit'] = allowedTo('calendar_edit_any');

	// Holidays between now and now + days.
	$context['calendar_holidays'] = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (isset($holidays[strftime('%Y-%m-%d', $i)]))
			$context['calendar_holidays'] = array_merge($context['calendar_holidays'], $holidays[strftime('%Y-%m-%d', $i)]);
	}

	// Happy Birthday, guys and gals!
	$context['calendar_birthdays'] = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
		if (isset($bday[strftime('%Y-%m-%d', $i)]))
		{
			foreach ($bday[strftime('%Y-%m-%d', $i)] as $index => $dummy)
				$bday[strftime('%Y-%m-%d', $i)][$index]['is_today'] = strftime('%Y-%m-%d', $i) == strftime('%Y-%m-%d', forum_time());
			$context['calendar_birthdays'] = array_merge($context['calendar_birthdays'], $bday[strftime('%Y-%m-%d', $i)]);
		}

	$context['calendar_events'] = array();
	$duplicates = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (empty($events[strftime('%Y-%m-%d', $i)]))
			continue;

		foreach ($events[strftime('%Y-%m-%d', $i)] as $ev => $event)
		{
			if (empty($event['topic']) || (count(array_intersect($user_info['groups'], $event['allowed_groups'])) != 0 || allowedTo('admin_forum')))
			{
				if (isset($duplicates[$events[strftime('%Y-%m-%d', $i)][$ev]['topic'] . $events[strftime('%Y-%m-%d', $i)][$ev]['title']]))
				{
					unset($events[strftime('%Y-%m-%d', $i)][$ev]);
					continue;
				}

				$this_event = &$events[strftime('%Y-%m-%d', $i)][$ev];
				$this_event['href'] = $this_event['topic'] == 0 ? '' : $scripturl . '?topic=' . $this_event['topic'] . '.0';
				$this_event['modify_href'] = $scripturl . '?action=' . ($this_event['topic'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $this_event['msg'] . ';topic=' . $this_event['topic'] . '.0;calendar;') . 'eventid=' . $this_event['id'] . ';sesc=' . $sc;
				$this_event['can_edit'] = allowedTo('calendar_edit_any') || ($this_event['poster'] == $ID_MEMBER && allowedTo('calendar_edit_own'));
				$this_event['is_today'] = (strftime('%Y-%m-%d', $i)) == strftime('%Y-%m-%d', forum_time());
				$this_event['date'] = strftime('%Y-%m-%d', $i);

				$duplicates[$this_event['topic'] . $this_event['title']] = true;
			}
			else
				unset($events[strftime('%Y-%m-%d', $i)][$ev]);
		}

		if (!empty($events[strftime('%Y-%m-%d', $i)]))
			$context['calendar_events'] = array_merge($context['calendar_events'], $events[strftime('%Y-%m-%d', $i)]);
	}

	for ($i = 0, $n = count($context['calendar_birthdays']); $i < $n; $i++)
		$context['calendar_birthdays'][$i]['is_last'] = !isset($context['calendar_birthdays'][$i + 1]);
	for ($i = 0, $n = count($context['calendar_events']); $i < $n; $i++)
		$context['calendar_events'][$i]['is_last'] = !isset($context['calendar_events'][$i + 1]);

	// This is used to make sure the header should be displayed.
	return !empty($context['calendar_holidays']) || !empty($context['calendar_birthdays']) || !empty($context['calendar_events']);
}

?>