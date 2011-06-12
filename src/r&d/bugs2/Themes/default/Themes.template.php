<?php
// Version: 1.1; Themes

// The main sub template - for theme administration.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=theme;sa=admin" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" value="0" name="options[theme_allow]" />
			<input type="hidden" value="0" name="options[theme_default]" />

			<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td colspan="3">
						<a href="', $scripturl, '?action=helpadmin;help=themes" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a>
						', $txt['themeadmin_title'], '
					</td>
				</tr>
				<tr class="windowbg">
					<td colspan="3" class="smalltext" style="padding: 2ex;">', $txt['themeadmin_explain'], '</td>
				</tr>
				<tr class="windowbg2">
					<td colspan="3"><label for="options-theme_allow"><input type="checkbox" name="options[theme_allow]" id="options-theme_allow" value="1"', !empty($modSettings['theme_allow']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['theme_allow'], '</label></td>
				</tr>
				<tr class="windowbg2">
					<td colspan="3"><label for="options-theme_default"><input type="checkbox" name="options[theme_default]" id="options-theme_default" value="1"', !empty($modSettings['theme_default']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['theme_default'], '</label></td>
				</tr>
				<tr class="windowbg2">
					<td style="width: 20ex;">', $txt['theme_guests'], ':</td>
					<td style="width: 20ex;" align="right">
						<select name="options[theme_guests]">';

	// Put an option for each theme in the select box.
	foreach ($context['themes'] as $theme)
		echo '
							<option value="', $theme['id'], '"', $modSettings['theme_guests'] == $theme['id'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';

	echo '
						</select>
					</td>
					<td class="smalltext">&nbsp; <a href="', $scripturl, '?action=theme;sa=pick;u=-1;sesc=', $context['session_id'], '">', $txt['theme_select'], '</a></td>
				</tr>
				<tr class="windowbg2">
					<td style="width: 20ex;">', $txt['theme_reset'], ':</td>
					<td style="width: 20ex;" align="right">
						<select name="theme_reset">
							<option value="-1" selected="selected">', $txt['theme_nochange'], '</option>
							<option value="0">', $txt['theme_forum_default'], '</option>';

	// Same thing, this time for changing the theme of everyone.
	foreach ($context['themes'] as $theme)
		echo '
							<option value="', $theme['id'], '">', $theme['name'], '</option>';

	echo '
						</select>
					</td>
					<td class="smalltext">&nbsp; <a href="', $scripturl, '?action=theme;sa=pick;u=0;sesc=', $context['session_id'], '">', $txt['theme_select'], '</a></td>
				</tr>
				<tr class="windowbg2">
					<td colspan="3" align="center" valign="middle" style="padding-top: 2ex; padding-bottom: 2ex;"><input type="submit" name="submit" value="' . $txt[10] . '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';

	// And lastly, link to simplemachines.org for latest themes and info!
	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder" style="margin-bottom: 2ex; margin-top: 2ex;">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=latest_themes" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['theme_latest'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" id="themeLatest">', $txt['theme_latest_fetch'], '</td>
			</tr>
		</table>';

	// Warn them if theme creation isn't possible!
	if (!$context['can_create_new'])
		echo '
		<b>', $txt['theme_install_writable'], '</b><br /><br />';

		echo '
		<form action="', $scripturl, '?action=theme;sa=install" method="post" enctype="multipart/form-data" onsubmit="return confirm(\'', $txt['theme_install_new_confirm'], '\');" accept-charset="', $context['character_set'], '">
			<table width="80%" border="0" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td><a href="', $scripturl, '?action=helpadmin;help=theme_install" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['theme_install'], '</td>
				</tr>';

	// Here's a little box for installing a new theme.
	// !!! Should the value="theme_gz" be there?!
	if ($context['can_create_new'])
		echo '
				<tr class="windowbg2">
					<td valign="top"><label for="theme_gz">', $txt['theme_install_file'], '</label>:</td>
				</tr>
				<tr class="windowbg2">
					<td style="padding-left: 20ex;"><input type="file" name="theme_gz" id="theme_gz" value="theme_gz" size="40" onchange="this.form.copy.disabled = this.value != \'\'; this.form.theme_dir.disabled = this.value != \'\';" /></td>
				</tr>';

	echo '
				<tr class="windowbg2">
					<td valign="top" style="padding-bottom: 0;"><label for="theme_dir">', $txt['theme_install_dir'], '</label>:</td>
				</tr>
				<tr class="windowbg2">
					<td style="padding-left: 20ex;"><input type="text" name="theme_dir" id="theme_dir" value="', $context['new_theme_dir'], '" size="40" style="width: 70%;" /></td>
				</tr>';

	if ($context['can_create_new'])
		echo '
				<tr class="windowbg2">
					<td valign="top" style="padding-bottom: 0;"><label for="copy">', $txt['theme_install_new'], ':</label></td>
				</tr>
				<tr class="windowbg2">
					<td style="padding-left: 20ex;"><input type="text" name="copy" id="copy" value="', $context['new_theme_name'], '" size="40" /></td>
				</tr>';

	echo '
				<tr class="windowbg2">
					<td align="right"><input type="submit" name="submit" value="', $txt['theme_install_go'], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.smfForum_scripturl = "', $scripturl, '";
			window.smfForum_sessionid = "', $context['session_id'], '";
			window.smfThemes_writable = ', $context['can_create_new'] ? 'true' : 'false', ';
		// ]]></script>';

	if (empty($modSettings['disable_smf_js']))
		echo '
		<script language="JavaScript" type="text/javascript" src="http://www.simplemachines.org/smf/latest-themes.js?language=', $context['user']['language'], '"></script>';
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var tempOldOnload;

			function smfSetLatestThemes()
			{
				if (typeof(window.smfLatestThemes) != "undefined")
					setInnerHTML(document.getElementById("themeLatest"), window.smfLatestThemes);

				if (tempOldOnload)
					tempOldOnload();
			}
		// ]]></script>';

	// Gotta love IE4, and its hatefulness...
	if ($context['browser']['is_ie4'])
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			tempOldOnload = window.onload;
			window.onload = smfSetLatestThemes;
		// ]]></script>';
	else
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			smfSetLatestThemes();
		// ]]></script>';
}

