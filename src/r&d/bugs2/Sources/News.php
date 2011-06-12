<?php
/**********************************************************************************
* News.php                                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.12                                             *
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

/*	This file contains the files necessary to display news as an XML feed.

	void ShowXmlFeed()
		- is called to output xml information.
		- can be passed four subactions which decide what is output: 'recent'
		  for recent posts, 'news' for news topics, 'members' for recently
		  registered members, and 'profile' for a member's profile.
		- To display a member's profile, a user id has to be given. (;u=1)
		- uses the Stats language file.
		- outputs an rss feed instead of a proprietary one if the 'type' get
		  parameter is 'rss' or 'rss2'.
		- does not use any templates, sub templates, or template layers.
		- is accessed via ?action=.xml.

	void dumpTags(array data, int indentation, string tag = use_array,
			string format)
		- formats data retrieved in other functions into xml format.
		- additionally formats data based on the specific format passed.
		- the data parameter is the array to output as xml data.
		- indentation is the amount of indentation to use.
		- if a tag is specified, it will be used instead of the keys of data.
		- this function is recursively called to handle sub arrays of data.

	array getXmlMembers(string format)
		- is called to retrieve list of members from database.
		- the array will be generated to match the format.
		- returns array of data.

	array getXmlNews(string format)
		- is called to retrieve news topics from database.
		- the array will be generated to match the format.
		- returns array of topics.

	array getXmlRecent(string format)
		- is called to retrieve list of recent topics.
		- the array will be generated to match the format.
		- returns an array of recent posts.

	array getXmlProfile(string format)
		- is called to retrieve profile information for member into array.
		- the array will be generated to match the format.
		- returns an array of data.
*/

