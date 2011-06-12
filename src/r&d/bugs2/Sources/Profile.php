<?php
/**********************************************************************************
* Profile.php                                                                     *
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

/*	This file has the primary job of showing and editing people's profiles.
	It also allows the user to change some of their or another's preferences,
	and such things.  It uses the following functions:

	void ModifyProfile(array errors = none)
		// !!!

	void ModifyProfile2()
		// !!!

	void saveProfileChanges(array &profile_variables, array &errors, int ID_MEMBER)
		// !!!

	void makeThemeChanges(int ID_MEMBER, int ID_THEME)
		// !!!

	void makeNotificationChanges(int ID_MEMBER)
		// !!!

	void makeAvatarChanges(int ID_MEMBER, array &errors)
		// !!!

	void summary(int ID_MEMBER)
		// !!!

	void showPosts(int ID_MEMBER)
		// !!!

	void statPanel(int ID_MEMBER)
		// !!!

	void trackUser(int ID_MEMBER)
		// !!!

	void TrackIP(int ID_MEMBER = none)
		// !!!

	void showPermissions(int ID_MEMBER)
		// !!!

	void account(int ID_MEMBER)
		// !!!

	void forumProfile(int ID_MEMBER)
		// !!!

	array getAvatars(string directory, int level)
		// !!!

	void theme(int ID_MEMBER)
		// !!!

	void notification(int ID_MEMBER)
		// !!!

	void pmprefs(int ID_MEMBER)
		// !!!

	void deleteAccount(int ID_MEMBER)
		// !!!

	void deleteAccount2(array profile_variables, array &errors, int ID_MEMBER)
		// !!!

	void rememberPostData()
		// !!!

	void loadThemeOptions(int ID_MEMBER)
		// !!!

	Adding new fields to the profile:
	---------------------------------------------------------------------------
		// !!!
*/

