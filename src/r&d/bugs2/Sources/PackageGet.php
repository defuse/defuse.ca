<?php
/**********************************************************************************
* PackageGet.php                                                                  *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.12                                          *
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

/* // !!!

	void PackageGet()
		// !!!

	void PackageGBrowse()
		// !!!

	void PackageDownload()
		// !!!

	void PackageUpload()
		// !!!

	void PackageServerAdd()
		// !!!

	void PackageServerRemove()
		// !!!
*/

// Browse the list of package servers, add servers...
function PackageGet()
{
	global $txt, $scripturl, $context, $boarddir, $sourcedir, $modSettings;

	isAllowedTo('admin_forum');
	require_once($sourcedir . '/Subs-Package.php');

	// Still managing packages...
	adminIndex('manage_packages');

	// Use the Packages template... no reason to separate.
	loadLanguage('Packages');
	loadTemplate('Packages');

	// Add the appropriate items to the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=packages',
		'name' => &$txt['package1']
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=packageget',
		'name' => &$txt['smf182']
	);
	$context['page_title'] = $txt['package1'];

	// Here is a list of all the potentially valid actions.
	$subActions = array(
		'servers' => 'PackageServers',
		'add' => 'PackageServerAdd',
		'browse' => 'PackageGBrowse',
		'download' => 'PackageDownload',
		'remove' => 'PackageServerRemove',
		'upload' => 'PackageUpload',
	);

	// Now let's decide where we are taking this...
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$context['sub_action'] = $_REQUEST['sa'];
	// We need to support possible old javascript links...
	elseif ($_REQUEST['action'] == 'pgdownload')
		$context['sub_action'] = 'download';
	else
		$context['sub_action'] = 'servers';

	// Now create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => &$txt['package1'],
		//'help' => 'registrations',
		'description' => $txt['package_manager_desc'],
		'tabs' => array(
			'browse' => array(
				'title' => $txt['package3'],
				'href' => $scripturl . '?action=packages;sa=browse',
			),
			'packageget' => array(
				'title' => $txt['download_packages'],
				'description' => $txt['download_packages_desc'],
				'href' => $scripturl . '?action=packageget',
				'is_selected' => true,
			),
			'installed' => array(
				'title' => $txt['installed_packages'],
				'description' => $txt['installed_packages_desc'],
				'href' => $scripturl . '?action=packages;sa=installed',
			),
			'options' => array(
				'title' => $txt['package_settings'],
				'description' => $txt['package_install_options_ftp_why'],
				'href' => $scripturl . '?action=packages;sa=options',
				'is_last' => true,
			),
		),
	);

	$subActions[$context['sub_action']]();
}

