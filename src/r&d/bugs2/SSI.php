<?php
/**********************************************************************************
* SSI.php                                                                         *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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


// Don't do anything if SMF is already loaded.
if (defined('SMF'))
	return true;

define('SMF', 'SSI');

// We're going to want a few globals... these are all set later.
global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;

// Remember the current configuration so it can be set back.
$ssi_magic_quotes_runtime = @get_magic_quotes_runtime();
@set_magic_quotes_runtime(0);
$time_start = microtime();

// Make sure some things simply do not exist.
foreach (array('db_character_set') as $variable)
	if (isset($GLOBALS[$variable]))
		unset($GLOBALS[$variable]);

// Get the forum's settings for database and file paths.
require_once(dirname(__FILE__) . '/Settings.php');

$ssi_error_reporting = error_reporting(E_ALL);

// Don't do john didley if the forum's been shut down competely.
if ($maintenance == 2 && (!isset($ssi_maintenance_off) || $ssi_maintenance_off !== true))
	die($mmessage);

// Fix for using the current directory as a path.
if (substr($sourcedir, 0, 1) == '.' && substr($sourcedir, 1, 1) != '.')
	$sourcedir = dirname(__FILE__) . substr($sourcedir, 1);

// Load the important includes.
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Load.php');
require_once($sourcedir . '/Security.php');

if (@version_compare(PHP_VERSION, '4.2.3') != 1)
	require_once($sourcedir . '/Subs-Compat.php');

// Connect to the MySQL database.
if (empty($db_persist))
	$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
else
	$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
if ($db_connection === false)
	return false;

// Add the database onto the prefix to avoid conflicts with other scripts.
if (strpos($db_prefix, '.') === false)
	$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;
else
	@mysql_select_db($db_name, $db_connection);

// Load installed 'Mods' settings.
reloadSettings();
// Clean the request variables.
cleanRequest();

// Seed the random generator?
if (empty($modSettings['rand_seed']) || mt_rand(1, 250) == 69)
	smf_seed_generator();

// Check on any hacking attempts.
if (isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS']))
	die('Hacking attempt...');
elseif (isset($_REQUEST['ssi_theme']) && (int) $_REQUEST['ssi_theme'] == (int) $ssi_theme)
	die('Hacking attempt...');
elseif (isset($_COOKIE['ssi_theme']) && (int) $_COOKIE['ssi_theme'] == (int) $ssi_theme)
	die('Hacking attempt...');
elseif (isset($_REQUEST['ssi_layers'], $ssi_layers) && (@get_magic_quotes_gpc() ? stripslashes($_REQUEST['ssi_layers']) : $_REQUEST['ssi_layers']) == $ssi_layers)
	die('Hacking attempt...');
if (isset($_REQUEST['context']))
	die('Hacking attempt...');

// Make sure wireless is always off.
define('WIRELESS', false);

// Gzip output? (because it must be boolean and true, this can't be hacked.)
if (isset($ssi_gzip) && $ssi_gzip === true && @ini_get('zlib.output_compression') != '1' && @ini_get('output_handler') != 'ob_gzhandler' && @version_compare(PHP_VERSION, '4.2.0') != -1)
	ob_start('ob_gzhandler');
else
	$modSettings['enableCompressedOutput'] = '0';

// Primarily, this is to fix the URLs...
ob_start('ob_sessrewrite');

// Start the session... known to scramble SSI includes in cases...
if (!headers_sent())
	loadSession();
else
{
	if (isset($_COOKIE[session_name()]) || isset($_REQUEST[session_name()]))
	{
		// Make a stab at it, but ignore the E_WARNINGs generted because we can't send headers.
		$temp = error_reporting(error_reporting() & !E_WARNING);
		loadSession();
		error_reporting($temp);
	}

	if (!isset($_SESSION['rand_code']))
		$_SESSION['rand_code'] = '';
	$sc = &$_SESSION['rand_code'];
}

// Get rid of $board and $topic... do stuff loadBoard would do.
unset($board);
unset($topic);
$user_info['is_mod'] = false;
$context['user']['is_mod'] = false;
$context['linktree'] = array();

// Load the user and their cookie, as well as their settings.
loadUserSettings();
// Load the current or SSI theme. (just ues $ssi_theme = ID_THEME;)
loadTheme(isset($ssi_theme) ? (int) $ssi_theme : 0);

// Take care of any banning that needs to be done.
if (isset($_REQUEST['ssi_ban']) || (isset($ssi_ban) && $ssi_ban === true))
	is_not_banned();

// Load the current user's permissions....
loadPermissions();

// Do we allow guests in here?
if (empty($ssi_guest_access) && empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && basename($_SERVER['PHP_SELF']) != 'SSI.php')
{
	require_once($sourcedir . '/Subs-Auth.php');
	KickGuest();
	obExit(null, true);
}

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
{
	$context['template_layers'] = $ssi_layers;
	template_header();
}
else
	setupThemeContext();

// Make sure they didn't muss around with the settings... but only if it's not cli.
if (isset($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['is_cli']) && session_id() == '')
	trigger_error($txt['ssi_session_broken'], E_USER_NOTICE);

// Without visiting the forum this session variable might not be set on submit.
if (!isset($_SESSION['USER_AGENT']) && (!isset($_GET['ssi_function']) || $_GET['ssi_function'] !== 'pollVote'))
	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

// Call a function passed by GET.
if (isset($_GET['ssi_function']) && function_exists('ssi_' . $_GET['ssi_function']) && (!empty($modSettings['allow_guestAccess']) || !$user_info['is_guest']))
{
	call_user_func('ssi_' . $_GET['ssi_function']);
	exit;
}
if (isset($_GET['ssi_function']))
	exit;
// You shouldn't just access SSI.php directly by URL!!
elseif (basename($_SERVER['PHP_SELF']) == 'SSI.php')
	die(sprintf($txt['ssi_not_direct'], $user_info['is_admin'] ? '\'' . addslashes(__FILE__) . '\'' : '\'SSI.php\''));

error_reporting($ssi_error_reporting);
@set_magic_quotes_runtime($ssi_magic_quotes_runtime);

return true;

// This shuts down the SSI and shows the footer.
function ssi_shutdown()
{
	if (!isset($_GET['ssi_function']) || $_GET['ssi_function'] != 'shutdown')
		template_footer();
}

// Display a welcome message, like:  Hey, User, you have 0 messages, 0 are new.
function ssi_welcome($output_method = 'echo')
{
	global $context, $txt, $scripturl;

	if ($output_method == 'echo')
	{
		if ($context['user']['is_guest'])
			echo $txt['welcome_guest'];
		else
			echo $txt['hello_member'], ' <b>', $context['user']['name'], '</b>', allowedTo('pm_read') ? ', ' . $txt[152] . ' <a href="' . $scripturl . '?action=pm">' . $context['user']['messages'] . ' ' . ($context['user']['messages'] == '1' ? $txt[471] : $txt[153]) . '</a>' . $txt['newmessages4'] . ' ' . $context['user']['unread_messages'] . ' ' . ($context['user']['unread_messages'] == '1' ? $txt['newmessages0'] : $txt['newmessages1']) : '', '.';
	}
	// Don't echo... then do what?!
	else
		return $context['user'];
}

// Display a menu bar, like is displayed at the top of the forum.
function ssi_menubar($output_method = 'echo')
{
	global $context;

	if ($output_method == 'echo')
		template_menu();
	// What else could this do?
	else
		return $context;
}

// Show a logout link.
function ssi_logout($redirect_to = '', $output_method = 'echo')
{
	global $context, $txt, $scripturl, $sc;

	if ($redirect_to != '')
		$_SESSION['logout_url'] = $redirect_to;

	// Guests can't log out.
	if ($context['user']['is_guest'])
		return false;

	echo '<a href="', $scripturl, '?action=logout;sesc=', $sc, '">', $txt[108], '</a>';
}

// Recent post list:   [board] Subject by Poster	Date
function ssi_recentPosts($num_recent = 8, $exclude_boards = null, $output_method = 'echo')
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $ID_MEMBER;
	global $user_info, $modSettings, $func;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;

	// Find all the posts.  Newer ones will have higher IDs.
	$request = db_query("
		SELECT
			m.posterTime, m.subject, m.ID_TOPIC, m.ID_MEMBER, m.ID_MSG, m.ID_BOARD, b.name AS bName,
			IFNULL(mem.realName, m.posterName) AS posterName, " . ($user_info['is_guest'] ? '1 AS isRead, 0 AS new_from' : '
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) >= m.ID_MSG_MODIFIED AS isRead,
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from') . ", LEFT(m.body, 384) AS body, m.smileysEnabled
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = m.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = m.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)" : '') . "
		WHERE m.ID_MSG >= " . ($modSettings['maxMsgID'] - 25 * min($num_recent, 5)) . "
			AND b.ID_BOARD = m.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_see_board]
		ORDER BY m.ID_MSG DESC
		LIMIT $num_recent", __FILE__, __LINE__);
	$posts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
		if ($func['strlen']($row['body']) > 128)
			$row['body'] = $func['substr']($row['body'], 0, 128) . '...';

		// Censor it!
		censorText($row['subject']);
		censorText($row['body']);

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
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
			'new' => !empty($row['isRead']),
			'new_from' => $row['new_from'],
		);
	}
	mysql_free_result($request);

	// Just return it.
	if ($output_method != 'echo' || empty($posts))
		return $posts;

	echo '
		<table border="0" class="ssi_table">';
	foreach ($posts as $post)
		echo '
			<tr>
				<td align="right" valign="top" nowrap="nowrap">
					[', $post['board']['link'], ']
				</td>
				<td valign="top">
					<a href="', $post['href'], '">', $post['subject'], '</a>
					', $txt[525], ' ', $post['poster']['link'], '
					', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt[302] . '" border="0" /></a>', '
				</td>
				<td align="right" nowrap="nowrap">
					', $post['time'], '
				</td>
			</tr>';
	echo '
		</table>';
}

// Recent topic list:   [board] Subject by Poster	Date
function ssi_recentTopics($num_recent = 8, $exclude_boards = null, $output_method = 'echo')
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $ID_MEMBER;
	global $user_info, $modSettings, $func;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = db_query("
		SELECT
			m.posterTime, ms.subject, m.ID_TOPIC, m.ID_MEMBER, m.ID_MSG, b.ID_BOARD, b.name AS bName,
			IFNULL(mem.realName, m.posterName) AS posterName, " . ($user_info['is_guest'] ? '1 AS isRead, 0 AS new_from' : '
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) >= m.ID_MSG_MODIFIED AS isRead,
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from') . ", LEFT(m.body, 384) AS body, m.smileysEnabled, m.icon
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}messages AS ms)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = b.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)" : '') . "
		WHERE t.ID_LAST_MSG >= " . ($modSettings['maxMsgID'] - 35 * min($num_recent, 5)) . "
			AND t.ID_LAST_MSG = m.ID_MSG
			AND b.ID_BOARD = t.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_see_board]
			AND ms.ID_MSG = t.ID_FIRST_MSG
		ORDER BY t.ID_LAST_MSG DESC
		LIMIT $num_recent", __FILE__, __LINE__);
	$posts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
		if ($func['strlen']($row['body']) > 128)
			$row['body'] = $func['substr']($row['body'], 0, 128) . '...';

		// Censor the subject.
		censorText($row['subject']);
		censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

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
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#new">' . $row['subject'] . '</a>',
			'new' => !empty($row['isRead']),
			'new_from' => $row['new_from'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
		);
	}
	mysql_free_result($request);

	// Just return it.
	if ($output_method != 'echo' || empty($posts))
		return $posts;

	echo '
		<table border="0" class="ssi_table">';
	foreach ($posts as $post)
		echo '
			<tr>
				<td align="right" valign="top" nowrap="nowrap">
					[', $post['board']['link'], ']
				</td>
				<td valign="top">
					<a href="', $post['href'], '">', $post['subject'], '</a>
					', $txt[525], ' ', $post['poster']['link'], '
					', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt[302] . '" border="0" /></a>', '
				</td>
				<td align="right" nowrap="nowrap">
					', $post['time'], '
				</td>
			</tr>';
	echo '
		</table>';
}

// Show the top poster's name and profile link.
function ssi_topPoster($topNumber = 1, $output_method = 'echo')
{
	global $db_prefix, $scripturl;

	// Find the latest poster.
	$request = db_query("
		SELECT ID_MEMBER, realName, posts
		FROM {$db_prefix}members
		ORDER BY posts DESC
		LIMIT $topNumber", __FILE__, __LINE__);
	$return = array();
	while ($row = mysql_fetch_assoc($request))
		$return[] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'posts' => $row['posts']
		);
	mysql_free_result($request);

	// Just return all the top posters.
	if ($output_method != 'echo')
		return $return;

	// Make a quick array to list the links in.
	$temp_array = array();
	foreach ($return as $member)
		$temp_array[] = $member['link'];

	echo implode(', ', $temp_array);
}

// Show boards by activity.
function ssi_topBoards($num_top = 10, $output_method = 'echo')
{
	global $context, $settings, $db_prefix, $txt, $scripturl, $ID_MEMBER, $user_info, $modSettings;

	// Find boards with lots of posts.
	$request = db_query("
		SELECT
			b.name, b.numTopics, b.numPosts, b.ID_BOARD," . (!$user_info['is_guest'] ? ' 1 AS isRead' : '
			(IFNULL(lb.ID_MSG, 0) >= b.ID_LAST_MSG) AS isRead') . "
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
		WHERE $user_info[query_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != " . (int) $modSettings['recycle_board'] : '') . "
		ORDER BY b.numPosts DESC
		LIMIT $num_top", __FILE__, __LINE__);
	$boards = array();
	while ($row = mysql_fetch_assoc($request))
		$boards[] = array(
			'id' => $row['ID_BOARD'],
			'num_posts' => $row['numPosts'],
			'num_topics' => $row['numTopics'],
			'name' => $row['name'],
			'new' => empty($row['isRead']),
			'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>'
		);
	mysql_free_result($request);

	// If we shouldn't output or have nothing to output, just jump out.
	if ($output_method != 'echo' || empty($boards))
		return $boards;

	echo '
		<table class="ssi_table">
			<tr>
				<th align="left">', $txt['smf82'], '</th>
				<th align="left">', $txt[330], '</th>
				<th align="left">', $txt[21], '</th>
			</tr>';
	foreach ($boards as $board)
		echo '
			<tr>
				<td>', $board['link'], $board['new'] ? ' <a href="' . $board['href'] . '"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt[302] . '" border="0" /></a>' : '', '</td>
				<td align="right">', $board['num_topics'], '</td>
				<td align="right">', $board['num_posts'], '</td>
			</tr>';
	echo '
		</table>';
}

// Shows the top topics.
function ssi_topTopics($type = 'replies', $num_topics = 10, $output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $ID_MEMBER, $user_info, $modSettings;

	if ($modSettings['totalMessages'] > 100000)
	{
		$request = db_query("
			SELECT ID_TOPIC
			FROM {$db_prefix}topics
			WHERE num" . ($type != 'replies' ? 'Views' : 'Replies') . " != 0
			ORDER BY num" . ($type != 'replies' ? 'Views' : 'Replies') . " DESC
			LIMIT 100", __FILE__, __LINE__);
		$topic_ids = array();
		while ($row = mysql_fetch_assoc($request))
			$topic_ids[] = $row['ID_TOPIC'];
		mysql_free_result($request);
	}
	else
		$topic_ids = array();

	$request = db_query("
		SELECT m.subject, m.ID_TOPIC, t.numViews, t.numReplies
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE m.ID_MSG = t.ID_FIRST_MSG
			AND t.ID_BOARD = b.ID_BOARD" . (!empty($topic_ids) ? "
			AND t.ID_TOPIC IN (" . implode(', ', $topic_ids) . ")" : '') . "
			AND $user_info[query_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.ID_BOARD != $modSettings[recycle_board]" : '') . "
		ORDER BY t.num" . ($type != 'replies' ? 'Views' : 'Replies') . " DESC
		LIMIT $num_topics", __FILE__, __LINE__);
	$topics = array();
	while ($row = mysql_fetch_assoc($request))
	{
		censorText($row['subject']);

		$topics[] = array(
			'id' => $row['ID_TOPIC'],
			'subject' => $row['subject'],
			'num_replies' => $row['numReplies'],
			'num_views' => $row['numViews'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>',
		);
	}
	mysql_free_result($request);

	if ($output_method != 'echo' || empty($topics))
		return $topics;

	echo '
		<table class="ssi_table">
			<tr>
				<th align="left"></th>
				<th align="left">', $txt[301], '</th>
				<th align="left">', $txt[110], '</th>
			</tr>';
	foreach ($topics as $topic)
		echo '
			<tr>
				<td align="left">
					', $topic['link'], '
				</td>
				<td align="right">', $topic['num_views'], '</td>
				<td align="right">', $topic['num_replies'], '</td>
			</tr>';
	echo '
		</table>';
}

// Shows the top topics, by replies.
function ssi_topTopicsReplies($num_topics = 10, $output_method = 'echo')
{
	return ssi_topTopics('replies', $num_topics, $output_method);
}

// Shows the top topics, by views.
function ssi_topTopicsViews($num_topics = 10, $output_method = 'echo')
{
	return ssi_topTopics('views', $num_topics, $output_method);
}

// Show a link to the latest member:  Please welcome, Someone, out latest member.
function ssi_latestMember($output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $context;

	if ($output_method == 'echo')
		echo '
	', $txt[201], ' ', $context['common_stats']['latest_member']['link'], '', $txt[581], '<br />';
	else
		return $context['common_stats']['latest_member'];
}

// Show some basic stats:  Total This: XXXX, etc.
function ssi_boardStats($output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $modSettings;

	$totals = array(
		'members' => $modSettings['totalMembers'],
		'posts' => $modSettings['totalMessages'],
		'topics' => $modSettings['totalTopics']
	);

	$result = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	list ($totals['boards']) = mysql_fetch_row($result);
	mysql_free_result($result);

	$result = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}categories", __FILE__, __LINE__);
	list ($totals['categories']) = mysql_fetch_row($result);
	mysql_free_result($result);

	if ($output_method != 'echo')
		return $totals;

	echo '
		', $txt[488], ': <a href="', $scripturl . '?action=mlist">', $totals['members'], '</a><br />
		', $txt[489], ': ', $totals['posts'], '<br />
		', $txt[490], ': ', $totals['topics'], ' <br />
		', $txt[658], ': ', $totals['categories'], '<br />
		', $txt[665], ': ', $totals['boards'];
}

// Shows a list of online users:  YY Guests, ZZ Users and then a list...
function ssi_whosOnline($output_method = 'echo')
{
	global $scripturl, $db_prefix, $user_info, $txt;

	// Load the users online right now.
	$result = db_query("
		SELECT
			lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
			mg.onlineColor, mg.ID_GROUP
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))", __FILE__, __LINE__);

	$return['users'] = array();
	$return['guests'] = 0;
	$return['hidden'] = 0;
	$return['buddies'] = 0;
	$show_buddies = !empty($user_info['buddies']);

	while ($row = mysql_fetch_assoc($result))
	{
		if (!isset($row['realName']))
			$return['guests']++;
		elseif (!empty($row['showOnline']) || allowedTo('moderate_forum'))
		{
			// Some basic color coding...
			if (!empty($row['onlineColor']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

			// Bold any buddies.
			if ($show_buddies && in_array($row['ID_MEMBER'], $user_info['buddies']))
			{
				$return['buddies']++;
				$link = '<b>' . $link . '</b>';
			}

			$return['users'][$row['logTime'] . $row['memberName']] = array(
				'id' => $row['ID_MEMBER'],
				'username' => $row['memberName'],
				'name' => $row['realName'],
				'group' => $row['ID_GROUP'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => $link,
				'hidden' => empty($row['showOnline']),
				'is_last' => false,
			);
		}
		else
			$return['hidden']++;
	}
	mysql_free_result($result);

	if (!empty($return['users']))
	{
		krsort($return['users']);
		$userlist = array_keys($return['users']);
		$return['users'][$userlist[count($userlist) - 1]]['is_last'] = true;
	}
	$return['num_users'] = count($return['users']) + $return['hidden'];
	$return['total_users'] = $return['num_users'] + $return['guests'];

	if ($output_method != 'echo')
		return $return;

	echo '
		', $return['guests'], ' ', $return['guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', $return['num_users'], ' ', $return['num_users'] == 1 ? $txt['user'] : $txt['users'];

	// Hidden users, or buddies?
	if ($return['hidden'] > 0 || $show_buddies)
		echo '
			(' . ($show_buddies ? ($return['buddies'] . ' ' . ($return['buddies'] == 1 ? $txt['buddy'] : $txt['buddies'])) : '') . ($show_buddies && $return['hidden'] ? ', ' : '') . (!$return['hidden'] ? '' : $return['hidden'] . ' ' . $txt['hidden']) . ')';

	echo '<br />';
	foreach ($return['users'] as $user)
		echo $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'], $user['is_last'] ? '' : ', ';
}

// Just like whosOnline except it also logs the online presence.
function ssi_logOnline($output_method = 'echo')
{
	writeLog();

	if ($output_method != 'echo')
		return ssi_whosOnline($output_method);
	else
		ssi_whosOnline($output_method);
}

// Shows a login box.
function ssi_login($redirect_to = '', $output_method = 'echo')
{
	global $scripturl, $txt, $user_info, $context;

	if ($redirect_to != '')
		$_SESSION['login_url'] = $redirect_to;

	if ($output_method != 'echo' || !$user_info['is_guest'])
		return $user_info['is_guest'];

	echo '
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
				<tr>
					<td align="right"><label for="user">', $txt[35], ':</label>&nbsp;</td>
					<td><input type="text" id="user" name="user" size="9" value="', $user_info['username'], '" /></td>
				</tr><tr>
					<td align="right"><label for="passwrd">', $txt[36], ':</label>&nbsp;</td>
					<td><input type="password" name="passwrd" id="passwrd" size="9" /></td>
				</tr><tr>
					<td><input type="hidden" name="cookielength" value="-1" /></td>
					<td><input type="submit" value="', $txt[34], '" /></td>
				</tr>
			</table>
		</form>';
}

// Show the most-voted-in poll.
function ssi_topPoll($output_method = 'echo')
{
	// Just use recentPoll, no need to duplicate code...
	return ssi_recentPoll($output_method, true);
}

// Show the most recently posted poll.
function ssi_recentPoll($output_method = 'echo', $topPollInstead = false)
{
	global $db_prefix, $txt, $ID_MEMBER, $settings, $boardurl, $sc, $user_info;
	global $context;

	$boardsAllowed = array_intersect(boardsAllowedTo('poll_view'), boardsAllowedTo('poll_vote'));

	if (empty($boardsAllowed))
		return array();

	$request = db_query("
		SELECT p.ID_POLL, p.question, t.ID_TOPIC, p.maxVotes
		FROM ({$db_prefix}polls AS p, {$db_prefix}boards AS b, {$db_prefix}topics AS t" . ($topPollInstead ? ", {$db_prefix}poll_choices AS pc" : '') . ")
			LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.ID_POLL = p.ID_POLL AND lp.ID_MEMBER = $ID_MEMBER)
		WHERE p.votingLocked = 0" . ($topPollInstead ? "
			AND pc.ID_POLL = p.ID_POLL" : '') . "
			AND lp.ID_CHOICE IS NULL
			AND t.ID_POLL = p.ID_POLL
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.ID_BOARD IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
		ORDER BY " . ($topPollInstead ? 'pc.votes' : 'p.ID_POLL') . " DESC
		LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// This user has voted on all the polls.
	if ($row === false)
		return array();

	$request = db_query("
		SELECT COUNT(DISTINCT ID_MEMBER)
		FROM {$db_prefix}log_polls
		WHERE ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
	list ($total) = mysql_fetch_row($request);
	mysql_free_result($request);

	$request = db_query("
		SELECT ID_CHOICE, label, votes
		FROM {$db_prefix}poll_choices
		WHERE ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
	$options = array();
	while ($rowChoice = mysql_fetch_assoc($request))
	{
		censorText($rowChoice['label']);

		$options[$rowChoice['ID_CHOICE']] = array($rowChoice['label'], $rowChoice['votes']);
	}
	mysql_free_result($request);

	$return = array(
		'id' => $row['ID_POLL'],
		'image' => 'poll',
		'question' => $row['question'],
		'total_votes' => $total,
		'is_locked' => false,
		'topic' => $row['ID_TOPIC'],
		'options' => array()
	);

	// Calculate the percentages and bar lengths...
	$divisor = $return['total_votes'] == 0 ? 1 : $return['total_votes'];
	foreach ($options as $i => $option)
	{
		$bar = floor(($option[1] * 100) / $divisor);
		$barWide = $bar == 0 ? 1 : floor(($bar * 5) / 3);
		$return['options'][$i] = array(
			'id' => 'options-' . $i,
			'percent' => $bar,
			'votes' => $option[1],
			'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
			'option' => parse_bbc($option[0]),
			'vote_button' => '<input type="' . ($row['maxVotes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
		);
	}

	$return['allowed_warning'] = $row['maxVotes'] > 1 ? sprintf($txt['poll_options6'], $row['maxVotes']) : '';

	if ($output_method != 'echo')
		return $return;

	echo '
		<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="poll" value="', $return['id'], '" />
			<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
				<tr>
					<td><b>', $return['question'], '</b></td>
				</tr>
				<tr>
					<td>', $return['allowed_warning'], '</td>
				</tr>';
	foreach ($return['options'] as $option)
		echo '
				<tr>
					<td><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></td>
				</tr>';
	echo '
				<tr>
					<td><input type="submit" value="', $txt['smf23'], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $sc, '" />
		</form>';
}

function ssi_showPoll($topic = null, $output_method = 'echo')
{
	global $db_prefix, $txt, $ID_MEMBER, $settings, $boardurl, $sc, $user_info;
	global $context;

	$boardsAllowed = boardsAllowedTo('poll_view');

	if (empty($boardsAllowed))
		return array();

	if ($topic === null && isset($_REQUEST['ssi_topic']))
		$topic = (int) $_REQUEST['ssi_topic'];
	else
		$topic = (int) $topic;

	$request = db_query("
		SELECT
			p.ID_POLL, p.question, p.votingLocked, p.hideResults, p.expireTime, p.maxVotes, b.ID_BOARD
		FROM ({$db_prefix}topics AS t, {$db_prefix}polls AS p, {$db_prefix}boards AS b)
		WHERE p.ID_POLL = t.ID_POLL
			AND t.ID_TOPIC = $topic
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.ID_BOARD IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
		LIMIT 1", __FILE__, __LINE__);

	// Either this topic has no poll, or the user cannot view it.
	if (mysql_num_rows($request) == 0)
		return array();

	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Check if they can vote.
	if (!empty($row['expireTime']) && $row['expireTime'] < time())
		$allow_vote = false;
	elseif ($user_info['is_guest'] || !empty($row['votingLocked']) || !allowedTo('poll_vote', array($row['ID_BOARD'])))
		$allow_vote = false;
	else
	{
		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}log_polls
			WHERE ID_POLL = $row[ID_POLL]
				AND ID_MEMBER = $ID_MEMBER
			LIMIT 1", __FILE__, __LINE__);
		$allow_vote = mysql_num_rows($request) == 0;
		mysql_free_result($request);
	}

	$request = db_query("
		SELECT COUNT(DISTINCT ID_MEMBER)
		FROM {$db_prefix}log_polls
		WHERE ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
	list ($total) = mysql_fetch_row($request);
	mysql_free_result($request);

	$request = db_query("
		SELECT ID_CHOICE, label, votes
		FROM {$db_prefix}poll_choices
		WHERE ID_POLL = $row[ID_POLL]", __FILE__, __LINE__);
	$options = array();
	$total_votes = 0;
	while ($rowChoice = mysql_fetch_assoc($request))
	{
		censorText($rowChoice['label']);

		$options[$rowChoice['ID_CHOICE']] = array($rowChoice['label'], $rowChoice['votes']);
		$total_votes += $rowChoice['votes'];
	}
	mysql_free_result($request);

	$return = array(
		'id' => $row['ID_POLL'],
		'image' => empty($pollinfo['votingLocked']) ? 'poll' : 'locked_poll',
		'question' => $row['question'],
		'total_votes' => $total,
		'is_locked' => !empty($pollinfo['votingLocked']),
		'allow_vote' => $allow_vote,
		'topic' => $topic
	);

	// Calculate the percentages and bar lengths...
	$divisor = $total_votes == 0 ? 1 : $total_votes;
	foreach ($options as $i => $option)
	{
		$bar = floor(($option[1] * 100) / $divisor);
		$barWide = $bar == 0 ? 1 : floor(($bar * 5) / 3);
		$return['options'][$i] = array(
			'id' => 'options-' . $i,
			'percent' => $bar,
			'votes' => $option[1],
			'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
			'option' => parse_bbc($option[0]),
			'vote_button' => '<input type="' . ($row['maxVotes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
		);
	}

	$return['allowed_warning'] = $row['maxVotes'] > 1 ? sprintf($txt['poll_options6'], $row['maxVotes']) : '';

	if ($output_method != 'echo')
		return $return;

	if ($return['allow_vote'])
	{
		echo '
			<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
				<input type="hidden" name="poll" value="', $return['id'], '" />
				<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
					<tr>
						<td><b>', $return['question'], '</b></td>
					</tr>
					<tr>
						<td>', $return['allowed_warning'], '</td>
					</tr>';
		foreach ($return['options'] as $option)
			echo '
					<tr>
						<td><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></td>
					</tr>';
		echo '
					<tr>
						<td><input type="submit" value="', $txt['smf23'], '" /></td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="', $sc, '" />
			</form>';
	}
	else
	{
		echo '
				<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
					<tr>
						<td colspan="2"><b>', $return['question'], '</b></td>
					</tr>';
		foreach ($return['options'] as $option)
			echo '
					<tr>
						<td align="right" valign="top">', $option['option'], '</td>
						<td align="left">', $option['bar'], ' ', $option['votes'], ' (', $option['percent'], '%)</td>
					</tr>';
		echo '
					<tr>
						<td colspan="2"><b>', $txt['smf24'], ': ', $return['total_votes'], '</b></td>
					</tr>
				</table>';
	}
}

// Takes care of voting - don't worry, this is done automatically.
function ssi_pollVote()
{
	global $db_prefix, $ID_MEMBER, $user_info, $sc;

	if (!isset($_POST['sc']) || $_POST['sc'] != $sc || empty($_POST['options']) || !isset($_POST['poll']))
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		history.go(-1);
	// ]]></script>
</head>
<body>&laquo;</body>
</html>';
		return;
	}

	// This can cause weird errors! (ie. copyright missing.)
	checkSession();

	$_POST['poll'] = (int) $_POST['poll'];

	// Check if they have already voted, or voting is locked.
	$request = db_query("
		SELECT IFNULL(lp.ID_CHOICE, -1) AS selected, p.votingLocked, p.expireTime, p.maxVotes, t.ID_TOPIC
		FROM ({$db_prefix}polls AS p, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.ID_POLL = p.ID_POLL AND lp.ID_MEMBER = $ID_MEMBER)
		WHERE p.ID_POLL = $_POST[poll]
			AND t.ID_POLL = $_POST[poll]
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		die;
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	if (!empty($row['votingLocked']) || $row['selected'] != -1 || (!empty($row['expireTime']) && time() > $row['expireTime']))
		redirectexit('topic=' . $row['ID_TOPIC'] . '.0');

	// Too many options checked?
	if (count($_REQUEST['options']) > $row['maxVotes'])
		redirectexit('topic=' . $row['ID_TOPIC'] . '.0');

	$options = array();
	$setString = '';
	foreach ($_REQUEST['options'] as $id)
	{
		$id = (int) $id;

		$options[] = $id;
		$setString .= "
				($_POST[poll], $ID_MEMBER, $id),";
	}
	$setString = substr($setString, 0, -1);

	// Add their vote in to the tally.
	db_query("
		INSERT INTO {$db_prefix}log_polls
			(ID_POLL, ID_MEMBER, ID_CHOICE)
		VALUES $setString", __FILE__, __LINE__);
	db_query("
		UPDATE {$db_prefix}poll_choices
		SET votes = votes + 1
		WHERE ID_POLL = $_POST[poll]
			AND ID_CHOICE IN (" . implode(', ', $options) . ")
		LIMIT " . count($options), __FILE__, __LINE__);

	redirectexit('topic=' . $row['ID_TOPIC'] . '.0');
}

// Show a search box.
function ssi_quickSearch($output_method = 'echo')
{
	global $scripturl, $txt, $context;

	if ($output_method != 'echo')
		return $scripturl . '?action=search';

	echo '
		<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="advanced" value="0" /><input type="text" name="search" size="30" /> <input type="submit" name="submit" value="', $txt[182], '" />
		</form>';
}

// Show what would be the forum news.
function ssi_news($output_method = 'echo')
{
	global $context;

	if ($output_method != 'echo')
		return $context['random_news_line'];

	echo $context['random_news_line'];
}

// Show today's birthdays.
function ssi_todaysBirthdays($output_method = 'echo')
{
	global $context, $scripturl;

	if (!smf_loadCalendarInfo() || empty($context['calendar_birthdays']))
		return array();

	if ($output_method != 'echo')
		return $context['calendar_birthdays'];

	foreach ($context['calendar_birthdays'] as $member)
		echo '
			<a href="', $scripturl, '?action=profile;u=', $member['id'], '">' . $member['name'] . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '</a>' . (!$member['is_last'] ? ', ' : '');
}

// Show today's holidays.
function ssi_todaysHolidays($output_method = 'echo')
{
	global $context;

	if (!smf_loadCalendarInfo() || empty($context['calendar_holidays']))
		return array();

	if ($output_method != 'echo')
		return $context['calendar_holidays'];

	echo '
		', implode(', ', $context['calendar_holidays']);
}

// Show today's events.
function ssi_todaysEvents($output_method = 'echo')
{
	global $context;

	if (!smf_loadCalendarInfo() || empty($context['calendar_events']))
		return array();

	if ($output_method != 'echo')
		return $context['calendar_events'];

	foreach ($context['calendar_events'] as $event)
	{
		if ($event['can_edit'])
			echo '
	<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';
		echo '
	' . $event['link'] . (!$event['is_last'] ? ', ' : '');
	}
}

// Show all calendar entires for today. (birthdays, holodays, and events.)
function ssi_todaysCalendar($output_method = 'echo')
{
	global $context, $modSettings, $txt, $scripturl;

	if (!smf_loadCalendarInfo())
		return array();

	if ($output_method != 'echo')
		return array(
			'birthdays' => $context['calendar_birthdays'],
			'holidays' => $context['calendar_holidays'],
			'events' => $context['calendar_events']
		);

	if (!empty($context['calendar_holidays']))
		echo '
			<span style="color: #' . $modSettings['cal_holidaycolor'] . ';">' . $txt['calendar5'] . ' ' . implode(', ', $context['calendar_holidays']) . '<br /></span>';
	if (!empty($context['calendar_birthdays']))
	{
		echo '
			<span style="color: #' . $modSettings['cal_bdaycolor'] . ';">' . $txt['calendar3b'] . '</span> ';
		foreach ($context['calendar_birthdays'] as $member)
			echo '
			<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', !$member['is_last'] ? ', ' : '';
		echo '
			<br />';
	}
	if (!empty($context['calendar_events']))
	{
		echo '
			<span style="color: #' . $modSettings['cal_eventcolor'] . ';">' . $txt['calendar4b'] . '</span> ';
		foreach ($context['calendar_events'] as $event)
		{
			if ($event['can_edit'])
				echo '
			<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';
			echo '
			' . $event['link'] . (!$event['is_last'] ? ', ' : '');
		}
	}
}

// Show the latest news, with a template... by board.
function ssi_boardNews($board = null, $limit = null, $start = null, $length = null, $output_method = 'echo')
{
	global $scripturl, $db_prefix, $txt, $settings, $modSettings, $context;
	global $func;

	loadLanguage('Stats');

	// Must be integers....
	if ($limit === null)
		$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
	else
		$limit = (int) $limit;

	if ($start === null)
		$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	else
		$start = (int) $start;

	if ($board !== null)
		$board = (int) $board;
	elseif (isset($_GET['board']))
		$board = (int) $_GET['board'];

	if ($length === null)
		$length = isset($_GET['length']) ? (int) $_GET['length'] : 0;
	else
		$length = (int) $length;

	$limit = max(0, $limit);
	$start = max(0, $start);

	// Make sure guests can see this board.
	$request = db_query("
		SELECT ID_BOARD
		FROM {$db_prefix}boards
		WHERE " . ($board === null ? '' : "ID_BOARD = $board
			AND ") . "FIND_IN_SET(-1, memberGroups)
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
	{
		if ($output_method == 'echo')
			die($txt['smf_news_error2']);
		else
			return array();
	}
	list ($board) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Load the message icons - the usual suspects.
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	// Find the post ids.
	$request = db_query("
		SELECT ID_FIRST_MSG
		FROM {$db_prefix}topics
		WHERE ID_BOARD = $board
		ORDER BY ID_FIRST_MSG DESC
		LIMIT $start, $limit", __FILE__, __LINE__);
	$posts = array();
	while ($row = mysql_fetch_assoc($request))
		$posts[] = $row['ID_FIRST_MSG'];
	mysql_free_result($request);

	if (empty($posts))
		return array();

	// Find the posts.
	$request = db_query("
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime,
			t.numReplies, t.ID_TOPIC, m.ID_MEMBER, m.smileysEnabled, m.ID_MSG, t.locked
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE t.ID_FIRST_MSG IN (" . implode(', ', $posts) . ")
			AND m.ID_MSG = t.ID_FIRST_MSG
		ORDER BY t.ID_FIRST_MSG DESC
		LIMIT " . count($posts), __FILE__, __LINE__);
	$return = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// If we want to limit the length of the post.
		if (!empty($length) && $func['strlen']($row['body']) > $length)
		{
			$row['body'] = $func['substr']($row['body'], 0, $length);

			// The first space or line break. (<br />, etc.)
			$cutoff = max(strrpos($row['body'], ' '), strrpos($row['body'], '<'));

			if ($cutoff !== false)
				$row['body'] = $func['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		// Check that this message icon is there...
		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

		censorText($row['subject']);
		censorText($row['body']);

		$return[] = array(
			'id' => $row['ID_TOPIC'],
			'message_id' => $row['ID_MSG'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'body' => $row['body'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['numReplies'] . ' ' . ($row['numReplies'] == 1 ? $txt['smf_news_1'] : $txt['smf_news_2']) . '</a>',
			'replies' => $row['numReplies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'],
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . ';num_replies=' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['numReplies'] . '">' . $txt['smf_news_3'] . '</a>',
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'href' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
				'link' => !empty($row['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>' : $row['posterName']
			),
			'locked' => !empty($row['locked']),
			'is_last' => false
		);
	}
	mysql_free_result($request);

	if (empty($return))
		return $return;

	$return[count($return) - 1]['is_last'] = true;

	if ($output_method != 'echo')
		return $return;

	foreach ($return as $news)
	{
		echo '
			<div>
				<a href="', $news['href'], '">', $news['icon'], '</a> <b>', $news['subject'], '</b>
				<div class="smaller">', $news['time'], ' ', $txt[525], ' ', $news['poster']['link'], '</div>

				<div class="post" style="padding: 2ex 0;">', $news['body'], '</div>

				', $news['link'], $news['locked'] ? '' : ' | ' . $news['comment_link'], '
			</div>';

		if (!$news['is_last'])
			echo '
			<hr style="margin: 2ex 0;" width="100%" />';
	}
}

// Show the most recent events.
function ssi_recentEvents($max_events = 7, $output_method = 'echo')
{
	global $db_prefix, $user_info, $scripturl, $modSettings, $txt, $sc, $ID_MEMBER;

	// Find all events which are happening in the near future that the member can see.
	$request = db_query("
		SELECT
			cal.ID_EVENT, cal.startDate, cal.endDate, cal.title, cal.ID_MEMBER, cal.ID_TOPIC,
			cal.ID_BOARD, t.ID_FIRST_MSG
		FROM {$db_prefix}calendar AS cal
			LEFT JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = cal.ID_BOARD)
			LEFT JOIN {$db_prefix}topics AS t ON (t.ID_TOPIC = cal.ID_TOPIC)
		WHERE cal.startDate <= '" . strftime('%Y-%m-%d', forum_time(false)) . "'
			AND cal.endDate >= '" . strftime('%Y-%m-%d', forum_time(false)) . "'
			AND (cal.ID_BOARD = 0 OR $user_info[query_see_board])
		ORDER BY cal.startDate DESC
		LIMIT $max_events", __FILE__, __LINE__);
	$return = array();
	$duplicates = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Check if we've already come by an event linked to this same topic with the same title... and don't display it if we have.
		if (!empty($duplicates[$row['title'] . $row['ID_TOPIC']]))
			continue;

		// Censor the title.
		censorText($row['title']);

		if ($row['startDate'] < strftime('%Y-%m-%d', forum_time(false)))
			$date = strftime('%Y-%m-%d', forum_time(false));
		else
			$date = $row['startDate'];

		$return[$date][] = array(
			'id' => $row['ID_EVENT'],
			'title' => $row['title'],
			'can_edit' => allowedTo('calendar_edit_any') || ($row['ID_MEMBER'] == $ID_MEMBER && allowedTo('calendar_edit_own')),
			'modify_href' => $scripturl . '?action=' . ($row['ID_BOARD'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $row['ID_FIRST_MSG'] . ';topic=' . $row['ID_TOPIC'] . '.0;calendar;') . 'eventid=' . $row['ID_EVENT'] . ';sesc=' . $sc,
			'href' => $row['ID_BOARD'] == 0 ? '' : $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => $row['ID_BOARD'] == 0 ? $row['title'] : '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['title'] . '</a>',
			'start_date' => $row['startDate'],
			'end_date' => $row['endDate'],
			'is_last' => false
		);

		// Let's not show this one again, huh?
		$duplicates[$row['title'] . $row['ID_TOPIC']] = true;
	}
	mysql_free_result($request);

	foreach ($return as $mday => $array)
		$return[$mday][count($array) - 1]['is_last'] = true;

	if ($output_method != 'echo' || empty($return))
		return $return;

	// Well the output method is echo.
	echo '
			<span style="color: #' . $modSettings['cal_eventcolor'] . ';">' . $txt['calendar4'] . '</span> ';
	foreach ($return as $mday => $array)
		foreach ($array as $event)
		{
			if ($event['can_edit'])
				echo '
				<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';

			echo '
				' . $event['link'] . (!$event['is_last'] ? ', ' : '');
		}
}

// Load the calendar information. (internal...)
function smf_loadCalendarInfo()
{
	global $modSettings, $context, $user_info, $scripturl, $sc, $ID_MEMBER;

	// Get the current forum time and check whether the statistics are up to date.
	if (!isset($modSettings['cal_today_updated']) || $modSettings['cal_today_updated'] != strftime('%Y%m%d', forum_time(false)))
		updateStats('calendar');

	// Load the holidays for today...
	if (isset($modSettings['cal_today_holiday']))
		$holidays = unserialize($modSettings['cal_today_holiday']);
	// ... the birthdays for today...
	if (isset($modSettings['cal_today_birthday']))
		$bday = unserialize($modSettings['cal_today_birthday']);
	// ... and the events for today.
	if (isset($modSettings['cal_today_event']))
		$events = unserialize($modSettings['cal_today_event']);

	// No events, birthdays, or holidays... don't show anything.
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
	{
		if (isset($bday[strftime('%Y-%m-%d', $i)]))
		{
			foreach ($bday[strftime('%Y-%m-%d', $i)] as $index => $dummy)
				$bday[strftime('%Y-%m-%d', $i)][$index]['is_today'] = (strftime('%Y-%m-%d', $i)) == strftime('%Y-%m-%d', forum_time());
			$context['calendar_birthdays'] = array_merge($context['calendar_birthdays'], $bday[strftime('%Y-%m-%d', $i)]);
		}
	}

	$context['calendar_events'] = array();
	$duplicates = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (isset($events[strftime('%Y-%m-%d', $i)]))
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
					$this_event['link'] = $this_event['topic'] == 0 ? $this_event['title'] : '<a href="' . $this_event['href'] . '">' . $this_event['title'] . '</a>';
					$this_event['modify_href'] = $scripturl . '?action=' . ($this_event['topic'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $this_event['msg'] . ';topic=' . $this_event['topic'] . '.0;calendar;') . 'eventid=' . $this_event['id'] . ';sesc=' . $sc;
					$this_event['can_edit'] = allowedTo('calendar_edit_any') || ($this_event['poster'] == $ID_MEMBER && allowedTo('calendar_edit_own'));
					$this_event['is_today'] = (strftime('%Y-%m-%d', $i)) == strftime('%Y-%m-%d', forum_time());
					$this_event['date'] = strftime('%Y-%m-%d', $i);

					$duplicates[$this_event['topic'] . $this_event['title']] = true;
				}
				else
					unset($events[strftime('%Y-%m-%d', $i)][$ev]);
			}

		if (isset($events[strftime('%Y-%m-%d', $i)]))
			$context['calendar_events'] = array_merge($context['calendar_events'], $events[strftime('%Y-%m-%d', $i)]);
	}

	for ($i = 0, $n = count($context['calendar_birthdays']); $i < $n; $i++)
		$context['calendar_birthdays'][$i]['is_last'] = !isset($context['calendar_birthdays'][$i + 1]);
	for ($i = 0, $n = count($context['calendar_events']); $i < $n; $i++)
		$context['calendar_events'][$i]['is_last'] = !isset($context['calendar_events'][$i + 1]);

	return !empty($context['calendar_holidays']) || !empty($context['calendar_birthdays']) || !empty($context['calendar_events']);
}

// Check the passed ID_MEMBER/password.  If $is_username is true, treats $id as a username.
function ssi_checkPassword($id = null, $password = null, $is_username = false)
{
	global $db_prefix, $sourcedir;

	// If $id is null, this was most likely called from a query string and should do nothing.
	if ($id === null)
		return;

	$request = db_query("
		SELECT passwd, memberName, is_activated
		FROM {$db_prefix}members
		WHERE " . ($is_username ? 'memberName' : 'ID_MEMBER') . " = '$id'
		LIMIT 1", __FILE__, __LINE__);
	list ($pass, $user, $active) = mysql_fetch_row($request);
	mysql_free_result($request);

	return sha1(strtolower($user) . $password) == $pass && $active == 1;
}

?>