function template_list_themes()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['themeadmin_list_heading'], '</td>
			</tr>
			<tr class="windowbg">
				<td class="smalltext" style="padding: 2ex;">', $txt['themeadmin_list_tip'], '</td>
			</tr>';

	// Show each theme.... with X for delete and a link to settings.
	foreach ($context['themes'] as $theme)
	{
		echo '
			<tr class="catbg">
				<td>
					<div style="float: left;"><b><a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=settings">', $theme['name'], '</a></b>', !empty($theme['version']) ? ' <em>(' . $theme['version'] . ')</em>' : '', '</div>';

		// You *cannot* delete the default theme. It's important!
		if ($theme['id'] != 1)
			echo '
					<div style="text-align: right;"><a href="', $scripturl, '?action=theme;sa=remove;th=', $theme['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['theme_remove_confirm'], '\');"><img src="', $settings['images_url'], '/icons/delete.gif" alt="', $txt['theme_remove'], '" title="', $txt['theme_remove'], '" /></a></div>';

		echo '
				</td>
			</tr>
			<tr class="windowbg2">
				<td style="padding-left: 5ex;" class="smalltext">
					<div style="padding-bottom: 2px;"><div style="float: left; width: 38ex; padding-bottom: 2px;">', $txt['themeadmin_list_theme_dir'], ':</div> <b style="', $theme['valid_path'] ? '' : 'color: red; ', 'white-space: nowrap;">', $theme['theme_dir'], '</b>', $theme['valid_path'] ? '' : ' ' . $txt['themeadmin_list_invalid'], '</div>
					<div style="padding-bottom: 2px;"><div style="float: left; width: 38ex;">', $txt['themeadmin_list_theme_url'], ':</div> <b style="white-space: nowrap;">', $theme['theme_url'], '</b></div>
					<div style="padding-bottom: 2px;"><div style="float: left; width: 38ex;">', $txt['themeadmin_list_images_url'], ':</div> <b style="white-space: nowrap;">', $theme['images_url'], '</b></div>
				</td>
			</tr>';
	}

	echo '
		</table>

		<form action="', $scripturl, '?action=theme;sesc=', $context['session_id'], ';sa=list" method="post" accept-charset="', $context['character_set'], '">
			<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder" style="margin-top: 2ex;">
				<tr class="titlebg">
					<td colspan="2">', $txt['themeadmin_list_reset'], '</td>
				</tr>
				<tr class="windowbg2">
					<td width="30%">', $txt['themeadmin_list_reset_dir'], ':</td>
					<td><input type="text" name="reset_dir" value="', $context['reset_dir'], '" size="40" style="width: 80%;" /></td>
				</tr>
				<tr class="windowbg2">
					<td width="30%">', $txt['themeadmin_list_reset_url'], ':</td>
					<td><input type="text" name="reset_url" value="', $context['reset_url'], '" size="40" style="width: 80%;" /></td>
				</tr>
				<tr class="windowbg2">
					<td colspan="2" align="center" style="padding-bottom: 1ex;"><input type="submit" name="submit" value="', $txt['themeadmin_list_reset_go'], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_reset_list()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['themeadmin_reset_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2" class="smalltext" style="padding: 2ex;">', $txt['themeadmin_reset_tip'], '</td>
			</tr>';

	// Show each theme.... with X for delete and a link to settings.
	foreach ($context['themes'] as $theme)
	{
		echo '
			<tr class="windowbg2">
				<td style="padding-bottom: 1ex;">
					<b><a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=settings">', $theme['name'], '</a></b><br />
					<div style="padding-left: 5ex; line-height: 3ex;" class="smalltext">
						<a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=reset">', $txt['themeadmin_reset_defaults'], '</a> (', $theme['num_default_options'], ' ', $txt['themeadmin_reset_defaults_current'], ')<br />
						<a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=reset;who=1">', $txt['themeadmin_reset_members'], '</a><br />
						<a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=reset;who=2" onclick="return confirm(\'', $txt['themeadmin_reset_remove_confirm'], '\');">', $txt['themeadmin_reset_remove'], '</a> (', $theme['num_members'], ' ', $txt['themeadmin_reset_remove_current'], ')
					</div>
				</td>
			</tr>';
	}

	echo '
		</table>';
}

