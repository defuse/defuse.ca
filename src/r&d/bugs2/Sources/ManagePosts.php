<?php
/**********************************************************************************
* ManagePosts.php                                                                 *
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

/*	This file contains all the screens that control settings for topics and
	posts.

	void ManagePostSettings()
		- the main entrance point for the 'Posts and topics' screen.
		- accessed from ?action=postsettings.
		- calls the right function based on the given sub-action.
		- defaults to sub-action 'posts'.
		- requires (and checks for) the admin_forum permission.

	void SetCensor()
		- shows an interface to set and test word censoring.
		- requires the moderate_forum permission.
		- uses the Admin template and the edit_censored sub template.
		- tests the censored word if one was posted.
		- uses the censor_vulgar, censor_proper, censorWholeWord, and
		  censorIgnoreCase settings.
		- accessed from ?action=postsettings;sa=censor.

	void ModifyPostSettings()
		- set any setting related to posts and posting.
		- requires the admin_forum permission
		- uses the edit_post_settings sub template of the Admin template.
		- accessed from ?action=postsettings;sa=posts.

	void ModifyBBCSettings()
		- set a few Bulletin Board Code settings.
		- requires the admin_forum permission
		- uses the edit_bbc_settings sub template of the Admin template.
		- accessed from ?action=postsettings;sa=bbc.
		- loads a list of Bulletin Board Code tags to allow disabling tags.

	void ModifyTopicSettings()
		- set any setting related to topics.
		- requires the admin_forum permission
		- uses the edit_topic_settings sub template of the Admin template.
		- accessed from ?action=postsettings;sa=topics.
*/

function ManagePostSettings()
{
	global $context, $txt, $scripturl;

	// Boldify "Posts and Topics" on the admin bar.
	adminIndex('posts_and_topics');

	$subActions = array(
		'posts' => array('ModifyPostSettings', 'admin_forum'),
		'bbc' => array('ModifyBBCSettings', 'admin_forum'),
		'censor' => array('SetCensor', 'moderate_forum'),
		'topics' => array('ModifyTopicSettings', 'admin_forum'),
	);

	// Default the sub-action to 'view ban list'.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('admin_forum') ? 'posts' : 'censor');

	// Make sure you can do this.
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$context['page_title'] = $txt['manageposts_title'];

	// Tabs for browsing the different ban functions.
	$context['admin_tabs'] = array(
		'title' => $txt['manageposts_title'],
		'help' => 'posts_and_topics',
		'description' => $txt['manageposts_description'],
		'tabs' => array()
	);
	if (allowedTo('admin_forum'))
	{
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['manageposts_settings'],
			'description' => $txt['manageposts_settings_description'],
			'href' => $scripturl . '?action=postsettings;sa=posts',
			'is_selected' => $_REQUEST['sa'] == 'posts',
		);
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['manageposts_bbc_settings'],
			'description' => $txt['manageposts_bbc_settings_description'],
			'href' => $scripturl . '?action=postsettings;sa=bbc',
			'is_selected' => $_REQUEST['sa'] == 'bbc',
		);
	}
	if (allowedTo('moderate_forum'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt[135],
			'description' => $txt[141],
			'href' => $scripturl . '?action=postsettings;sa=censor',
			'is_selected' => $_REQUEST['sa'] == 'censor',
			'is_last' => !allowedTo('admin_forum'),
		);
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['manageposts_topic_settings'],
			'description' => $txt['manageposts_topic_settings_description'],
			'href' => $scripturl . '?action=postsettings;sa=topics',
			'is_selected' => $_REQUEST['sa'] == 'topics',
			'is_last' => true,
		);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']][0]();
}