// Show an xml file representing recent information or a profile.
function ShowXmlFeed()
{
	global $db_prefix, $board, $board_info, $context, $scripturl, $txt, $modSettings, $user_info;
	global $query_this_board;

	// If it's not enabled, die.
	if (empty($modSettings['xmlnews_enable']))
		obExit(false);

	loadLanguage('Stats');

	// Default to latest 5.  No more than 255, please.
	$_GET['limit'] = empty($_GET['limit']) || (int) $_GET['limit'] < 1 ? 5 : min((int) $_GET['limit'], 255);

	// Handle the cases where a board, boards, or category is asked for.
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
				WHERE ID_CAT = " . (int) $_REQUEST['c'][0], __FILE__, __LINE__);
			list ($feed_title) = mysql_fetch_row($request);
			mysql_free_result($request);

			$feed_title = ' - ' . strip_tags($feed_title);
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

		if (!empty($boards))
			$query_this_board = 'b.ID_BOARD IN (' . implode(', ', $boards) . ')';

		// Try to limit the number of messages we look through.
		if ($total_cat_posts > 100 && $total_cat_posts > $modSettings['totalMessages'] / 15)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 400 - $_GET['limit'] * 5);
	}
	elseif (!empty($_REQUEST['boards']))
	{
		$_REQUEST['boards'] = explode(',', $_REQUEST['boards']);
		foreach ($_REQUEST['boards'] as $i => $b)
			$_REQUEST['boards'][$i] = (int) $b;

		$request = db_query("
			SELECT b.ID_BOARD, b.numPosts, b.name
			FROM {$db_prefix}boards AS b
			WHERE b.ID_BOARD IN (" . implode(', ', $_REQUEST['boards']) . ")
				AND $user_info[query_see_board]
			LIMIT " . count($_REQUEST['boards']), __FILE__, __LINE__);

		// Either the board specified doesn't exist or you have no access.
		if (mysql_num_rows($request) == 0)
			fatal_lang_error('smf232');

		$total_posts = 0;
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
		{
			if (count($_REQUEST['boards']) == 1)
				$feed_title = ' - ' . strip_tags($row['name']);

			$boards[] = $row['ID_BOARD'];
			$total_posts += $row['numPosts'];
		}
		mysql_free_result($request);

		if (!empty($boards))
			$query_this_board = 'b.ID_BOARD IN (' . implode(', ', $boards) . ')';

		// The more boards, the more we're going to look through...
		if ($total_posts > 100 && $total_posts > $modSettings['totalMessages'] / 12)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 500 - $_GET['limit'] * 5);
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

		$feed_title = ' - ' . strip_tags($board_info['name']);

		$query_this_board = 'b.ID_BOARD = ' . $board;

		// Try to look through just a few messages, if at all possible.
		if ($total_posts > 80 && $total_posts > $modSettings['totalMessages'] / 10)
			$query_this_board .= '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 600 - $_GET['limit'] * 5);
	}
	else
	{
		$query_this_board = $user_info['query_see_board'] . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != $modSettings[recycle_board]" : ''). '
			AND m.ID_MSG >= ' . max(0, $modSettings['maxMsgID'] - 100 - $_GET['limit'] * 5);
	}

	// Show in rss or proprietary format?
	$xml_format = isset($_GET['type']) && in_array($_GET['type'], array('smf', 'rss', 'rss2', 'atom', 'rdf')) ? $_GET['type'] : 'smf';

	// !!! Birthdays?

	// List all the different types of data they can pull.
	$subActions = array(
		'recent' => array('getXmlRecent', 'recent-post'),
		'news' => array('getXmlNews', 'article'),
		'members' => array('getXmlMembers', 'member'),
		'profile' => array('getXmlProfile', null),
	);
	if (empty($_GET['sa']) || !isset($subActions[$_GET['sa']]))
		$_GET['sa'] = 'recent';

	// Get the associative array representing the xml.
	if ($user_info['is_guest'] && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
		$xml = cache_get_data('xmlfeed-' . $xml_format . ':' . md5(serialize($_GET)), 240);
	if (empty($xml))
	{
		$xml = $subActions[$_GET['sa']][0]($xml_format);

		if ($user_info['is_guest'] && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
			cache_put_data('xmlfeed-' . $xml_format . ':' . md5(serialize($_GET)), $xml, 240);
	}

	$feed_title = htmlspecialchars(strip_tags($context['forum_name'])) . (isset($feed_title) ? $feed_title : '');

	// This is an xml file....
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	if ($xml_format == 'smf' || isset($_REQUEST['debug']))
		header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	elseif ($xml_format == 'rss' || $xml_format == 'rss2')
		header('Content-Type: application/rss+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	elseif ($xml_format == 'atom')
		header('Content-Type: application/atom+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	elseif ($xml_format == 'rdf')
		header('Content-Type: application/rdf+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	// First, output the xml header.
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?' . '>';

	// Are we outputting an rss feed or one with more information?
	if ($xml_format == 'rss' || $xml_format == 'rss2')
	{
		// Start with an RSS 2.0 header.
		echo '
<rss version=', $xml_format == 'rss2' ? '"2.0"' : '"0.92"', ' xml:lang="', strtr($txt['lang_locale'], '_', '-'), '">
	<channel>
		<title>', $feed_title, '</title>
		<link>', $scripturl, '</link>
		<description><![CDATA[', strip_tags($txt['xml_rss_desc']), ']]></description>';

		// Output all of the associative array, start indenting with 2 tabs, and name everything "item".
		dumpTags($xml, 2, 'item', $xml_format);

		// Output the footer of the xml.
		echo '
	</channel>
</rss>';
	}
	elseif ($xml_format == 'atom')
	{
		echo '
<feed version="0.3" xmlns="http://purl.org/atom/ns#">
	<title>', $feed_title, '</title>
	<link rel="alternate" type="text/html" href="', $scripturl, '" />

	<modified>', gmstrftime('%Y-%m-%dT%H:%M:%SZ'), '</modified>
	<tagline><![CDATA[', strip_tags($txt['xml_rss_desc']), ']]></tagline>
	<generator>SMF</generator>
	<author>
		<name>', strip_tags($context['forum_name']), '</name>
	</author>';

		dumpTags($xml, 2, 'entry', $xml_format);

		echo '
</feed>';
	}
	elseif ($xml_format == 'rdf')
	{
		echo '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns="http://purl.org/rss/1.0/">
	<channel rdf:about="', $scripturl, '">
		<title>', $feed_title, '</title>
		<link>', $scripturl, '</link>
		<description><![CDATA[', strip_tags($txt['xml_rss_desc']), ']]></description>
		<items>
			<rdf:Seq>';

		foreach ($xml as $item)
			echo '
				<rdf:li rdf:resource="', $item['link'], '" />';

		echo '
			</rdf:Seq>
		</items>
	</channel>
';

		dumpTags($xml, 1, 'item', $xml_format);

		echo '
</rdf:RDF>';
	}
	// Otherwise, we're using our proprietary formats - they give more data, though.
	else
	{
		echo '
<smf:xml-feed xmlns:smf="http://www.simplemachines.org/" xmlns="http://www.simplemachines.org/xml/', $_GET['sa'], '" xml:lang="', strtr($txt['lang_locale'], '_', '-'), '">';

		// Dump out that associative array.  Indent properly.... and use the right names for the base elements.
		dumpTags($xml, 1, $subActions[$_GET['sa']][1], $xml_format);

		echo '
</smf:xml-feed>';
}

	obExit(false);
}

function fix_possible_url($val)
{
	global $modSettings, $context, $scripturl;

	if (substr($val, 0, strlen($scripturl)) != $scripturl)
		return $val;

	if (isset($modSettings['integrate_fix_url']) && funcion_exists($modSettings['integrate_fix_url']))
		$val = call_user_func($modSettings['integrate_fix_url'], $val);

	if (empty($modSettings['queryless_urls']) || ($context['server']['is_cgi'] && @ini_get('cgi.fix_pathinfo') == 0) || !$context['server']['is_apache'])
		return $val;

	$val = preg_replace('/^' . preg_quote($scripturl, '/') . '\?((?:board|topic)=[^#"]+)(#[^"]*)?$/e', "'' . \$scripturl . '/' . strtr('\$1', '&;=', '//,') . '.html\$2'", $val);
	return $val;
}

function cdata_parse($data, $ns = '')
{
	global $func;

	$cdata = '<![CDATA[';

	for ($pos = 0, $n = $func['strlen']($data); $pos < $n; null)
	{
		$positions = array(
			$func['strpos']($data, '&', $pos),
			$func['strpos']($data, ']', $pos),
		);
		if ($ns != '')
			$positions[] = $func['strpos']($data, '<', $pos);
		foreach ($positions as $k => $dummy)
		{
			if ($dummy === false)
				unset($positions[$k]);
		}

		$old = $pos;
		$pos = empty($positions) ? $n : min($positions);

		if ($pos - $old > 0)
			$cdata .= $func['substr']($data, $old, $pos - $old);
		if ($pos >= $n)
			break;

		if ($func['substr']($data, $pos, 1) == '<')
		{
			$pos2 = $func['strpos']($data, '>', $pos);
			if ($pos2 === false)
				$pos2 = $n;
			if ($func['substr']($data, $pos + 1, 1) == '/')
				$cdata .= ']]></' . $ns . ':' . $func['substr']($data, $pos + 2, $pos2 - $pos - 1) . '<![CDATA[';
			else
				$cdata .= ']]><' . $ns . ':' . $func['substr']($data, $pos + 1, $pos2 - $pos) . '<![CDATA[';
			$pos = $pos2 + 1;
		}
		elseif ($func['substr']($data, $pos, 1) == ']')
		{
			$cdata .= ']]>&#093;<![CDATA[';
			$pos++;
		}
		elseif ($func['substr']($data, $pos, 1) == '&')
		{
			$pos2 = $func['strpos']($data, ';', $pos);
			if ($pos2 === false)
				$pos2 = $n;
			$ent = $func['substr']($data, $pos + 1, $pos2 - $pos - 1);

			if ($func['substr']($data, $pos + 1, 1) == '#')
				$cdata .= ']]>' . $func['substr']($data, $pos, $pos2 - $pos + 1) . '<![CDATA[';
			elseif (in_array($ent, array('amp', 'lt', 'gt', 'quot')))
				$cdata .= ']]>' . $func['substr']($data, $pos, $pos2 - $pos + 1) . '<![CDATA[';
			// !!! ??

			$pos = $pos2 + 1;
		}
	}

	$cdata .= ']]>';

	return strtr($cdata, array('<![CDATA[]]>' => ''));
}

function dumpTags($data, $i, $tag = null, $xml_format = '')
{
	global $modSettings, $context, $scripturl;

	// For every array in the data...
	foreach ($data as $key => $val)
	{
		// Skip it, it's been set to null.
		if ($val == null)
			continue;

		// If a tag was passed, use it instead of the key.
		$key = isset($tag) ? $tag : $key;

		// First let's indent!
		echo "\n", str_repeat("\t", $i);

		// Grr, I hate kludges... almost worth doing it properly, here, but not quite.
		if ($xml_format == 'atom' && $key == 'link')
		{
			echo '<link rel="alternate" type="text/html" href="', fix_possible_url($val), '" />';
			continue;
		}

		// If it's empty/0/nothing simply output an empty tag.
		if ($val == '')
			echo '<', $key, ' />';
		else
		{
			// Beginning tag.
			if ($xml_format == 'rdf' && $key == 'item' && isset($val['link']))
			{
				echo '<', $key, ' rdf:about="', fix_possible_url($val['link']), '">';
				echo "\n", str_repeat("\t", $i + 1);
				echo '<dc:format>text/html</dc:format>';
			}
			elseif ($xml_format == 'atom' && $key == 'summary')
				echo '<', $key, ' type="html">';
			else
				echo '<', $key, '>';

			if (is_array($val))
			{
				// An array.  Dump it, and then indent the tag.
				dumpTags($val, $i + 1, null, $xml_format);
				echo "\n", str_repeat("\t", $i), '</', $key, '>';
			}
			// A string with returns in it.... show this as a multiline element.
			elseif (strpos($val, "\n") !== false || strpos($val, '<br />') !== false)
				echo "\n", fix_possible_url($val), "\n", str_repeat("\t", $i), '</', $key, '>';
			// A simple string.
			else
				echo fix_possible_url($val), '</', $key, '>';
		}
	}
}

function getXmlMembers($xml_format)
{
	global $db_prefix, $scripturl;

	if (!allowedTo('view_mlist'))
		return array();

	// Find the most recent members.
	$request = db_query("
		SELECT ID_MEMBER, memberName, realName, dateRegistered, lastLogin
		FROM {$db_prefix}members
		ORDER BY ID_MEMBER DESC
		LIMIT $_GET[limit]", __FILE__, __LINE__);
	$data = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Make the data look rss-ish.
		if ($xml_format == 'rss' || $xml_format == 'rss2')
			$data[] = array(
				'title' => cdata_parse($row['realName']),
				'link' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'comments' => $scripturl . '?action=pm;sa=send;u=' . $row['ID_MEMBER'],
				'pubDate' => gmdate('D, d M Y H:i:s \G\M\T', $row['dateRegistered']),
				'guid' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			);
		elseif ($xml_format == 'rdf')
			$data[] = array(
				'title' => cdata_parse($row['realName']),
				'link' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			);
		elseif ($xml_format == 'atom')
			$data[] = array(
				'title' => cdata_parse($row['realName']),
				'link' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'created' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['dateRegistered']),
				'issued' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['dateRegistered']),
				'modified' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['lastLogin']),
				'id' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			);
		// More logical format for the data, but harder to apply.
		else
			$data[] = array(
				'name' => cdata_parse($row['realName']),
				'time' => strip_tags(timeformat($row['dateRegistered'])),
				'id' => $row['ID_MEMBER'],
				'link' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER']
			);
	}
	mysql_free_result($request);

	return $data;
}

function getXmlNews($xml_format)
{
	global $db_prefix, $user_info, $scripturl, $modSettings, $board;
	global $query_this_board, $func;

	/* Find the latest posts that:
		- are the first post in their topic.
		- are on an any board OR in a specified board.
		- can be seen by this user.
		- are actually the latest posts. */
	$request = db_query("
		SELECT
			m.smileysEnabled, m.posterTime, m.ID_MSG, m.subject, m.body, t.ID_TOPIC, t.ID_BOARD,
			b.name AS bname, t.numReplies, m.ID_MEMBER, IFNULL(mem.realName, m.posterName) AS posterName,
			mem.hideEmail, IFNULL(mem.emailAddress, m.posterEmail) AS posterEmail, m.modifiedTime
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE b.ID_BOARD = " . (empty($board) ? 't.ID_BOARD' : "$board
			AND t.ID_BOARD = $board") . "
			AND m.ID_MSG = t.ID_FIRST_MSG
			AND $query_this_board
		ORDER BY t.ID_FIRST_MSG DESC
		LIMIT $_GET[limit]", __FILE__, __LINE__);
	$data = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Limit the length of the message, if the option is set.
		if (!empty($modSettings['xmlnews_maxlen']) && $func['strlen'](str_replace('<br />', "\n", $row['body'])) > $modSettings['xmlnews_maxlen'])
			$row['body'] = strtr($func['substr'](str_replace('<br />', "\n", $row['body']), 0, $modSettings['xmlnews_maxlen'] - 3), array("\n" => '<br />')) . '...';

		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		censorText($row['body']);
		censorText($row['subject']);

		// Being news, this actually makes sense in rss format.
		if ($xml_format == 'rss' || $xml_format == 'rss2')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
				'description' => cdata_parse($row['body']),
				'author' => (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']) || (!empty($row['hideEmail']) && !empty($modSettings['allow_hideEmail']) && !allowedTo('moderate_forum')) ? null : $row['posterEmail'],
				'comments' => $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.0',
				'category' => '<![CDATA[' . $row['bname'] . ']]>',
				'pubDate' => gmdate('D, d M Y H:i:s \G\M\T', $row['posterTime']),
				'guid' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG']
			);
		elseif ($xml_format == 'rdf')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
				'description' => cdata_parse($row['body']),
			);
		elseif ($xml_format == 'atom')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
				'summary' => cdata_parse($row['body']),
				'author' => array('name' => $row['posterName']),
				'created' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['posterTime']),
				'issued' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['posterTime']),
				'modified' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', empty($row['modifiedTime']) ? $row['posterTime'] : $row['modifiedTime']),
				'id' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG']
			);
		// The biggest difference here is more information.
		else
			$data[] = array(
				'time' => strip_tags(timeformat($row['posterTime'])),
				'id' => $row['ID_MSG'],
				'subject' => cdata_parse($row['subject']),
				'body' => cdata_parse($row['body']),
				'poster' => array(
					'name' => cdata_parse($row['posterName']),
					'id' => $row['ID_MEMBER'],
					'link' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : ''
				),
				'topic' => $row['ID_TOPIC'],
				'board' => array(
					'name' => cdata_parse($row['bname']),
					'id' => $row['ID_BOARD'],
					'link' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0'
				),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0'
			);
	}
	mysql_free_result($request);

	return $data;
}