function PackageServers()
{
	global $txt, $scripturl, $context, $boarddir, $sourcedir, $modSettings, $db_prefix;

	// Ensure we use the correct template, and page title.
	$context['sub_template'] = 'servers';
	$context['page_title'] .= ' - ' . $txt['download_packages'];

	// Load the list of servers.
	$request = db_query("
		SELECT ID_SERVER, name, url
		FROM {$db_prefix}package_servers", __FILE__, __LINE__);
	$context['servers'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['servers'][] = array(
			'name' => $row['name'],
			'url' => $row['url'],
			'id' => $row['ID_SERVER'],
		);
	}
	mysql_free_result($request);

	$context['package_download_broken'] = !is_writable($boarddir . '/Packages') || !is_writable($boarddir . '/Packages/installed.list');

	if ($context['package_download_broken'])
	{
		@chmod($boarddir . '/Packages', 0777);
		@chmod($boarddir . '/Packages/installed.list', 0777);
	}

	$context['package_download_broken'] = !is_writable($boarddir . '/Packages') || !is_writable($boarddir . '/Packages/installed.list');

	if ($context['package_download_broken'])
	{
		if (isset($_POST['ftp_username']))
		{
			$ftp = new ftp_connection($_POST['ftp_server'], $_POST['ftp_port'], $_POST['ftp_username'], $_POST['ftp_password']);

			if ($ftp->error === false)
			{
				// I know, I know... but a lot of people want to type /home/xyz/... which is wrong, but logical.
				if (!$ftp->chdir($_POST['ftp_path']))
				{
					$ftp_error = $ftp->error;
					$ftp->chdir(preg_replace('~^/home[2]?/[^/]+?~', '', $_POST['ftp_path']));
				}
			}
		}

		if (!isset($ftp) || $ftp->error !== false)
		{
			if (!isset($ftp))
				$ftp = new ftp_connection(null);
			elseif ($ftp->error !== false && !isset($ftp_error))
				$ftp_error = $ftp->last_message === null ? '' : $ftp->last_message;

			list ($username, $detect_path, $found_path) = $ftp->detect_path($boarddir);

			if ($found_path || !isset($_POST['ftp_path']))
				$_POST['ftp_path'] = $detect_path;

			if (!isset($_POST['ftp_username']))
				$_POST['ftp_username'] = $username;

			$context['package_ftp'] = array(
				'server' => isset($_POST['ftp_server']) ? $_POST['ftp_server'] : (isset($modSettings['package_server']) ? $modSettings['package_server'] : 'localhost'),
				'port' => isset($_POST['ftp_port']) ? $_POST['ftp_port'] : (isset($modSettings['package_port']) ? $modSettings['package_port'] : '21'),
				'username' => isset($_POST['ftp_username']) ? $_POST['ftp_username'] : (isset($modSettings['package_username']) ? $modSettings['package_username'] : ''),
				'path' => $_POST['ftp_path'],
				'error' => empty($ftp_error) ? null : $ftp_error,
			);
		}
		else
		{
			$context['package_download_broken'] = false;

			$ftp->chmod('Packages', 0777);
			$ftp->chmod('Packages/installed.list', 0777);

			$ftp->close();
		}
	}
}

// Browse a server's list of packages.
function PackageGBrowse()
{
	global $txt, $boardurl, $context, $scripturl, $boarddir, $sourcedir, $forum_version, $context, $db_prefix;

	if (isset($_GET['server']))
	{
		if ($_GET['server'] == '')
			redirectexit('action=packageget');

		$server = (int) $_GET['server'];

		// Query the server list to find the current server.
		$request = db_query("
			SELECT name, url
			FROM {$db_prefix}package_servers
			WHERE ID_SERVER = $server
			LIMIT 1", __FILE__, __LINE__);
		list ($name, $url) = mysql_fetch_row($request);
		mysql_free_result($request);

		// If the server does not exist, dump out.
		if (empty($url))
			fatal_lang_error('smf191', false);

		// If there is a relative link, append to the stored server url.
		if (isset($_GET['relative']))
			$url = $url . (substr($url, -1) == '/' ? '' : '/') . $_GET['relative'];

		// Clear any "absolute" URL.  Since "server" is present, "absolute" is garbage.
		unset($_GET['absolute']);
	}
	elseif (isset($_GET['absolute']) && $_GET['absolute'] != '')
	{
		// Initialize the requried variables.
		$server = '';
		$url = $_GET['absolute'];
		$name = '';
		$_GET['package'] = $url . '/packages.xml?language=' . $context['user']['language'];

		// Clear any "relative" URL.  Since "server" is not present, "relative" is garbage.
		unset($_GET['relative']);
		
		$token = checkConfirm('get_absolute_url');
		if ($token !== true)
		{
			$context['sub_template'] = 'package_confirm';
		
			$context['page_title'] = $txt['smf183'];
			$context['confirm_message'] = sprintf($txt['package_confirm_view_package_content'], htmlspecialchars($_GET['absolute']));
			$context['proceed_href'] = $scripturl . '?action=packageget;sa=browse;absolute=' . urlencode($_GET['absolute']) . ';confirm=' . $token;
			
			return;
		}
	}
	// Minimum required parameter did not exist so dump out.
	else
		fatal_lang_error('smf191', false);

	// In safe mode or on lycos?  Try this URL. (includes package-list for informational purposes ;).)
	//if (@ini_get('safe_mode'))
	//	redirectexit($url . '/index.php?package-list&language=' . $context['user']['language'] . '&ref=' . $boardurl);

	// Attempt to connect.  If unsuccessful... try the URL.
	if (!isset($_GET['package']) || file_exists($_GET['package']))
		$_GET['package'] = $url . '/packages.xml?language=' . $context['user']['language'];

	// Check to be sure the packages.xml file actually exists where it is should be... or dump out.
	if ((isset($_GET['absolute']) || isset($_GET['relative'])) && !url_exists($_GET['package']))
		fatal_lang_error('packageget_unable', false, array($url . '/index.php'));

	// Read packages.xml and parse into xmlArray. (the true tells it to trim things ;).)
	$listing = new xmlArray(fetch_web_data($_GET['package']), true);

	// Errm.... empty file?  Try the URL....
	if (!$listing->exists('package-list'))
		fatal_lang_error('packageget_unable', false, array($url . '/index.php'));

	// List out the packages...
	$context['package_list'] = array();

	$listing = $listing->path('package-list[0]');

	// Use the package list's name if it exists.
	if ($listing->exists('list-title'))
		$name = $listing->fetch('list-title');

	// Pick the correct template.
	$context['sub_template'] = 'package_list';

	$context['page_title'] = $txt['smf183'] . ($name != '' ? ' - ' . $name : '');
	$context['package_server'] = $server;

	$instmods = loadInstalledPackages();

	// Look through the list of installed mods...
	foreach ($instmods as $installed_mod)
		$installed_mods[$installed_mod['id']] = $installed_mod['version'];

	// Get default author and email if they exist.
	if ($listing->exists('default-author'))
	{
		$default_author = htmlspecialchars($listing->fetch('default-author'));
		if ($listing->exists('default-author/@email'))
			$default_email = $listing->fetch('default-author/@email');
	}

	// Get default web site if it exists.
	if ($listing->exists('default-website'))
	{
		$default_website = $listing->fetch('default-website');
		if ($listing->exists('default-website/@title'))
			$default_title = htmlspecialchars($listing->fetch('default-website/@title'));
	}

	$the_version = strtr($forum_version, array('SMF ' => ''));
	if (!empty($_SESSION['version_emulate']))
		$the_version = $_SESSION['version_emulate'];

	$packageNum = 0;

	$sections = $listing->set('section');
	foreach ($sections as $i => $section)
	{
		$packages = $section->set('title|heading|text|remote|rule|modification|language|avatar-pack|theme|smiley-set');
		foreach ($packages as $thisPackage)
		{
			$package = &$context['package_list'][];
			$package['type'] = $thisPackage->name();

			// It's a Title, Heading, Rule or Text.
			if (in_array($package['type'], array('title', 'heading', 'text', 'rule')))
				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
			// It's a Remote link.
			elseif ($package['type'] == 'remote')
			{
				$remote_type = $thisPackage->exists('@type') ? $thisPackage->fetch('@type') : 'relative';

				if ($remote_type == 'relative' && substr($thisPackage->fetch('@href'), 0, 7) != 'http://')
				{
					if (isset($_GET['absolute']))
						$current_url = $_GET['absolute'] . '/';
					elseif (isset($_GET['relative']))
						$current_url = $_GET['relative'] . '/';
					else
						$current_url = '';

					$current_url .= $thisPackage->fetch('@href');
					if (isset($_GET['absolute']))
						$package['href'] = $scripturl . '?action=packageget;sa=browse;absolute=' . $current_url;
					else
						$package['href'] = $scripturl . '?action=packageget;sa=browse;server=' . $context['package_server'] . ';relative=' . $current_url;
				}
				else
				{
					$current_url = $thisPackage->fetch('@href');
					$package['href'] = $scripturl . '?action=packageget;sa=browse;absolute=' . $current_url;
				}

				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
			}
			// It's a package...
			else
			{
				if (isset($_GET['absolute']))
					$current_url = $_GET['absolute'] . '/';
				elseif (isset($_GET['relative']))
					$current_url = $_GET['relative'] . '/';
				else
					$current_url = '';

				$server_att = $server != '' ? ';server=' . $server : '';

				$package += $thisPackage->to_array();

				if (isset($package['website']))
					unset($package['website']);
				$package['author'] = array();

				if ($package['description'] == '')
					$package['description'] = $txt['pacman8'];
				else
					$package['description'] = parse_bbc(preg_replace('~\[[/]?html\]~i', '', htmlspecialchars($package['description'])));				

				$package['is_installed'] = isset($installed_mods[$package['id']]);
				$package['is_current'] = $package['is_installed'] && ($installed_mods[$package['id']] == $package['version']);
				$package['is_newer'] = $package['is_installed'] && ($installed_mods[$package['id']] > $package['version']);

				// This package is either not installed, or installed but old.  Is it supported on this version of SMF?
				if (!$package['is_installed'] || (!$package['is_current'] && !$package['is_newer']))
				{
					if ($thisPackage->exists('version/@for'))
						$package['can_install'] = matchPackageVersion($the_version, $thisPackage->fetch('version/@for'));
				}
				// Okay, it's already installed AND up to date.
				else
					$package['can_install'] = false;

				$already_exists = getPackageInfo(basename($package['filename']));
				$package['download_conflict'] = !empty($already_exists) && $already_exists['id'] == $package['id'] && $already_exists['version'] != $package['version'];

				$package['href'] = $url . '/' . $package['filename'];
				$package['name'] = htmlspecialchars($package['name']);
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
				$package['download']['href'] = $scripturl . '?action=packageget;sa=download' . $server_att . ';package=' . $current_url . $package['filename'] . ($package['download_conflict'] ? ';conflict' : '') . ';sesc=' . $context['session_id'];
				$package['download']['link'] = '<a href="' . $package['download']['href'] . '">' . $package['name'] . '</a>';

				if ($thisPackage->exists('author') || isset($default_author))
				{
					if ($thisPackage->exists('author/@email'))
						$package['author']['email'] = htmlspecialchars($thisPackage->fetch('author/@email'));
					elseif (isset($default_email))
						$package['author']['email'] = $default_email;

					if ($thisPackage->exists('author') && $thisPackage->fetch('author') != '')
						$package['author']['name'] = htmlspecialchars($thisPackage->fetch('author'));
					else
						$package['author']['name'] = $default_author;

					if (!empty($package['author']['email']))
					{
						// Only put the "mailto:" if it looks like a valid email address.  Some may wish to put a link to an SMF IM Form or other web mail form.
						$package['author']['href'] = preg_match('~^[\w\.\-]+@[\w][\w\-\.]+[\w]$~', $package['author']['email']) != 0 ? 'mailto:' . $package['author']['email'] : $package['author']['email'];
						$package['author']['link'] = '<a href="' . $package['author']['href'] . '">' . $package['author']['name'] . '</a>';
					}
				}

				if ($thisPackage->exists('website') || isset($default_website))
				{
					if ($thisPackage->exists('website') && $thisPackage->exists('website/@title'))
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website/@title'));
					elseif (isset($default_title))
						$package['author']['website']['name'] = $default_title;
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website'));
					else
						$package['author']['website']['name'] = $default_website;

					if ($thisPackage->exists('website') && $thisPackage->fetch('website') != '')
						$authorhompage = $thisPackage->fetch('website');
					else
						$authorhompage = $default_website;

					if (strpos(strtolower($authorhompage), 'a href') === false)
					{
						$package['author']['website']['href'] = $authorhompage;
						$package['author']['website']['link'] = '<a href="' . $authorhompage . '">' . $package['author']['website']['name'] . '</a>';
					}
					else
					{
						if (preg_match('/a href="(.+?)"/', $authorhompage, $match) == 1)
							$package['author']['website']['href'] = $match[1];
						else
							$package['author']['website']['href'] = '';
						$package['author']['website']['link'] = $authorhompage;
					}
				}
				else
				{
					$package['author']['website']['href'] = '';
					$package['author']['website']['link'] = '';
				}
			}

			$package['is_remote'] = $package['type'] == 'remote';
			$package['is_title'] = $package['type'] == 'title';
			$package['is_heading'] = $package['type'] == 'heading';
			$package['is_text'] = $package['type'] == 'text';
			$package['is_line'] = $package['type'] == 'rule';

			$packageNum = in_array($package['type'], array('title', 'heading', 'text', 'remote', 'rule')) ? 0 : $packageNum + 1;
			$package['count'] = $packageNum;
		}
	}

	// Lets make sure we get a nice new spiffy clean $package to work with.  Otherwise we get PAIN!
	unset($package);
		
	foreach ($context['package_list'] as $i => $package)
	{
		if ($package['count'] == 0 || isset($package['can_install']))
			continue;

		$context['package_list'][$i]['can_install'] = false;

		$packageInfo = getPackageInfo($url . '/' . $package['filename']);
		if (!empty($packageInfo) && $packageInfo['xml']->exists('install'))
		{
			$installs = $packageInfo['xml']->set('install');
			foreach ($installs as $install)
				if (!$install->exists('@for') || matchPackageVersion($the_version, $install->fetch('@for')))
				{
					// Okay, this one is good to go.
					$context['package_list'][$i]['can_install'] = true;
					break;
				}
		}
	}
}

// Download a package.
function PackageDownload()
{
	global $txt, $scripturl, $boarddir, $context, $sourcedir, $db_prefix;

	// Use the downloaded sub template.
	$context['sub_template'] = 'downloaded';

	// Security is good...
	checkSession('get');

	if (isset($_GET['server']))
	{
		$server = (int) $_GET['server'];

		// Query the server table to find the requested server.
		$request = db_query("
			SELECT name, url
			FROM {$db_prefix}package_servers
			WHERE ID_SERVER = $server
			LIMIT 1", __FILE__, __LINE__);
		list ($name, $url) = mysql_fetch_row($request);
		mysql_free_result($request);

		// If server does not exist then dump out.
		if (empty($url))
			fatal_lang_error('smf191', false);

		$url = $url . '/';
	}
	else
	{
		// Initialize the requried variables.
		$server = '';
		$url = '';
	}

	$package_name = basename($_REQUEST['package']);
	if (isset($_REQUEST['conflict']) || (isset($_REQUEST['auto']) && file_exists($boarddir . '/Packages/' . $package_name)))
	{
		// Find the extension, change abc.tar.gz to abc_1.tar.gz...
		if (strrpos(substr($package_name, 0, -3), '.') !== false)
		{
			$ext = substr($package_name, strrpos(substr($package_name, 0, -3), '.'));
			$package_name = substr($package_name, 0, strrpos(substr($package_name, 0, -3), '.')) . '_';
		}
		else
			$ext = '';

		// Find the first available.
		$i = 1;
		while (file_exists($boarddir . '/Packages/' . $package_name . $i . $ext))
			$i++;

		$package_name = $package_name . $i . $ext;
	}

	// First make sure it's a package.
	if (getPackageInfo($url . $_REQUEST['package']) == false)
		fatal_lang_error('package45', false);

	// Use FTP if necessary.
	packageRequireFTP($scripturl . '?action=packageget;sa=download' . (isset($_GET['server']) ? ';server=' . $_GET['server'] : '') . (isset($_REQUEST['auto']) ? ';auto' : '') . ';package=' . $_REQUEST['package'] . (isset($_REQUEST['conflict']) ? ';conflict' : '') . ';sesc=' . $context['session_id'], array($boarddir . '/Packages/' . $package_name));
	package_put_contents($boarddir . '/Packages/' . $package_name, fetch_web_data($url . $_REQUEST['package']));

	// Done!  Did we get this package automatically?
	if (preg_match('~^http://[\w_\-]+\.simplemachines\.org/~', $_REQUEST['package']) == 1 && strpos($_REQUEST['package'], 'dlattach') === false && isset($_REQUEST['auto']))
		redirectexit('action=packages;sa=install;package=' . $package_name . ';sesc=' . $context['session_id']);

	// You just downloaded a mod from SERVER_NAME_GOES_HERE.
	$context['package_server'] = $server;

	$context['package'] = getPackageInfo($package_name);

	if (empty($context['package']))
		fatal_lang_error('package_cant_download', false);

	if ($context['package']['type'] == 'modification')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package11'] . ' ]</a>';
	elseif ($context['package']['type'] == 'avatar')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package12'] . ' ]</a>';
	elseif ($context['package']['type'] == 'language')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package13'] . ' ]</a>';
	else
		$context['package']['install']['link'] = '';

	$context['package']['list_files']['link'] = '<a href="' . $scripturl . '?action=packages;sa=list;package=' . $context['package']['filename'] . '">[ ' . $txt['package14'] . ' ]</a>';

	// Free a little bit of memory...
	unset($context['package']['xml']);

	$context['page_title'] = $txt['smf192'];
}

