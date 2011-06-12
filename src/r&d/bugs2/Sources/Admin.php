<?php
/**********************************************************************************
* Admin.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.6                                           *
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

/*	This file, unpredictable as this might be, handles basic administration.
	The most important function in this file for mod makers happens to be the
	updateSettingsFile() function, but it shouldn't be used often anyway.

	void Admin()
		- prepares all the data necessary for the administration front page.
		- uses the Admin template along with the admin sub template.
		- requires the moderate_forum, manage_membergroups, manage_bans,
		  admin_forum, manage_permissions, manage_attachments, manage_smileys,
		  manage_boards, edit_news, or send_mail permission.
		- uses the index administrative area.
		- can be found by going to ?action=admin.

	void OptimizeTables()
		- optimizes all tables in the database and lists how much was saved.
		- requires the admin_forum permission.
		- uses the rawdata sub template (built in.)
		- shows as the maintain_forum admin area.
		- updates the autoOptLastOpt setting such that the tables are not
		  automatically optimized again too soon.
		- accessed from ?action=optimizetables.

	void Maintenance()
		- shows a listing of maintenance options - including repair, recount,
		  optimize, database dump, clear logs, and remove old posts.
		- handles directly the tasks of clearing logs.
		- requires the admin_forum permission.
		- uses the maintain_forum admin area.
		- shows the maintain sub template of the Admin template.
		- accessed by ?action=maintain.

	void AdminBoardRecount()
		- recounts many forum totals that can be recounted automatically
		  without harm.
		- requires the admin_forum permission.
		- shows the maintain_forum admin area.
		- fixes topics with wrong numReplies.
		- updates the numPosts and numTopics of all boards.
		- recounts instantMessages but not unreadMessages.
		- repairs messages pointing to boards with topics pointing to
		  other boards.
		- updates the last message posted in boards and children.
		- updates member count, latest member, topic count, and message count.
		- redirects back to ?action=maintain when complete.
		- accessed via ?action=boardrecount.

	void VersionDetail()
		- parses the comment headers in all files for their version information
		  and outputs that for some javascript to check with simplemacines.org.
		- does not connect directly with simplemachines.org, but rather
		  expects the client to.
		- requires the admin_forum permission.
		- uses the view_versions admin area.
		- loads the view_versions sub template (in the Admin template.)
		- accessed through ?action=detailedversion.

	void ManageCopyright()
		// !!!

	void CleanupPermissions()
		- cleans up file permissions, in the hopes of making things work
		  smoother and potentially more securely.
		- can set permissions to either restrictive, free, or standard.
		- accessed via ?action=cleanperms.

	void updateSettingsFile(array config_vars)
		- updates the Settings.php file with the changes in config_vars.
		- expects config_vars to be an associative array, with the keys as the
		  variable names in Settings.php, and the values the varaible values.
		- does not escape or quote values.
		- preserves case, formatting, and additional options in file.
		- writes nothing if the resulting file would be less than 10 lines
		  in length (sanity check for read lock.)

	void ConvertUtf8()
		- converts the data and database tables to UTF-8 character set.
		- requires the admin_forum permission.
		- uses the convert_utf8 sub template of the Admin template.
		- only works if UTF-8 is not the global character set.
		- supports all character sets used by SMF's language files.
		- redirects to ?action=maintain after finishing.
		- is linked from the maintenance screen (if applicable).
		- accessed by ?action=convertutf8.

	void ConvertEntities()
		- converts HTML-entities to UTF-8 characters.
		- requires the admin_forum permission.
		- uses the convert_entities sub template of the Admin template.
		- only works if UTF-8 has been set as database and global character set.
		- is divided in steps of 10 seconds.
		- is linked from the maintenance screen (if applicable).
		- accessed by ?action=convertentities.
*/