function getXmlRecent($xml_format)
{
	global $db_prefix, $user_info, $scripturl, $modSettings, $board;
	global $query_this_board, $func;

	$request = db_query("
		SELECT m.ID_MSG
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE m.ID_BOARD = " . (empty($board) ? "b.ID_BOARD" : "$board
			AND b.ID_BOARD = $board") . "
			AND $query_this_board
		ORDER BY m.ID_MSG DESC
		LIMIT $_GET[limit]", __FILE__, __LINE__);
	$messages = array();
	while ($row = mysql_fetch_assoc($request))
		$messages[] = $row['ID_MSG'];
	mysql_free_result($request);

	if (empty($messages))
		return array();

	// Find the most recent posts this user can see.
	$request = db_query("
		SELECT
			m.smileysEnabled, m.posterTime, m.ID_MSG, m.subject, m.body, m.ID_TOPIC, t.ID_BOARD,
			b.name AS bname, t.numReplies, m.ID_MEMBER, mf.ID_MEMBER AS ID_FIRST_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, mf.subject AS firstSubject,
			IFNULL(memf.realName, mf.posterName) AS firstPosterName, mem.hideEmail,
			IFNULL(mem.emailAddress, m.posterEmail) AS posterEmail, m.modifiedTime
		FROM ({$db_prefix}messages AS m, {$db_prefix}messages AS mf, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}members AS memf ON (memf.ID_MEMBER = mf.ID_MEMBER)
		WHERE t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = " . (empty($board) ? 't.ID_BOARD' : "$board
			AND t.ID_BOARD = $board") . "
			AND mf.ID_MSG = t.ID_FIRST_MSG
			AND m.ID_MSG IN (" . implode(', ', $messages) . ")
		ORDER BY m.ID_MSG DESC
		LIMIT $_GET[limit]", __FILE__, __LINE__);
	$data = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Limit the length of the message, if the option is set.
		if (!empty($modSettings['xmlnews_maxlen']) && $func['strlen'](str_replace('<br />', "\n", $row['body'])) > $modSettings['xmlnews_maxlen'])
			$row['body'] = strtr($func['substr'](str_replace('<br />', "\n", $row['body']), 0, $modSettings['xmlnews_maxlen'] - 3), array("\n" => '<br />')) . '...';

		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		censorText($row['body']);
		censorText($row['subject']);

		// Doesn't work as well as news, but it kinda does..
		if ($xml_format == 'rss' || $xml_format == 'rss2')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
				'description' => cdata_parse($row['body']),
				'author' => (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']) || (!empty($row['hideEmail']) && !empty($modSettings['allow_hideEmail']) && !allowedTo('moderate_forum')) ? null : $row['posterEmail'],
				'category' => cdata_parse($row['bname']),
				'comments' => $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.0',
				'pubDate' => gmdate('D, d M Y H:i:s \G\M\T', $row['posterTime']),
				'guid' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG']
			);
		elseif ($xml_format == 'rdf')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
				'description' => cdata_parse($row['body']),
			);
		elseif ($xml_format == 'atom')
			$data[] = array(
				'title' => cdata_parse($row['subject']),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
				'summary' => cdata_parse($row['body']),
				'author' => array('name' => $row['posterName']),
				'created' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['posterTime']),
				'issued' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $row['posterTime']),
				'modified' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', empty($row['modifiedTime']) ? $row['posterTime'] : $row['modifiedTime']),
				'id' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG']
			);
		// A lot of information here.  Should be enough to please the rss-ers.
		else
			$data[] = array(
				'time' => strip_tags(timeformat($row['posterTime'])),
				'id' => $row['ID_MSG'],
				'subject' => cdata_parse($row['subject']),
				'body' => cdata_parse($row['body']),
				'starter' => array(
					'name' => cdata_parse($row['firstPosterName']),
					'id' => $row['ID_FIRST_MEMBER'],
					'link' => !empty($row['ID_FIRST_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_FIRST_MEMBER'] : ''
				),
				'poster' => array(
					'name' => cdata_parse($row['posterName']),
					'id' => $row['ID_MEMBER'],
					'link' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : ''
				),
				'topic' => array(
					'subject' => cdata_parse($row['firstSubject']),
					'id' => $row['ID_TOPIC'],
					'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.new#new'
				),
				'board' => array(
					'name' => cdata_parse($row['bname']),
					'id' => $row['ID_BOARD'],
					'link' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0'
				),
				'link' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG']
			);
	}
	mysql_free_result($request);

	return $data;
}