// Upload a new package to the directory.
function PackageUpload()
{
	global $txt, $scripturl, $boarddir, $context, $sourcedir;

	// Setup the correct template, even though I'll admit we ain't downloading ;)
	$context['sub_template'] = 'downloaded';

	// !!! TODO: Use FTP if the Packages directory is not writable.

	// Check the file was even sent!
	if (!isset($_FILES['package']['name']) || $_FILES['package']['name'] == '' || !is_uploaded_file($_FILES['package']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['package']['tmp_name'])))
		fatal_lang_error('package_upload_error');

	// Make sure it has a sane filename.
	$_FILES['package']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['package']['name']);

	if (strtolower(substr($_FILES['package']['name'], -4)) != '.zip' && strtolower(substr($_FILES['package']['name'], -4)) != '.tgz' && strtolower(substr($_FILES['package']['name'], -7)) != '.tar.gz')
		fatal_error($txt['package_upload_error_supports'] . 'zip, tgz, tar.gz.', false);

	// We only need the filename...
	$packageName = basename($_FILES['package']['name']);

	// Setup the destination and throw an error if the file is already there!
	$destination = $boarddir . '/Packages/' . $packageName;
	// !!! Maybe just roll it like we do for downloads?
	if (file_exists($destination))
		fatal_lang_error('package_upload_error_exists');

	// Now move the file.
	move_uploaded_file($_FILES['package']['tmp_name'], $destination);
	@chmod($destination, 0777);

	// If we got this far that should mean it's available.
	$context['package'] = getPackageInfo($packageName);
	$context['package_server'] = '';

	// Not really a package, you lazy bum!
	if (empty($context['package']))
	{
		@unlink($destination);
		fatal_lang_error('package_upload_error_broken', false);
	}

	if ($context['package']['type'] == 'modification')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package11'] . ' ]</a>';
	elseif ($context['package']['type'] == 'avatar')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package12'] . ' ]</a>';
	elseif ($context['package']['type'] == 'language')
		$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=packages;sa=install;package=' . $context['package']['filename'] . ';sesc=' . $context['session_id'] . '">[ ' . $txt['package13'] . ' ]</a>';
	else
		$context['package']['install']['link'] = '';

	$context['package']['list_files']['link'] = '<a href="' . $scripturl . '?action=packages;sa=list;package=' . $context['package']['filename'] . '">[ ' . $txt['package14'] . ' ]</a>';

	unset($context['package']['xml']);

	$context['page_title'] = $txt['package_uploaded_success'];
}

// Add a package server to the list.
function PackageServerAdd()
{
	global $db_prefix;

	// Validate the user.
	checkSession();

	// If they put a slash on the end, get rid of it.
	if (substr($_POST['serverurl'], -1) == '/')
		$_POST['serverurl'] = substr($_POST['serverurl'], 0, -1);
	$servername = htmlspecialchars($_POST['servername']);
	$serverurl = htmlspecialchars($_POST['serverurl']);

	// Make sure the URL has the correct prefix.
	if (strpos($serverurl, 'http://') !== 0 && strpos($serverurl, 'https://') !== 0)
		$serverurl = 'http://' . $serverurl;

	db_query("
		INSERT INTO {$db_prefix}package_servers
			(name, url)
		VALUES (SUBSTRING('$servername', 1, 255), SUBSTRING('$serverurl', 1, 255))", __FILE__, __LINE__);

	redirectexit('action=packageget');
}

// Remove a server from the list.
function PackageServerRemove()
{
	global $db_prefix;

	checkSession('get');

	db_query("
		DELETE FROM {$db_prefix}package_servers
		WHERE ID_SERVER = " . (int) $_GET['server'] . "
		LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=packageget');
}

?>