function template_set_options()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=theme;th=', $context['theme_settings']['theme_id'], ';sa=reset" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="who" value="', $context['theme_options_reset'] ? 1 : 0, '" />

			<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td colspan="2">', $txt['theme_options_title'], ' - ', $context['theme_settings']['name'], '</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2" class="smalltext" style="padding: 2ex;">', $context['theme_options_reset'] ? $txt['themeadmin_reset_options_info'] : $txt['theme_options_defaults'], '</td>
				</tr>';

	foreach ($context['options'] as $setting)
	{
		echo '
				<tr class="windowbg2">
					<td colspan="2">';

		if ($context['theme_options_reset'])
			echo '
						<select name="', !empty($setting['default']) ? 'default_' : '', 'options_master[', $setting['id'], ']" onchange="this.form.options_', $setting['id'], '.disabled = this.selectedIndex != 1;">
							<option value="0" selected="selected">', $txt['themeadmin_reset_options_none'], '</option>
							<option value="1">', $txt['themeadmin_reset_options_change'], '</option>
							<option value="2">', $txt['themeadmin_reset_options_remove'], '</option>
						</select>';

		if ($setting['type'] == 'checkbox')
		{
			echo '
						<input type="hidden" name="' . (!empty($setting['default']) ? 'default_' : '') . 'options[' . $setting['id'] . ']" value="0" />
						<label for="options_', $setting['id'], '"><input type="checkbox" name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="options_', $setting['id'], '"', !empty($setting['value']) ? ' checked="checked"' : '', $context['theme_options_reset'] ? ' disabled="disabled"' : '', ' value="1" class="check" /> ', $setting['label'], '</label>';
		}
		elseif ($setting['type'] == 'list')
		{
			echo '
						&nbsp;<label for="options_', $setting['id'], '">', $setting['label'], '</label>
						<select name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="options_', $setting['id'], '"', $context['theme_options_reset'] ? ' disabled="disabled"' : '', '>';

			foreach ($setting['options'] as $value => $label)
			{
				echo '
							<option value="', $value, '"', $value == $setting['value'] ? ' selected="selected"' : '', '>', $label, '</option>';
			}

			echo '
						</select>';
		}
		else
			echo '
						&nbsp;<label for="options_', $setting['id'], '">', $setting['label'], '</label>
						<input type="text" name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="options_', $setting['id'], '" value="', $setting['value'], '"', $setting['type'] == 'number' ? ' size="5"' : '', $context['theme_options_reset'] ? ' disabled="disabled"' : '', ' />';

		if (isset($setting['description']))
			echo '
						<div class="smalltext">', $setting['description'], '</div>';

		echo '
					</td>
				</tr>';
	}

	echo '
				<tr class="windowbg2">
					<td align="center" colspan="2"><br /><input type="submit" name="submit" value="', $txt[10], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_set_settings()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=theme;sa=settings;th=', $context['theme_settings']['theme_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="80%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td colspan="2"><a href="', $scripturl, '?action=helpadmin;help=theme_settings" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['theme4'], ' - ', $context['theme_settings']['name'], '</td>
				</tr>';

	// !!! Why can't I edit the default theme popup.
	if ($context['theme_settings']['theme_id'] != 1)
		echo '
				<tr class="catbg">
					<td colspan="2"><img src="', $settings['images_url'], '/icons/config_sm.gif" alt="" align="top" /> ', $txt['theme_edit'], '</td>
				</tr>
				<tr class="windowbg2">
					<td colspan="2" style="padding-bottom: 2ex;">
						<a href="', $scripturl, '?action=theme;th=', $context['theme_settings']['theme_id'], ';sesc=', $context['session_id'], ';sa=edit;filename=index.template.php">', $txt['theme_edit_index'], '</a><br />
						<a href="', $scripturl, '?action=theme;th=', $context['theme_settings']['theme_id'], ';sesc=', $context['session_id'], ';sa=edit;filename=style.css">', $txt['theme_edit_style'], '</a>
					</td>
				</tr>';

	echo '
				<tr class="catbg">
					<td colspan="2"><img src="', $settings['images_url'], '/icons/config_sm.gif" alt="" align="top" /> ', $txt['theme5'], '</td>
				</tr>
				<tr class="windowbg2">
					<td>', $txt['actual_theme_name'], '</td>
					<td><input type="text" name="options[name]" value="', $context['theme_settings']['name'], '" size="32" /></td>
				</tr>
				<tr class="windowbg2">
					<td>', $txt['actual_theme_url'], '</td>
					<td><input type="text" name="options[theme_url]" value="', $context['theme_settings']['actual_theme_url'], '" size="50" style="max-width: 100%; width: 50ex;" /></td>
				</tr>
				<tr class="windowbg2">
					<td>', $txt['actual_images_url'], '</td>
					<td><input type="text" name="options[images_url]" value="', $context['theme_settings']['actual_images_url'], '" size="50" style="max-width: 100%; width: 50ex;" /></td>
				</tr>
				<tr class="windowbg2">
					<td style="padding-bottom: 2ex;">', $txt['actual_theme_dir'], '</td>
					<td style="padding-bottom: 2ex;"><input type="text" name="options[theme_dir]" value="', $context['theme_settings']['actual_theme_dir'], '" size="50" style="max-width: 100%; width: 50ex;" /></td>
				</tr>
				<tr class="catbg">
					<td colspan="2"><img src="', $settings['images_url'], '/icons/config_sm.gif" alt="" align="top" /> ', $txt['theme6'], '</td>
				</tr>';

	foreach ($context['settings'] as $setting)
	{
		echo '
			<tr class="windowbg2">
				<td colspan="2">';

		if ($setting['type'] == 'checkbox')
			echo '
					<input type="hidden" name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" value="0" />
					<label for="', $setting['id'], '"><input type="checkbox" name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="', $setting['id'], '"', !empty($setting['value']) ? ' checked="checked"' : '', ' value="1" class="check" /> ', $setting['label'], '</label>';
		elseif ($setting['type'] == 'list')
		{
			echo '
					<label for="', $setting['id'], '">', $setting['label'], '</label>
					<select name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="', $setting['id'], '">';

			foreach ($setting['options'] as $value => $label)
			{
				echo '
						<option value="', $value, '"', $value == $setting['value'] ? ' selected="selected"' : '', '>', $label, '</option>';
			}

			echo '
					</select>';
		}
		else
			echo '
					<label for="', $setting['id'], '">', $setting['label'], '</label>
					<input type="text" name="', !empty($setting['default']) ? 'default_' : '', 'options[', $setting['id'], ']" id="', $setting['id'], '" value="', $setting['value'], '"', $setting['type'] == 'number' ? ' size="5"' : ' size="40"', ' />';

		if (isset($setting['description']))
			echo '
					<div class="smalltext">', $setting['description'], '</div>';

		echo '
				</td>
			</tr>';
	}

	echo '
				<tr class="windowbg2">
					<td align="center" colspan="2" style="padding-top: 1ex; padding-bottom: 1ex;"><input type="submit" name="submit" value="', $txt[10], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