function getXmlProfile($xml_format)
{
	global $scripturl, $memberContext, $user_profile, $modSettings, $user_info;

	// You must input a valid user....
	if (empty($_GET['u']) || loadMemberData((int) $_GET['u']) === false)
		return array();

	// Make sure the id is a number and not "I like trying to hack the database".
	$_GET['u'] = (int) $_GET['u'];

	// Load the member's contextual information!
	if (!loadMemberContext($_GET['u']) || !allowedTo('profile_view_any'))
		return array();

	// Okay, I admit it, I'm lazy.  Stupid $_GET['u'] is long and hard to type.
	$profile = &$memberContext[$_GET['u']];

	if ($xml_format == 'rss' || $xml_format == 'rss2')
		$data = array(array(
			'title' => cdata_parse($profile['name']),
			'link' => $scripturl  . '?action=profile;u=' . $profile['id'],
			'description' => cdata_parse(isset($profile['group']) ? $profile['group'] : $profile['post_group']),
			'comments' => $scripturl . '?action=pm;sa=send;u=' . $profile['id'],
			'pubDate' => gmdate('D, d M Y H:i:s \G\M\T', $user_profile[$profile['id']]['dateRegistered']),
			'guid' => $scripturl  . '?action=profile;u=' . $profile['id'],
		));
	elseif ($xml_format == 'rdf')
		$data = array(array(
			'title' => cdata_parse($profile['name']),
			'link' => $scripturl  . '?action=profile;u=' . $profile['id'],
			'description' => cdata_parse(isset($profile['group']) ? $profile['group'] : $profile['post_group']),
		));
	elseif ($xml_format == 'atom')
		$data[] = array(
			'title' => cdata_parse($profile['name']),
			'link' => $scripturl  . '?action=profile;u=' . $profile['id'],
			'summary' => cdata_parse(isset($profile['group']) ? $profile['group'] : $profile['post_group']),
			'created' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $user_profile[$profile['id']]['dateRegistered']),
			'issued' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $user_profile[$profile['id']]['dateRegistered']),
			'modified' => gmstrftime('%Y-%m-%dT%H:%M:%SZ', $user_profile[$profile['id']]['lastLogin']),
			'id' => $scripturl  . '?action=profile;u=' . $profile['id']
		);
	else
	{
		$data = array(
			'username' => cdata_parse($profile['username']),
			'name' => cdata_parse($profile['name']),
			'link' => $scripturl  . '?action=profile;u=' . $profile['id'],
			'posts' => $profile['posts'],
			'post-group' => cdata_parse($profile['post_group']),
			'language' => cdata_parse($profile['language']),
			'last-login' => gmdate('D, d M Y H:i:s \G\M\T', $user_profile[$profile['id']]['lastLogin']),
			'registered' => gmdate('D, d M Y H:i:s \G\M\T', $user_profile[$profile['id']]['dateRegistered'])
		);

		// Everything below here might not be set, and thus maybe shouldn't be displayed.
		if ($profile['gender']['name'] != '')
			$data['gender'] = cdata_parse($profile['gender']['name']);

		if ($profile['avatar']['name'] != '')
			$data['avatar'] = $profile['avatar']['url'];

		// If they are online, show an empty tag... no reason to put anything inside it.
		if ($profile['online']['is_online'])
			$data['online'] = '';

		if ($profile['signature'] != '')
			$data['signature'] = cdata_parse($profile['signature']);
		if ($profile['blurb'] != '')
			$data['blurb'] = cdata_parse($profile['blurb']);
		if ($profile['location'] != '')
			$data['location'] = cdata_parse($profile['location']);
		if ($profile['title'] != '')
			$data['title'] = cdata_parse($profile['title']);

		if (!empty($profile['icq']['name']) && !(!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']))
			$data['icq'] = $profile['icq']['name'];
		if ($profile['aim']['name'] != '' && !(!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']))
			$data['aim'] = $profile['aim']['name'];
		if ($profile['msn']['name'] != '' && !(!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']))
			$data['msn'] = $profile['msn']['name'];
		if ($profile['yim']['name'] != '' && !(!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']))
			$data['yim'] = $profile['yim']['name'];

		if ($profile['website']['title'] != '')
			$data['website'] = array(
				'title' => cdata_parse($profile['website']['title']),
				'link' => $profile['website']['url']
			);

		if ($profile['group'] != '')
			$data['postition'] = cdata_parse($profile['group']);

		if (!empty($modSettings['karmaMode']))
			$data['karma'] = array(
				'good' => $profile['karma']['good'],
				'bad' => $profile['karma']['bad']
			);

		if ((empty($profile['hide_email']) || empty($modSettings['allow_hideEmail'])) && !(!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']))
			$data['email'] = $profile['email'];

		if (!empty($profile['birth_date']) && substr($profile['birth_date'], 0, 4) != '0000')
		{
			list ($birth_year, $birth_month, $birth_day) = sscanf($profile['birth_date'], '%d-%d-%d');
			$datearray = getdate(forum_time());
			$data['age'] = $datearray['year'] - $birth_year - (($datearray['mon'] > $birth_month || ($datearray['mon'] == $birth_month && $datearray['mday'] >= $birth_day)) ? 0 : 1);
		}
	}

	// Save some memory.
	unset($profile);
	unset($memberContext[$_GET['u']]);

	return $data;
}

?>