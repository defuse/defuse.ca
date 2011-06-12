<?php
/**********************************************************************************
* Help.php                                                                        *
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

/*	This file has the important job of taking care of help messages and the
	help center.  It does this with two simple functions:

	void ShowHelp()
		- loads information needed for the help section.
		- accesed by ?action=help.
		- uses the Help template and Manual language file.
		- calls the appropriate sub template depending on the page being viewed.

	void ShowAdminHelp()
		- shows a popup for administrative or user help.
		- uses the help parameter to decide what string to display and where
		  to get the string from. ($helptxt or $txt?)
		- loads the ManagePermissions language file if the help starts with
		  permissionhelp.
		- uses the Help template, popup sub template, no layers.
		- accessed via ?action=helpadmin;help=??.
*/

// Redirect to the user help ;).
function ShowHelp()
{
	global $settings, $user_info, $language, $context, $txt;

	loadTemplate('Help');
	loadLanguage('Manual');

	// All the available pages.
	$context['all_pages'] = array(
		'index' => 'intro',
		'registering' => 'register',
		'loginout' => 'login',
		'profile' => 'profile',
		'post' => 'posting',
		'pm' => 'pm',
		'searching' => 'search',
	);

	if (!isset($_GET['page']) || !is_string($_GET['page']) || !isset($context['all_pages'][$_GET['page']]))
		$_GET['page'] = 'index';

	$context['current_page'] = $_GET['page'];
	$context['sub_template'] = 'manual_' . $context['all_pages'][$context['current_page']];

	$context['template_layers'][] = 'manual';
	$context['page_title'] = $txt['manual_smf_user_help'] . ': ' . $txt['manual_index_' . $context['all_pages'][$context['current_page']]];

	// We actually need a special style sheet for help ;)
	$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="' . (file_exists($settings['theme_dir'] . '/help.css') ? $settings['theme_url'] : $settings['default_theme_url']) . '/help.css" />';
}

// Show some of the more detailed help to give the admin an idea...
function ShowAdminHelp()
{
	global $txt, $helptxt, $context;

	// Load the admin help language file and template.
	loadLanguage('Help');

	// Permission specific help?
	if (isset($_GET['help']) && substr($_GET['help'], 0, 14) == 'permissionhelp')
		loadLanguage('ManagePermissions');

	loadTemplate('Help');

	// Set the page title to something relevant.
	$context['page_title'] = $context['forum_name'] . ' - ' . $txt[119];

	// Don't show any template layers, just the popup sub template.
	$context['template_layers'] = array();
	$context['sub_template'] = 'popup';

	// What help string should be used?
	if (isset($helptxt[$_GET['help']]))
		$context['help_text'] = &$helptxt[$_GET['help']];
	elseif (isset($txt[$_GET['help']]))
		$context['help_text'] = &$txt[$_GET['help']];
	else
		$context['help_text'] = $_GET['help'];
}

?>