// Set the censored words.
function SetCensor()
{
	global $txt, $modSettings, $context;

	if (!empty($_POST['save_censor']))
	{
		// Make sure censoring is something they can do.
		checkSession();

		$censored_vulgar = array();
		$censored_proper = array();

		// Rip it apart, then split it into two arrays.
		if (isset($_POST['censortext']))
		{
			$_POST['censortext'] = explode("\n", strtr($_POST['censortext'], array("\r" => '')));

			foreach ($_POST['censortext'] as $c)
				list ($censored_vulgar[], $censored_proper[]) = array_pad(explode('=', trim($c)), 2, '');
		}
		elseif (isset($_POST['censor_vulgar'], $_POST['censor_proper']))
		{
			if (is_array($_POST['censor_vulgar']))
			{
				foreach ($_POST['censor_vulgar'] as $i => $value)
					if ($value == '')
					{
						unset($_POST['censor_vulgar'][$i]);
						unset($_POST['censor_proper'][$i]);
					}

				$censored_vulgar = $_POST['censor_vulgar'];
				$censored_proper = $_POST['censor_proper'];
			}
			else
			{
				$censored_vulgar = explode("\n", strtr($_POST['censor_vulgar'], array("\r" => '')));
				$censored_proper = explode("\n", strtr($_POST['censor_proper'], array("\r" => '')));
			}
		}

		// Set the new arrays and settings in the database.
		$updates = array(
			'censor_vulgar' => implode("\n", $censored_vulgar),
			'censor_proper' => implode("\n", $censored_proper),
			'censorWholeWord' => empty($_POST['censorWholeWord']) ? '0' : '1',
			'censorIgnoreCase' => empty($_POST['censorIgnoreCase']) ? '0' : '1',
		);

		updateSettings($updates);
	}

	if (isset($_POST['censortest']))
	{
		$censorText = htmlspecialchars(stripslashes($_POST['censortest']), ENT_QUOTES);
		$context['censor_test'] = strtr(censorText($censorText), array('"' => '&quot;'));
	}

	// Set everything up for the template to do its thang.
	$censor_vulgar = explode("\n", $modSettings['censor_vulgar']);
	$censor_proper = explode("\n", $modSettings['censor_proper']);

	$context['censored_words'] = array();
	for ($i = 0, $n = count($censor_vulgar); $i < $n; $i++)
	{
		if (empty($censor_vulgar[$i]))
			continue;

		// Skip it, it's either spaces or stars only.
		if (trim(strtr($censor_vulgar[$i], '*', ' ')) == '')
			continue;

		$context['censored_words'][htmlspecialchars(trim($censor_vulgar[$i]))] = isset($censor_proper[$i]) ? htmlspecialchars($censor_proper[$i]) : '';
	}

	$context['sub_template'] = 'edit_censored';
	$context['page_title'] = $txt[135];
}