// This template allows for the selection of different themes ;).
function template_pick()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Just go through each theme and show its information - thumbnail, etc.
	foreach ($context['available_themes'] as $theme)
		echo '
	<table align="center" width="85%" cellpadding="3" cellspacing="0" border="0" class="tborder">
		<tr class="', $theme['selected'] ? 'windowbg' : 'windowbg2', '">
			<td rowspan="2" width="126" height="120"><img src="', $theme['thumbnail_href'], '" alt="" /></td>
			<td valign="top" style="padding-top: 5px;">
				<div style="font-size: larger; padding-bottom: 6px;"><b><a href="', $scripturl, '?action=theme;sa=pick;u=', $context['current_member'], ';th=', $theme['id'], ';sesc=', $context['session_id'], '">', $theme['name'], '</a></b></div>
				', $theme['description'], '
			</td>
		</tr>
		<tr class="', $theme['selected'] ? 'windowbg' : 'windowbg2', '">
			<td valign="bottom" align="right" style="padding: 6px; padding-top: 0;">
				<div style="float: left;" class="smalltext"><i>', $theme['num_users'], ' ', ($theme['num_users'] == 1 ? $txt['theme_user'] : $txt['theme_users']), '</i></div>
				<a href="', $scripturl, '?action=theme;sa=pick;u=', $context['current_member'], ';th=', $theme['id'], ';sesc=', $context['session_id'], '">', $txt['theme_set'], '</a> |
				<a href="', $scripturl, '?action=theme;sa=pick;u=', $context['current_member'], ';theme=', $theme['id'], ';sesc=', $context['session_id'], '">', $txt['theme_preview'], '</a>
			</td>
		</tr>
	</table>
	<br />';
}

