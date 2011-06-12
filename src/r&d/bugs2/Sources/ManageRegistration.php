<?php
/**********************************************************************************
* ManageRegistration.php                                                          *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.10                                          *
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

/*	This file helps the administrator setting registration settings and policy
	as well as allow the administrator to register new members themselves.

	void RegCenter()
		- entrance point for the registration center.
		- accessed by ?action=regcenter.
		- requires either the moderate_forum or the admin_forum permission.
		- loads the Login language file and the Register template.
		- calls the right function based on the subaction.

	void AdminRegister()
		- a function to register a new member from the admin center.
		- accessed by ?action=regcenter;sa=register
		- requires the moderate_forum permission.
		- uses the admin_register sub template of the Register template.
		- allows assigning a primary group to the member being registered.

	void EditAgreement()
		- allows the administrator to edit the registration agreement, and
		  choose whether it should be shown or not.
		- accessed by ?action=regcenter;sa=agreement.
		- uses the Admin template and the edit_agreement sub template.
		- requires the admin_forum permission.
		- uses the edit_agreement administration area.
		- writes and saves the agreement to the agreement.txt file.

	void SetReserve()
		- set the names under which users are not allowed to register.
		- accessed by ?action=regcenter;sa=reservednames.
		- requires the admin_forum permission.
		- uses the reserved_words sub template of the Register template.

	void AdminSettings()
		- set general registration settings and Coppa compliance settings.
		- accessed by ?action=regcenter;sa=settings.
		- requires the admin_forum permission.
		- uses the admin_settings sub template of the Register template.
*/

// Main handling function for the admin approval center
function RegCenter()
{
	global $modSettings, $context, $txt, $db_prefix, $scripturl;

	// Old templates might still request this.
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'browse')
		redirectexit('action=viewmembers;sa=browse' . (isset($_REQUEST['type']) ? ';type=' . $_REQUEST['type'] : ''));

	$subActions = array(
		'register' => array('AdminRegister', 'moderate_forum'),
		'agreement' => array('EditAgreement', 'admin_forum'),
		'reservednames' => array('SetReserve', 'admin_forum'),
		'settings' => array('AdminSettings', 'admin_forum'),
	);

	// Work out which to call...
	$context['sub_action'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('moderate_forum') ? 'register' : 'settings');

	// Must have sufficient permissions.
	isAllowedTo($subActions[$context['sub_action']][1]);

	// Set the admin area...
	adminIndex('registration_center');

	// Loading, always loading.
	loadLanguage('Login');
	loadTemplate('Register');

	// Next create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => &$txt['registration_center'],
		'help' => 'registrations',
		'description' => $txt['admin_settings_desc'],
		'tabs' => array(
			'register' => array(
				'title' => $txt['admin_browse_register_new'],
				'description' => $txt['admin_register_desc'],
				'href' => $scripturl . '?action=regcenter;sa=register',
				'is_selected' => $context['sub_action'] == 'register',
				'is_last' => !allowedTo('admin_forum'),
			)
		)
	);
	if (allowedTo('admin_forum'))
	{
		$context['admin_tabs']['tabs']['agreement'] = array(
			'title' => $txt['smf11'],
			'description' => $txt['smf12'],
			'href' => $scripturl . '?action=regcenter;sa=agreement',
			'is_selected' => $context['sub_action'] == 'agreement',
		);
		$context['admin_tabs']['tabs']['reservednames'] = array(
			'title' => $txt[341],
			'description' => $txt[699],
			'href' => $scripturl . '?action=regcenter;sa=reservednames',
			'is_selected' => $context['sub_action'] == 'reservednames',
		);
		$context['admin_tabs']['tabs']['settings'] = array(
			'title' => $txt['settings'],
			'description' => $txt['admin_settings_desc'],
			'href' => $scripturl . '?action=regcenter;sa=settings',
			'is_last' => true,
			'is_selected' => $context['sub_action'] == 'settings',
		);
	}

	// Finally, get around to calling the function...
	$subActions[$context['sub_action']][0]();
}

// This function allows the admin to register a new member by hand.
function AdminRegister()
{
	global $txt, $context, $db_prefix, $sourcedir, $scripturl;

	// Setup the "tab", just incase an error occurs.
	$context['admin_tabs']['tabs']['register']['is_selected'] = true;

	if (!empty($_POST['regSubmit']))
	{
		checkSession();

		foreach ($_POST as $key => $value)
			if (!is_array($_POST[$key]))
				$_POST[$key] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST[$key]));

		$regOptions = array(
			'interface' => 'admin',
			'username' => $_POST['user'],
			'email' => $_POST['email'],
			'password' => $_POST['password'],
			'password_check' => $_POST['password'],
			'check_reserved_name' => true,
			'check_password_strength' => false,
			'check_email_ban' => false,
			'send_welcome_email' => isset($_POST['emailPassword']) || empty($_POST['password']),
			'require' => isset($_POST['emailActivate']) ? 'activation' : 'nothing',
			'memberGroup' => empty($_POST['group']) ? 0 : (int) $_POST['group'],
		);

		require_once($sourcedir . '/Subs-Members.php');
		$memberID = registerMember($regOptions);
		if (!empty($memberID))
		{
			$context['new_member'] = array(
				'id' => $memberID,
				'name' => $_POST['user'],
				'href' => $scripturl . '?action=profile;u=' . $memberID,
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $memberID . '">' . $_POST['user'] . '</a>',
			);
			$context['registration_done'] = sprintf($txt['admin_register_done'], $context['new_member']['link']);
		}
	}

	// Basic stuff.
	$context['sub_template'] = 'admin_register';
	$context['page_title'] = $txt['registration_center'];

	// Load the assignable member groups.
	$request = db_query("
		SELECT groupName, ID_GROUP
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP != 3
			AND minPosts = -1" . (allowedTo('admin_forum') ? '' : "
			AND ID_GROUP != 1") . "
		ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	$context['member_groups'] = array(0 => &$txt['admin_register_group_none']);
	while ($row = mysql_fetch_assoc($request))
		$context['member_groups'][$row['ID_GROUP']] = $row['groupName'];
	mysql_free_result($request);
}

