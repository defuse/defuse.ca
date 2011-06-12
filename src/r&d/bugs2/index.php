<?php
/**********************************************************************************
* index.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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


/*	This, as you have probably guessed, is the crux on which SMF functions.
	Everything should start here, so all the setup and security is done
	properly.  The most interesting part of this file is the action array in
	the smf_main() function.  It is formatted as so:

		'action-in-url' => array('Source-File.php', 'FunctionToCall'),

	Then, you can access the FunctionToCall() function from Source-File.php
	with the URL index.php?action=action-in-url.  Relatively simple, no?
*/

$forum_version = 'SMF 1.1.13';

// Get everything started up...
define('SMF', 1);
@set_magic_quotes_runtime(0);
error_reporting(E_ALL);
$time_start = microtime();

// Make sure some things simply do not exist.
foreach (array('db_character_set') as $variable)
	if (isset($GLOBALS[$variable]))
		unset($GLOBALS[$variable]);

// Load the settings...
require_once(dirname(__FILE__) . '/Settings.php');

// And important includes.
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Load.php');
require_once($sourcedir . '/Security.php');

// Using an old version of PHP?
if (@version_compare(PHP_VERSION, '4.2.3') != 1)
	require_once($sourcedir . '/Subs-Compat.php');

// If $maintenance is set specifically to 2, then we're upgrading or something.
if (!empty($maintenance) && $maintenance == 2)
	db_fatal_error();

// Connect to the MySQL database.
if (empty($db_persist))
	$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
else
	$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

// Show an error if the connection couldn't be made.
if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
	db_fatal_error();

// Load the settings from the settings table, and perform operations like optimizing.
reloadSettings();
// Clean the request variables, add slashes, etc.
cleanRequest();
$context = array();

// Seed the random generator?
if (empty($modSettings['rand_seed']) || mt_rand(1, 250) == 69)
	smf_seed_generator();

// Determine if this is using WAP, WAP2, or imode.  Technically, we should check that wap comes before application/xhtml or text/html, but this doesn't work in practice as much as it should.
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false)
	$_REQUEST['wap2'] = 1;
elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false)
{
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'DoCoMo/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'portalmmm/') !== false)
		$_REQUEST['imode'] = 1;
	else
		$_REQUEST['wap'] = 1;
}

if (!defined('WIRELESS'))
	define('WIRELESS', isset($_REQUEST['wap']) || isset($_REQUEST['wap2']) || isset($_REQUEST['imode']));

// Some settings and headers are different for wireless protocols.
if (WIRELESS)
{
	define('WIRELESS_PROTOCOL', isset($_REQUEST['wap']) ? 'wap' : (isset($_REQUEST['wap2']) ? 'wap2' : (isset($_REQUEST['imode']) ? 'imode' : '')));

	// Some cellphones can't handle output compression...
	$modSettings['enableCompressedOutput'] = '0';
	// !!! Do we want these hard coded?
	$modSettings['defaultMaxMessages'] = 5;
	$modSettings['defaultMaxTopics'] = 9;

	// Wireless protocol header.
	if (WIRELESS_PROTOCOL == 'wap')
		header('Content-Type: text/vnd.wap.wml');
}

// Check if compressed output is enabled, supported, and not already being done.
if (!empty($modSettings['enableCompressedOutput']) && !headers_sent() && ob_get_length() == 0)
{
	// If zlib is being used, turn off output compression.
	if (@ini_get('zlib.output_compression') == '1' || @ini_get('output_handler') == 'ob_gzhandler' || @version_compare(PHP_VERSION, '4.2.0') == -1)
		$modSettings['enableCompressedOutput'] = '0';
	else
		ob_start('ob_gzhandler');
}
// This makes it so headers can be sent!
if (empty($modSettings['enableCompressedOutput']))
	ob_start();

// Register an error handler.
set_error_handler('error_handler');

// Start the session. (assuming it hasn't already been.)
loadSession();

// What function shall we execute? (done like this for memory's sake.)
call_user_func(smf_main());

// Call obExit specially; we're coming from the main area ;).
obExit(null, null, true);