// Okay, that theme was installed successfully!
function template_installed()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Not much to show except a link back...
	echo '
		<table width="90%" cellpadding="4" cellspacing="0" class="tborder">
			<tr class="titlebg">
				<td>', $context['page_title'], '</td>
			</tr>
			<tr class="windowbg2">
				<td>
					<a href="', $scripturl, '?action=theme;sa=settings;th=', $context['installed_theme']['id'], ';sesc=', $context['session_id'], '">', $context['installed_theme']['name'], '</a> ', $txt['theme_installed_message'], '<br />
					<br />
					<a href="', $scripturl, '?action=theme;sa=admin;sesc=', $context['session_id'], '">', $txt[250], '</a>
				</td>
			</tr>
		</table>';
}

function template_edit_list()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['themeadmin_edit_title'], '</td>
			</tr>';

	foreach ($context['themes'] as $theme)
	{
		echo '
			<tr class="windowbg2">
				<td style="padding-bottom: 1ex;">
					<b><a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=edit">', $theme['name'], '</a></b>', !empty($theme['version']) ? ' <em>(' . $theme['version'] . ')</em>' : '', '<br />
					<div style="padding-left: 5ex; line-height: 3ex;" class="smalltext">
						<a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=edit">', $txt['themeadmin_edit_browse'], '</a><br />', $theme['can_edit_style'] ? '
						<a href="' . $scripturl . '?action=theme;th=' . $theme['id'] . ';sesc=' . $context['session_id'] . ';sa=edit;filename=style.css">' . $txt['themeadmin_edit_style'] . '</a><br />' : '', '
						<a href="', $scripturl, '?action=theme;th=', $theme['id'], ';sesc=', $context['session_id'], ';sa=copy">', $txt['themeadmin_edit_copy_template'], '</a><br />
					</div>
				</td>
			</tr>';
	}

	echo '
		</table>';
}

