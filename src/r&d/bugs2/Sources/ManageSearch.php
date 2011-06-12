<?php
/**********************************************************************************
* ManageSearch.php                                                                *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.12                                           *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

/* The admin screen to change the search settings.

	void ManageSearch()
		- main entry point for the admin search settings screen.
		- called by ?action=managesearch.
		- requires the admin_forum permission.
		- loads the ManageSearch template.
		- loads the Search language file.
		- calls a function based on the given sub-action.
		- defaults to sub-action 'settings'.

	void EditSearchSettings()
		- edit some general settings related to the search function.
		- called by ?action=managesearch;sa=settings.
		- requires the admin_forum permission.
		- uses the 'modify_settings' sub template of the ManageSearch template.

	void EditWeights()
		- edit the relative weight of the search factors.
		- called by ?action=managesearch;sa=weights.
		- requires the admin_forum permission.
		- uses the 'modify_weights' sub template of the ManageSearch template.

	void EditSearchMethod()
		- edit the search method and search index used.
		- called by ?action=managesearch;sa=method.
		- requires the admin_forum permission.
		- uses the 'select_search_method' sub template of the ManageSearch 
		  template.
		- allows to create and delete a fulltext index on the messages table.
		- allows to delete a custom index (that CreateMessageIndex() created).
		- calculates the size of the current search indexes in use.

	void CreateMessageIndex()
		- create a custom search index for the messages table.
		- called by ?action=managesearch;sa=createmsgindex.
		- linked from the EditSearchMethod screen.
		- requires the admin_forum permission.
		- uses the 'create_index', 'create_index_progress', and 
		  'create_index_done' sub templates of the ManageSearch template.
		- depending on the size of the message table, the process is divided 
		  in steps.
*/

function ManageSearch()
{
	global $context, $txt, $scripturl;

	isAllowedTo('admin_forum');
	adminIndex('manage_search');

	loadLanguage('Search');
	loadTemplate('ManageSearch');

	$subActions = array(
		'settings' => 'EditSearchSettings',
		'weights' => 'EditWeights',
		'method' => 'EditSearchMethod',
		'createfulltext' => 'EditSearchMethod',
		'removecustom' => 'EditSearchMethod',
		'removefulltext' => 'EditSearchMethod',
		'createmsgindex' => 'CreateMessageIndex',
	);

	// Default the sub-action to 'edit search settings'.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'settings';

	$context['sub_action'] = $_REQUEST['sa'];

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => &$txt['manage_search'],
		'help' => 'search',
		'description' => $txt['search_settings_desc'],
		'tabs' => array(
			'weights' => array(
				'title' => $txt['search_weights'],
				'description' => $txt['search_weights_desc'],
				'href' => $scripturl . '?action=managesearch;sa=weights',
			),
			'method' => array(
				'title' => $txt['search_method'],
				'description' => $txt['search_method_desc'],
				'href' => $scripturl . '?action=managesearch;sa=method',
			),
			'settings' => array(
				'title' => $txt['settings'],
				'description' => $txt['search_settings_desc'],
				'href' => $scripturl . '?action=managesearch;sa=settings',
				'is_last' => true,
			),
		),
	);

	// Make sure the tab they are using has is_selected set.
	if (isset($context['admin_tabs']['tabs'][$_REQUEST['sa']]))
		$context['admin_tabs']['tabs'][$_REQUEST['sa']]['is_selected'] = true;

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

function EditSearchSettings()
{
	global $txt, $context, $sourcedir;

	$context['page_title'] = $txt['search_settings_title'];
	$context['sub_template'] = 'modify_settings';

	// Including a file needed for inline permissions.
	require_once($sourcedir . '/ManagePermissions.php');

	// A form was submitted.
	if (isset($_POST['save']))
	{
		checkSession();

		updateSettings(array(
			'simpleSearch' => isset($_POST['simpleSearch']) ? '1' : '0',
			'search_results_per_page' => (int) $_POST['search_results_per_page'],
			'search_max_results' => (int) $_POST['search_max_results'],
		));

		// Save the permissions.
		save_inline_permissions(array('search_posts'));
	}

	// Initialize permissions.
	init_inline_permissions(array('search_posts'));
}