// Modify all settings related to posts and posting.
function ModifyPostSettings()
{
	global $context, $txt, $db_prefix, $modSettings;

	// Setup the template.
	$context['sub_template'] = 'edit_post_settings';
	$context['page_title'] = $txt['manageposts_settings'];

	// Saving?
	if (isset($_POST['save_settings']))
	{
		checkSession();

		// Let's find out if they want things way too long...
		if (!empty($_POST['max_messageLength']) && $_POST['max_messageLength'] != $modSettings['max_messageLength'])
		{
			$request = db_query("
				SHOW COLUMNS
				FROM {$db_prefix}messages", false, false);
			if ($request !== false)
			{
				while ($row = mysql_fetch_assoc($request))
					if ($row['Field'] == 'body')
						$body_type = $row['Type'];
				mysql_free_result($request);
			}

			$request = db_query("
				SHOW INDEX
				FROM {$db_prefix}messages", false, false);
			if ($request !== false)
			{
				while ($row = mysql_fetch_assoc($request))
					if ($row['Column_name'] == 'body' && (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT' || isset($row['Comment']) && $row['Comment'] == 'FULLTEXT'))
						$fulltext = true;
				mysql_free_result($request);
			}

			if (isset($body_type) && $_POST['max_messageLength'] > 65535 && $body_type == 'text')
			{
				// !!! Show an error message?!
				// MySQL only likes fulltext indexes on text columns... for now?
				if (!empty($fulltext))
					$_POST['max_messageLength'] = 65535;
				else
				{
					// Make it longer so we can do their limit.
					db_query("
						ALTER TABLE {$db_prefix}messages
						CHANGE COLUMN body body mediumtext", __FILE__, __LINE__);
				}
			}
			elseif (isset($body_type) && $_POST['max_messageLength'] <= 65535 && $body_type != 'text')
			{
				// Shorten the column so we can have the benefit of fulltext searching again!
				db_query("
					ALTER TABLE {$db_prefix}messages
					CHANGE COLUMN body body text", __FILE__, __LINE__);
			}
		}

		// Update the actual settings.
		updateSettings(array(
			'removeNestedQuotes' => empty($_POST['removeNestedQuotes']) ? '0' : '1',
			'enableEmbeddedFlash' => empty($_POST['enableEmbeddedFlash']) ? '0' : '1',
			'enableSpellChecking' => empty($_POST['enableSpellChecking']) ? '0' : '1',
			'max_messageLength' => empty($_POST['max_messageLength']) ? '0' : (int) $_POST['max_messageLength'],
			'fixLongWords' => empty($_POST['fixLongWords']) ? '0' : (int) $_POST['fixLongWords'],
			'topicSummaryPosts' => empty($_POST['topicSummaryPosts']) ? '0' : (int) $_POST['topicSummaryPosts'],
			'spamWaitTime' => empty($_POST['spamWaitTime']) ? '0' : (int) $_POST['spamWaitTime'],
			'edit_wait_time' => empty($_POST['edit_wait_time']) ? '0' : (int) $_POST['edit_wait_time'],
			'edit_disable_time' => empty($_POST['edit_disable_time']) ? '0' : (int) $_POST['edit_disable_time'],
		));
	}

	// Check if your PHP is able to use spell checking.
	$context['spellcheck_installed'] = function_exists('pspell_new');
}

// Bulletin Board Code...a lot of Bulletin Board Code.
function ModifyBBCSettings()
{
	global $context, $txt, $modSettings, $helptxt;

	// Setup the template.
	$context['sub_template'] = 'edit_bbc_settings';
	$context['page_title'] = $txt['manageposts_bbc_settings_title'];

	// Ask parse_bbc() for its bbc code list.
	$temp = parse_bbc(false);
	$bbcTags = array();
	foreach ($temp as $tag)
		$bbcTags[] = $tag['tag'];

	$bbcTags = array_unique($bbcTags);
	$totalTags = count($bbcTags);

	// The number of columns we want to show the BBC tags in.
	$numColumns = 3;

	// In case we're saving.
	if (isset($_POST['save_settings']))
	{
		checkSession();

		if (!isset($_POST['enabledTags']))
			$_POST['enabledTags'] = array();
		elseif (!is_array($_POST['enabledTags']))
			$_POST['enabledTags'] = array($_POST['enabledTags']);

		// Update the actual settings.
		updateSettings(array(
			'enableBBC' => empty($_POST['enableBBC']) ? '0' : '1',
			'enablePostHTML' => empty($_POST['enablePostHTML']) ? '0' : '1',
			'autoLinkUrls'  => empty($_POST['autoLinkUrls']) ? '0' : '1',
			'disabledBBC' => implode(',', array_diff($bbcTags, $_POST['enabledTags'])),
		));
	}

	$context['bbc_columns'] = array();
	$tagsPerColumn = ceil($totalTags / $numColumns);
	$disabledTags = empty($modSettings['disabledBBC']) ? array() : explode(',', $modSettings['disabledBBC']);

	$col = 0;
	$i = 0;
	foreach ($bbcTags as $tag)
	{
		if ($i % $tagsPerColumn == 0 && $i != 0)
			$col++;

		$context['bbc_columns'][$col][] = array(
			'tag' => $tag,
			'is_enabled' => !in_array($tag, $disabledTags),
			// !!! 'tag_' . ?
			'show_help' => isset($helptxt[$tag]),
		);

		$i++;
	}

	$context['bbc_all_selected'] = empty($disabledTags);
}

// Function for modifying topic settings. Not very exciting.
function ModifyTopicSettings()
{
	global $context, $txt, $modSettings;

	// Setup the template.
	$context['sub_template'] = 'edit_topic_settings';
	$context['page_title'] = $txt['manageposts_topic_settings'];

	// Wanna save this page?
	if (isset($_POST['save_settings']))
	{
		checkSession();

		// Update the actual settings.
		updateSettings(array(
			'enableStickyTopics' => empty($_POST['enableStickyTopics']) ? '0' : '1',
			'enableParticipation' => empty($_POST['enableParticipation']) ? '0' : '1',
			'oldTopicDays' => empty($_POST['oldTopicDays']) ? '0' : (int) $_POST['oldTopicDays'],
			'defaultMaxTopics' => empty($_POST['defaultMaxTopics']) ? '0' : (int) $_POST['defaultMaxTopics'],
			'defaultMaxMessages' => empty($_POST['defaultMaxMessages']) ? '0' : (int) $_POST['defaultMaxMessages'],
			'hotTopicPosts' => empty($_POST['hotTopicPosts']) ? '0' : (int) $_POST['hotTopicPosts'],
			'hotTopicVeryPosts' => empty($_POST['hotTopicVeryPosts']) ? '0' : (int) $_POST['hotTopicVeryPosts'],
			'enableAllMessages' => empty($_POST['enableAllMessages']) ? '0' : (int) $_POST['enableAllMessages'],
			'enablePreviousNext' => empty($_POST['enablePreviousNext']) ? '0' : '1',
		));
	}
}

?>