// The main administration section.
function Admin()
{
	global $sourcedir, $db_prefix, $forum_version, $txt, $scripturl, $context, $modSettings;
	global $user_info, $_PHPA, $boardurl, $memcached;

	if (isset($_GET['area']) && $_GET['area'] == 'copyright')
		return ManageCopyright();

	// You have to be able to do at least one of the below to see this page.
	isAllowedTo(array('admin_forum', 'manage_permissions', 'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news', 'manage_boards', 'manage_smileys', 'manage_attachments'));

	// Load the common admin stuff... select 'index'.
	adminIndex(isset($_GET['credits']) ? 'credits' : 'index');

	// Find all of this forum's administrators.
	$request = db_query("
		SELECT ID_MEMBER, realName
		FROM {$db_prefix}members
		WHERE ID_GROUP = 1 OR FIND_IN_SET(1, additionalGroups)
		LIMIT 33", __FILE__, __LINE__);
	$context['administrators'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['administrators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';
	mysql_free_result($request);

	// If there are more than 32 admins show a more link and direct the admin to the memberlist with admin filter.
	if (count($context['administrators']) > 32)
	{
		// Quicker to get one too many than to count first...
		unset($context['administrators'][32]);
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=mlist;sa=search;fields=group;search=administrator">' . $txt['more'] . '</a>';
	}

	// Some stuff.... :P.
	$context['credits'] = '
<i>Simple Machines wants to thank everyone who helped make SMF 1.1 what it is today; shaping and directing our project, all through the thick and the thin. It wouldn\'t have been possible without you.</i><br />
<div style="margin-top: 1ex;"><i>This includes our users and especially Charter Members - thanks for installing and using our software as well as providing valuable feedback, bug reports, and opinions.</i></div>
<div style="margin-top: 2ex;"><b>Project Managers:</b> Amacythe, David Recordon, Joseph Fung, and Jeff Lewis.</div>
<div style="margin-top: 1ex;"><b>Developers:</b> Hendrik Jan &quot;Compuart&quot; Visser, Matt &quot;Grudge&quot; Wolf, Michael &quot;Thantos&quot; Miller, Theodore &quot;Orstio&quot; Hildebrandt, and Unknown W. &quot;[Unknown]&quot; Brackets</div>
<div style="margin-top: 1ex;"><b>Support Specialists:</b> Ben Scott, Michael &quot;Oldiesmann&quot; Eshom, Jan-Olof &quot;Owdy&quot; Eriksson, A&auml;ron van Geffen, Alexandre &quot;Ap2&quot; Patenaude, Andrea Hubacher, Chris Cromer, [darksteel], dtm.exe, Nick &quot;Fizzy&quot; Dyer, Horseman, Huw Ayling-Miller, jerm, Justyne, kegobeer, Kindred, Matthew &quot;Mattitude&quot; Hall, Mediman, Metho, Omar Bazavilvazo, Pitti, redone, Tomer &quot;Lamper&quot; Dean, Tony, and xenovanis.</div>
<div style="margin-top: 1ex;"><b>Mod Developers:</b> snork13, Cristi&aacute;n &quot;Anguz&quot; L&aacute;vaque, Goosemoose, Jack.R.Abbit, James &quot;Cheschire&quot; Yarbro, Jesse &quot;Gobalopper&quot; Reid, Juan &quot;JayBachatero&quot; Hernandez, Kirby, vbgamer45, and winrules.</div>
<div style="margin-top: 1ex;"><b>Documentation Writers:</b> akabugeyes, eldacar, Gary M. &quot;AwwLilMaggie&quot; Gadsdon, Jerry, and Nave.</div>
<div style="margin-top: 1ex;"><b>Language Coordinators:</b> Daniel Diehl and Adam &quot;Bostasp&quot; Southall.</div>
<div style="margin-top: 1ex;"><b>Graphic Designers:</b> Bjoern &quot;Bloc&quot; Kristiansen, Alienine (Adrian), A.M.A, babylonking, BlackouT, Burpee, diplomat, Eren &quot;forsakenlad&quot; Yasarkurt, Hyper Piranha, Killer Possum, Mystica, Nico &quot;aliencowfarm&quot; Boer, Philip &quot;Meriadoc&quot; Renich and Tippmaster.</div>
<div style="margin-top: 1ex;"><b>Site team:</b> dschwab9 and Tim.</div>
<div style="margin-top: 1ex;"><b>Marketing:</b> Douglas &quot;The Bear&quot; Hazard, RickC and Trekkie101.</div>
<div style="margin-top: 1ex;">And for anyone we may have missed, thank you!</div>';

	// Copyright?
	if (!empty($modSettings['copy_settings']) || !empty($modSettings['copyright_key']))
	{
		if (empty($modSettings['copy_settings']))
			$modSettings['copy_settings'] = 'a,0';

		// Not done it yet...
		if (empty($_SESSION['copy_expire']))
		{
			list ($key, $expires) = explode(',', $modSettings['copy_settings']);
			// Get the expired date.
			require_once($sourcedir . '/Subs-Package.php');
			$return_data = fetch_web_data($url = 'http://www.simplemachines.org/smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $key . '&version=' . base64_encode($forum_version));

			// Get the expire date.
			$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
			$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

			if ($return_data != 'void')
			{
				list ($_SESSION['copy_expire'], $copyright_key) = explode('|', $return_data);
				$_SESSION['copy_key'] = $key;
				$copy_settings = $key . ',' . (int) $_SESSION['copy_expire'];
				updateSettings(array('copy_settings' => $copy_settings, 'copyright_key' => $copyright_key));
			}
			else
			{
				$_SESSION['copy_expire'] = '';
				db_query("
					DELETE FROM {$db_prefix}settings
					WHERE variable = 'copy_settings'
						OR variable = 'copyright_key'", __FILE__, __LINE__);
			}
		}

		if (isset($_SESSION['copy_expire']) && $_SESSION['copy_expire'] > time())
		{
			$context['copyright_expires'] = (int) (($_SESSION['copy_expire'] - time()) / 3600 / 24);
			$context['copyright_key'] = $_SESSION['copy_key'];
		}
	}

	// This makes it easier to get the latest news with your time format.
	$context['time_format'] = urlencode($user_info['time_format']);

	$context['current_versions'] = array(
		'php' => array('title' => $txt['support_versions_php'], 'version' => PHP_VERSION),
		'mysql' => array('title' => $txt['support_versions_mysql'], 'version' => ''),
		'server' => array('title' => $txt['support_versions_server'], 'version' => $_SERVER['SERVER_SOFTWARE']),
	);
	$context['forum_version'] = $forum_version;

	// Is GD available?  If it is, we should show version information for it too.
	if (function_exists('gd_info'))
	{
		$temp = gd_info();
		$context['current_versions']['gd'] = array('title' => $txt['support_versions_gd'], 'version' => $temp['GD Version']);
	}

	$request = db_query("
		SELECT VERSION()", __FILE__, __LINE__);
	list ($context['current_versions']['mysql']['version']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// If we're using memcache we need the server info.
	if (empty($memcached) && function_exists('memcache_get') && isset($modSettings['cache_memcached']) && trim($modSettings['cache_memcached']) != '')
		get_memcached_server();

	// Check to see if we have any accelerators installed...
	if (defined('MMCACHE_VERSION'))
		$context['current_versions']['mmcache'] = array('title' => 'Turck MMCache', 'version' => MMCACHE_VERSION);
	if (defined('EACCELERATOR_VERSION'))
		$context['current_versions']['eaccelerator'] = array('title' => 'eAccelerator', 'version' => EACCELERATOR_VERSION);
	if (isset($_PHPA))
		$context['current_versions']['phpa'] = array('title' => 'ionCube PHP-Accelerator', 'version' => $_PHPA['VERSION']);
	if (extension_loaded('apc'))
		$context['current_versions']['apc'] = array('title' => 'Alternative PHP Cache', 'version' => phpversion('apc'));
	if (function_exists('memcache_set'))
		$context['current_versions']['memcache'] = array('title' => 'Memcached', 'version' => empty($memcached) ? '???' : memcache_get_version($memcached));

	$context['can_admin'] = allowedTo('admin_forum');

	$context['sub_template'] = isset($_GET['credits']) ? 'credits' : 'admin';
	$context['page_title'] = isset($_GET['credits']) ? $txt['support_credits_title'] : $txt[208];

	// The format of this array is: permission, action, title, description.
	$quick_admin_tasks = array(
		array('', 'admin;credits', 'support_credits_title', 'support_credits_info'),
		array('admin_forum', 'featuresettings', 'modSettings_title', 'modSettings_info'),
		array('admin_forum', 'maintain', 'maintain_title', 'maintain_info'),
		array('manage_permissions', 'permissions', 'edit_permissions', 'edit_permissions_info'),
		array('admin_forum', 'theme;sa=admin;sesc=' . $context['session_id'], 'theme_admin', 'theme_admin_info'),
		array('admin_forum', 'packages', 'package1', 'package_info'),
		array('manage_smileys', 'smileys', 'smileys_manage', 'smileys_manage_info'),
		array('moderate_forum', 'viewmembers', '5', 'member_center_info'),
	);

	$context['quick_admin_tasks'] = array();
	foreach ($quick_admin_tasks as $task)
	{
		if (!empty($task[0]) && !allowedTo($task[0]))
			continue;

		$context['quick_admin_tasks'][] = array(
			'href' => $scripturl . '?action=' . $task[1],
			'link' => '<a href="' . $scripturl . '?action=' . $task[1] . '">' . $txt[$task[2]] . '</a>',
			'title' => $txt[$task[2]],
			'description' => $txt[$task[3]],
			'is_last' => false
		);
	}

	if (count($context['quick_admin_tasks']) % 2 == 1)
	{
		$context['quick_admin_tasks'][] = array(
			'href' => '',
			'link' => '',
			'title' => '',
			'description' => '',
			'is_last' => true
		);
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
	elseif (count($context['quick_admin_tasks']) != 0)
	{
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 1]['is_last'] = true;
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
}

// Optimize the database's tables.
function OptimizeTables()
{
	global $db_name, $txt, $context, $scripturl;

	isAllowedTo('admin_forum');

	// Boldify "Maintain Forum".
	adminIndex('maintain_forum');

	ignore_user_abort(true);

	// Start with no tables optimized.
	$opttab = 0;

	$context['page_title'] = $txt['smf281'];
	$context['sub_template'] = 'optimize';

	// Get a list of tables, as well as how many there are.
	$result = db_query("
		SHOW TABLE STATUS
		FROM `$db_name`", false, false);
	$tables = array();

	if (!$result)
	{
		$result = db_query("
			SHOW TABLES
			FROM `$db_name`", __FILE__, __LINE__);
		while ($table = mysql_fetch_row($result))
			$tables[] = array('table_name' => $table[0]);
		mysql_free_result($result);
	}
	else
	{
		$i = 0;
		while ($table = mysql_fetch_assoc($result))
			$tables[] = $table + array('table_name' => mysql_tablename($result, $i++));
		mysql_free_result($result);
	}

	// If there aren't any tables then I believe that would mean the world has exploded...
	$context['num_tables'] = count($tables);
	if ($context['num_tables'] == 0)
		fatal_error('You appear to be running SMF in a flat file mode... fantastic!', false);

	// For each table....
	$context['optimized_tables'] = array();
	foreach ($tables as $table)
	{
		// Optimize the table!  We use backticks here because it might be a custom table.
		$result = db_query("
			OPTIMIZE TABLE `$table[table_name]`", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);

		if (!isset($row['Msg_text']) || strpos($row['Msg_text'], 'already') === false || !isset($table['Data_free']) || $table['Data_free'] != 0)
			$context['optimized_tables'][] = array(
				'name' => $table['table_name'],
				'data_freed' => isset($table['Data_free']) ? $table['Data_free'] / 1024 : '<i>??</i>',
			);
	}

	// Number of tables, etc....
	$txt['smf282'] = sprintf($txt['smf282'], $context['num_tables']);
	$context['num_tables_optimized'] = count($context['optimized_tables']);

	updateSettings(array('autoOptLastOpt' => time()));
}

// Miscellaneous maintenance..
function Maintenance()
{
	global $context, $txt, $db_prefix, $user_info, $db_character_set, $modSettings;

	isAllowedTo('admin_forum');

	adminIndex('maintain_forum');

	if (isset($_GET['sa']) && $_GET['sa'] == 'logs')
	{
		// No one's online now.... MUHAHAHAHA :P.
		db_query("
			DELETE FROM {$db_prefix}log_online", __FILE__, __LINE__);

		// Dump the banning logs.
		db_query("
			DELETE FROM {$db_prefix}log_banned", __FILE__, __LINE__);

		// Start ID_ERROR back at 0 and dump the error log.
		db_query("
			TRUNCATE {$db_prefix}log_errors", __FILE__, __LINE__);

		// Clear out the spam log.
		db_query("
			DELETE FROM {$db_prefix}log_floodcontrol", __FILE__, __LINE__);

		// Clear out the karma actions.
		db_query("
			DELETE FROM {$db_prefix}log_karma", __FILE__, __LINE__);

		// Last but not least, the search logs!
		db_query("
			TRUNCATE {$db_prefix}log_search_topics", __FILE__, __LINE__);
		db_query("
			TRUNCATE {$db_prefix}log_search_messages", __FILE__, __LINE__);
		db_query("
			TRUNCATE {$db_prefix}log_search_results", __FILE__, __LINE__);

		updateSettings(array('search_pointer' => 0));

		$context['maintenance_finished'] = true;
	}
	elseif (isset($_GET['sa']) && $_GET['sa'] == 'destroy')
	{
		// Oh noes!
		echo '<html><head><title>', $context['forum_name'], ' deleted!</title></head>
			<body style="background-color: orange; font-family: arial, sans-serif; text-align: center;">
			<div style="margin-top: 8%; font-size: 400%; color: black;">Oh my, you killed ', $context['forum_name'], '!</div>
			<div style="margin-top: 7%; font-size: 500%; color: red;"><b>You lazy bum!</b></div>
			</body></html>';
		obExit(false);
	}
	else
		$context['maintenance_finished'] = isset($_GET['done']);

	// Grab some boards maintenance can be done on.
	$result = db_query("
		SELECT b.ID_BOARD, b.name, b.childLevel, c.name AS catName, c.ID_CAT
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['categories'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		if (!isset($context['categories'][$row['ID_CAT']]))
			$context['categories'][$row['ID_CAT']] = array(
				'name' => $row['catName'],
				'boards' => array()
			);

		$context['categories'][$row['ID_CAT']]['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'child_level' => $row['childLevel']
		);
	}
	mysql_free_result($result);

	$context['convert_utf8'] = (!isset($db_character_set) || $db_character_set !== 'utf8' || empty($modSettings['global_character_set']) || $modSettings['global_character_set'] !== 'UTF-8') && version_compare('4.1.2', preg_replace('~\-.+?$~', '', mysql_get_server_info())) <= 0;
	$context['convert_entities'] = isset($db_character_set, $modSettings['global_character_set']) && $db_character_set === 'utf8' && $modSettings['global_character_set'] === 'UTF-8';

	$context['sub_template'] = 'maintain';
	$context['page_title'] = $txt['maintain_title'];
}

// Recount all the important board totals.
function AdminBoardRecount()
{
	global $txt, $db_prefix, $context, $scripturl, $modSettings, $time_start;

	isAllowedTo('admin_forum');

	// Select it on the left.
	adminIndex('maintain_forum');

	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '3';
	$context['sub_template'] = 'not_done';

	// Try for as much time as possible.
	@set_time_limit(600);

	// Step the number of topics at a time so things don't time out...
	$request = db_query("
		SELECT MAX(ID_TOPIC)
		FROM {$db_prefix}topics", __FILE__, __LINE__);
	list ($max_topics) = mysql_fetch_row($request);
	mysql_free_result($request);

	$increment = min(ceil($max_topics / 4), 2000);
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	$total_steps = 5;

	// Get each topic with a wrong reply count and fix it - let's just do some at a time, though.
	if (empty($_REQUEST['step']))
	{
		$_REQUEST['step'] = 0;

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = db_query("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_TOPIC, t.numReplies, COUNT(*) - 1 AS realNumReplies
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
				WHERE m.ID_TOPIC = t.ID_TOPIC
					AND t.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND t.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
				GROUP BY t.ID_TOPIC
				HAVING realNumReplies != numReplies", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				db_query("
					UPDATE {$db_prefix}topics
					SET numReplies = $row[realNumReplies]
					WHERE ID_TOPIC = $row[ID_TOPIC]
					LIMIT 1", __FILE__, __LINE__);
			mysql_free_result($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=boardrecount;step=0;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the post and topic count of each board.
	if ($_REQUEST['step'] <= 1)
	{
		if (empty($_REQUEST['start']))
			db_query("
				UPDATE {$db_prefix}boards
				SET numPosts = 0, numTopics = 0", __FILE__, __LINE__);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = db_query("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_BOARD, COUNT(*) AS realNumPosts, COUNT(DISTINCT t.ID_TOPIC) AS realNumTopics
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
				WHERE m.ID_TOPIC = t.ID_TOPIC
					AND m.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND m.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
				GROUP BY t.ID_BOARD", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				db_query("
					UPDATE {$db_prefix}boards
					SET numPosts = numPosts + $row[realNumPosts],
						numTopics = numTopics + $row[realNumTopics]
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			mysql_free_result($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=boardrecount;step=1;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((100 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Get all members with wrong number of personal messages.
	if ($_REQUEST['step'] <= 2)
	{
		$request = db_query("
			SELECT /*!40001 SQL_NO_CACHE */ mem.ID_MEMBER, COUNT(pmr.ID_PM) AS realNum, mem.instantMessages
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (mem.ID_MEMBER = pmr.ID_MEMBER AND pmr.deleted = 0)
			GROUP BY mem.ID_MEMBER
			HAVING realNum != instantMessages", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			updateMemberData($row['ID_MEMBER'], array('instantMessages' => $row['realNum']));
		mysql_free_result($request);

		$request = db_query("
			SELECT /*!40001 SQL_NO_CACHE */ mem.ID_MEMBER, COUNT(pmr.ID_PM) AS realNum, mem.unreadMessages
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (mem.ID_MEMBER = pmr.ID_MEMBER AND pmr.deleted = 0 AND pmr.is_read = 0)
			GROUP BY mem.ID_MEMBER
			HAVING realNum != unreadMessages", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			updateMemberData($row['ID_MEMBER'], array('unreadMessages' => $row['realNum']));
		mysql_free_result($request);

		if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
		{
			$context['continue_get_data'] = '?action=boardrecount;step=2;start=0';
			$context['continue_percent'] = round(300 / $total_steps);

			return;
		}
	}

	// Any messages pointing to the wrong board?
	if ($_REQUEST['step'] <= 3)
	{
		while ($_REQUEST['start'] < $modSettings['maxMsgID'])
		{
			$request = db_query("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_BOARD, m.ID_MSG
				FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
				WHERE t.ID_TOPIC = m.ID_TOPIC
					AND m.ID_MSG > $_REQUEST[start]
					AND m.ID_MSG <= " . ($_REQUEST['start'] + $increment) . "
					AND m.ID_BOARD != t.ID_BOARD", __FILE__, __LINE__);
			$boards = array();
			while ($row = mysql_fetch_assoc($request))
				$boards[$row['ID_BOARD']][] = $row['ID_MSG'];
			mysql_free_result($request);

			foreach ($boards as $board_id => $messages)
				db_query("
					UPDATE {$db_prefix}messages
					SET ID_BOARD = $board_id
					WHERE ID_MSG IN (" . implode(', ', $messages) . ")
					LIMIT " . count($messages), __FILE__, __LINE__);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=boardrecount;step=3;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((300 + 100 * $_REQUEST['start'] / $modSettings['maxMsgID']) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the latest message of each board.
	$request = db_query("
		SELECT /*!40001 SQL_NO_CACHE */ b.ID_BOARD, b.ID_PARENT, b.ID_LAST_MSG, MAX(m.ID_MSG) AS localLastMsg, b.childLevel
		FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m)
		WHERE b.ID_BOARD = m.ID_BOARD
		GROUP BY ID_BOARD", __FILE__, __LINE__);
	$resort_me = array();
	while ($row = mysql_fetch_assoc($request))
		$resort_me[$row['childLevel']][] = $row;
	mysql_free_result($request);

	krsort($resort_me);

	$lastMsg = array();
	foreach ($resort_me as $rows)
		foreach ($rows as $row)
		{
			// The latest message is the latest of the current board and its children.
			if (isset($lastMsg[$row['ID_BOARD']]))
				$curLastMsg = max($row['localLastMsg'], $lastMsg[$row['ID_BOARD']]);
			else
				$curLastMsg = $row['localLastMsg'];

			// If what is and what should be the latest message differ, an update is necessary.
			if ($curLastMsg != $row['ID_LAST_MSG'])
				db_query("
					UPDATE {$db_prefix}boards
					SET ID_LAST_MSG = $curLastMsg
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);

			// Parent boards inherit the latest message of their children.
			if (isset($lastMsg[$row['ID_PARENT']]))
				$lastMsg[$row['ID_PARENT']] = max($row['localLastMsg'], $lastMsg[$row['ID_PARENT']]);
			else
				$lastMsg[$row['ID_PARENT']] = $row['localLastMsg'];
		}

	// Update all the basic statistics.
	updateStats('member');
	updateStats('message');
	updateStats('topic');

	redirectexit('action=maintain;done');
}

// Perform a detailed version check.  A very good thing ;).
function VersionDetail()
{
	global $forum_version;
	global $txt, $boarddir, $sourcedir, $context, $settings;

	isAllowedTo('admin_forum');

	// Set up the sidebar for version checking.
	adminIndex('view_versions');

	$context['file_versions'] = array();
	$context['default_template_versions'] = array();
	$context['template_versions'] = array();
	$context['default_language_versions'] = array();

	// Find the version in SSI.php's file header.
	$fp = fopen($boarddir . '/SSI.php', 'rb');
	$header = fread($fp, 4096);
	fclose($fp);

	// The comment looks rougly like... that.
	if (preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $header, $match) == 1)
		$context['file_versions']['SSI.php'] = $match[1];
	// Not found!  This is bad.
	else
		$context['file_versions']['SSI.php'] = '??';

	// Load all the files in the Sources directory, except this file and the redirect.
	$Sources_dir = dir($sourcedir);
	while ($entry = $Sources_dir->read())
		if (substr($entry, -4) == '.php' && !is_dir($sourcedir . '/' . $entry) && $entry != 'index.php')
		{
			// Read the first 4k from the file.... enough for the header.
			$fp = fopen($sourcedir . '/' . $entry, 'rb');
			$header = fread($fp, 4096);
			fclose($fp);

			// Look for the version comment in the file header.
			if (preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $header, $match) == 1)
				$context['file_versions'][$entry] = $match[1];
			// It wasn't found, but the file was... show a '??'.
			else
				$context['file_versions'][$entry] = '??';
		}
	$Sources_dir->close();

	// Load all the files in the default template directory - and the current theme if applicable.
	$directories = array('default_template_versions' => $settings['default_theme_dir']);
	if ($settings['theme_id'] != 1)
		$directories += array('template_versions' => $settings['theme_dir']);

	foreach ($directories as $type => $dirname)
	{
		$This_dir = dir($dirname);
		while ($entry = $This_dir->read())
			if (substr($entry, -12) == 'template.php' && !is_dir($dirname . '/' . $entry))
			{
				// Read the first 768 bytes from the file.... enough for the header.
				$fp = fopen($dirname . '/' . $entry, 'rb');
				$header = fread($fp, 768);
				fclose($fp);

				// Look for the version comment in the file header.
				if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*' . preg_quote(basename($entry, '.template.php'), '~') . '(?:[\s]{2}|\*/)~i', $header, $match) == 1)
					$context[$type][$entry] = $match[1];
				// It wasn't found, but the file was... show a '??'.
				else
					$context[$type][$entry] = '??';
			}
		$This_dir->close();
	}

	// Load up all the files in the default language directory and sort by language.
	$lang_dir = $settings['default_theme_dir'] . '/languages';
	$This_dir = dir($lang_dir);
	while ($entry = $This_dir->read())
		if (substr($entry, -4) == '.php' && $entry != 'index.php' && !is_dir($lang_dir . '/' . $entry))
		{
			// Read the first 768 bytes from the file.... enough for the header.
			$fp = fopen($lang_dir . '/' . $entry, 'rb');
			$header = fread($fp, 768);
			fclose($fp);

			// Split the file name off into useful bits.
			list ($name, $language) = explode('.', $entry);

			// Look for the version comment in the file header.
			if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*' . preg_quote($name, '~') . '(?:[\s]{2}|\*/)~i', $header, $match) == 1)
				$context['default_language_versions'][$language][$name] = $match[1];
			// It wasn't found, but the file was... show a '??'.
			else
				$context['default_language_versions'][$language][$name] = '??';
		}
	$This_dir->close();

	// Sort the file versions by filename.
	ksort($context['file_versions']);
	ksort($context['default_template_versions']);
	ksort($context['template_versions']);
	ksort($context['default_language_versions']);

	// For languages sort each language too.
	foreach ($context['default_language_versions'] as $key => $dummy)
		ksort($context['default_language_versions'][$key]);

	$context['default_known_languages'] = array_keys($context['default_language_versions']);

	// Make it easier to manage for the template.
	$context['forum_version'] = $forum_version;

	$context['sub_template'] = 'view_versions';
	$context['page_title'] = $txt[429];
}

// Allow users to remove their copyright.
function ManageCopyright()
{
	global $forum_version, $txt, $sourcedir, $context, $boardurl, $modSettings;

	isAllowedTo('admin_forum');

	if (isset($_POST['copy_code']))
	{
		checkSession('post');

		$_POST['copy_code'] = urlencode($_POST['copy_code']);

		// Check the actual code.
		require_once($sourcedir . '/Subs-Package.php');
		$return_data = fetch_web_data('http://www.simplemachines.org/smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $_POST['copy_code'] . '&version=' . base64_encode($forum_version));

		// Get the data back
		$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
		$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

		if ($return_data != 'void')
		{
			list ($_SESSION['copy_expire'], $copyright_key) = explode('|', $return_data);
			$_SESSION['copy_key'] = $_POST['copy_code'];
			$copy_settings = $_POST['copy_code'] . ',' . (int) $_SESSION['copy_expire'];
			updateSettings(array('copy_settings' => $copy_settings, 'copyright_key' => $copyright_key));
			redirectexit('action=admin');
		}
		else
		{
			fatal_lang_error('copyright_failed');
		}
	}

	adminIndex('index');

	$context['sub_template'] = 'manage_copyright';
	$context['page_title'] = $txt['copyright_removal'];
}

// Clean up the permissions one way or another.
function CleanupPermissions()
{
	global $boarddir, $sourcedir, $scripturl, $package_ftp, $modSettings;

	isAllowedTo('admin_forum');
	umask(0);

	loadTemplate('Packages');
	loadLanguage('Packages');

	if (!isset($_REQUEST['perm_type']) || !in_array($_REQUEST['perm_type'], array('free', 'restrictive', 'standard')))
		$_REQUEST['perm_type'] = 'free';

	checkSession();

	// FTP to the rescue!
	require_once($sourcedir . '/Subs-Package.php');
	packageRequireFTP($scripturl . '?action=cleanperms;perm_type=' . $_REQUEST['perm_type']);

	// The files that should be chmod'd are here - add any if you add any files with a modification.
	$special_files = array();
	$special_files['restrictive'] = array(
		'/attachments',
		'/custom_avatar_dir',
		'/Settings.php',
		'/Settings_bak.php',
	);
	$special_files['standard'] = array(
		'/attachments',
		'/avatars',
		'/custom_avatar_dir',
		'/Packages',
		'/Packages/installed.list',
		'/Smileys',
		'/Themes',
		'/agreement.txt',
		'/Settings.php',
		'/Settings_bak.php',
	);

	@chmod($boarddir . '/Settings.php', 0755);
	if (isset($package_ftp))
		$package_ftp->chmod(strtr($boarddir . '/Settings.php', array($_SESSION['pack_ftp']['root'] => '')), 0755);

	// If the owner of PHP is not nobody, this should probably pass through - in which case 755 is better than 777.
	if ((!function_exists('is_executable') || is_executable($boarddir . '/Settings.php')) && is_writable($boarddir . '/Settings.php'))
		$suexec_fix = 0755;
	else
		$suexec_fix = 0777;

	@chmod($boarddir, $_REQUEST['perm_type'] == 'free' ? $suexec_fix : 0755);
	if (isset($package_ftp))
		$package_ftp->chmod(strtr($boarddir . '/.', array($_SESSION['pack_ftp']['root'] => '')), $_REQUEST['perm_type'] == 'free' ? $suexec_fix : 0755);

	$dirs = array('' => $boarddir);

	if (substr($sourcedir, 0, strlen($boarddir)) != $boarddir)
		$dirs['/Sources'] = $sourcedir;
	if (substr($modSettings['attachmentUploadDir'], 0, strlen($boarddir)) != $boarddir)
		$dirs['/attachments'] = $modSettings['attachmentUploadDir'];
	if (substr($modSettings['smileys_dir'], 0, strlen($boarddir)) != $boarddir)
		$dirs['/Smileys'] = $modSettings['smileys_dir'];
	if (substr($modSettings['avatar_directory'], 0, strlen($boarddir)) != $boarddir)
		$dirs['/avatars'] = $modSettings['avatar_directory'];
	if (isset($modSettings['custom_avatar_dir']) && substr($modSettings['custom_avatar_dir'], 0, strlen($boarddir)) != $boarddir)
		$dirs['/custom_avatar_dir'] = $modSettings['custom_avatar_dir'];

	$done_dirs = array();
	while (count($dirs) > 0)
	{
		// The alias is what we know it as.  The attachments directory *could* be named "uploads".
		$temp = array_keys($dirs);
		$alias = $temp[0];

		// The actual full filename...
		$dirname = $dirs[$alias];
		unset($dirs[$alias]);

		$dir = dir($dirname);
		if (!$dir)
			continue;

		while ($entry = $dir->read())
		{
			if ($entry == '.' || $entry == '..')
				continue;

			// Figure out the filenames to chmod with...
			$filename = $dirname . '/' . $entry;
			$ftp_file = strtr($filename, array($_SESSION['pack_ftp']['root'] => ''));

			// Is this one we want writable?
			if ($_REQUEST['perm_type'] == 'free' || in_array($alias . '/' . $entry, $special_files[$_REQUEST['perm_type']]))
			{
				@chmod($filename, $suexec_fix);
				if (isset($package_ftp))
					$package_ftp->chmod($ftp_file, $suexec_fix);
			}
			// Not writable, just executable... yes?
			else
			{
				@chmod($filename, 0755);
				if (isset($package_ftp))
					$package_ftp->chmod($ftp_file, 0755);
			}

			// Directories get added to the todo list.
			if (@is_dir($filename) && !in_array($filename, $done_dirs))
				$dirs[$alias . '/' . $entry] = $filename;
		}

		$done_dirs[] = $dirname;
	}

	redirectexit('action=packages;sa=options');
}

// Update the Settings.php file.
function updateSettingsFile($config_vars)
{
	global $boarddir;

	// Load the file.  Break it up based on \r or \n, and then clean out extra characters.
	$settingsArray = file_get_contents($boarddir . '/Settings.php');
	if (strpos($settingsArray, "\n") !== false)
		$settingsArray = explode("\n", $settingsArray);
	elseif (strpos($settingsArray, "\r") !== false)
		$settingsArray = explode("\r", $settingsArray);
	else
		return;

	// Make sure we got a good file.
	if (count($config_vars) == 1 && isset($config_vars['db_last_error']))
	{
		$temp = trim(implode("\n", $settingsArray));
		if (substr($temp, 0, 5) != '<?php' || substr($temp, -2) != '?' . '>')
			return;
		if (strpos($temp, 'sourcedir') === false || strpos($temp, 'boarddir') === false || strpos($temp, 'cookiename') === false)
			return;
	}

	// Presumably, the file has to have stuff in it for this function to be called :P.
	if (count($settingsArray) < 10)
		return;

	foreach ($settingsArray as $k => $dummy)
		$settingsArray[$k] = strtr($dummy, array("\r" => '')) . "\n";

	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		// Don't trim or bother with it if it's not a variable.
		if (substr($settingsArray[$i], 0, 1) != '$')
			continue;

		$settingsArray[$i] = trim($settingsArray[$i]) . "\n";

		// Look through the variables to set....
		foreach ($config_vars as $var => $val)
		{
			if (strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				$comment = strstr(substr($settingsArray[$i], strpos($settingsArray[$i], ';')), '#');
				$settingsArray[$i] = '$' . $var . ' = ' . $val . ';' . ($comment == '' ? '' : "\t\t" . rtrim($comment)) . "\n";

				// This one's been 'used', so to speak.
				unset($config_vars[$var]);
			}
		}

		if (substr(trim($settingsArray[$i]), 0, 2) == '?' . '>')
			$end = $i;
	}

	// This should never happen, but apparently it is happening.
	if (empty($end) || $end < 10)
		$end = count($settingsArray) - 1;

	// Still more?  Add them at the end.
	if (!empty($config_vars))
	{
		if (trim($settingsArray[$end]) == '?' . '>')
			$settingsArray[$end++] = '';
		else
			$end++;

		foreach ($config_vars as $var => $val)
			$settingsArray[$end++] = '$' . $var . ' = ' . $val . ';' . "\n";
		$settingsArray[$end] = '?' . '>';
	}
	else
		$settingsArray[$end] = trim($settingsArray[$end]);

	// Sanity error checking: the file needs to be at least 12 lines.
	if (count($settingsArray) < 12)
		return;

	// Blank out the file - done to fix a oddity with some servers.
	$fp = @fopen($boarddir . '/Settings.php', 'w');

	// Is it even writable, though?
	if ($fp)
	{
		fclose($fp);

		$fp = fopen($boarddir . '/Settings.php', 'r+');
		foreach ($settingsArray as $line)
			fwrite($fp, strtr($line, "\r", ''));
		fclose($fp);
	}
}

// Convert both data and database tables to UTF-8 character set.
function ConvertUtf8()
{
	global $scripturl, $context, $txt, $language, $db_prefix, $db_character_set;
	global $modSettings, $user_info, $sourcedir;

	// Show me your badge!
	isAllowedTo('admin_forum');

	// The character sets used in SMF's language files with their db equivalent.
	$charsets = array(
		// Chinese-traditional.
		'big5' => 'big5',
		// Chinese-simplified.
		'gbk' => 'gbk',
		// West European.
		'ISO-8859-1' => 'latin1',
		// Romanian.
		'ISO-8859-2' => 'latin2',
		// Turkish.
		'ISO-8859-9' => 'latin5',
		// Thai.
		'tis-620' => 'tis620',
		// Persian, Chinese, etc.
		'UTF-8' => 'utf8',
		// Russian.
		'windows-1251' => 'cp1251',
		// Greek.
		'windows-1253' => 'utf8',
		// Hebrew.
		'windows-1255' => 'utf8',
		// Arabic.
		'windows-1256' => 'cp1256',
	);

	// Get a list of character sets supported by your MySQL server.
	$request = db_query("
		SHOW CHARACTER SET", __FILE__, __LINE__);
	$db_charsets = array();
	while ($row = mysql_fetch_assoc($request))
		$db_charsets[] = $row['Charset'];

	// Character sets supported by both MySQL and SMF's language files.
	$charsets = array_intersect($charsets, $db_charsets);

	// This is for the first screen telling backups is good.
	if (!isset($_POST['proceed']))
	{
		adminIndex('maintain_forum');

		// Character set conversions are only supported as of MySQL 4.1.2.
		if (version_compare('4.1.2', preg_replace('~\-.+?$~', '', mysql_get_server_info())) > 0)
			fatal_lang_error('utf8_db_version_too_low');

		// Use the messages.body column as indicator for the database charset.
		$request = db_query("
			SHOW FULL COLUMNS
			FROM {$db_prefix}messages
			LIKE 'body'", __FILE__, __LINE__);
		$column_info = mysql_fetch_assoc($request);
		mysql_free_result($request);

		// A collation looks like latin1_swedish. We only need the character set.
		list($context['database_charset']) = explode('_', $column_info['Collation']);
		$context['database_charset'] = in_array($context['database_charset'], $charsets) ? array_search($context['database_charset'], $charsets) : $context['database_charset'];

		// No need to convert to UTF-8 if it already is.
		if ($db_character_set === 'utf8' && !empty($modSettings['global_character_set']) && $modSettings['global_character_set'] === 'UTF-8')
			fatal_lang_error('utf8_already_utf8');

		// Grab the character set from the default language file.
		loadLanguage('index', $language, true);
		$context['charset_detected'] = $txt['lang_character_set'];
		$context['charset_about_detected'] = sprintf($txt['utf8_detected_charset'], $language, $context['charset_detected']);

		// Go back to your own language.
		loadLanguage('index', $user_info['language'], true);

		// Show a warning if the character set seems not to be supported.
		if (!isset($charsets[strtr(strtolower($context['charset_detected']), array('utf' => 'UTF', 'iso' => 'ISO'))]))
		{
			$context['charset_warning'] = sprintf($txt['utf8_charset_not_supported'], $txt['lang_character_set']);

			// Default to ISO-8859-1.
			$context['charset_detected'] = 'ISO-8859-1';
		}

		$context['charset_list'] = array_keys($charsets);

		$context['page_title'] = $txt['utf8_title'];
		$context['sub_template'] = 'convert_utf8';
		return;
	}
	
	// After this point we're starting the conversion. But first: session check.
	checkSession();

	// Translation table for the character sets not native for MySQL.
	$translation_tables = array(
		'windows-1255' => array(
			'0x81' => '\'\'',			'0x8A' => '\'\'',			'0x8C' => '\'\'',
			'0x8D' => '\'\'',			'0x8E' => '\'\'',			'0x8F' => '\'\'',
			'0x90' => '\'\'',			'0x9A' => '\'\'',			'0x9C' => '\'\'',
			'0x9D' => '\'\'',			'0x9E' => '\'\'',			'0x9F' => '\'\'',
			'0xCA' => '\'\'',			'0xD9' => '\'\'',			'0xDA' => '\'\'',
			'0xDB' => '\'\'',			'0xDC' => '\'\'',			'0xDD' => '\'\'',
			'0xDE' => '\'\'',			'0xDF' => '\'\'',			'0xFB' => '\'\'',
			'0xFC' => '\'\'',			'0xFF' => '\'\'',			'0xC2' => '0xFF',
			'0x80' => '0xFC',			'0xE2' => '0xFB',			'0xA0' => '0xC2A0',
			'0xA1' => '0xC2A1',		'0xA2' => '0xC2A2',		'0xA3' => '0xC2A3',
			'0xA5' => '0xC2A5',		'0xA6' => '0xC2A6',		'0xA7' => '0xC2A7',
			'0xA8' => '0xC2A8',		'0xA9' => '0xC2A9',		'0xAB' => '0xC2AB',
			'0xAC' => '0xC2AC',		'0xAD' => '0xC2AD',		'0xAE' => '0xC2AE',
			'0xAF' => '0xC2AF',		'0xB0' => '0xC2B0',		'0xB1' => '0xC2B1',
			'0xB2' => '0xC2B2',		'0xB3' => '0xC2B3',		'0xB4' => '0xC2B4',
			'0xB5' => '0xC2B5',		'0xB6' => '0xC2B6',		'0xB7' => '0xC2B7',
			'0xB8' => '0xC2B8',		'0xB9' => '0xC2B9',		'0xBB' => '0xC2BB',
			'0xBC' => '0xC2BC',		'0xBD' => '0xC2BD',		'0xBE' => '0xC2BE',
			'0xBF' => '0xC2BF',		'0xD7' => '0xD7B3',		'0xD1' => '0xD781',
			'0xD4' => '0xD7B0',		'0xD5' => '0xD7B1',		'0xD6' => '0xD7B2',
			'0xE0' => '0xD790',		'0xEA' => '0xD79A',		'0xEC' => '0xD79C',
			'0xED' => '0xD79D',		'0xEE' => '0xD79E',		'0xEF' => '0xD79F',
			'0xF0' => '0xD7A0',		'0xF1' => '0xD7A1',		'0xF2' => '0xD7A2',
			'0xF3' => '0xD7A3',		'0xF5' => '0xD7A5',		'0xF6' => '0xD7A6',
			'0xF7' => '0xD7A7',		'0xF8' => '0xD7A8',		'0xF9' => '0xD7A9',
			'0x82' => '0xE2809A',	'0x84' => '0xE2809E',	'0x85' => '0xE280A6',
			'0x86' => '0xE280A0',	'0x87' => '0xE280A1',	'0x89' => '0xE280B0',
			'0x8B' => '0xE280B9',	'0x93' => '0xE2809C',	'0x94' => '0xE2809D',
			'0x95' => '0xE280A2',	'0x97' => '0xE28094',	'0x99' => '0xE284A2',
			'0xC0' => '0xD6B0',		'0xC1' => '0xD6B1',		'0xC3' => '0xD6B3',
			'0xC4' => '0xD6B4',		'0xC5' => '0xD6B5',		'0xC6' => '0xD6B6',
			'0xC7' => '0xD6B7',		'0xC8' => '0xD6B8',		'0xC9' => '0xD6B9',
			'0xCB' => '0xD6BB',		'0xCC' => '0xD6BC',		'0xCD' => '0xD6BD',
			'0xCE' => '0xD6BE',		'0xCF' => '0xD6BF',		'0xD0' => '0xD780',
			'0xD2' => '0xD782',		'0xE3' => '0xD793',		'0xE4' => '0xD794',
			'0xE5' => '0xD795',		'0xE7' => '0xD797',		'0xE9' => '0xD799',
			'0xFD' => '0xE2808E',	'0xFE' => '0xE2808F',	'0x92' => '0xE28099',
			'0x83' => '0xC692',		'0xD3' => '0xD783',		'0x88' => '0xCB86',
			'0x98' => '0xCB9C',		'0x91' => '0xE28098',	'0x96' => '0xE28093',
			'0xBA' => '0xC3B7',		'0x9B' => '0xE280BA',	'0xAA' => '0xC397',
			'0xA4' => '0xE282AA',	'0xE1' => '0xD791',		'0xE6' => '0xD796',
			'0xE8' => '0xD798',		'0xEB' => '0xD79B',		'0xF4' => '0xD7A4',
			'0xFA' => '0xD7AA',		'0xFF' => '0xD6B2',		'0xFC' => '0xE282AC',
			'0xFB' => '0xD792',
		),
		'windows-1253' => array(
			'0x81' => "''",			'0x88' => "''",			'0x8A' => "''",
			'0x8C' => "''",			'0x8D' => "''",			'0x8E' => "''",
			'0x8F' => "''",			'0x90' => "''",			'0x98' => "''",
			'0x9A' => "''",			'0x9C' => "''",			'0x9D' => "''",
			'0x9E' => "''",			'0x9F' => "''",			'0xAA' => "''",
			'0xD2' => "''",			'0xFF' => "''",			'0xCE' => '0xCE9E',
			'0xB8' => '0xCE88',		'0xBA' => '0xCE8A',		'0xBC' => '0xCE8C',
			'0xBE' => '0xCE8E',		'0xBF' => '0xCE8F',		'0xC0' => '0xCE90',
			'0xC8' => '0xCE98',		'0xCA' => '0xCE9A',		'0xCC' => '0xCE9C',
			'0xCD' => '0xCE9D',		'0xCF' => '0xCE9F',		'0xDA' => '0xCEAA',
			'0xE8' => '0xCEB8',		'0xEA' => '0xCEBA',		'0xEC' => '0xCEBC',
			'0xEE' => '0xCEBE',		'0xEF' => '0xCEBF',		'0xC2' => '0xFF',
			'0xBD' => '0xC2BD',		'0xED' => '0xCEBD',		'0xB2' => '0xC2B2',
			'0xA0' => '0xC2A0',		'0xA3' => '0xC2A3',		'0xA4' => '0xC2A4',
			'0xA5' => '0xC2A5',		'0xA6' => '0xC2A6',		'0xA7' => '0xC2A7',
			'0xA8' => '0xC2A8',		'0xA9' => '0xC2A9',		'0xAB' => '0xC2AB',
			'0xAC' => '0xC2AC',		'0xAD' => '0xC2AD',		'0xAE' => '0xC2AE',
			'0xB0' => '0xC2B0',		'0xB1' => '0xC2B1',		'0xB3' => '0xC2B3',
			'0xB5' => '0xC2B5',		'0xB6' => '0xC2B6',		'0xB7' => '0xC2B7',
			'0xBB' => '0xC2BB',		'0xE2' => '0xCEB2',		'0x80' => '0xD2',
			'0x82' => '0xE2809A',	'0x84' => '0xE2809E',	'0x85' => '0xE280A6',
			'0x86' => '0xE280A0',	'0xA1' => '0xCE85',		'0xA2' => '0xCE86',
			'0x87' => '0xE280A1',	'0x89' => '0xE280B0',	'0xB9' => '0xCE89',
			'0x8B' => '0xE280B9',	'0x91' => '0xE28098',	'0x99' => '0xE284A2',
			'0x92' => '0xE28099',	'0x93' => '0xE2809C',	'0x94' => '0xE2809D',
			'0x95' => '0xE280A2',	'0x96' => '0xE28093',	'0x97' => '0xE28094',
			'0x9B' => '0xE280BA',	'0xAF' => '0xE28095',	'0xB4' => '0xCE84',
			'0xC1' => '0xCE91',		'0xC3' => '0xCE93',		'0xC4' => '0xCE94',
			'0xC5' => '0xCE95',		'0xC6' => '0xCE96',		'0x83' => '0xC692',
			'0xC7' => '0xCE97',		'0xC9' => '0xCE99',		'0xCB' => '0xCE9B',
			'0xD0' => '0xCEA0',		'0xD1' => '0xCEA1',		'0xD3' => '0xCEA3',
			'0xD4' => '0xCEA4',		'0xD5' => '0xCEA5',		'0xD6' => '0xCEA6',
			'0xD7' => '0xCEA7',		'0xD8' => '0xCEA8',		'0xD9' => '0xCEA9',
			'0xDB' => '0xCEAB',		'0xDC' => '0xCEAC',		'0xDD' => '0xCEAD',
			'0xDE' => '0xCEAE',		'0xDF' => '0xCEAF',		'0xE0' => '0xCEB0',
			'0xE1' => '0xCEB1',		'0xE3' => '0xCEB3',		'0xE4' => '0xCEB4',
			'0xE5' => '0xCEB5',		'0xE6' => '0xCEB6',		'0xE7' => '0xCEB7',
			'0xE9' => '0xCEB9',		'0xEB' => '0xCEBB',		'0xF0' => '0xCF80',
			'0xF1' => '0xCF81',		'0xF2' => '0xCF82',		'0xF3' => '0xCF83',
			'0xF4' => '0xCF84',		'0xF5' => '0xCF85',		'0xF6' => '0xCF86',
			'0xF7' => '0xCF87',		'0xF8' => '0xCF88',		'0xF9' => '0xCF89',
			'0xFA' => '0xCF8A',		'0xFB' => '0xCF8B',		'0xFC' => '0xCF8C',
			'0xFD' => '0xCF8D',		'0xFE' => '0xCF8E',		'0xFF' => '0xCE92',
			'0xD2' => '0xE282AC',
		),
	);

	// Make some preparations.
	if (isset($translation_tables[$_POST['src_charset']]))
	{
		$replace = '%field%';
		foreach ($translation_tables[$_POST['src_charset']] as $from => $to)
			$replace = "REPLACE($replace, $from, $to)";
	}

	// Grab a list of tables.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) === 1)
		$queryTables = db_query("
			SHOW TABLE STATUS
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'", __FILE__, __LINE__);
	else
		$queryTables = db_query("
			SHOW TABLE STATUS
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'", __FILE__, __LINE__);

	while ($table_info = mysql_fetch_assoc($queryTables))
	{
		// Just to make sure it doesn't time out.
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		$table_charsets = array();

		// Loop through each column.
		$queryColumns = db_query("
			SHOW FULL COLUMNS 
			FROM $table_info[Name]", __FILE__, __LINE__);
		while ($column_info = mysql_fetch_assoc($queryColumns))
		{
			// Only text'ish columns have a character set and need converting.
			if (strpos($column_info['Type'], 'text') !== false || strpos($column_info['Type'], 'char') !== false)
			{
				$collation = empty($column_info['Collation']) || $column_info['Collation'] === 'NULL' ? $table_info['Collation'] : $column_info['Collation'];
				if (!empty($collation) && $collation !== 'NULL')
				{
					list($charset) = explode('_', $collation);

					if (!isset($table_charsets[$charset]))
						$table_charsets[$charset] = array();

					$table_charsets[$charset][] = $column_info;
				}
			}
		}
		mysql_free_result($queryColumns);

		// Only change the column if the data doesn't match the current charset.
		if ((count($table_charsets) === 1 && key($table_charsets) !== $charsets[$_POST['src_charset']]) || count($table_charsets) > 1)
		{
			$updates_blob = '';
			$updates_text = '';
			foreach ($table_charsets as $charset => $columns)
			{
				if ($charset !== $charsets[$_POST['src_charset']])
				{
					foreach ($columns as $column)
					{
						$updates_blob .= "
							CHANGE COLUMN $column[Field] $column[Field] " . strtr($column['Type'], array('text' => 'blob', 'char' => 'binary')) . ($column['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : " default '$column[Default]'") . ',';
						$updates_text .= "
							CHANGE COLUMN $column[Field] $column[Field] $column[Type] CHARACTER SET " . $charsets[$_POST['src_charset']] . ($column['Null'] === 'YES' ? '' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : " default '$column[Default]'") . ',';
					}
				}
			}

			// Change the columns to binary form.
			db_query("
				ALTER TABLE $table_info[Name]" . substr($updates_blob, 0, -1), __FILE__, __LINE__);

			// Convert the character set if MySQL has no native support for it.
			if (isset($translation_tables[$_POST['src_charset']]))
			{
				$update = '';
				foreach ($table_charsets as $charset => $columns)
					foreach ($columns as $column)
						$update .= "
							$column[Field] = " . strtr($replace, array('%field%' => $column['Field'])) . ',';
				
				db_query("
					UPDATE $table_info[Name]
					SET " . substr($update, 0, -1), __FILE__, __LINE__);
			}

			// Change the columns back, but with the proper character set.
			db_query("
				ALTER TABLE $table_info[Name]" . substr($updates_text, 0, -1), __FILE__, __LINE__);
		}

		// Now do the actual conversion (if still needed).
		if ($charsets[$_POST['src_charset']] !== 'utf8')
			db_query("
				ALTER TABLE $table_info[Name]
				CONVERT TO CHARACTER SET utf8", __FILE__, __LINE__);
	}
	mysql_free_result($queryTables);

	// Let the settings know we have a new character set.
	updateSettings(array('global_character_set' => 'UTF-8'));
	updateSettingsFile(array('db_character_set' => '\'utf8\''));

	// The conversion might have messed up some serialized strings. Fix them!
	require_once($sourcedir . '/Subs-Charset.php');
	fix_serialized_columns();

	redirectExit('action=maintain');
}

// Convert HTML-entities to their UTF-8 character equivalents.
function ConvertEntities()
{
	global $db_prefix, $db_character_set, $modSettings, $context, $sourcedir;

	isAllowedTo('admin_forum');

	// Show the maintenance highlighted on the admin bar.
	adminIndex('maintain_forum');

	// Check to see if UTF-8 is currently the default character set.
	if ($modSettings['global_character_set'] !== 'UTF-8' || !isset($db_character_set) || $db_character_set !== 'utf8')
		fatal_lang_error('entity_convert_only_utf8');

	// Select the sub template from the Admin template.
	$context['sub_template'] = 'convert_entities';

	// Some starting values.
	$context['table'] = empty($_REQUEST['table']) ? 0 : (int) $_REQUEST['table'];
	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];

	$context['start_time'] = time();

	$context['first_step'] = !isset($_REQUEST['sesc']);
	$context['last_step'] = false;

	// The first step is just a text screen with some explanation.
	if ($context['first_step'])
		return;

	// Now we're actually going to convert...
	checkSession('get');

	// A list of tables ready for conversion.
	$tables = array(
		'ban_groups',
		'ban_items',
		'boards',
		'calendar',
		'calendar_holidays',
		'categories',
		'log_errors',
		'log_search_subjects',
		'membergroups',
		'members',
		'message_icons',
		'messages',
		'package_servers',
		'personal_messages',
		'pm_recipients',
		'polls',
		'poll_choices',
		'smileys',
		'themes',
	);
	$context['num_tables'] = count($tables);

	// This function will do the conversion later on.
	$entity_replace = create_function('$string', '
		$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;
		return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : ($num < 0x80 ? \'&#\' . $num . \';\' : ($num < 0x800 ? chr(192 | $num >> 6) . chr(128 | $num & 63) : ($num < 0x10000 ? chr(224 | $num >> 12) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63) : chr(240 | $num >> 18) . chr(128 | $num >> 12 & 63) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63))));');

	// Loop through all tables that need converting.
	for (; $context['table'] < $context['num_tables']; $context['table']++)
	{
		$cur_table = $tables[$context['table']];
		$primary_key = '';

		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		// Get a list of text columns.
		$columns = array();
		$request = db_query("
			SHOW FULL COLUMNS 
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		while ($column_info = mysql_fetch_assoc($request))
			if (strpos($column_info['Type'], 'text') !== false || strpos($column_info['Type'], 'char') !== false)
				$columns[] = $column_info['Field'];

		// Get the column with the (first) primary key.
		$request = db_query("
			SHOW KEYS
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if ($row['Key_name'] === 'PRIMARY' && $row['Seq_in_index'] == 1)
			{
				$primary_key = $row['Column_name'];
				break;
			}
		}
		mysql_free_result($request);

		// No primary key, no glory.
		if (empty($primary_key))
			continue;

		// Get the maximum value for the primary key.
		$request = db_query("
			SELECT MAX($primary_key)
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		list($max_value) = mysql_fetch_row($request);
		mysql_free_result($request);

		if (empty($max_value))
			continue;

		while ($context['start'] <= $max_value)
		{
			// Retrieve a list of rows that has at least one entity to convert.
			$request = db_query("
				SELECT $primary_key, " . implode(', ', $columns) . "
				FROM {$db_prefix}$cur_table
				WHERE $primary_key BETWEEN $context[start] AND $context[start] + 499
					AND (" . implode(" LIKE '%&#%' OR ", $columns) . " LIKE '%&#%')
				LIMIT 500", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				$changes = array();
				foreach ($row as $column_name => $column_value)
					if ($column_name !== $primary_key && strpos($column_value, '&#') !== false)
						$changes[] = "$column_name = '" . addslashes(preg_replace('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$entity_replace(\'\\2\')', $column_value)) . "'";
				
				// Update the row.
				if (!empty($changes))
					db_query("
						UPDATE {$db_prefix}$cur_table
						SET 
							" . implode(",
							", $changes) . "
						WHERE $primary_key = " . $row[$primary_key] . "
						LIMIT 1", __FILE__, __LINE__);
			}
			mysql_free_result($request);
			$context['start'] += 500;

			// After ten seconds interrupt.
			if (time() - $context['start_time'] > 10)
			{
				// Calculate an approximation of the percentage done.
				$context['percent_done'] = round(100 * ($context['table'] + ($context['start'] / $max_value)) / $context['num_tables'], 1);
				$context['continue_get_data'] = '?action=convertentities;table=' . $context['table'] . ';start=' . $context['start'] . ';sesc=' . $context['session_id'];
				return;
			}
		}
		$context['start'] = 0;
	}

	// Make sure all serialized strings are all right.
	require_once($sourcedir . '/Subs-Charset.php');
	fix_serialized_columns();

	// If we're here, we must be done.
	$context['percent_done'] = 100;
	$context['continue_get_data'] = '?action=maintain';
	$context['last_step'] = true;
}

?>