// I hereby agree not to be a lazy bum.
function EditAgreement()
{
	global $txt, $boarddir, $context, $modSettings;

	if (isset($_POST['agreement']))
	{
		checkSession();

		// Off it goes to the agreement file.
		$fp = fopen($boarddir . '/agreement.txt', 'w');
		fwrite($fp, str_replace("\r", '', stripslashes($_POST['agreement'])));
		fclose($fp);

		updateSettings(array('requireAgreement' => !empty($_POST['requireAgreement'])));

		redirectexit('action=regcenter;sa=agreement');
	}

	// Get the current agreement.
	$context['agreement'] = file_exists($boarddir . '/agreement.txt') ? htmlspecialchars(file_get_contents($boarddir . '/agreement.txt')) : '';
	$context['warning'] = is_writable($boarddir . '/agreement.txt') ? '' : $txt['smf320'];
	$context['require_agreement'] = !empty($modSettings['requireAgreement']);

	$context['sub_template'] = 'edit_agreement';
	$context['page_title'] = $txt['smf11'];
}

// Set reserved names/words....
function SetReserve()
{
	global $txt, $db_prefix, $context, $modSettings;

	// Submitting new reserved words.
	if (!empty($_POST['save_reserved_names']))
	{
		checkSession();

		// Set all the options....
		updateSettings(array(
			'reserveWord' => (isset($_POST['matchword']) ? '1' : '0'),
			'reserveCase' => (isset($_POST['matchcase']) ? '1' : '0'),
			'reserveUser' => (isset($_POST['matchuser']) ? '1' : '0'),
			'reserveName' => (isset($_POST['matchname']) ? '1' : '0'),
			'reserveNames' => str_replace("\r", '', $_POST['reserved'])
		));
	}

	// Get the reserved word options and words.
	$context['reserved_words'] = explode("\n", $modSettings['reserveNames']);
	$context['reserved_word_options'] = array();
	$context['reserved_word_options']['match_word'] = $modSettings['reserveWord'] == '1';
	$context['reserved_word_options']['match_case'] = $modSettings['reserveCase'] == '1';
	$context['reserved_word_options']['match_user'] = $modSettings['reserveUser'] == '1';
	$context['reserved_word_options']['match_name'] = $modSettings['reserveName'] == '1';

	// Ready the template......
	$context['sub_template'] = 'edit_reserved_words';
	$context['page_title'] = $txt[341];
}

// This function handles registration settings, and provides a few pretty stats too while it's at it.
function AdminSettings()
{
	global $txt, $context, $db_prefix, $scripturl, $modSettings;

	// Setup the template
	$context['sub_template'] = 'admin_settings';
	$context['page_title'] = $txt['registration_center'];

	// Saving?
	if (isset($_POST['save']))
	{
		checkSession();

		// Are there some contacts missing?
		if (!empty($_POST['coppaAge']) && !empty($_POST['coppaType']) && empty($_POST['coppaPost']) && empty($_POST['coppaFax']))
			fatal_error($txt['admin_setting_coppa_require_contact']);

		// Post needs to take into account line breaks.
		$_POST['coppaPost'] = str_replace("\n", '<br />', empty($_POST['coppaPost']) ? '' : $_POST['coppaPost']);

		// Update the actual settings.
		updateSettings(array(
			'registration_method' => (int) $_POST['registration_method'],
			'notify_new_registration' => isset($_POST['notify_new_registration']) ? 1 : 0,
			'send_welcomeEmail' => isset($_POST['send_welcomeEmail']) ? 1 : 0,
			'password_strength' => (int) $_POST['password_strength'],
			'disable_visual_verification' => isset($_POST['visual_verification_type']) ? (int) $_POST['visual_verification_type'] : 0,
			'coppaAge' => (int) $_POST['coppaAge'],
			'coppaType' => empty($_POST['coppaType']) ? 0 : (int) $_POST['coppaType'],
			'coppaPost' => $_POST['coppaPost'],
			'coppaFax' => !empty($_POST['coppaFax']) ? $_POST['coppaFax'] : '',
			'coppaPhone' => !empty($_POST['coppaPhone']) ? $_POST['coppaPhone'] : '',
		));

		// Reload the page, so the tabs are accurate.
		redirectexit('action=regcenter;sa=settings');
	}

	// Turn the postal address into something suitable for a textbox.
	$context['coppaPost'] = !empty($modSettings['coppaPost']) ? preg_replace('~<br(?: /)?' . '>~', "\n", $modSettings['coppaPost']) : '';

	// Generate a sample registration image.
	$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
	$context['verificiation_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(mt_rand());

	$character_range = array_merge(range('A', 'H'), array('K', 'M', 'N', 'P'), range('R', 'Z'));
	$_SESSION['visual_verification_code'] = '';
	for ($i = 0; $i < 5; $i++)
		$_SESSION['visual_verification_code'] .= $character_range[array_rand($character_range)];
}

?>