function template_copy_template()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['themeadmin_edit_filename'], '</td>
				<td></td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2" class="smalltext" style="padding: 2ex;">', $txt['themeadmin_edit_copy_warning'], '</td>
			</tr>';

	$alternate = false;
	foreach ($context['available_templates'] as $template)
	{
		echo '
			<tr class="windowbg', $alternate ? '2' : '', '">
				<td>', $template['filename'], $template['already_exists'] ? ' <em>(' . $txt['themeadmin_edit_exists'] . ')</em>' : '', '</td>
				<td style="text-align: right;">';

		if ($template['can_copy'])
			echo '<a href="', $scripturl, '?action=theme;th=', $context['theme_id'], ';sesc=', $context['session_id'], ';sa=copy;template=', $template['value'], '" onclick="return confirm(\'', $template['already_exists'] ? $txt['themeadmin_edit_overwrite_confirm'] : $txt['themeadmin_edit_copy_confirm'], '\');">', $txt['themeadmin_edit_do_copy'], '</a>';
		else
			echo $txt['themeadmin_edit_no_copy'];

		echo '</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
		</table>';
}

function template_edit_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['themeadmin_edit_filename'], '</td>
				<td style="width: 24ex; text-align: right;">', $txt['themeadmin_edit_modified'], '</td>
				<td style="width: 15ex; text-align: right;">', $txt['themeadmin_edit_size'], '</td>
			</tr>';

	foreach ($context['theme_files'] as $file)
	{
		echo '
			<tr class="windowbg2">
				<td>';
		if ($file['is_editable'])
			echo '<a href="', $file['href'], '"', $file['is_template'] ? ' style="font-weight: bold;"' : '', '>', $file['filename'], '</a>';
		elseif ($file['is_directory'])
			echo '<a href="', $file['href'], '">', $file['filename'], '</a>';
		else
			echo $file['filename'];
		echo '
				</td>
				<td style="text-align: right;" class="smalltext">', !empty($file['last_modified']) ? $file['last_modified'] : '', '</td>
				<td style="text-align: right;">', $file['size'], '</td>
			</tr>';
	}

	echo '
		</table>';
}