// The main controlling function.
function smf_main()
{
	global $modSettings, $settings, $user_info, $board, $topic, $maintenance, $sourcedir;

	// Special case: session keep-alive.
	if (isset($_GET['action']) && $_GET['action'] == 'keepalive')
		die;

	// Load the user's cookie (or set as guest) and load their settings.
	loadUserSettings();

	// Load the current board's information.
	loadBoard();

	// Load the current theme.  (note that ?theme=1 will also work, may be used for guest theming.)
	loadTheme();

	// Check if the user should be disallowed access.
	is_not_banned();

	// Load the current user's permissions.
	loadPermissions();

	// Do some logging, unless this is an attachment, avatar, theme option or XML feed.
	if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], array('dlattach', 'jsoption', '.xml')))
	{
		// Log this user as online.
		writeLog();

		// Track forum statistics and hits...?
		if (!empty($modSettings['hitStats']))
			trackStats(array('hits' => '+'));
	}

	// Is the forum in maintenance mode? (doesn't apply to administrators.)
	if (!empty($maintenance) && !allowedTo('admin_forum'))
	{
		// You can only login.... otherwise, you're getting the "maintenance mode" display.
		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'login2' || $_REQUEST['action'] == 'logout'))
		{
			require_once($sourcedir . '/LogInOut.php');
			return $_REQUEST['action'] == 'login2' ? 'Login2' : 'Logout';
		}
		// Don't even try it, sonny.
		else
		{
			require_once($sourcedir . '/Subs-Auth.php');
			return 'InMaintenance';
		}
	}
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', 'verificationcode'))))
	{
		require_once($sourcedir . '/Subs-Auth.php');
		return 'KickGuest';
	}
	elseif (empty($_REQUEST['action']))
	{
		// Action and board are both empty... BoardIndex!
		if (empty($board) && empty($topic))
		{
			require_once($sourcedir . '/BoardIndex.php');
			return 'BoardIndex';
		}
		// Topic is empty, and action is empty.... MessageIndex!
		elseif (empty($topic))
		{
			require_once($sourcedir . '/MessageIndex.php');
			return 'MessageIndex';
		}
		// Board is not empty... topic is not empty... action is empty.. Display!
		else
		{
			require_once($sourcedir . '/Display.php');
			return 'Display';
		}
	}

	// Here's the monstrous $_REQUEST['action'] array - $_REQUEST['action'] => array($file, $function).
	$actionArray = array(
		'activate' => array('Register.php', 'Activate'),
		'admin' => array('Admin.php', 'Admin'),
		'announce' => array('Post.php', 'AnnounceTopic'),
		'ban' => array('ManageBans.php', 'Ban'),
		'boardrecount' => array('Admin.php', 'AdminBoardRecount'),
		'buddy' => array('Subs-Members.php', 'BuddyListToggle'),
		'calendar' => array('Calendar.php', 'CalendarMain'),
		'cleanperms' => array('Admin.php', 'CleanupPermissions'),
		'collapse' => array('Subs-Boards.php', 'CollapseCategory'),
		'convertentities' => array('Admin.php', 'ConvertEntities'),
		'convertutf8' => array('Admin.php', 'ConvertUtf8'),
		'coppa' => array('Register.php', 'CoppaForm'),
		'deletemsg' => array('RemoveTopic.php', 'DeleteMessage'),
		'detailedversion' => array('Admin.php', 'VersionDetail'),
		'display' => array('Display.php', 'Display'),
		'dlattach' => array('Display.php', 'Download'),
		'dumpdb' => array('DumpDatabase.php', 'DumpDatabase2'),
		'editpoll' => array('Poll.php', 'EditPoll'),
		'editpoll2' => array('Poll.php', 'EditPoll2'),
		'featuresettings' => array('ModSettings.php', 'ModifyFeatureSettings'),
		'featuresettings2' => array('ModSettings.php', 'ModifyFeatureSettings2'),
		'findmember' => array('Subs-Auth.php', 'JSMembers'),
		'help' => array('Help.php', 'ShowHelp'),
		'helpadmin' => array('Help.php', 'ShowAdminHelp'),
		'im' => array('PersonalMessage.php', 'MessageMain'),
		'jsoption' => array('Themes.php', 'SetJavaScript'),
		'jsmodify' => array('Post.php', 'JavaScriptModify'),
		'lock' => array('LockTopic.php', 'LockTopic'),
		'lockVoting' => array('Poll.php', 'LockVoting'),
		'login' => array('LogInOut.php', 'Login'),
		'login2' => array('LogInOut.php', 'Login2'),
		'logout' => array('LogInOut.php', 'Logout'),
		'maintain' => array('Admin.php', 'Maintenance'),
		'manageattachments' => array('ManageAttachments.php', 'ManageAttachments'),
		'manageboards' => array('ManageBoards.php', 'ManageBoards'),
		'managecalendar' => array('ManageCalendar.php', 'ManageCalendar'),
		'managesearch' => array('ManageSearch.php', 'ManageSearch'),
		'markasread' => array('Subs-Boards.php', 'MarkRead'),
		'membergroups' => array('ManageMembergroups.php', 'ModifyMembergroups'),
		'mergetopics' => array('SplitTopics.php', 'MergeTopics'),
		'mlist' => array('Memberlist.php', 'Memberlist'),
		'modifycat' => array('ManageBoards.php', 'ModifyCat'),
		'modifykarma' => array('Karma.php', 'ModifyKarma'),
		'modlog' => array('Modlog.php', 'ViewModlog'),
		'movetopic' => array('MoveTopic.php', 'MoveTopic'),
		'movetopic2' => array('MoveTopic.php', 'MoveTopic2'),
		'news' => array('ManageNews.php', 'ManageNews'),
		'notify' => array('Notify.php', 'Notify'),
		'notifyboard' => array('Notify.php', 'BoardNotify'),
		'optimizetables' => array('Admin.php', 'OptimizeTables'),
		'packageget' => array('PackageGet.php', 'PackageGet'),
		'packages' => array('Packages.php', 'Packages'),
		'permissions' => array('ManagePermissions.php', 'ModifyPermissions'),
		'pgdownload' => array('PackageGet.php', 'PackageGet'),
		'pm' => array('PersonalMessage.php', 'MessageMain'),
		'post' => array('Post.php', 'Post'),
		'post2' => array('Post.php', 'Post2'),
		'postsettings' => array('ManagePosts.php', 'ManagePostSettings'),
		'printpage' => array('Printpage.php', 'PrintTopic'),
		'profile' => array('Profile.php', 'ModifyProfile'),
		'profile2' => array('Profile.php', 'ModifyProfile2'),
		'quotefast' => array('Post.php', 'QuoteFast'),
		'quickmod' => array('Subs-Boards.php', 'QuickModeration'),
		'quickmod2' => array('Subs-Boards.php', 'QuickModeration2'),
		'recent' => array('Recent.php', 'RecentPosts'),
		'regcenter' => array('ManageRegistration.php', 'RegCenter'),
		'register' => array('Register.php', 'Register'),
		'register2' => array('Register.php', 'Register2'),
		'reminder' => array('Reminder.php', 'RemindMe'),
		'removetopic2' => array('RemoveTopic.php', 'RemoveTopic2'),
		'removeoldtopics2' => array('RemoveTopic.php', 'RemoveOldTopics2'),
		'removepoll' => array('Poll.php', 'RemovePoll'),
		'repairboards' => array('RepairBoards.php', 'RepairBoards'),
		'reporttm' => array('SendTopic.php', 'ReportToModerator'),
		'reports' => array('Reports.php', 'ReportsMain'),
		'requestmembers' => array('Subs-Auth.php', 'RequestMembers'),
		'search' => array('Search.php', 'PlushSearch1'),
		'search2' => array('Search.php', 'PlushSearch2'),
		'sendtopic' => array('SendTopic.php', 'SendTopic'),
		'serversettings' => array('ManageServer.php', 'ModifySettings'),
		'serversettings2' => array('ManageServer.php', 'ModifySettings2'),
		'smileys' => array('ManageSmileys.php', 'ManageSmileys'),
		'smstats' => array('Stats.php', 'SMStats'),
		'spellcheck' => array('Subs-Post.php', 'SpellCheck'),
		'splittopics' => array('SplitTopics.php', 'SplitTopics'),
		'stats' => array('Stats.php', 'DisplayStats'),
		'sticky' => array('LockTopic.php', 'Sticky'),
		'theme' => array('Themes.php', 'ThemesMain'),
		'trackip' => array('Profile.php', 'trackIP'),
		'about:mozilla' => array('Karma.php', 'BookOfUnknown'),
		'about:unknown' => array('Karma.php', 'BookOfUnknown'),
		'unread' => array('Recent.php', 'UnreadTopics'),
		'unreadreplies' => array('Recent.php', 'UnreadTopics'),
		'viewErrorLog' => array('ManageErrors.php', 'ViewErrorLog'),
		'viewmembers' => array('ManageMembers.php', 'ViewMembers'),
		'viewprofile' => array('Profile.php', 'ModifyProfile'),
		'verificationcode' => array('Register.php', 'VerificationCode'),
		'vote' => array('Poll.php', 'Vote'),
		'viewquery' => array('ViewQuery.php', 'ViewQuery'),
		'who' => array('Who.php', 'Who'),
		'.xml' => array('News.php', 'ShowXmlFeed'),
	);

	// Get the function and file to include - if it's not there, do the board index.
	if (!isset($_REQUEST['action']) || !isset($actionArray[$_REQUEST['action']]))
	{
		// Catch the action with the theme?
		if (!empty($settings['catch_action']))
		{
			require_once($sourcedir . '/Themes.php');
			return 'WrapAction';
		}

		// Fall through to the board index then...
		require_once($sourcedir . '/BoardIndex.php');
		return 'BoardIndex';
	}

	// Otherwise, it was set - so let's go to that action.
	require_once($sourcedir . '/' . $actionArray[$_REQUEST['action']][0]);
	return $actionArray[$_REQUEST['action']][1];
}

?>