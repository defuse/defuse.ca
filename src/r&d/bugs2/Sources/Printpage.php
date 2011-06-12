<?php
/**********************************************************************************
* Printpage.php                                                                   *
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

/*	This file contains just one function that formats a topic to be printer
	friendly.

	void PrintTopic()
		- is called to format a topic to be printer friendly.
		- must be called with a topic specified.
		- uses the Printpage (main sub template.) template.
		- uses the print_above/print_below later without the main layer.
		- is accessed via ?action=printpage.
*/

function PrintTopic()
{
	global $db_prefix, $topic, $txt, $scripturl, $context;
	global $board_info;

	if (empty($topic))
		fatal_lang_error(472, false);

	// Get the topic starter information.
	$request = db_query("
		SELECT m.posterTime, IFNULL(mem.realName, m.posterName) AS posterName
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_TOPIC = $topic
		ORDER BY ID_MSG
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Lets "output" all that info.
	loadTemplate('Printpage');
	$context['template_layers'] = array('print');
	$context['board_name'] = $board_info['name'];
	$context['category_name'] = $board_info['cat']['name'];
	$context['poster_name'] = $row['posterName'];
	$context['post_time'] = timeformat($row['posterTime'], false);

	// Split the topics up so we can print them.
	$request = db_query("
		SELECT subject, posterTime, body, IFNULL(mem.realName, posterName) AS posterName
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE ID_TOPIC = $topic
		ORDER BY ID_MSG", __FILE__, __LINE__);
	$context['posts'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Censor the subject and message.
		censorText($row['subject']);
		censorText($row['body']);

		$context['posts'][] = array(
			'subject' => $row['subject'],
			'member' => $row['posterName'],
			'time' =>  timeformat($row['posterTime'], false),
			'timestamp' => forum_time(true, $row['posterTime']),
			'body' => parse_bbc($row['body'], 'print'),
		);

		if (!isset($context['topic_subject']))
			$context['topic_subject'] = $row['subject'];
	}
	mysql_free_result($request);
}

?>