// Wanna edit the stylesheet?
function template_edit_style()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['session_error'])
		echo '
		<div style="color: red; padding: 2ex;">
			', $txt['error_session_timeout'], '
		</div>';

	// From now on no one can complain that editing css is difficult. If you disagree, go to www.w3schools.com.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var previewData = "";
			var previewTimeout;

			// Load up a page, but apply our stylesheet.
			function navigatePreview(url)
			{
				var myDoc = new XMLHttpRequest();
				myDoc.onreadystatechange = function ()
				{
					if (myDoc.readyState != 4)
						return;

					if (myDoc.responseText != null && myDoc.status == 200)
					{
						previewData = myDoc.responseText;
						document.getElementById("css_preview_box").style.display = "";

						// Revert to the theme they actually use ;).
						var tempImage = new Image();
						tempImage.src = "', $scripturl, '?action=theme;sa=edit;theme=', $settings['theme_id'], ';preview;" + (new Date().getTime());

						refreshPreviewCache = null;
						refreshPreview(false);
					}
				};

				var anchor = "";
				if (url.indexOf("#") != -1)
				{
					anchor = url.substr(url.indexOf("#"));
					url = url.substr(0, url.indexOf("#"));
				}

				myDoc.open("GET", url + (url.indexOf("?") == -1 ? "?" : ";") + "theme=', $context['theme_id'], '" + anchor, true);
				myDoc.send(null);
			}
			navigatePreview(smf_scripturl);

			var refreshPreviewCache;
			function refreshPreview(check)
			{
				var identical = document.forms.stylesheetForm.entire_file.value == refreshPreviewCache;

				// Don\'t reflow the whole thing if nothing changed!!
				if (check && identical)
					return;
				refreshPreviewCache = document.forms.stylesheetForm.entire_file.value;

				// Try to do it without a complete reparse.
				if (identical)
				{
					try
					{
					';
	if ($context['browser']['is_ie'])
		echo '
						var sheets = frames["css_preview_box"].document.styleSheets;
						for (var j = 0; j < sheets.length; j++)
						{
							if (sheets[j].id == "css_preview_box")
								sheets[j].cssText = document.forms.stylesheetForm.entire_file.value;
						}';
	else
		echo '
						setInnerHTML(frames["css_preview_box"].document.getElementById("css_preview_sheet"), document.forms.stylesheetForm.entire_file.value);';
	echo '
					}
					catch (e)
					{
						identical = false;
					}
				}

				// This will work most of the time... could be done with an after-apply, maybe.
				if (!identical)
				{
					var data = previewData + "";
					data = data.replace(/<link rel="stylesheet"[^>]+?>/, "<style type=\"text/css\" id=\"css_preview_sheet\">" + document.forms.stylesheetForm.entire_file.value + "</style>");

					frames["css_preview_box"].document.open();
					frames["css_preview_box"].document.write(data);
					frames["css_preview_box"].document.close();

					// Next, fix all its links so we can handle them and reapply the new css!
					frames["css_preview_box"].onload = function ()
					{
						var fixLinks = frames["css_preview_box"].document.getElementsByTagName("a");
						for (var i = 0; i < fixLinks.length; i++)
						{
							if (fixLinks[i].onclick)
								continue;
							fixLinks[i].onclick = function ()
							{
								window.parent.navigatePreview(this.href);
								return false;
							};
						}
					};
				}
			}

			// The idea here is simple: don\'t refresh the preview on every keypress, but do refresh after they type.
			function setPreviewTimeout()
			{
				if (previewTimeout)
				{
					window.clearTimeout(previewTimeout);
					previewTimeout = null;
				}

				previewTimeout = window.setTimeout("refreshPreview(true); previewTimeout = null;", 500);
			}
		// ]]></script>
		<iframe id="css_preview_box" name="css_preview_box" src="about:blank" width="100%" height="300" frameborder="0" style="display: none; margin-bottom: 2ex; border: 1px solid black;"></iframe>';

	// Just show a big box.... grey out the Save button if it's not saveable... (ie. not 777.)
	echo '
		<form action="', $scripturl, '?action=theme;th=', $context['theme_id'], ';sa=edit" method="post" accept-charset="', $context['character_set'], '" name="stylesheetForm" id="stylesheetForm">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder" align="center" style="table-layout: fixed;">
				<tr class="titlebg">
					<td>', $txt['theme_edit'], ' - ', $context['edit_filename'], '</td>
				</tr>
				<tr class="windowbg2">
					<td align="center" style="padding-bottom: 1ex;">';

	if (!$context['allow_save'])
		echo '
						', $txt['theme_edit_no_save'], ': ', $context['allow_save_filename'], '<br />';

	echo '
						<textarea name="entire_file" cols="80" rows="20" style="width: 96%; font-family: monospace; margin-top: 1ex; white-space: pre;" onkeyup="setPreviewTimeout();" onchange="refreshPreview(true);">', $context['entire_file'], '</textarea><br />
						<input type="submit" name="submit" value="', $txt['theme_edit_save'], '"', $context['allow_save'] ? '' : ' disabled="disabled"', ' style="margin-top: 1ex;" />
						<input type="button" value="', $txt['themeadmin_edit_preview'], '" onclick="refreshPreview(false);" />
					</td>
				</tr>
			</table>

			<input type="hidden" name="filename" value="', $context['edit_filename'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