function EditWeights()
{
	global $txt, $context, $modSettings;

	$context['page_title'] = $txt['search_weights_title'];
	$context['sub_template'] = 'modify_weights';

	$factors = array(
		'search_weight_frequency',
		'search_weight_age',
		'search_weight_length',
		'search_weight_subject',
		'search_weight_first_message',
		'search_weight_sticky',
	);

	// A form was submitted.
	if (isset($_POST['save']))
	{
		checkSession();

		$changes = array();
		foreach ($factors as $factor)
			$changes[$factor] = (int) $_POST[$factor];
		updateSettings($changes);
	}

	$context['relative_weights'] = array('total' => 0);
	foreach ($factors as $factor)
		$context['relative_weights']['total'] += isset($modSettings[$factor]) ? $modSettings[$factor] : 0;

	foreach ($factors as $factor)
		$context['relative_weights'][$factor] = round(100 * (isset($modSettings[$factor]) ? $modSettings[$factor] : 0) / $context['relative_weights']['total'], 1);
}

function EditSearchMethod()
{
	global $txt, $context, $modSettings, $db_prefix;

	$context['admin_tabs']['tabs']['method']['is_selected'] = true;
	$context['page_title'] = $txt['search_method_title'];
	$context['sub_template'] = 'select_search_method';

	// Detect whether a fulltext index is set.
	$request = db_query("
		SHOW INDEX
		FROM {$db_prefix}messages", false, false);
	$context['fulltext_index'] = '';
	if ($request !== false || mysql_num_rows($request) != 0)
	{
		while ($row = mysql_fetch_assoc($request))
			if ($row['Column_name'] == 'body' && (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT' || isset($row['Comment']) && $row['Comment'] == 'FULLTEXT'))
				$context['fulltext_index'][] = $row['Key_name'];
		mysql_free_result($request);

		if (is_array($context['fulltext_index']))
			$context['fulltext_index'] = array_unique($context['fulltext_index']);
	}

	$request = db_query("
		SHOW COLUMNS
		FROM {$db_prefix}messages", false, false);
	if ($request !== false)
	{
		while ($row = mysql_fetch_assoc($request))
			if ($row['Field'] == 'body' && $row['Type'] == 'mediumtext')
				$context['cannot_create_fulltext'] = true;
		mysql_free_result($request);
	}

	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) !== 0)
		$request = db_query("
			SHOW TABLE STATUS
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "messages'", false, false);
	else
		$request = db_query("
			SHOW TABLE STATUS
			LIKE '" . str_replace('_', '\_', $db_prefix) . "messages'", false, false);

	if ($request !== false)
	{
		while ($row = mysql_fetch_assoc($request))
			if ((isset($row['Type']) && strtolower($row['Type']) != 'myisam') || (isset($row['Engine']) && strtolower($row['Engine']) != 'myisam'))
				$context['cannot_create_fulltext'] = true;
		mysql_free_result($request);
	}

	if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'createfulltext')
	{
		checkSession('get');

		// Make sure it's gone before creating it.
		db_query("
			ALTER TABLE {$db_prefix}messages
			DROP INDEX body", false, false);

		db_query("
			ALTER TABLE {$db_prefix}messages
			ADD FULLTEXT body (body)", __FILE__, __LINE__);

		$context['fulltext_index'] = 'body';
	}
	elseif (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'removefulltext' && !empty($context['fulltext_index']))
	{
		checkSession('get');

		db_query("
			ALTER TABLE {$db_prefix}messages
			DROP INDEX " . implode(',
			DROP INDEX ', $context['fulltext_index']), __FILE__, __LINE__);

		$context['fulltext_index'] = '';

		// Go back to the default search method.
		if (!empty($modSettings['search_index']) && $modSettings['search_index'] == 'fulltext')
			updateSettings(array(
				'search_index' => '',
			));
	}
	elseif (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'removecustom')
	{
		checkSession('get');

		db_query("
			DROP TABLE IF EXISTS {$db_prefix}log_search_words", __FILE__, __LINE__);

		updateSettings(array(
			'search_custom_index_config' => '',
		));

		// Go back to the default search method.
		if (!empty($modSettings['search_index']) && $modSettings['search_index'] == 'custom')
			updateSettings(array(
				'search_index' => '',
			));
	}
	elseif (isset($_POST['save']))
	{
		checkSession();
		updateSettings(array(
			'search_index' => empty($_POST['search_index']) || !in_array($_POST['search_index'], array('fulltext', 'custom')) ? '' : $_POST['search_index'],
			'search_force_index' => isset($_POST['search_force_index']) ? '1' : '0',
			'search_match_words' => isset($_POST['search_match_words']) ? '1' : '0',
		));
	}

	$context['table_info'] = array(
		'data_length' => 0,
		'index_length' => 0,
		'fulltext_length' => 0,
		'custom_index_length' => 0,
	);

	// Get some info about the messages table, to show its size and index size.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
		$request = db_query("
			SHOW TABLE STATUS
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "messages'", false, false);
	else
		$request = db_query("
			SHOW TABLE STATUS
			LIKE '" . str_replace('_', '\_', $db_prefix) . "messages'", false, false);
	if ($request !== false && mysql_num_rows($request) == 1)
	{
		// Only do this if the user has permission to execute this query.
		$row = mysql_fetch_assoc($request);
		$context['table_info']['data_length'] = $row['Data_length'];
		$context['table_info']['index_length'] = $row['Index_length'];
		$context['table_info']['fulltext_length'] = $row['Index_length'];
		mysql_free_result($request);
	}

	// Now check the custom index table, if it exists at all.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) !== 0)
		$request = db_query("
			SHOW TABLE STATUS
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "log_search_words'", false, false);
	else
		$request = db_query("
			SHOW TABLE STATUS
			LIKE '" . str_replace('_', '\_', $db_prefix) . "log_search_words'", false, false);
	if ($request !== false && mysql_num_rows($request) == 1)
	{
		// Only do this if the user has permission to execute this query.
		$row = mysql_fetch_assoc($request);
		$context['table_info']['index_length'] += $row['Data_length'] + $row['Index_length'];
		$context['table_info']['custom_index_length'] = $row['Data_length'] + $row['Index_length'];
		mysql_free_result($request);
	}

	// Format the data and index length in kilobytes.
	foreach ($context['table_info'] as $type => $size)
		$context['table_info'][$type] = comma_format($context['table_info'][$type] / 1024);

	$context['custom_index'] = !empty($modSettings['search_custom_index_config']);
	$context['partial_custom_index'] = !empty($modSettings['search_custom_index_resume']) && empty($modSettings['search_custom_index_config']);
	$context['double_index'] = !empty($context['fulltext_index']) && $context['custom_index'];
}

function CreateMessageIndex()
{
	global $modSettings, $context, $db_prefix;

	$context['admin_tabs']['tabs']['method']['is_selected'] = true;

	$messages_per_batch = 100;

	$index_properties = array(
		2 => array(
			'column_definition' => 'smallint(5)',
		),
		4 => array(
			'column_definition' => 'mediumint(8)',
			'step_size' => 1000000,
			'max_size' => 16777215,
		),
		5 => array(
			'column_definition' => 'int(10)',
			'step_size' => 100000000,
			'max_size' => 4294967295,
		),
	);

	if (isset($_REQUEST['resume']) && !empty($modSettings['search_custom_index_resume']))
	{
		$context['index_settings'] = unserialize($modSettings['search_custom_index_resume']);
		$context['start'] = (int) $context['index_settings']['resume_at'];
		unset($context['index_settings']['resume_at']);
		$context['step'] = 1;
	}
	else
	{
		$context['index_settings'] = array(
			'bytes_per_word' => isset($_REQUEST['bytes_per_word']) && isset($index_properties[$_REQUEST['bytes_per_word']]) ? (int) $_REQUEST['bytes_per_word'] : 2,
		);
		$context['start'] = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
		$context['step'] = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0;
	}

	if ($context['step'] !== 0)
		checkSession('request');

	// Step 0: let the user determine how they like their index.
	if ($context['step'] === 0)
	{
		$context['sub_template'] = 'create_index';
	}

	// Step 1: insert all the words.
	if ($context['step'] === 1)
	{
		$context['sub_template'] = 'create_index_progress';

		if ($context['start'] === 0)
		{
			db_query("
				DROP TABLE IF EXISTS {$db_prefix}log_search_words", __FILE__, __LINE__);

			// MySQL users below 4.0 can not use Engine
			if (version_compare('4', preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
				$schema_type = 'TYPE=';
			else
				$schema_type = 'ENGINE=';

			db_query("
				CREATE TABLE {$db_prefix}log_search_words (
					ID_WORD " . $index_properties[$context['index_settings']['bytes_per_word']]['column_definition'] . " unsigned NOT NULL default '0',
					ID_MSG int(10) unsigned NOT NULL default '0',
					PRIMARY KEY (ID_WORD, ID_MSG)
				) " . $schema_type . "MyISAM", __FILE__, __LINE__);

			// Temporarily switch back to not using a search index.
			if (!empty($modSettings['search_index']) && $modSettings['search_index'] == 'custom')
				updateSettings(array('search_index' => ''));

			// Don't let simultanious processes be updating the search index.
			if (!empty($modSettings['search_custom_index_config']))
				updateSettings(array('search_custom_index_config' => ''));
		}

		$num_messages = array(
			'done' => 0,
			'todo' => 0,
		);

		$request = db_query("
			SELECT ID_MSG >= $context[start] AS todo, COUNT(*) AS numMesages
			FROM {$db_prefix}messages
			GROUP BY todo", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$num_messages[empty($row['todo']) ? 'done' : 'todo'] = $row['numMesages'];

		if (empty($num_messages['todo']))
		{
			$context['step'] = 2;
			$context['percentage'] = 80;
			$context['start'] = 0;
		}
		else
		{
			// Number of seconds before the next step.
			$stop = time() + 3;
			while (time() < $stop)
			{
				$inserts = '';
				$request = db_query("
					SELECT ID_MSG, body
					FROM {$db_prefix}messages
					WHERE ID_MSG BETWEEN $context[start] AND " . ($context['start'] + $messages_per_batch - 1) . "
					LIMIT $messages_per_batch", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
					foreach (text2words($row['body'], $context['index_settings']['bytes_per_word'], true) as $ID_WORD)
						$inserts .= "($ID_WORD, $row[ID_MSG]),\n";
				$num_messages['done'] += mysql_num_rows($request);
				$num_messages['todo'] -= mysql_num_rows($request);
				mysql_free_result($request);

				$context['start'] += $messages_per_batch;

				if (!empty($inserts))
					db_query("
						INSERT IGNORE INTO {$db_prefix}log_search_words
							(ID_WORD, ID_MSG)
						VALUES
							" . substr($inserts, 0, -2), __FILE__, __LINE__);
				if ($num_messages['todo'] === 0)
				{
					$context['step'] = 2;
					$context['start'] = 0;
					break;
				}
				else
					updateSettings(array('search_custom_index_resume' => serialize(array_merge($context['index_settings'], array('resume_at' => $context['start'])))));
			}

			// Since there are still two steps to go, 90% is the maximum here.
			$context['percentage'] = round($num_messages['done'] / ($num_messages['done'] + $num_messages['todo']), 3) * 80;
		}
	}

	// Step 2: removing the words that occur too often and are of no use.
	elseif ($context['step'] === 2)
	{
		if ($context['index_settings']['bytes_per_word'] < 4)
			$context['step'] = 3;
		else
		{
			$stop_words = $context['start'] === 0 || empty($modSettings['search_stopwords']) ? array() : explode(',', $modSettings['search_stopwords']);
			$stop = time() + 3;
			$context['sub_template'] = 'create_index_progress';
			$maxMessages = ceil(60 * $modSettings['totalMessages'] / 100);

			while (time() < $stop)
			{
				$request = db_query("
					SELECT ID_WORD, count(ID_WORD) AS numWords
					FROM {$db_prefix}log_search_words
					WHERE ID_WORD BETWEEN $context[start] AND " . ($context['start'] + $index_properties[$context['index_settings']['bytes_per_word']]['step_size'] - 1) . "
					GROUP BY ID_WORD
					HAVING numWords > $maxMessages", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
					$stop_words[] = $row['ID_WORD'];
				mysql_free_result($request);

				updateSettings(array('search_stopwords' => implode(',', $stop_words)));

				if (!empty($stop_words))
					db_query("
						DELETE FROM {$db_prefix}log_search_words
						WHERE ID_WORD in (" . implode(', ', $stop_words) . ')', __FILE__, __LINE__);

				$context['start'] += $index_properties[$context['index_settings']['bytes_per_word']]['step_size'];
				if ($context['start'] > $index_properties[$context['index_settings']['bytes_per_word']]['max_size'])
				{
					$context['step'] = 3;
					break;
				}
			}
			$context['percentage'] = 80 + round($context['start'] / $index_properties[$context['index_settings']['bytes_per_word']]['max_size'], 3) * 20;
		}
	}

	// Step 3: remove words not distinctive enough.
	if ($context['step'] === 3)
	{
		$context['sub_template'] = 'create_index_done';

		updateSettings(array('search_index' => 'custom', 'search_custom_index_config' => serialize($context['index_settings'])));
		db_query("
			DELETE FROM {$db_prefix}settings
			WHERE variable = 'search_custom_index_resume'", __FILE__, __LINE__);
	}
}

?>