// Allow the change or view of profiles...
function ModifyProfile($post_errors = array())
{
	global $txt, $scripturl, $user_info, $context, $ID_MEMBER, $sourcedir, $user_profile, $modSettings;

	loadLanguage('Profile');
	loadTemplate('Profile');

	/* Set allowed sub-actions.

	 The format of $sa_allowed is as follows:

	$sa_allowed = array(
		'sub-action' => array(permission_array_for_editing_OWN_profile, permission_array_for_editing_ANY_profile[, require_validation]),
		...
	);

	*/

	$sa_allowed = array(
		'summary' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'statPanel' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'showPosts' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'editBuddies' => array(array('profile_extra_any', 'profile_extra_own'), array()),
		'trackUser' => array(array('moderate_forum'), array('moderate_forum')),
		'trackIP' => array(array('moderate_forum'), array('moderate_forum')),
		'showPermissions' => array(array('manage_permissions'), array('manage_permissions')),
		'account' => array(array('manage_membergroups', 'profile_identity_any', 'profile_identity_own'), array('manage_membergroups', 'profile_identity_any')),
		'forumProfile' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'theme' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'notification' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'pmprefs' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'deleteAccount' => array(array('profile_remove_any', 'profile_remove_own'), array('profile_remove_any')),
	);

	// Set the profile layer to be displayed.
	$context['template_layers'][] = 'profile';

	// Did we get the user by name...
	if (isset($_REQUEST['user']))
		$memberResult = loadMemberData($_REQUEST['user'], true, 'profile');
	// ... or by ID_MEMBER?
	elseif (!empty($_REQUEST['u']))
		$memberResult = loadMemberData((int) $_REQUEST['u'], false, 'profile');
	// If it was just ?action=profile, edit your own profile.
	else
		$memberResult = loadMemberData($ID_MEMBER, false, 'profile');

	// Check if loadMemberData() has returned a valid result.
	if (!is_array($memberResult))
		fatal_lang_error(453, false);

	// If all went well, we have a valid member ID!
	list ($memID) = $memberResult;

	// Is this the profile of the user himself or herself?
	$context['user']['is_owner'] = $memID == $ID_MEMBER;

	// No Subaction?
	if (!isset($_REQUEST['sa']) || !isset($sa_allowed[$_REQUEST['sa']]))
	{
		// Pick the first subaction you're allowed to see.
		if ((allowedTo('profile_view_own') && $context['user']['is_owner']) || allowedTo('profile_view_any'))
			$_REQUEST['sa'] = 'summary';
		elseif (allowedTo('moderate_forum'))
			$_REQUEST['sa'] = 'trackUser';
		elseif (allowedTo('manage_permissions'))
			$_REQUEST['sa'] = 'showPermissions';
		elseif ((allowedTo('profile_identity_own') && $context['user']['is_owner']) || allowedTo('profile_identity_any') || allowedTo('manage_membergroups'))
			$_REQUEST['sa'] = 'account';
		elseif ((allowedTo('profile_extra_own') && $context['user']['is_owner']) || allowedTo('profile_extra_any'))
			$_REQUEST['sa'] = 'forumProfile';
		elseif ((allowedTo('profile_remove_own') && $context['user']['is_owner']) || allowedTo('profile_remove_any'))
			$_REQUEST['sa'] = 'deleteAccount';
		else
			isAllowedTo('profile_view_' . ($context['user']['is_owner'] ? 'own' : 'any'));
	}

	// Check the permissions for the given sub action.
	isAllowedTo($sa_allowed[$_REQUEST['sa']][$context['user']['is_owner'] ? 0 : 1]);

	// Make sure the user is who he claims to be, before any important account stuff is changed.
	if (!empty($sa_allowed[$_REQUEST['sa']][2]))
		validateSession();

	// No need for this anymore.
	unset($sa_allowed);

	$context['profile_areas'] = array();

	// Set the menu items in the left bar...
	if (!$user_info['is_guest'] && (($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo(array('profile_view_any', 'moderate_forum', 'manage_permissions'))))
	{
		$context['profile_areas']['info'] = array(
			'title' => $txt['profileInfo'],
			'areas' => array()
		);

		if (($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo('profile_view_any'))
		{
			$context['profile_areas']['info']['areas']['summary'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=summary">' . $txt['summary'] . '</a>';
			$context['profile_areas']['info']['areas']['statPanel']	= '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=statPanel">' . $txt['statPanel'] . '</a>';
			$context['profile_areas']['info']['areas']['showPosts']	= '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=showPosts">' . $txt['showPosts'] . '</a>';
		}

		// Groups with moderator permissions can also....
		if (allowedTo('moderate_forum'))
		{
			$context['profile_areas']['info']['areas']['trackUser'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=trackUser">' . $txt['trackUser'] . '</a>';
			$context['profile_areas']['info']['areas']['trackIP'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=trackIP">' . $txt['trackIP'] . '</a>';
		}
		if (allowedTo('manage_permissions'))
			$context['profile_areas']['info']['areas']['showPermissions'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=showPermissions">' . $txt['showPermissions'] . '</a>';
	}

	// Edit your/this person's profile?
	if (($context['user']['is_owner'] && (allowedTo(array('profile_identity_own', 'profile_extra_own')))) || allowedTo(array('profile_identity_any', 'profile_extra_any', 'manage_membergroups')))
	{
		$context['profile_areas']['edit_profile'] = array(
			'title' => $txt['profileEdit'],
			'areas' => array()
		);

		if (($context['user']['is_owner'] && allowedTo('profile_identity_own')) || allowedTo(array('profile_identity_any', 'manage_membergroups')))
			$context['profile_areas']['edit_profile']['areas']['account'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=account">' . $txt['account'] . '</a>';

		if (($context['user']['is_owner'] && allowedTo('profile_extra_own')) || allowedTo('profile_extra_any'))
		{
			$context['profile_areas']['edit_profile']['areas']['forumProfile'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=forumProfile">' . $txt['forumProfile'] . '</a>';
			$context['profile_areas']['edit_profile']['areas']['theme'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=theme">' . $txt['theme'] . '</a>';
			$context['profile_areas']['edit_profile']['areas']['notification'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=notification">' . $txt['notification'] . '</a>';
			$context['profile_areas']['edit_profile']['areas']['pmprefs'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=pmprefs">' . $txt['pmprefs'] . '</a>';
		}

		// !!! I still don't think this warrants a new section by any means, but it's definitely not part of viewing a person's profile, if only the owner can do it.
		if (!empty($modSettings['enable_buddylist']) && $context['user']['is_owner'] && allowedTo(array('profile_extra_own', 'profile_extra_any')))
			$context['profile_areas']['edit_profile']['areas']['editBuddies'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=editBuddies">' . $txt['editBuddies'] . '</a>';
	}

	// If you have permission to do something with this profile, you'll see one or more actions.
	if (($context['user']['is_owner'] && allowedTo('profile_remove_own')) || allowedTo('profile_remove_any') || (!$context['user']['is_owner'] && allowedTo('pm_send')))
	{
		// Initialize the action menu group...
		$context['profile_areas']['profile_action'] = array(
			'title' => $txt['profileAction'],
			'areas' => array()
		);

		// You shouldn't PM (or ban really..) yourself!! (only administrators see this because it's not in the menu.)
		if (!$context['user']['is_owner'] && allowedTo('pm_send'))
			$context['profile_areas']['profile_action']['areas']['send_pm'] = '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $memID . '">' . $txt['profileSendIm'] . '</a>';
		// We don't wanna ban admins, do we?
		if (allowedTo('manage_bans') && $user_profile[$memID]['ID_GROUP'] != 1 && !in_array(1, explode(',', $user_profile[$memID]['additionalGroups'])))
			$context['profile_areas']['profile_action']['areas']['banUser'] = '<a href="' . $scripturl . '?action=ban;sa=add;u=' . $memID . '">' . $txt['profileBanUser'] . '</a>';

		// You may remove your own account 'cuz it's yours or you're an admin.
		if (($context['user']['is_owner'] && allowedTo('profile_remove_own')) || allowedTo('profile_remove_any'))
			$context['profile_areas']['profile_action']['areas']['deleteAccount'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=deleteAccount">' . $txt['deleteAccount'] . '</a>';
	}

	// This is here so the menu won't be shown unless it's actually needed.
	if (!isset($context['profile_areas']['info']['areas']['trackUser']) && !isset($context['profile_areas']['info']['areas']['showPermissions']) && !isset($context['profile_areas']['edit_profile']) && !isset($context['profile_areas']['profile_action']['areas']['banUser']) && !isset($context['profile_areas']['profile_action']['areas']['deleteAccount']))
		$context['profile_areas'] = array();

	// Set the selected items.
	$context['menu_item_selected'] = $_REQUEST['sa'];
	$context['sub_template'] = $_REQUEST['sa'];

	// All the subactions that require a user password in order to validate.
	$context['require_password'] = in_array($context['menu_item_selected'], array('account'));

	$context['member'] = array(
		'id' => $memID,
		'username' => $user_profile[$memID]['memberName'],
		'name' => !isset($user_profile[$memID]['realName']) || $user_profile[$memID]['realName'] == '' ? '' : $user_profile[$memID]['realName'],
		'email' => $user_profile[$memID]['emailAddress'],
		'posts' => empty($user_profile[$memID]['posts']) ? 0: (int) $user_profile[$memID]['posts'],
		'hide_email' => empty($user_profile[$memID]['hideEmail']) ? 0 : $user_profile[$memID]['hideEmail'],
		'show_online' => empty($user_profile[$memID]['showOnline']) ? 0 : $user_profile[$memID]['showOnline'],
		'registered' => empty($user_profile[$memID]['dateRegistered']) ? $txt[470] : strftime('%Y-%m-%d', $user_profile[$memID]['dateRegistered'] + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600),
		'group' => $user_profile[$memID]['ID_GROUP'],
		'gender' => array('name' => empty($user_profile[$memID]['gender']) ? '' : ($user_profile[$memID]['gender'] == 2 ? 'f' : 'm')),
		'karma' => array(
			'good' => empty($user_profile[$memID]['karmaGood']) ? '0' : $user_profile[$memID]['karmaGood'],
			'bad' => empty($user_profile[$memID]['karmaBad']) ? '0' : $user_profile[$memID]['karmaBad'],
		),
		'avatar' => array(
			'name' => &$user_profile[$memID]['avatar'],
			'href' => empty($user_profile[$memID]['ID_ATTACH']) ? '' : (empty($user_profile[$memID]['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $user_profile[$memID]['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $user_profile[$memID]['filename']),
			'custom' => stristr($user_profile[$memID]['avatar'], 'http://') ? $user_profile[$memID]['avatar'] : 'http://',
			'selection' => $user_profile[$memID]['avatar'] == '' || stristr($user_profile[$memID]['avatar'], 'http://') ? '' : $user_profile[$memID]['avatar'],
			'ID_ATTACH' => &$user_profile[$memID]['ID_ATTACH'],
			'filename' => &$user_profile[$memID]['filename'],
			'allow_server_stored' => allowedTo('profile_server_avatar') || !$context['user']['is_owner'],
			'allow_upload' => allowedTo('profile_upload_avatar') || !$context['user']['is_owner'],
			'allow_external' => allowedTo('profile_remote_avatar') || !$context['user']['is_owner'],
		),
		'icq' => array('name' => !isset($user_profile[$memID]['ICQ']) ? '' : $user_profile[$memID]['ICQ']),
		'aim' => array('name' => empty($user_profile[$memID]['AIM']) ? '' : str_replace('+', ' ', $user_profile[$memID]['AIM'])),
		'yim' => array('name' => empty($user_profile[$memID]['YIM']) ? '' : $user_profile[$memID]['YIM']),
		'msn' => array('name' => empty($user_profile[$memID]['MSN']) ? '' : $user_profile[$memID]['MSN']),
		'website' => array(
			'title' => !isset($user_profile[$memID]['websiteTitle']) ? '' : $user_profile[$memID]['websiteTitle'],
			'url' => !isset($user_profile[$memID]['websiteUrl']) ? '' : $user_profile[$memID]['websiteUrl'],
		),
	);

	// Call the appropriate subaction function.
	$_REQUEST['sa']($memID);

	if (!empty($post_errors))
	{
		// Set all the errors so the template knows what went wrong.
		foreach ($post_errors as $error_type)
			$context['modify_error'][$error_type] = true;
		rememberPostData();
	}

	// Set the page title if it's not already set...
	if (!isset($context['page_title']))
		$context['page_title'] = $txt[79] . ' - ' . $txt[$_REQUEST['sa']];
}

// Execute the modifications!
function ModifyProfile2()
{
	global $txt, $modSettings;
	global $cookiename, $context;
	global $sourcedir, $scripturl, $db_prefix;
	global $ID_MEMBER, $user_info;
	global $context, $newpassemail, $user_profile, $validationCode;

	loadLanguage('Profile');

	/* Set allowed sub-actions.

	 The format of $sa_allowed is as follows:

	$sa_allowed = array(
		'sub-action' => array(permission_array_for_editing_OWN_profile, permission_array_for_editing_ANY_profile, session_validation_method[, require_password]),
		...
	);

	*/

	$sa_allowed = array(
		'account' => array(array('manage_membergroups', 'profile_identity_any', 'profile_identity_own'), array('manage_membergroups', 'profile_identity_any'), 'post', true),
		'forumProfile' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'theme' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'notification' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'pmprefs' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'deleteAccount' => array(array('profile_remove_any', 'profile_remove_own'), array('profile_remove_any'), 'post', true),
		'activateAccount' => array(array(), array('moderate_forum'), 'get'),
	);

	// Is the current sub-action allowed?
	if (empty($_REQUEST['sa']) || !isset($sa_allowed[$_REQUEST['sa']]))
		fatal_lang_error(453, false);

	checkSession($sa_allowed[$_REQUEST['sa']][2]);

	// Start with no updates and no errors.
	$profile_vars = array();
	$post_errors = array();

	// Normally, don't send an email.
	$newpassemail = false;

	// Clean up the POST variables.
	$_POST = htmltrim__recursive($_POST);
	$_POST = stripslashes__recursive($_POST);
	$_POST = htmlspecialchars__recursive($_POST);
	$_POST = addslashes__recursive($_POST);

	// Search for the member being edited and put the information in $user_profile.
	$memberResult = loadMemberData((int) $_REQUEST['userID'], false, 'profile');

	if (!is_array($memberResult))
		fatal_lang_error(453, false);

	list ($memID) = $memberResult;

	// Are you modifying your own, or someone else's?
	if ($ID_MEMBER == $memID)
		$context['user']['is_owner'] = true;
	else
	{
		$context['user']['is_owner'] = false;
		validateSession();
	}

	// Check profile editing permissions.
	isAllowedTo($sa_allowed[$_REQUEST['sa']][$context['user']['is_owner'] ? 0 : 1]);

	// If this is yours, check the password.
	if ($context['user']['is_owner'] && !empty($sa_allowed[$_REQUEST['sa']][3]))
	{
		// You didn't even enter a password!
		if (trim($_POST['oldpasswrd']) == '')
			$post_errors[] = 'no_password';

		// Since the password got modified due to all the $_POST cleaning, lets undo it so we can get the correct password
		$_POST['oldpasswrd'] = addslashes(un_htmlspecialchars(stripslashes($_POST['oldpasswrd'])));

		// Does the integration want to check passwords?
		$good_password = false;
		if (isset($modSettings['integrate_verify_password']) && function_exists($modSettings['integrate_verify_password']))
			if (call_user_func($modSettings['integrate_verify_password'], $user_profile[$memID]['memberName'], $_POST['oldpasswrd'], false) === true)
				$good_password = true;

		// Bad password!!!
		if (!$good_password && $user_info['passwd'] != sha1(strtolower($user_profile[$memID]['memberName']) . $_POST['oldpasswrd']))
			$post_errors[] = 'bad_password';
	}

	// No need for the sub action array.
	unset($sa_allowed);

	// If the user is an admin - see if they are resetting someones username.
	if ($user_info['is_admin'] && isset($_POST['memberName']))
	{
		// We'll need this...
		require_once($sourcedir . '/Subs-Auth.php');

		// Do the reset... this will send them an email too.
		resetPassword($memID, $_POST['memberName']);
	}

	// Change the IP address in the database.
	if ($context['user']['is_owner'])
		$profile_vars['memberIP'] = "'$user_info[ip]'";

	// Now call the sub-action function...
	if (isset($_POST['sa']) && $_POST['sa'] == 'deleteAccount')
	{
		deleteAccount2($profile_vars, $post_errors, $memID);

		if (empty($post_errors))
			redirectexit();
	}
	else
		saveProfileChanges($profile_vars, $post_errors, $memID);

	// There was a problem, let them try to re-enter.
	if (!empty($post_errors))
	{
		// Load the language file so we can give a nice explanation of the errors.
		loadLanguage('Errors');
		$context['post_errors'] = $post_errors;

		$_REQUEST['sa'] = $_POST['sa'];
		$_REQUEST['u'] = $memID;
		return ModifyProfile($post_errors);
	}

	if (!empty($profile_vars))
	{
		// If we've changed the password, notify any integration that may be listening in.
		if (isset($profile_vars['passwd']) && isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
			call_user_func($modSettings['integrate_reset_pass'], $user_profile[$memID]['memberName'], $user_profile[$memID]['memberName'], $_POST['passwrd1']);

		updateMemberData($memID, $profile_vars);
	}

	// What if this is the newest member?
	if ($modSettings['latestMember'] == $memID)
		updateStats('member');
	elseif (isset($profile_vars['realName']))
		updateSettings(array('memberlist_updated' => time()));

	// If the member changed his/her birthdate, update calendar statistics.
	if (isset($profile_vars['birthdate']) || isset($profile_vars['realName']))
		updateStats('calendar');

	// Send an email?
	if ($newpassemail)
	{
		require_once($sourcedir . '/Subs-Post.php');

		// Send off the email.
		sendmail($_POST['emailAddress'], $txt['activate_reactivate_title'] . ' ' . $context['forum_name'],
			"$txt[activate_reactivate_mail]\n\n" .
			"$scripturl?action=activate;u=$memID;code=$validationCode\n\n" .
			"$txt[activate_code]: $validationCode\n\n" .
			$txt[130]);

		// Log the user out.
		db_query("
			DELETE FROM {$db_prefix}log_online
			WHERE ID_MEMBER = $memID", __FILE__, __LINE__);
		$_SESSION['log_time'] = 0;
		$_SESSION['login_' . $cookiename] = serialize(array(0, '', 0));

		if (isset($_COOKIE[$cookiename]))
			$_COOKIE[$cookiename] = '';

		loadUserSettings();

		$context['user']['is_logged'] = false;
		$context['user']['is_guest'] = true;

		// Send them to the done-with-registration-login screen.
		loadTemplate('Register');
		$context += array(
			'page_title' => &$txt[79],
			'sub_template' => 'after',
			'description' => &$txt['activate_changed_email']
		);
		return;
	}
	elseif ($context['user']['is_owner'])
	{
		// Log them back in.
		if (isset($_POST['passwrd1']) && $_POST['passwrd1'] != '')
		{
			require_once($sourcedir . '/Subs-Auth.php');
			setLoginCookie(60 * $modSettings['cookieTime'], $memID, sha1(sha1(strtolower($user_profile[$memID]['memberName']) . un_htmlspecialchars(stripslashes($_POST['passwrd1']))) . $user_profile[$memID]['passwordSalt']));
		}

		loadUserSettings();
		writeLog();
	}

	// Back to same subaction page..
	redirectexit('action=profile;u=' . $memID . ';sa=' . $_REQUEST['sa'], (isset($_POST['passwrd1']) && $context['server']['needs_login_fix']) || ($context['browser']['is_ie'] && isset($_FILES['attachment'])));
}

// Save the profile changes....
function saveProfileChanges(&$profile_vars, &$post_errors, $memID)
{
	global $db_prefix, $user_info, $txt, $modSettings, $user_profile;
	global $newpassemail, $validationCode, $context, $settings, $sourcedir;
	global $func;

	// These make life easier....
	$old_profile = &$user_profile[$memID];

	// Permissions...
	if ($context['user']['is_owner'])
	{
		$changeIdentity = allowedTo(array('profile_identity_any', 'profile_identity_own'));
		$changeOther = allowedTo(array('profile_extra_any', 'profile_extra_own'));
	}
	else
	{
		$changeIdentity = allowedTo('profile_identity_any');
		$changeOther = allowedTo('profile_extra_any');
	}

	// Arrays of all the changes - makes things easier.
	$profile_bools = array(
		'notifyAnnouncements', 'notifyOnce', 'notifySendBody',
	);
	$profile_ints = array(
		'pm_email_notify',
		'notifyTypes',
		'ICQ',
		'gender',
		'ID_THEME',
	);
	$profile_floats = array(
		'timeOffset',
	);
	$profile_strings = array(
		'websiteUrl', 'websiteTitle',
		'AIM', 'YIM',
		'location', 'birthdate',
		'timeFormat',
		'buddy_list',
		'pm_ignore_list',
		'smileySet',
		'signature', 'personalText', 'avatar',
	);

	// Fix the spaces in messenger screennames...
	$fix_spaces = array('MSN', 'AIM', 'YIM');
	foreach ($fix_spaces as $var)
	{
		// !!! Why?
		if (isset($_POST[$var]))
			$_POST[$var] = strtr($_POST[$var], ' ', '+');
	}

	// Make sure the MSN one is an email address, not something like 'none' :P.
	if (isset($_POST['MSN']) && ($_POST['MSN'] == '' || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $_POST['MSN']) != 0))
		$profile_strings[] = 'MSN';

	// Validate the title...
	if (!empty($modSettings['titlesEnable']) && (allowedTo('profile_title_any') || (allowedTo('profile_title_own') && $context['user']['is_owner'])))
		$profile_strings[] = 'usertitle';

	// Validate the timeOffset...
	if (isset($_POST['timeOffset']))
	{
		$_POST['timeOffset'] = strtr($_POST['timeOffset'], ',', '.');

		if ($_POST['timeOffset'] < -23.5 || $_POST['timeOffset'] > 23.5)
			$post_errors[] = 'bad_offset';
	}

	// Fix the URL...
	if (isset($_POST['websiteUrl']))
	{
		if (strlen(trim($_POST['websiteUrl'])) > 0 && strpos($_POST['websiteUrl'], '://') === false)
			$_POST['websiteUrl'] = 'http://' . $_POST['websiteUrl'];
		if (strlen($_POST['websiteUrl']) < 8 || (substr($_POST['websiteUrl'], 0, 7) !== 'http://' && substr($_POST['websiteUrl'], 0, 8) !== 'https://'))
			$_POST['websiteUrl'] = '';
	}

	// !!! Should we check for this year and tell them they made a mistake :P? (based on coppa at least?)
	if (isset($_POST['birthdate']))
	{
		if (preg_match('/(\d{4})[\-\., ](\d{2})[\-\., ](\d{2})/', $_POST['birthdate'], $dates) === 1)
			$_POST['birthdate'] = checkdate($dates[2], $dates[3], $dates[1] < 4 ? 4 : $dates[1]) ? sprintf('%04d-%02d-%02d', $dates[1] < 4 ? 4 : $dates[1], $dates[2], $dates[3]) : '0001-01-01';
		else
			unset($_POST['birthdate']);
	}
	elseif (isset($_POST['bday1'], $_POST['bday2'], $_POST['bday3']) && $_POST['bday1'] > 0 && $_POST['bday2'] > 0)
		$_POST['birthdate'] = checkdate($_POST['bday1'], $_POST['bday2'], $_POST['bday3'] < 4 ? 4 : $_POST['bday3']) ? sprintf('%04d-%02d-%02d', $_POST['bday3'] < 4 ? 4 : $_POST['bday3'], $_POST['bday1'], $_POST['bday2']) : '0001-01-01';
	elseif (isset($_POST['bday1']) || isset($_POST['bday2']) || isset($_POST['bday3']))
		$_POST['birthdate'] = '0001-01-01';

	if (isset($_POST['im_email_notify']))
		$_POST['pm_email_notify'] = $_POST['im_email_notify'];

	// Validate and set the ignorelist...
	if (isset($_POST['pm_ignore_list']) || isset($_POST['im_ignore_list']))
	{
		if (!isset($_POST['pm_ignore_list']))
			$_POST['pm_ignore_list'] = $_POST['im_ignore_list'];
		$_POST['pm_ignore_list'] = strtr($func['htmltrim']($_POST['pm_ignore_list']), array('\\\'' => '&#039;', "\n" => "', '", "\r" => '', '&quot;' => ''));

		if (preg_match('~(\A|,)\*(\Z|,)~s', $_POST['pm_ignore_list']) == 0)
		{
			$result = db_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}members
				WHERE memberName IN ('$_POST[pm_ignore_list]') OR realName IN ('$_POST[pm_ignore_list]')
				LIMIT " . (substr_count($_POST['pm_ignore_list'], '\', \'') + 1), __FILE__, __LINE__);
			$_POST['pm_ignore_list'] = '';
			while ($row = mysql_fetch_assoc($result))
				$_POST['pm_ignore_list'] .= $row['ID_MEMBER'] . ',';
			mysql_free_result($result);

			// !!! Did we find all the members?

			$_POST['pm_ignore_list'] = substr($_POST['pm_ignore_list'], 0, -1);
		}
		else
			$_POST['pm_ignore_list'] = '*';
	}

	// Similarly, do the same for the buddy list
	if (isset($_POST['buddy_list']))
	{
		$_POST['buddy_list'] = strtr(trim($_POST['buddy_list']), array('\\\'' => '&#039;', "\n" => "', '", "\r" => '', '&quot;' => ''));

		if (trim($_POST['buddy_list']) != '')
		{
			$result = db_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}members
				WHERE memberName IN ('$_POST[buddy_list]') OR realName IN ('$_POST[buddy_list]')
				LIMIT " . (substr_count($_POST['buddy_list'], '\', \'') + 1), __FILE__, __LINE__);
			$_POST['buddy_list'] = '';
			while ($row = mysql_fetch_assoc($result))
				$_POST['buddy_list'] .= $row['ID_MEMBER'] . ',';
			mysql_free_result($result);

			// !!! Did we find all the members?

			$_POST['buddy_list'] = substr($_POST['buddy_list'], 0, -1);
		}
	}

	// Validate the smiley set.
	if (isset($_POST['smileySet']))
	{
		$smiley_sets = explode(',', $modSettings['smiley_sets_known']);
		if (!in_array($_POST['smileySet'], $smiley_sets) && $_POST['smileySet'] != 'none')
			$_POST['smileySet'] = '';
	}

	// Make sure the signature isn't too long.
	if (isset($_POST['signature']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		if (!empty($modSettings['max_signatureLength']) && $func['strlen']($_POST['signature']) > $modSettings['max_signatureLength'])
			$_POST['signature'] = addslashes($func['substr'](stripslashes($_POST['signature']), 0, $modSettings['max_signatureLength']));

		if (strlen($_POST['signature']) > 65534)
			$_POST['signature'] = addslashes($func['truncate'](stripslashes($_POST['signature']), 65534));

		$_POST['signature'] = strtr($_POST['signature'], array('&quot;' => '\\&quot;', '&#039;' => '\\&#39;', '&#39;' => '\\&#39;'));
		preparsecode($_POST['signature']);
	}

	// Identity-only changes...
	if ($changeIdentity)
	{
		// This block is only concerned with display name validation.
		if (isset($_POST['realName']) && (!empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum')) && trim($_POST['realName']) != $old_profile['realName'])
		{
			$_POST['realName'] = trim(preg_replace('~[\s]~' . ($context['utf8'] ? 'u' : ''), ' ', $_POST['realName']));
			if (trim($_POST['realName']) == '')
				$post_errors[] = 'no_name';
			elseif ($func['strlen']($_POST['realName']) > 60)
				$post_errors[] = 'name_too_long';
			else
			{
				require_once($sourcedir . '/Subs-Members.php');
				if (isReservedName($_POST['realName'], $memID))
					$post_errors[] = 'name_taken';
			}

			if (isset($_POST['realName']))
				$profile_vars['realName'] = '\'' . $_POST['realName'] . '\'';
		}

		// Change the registration date.
		if (!empty($_POST['dateRegistered']) && allowedTo('admin_forum'))
		{
			// Bad date!  Go try again - please?
			if (($_POST['dateRegistered'] = strtotime($_POST['dateRegistered'])) === -1)
				fatal_error($txt['smf233'] . ' ' . strftime('%d %b %Y ' . (strpos($user_info['time_format'], '%H') !== false ? '%I:%M:%S %p' : '%H:%M:%S'), forum_time(false)), false);
			// As long as it doesn't equal 'N/A'...
			elseif ($_POST['dateRegistered'] != $txt[470] && $_POST['dateRegistered'] != strtotime(strftime('%Y-%m-%d', $user_profile[$memID]['dateRegistered'] + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600)))
				$profile_vars['dateRegistered'] = $_POST['dateRegistered'] - ($user_info['time_offset'] + $modSettings['time_offset']) * 3600;
		}

		// Change the number of posts.
		if (isset($_POST['posts']) && allowedTo('moderate_forum'))
			$profile_vars['posts'] = $_POST['posts'] != '' ? (int) strtr($_POST['posts'], array(',' => '', '.' => '', ' ' => '')) : '\'\'';

		// This block is only concerned with email address validation..
		if (isset($_POST['emailAddress']) && strtolower($_POST['emailAddress']) != strtolower($old_profile['emailAddress']))
		{
			$_POST['emailAddress'] = strtr($_POST['emailAddress'], array('&#039;' => '\\\''));

			// Prepare the new password, or check if they want to change their own.
			if (!empty($modSettings['send_validation_onChange']) && !allowedTo('moderate_forum'))
			{
				require_once($sourcedir . '/Subs-Members.php');
				$validationCode = generateValidationCode();
				$profile_vars['validation_code'] = '\'' . $validationCode . '\'';
				$profile_vars['is_activated'] = '2';
				$newpassemail = true;
			}

			// Check the name and email for validity.
			if (trim($_POST['emailAddress']) == '')
				$post_errors[] = 'no_email';
			if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_POST['emailAddress'])) == 0)
				$post_errors[] = 'bad_email';

			// Email addresses should be and stay unique.
			$request = db_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}members
				WHERE ID_MEMBER != $memID
					AND emailAddress = '$_POST[emailAddress]'
				LIMIT 1", __FILE__, __LINE__);
			if (mysql_num_rows($request) > 0)
				$post_errors[] = 'email_taken';
			mysql_free_result($request);

			$profile_vars['emailAddress'] = '\'' . $_POST['emailAddress'] . '\'';
		}

		// Hide email address?
		if (isset($_POST['hideEmail']) && (!empty($modSettings['allow_hideEmail']) || allowedTo('moderate_forum')))
			$profile_vars['hideEmail'] = empty($_POST['hideEmail']) ? '0' : '1';

		// Are they allowed to change their hide status?
		if (isset($_POST['showOnline']) && (!empty($modSettings['allow_hideOnline']) || allowedTo('moderate_forum')))
			$profile_vars['showOnline'] = empty($_POST['showOnline']) ? '0' : '1';

		// If they're trying to change the password, let's check they pick a sensible one.
		if (isset($_POST['passwrd1']) && $_POST['passwrd1'] != '')
		{
			// Do the two entries for the password even match?
			if ($_POST['passwrd1'] != $_POST['passwrd2'])
				$post_errors[] = 'bad_new_password';

			// Let's get the validation function into play...
			require_once($sourcedir . '/Subs-Auth.php');
			$passwordErrors = validatePassword($_POST['passwrd1'], $user_info['username'], array($user_info['name'], $user_info['email']));

			// Were there errors?
			if ($passwordErrors != null)
				$post_errors[] = 'password_' . $passwordErrors;

			// Set up the new password variable... ready for storage.
			$profile_vars['passwd'] = '\'' . sha1(strtolower($old_profile['memberName']) . un_htmlspecialchars(stripslashes($_POST['passwrd1']))) . '\'';
		}

		if (isset($_POST['secretQuestion']))
			$profile_vars['secretQuestion'] = '\'' . $_POST['secretQuestion'] . '\'';

		// Do you have a *secret* password?
		if (isset($_POST['secretAnswer']) && $_POST['secretAnswer'] != '')
			$profile_vars['secretAnswer'] = '\'' . md5($_POST['secretAnswer']) . '\'';
	}

	// Things they can do if they are a forum moderator.
	if (allowedTo('moderate_forum'))
	{
		if (($_REQUEST['sa'] == 'activateAccount' || !empty($_POST['is_activated'])) && isset($old_profile['is_activated']) && $old_profile['is_activated'] != 1)
		{
			// If we are approving the deletion of an account, we do something special ;)
			if ($old_profile['is_activated'] == 4)
			{
				require_once($sourcedir . '/Subs-Members.php');
				deleteMembers($memID);
				redirectexit();
			}

			if (isset($modSettings['integrate_activate']) && function_exists($modSettings['integrate_activate']))
				call_user_func($modSettings['integrate_activate'], $old_profile['memberName']);

			// Actually update this member now, as it guarantees the unapproved count can't get corrupted.
			updateMemberData($memID, array('is_activated' => $old_profile['is_activated'] >= 10 ? '11' : '1', 'validation_code' => '\'\''));

			// If we are doing approval, update the stats for the member just incase.
			if (in_array($old_profile['is_activated'], array(3, 4, 13, 14)))
				updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > 1 ? $modSettings['unapprovedMembers'] - 1 : 0)));

			// Make sure we update the stats too.
			updateStats('member', false);
		}

		if (isset($_POST['karmaGood']))
			$profile_vars['karmaGood'] = $_POST['karmaGood'] != '' ? (int) $_POST['karmaGood'] : '\'\'';
		if (isset($_POST['karmaBad']))
			$profile_vars['karmaBad'] = $_POST['karmaBad'] != '' ? (int) $_POST['karmaBad'] : '\'\'';
	}

	// Assigning membergroups (you need admin_forum permissions to change an admins' membergroups).
	if (allowedTo('manage_membergroups'))
	{
		// The account page allows the change of your ID_GROUP - but not to admin!.
		if (isset($_POST['ID_GROUP']) && (allowedTo('admin_forum') || ((int) $_POST['ID_GROUP'] != 1 && $old_profile['ID_GROUP'] != 1)))
			$profile_vars['ID_GROUP'] = (int) $_POST['ID_GROUP'];

		// Find the additional membergroups (if any)
		if (isset($_POST['additionalGroups']) && is_array($_POST['additionalGroups']))
		{
			foreach ($_POST['additionalGroups'] as $i => $group_id)
			{
				if ((int) $group_id == 0 || (!allowedTo('admin_forum') && (int) $group_id == 1))
					unset($_POST['additionalGroups'][$i], $_POST['additionalGroups'][$i]);
				else
					$_POST['additionalGroups'][$i] = (int) $group_id;
			}

			// Put admin back in there if you don't have permission to take it away.
			if (!allowedTo('admin_forum') && in_array(1, explode(',', $old_profile['additionalGroups'])))
				$_POST['additionalGroups'][] = 1;

			$profile_vars['additionalGroups'] = '\'' . implode(',', $_POST['additionalGroups']) . '\'';
		}

		// Too often, people remove delete their own account, or something.
		if (in_array(1, explode(',', $old_profile['additionalGroups'])) || $old_profile['ID_GROUP'] == 1)
		{
			$stillAdmin = !isset($profile_vars['ID_GROUP']) || $profile_vars['ID_GROUP'] == 1 || (isset($_POST['additionalGroups']) && in_array(1, $_POST['additionalGroups']));

			// If they would no longer be an admin, look for any other...
			if (!$stillAdmin)
			{
				$request = db_query("
					SELECT ID_MEMBER
					FROM {$db_prefix}members
					WHERE (ID_GROUP = 1 OR FIND_IN_SET(1, additionalGroups))
						AND ID_MEMBER != $memID
					LIMIT 1", __FILE__, __LINE__);
				list ($another) = mysql_fetch_row($request);
				mysql_free_result($request);

				if (empty($another))
					fatal_lang_error('at_least_one_admin');
			}
		}
	}

	// Validate the language file...
	if (($changeIdentity || $changeOther) && isset($_POST['lngfile']) && !empty($modSettings['userLanguage']))
	{
		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
			$settings['actual_theme_dir'] . '/languages',
		);
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
				if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) && $matches[1] == $_POST['lngfile'])
				{
					$profile_vars['lngfile'] = "'$_POST[lngfile]'";

					// If they are the owner, make this persist even after they log out.
					if ($context['user']['is_owner'])
						$_SESSION['language'] = $_POST['lngfile'];
				}
			$dir->close();
		}
	}

	// Here's where we sort out all the 'other' values...
	if ($changeOther)
	{
		makeThemeChanges($memID, isset($_POST['ID_THEME']) ? (int) $_POST['ID_THEME'] : $old_profile['ID_THEME']);
		makeAvatarChanges($memID, $post_errors);
		makeNotificationChanges($memID);

		foreach ($profile_bools as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = empty($_POST[$var]) ? '0' : '1';
		foreach ($profile_ints as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = $_POST[$var] != '' ? (int) $_POST[$var] : '\'\'';
		foreach ($profile_floats as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = (float) $_POST[$var];
		foreach ($profile_strings as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = '\'' . $_POST[$var] . '\'';
	}

	if (isset($profile_vars['ICQ']) && $profile_vars['ICQ'] == '0')
		$profile_vars['ICQ'] = '\'\'';
}

// Make any theme changes that are sent with the profile..
function makeThemeChanges($memID, $ID_THEME)
{
	global $db_prefix, $modSettings;

	$reservedVars = array(
		'actual_theme_url',
		'actual_images_url',
		'base_theme_dir',
		'base_theme_url',
		'default_images_url',
		'default_theme_dir',
		'default_theme_url',
		'default_template',
		'images_url',
		'number_recent_posts',
		'smiley_sets_default',
		'theme_dir',
		'theme_id',
		'theme_layers',
		'theme_templates',
		'theme_url',
	);

	// Can't change reserved vars.
	if ((isset($_POST['options']) && array_intersect(array_keys($_POST['options']), $reservedVars) != array()) || (isset($_POST['default_options']) && array_intersect(array_keys($_POST['default_options']), $reservedVars) != array()))
		fatal_lang_error(1);

	// These are the theme changes...
	$themeSetArray = array();
	if (isset($_POST['options']) && is_array($_POST['options']))
	{
		foreach ($_POST['options'] as $opt => $val)
			$themeSetArray[] = '(' . $memID . ', ' . $ID_THEME . ", SUBSTRING('" . addslashes($opt) . "', 1, 255), SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534))";
	}

	$erase_options = array();
	if (isset($_POST['default_options']) && is_array($_POST['default_options']))
		foreach ($_POST['default_options'] as $opt => $val)
		{
			$themeSetArray[] = "($memID, 1, SUBSTRING('" . addslashes($opt) . "', 1, 255), SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534))";
			$erase_options[] = addslashes($opt);
		}

	// If themeSetArray isn't still empty, send it to the database.
	if (!empty($themeSetArray))
	{
		db_query("
			REPLACE INTO {$db_prefix}themes
				(ID_MEMBER, ID_THEME, variable, value)
			VALUES " . implode(",
				", $themeSetArray), __FILE__, __LINE__);
	}

	if (!empty($erase_options))
	{
		db_query("
			DELETE FROM {$db_prefix}themes
			WHERE ID_THEME != 1
				AND variable IN ('" . implode("', '", $erase_options) . "')
				AND ID_MEMBER = $memID", __FILE__, __LINE__);
	}

	$themes = explode(',', $modSettings['knownThemes']);
	foreach ($themes as $t)
		cache_put_data('theme_settings-' . $t . ':' . $memID, null, 60);
}

// Make any notification changes that need to be made.
function makeNotificationChanges($memID)
{
	global $db_prefix;

	// Update the boards they are being notified on.
	if (isset($_POST['edit_notify_boards']) && !empty($_POST['notify_boards']))
	{
		// Make sure only integers are deleted.
		foreach ($_POST['notify_boards'] as $index => $id)
			$_POST['notify_boards'][$index] = (int) $id;

		// ID_BOARD = 0 is reserved for topic notifications.
		$_POST['notify_boards'] = array_diff($_POST['notify_boards'], array(0));

		db_query("
			DELETE FROM {$db_prefix}log_notify
			WHERE ID_BOARD IN (" . implode(', ', $_POST['notify_boards']) . ")
				AND ID_MEMBER = $memID", __FILE__, __LINE__);
	}

	// We are editing topic notifications......
	elseif (isset($_POST['edit_notify_topics']) && !empty($_POST['notify_topics']))
	{
		foreach ($_POST['notify_topics'] as $index => $id)
			$_POST['notify_topics'][$index] = (int) $id;

		// Make sure there are no zeros left.
		$_POST['notify_topics'] = array_diff($_POST['notify_topics'], array(0));

		db_query("
			DELETE FROM {$db_prefix}log_notify
			WHERE ID_TOPIC IN (" . implode(', ', $_POST['notify_topics']) . ")
				AND ID_MEMBER = $memID", __FILE__, __LINE__);
	}
}

// The avatar is incredibly complicated, what with the options... and what not.
function makeAvatarChanges($memID, &$post_errors)
{
	global $modSettings, $sourcedir, $db_prefix;

	if (!isset($_POST['avatar_choice']) || empty($memID))
		return;

	require_once($sourcedir . '/ManageAttachments.php');

	$uploadDir = empty($modSettings['custom_avatar_enabled']) ? $modSettings['attachmentUploadDir'] : $modSettings['custom_avatar_dir'];

	$downloadedExternalAvatar = false;
	if ($_POST['avatar_choice'] == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && strlen($_POST['userpicpersonal']) > 7 && !empty($modSettings['avatar_download_external']))
	{
		if (!is_writable($uploadDir))
			fatal_lang_error('attachments_no_write');

		require_once($sourcedir . '/Subs-Package.php');

		$url = parse_url($_POST['userpicpersonal']);
		$contents = fetch_web_data('http://' . $url['host'] . (empty($url['port']) ? '' : ':' . $url['port']) . $url['path']);

		if ($contents != false && $tmpAvatar = fopen($uploadDir . '/avatar_tmp_' . $memID, 'wb'))
		{
			fwrite($tmpAvatar, $contents);
			fclose($tmpAvatar);

			$downloadedExternalAvatar = true;
			$_FILES['attachment']['tmp_name'] = $uploadDir . '/avatar_tmp_' . $memID;
		}
	}

	if ($_POST['avatar_choice'] == 'server_stored' && allowedTo('profile_server_avatar'))
	{
		$_POST['avatar'] = strtr(empty($_POST['file']) ? (empty($_POST['cat']) ? '' : $_POST['cat']) : $_POST['file'], array('&amp;' => '&'));
		$_POST['avatar'] = preg_match('~^([\w _!@%*=\-#()\[\]&.,]+/)?[\w _!@%*=\-#()\[\]&.,]+$~', $_POST['avatar']) != 0 && preg_match('/\.\./', $_POST['avatar']) == 0 && file_exists($modSettings['avatar_directory'] . '/' . $_POST['avatar']) ? ($_POST['avatar'] == 'blank.gif' ? '' : $_POST['avatar']) : '';

		// Get rid of their old avatar. (if uploaded.)
		removeAttachments('a.ID_MEMBER = ' . $memID);
	}
	elseif ($_POST['avatar_choice'] == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && empty($modSettings['avatar_download_external']))
	{
		// Remove any attached avatar...
		removeAttachments('a.ID_MEMBER = ' . $memID);

		$_POST['avatar'] = preg_replace('~action(=|%3d)(?!dlattach)~i', 'action-', $_POST['userpicpersonal']);

		if ($_POST['avatar'] == 'http://' || $_POST['avatar'] == 'http:///')
			$_POST['avatar'] = '';
		// Trying to make us do something we'll regret?
		elseif (substr($_POST['avatar'], 0, 7) != 'http://')
			$post_errors[] = 'bad_avatar';
		// Should we check dimensions?
		elseif (!empty($modSettings['avatar_max_height_external']) || !empty($modSettings['avatar_max_width_external']))
		{
			// Now let's validate the avatar.
			$sizes = url_image_size($_POST['avatar']);

			if (is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				// Houston, we have a problem. The avatar is too large!!
				if ($modSettings['avatar_action_too_large'] == 'option_refuse')
					$post_errors[] = 'bad_avatar';
				elseif ($modSettings['avatar_action_too_large'] == 'option_download_and_resize')
				{
					require_once($sourcedir . '/Subs-Graphics.php');
					if (downloadAvatar($_POST['avatar'], $memID, $modSettings['avatar_max_width_external'], $modSettings['avatar_max_height_external']))
						$_POST['avatar'] = '';
					else
						$post_errors[] = 'bad_avatar';
				}
			}
		}
	}
	elseif (($_POST['avatar_choice'] == 'upload' && allowedTo('profile_upload_avatar') ) || $downloadedExternalAvatar)
	{
		if ((isset($_FILES['attachment']['name']) && $_FILES['attachment']['name'] != '') || $downloadedExternalAvatar)
		{
			// Get the dimensions of the image.
			if (!$downloadedExternalAvatar)
			{
				if (!is_writable($uploadDir))
					fatal_lang_error('attachments_no_write');

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . '/avatar_tmp_' . $memID))
					fatal_lang_error('smf124');

				$_FILES['attachment']['tmp_name'] = $uploadDir . '/avatar_tmp_' . $memID;
			}

			$sizes = @getimagesize($_FILES['attachment']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				$post_errors[] = 'bad_avatar';
			// Check whether the image is too large.
			elseif ((!empty($modSettings['avatar_max_width_upload']) && $sizes[0] > $modSettings['avatar_max_width_upload']) || (!empty($modSettings['avatar_max_height_upload']) && $sizes[1] > $modSettings['avatar_max_height_upload']))
			{
				if (!empty($modSettings['avatar_resize_upload']))
				{
					// Attempt to chmod it.
					@chmod($uploadDir . '/avatar_tmp_' . $memID, 0644);

					require_once($sourcedir . '/Subs-Graphics.php');
					downloadAvatar($uploadDir . '/avatar_tmp_' . $memID, $memID, $modSettings['avatar_max_width_upload'], $modSettings['avatar_max_height_upload']);
				}
				else
					$post_errors[] = 'bad_avatar';
			}
			elseif (is_array($sizes))
			{
				// Though not an exhaustive list, better safe than sorry.
				$fp = fopen($_FILES['attachment']['tmp_name'], 'rb');
				if (!$fp)
					fatal_lang_error('smf124');

				// Now try to find an infection.
				while (!feof($fp))
				{
					if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script\W)~', fgets($fp, 4096)) === 1)
					{
						if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
							@unlink($uploadDir . '/avatar_tmp_' . $memID);

						fatal_lang_error('smf124');
					}
				}
				fclose($fp);

				$extensions = array(
					'1' => '.gif',
					'2' => '.jpg',
					'3' => '.png',
					'6' => '.bmp'
				);
				$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';

				$destName = 'avatar_' . $memID . $extension;
				list ($width, $height) = getimagesize($_FILES['attachment']['tmp_name']);

				// Remove previous attachments this member might have had.
				removeAttachments('a.ID_MEMBER = ' . $memID);

				$file_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, true) : '';

				db_query("
					INSERT INTO {$db_prefix}attachments
						(ID_MEMBER, attachmentType, filename, file_hash, size, width, height)
					VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', '" . (empty($file_hash) ? "" : "$file_hash") . "', " . filesize($_FILES['attachment']['tmp_name']) . ", " . (int) $width . ", " . (int) $height . ")", __FILE__, __LINE__);
				$attachID = db_insert_id();

				// Try to move this avatar.
				$destinationPath = $uploadDir . '/' . (empty($file_hash) ? $destName : $attachID . '_' . $file_hash);
				if (!rename($_FILES['attachment']['tmp_name'], $destinationPath))
				{
					// The move failed, get rid of it and die.
					db_query("
						DELETE FROM {$db_prefix}attachments
						WHERE ID_ATTACH = $attachID", __FILE__, __LINE__);

					fatal_lang_error('smf124');
				}

				// Attempt to chmod it.
				@chmod($destinationPath, 0644);
			}
			$_POST['avatar'] = '';

			// Delete any temporary file.
			if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
				@unlink($uploadDir . '/avatar_tmp_' . $memID);
		}
		// Selected the upload avatar option and had one already uploaded before or didn't upload one.
		else
			$_POST['avatar'] = '';
	}
	else
		$_POST['avatar'] = '';
}

// View a summary.
function summary($memID)
{
	global $context, $memberContext, $txt, $modSettings, $user_info, $user_profile, $sourcedir, $db_prefix, $scripturl;

	// Attempt to load the member's profile data.
	if (!loadMemberContext($memID) || !isset($memberContext[$memID]))
		fatal_error($txt[453] . ' - ' . $memID, false);

	// Set up the stuff and load the user.
	$context += array(
		'allow_hide_email' => !empty($modSettings['allow_hideEmail']),
		'page_title' => $txt[92] . ' ' . $memberContext[$memID]['name'],
		'can_send_pm' => allowedTo('pm_send'),
		'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
	);
	$context['member'] = &$memberContext[$memID];

	// They haven't even been registered for a full day!?
	$days_registered = (int) ((time() - $user_profile[$memID]['dateRegistered']) / (3600 * 24));
	if (empty($user_profile[$memID]['dateRegistered']) || $days_registered < 1)
		$context['member']['posts_per_day'] = $txt[470];
	else
		$context['member']['posts_per_day'] = comma_format($context['member']['real_posts'] / $days_registered, 3);

	// Set the age...
	if (empty($context['member']['birth_date']))
	{
		$context['member'] +=  array(
			'age' => &$txt[470],
			'today_is_birthday' => false
		);
	}
	else
	{
		list ($birth_year, $birth_month, $birth_day) = sscanf($context['member']['birth_date'], '%d-%d-%d');
		$datearray = getdate(forum_time());
		$context['member'] += array(
			'age' => $birth_year <= 4 ? $txt[470] : $datearray['year'] - $birth_year - (($datearray['mon'] > $birth_month || ($datearray['mon'] == $birth_month && $datearray['mday'] >= $birth_day)) ? 0 : 1),
			'today_is_birthday' => $datearray['mon'] == $birth_month && $datearray['mday'] == $birth_day
		);
	}

	if (allowedTo('moderate_forum'))
	{
		// Make sure it's a valid ip address; otherwise, don't bother...
		if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $memberContext[$memID]['ip']) == 1 && empty($modSettings['disableHostnameLookup']))
			$context['member']['hostname'] = host_from_ip($memberContext[$memID]['ip']);
		else
			$context['member']['hostname'] = '';

		$context['can_see_ip'] = true;
	}
	else
		$context['can_see_ip'] = false;

	if (!empty($modSettings['who_enabled']))
	{
		include_once($sourcedir . '/Who.php');
		$action = determineActions($user_profile[$memID]['url']);

		if ($action !== false)
			$context['member']['action'] = $action;
	}

	// If the user is awaiting activation, and the viewer has permission - setup some activation context messages.
	if ($context['member']['is_activated'] % 10 != 1 && allowedTo('moderate_forum'))
	{
		$context['activate_type'] = $context['member']['is_activated'];
		// What should the link text be?
		$context['activate_link_text'] = in_array($context['member']['is_activated'], array(3, 4, 5, 13, 14, 15)) ? $txt['account_approve'] : $txt['account_activate'];

		// Should we show a custom message?
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated'] % 10] : $txt['account_not_activated'];
	}

	// How about, are they banned?
	$context['member']['bans'] = array();
	if (allowedTo('moderate_forum'))
	{
		// Can they edit the ban?
		$context['can_edit_ban'] = allowedTo('manage_bans');

		$ban_query = array();
		$ban_query[] = "ID_MEMBER = " . $context['member']['id'];

		// Valid IP?
		if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $memberContext[$memID]['ip'], $ip_parts) == 1)
		{
			$ban_query[] = "(($ip_parts[1] BETWEEN bi.ip_low1 AND bi.ip_high1)
						AND ($ip_parts[2] BETWEEN bi.ip_low2 AND bi.ip_high2)
						AND ($ip_parts[3] BETWEEN bi.ip_low3 AND bi.ip_high3)
						AND ($ip_parts[4] BETWEEN bi.ip_low4 AND bi.ip_high4))";

			// Do we have a hostname already?
			if (!empty($context['member']['hostname']))
				$ban_query[] = "('" . addslashes($context['member']['hostname']) . "' LIKE hostname)";
		}
		// Use '255.255.255.255' for 'unknown' - it's not valid anyway.
		elseif ($memberContext[$memID]['ip'] == 'unknown')
			$ban_query[] = "(bi.ip_low1 = 255 AND bi.ip_high1 = 255
						AND bi.ip_low2 = 255 AND bi.ip_high2 = 255
						AND bi.ip_low3 = 255 AND bi.ip_high3 = 255
						AND bi.ip_low4 = 255 AND bi.ip_high4 = 255)";

		// Check their email as well...
		if (strlen($context['member']['email']) != 0)
			$ban_query[] = "('" . addslashes($context['member']['email']) . "' LIKE bi.email_address)";

		// So... are they banned?  Dying to know!
		$request = db_query("
			SELECT bg.ID_BAN_GROUP, bg.name, bg.cannot_access, bg.cannot_post, bg.cannot_register,
				bg.cannot_login, bg.reason
			FROM ({$db_prefix}ban_items AS bi, {$db_prefix}ban_groups AS bg)
			WHERE bg.ID_BAN_GROUP = bi.ID_BAN_GROUP
				AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . ")
				AND (" . implode(' OR ', $ban_query) . ')
			GROUP BY bg.ID_BAN_GROUP', __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			// Work out what restrictions we actually have.
			$ban_restrictions = array();
			foreach (array('access', 'register', 'login', 'post') as $type)
				if ($row['cannot_' . $type])
					$ban_restrictions[] = $txt['ban_type_' . $type];

			// No actual ban in place?
			if (empty($ban_restrictions))
				continue;

			// Prepare the link for context.
			$ban_explanation = sprintf($txt['user_cannot_due_to'], implode(', ', $ban_restrictions), '<a href="' . $scripturl . '?action=ban;sa=edit;bg=' . $row['ID_BAN_GROUP'] . '">' . $row['name'] . '</a>');

			$context['member']['bans'][] = array(
				'reason' => empty($row['reason']) ? '' : '<br /><br /><b>' . $txt['ban_reason'] . ':</b> ' . $row['reason'],
				'cannot' => array(
					'access' => !empty($row['cannot_access']),
					'register' => !empty($row['cannot_register']),
					'post' => !empty($row['cannot_post']),
					'login' => !empty($row['cannot_login']),
				),
				'explanation' => $ban_explanation,
			);
		}
		mysql_free_result($request);
	}
}

// Show all posts by the current user
function showPosts($memID)
{
	global $txt, $user_info, $scripturl, $modSettings, $db_prefix;
	global $context, $user_profile, $ID_MEMBER, $sourcedir;

	// If just deleting a message, do it and then redirect back.
	if (isset($_GET['delete']))
	{
		checkSession('get');

		// We can be lazy, since removeMessage() will check the permissions for us.
		require_once($sourcedir . '/RemoveTopic.php');
		removeMessage((int) $_GET['delete']);

		// Back to... where we are now ;).
		redirectexit('action=profile;u=' . $memID . ';sa=showPosts;start=' . $_GET['start']);
	}

	// Is the load average too high to allow searching just now?
	if (!empty($context['load_average']) && !empty($modSettings['loadavg_show_posts']) && $context['load_average'] >= $modSettings['loadavg_show_posts'])
		fatal_lang_error('loadavg_show_posts_disabled', false);

	// Default to 10.
	if (empty($_REQUEST['viewscount']) || !is_numeric($_REQUEST['viewscount']))
		$_REQUEST['viewscount'] = '10';

	$request = db_query("
		SELECT COUNT(*)
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE m.ID_MEMBER = $memID
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]", __FILE__, __LINE__);
	list ($msgCount) = mysql_fetch_row($request);
	mysql_free_result($request);

	$request = db_query("
		SELECT MIN(ID_MSG), MAX(ID_MSG)
		FROM {$db_prefix}messages AS m
		WHERE m.ID_MEMBER = $memID", __FILE__, __LINE__);
	list ($min_msg_member, $max_msg_member) = mysql_fetch_row($request);
	mysql_free_result($request);

	$reverse = false;
	$range_limit = '';
	$maxIndex = (int) $modSettings['defaultMaxMessages'];

	// Make sure the starting place makes sense and construct our friend the page index.
	$context['start'] = (int) $_REQUEST['start'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=showPosts', $context['start'], $msgCount, $maxIndex);
	$context['current_page'] = $context['start'] / $maxIndex;
	$context['current_member'] = $memID;

	// Reverse the query if we're past 50% of the pages for better performance.
	$start = $context['start'];
	$reverse = $_REQUEST['start'] > $msgCount / 2;
	if ($reverse)
	{
		$maxIndex = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 && $msgCount > $context['start'] ? $msgCount - $context['start'] : (int) $modSettings['defaultMaxMessages'];
		$start = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 || $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] ? 0 : $msgCount - $context['start'] - $modSettings['defaultMaxMessages'];
	}

	// Guess the range of messages to be shown.
	if ($msgCount > 1000)
	{
		$margin = floor(($max_msg_member - $min_msg_member) * (($start + $modSettings['defaultMaxMessages']) / $msgCount) + .1 * ($max_msg_member - $min_msg_member));
		$range_limit = $reverse ? 'ID_MSG < ' . ($min_msg_member + $margin) : 'ID_MSG > ' . ($max_msg_member - $margin);
	}

	$context['page_title'] = $txt[458] . ' ' . $user_profile[$memID]['realName'];

	// Find this user's posts.  The left join on categories somehow makes this faster, weird as it looks.
	$looped = false;
	while (true)
	{
		$request = db_query("
			SELECT
				b.ID_BOARD, b.name AS bname, c.ID_CAT, c.name AS cname, m.ID_TOPIC, m.ID_MSG,
				t.ID_MEMBER_STARTED, t.ID_FIRST_MSG, t.ID_LAST_MSG, m.body, m.smileysEnabled,
				m.subject, m.posterTime
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
				LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
			WHERE m.ID_MEMBER = $memID
				AND m.ID_TOPIC = t.ID_TOPIC
				AND t.ID_BOARD = b.ID_BOARD" . (empty($range_limit) ? '' : "
				AND $range_limit") . "
				AND $user_info[query_see_board]
			ORDER BY m.ID_MSG " . ($reverse ? 'ASC' : 'DESC') . "
			LIMIT $start, $maxIndex", __FILE__, __LINE__);

		// Make sure we quit this loop.
		if (mysql_num_rows($request) === $maxIndex || $looped)
			break;
		$looped = true;
		$range_limit = '';
	}

	// Start counting at the number of the first message displayed.
	$counter = $reverse ? $context['start'] + $maxIndex + 1 : $context['start'];
	$context['posts'] = array();
	$board_ids = array('own' => array(), 'any' => array());
	while ($row = mysql_fetch_assoc($request))
	{
		// Censor....
		censorText($row['body']);
		censorText($row['subject']);

		// Do the code.
		$row['body'] = parse_bbc($row['body'], $row['smileysEnabled'], $row['ID_MSG']);

		// And the array...
		$context['posts'][$counter += $reverse ? -1 : 1] = array(
			'body' => $row['body'],
			'counter' => $counter,
			'category' => array(
				'name' => $row['cname'],
				'id' => $row['ID_CAT']
			),
			'board' => array(
				'name' => $row['bname'],
				'id' => $row['ID_BOARD']
			),
			'topic' => $row['ID_TOPIC'],
			'subject' => $row['subject'],
			'start' => 'msg' . $row['ID_MSG'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime']),
			'id' => $row['ID_MSG'],
			'can_reply' => false,
			'can_mark_notify' => false,
			'can_delete' => false,
			'delete_possible' => ($row['ID_FIRST_MSG'] != $row['ID_MSG'] || $row['ID_LAST_MSG'] == $row['ID_MSG']) && (empty($modSettings['edit_disable_time']) || $row['posterTime'] + $modSettings['edit_disable_time'] * 60 >= time()),
		);

		if ($ID_MEMBER == $row['ID_MEMBER_STARTED'])
			$board_ids['own'][$row['ID_BOARD']][] = $counter;
		$board_ids['any'][$row['ID_BOARD']][] = $counter;
	}
	mysql_free_result($request);

	// All posts were retrieved in reverse order, get them right again.
	if ($reverse)
		$context['posts'] = array_reverse($context['posts'], true);

	// These are all the permissions that are different from board to board..
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

	// For every permission in the own/any lists...
	foreach ($permissions as $type => $list)
	{
		foreach ($list as $permission => $allowed)
		{
			// Get the boards they can do this on...
			$boards = boardsAllowedTo($permission);

			// Hmm, they can do it on all boards, can they?
			if (!empty($boards) && $boards[0] == 0)
				$boards = array_keys($board_ids[$type]);

			// Now go through each board they can do the permission on.
			foreach ($boards as $board_id)
			{
				// There aren't any posts displayed from this board.
				if (!isset($board_ids[$type][$board_id]))
					continue;

				// Set the permission to true ;).
				foreach ($board_ids[$type][$board_id] as $counter)
					$context['posts'][$counter][$allowed] = true;
			}
		}
	}

	// Clean up after posts that cannot be deleted.
	foreach ($context['posts'] as $counter => $dummy)
		$context['posts'][$counter]['can_delete'] &= $context['posts'][$counter]['delete_possible'];
}

// Show all the users buddies, as well as a add/delete interface.
function editBuddies($memID)
{
	global $txt, $scripturl, $modSettings, $db_prefix;
	global $context, $user_profile, $memberContext, $func;

	// Do a quick check to ensure people aren't getting here illegally!
	if (!$context['user']['is_owner'] || empty($modSettings['enable_buddylist']))
		fatal_lang_error(1, false);

	// !!! No page_title.

	// For making changes!
	$buddiesArray = explode(',', $user_profile[$memID]['buddy_list']);
	foreach ($buddiesArray as $k => $dummy)
		if ($dummy == '')
			unset($buddiesArray[$k]);

	// Removing a buddy?
	if (isset($_GET['remove']))
	{
		// Heh, I'm lazy, do it the easy way...
		foreach ($buddiesArray as $key => $buddy)
			if ($buddy == (int) $_GET['remove'])
				unset($buddiesArray[$key]);

		// Make the changes.
		$user_profile[$memID]['buddy_list'] = implode(',', $buddiesArray);
		updateMemberData($memID, array('buddy_list' => "'" . $user_profile[$memID]['buddy_list'] . "'"));

		// Redirect off the page because we don't like all this ugly query stuff to stick in the history.
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}
	elseif (isset($_POST['new_buddy']))
	{
		// Prepare the string for extraction...
		$_POST['new_buddy'] = strtr(addslashes($func['htmlspecialchars'](stripslashes($_POST['new_buddy']), ENT_QUOTES)), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['new_buddy'], $matches);
		$new_buddies = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['new_buddy']))));

		foreach ($new_buddies as $k => $dummy)
		{
			$new_buddies[$k] = strtr(trim($new_buddies[$k]), array('\\\'' => '&#039;'));

			if (strlen($new_buddies[$k]) == 0)
				unset($new_buddies[$k]);
		}

		if (!empty($new_buddies))
		{
			// Now find out the ID_MEMBER of the buddy.
			$request = db_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}members
				WHERE memberName IN ('" . implode("','", $new_buddies) . "') OR realName IN ('" . implode("','", $new_buddies) . "')
				LIMIT " . count($new_buddies), __FILE__, __LINE__);

			// Add the new member to the buddies array.
			while ($row = mysql_fetch_assoc($request))
				$buddiesArray[] = (int) $row['ID_MEMBER'];
			mysql_free_result($request);

			// Now update the current users buddy list.
			$user_profile[$memID]['buddy_list'] = implode(',', $buddiesArray);
			updateMemberData($memID, array('buddy_list' => "'" . $user_profile[$memID]['buddy_list'] . "'"));
		}

		// Back to the buddy list!
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}

	// Get all the users "buddies"...
	$buddies = array();

	if (!empty($buddiesArray))
	{
		$result = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE ID_MEMBER IN (" . implode(', ', $buddiesArray) . ")
			ORDER BY realName
			LIMIT " . (substr_count($user_profile[$memID]['buddy_list'], ',') + 1), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			$buddies[] = $row['ID_MEMBER'];
		mysql_free_result($result);
	}

	$context['buddy_count'] = count($buddies);

	// Load all the members up.
	loadMemberData($buddies, false, 'profile');

	// Setup the context for each buddy.
	$context['buddies'] = array();
	foreach ($buddies as $buddy)
	{
		loadMemberContext($buddy);
		$context['buddies'][$buddy] = $memberContext[$buddy];
	}
}

function statPanel($memID)
{
	global $txt, $scripturl, $db_prefix, $context, $user_profile, $user_info, $modSettings;

	$context['page_title'] = $txt['statPanel_showStats'] . ' ' . $user_profile[$memID]['realName'];

	// General user statistics.
	$timeDays = floor($user_profile[$memID]['totalTimeLoggedIn'] / 86400);
	$timeHours = floor(($user_profile[$memID]['totalTimeLoggedIn'] % 86400) / 3600);
	$context['time_logged_in'] = ($timeDays > 0 ? $timeDays . $txt['totalTimeLogged2'] : '') . ($timeHours > 0 ? $timeHours . $txt['totalTimeLogged3'] : '') . floor(($user_profile[$memID]['totalTimeLoggedIn'] % 3600) / 60) . $txt['totalTimeLogged4'];
	$context['num_posts'] = comma_format($user_profile[$memID]['posts']);

	// Number of topics started.
	// !!!SLOW This query is sorta slow...
	$result = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}topics
		WHERE ID_MEMBER_STARTED = $memID" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND ID_BOARD != $modSettings[recycle_board]" : ''), __FILE__, __LINE__);
	list ($context['num_topics']) = mysql_fetch_row($result);
	mysql_free_result($result);

	// Number polls started.
	$result = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}topics
		WHERE ID_MEMBER_STARTED = $memID" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND ID_BOARD != $modSettings[recycle_board]" : '') . "
			AND ID_POLL != 0", __FILE__, __LINE__);
	list ($context['num_polls']) = mysql_fetch_row($result);
	mysql_free_result($result);

	// Number polls voted in.
	$result = db_query("
		SELECT COUNT(DISTINCT ID_POLL)
		FROM {$db_prefix}log_polls
		WHERE ID_MEMBER = $memID", __FILE__, __LINE__);
	list ($context['num_votes']) = mysql_fetch_row($result);
	mysql_free_result($result);

	// Format the numbers...
	$context['num_topics'] = comma_format($context['num_topics']);
	$context['num_polls'] = comma_format($context['num_polls']);
	$context['num_votes'] = comma_format($context['num_votes']);

	// Grab the board this member posted in most often.
	$result = db_query("
		SELECT
			b.ID_BOARD, b.name, b.numPosts, COUNT(*) AS messageCount
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE m.ID_MEMBER = $memID
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]
		GROUP BY b.ID_BOARD
		ORDER BY messageCount DESC
		LIMIT 10", __FILE__, __LINE__);
	$context['popular_boards'] = array();
	$maxPosts = 0;
	while ($row = mysql_fetch_assoc($result))
	{
		if ($row['messageCount'] > $maxPosts)
			$maxPosts = $row['messageCount'];

		$context['popular_boards'][$row['ID_BOARD']] = array(
			'id' => $row['ID_BOARD'],
			'posts' => $row['messageCount'],
			'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>',
			'posts_percent' => 0,
			'total_posts' => $row['numPosts'],
		);
	}
	mysql_free_result($result);

	// Now that we know the total, calculate the percentage.
	foreach ($context['popular_boards'] as $ID_BOARD => $board_data)
		$context['popular_boards'][$ID_BOARD]['posts_percent'] = $board_data['total_posts'] == 0 ? 0 : comma_format(($board_data['posts'] * 100) / $board_data['total_posts'], 2);

	// Now get the 10 boards this user has most often participated in.
	$result = db_query("
		SELECT
			b.ID_BOARD, b.name, IF(COUNT(*) > b.numPosts, 1, COUNT(*) / b.numPosts) * 100 AS percentage
		FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE m.ID_MEMBER = $memID
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]
		GROUP BY b.ID_BOARD
		ORDER BY percentage DESC
		LIMIT 10", __FILE__, __LINE__);
	$context['board_activity'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$context['board_activity'][$row['ID_BOARD']] = array(
			'id' => $ID_BOARD,
			'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>',
			'percent' => $row['percentage'],
		);
	}
	mysql_free_result($result);

	// Posting activity by time.
	$result = db_query("
		SELECT
			HOUR(FROM_UNIXTIME(posterTime + " . (($user_info['time_offset'] + $modSettings['time_offset']) * 3600) . ")) AS hour,
			COUNT(*) AS postCount
		FROM {$db_prefix}messages
		WHERE ID_MEMBER = $memID" . ($modSettings['totalMessages'] > 100000 ? "
			AND ID_TOPIC > " . ($modSettings['totalTopics'] - 10000) : '') . "
		GROUP BY hour", __FILE__, __LINE__);
	$maxPosts = 0;
	$context['posts_by_time'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		if ($row['postCount'] > $maxPosts)
			$maxPosts = $row['postCount'];

		$context['posts_by_time'][$row['hour']] = array(
			'hour' => $row['hour'],
			'posts_percent' => $row['postCount']
		);
	}
	mysql_free_result($result);

	if ($maxPosts > 0)
		for ($hour = 0; $hour < 24; $hour++)
		{
			if (!isset($context['posts_by_time'][$hour]))
				$context['posts_by_time'][$hour] = array(
					'hour' => $hour,
					'posts_percent' => 0,
				);
			else
				$context['posts_by_time'][$hour]['posts_percent'] = round(($context['posts_by_time'][$hour]['posts_percent'] * 100) / $maxPosts);
		}

	// Put it in the right order.
	ksort($context['posts_by_time']);
}

function trackUser($memID)
{
	global $scripturl, $txt, $db_prefix, $modSettings;
	global $user_profile, $context;

	// Verify if the user has sufficient permissions.
	isAllowedTo('moderate_forum');

	$context['page_title'] = $txt['trackUser'] . ' - ' . $user_profile[$memID]['realName'];

	$context['last_ip'] = $user_profile[$memID]['memberIP'];
	$context['member']['name'] = $user_profile[$memID]['realName'];

	// If this is a big forum, or a large posting user, let's limit the search.
	if ($modSettings['totalMessages'] > 50000 && $user_profile[$memID]['posts'] > 500)
	{
		$request = db_query("
			SELECT MAX(ID_MSG)
			FROM {$db_prefix}messages AS m
			WHERE m.ID_MEMBER = $memID", __FILE__, __LINE__);
		list ($max_msg_member) = mysql_fetch_row($request);
		mysql_free_result($request);

		// There's no point worrying ourselves with messages made yonks ago, just get recent ones!
		$min_msg_member = max(0, $max_msg_member - $user_profile[$memID]['posts'] * 3);
	}

	// Default to at least the ones we know about.
	$ips = array(
		$user_profile[$memID]['memberIP'],
		$user_profile[$memID]['memberIP2'],
	);

	// Get all IP addresses this user has used for his messages.
	$request = db_query("
		SELECT posterIP
		FROM {$db_prefix}messages
		WHERE ID_MEMBER = $memID
		" . (isset($min_msg_member) ? "
			AND ID_MSG >= $min_msg_member AND ID_MSG <= $max_msg_member" : '') . "
		GROUP BY posterIP", __FILE__, __LINE__);
	$context['ips'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['ips'][] = '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['posterIP'] . '">' . $row['posterIP'] . '</a>';
		$ips[] = $row['posterIP'];
	}
	mysql_free_result($request);

	// Now also get the IP addresses from the error messages.
	$request = db_query("
		SELECT COUNT(*) AS errorCount, ip
		FROM {$db_prefix}log_errors
		WHERE ID_MEMBER = $memID
		GROUP BY ip", __FILE__, __LINE__);
	$context['error_ips'] = array();
	$totalErrors = 0;
	while ($row = mysql_fetch_assoc($request))
	{
		$context['error_ips'][] = '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['ip'] . '">' . $row['ip'] . '</a>';
		$ips[] = $row['ip'];
		$totalErrors += $row['errorCount'];
	}
	mysql_free_result($request);

	// Create the page indexes.
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=trackUser', $_REQUEST['start'], $totalErrors, 20);
	$context['start'] = $_REQUEST['start'];

	// Get a list of error messages from this ip (range).
	$request = db_query("
		SELECT
			le.logTime, le.ip, le.url, le.message, IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER,
			IFNULL(mem.realName, '$txt[28]') AS display_name, mem.memberName
		FROM {$db_prefix}log_errors AS le
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = le.ID_MEMBER)
		WHERE le.ID_MEMBER = $memID
		ORDER BY le.ID_ERROR DESC
		LIMIT $context[start], 20", __FILE__, __LINE__);
	$context['error_messages'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['error_messages'][] = array(
			'ip' => $row['ip'],
			'message' => strtr($row['message'], array('&lt;span class=&quot;remove&quot;&gt;' => '', '&lt;/span&gt;' => '')),
			'url' => $row['url'],
			'time' => timeformat($row['logTime']),
			'timestamp' => forum_time(true, $row['logTime'])
		);
	mysql_free_result($request);

	// Find other users that might use the same IP.
	$ips = array_unique($ips);
	$context['members_in_range'] = array();
	if (!empty($ips))
	{
		$request = db_query("
			SELECT ID_MEMBER, realName
			FROM {$db_prefix}members
			WHERE ID_MEMBER != $memID
				AND memberIP IN ('" . implode("', '", $ips) . "')", __FILE__, __LINE__);
		if (mysql_num_rows($request) > 0)
			while ($row = mysql_fetch_assoc($request))
				$context['members_in_range'][$row['ID_MEMBER']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';
		mysql_free_result($request);

		$request = db_query("
			SELECT mem.ID_MEMBER, mem.realName
			FROM ({$db_prefix}messages AS m, {$db_prefix}members AS mem)
			WHERE mem.ID_MEMBER = m.ID_MEMBER
				AND mem.ID_MEMBER != $memID
				AND m.posterIP IN ('" . implode("', '", $ips) . "')", __FILE__, __LINE__);
		if (mysql_num_rows($request) > 0)
			while ($row = mysql_fetch_assoc($request))
				$context['members_in_range'][$row['ID_MEMBER']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';
		mysql_free_result($request);
	}
}

function TrackIP($memID = 0)
{
	global $user_profile, $scripturl, $txt, $user_info;
	global $db_prefix, $context;

	// Can the user do this?
	isAllowedTo('moderate_forum');

	if ($memID == 0)
	{
		$context['ip'] = isset($_REQUEST['searchip']) ? trim($_REQUEST['searchip']) : $user_info['ip'];
		loadTemplate('Profile');
		loadLanguage('Profile');
		$context['sub_template'] = 'trackIP';
		$context['page_title'] = $txt[79];
	}
	else
		$context['ip'] = $user_profile[$memID]['memberIP'];

	if (preg_match('/^\d{1,3}\.(\d{1,3}|\*)\.(\d{1,3}|\*)\.(\d{1,3}|\*)$/', $context['ip']) == 0)
		fatal_error($txt['invalid_ip'], false);

	$dbip = str_replace('*', '%', $context['ip']);
	$dbip = strpos($dbip, '%') === false ? "= '$dbip'" : "LIKE '$dbip'";

	$context['page_title'] = $txt['trackIP'] . ' - ' . $context['ip'];

	// Get some totals for pagination.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
		WHERE $user_info[query_see_board]
			AND m.posterIP $dbip", __FILE__, __LINE__);
	list ($totalMessages) = mysql_fetch_row($request);
	mysql_free_result($request);
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}log_errors
		WHERE ip $dbip", __FILE__, __LINE__);
	list ($totalErrors) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['message_start'] = isset($_GET['mesStart']) ? (int) $_GET['mesStart'] : 0;
	$context['error_start'] = isset($_GET['errStart']) ? $_GET['errStart'] : 0;
	$context['message_page_index'] = constructPageIndex($scripturl . '?action=' . ($memID == 0 ? 'trackip;searchip=' . $context['ip'] : 'profile;u=' . $memID . ';sa=trackIP') . ';mesStart=%d;errStart=' . $context['error_start'], $context['message_start'], $totalMessages, 20, true);
	$context['error_page_index'] = constructPageIndex($scripturl . '?action=' . ($memID == 0 ? 'trackip;searchip=' . $context['ip'] : 'profile;u=' . $memID . ';sa=trackIP') . ';mesStart=' . $context['message_start'] . ';errStart=%d', $context['error_start'], $totalErrors, 20, true);

	$request = db_query("
		SELECT ID_MEMBER, realName AS display_name, memberIP
		FROM {$db_prefix}members
		WHERE memberIP $dbip", __FILE__, __LINE__);
	$context['ips'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['ips'][$row['memberIP']][] = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['display_name'] . '</a>';
	mysql_free_result($request);

	ksort($context['ips']);

	// !!!SLOW This query is using a filesort.
	$request = db_query("
		SELECT
			m.ID_MSG, m.posterIP, IFNULL(mem.realName, m.posterName) AS display_name, mem.ID_MEMBER,
			m.subject, m.posterTime, m.ID_TOPIC, m.ID_BOARD
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = m.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE $user_info[query_see_board]
			AND m.posterIP $dbip
		ORDER BY m.ID_MSG DESC
		LIMIT $context[message_start], 20", __FILE__, __LINE__);
	$context['messages'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['messages'][] = array(
			'ip' => $row['posterIP'],
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['display_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['display_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['display_name'] . '</a>'
			),
			'board' => array(
				'id' => $row['ID_BOARD'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD']
			),
			'topic' => $row['ID_TOPIC'],
			'id' => $row['ID_MSG'],
			'subject' => $row['subject'],
			'time' => timeformat($row['posterTime']),
			'timestamp' => forum_time(true, $row['posterTime'])
		);
	mysql_free_result($request);

	// !!!SLOW This query is using a filesort.
	$request = db_query("
		SELECT
			le.logTime, le.ip, le.url, le.message, IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER,
			IFNULL(mem.realName, '$txt[28]') AS display_name, mem.memberName
		FROM {$db_prefix}log_errors AS le
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = le.ID_MEMBER)
		WHERE le.ip $dbip
		ORDER BY le.ID_ERROR DESC
		LIMIT $context[error_start], 20", __FILE__, __LINE__);
	$context['error_messages'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['error_messages'][] = array(
			'ip' => $row['ip'],
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['display_name'],
				'href' => $row['ID_MEMBER'] > 0 ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
				'link' => $row['ID_MEMBER'] > 0 ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['display_name'] . '</a>' : $row['display_name']
			),
			'message' => strtr($row['message'], array('&lt;span class=&quot;remove&quot;&gt;' => '', '&lt;/span&gt;' => '')),
			'url' => $row['url'],
			'error_time' => timeformat($row['logTime'])
		);
	mysql_free_result($request);

	$context['single_ip'] = strpos($context['ip'], '*') === false;
	if ($context['single_ip'])
	{
		$context['whois_servers'] = array(
			'afrinic' => array(
				'name' => &$txt['whois_afrinic'],
				'url' => 'http://www.afrinic.net/cgi-bin/whois?searchtext=' . $context['ip'],
				'range' => array(),
			),
			'apnic' => array(
				'name' => &$txt['whois_apnic'],
				'url' => 'http://wq.apnic.net/apnic-bin/whois.pl?searchtext=' . $context['ip'],
				'range' => array(58, 59, 60, 61, 124, 125, 126, 202, 203, 210, 211, 218, 219, 220, 221, 222),
			),
			'arin' => array(
				'name' => &$txt['whois_arin'],
				'url' => 'http://ws.arin.net/whois/?queryinput=' . $context['ip'],
				'range' => array(63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 199, 204, 205, 206, 207, 208, 209, 216),
			),
			'lacnic' => array(
				'name' => &$txt['whois_lacnic'],
				'url' => 'http://lacnic.net/cgi-bin/lacnic/whois?query=' . $context['ip'],
				'range' => array(200, 201),
			),
			'ripe' => array(
				'name' => &$txt['whois_ripe'],
				'url' => 'http://www.db.ripe.net/whois?searchtext=' . $context['ip'],
				'range' => array(62, 80, 81, 82, 83, 84, 85, 86, 87, 88, 193, 194, 195, 212, 213, 217),
			),
		);

		foreach ($context['whois_servers'] as $whois)
		{
			// Strip off the "decimal point" and anything following...
			if (in_array((int) $context['ip'], $whois['range']))
				$context['auto_whois_server'] = $whois;
		}
	}
}

function showPermissions($memID)
{
	global $scripturl, $txt, $db_prefix, $board, $modSettings;
	global $user_profile, $context, $user_info;

	// Verify if the user has sufficient permissions.
	isAllowedTo('manage_permissions');

	loadLanguage('ManagePermissions');
	loadLanguage('Admin');
	loadTemplate('ManageMembers');

	$context['member']['id'] = $memID;
	$context['member']['name'] = $user_profile[$memID]['realName'];

	$context['page_title'] = $txt['showPermissions'];
	$board = empty($board) ? 0 : (int) $board;
	$context['board'] = $board;

	// Determine which groups this user is in.
	if (empty($user_profile[$memID]['additionalGroups']))
		$curGroups = array();
	else
		$curGroups = explode(',', $user_profile[$memID]['additionalGroups']);
	$curGroups[] = $user_profile[$memID]['ID_GROUP'];
	$curGroups[] = $user_profile[$memID]['ID_POST_GROUP'];

	// Load a list of boards for the jump box (but only those that have separate local permissions).
	$request = db_query("
		SELECT b.ID_BOARD, b.name, b.permission_mode, b.memberGroups, b.permission_mode != 0 OR mods.ID_MEMBER IS NOT NULL AS show_board
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = $memID)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['boards'] = array();
	$context['no_access_boards'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (count(array_intersect($curGroups, explode(',', $row['memberGroups']))) === 0)
			$context['no_access_boards'][] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'is_last' => false,
			);
		elseif (!empty($row['show_board']))
			$context['boards'][$row['ID_BOARD']] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'selected' => $board == $row['ID_BOARD'],
				'permission_mode' => $row['permission_mode'],
			);
	}
	mysql_free_result($request);

	if (!empty($context['no_access_boards']))
		$context['no_access_boards'][count($context['no_access_boards']) - 1]['is_last'] = true;

	$context['member']['permissions'] = array(
		'general' => array(),
		'board' => array()
	);

	// If you're an admin we know you can do everything, we might as well leave.
	$context['member']['has_all_permissions'] = in_array(1, $curGroups);
	if ($context['member']['has_all_permissions'])
		return;

	$denied = array();

	// Get all general permissions.
	$result = db_query("
		SELECT p.permission, p.addDeny, mg.groupName, p.ID_GROUP
		FROM {$db_prefix}permissions AS p
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = p.ID_GROUP)
		WHERE p.ID_GROUP IN (" . implode(', ', $curGroups) . ")
		ORDER BY p.addDeny DESC, p.permission, mg.minPosts, IF(mg.ID_GROUP < 4, mg.ID_GROUP, 4), mg.groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
	{
		// We don't know about this permission, it doesn't exist :P.
		if (!isset($txt['permissionname_' . $row['permission']]))
			continue;

		if (empty($row['addDeny']))
			$denied[] = $row['permission'];

		// Permissions that end with _own or _any consist of two parts.
		if (in_array(substr($row['permission'], -4), array('_own', '_any')) && isset($txt['permissionname_' . substr($row['permission'], 0, -4)]))
			$name = $txt['permissionname_' . substr($row['permission'], 0, -4)] . ' - ' . $txt['permissionname_' . $row['permission']];
		else
			$name = $txt['permissionname_' . $row['permission']];

		// Add this permission if it doesn't exist yet.
		if (!isset($context['member']['permissions']['general'][$row['permission']]))
			$context['member']['permissions']['general'][$row['permission']] = array(
				'id' => $row['permission'],
				'groups' => array(
					'allowed' => array(),
					'denied' => array()
				),
				'name' => $name,
				'is_denied' => false,
				'is_global' => true,
			);

		// Add the membergroup to either the denied or the allowed groups.
		$context['member']['permissions']['general'][$row['permission']]['groups'][empty($row['addDeny']) ? 'denied' : 'allowed'][] = $row['ID_GROUP'] == 0 ? $txt['membergroups_members'] : $row['groupName'];

		// Once denied is always denied.
		$context['member']['permissions']['general'][$row['permission']]['is_denied'] |= empty($row['addDeny']);
	}
	mysql_free_result($result);

	$request = db_query("
		SELECT
			bp.addDeny, bp.permission, bp.ID_GROUP, mg.groupName" . (empty($board) ? '' : ',
			b.permission_mode, IF(mods.ID_MEMBER IS NULL, 0, 1) AS is_moderator') . "
		FROM ({$db_prefix}board_permissions AS bp" . (empty($board) ? ')' : ", {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD AND mods.ID_MEMBER = $memID)") . "
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = bp.ID_GROUP)
		WHERE bp.ID_BOARD = " . (empty($modSettings['permission_enable_by_board']) || empty($board) ? '0' : 'IF(b.permission_mode = 1, b.ID_BOARD, 0)') . "
			AND bp.ID_GROUP IN (" . implode(', ', $curGroups) . "" . (empty($board) ? ')' : ", 3)
			AND b.ID_BOARD = $board
			AND (mods.ID_MEMBER IS NOT NULL OR bp.ID_GROUP != 3)"), __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($request))
	{
		// We don't know about this permission, it doesn't exist :P.
		if (!isset($txt['permissionname_' . $row['permission']]))
			continue;

		// Filter these special cases of board permissions out.
		if (empty($modSettings['permission_enable_by_board']) && !empty($board) && $row['ID_GROUP'] != 3)
		{
			if (in_array($row['permission'], array('post_reply_own', 'post_reply_any')) && $row['permission_mode'] == 4)
				continue;
			elseif ($row['permission'] == 'post_new' && $row['permission_mode'] >= 3)
				continue;
			elseif ($row['permission'] == 'poll_post' && $row['permission_mode'] >= 2)
				continue;
		}

		// The name of the permission using the format 'permission name' - 'own/any topic/event/etc.'.
		if (in_array(substr($row['permission'], -4), array('_own', '_any')) && isset($txt['permissionname_' . substr($row['permission'], 0, -4)]))
			$name = $txt['permissionname_' . substr($row['permission'], 0, -4)] . ' - ' . $txt['permissionname_' . $row['permission']];
		else
			$name = $txt['permissionname_' . $row['permission']];

		// Create the structure for this permission.
		if (!isset($context['member']['permissions']['board'][$row['permission']]))
			$context['member']['permissions']['board'][$row['permission']] = array(
				'id' => $row['permission'],
				'groups' => array(
					'allowed' => array(),
					'denied' => array()
				),
				'name' => $name,
				'is_denied' => false,
				'is_global' => empty($board),
			);

		$context['member']['permissions']['board'][$row['permission']]['groups'][empty($row['addDeny']) ? 'denied' : 'allowed'][$row['ID_GROUP']] = $row['ID_GROUP'] == 0 ? $txt['membergroups_members'] : $row['groupName'];

		$context['member']['permissions']['board'][$row['permission']]['is_denied'] |= empty($row['addDeny']);
	}
	mysql_free_result($request);
}

function account($memID)
{
	global $context, $settings, $user_profile, $txt, $db_prefix;
	global $scripturl, $membergroups, $modSettings, $language, $user_info;
	global $func;

	// Allow an administrator to edit the username?
	$context['allow_edit_username'] = isset($_GET['changeusername']) && allowedTo('admin_forum');

	// You might be allowed to only assign the membergroups, so let's check.
	$context['allow_edit_membergroups'] = allowedTo('manage_membergroups');
	$context['allow_edit_account'] = ($context['user']['is_owner'] && allowedTo('profile_identity_own')) || allowedTo('profile_identity_any');

	// How about their email address... online status, and name?
	$context['allow_hide_email'] = !empty($modSettings['allow_hideEmail']) || allowedTo('moderate_forum');
	$context['allow_hide_online'] = !empty($modSettings['allow_hideOnline']) || allowedTo('moderate_forum');
	$context['allow_edit_name'] = !empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum');

	// Load up the existing contextual data.
	$context['member'] += array(
		'is_admin' => !empty($user_profile[$memID]['ID_GROUP']) && $user_profile[$memID]['ID_GROUP'] == 1,
		'secret_question' => !isset($user_profile[$memID]['secretQuestion']) ? '' : $user_profile[$memID]['secretQuestion'],
	);

	// You need 'manage membergroups' permission for this.
	if ($context['allow_edit_membergroups'])
	{
		$context['member_groups'] = array(
			0 => array(
				'id' => 0,
				'name' => &$txt['no_primary_membergroup'],
				'is_primary' => $user_profile[$memID]['ID_GROUP'] == 0,
				'can_be_additional' => false,
			)
		);
		$curGroups = explode(',', $user_profile[$memID]['additionalGroups']);

		// Load membergroups, but only those groups the user can assign.
		$request = db_query("
			SELECT groupName, ID_GROUP
			FROM {$db_prefix}membergroups
			WHERE ID_GROUP != 3
				AND minPosts = -1
			ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			// We should skip the administrator group if they don't have the admin_forum permission!
			if ($row['ID_GROUP'] == 1 && !allowedTo('admin_forum'))
				continue;

			$context['member_groups'][$row['ID_GROUP']] = array(
				'id' => $row['ID_GROUP'],
				'name' => $row['groupName'],
				'is_primary' => $user_profile[$memID]['ID_GROUP'] == $row['ID_GROUP'],
				'is_additional' => in_array($row['ID_GROUP'], $curGroups),
				'can_be_additional' => true,
			);
		}
		mysql_free_result($request);
	}

	// Are languages user selectable?  If so, get a list.
	$context['languages'] = array();
	if ($context['allow_edit_account'] && !empty($modSettings['userLanguage']))
	{
		// Select the default language if the user has no language selected yet.
		$selectedLanguage = empty($user_profile[$memID]['lngfile']) ? $language : $user_profile[$memID]['lngfile'];

		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
			$settings['actual_theme_dir'] . '/languages',
		);
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
			{
				// Each language file must *at least* have a 'index.LANGUAGENAME.php' file.
				if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) == 0)
					continue;

				$context['languages'][$matches[1]] = array(
					'name' => $func['ucwords'](strtr($matches[1], array('_' => ' ', '-utf8' => ''))),
					'selected' => $selectedLanguage == $matches[1],
					'filename' => $matches[1],
				);
			}
			$dir->close();
		}
	}

	loadThemeOptions($memID);
}

function forumProfile($memID)
{
	global $context, $user_profile;
	global $user_info, $txt, $ID_MEMBER, $modSettings;

	$context['avatar_url'] = $modSettings['avatar_url'];
	$context['max_signature_length'] = $modSettings['max_signatureLength'];
	$context['allow_edit_title'] = allowedTo('profile_title_any') || (allowedTo('profile_title_own') && $context['user']['is_owner']);

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	$context['member'] += array(
		'birth_date' => empty($user_profile[$memID]['birthdate']) || $user_profile[$memID]['birthdate'] === '0001-01-01' ? '0000-00-00' : (substr($user_profile[$memID]['birthdate'], 0, 4) === '0004' ? '0000' . substr($user_profile[$memID]['birthdate'], 4) : $user_profile[$memID]['birthdate']),
		'location' => !isset($user_profile[$memID]['location']) ? '' : $user_profile[$memID]['location'],
		'title' => !isset($user_profile[$memID]['usertitle']) || $user_profile[$memID]['usertitle'] == '' ? '' : $user_profile[$memID]['usertitle'],
		'blurb' => !isset($user_profile[$memID]['personalText']) ? '' : str_replace(array('<', '>', '&amp;#039;'), array('&lt;', '&gt;', '&#039;'), $user_profile[$memID]['personalText']),
		'signature' => !isset($user_profile[$memID]['signature']) ? '' : str_replace(array('<br />', '<', '>', '"', '\''), array("\n", '&lt;', '&gt;', '&quot;', '&#039;'), $user_profile[$memID]['signature']),
	);

	// Split up the birthdate....
	list ($uyear, $umonth, $uday) = explode('-', $context['member']['birth_date']);
	$context['member']['birth_date'] = array(
		'year' => $uyear,
		'month' => $umonth,
		'day' => $uday
	);

	if ($user_profile[$memID]['avatar'] == '' && $user_profile[$memID]['ID_ATTACH'] > 0 && $context['member']['avatar']['allow_upload'])
		$context['member']['avatar'] += array(
			'choice' => 'upload',
			'server_pic' => 'blank.gif',
			'external' => 'http://'
		);
	elseif (stristr($user_profile[$memID]['avatar'], 'http://') && $context['member']['avatar']['allow_external'])
		$context['member']['avatar'] += array(
			'choice' => 'external',
			'server_pic' => 'blank.gif',
			'external' => $user_profile[$memID]['avatar']
		);
	elseif (file_exists($modSettings['avatar_directory'] . '/' . $user_profile[$memID]['avatar']) && $context['member']['avatar']['allow_server_stored'])
		$context['member']['avatar'] += array(
			'choice' => 'server_stored',
			'server_pic' => $user_profile[$memID]['avatar'] == '' ? 'blank.gif' : $user_profile[$memID]['avatar'],
			'external' => 'http://'
		);
	else
		$context['member']['avatar'] += array(
			'choice' => 'server_stored',
			'server_pic' => 'blank.gif',
			'external' => 'http://'
		);

	// Get a list of all the avatars.
	if ($context['member']['avatar']['allow_server_stored'])
	{
		$context['avatar_list'] = array();
		$context['avatars'] = is_dir($modSettings['avatar_directory']) ? getAvatars('', 0) : array();
	}
	else
		$context['avatars'] = array();

	// Second level selected avatar...
	$context['avatar_selected'] = substr(strrchr($context['member']['avatar']['server_pic'], '/'), 1);

	loadThemeOptions($memID);
}

// Recursive function to retrieve avatar files
function getAvatars($directory, $level)
{
	global $context, $txt, $modSettings;

	$result = array();

	// Open the directory..
	$dir = dir($modSettings['avatar_directory'] . (!empty($directory) ? '/' : '') . $directory);
	$dirs = array();
	$files = array();

	if (!$dir)
		return array();

	while ($line = $dir->read())
	{
		if (in_array($line, array('.', '..', 'blank.gif', 'index.php')))
			continue;

		if (is_dir($modSettings['avatar_directory'] . '/' . $directory . (!empty($directory) ? '/' : '') . $line))
			$dirs[] = $line;
		else
			$files[] = $line;
	}
	$dir->close();

	// Sort the results...
	natcasesort($dirs);
	natcasesort($files);

	if ($level == 0)
	{
		$result[] = array(
			'filename' => 'blank.gif',
			'checked' => in_array($context['member']['avatar']['server_pic'], array('', 'blank.gif')),
			'name' => &$txt[422],
			'is_dir' => false
		);
	}

	foreach ($dirs as $line)
	{
		$tmp = getAvatars($directory . (!empty($directory) ? '/' : '') . $line, $level + 1);
		if (!empty($tmp))
			$result[] = array(
				'filename' => htmlspecialchars($line),
				'checked' => strpos($context['member']['avatar']['server_pic'], $line . '/') !== false,
				'name' => '[' . htmlspecialchars(str_replace('_', ' ', $line)) . ']',
				'is_dir' => true,
				'files' => $tmp
		);
		unset($tmp);
	}

	foreach ($files as $line)
	{
		$filename = substr($line, 0, (strlen($line) - strlen(strrchr($line, '.'))));
		$extension = substr(strrchr($line, '.'), 1);

		// Make sure it is an image.
		if (strcasecmp($extension, 'gif') != 0 && strcasecmp($extension, 'jpg') != 0 && strcasecmp($extension, 'jpeg') != 0 && strcasecmp($extension, 'png') != 0 && strcasecmp($extension, 'bmp') != 0)
			continue;

		$result[] = array(
			'filename' => htmlspecialchars($line),
			'checked' => $line == $context['member']['avatar']['server_pic'],
			'name' => htmlspecialchars(str_replace('_', ' ', $filename)),
			'is_dir' => false
		);
		if ($level == 1)
			$context['avatar_list'][] = $directory . '/' . $line;
	}

	return $result;
}

function theme($memID)
{
	global $txt, $context, $user_profile, $db_prefix, $modSettings, $settings, $user_info;

	$request = db_query("
		SELECT value
		FROM {$db_prefix}themes
		WHERE ID_THEME = " . (int) $user_profile[$memID]['ID_THEME'] . "
			AND variable = 'name'
		LIMIT 1", __FILE__, __LINE__);
	list ($name) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['member'] += array(
		'theme' => array(
			'id' => $user_profile[$memID]['ID_THEME'],
			'name' => empty($user_profile[$memID]['ID_THEME']) ? $txt['theme_forum_default'] : $name
		),
		'smiley_set' => array(
			'id' => empty($user_profile[$memID]['smileySet']) ? '' : $user_profile[$memID]['smileySet']
		),
		'time_format' => !isset($user_profile[$memID]['timeFormat']) ? '' : $user_profile[$memID]['timeFormat'],
		'time_offset' => empty($user_profile[$memID]['timeOffset']) ? '0' : $user_profile[$memID]['timeOffset'],
	);

	$context['easy_timeformats'] = array(
		array('format' => '', 'title' => $txt['timeformat_easy0']),
		array('format' => '%B %d, %Y, %I:%M:%S %p', 'title' => $txt['timeformat_easy1']),
		array('format' => '%B %d, %Y, %H:%M:%S', 'title' => $txt['timeformat_easy2']),
		array('format' => '%Y-%m-%d, %H:%M:%S', 'title' => $txt['timeformat_easy3']),
		array('format' => '%d %B %Y, %H:%M:%S', 'title' => $txt['timeformat_easy4']),
		array('format' => '%d-%m-%Y, %H:%M:%S', 'title' => $txt['timeformat_easy5'])
	);

	$context['current_forum_time'] = timeformat(time() - $user_info['time_offset'] * 3600, false);
	$context['current_forum_time_hour'] = (int) strftime('%H', forum_time(false));

	$context['smiley_sets'] = explode(',', 'none,,' . $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $txt['smileys_none'] . "\n" . $txt['smileys_forum_board_default'] . "\n" . $modSettings['smiley_sets_names']);
	foreach ($context['smiley_sets'] as $i => $set)
	{
		$context['smiley_sets'][$i] = array(
			'id' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $context['member']['smiley_set']['id']
		);

		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = htmlspecialchars($set_names[$i]);
	}

	loadThemeOptions($memID);

	loadLanguage('Settings');
}

// Display the notifications and settings for changes.
function notification($memID)
{
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $ID_MEMBER, $modSettings;

	// All the boards with notification on..
	$request = db_query("
		SELECT b.ID_BOARD, b.name, IFNULL(lb.ID_MSG, 0) AS boardRead, b.ID_MSG_UPDATED
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
		WHERE ln.ID_MEMBER = $memID
			AND b.ID_BOARD = ln.ID_BOARD
			AND $user_info[query_see_board]
		ORDER BY b.boardOrder", __FILE__, __LINE__);
	$context['board_notifications'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['board_notifications'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>',
			'new' => $row['boardRead'] < $row['ID_MSG_UPDATED']
		);
	mysql_free_result($request);

	$request = db_query("
		SELECT COUNT(*)
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}boards AS b, {$db_prefix}topics AS t)
		WHERE ln.ID_MEMBER = $memID
			AND t.ID_TOPIC = ln.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]", __FILE__, __LINE__);
	list ($num_topics) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=notification', $_REQUEST['start'], $num_topics, $modSettings['defaultMaxMessages']);

	// All the topics with notification on...
	$request = db_query("
		SELECT
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from, b.ID_BOARD, b.name,
			t.ID_TOPIC, ms.subject, ms.ID_MEMBER, IFNULL(mem.realName, ms.posterName) AS realName,
			ml.ID_MSG_MODIFIED
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}boards AS b, {$db_prefix}topics AS t, {$db_prefix}messages AS ms, {$db_prefix}messages AS ml)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = ms.ID_MEMBER)
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = b.ID_BOARD AND lmr.ID_MEMBER = $ID_MEMBER)
		WHERE ln.ID_MEMBER = $memID
			AND t.ID_TOPIC = ln.ID_TOPIC
			AND ms.ID_MSG = t.ID_FIRST_MSG
			AND ml.ID_MSG = t.ID_LAST_MSG
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]
		ORDER BY ms.ID_MSG DESC
		LIMIT $_REQUEST[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['topic_notifications'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		censorText($row['subject']);

		$context['topic_notifications'][] = array(
			'id' => $row['ID_TOPIC'],
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['realName'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['realName'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>'
			),
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['subject'] . '</a>',
			'new' => $row['new_from'] <= $row['ID_MSG_MODIFIED'],
			'new_from' => $row['new_from'],
			'new_href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['new_from'] . '#new',
			'new_link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['new_from'] . '#new">' . $row['subject'] . '</a>',
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>'
			)
		);
	}
	mysql_free_result($request);

	// What options are set?
	$context['member'] += array(
		'notify_announcements' => $user_profile[$memID]['notifyAnnouncements'],
		'notify_once' => $user_profile[$memID]['notifyOnce'],
		'notify_send_body' => $user_profile[$memID]['notifySendBody'],
		'notify_types' => $user_profile[$memID]['notifyTypes'],
	);

	// How many rows can we expect?
	$context['num_rows'] = array(
		'topic' => count($context['topic_notifications']) + 3,
		'board' => count($context['board_notifications']) + 2
	);

	loadThemeOptions($memID);
}

function pmprefs($memID)
{
	global $txt, $user_profile, $db_prefix, $context, $db_prefix;

	// Tell the template what they are....
	$context['send_email'] = $user_profile[$memID]['pm_email_notify'];

	if ($user_profile[$memID]['pm_ignore_list'] != '*')
	{
		$result = db_query("
			SELECT realName
			FROM {$db_prefix}members
			WHERE FIND_IN_SET(ID_MEMBER, '" . $user_profile[$memID]['pm_ignore_list'] . "')
			LIMIT " . (substr_count($user_profile[$memID]['pm_ignore_list'], ',') + 1), __FILE__, __LINE__);
		$pm_ignore_list = '';
		while ($row = mysql_fetch_assoc($result))
			$pm_ignore_list .= "\n" . $row['realName'];
		mysql_free_result($result);

		$pm_ignore_list = substr($pm_ignore_list, 1);
	}
	else
		$pm_ignore_list = '*';

	// Get all their "buddies"...
	$result = db_query("
		SELECT realName
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(ID_MEMBER, '" . $user_profile[$memID]['buddy_list'] . "')
		LIMIT " . (substr_count($user_profile[$memID]['buddy_list'], ',') + 1), __FILE__, __LINE__);
	$buddy_list = '';
	while ($row = mysql_fetch_assoc($result))
		$buddy_list .= "\n" . $row['realName'];
	mysql_free_result($result);

	$context['buddy_list'] = substr($buddy_list, 1);
	$context['ignore_list'] = $pm_ignore_list;
	$context['page_title'] = $txt['pmprefs'] . ': ' . $txt[144];

	loadThemeOptions($memID);
}

// Present a screen to make sure the user wants to be deleted
function deleteAccount($memID)
{
	global $txt, $context, $ID_MEMBER, $modSettings, $user_profile;

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	// Permissions for removing stuff...
	$context['can_delete_posts'] = !$context['user']['is_owner'] && allowedTo('moderate_forum');

	// Can they do this, or will they need approval?
	$context['needs_approval'] = $context['user']['is_owner'] && !empty($modSettings['approveAccountDeletion']) && !allowedTo('moderate_forum');
	$context['page_title'] = $txt['deleteAccount'] . ': ' . $user_profile[$memID]['realName'];
}

function deleteAccount2($profile_vars, $post_errors, $memID)
{
	global $ID_MEMBER, $user_info, $sourcedir, $context, $db_prefix, $user_profile, $modSettings;

	// !!! Add a way to delete pms as well?

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	checkSession();

	$old_profile = &$user_profile[$memID];

	// Too often, people remove/delete their own only account.
	if (in_array(1, explode(',', $old_profile['additionalGroups'])) || $old_profile['ID_GROUP'] == 1)
	{
		// Are you allowed to administrate the forum, as they are?
		isAllowedTo('admin_forum');

		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE (ID_GROUP = 1 OR FIND_IN_SET(1, additionalGroups))
				AND ID_MEMBER != $memID
			LIMIT 1", __FILE__, __LINE__);
		list ($another) = mysql_fetch_row($request);
		mysql_free_result($request);

		if (empty($another))
			fatal_lang_error('at_least_one_admin');
	}

	// This file is needed for the deleteMembers function.
	require_once($sourcedir . '/Subs-Members.php');

	// Do you have permission to delete others profiles, or is that your profile you wanna delete?
	if ($memID != $ID_MEMBER)
	{
		isAllowedTo('profile_remove_any');

		// Now, have you been naughty and need your posts deleting?
		// !!! Should this check board permissions?
		if ($_POST['remove_type'] != 'none' && allowedTo('moderate_forum'))
		{
			// Include RemoveTopics - essential for this type of work!
			require_once($sourcedir . '/RemoveTopic.php');

			// First off we delete any topics the member has started - if they wanted topics being done.
			if ($_POST['remove_type'] == 'topics')
			{
				// Fetch all topics started by this user within the time period.
				$request = db_query("
					SELECT t.ID_TOPIC
					FROM {$db_prefix}topics AS t
					WHERE t.ID_MEMBER_STARTED = $memID", __FILE__, __LINE__);
				$topicIDs = array();
				while ($row = mysql_fetch_assoc($request))
					$topicIDs[] = $row['ID_TOPIC'];
				mysql_free_result($request);

				// Actually remove the topics.
				// !!! This needs to check permissions, but we'll let it slide for now because of moderate_forum already being had.
				removeTopics($topicIDs);
			}

			// Now delete the remaining messages.
			$request = db_query("
				SELECT m.ID_MSG
				FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
				WHERE m.ID_MEMBER = $memID
					AND m.ID_TOPIC = t.ID_TOPIC
					AND t.ID_FIRST_MSG != m.ID_MSG", __FILE__, __LINE__);
			// This could take a while... but ya know it's gonna be worth it in the end.
			while ($row = mysql_fetch_assoc($request))
				removeMessage($row['ID_MSG']);
			mysql_free_result($request);
		}

		// Only delete this poor members account if they are actually being booted out of camp.
		if (isset($_POST['deleteAccount']))
			deleteMembers($memID);
	}
	// Do they need approval to delete?
	elseif (empty($post_errors) && !empty($modSettings['approveAccountDeletion']) && !allowedTo('moderate_forum'))
	{
		// Setup their account for deletion ;)
		updateMemberData($memID, array('is_activated' => 4));
		// Another account needs approval...
		updateSettings(array('unapprovedMembers' => true), true);
	}
	// Also check if you typed your password correctly.
	elseif (empty($post_errors))
		deleteMembers($memID);
}

// This function 'remembers' the profile changes a user made after erronious input.
function rememberPostData()
{
	global $context, $scripturl, $txt, $modSettings, $ID_MEMBER, $user_profile, $user_info;

	// Overwrite member settings with the ones you selected.
	$context['member'] = array(
		'is_owner' => $_REQUEST['userID'] == $ID_MEMBER,
		'username' => $user_profile[$_REQUEST['userID']]['memberName'],
		'name' => !isset($_POST['realName']) || $_POST['realName'] == '' ? $user_profile[$_REQUEST['userID']]['memberName'] : stripslashes($_POST['realName']),
		'id' => (int) $_REQUEST['userID'],
		'title' => !isset($_POST['usertitle']) || $_POST['usertitle'] == '' ? '' : stripslashes($_POST['usertitle']),
		'email' => isset($_POST['emailAddress']) ? $_POST['emailAddress'] : '',
		'hide_email' => empty($_POST['hideEmail']) ? 0 : 1,
		'show_online' => empty($_POST['showOnline']) ? 0 : 1,
		'registered' => empty($_POST['dateRegistered']) || $_POST['dateRegistered'] == '0001-01-01' ? $txt[470] : strftime('%Y-%m-%d', $_POST['dateRegistered']),
		'blurb' => !isset($_POST['personalText']) ? '' : str_replace(array('<', '>', '&amp;#039;'), array('&lt;', '&gt;', '&#039;'), stripslashes($_POST['personalText'])),
		'gender' => array(
			'name' => empty($_POST['gender']) ? '' : ($_POST['gender'] == 2 ? 'f' : 'm')
		),
		'website' => array(
			'title' => !isset($_POST['websiteTitle']) ? '' : stripslashes($_POST['websiteTitle']),
			'url' => !isset($_POST['websiteUrl']) ? '' : stripslashes($_POST['websiteUrl']),
		),
		'birth_date' => array(
			'month' => empty($_POST['bday1']) ? '00' : (int) $_POST['bday1'],
			'day' => empty($_POST['bday2']) ? '00' : (int) $_POST['bday2'],
			'year' => empty($_POST['bday3']) ? '0000' : (int) $_POST['bday3']
		),
		'signature' => !isset($_POST['signature']) ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $_POST['signature']),
		'location' => !isset($_POST['location']) ? '' : stripslashes($_POST['location']),
		'icq' => array(
			'name' => !isset($_POST['icq']) ? '' : stripslashes($_POST['ICQ'])
		),
		'aim' => array(
			'name' => empty($_POST['aim']) ? '' : str_replace('+', ' ', $_POST['AIM'])
		),
		'yim' => array(
			'name' => empty($_POST['yim']) ? '' : stripslashes($_POST['YIM'])
		),
		'msn' => array(
			'name' => empty($_POST['msn']) ? '' : stripslashes($_POST['MSN'])
		),
		'posts' => empty($_POST['posts']) ? 0 : (int) $_POST['posts'],
		'avatar' => array(
			'name' => &$_POST['avatar'],
			'href' => empty($user_profile[$_REQUEST['userID']]['ID_ATTACH']) ? '' : (empty($user_profile[$_REQUEST['userID']]['attachmentType']) ? $scripturl . '?action=dlattach;attach=' . $user_profile[$_REQUEST['userID']]['ID_ATTACH'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $user_profile[$_REQUEST['userID']]['filename']),
			'custom' => stristr($_POST['avatar'], 'http://') ? $_POST['avatar'] : 'http://',
			'selection' => $_POST['avatar'] == '' || stristr($_POST['avatar'], 'http://') ? '' : $_POST['avatar'],
			'choice' => empty($_POST['avatar_choice']) ? 'server_stored' : $_POST['avatar_choice'],
			'external' => empty($_POST['userpicpersonal']) ? 'http://' : $_POST['userpicpersonal'],
			'ID_ATTACH' => empty($_POST['ID_ATTACH']) ? '0' : $_POST['ID_ATTACH'],
			'allow_server_stored' => allowedTo('profile_server_avatar') || !$context['user']['is_owner'],
			'allow_upload' => allowedTo('profile_upload_avatar') || !$context['user']['is_owner'],
			'allow_external' => allowedTo('profile_remote_avatar') || !$context['user']['is_owner'],
		),
		'karma' => array(
			'good' => empty($_POST['karmaGood']) ? '0' : $_POST['karmaGood'],
			'bad' => empty($_POST['karmaBad']) ? '0' : $_POST['karmaBad'],
		),
		'time_format' => !isset($_POST['timeFormat']) ? '' : stripslashes($_POST['timeFormat']),
		'time_offset' => empty($_POST['timeOffset']) ? '0' : $_POST['timeOffset'],
		'secret_question' => !isset($_POST['secretQuestion']) ? '' : stripslashes($_POST['secretQuestion']),
		'theme' => array(
			'id' => isset($context['member']['theme']['id']) ? $context['member']['theme']['id'] : 0,
			'name' => isset($context['member']['theme']['name']) ? $context['member']['theme']['name'] : '',
		),
		'notify_announcements' => empty($_POST['notifyAnnouncements']) ? 0 : 1,
		'notify_once' => empty($_POST['notifyOnce']) ? 0 : 1,
		'notify_send_body' => empty($_POST['notifySendBody']) ? 0 : (int) $_POST['notifySendBody'],
		'notify_types' => empty($_POST['notifyTypes']) ? 0 : (int) $_POST['notifyTypes'],
		'group' => isset($_POST['ID_GROUP']) ? $_POST['ID_GROUP'] : 0,
		'smiley_set' => array(
			'id' => isset($_POST['smileySet']) ? $_POST['smileySet'] : (isset($context['member']['smiley_set']) ? $context['member']['smiley_set']['id'] : ''),
			'name' => isset($context['member']['smiley_set']) ? $context['member']['smiley_set']['name'] : ''
		),
	);

	// Overwrite the currently set membergroups with those you just selected.
	if (allowedTo('manage_membergroups') && isset($_POST['ID_GROUP']))
	{
		foreach ($context['member_groups'] as $ID_GROUP => $dummy)
		{
			$context['member_groups'][$ID_GROUP]['is_primary'] = $ID_GROUP == $_POST['ID_GROUP'];
			$context['member_groups'][$ID_GROUP]['is_additional'] = !empty($_POST['additionalGroups']) && in_array($ID_GROUP, $_POST['additionalGroups']);
		}
	}

	loadThemeOptions((int) $_REQUEST['userID']);
}

function loadThemeOptions($memID)
{
	global $context, $options, $db_prefix, $user_profile;

	if (isset($_POST['options'], $_POST['default_options']))
		$_POST['options'] += $_POST['default_options'];

	if ($context['user']['is_owner'])
		$context['member']['options'] = $options;
	else
	{
		$request = db_query("
			SELECT ID_MEMBER, variable, value
			FROM {$db_prefix}themes
			WHERE ID_THEME IN (1, " . (int) $user_profile[$memID]['ID_THEME'] . ")
				AND ID_MEMBER IN (-1, $memID)", __FILE__, __LINE__);
		$temp = array();
		while ($row = mysql_fetch_assoc($request))
		{
			if ($row['ID_MEMBER'] == -1)
			{
				$temp[$row['variable']] = $row['value'];
				continue;
			}

			if (isset($_POST['options'][$row['variable']]))
				$row['value'] = $_POST['options'][$row['variable']];
			$context['member']['options'][$row['variable']] = $row['value'];
		}
		mysql_free_result($request);

		// Load up the default theme options for any missing.
		foreach ($temp as $k => $v)
		{
			if (!isset($context['member']['options'][$k]))
				$context['member']['options'][$k] = $v;
		}
	}
}

?>