// This edits the template...
function template_edit_template()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['session_error'])
		echo '
				<div style="color: red; padding: 2ex; padding-top: 1ex;">
					', $txt['error_session_timeout'], '
				</div>';

	if (isset($context['parse_error']))
		echo '
				<div style="color: red; padding: 2ex; padding-top: 1ex;">
					', $txt['themeadmin_edit_error'], '
					<div style="padding-left: 4ex;"><tt>', $context['parse_error'], '</tt></div>
				</div>';

	// Just show a big box.... grey out the Save button if it's not saveable... (ie. not 777.)
	echo '
		<form action="', $scripturl, '?action=theme;th=', $context['theme_id'], ';sa=edit" method="post" accept-charset="', $context['character_set'], '">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder" align="center" style="table-layout: fixed;">
				<tr class="titlebg">
					<td>', $txt['theme_edit'], ' - ', $context['edit_filename'], '</td>
				</tr>
				<tr class="windowbg2">
					<td style="padding-bottom: 1ex;">';

	if (!$context['allow_save'])
		echo '
						', $txt['theme_edit_no_save'], ': ', $context['allow_save_filename'], '<br />';

	foreach ($context['file_parts'] as $part)
		echo '
						', $txt['themeadmin_edit_on_line'], ' ', $part['line'], ':<br />
						<div align="center"><textarea name="entire_file[]" cols="80" rows="', $part['lines'] > 14 ? '14' : $part['lines'], '" style="width: 96%; font-family: monospace; margin-top: 1ex; white-space: pre;">', $part['data'], '</textarea></div>';

	echo '
						<input type="submit" name="submit" value="', $txt['theme_edit_save'], '"', $context['allow_save'] ? '' : ' disabled="disabled"', ' style="margin-top: 1ex;" />
					</td>
				</tr>
			</table>

			<input type="hidden" name="filename" value="', $context['edit_filename'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_edit_file()
{
	global $context, $settings, $options, $scripturl, $txt;

	if ($context['session_error'])
		echo '
				<div style="color: red; padding: 2ex;">
					', $txt['error_session_timeout'], '
				</div>';

	// Just show a big box.... grey out the Save button if it's not saveable... (ie. not 777.)
	echo '
		<form action="', $scripturl, '?action=theme;th=', $context['theme_id'], ';sa=edit" method="post" accept-charset="', $context['character_set'], '">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder" align="center" style="table-layout: fixed;">
				<tr class="titlebg">
					<td>', $txt['theme_edit'], ' - ', $context['edit_filename'], '</td>
				</tr>
				<tr class="windowbg2">
					<td align="center" style="padding-bottom: 1ex;">';

	if (!$context['allow_save'])
		echo '
						', $txt['theme_edit_no_save'], ': ', $context['allow_save_filename'], '<br />';

	echo '
						<textarea name="entire_file" cols="80" rows="20" style="width: 96%; font-family: monospace; margin-top: 1ex; white-space: pre;">', $context['entire_file'], '</textarea><br />
						<input type="submit" name="submit" value="', $txt['theme_edit_save'], '"', $context['allow_save'] ? '' : ' disabled="disabled"', ' style="margin-top: 1ex;" />
					</td>
				</tr>
			</table>

			<input type="hidden" name="filename" value="', $context['edit_filename'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

?>