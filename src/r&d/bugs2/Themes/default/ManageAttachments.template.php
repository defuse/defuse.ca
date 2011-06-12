<?php
// Version: 1.1.9; ManageAttachments

function template_manage_files_above()
{
	global $context, $settings, $options, $scripturl, $txt;

	// !!! Needs a helptext for manage_files, uploadable_avatars.
	echo '
	<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder">
		<tr class="titlebg">';

	// shall we use the tabs?
	if (!empty($settings['use_tabs']))
	{
			echo '
			<td><a href="' . $scripturl . '?action=helpadmin;help=manage_files" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt[119] . '" align="top" /></a> ', $txt['smf201'], '</td>
		</tr>
		<tr class="windowbg">
			<td class="smalltext" style="padding: 2ex;">
				', $context['description'], '
			</td>
		</tr>
	</table>';

		// the tabs
		echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
		<tr>
			<td class="maintab_first">&nbsp;</td>';

		// Show the attachment settings button.
		echo $context['selected'] == 'attachment_settings' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['selected'] == 'attachment_settings' ? 'active_' : '' , 'back"><a href="' . $scripturl . '?action=manageattachments">', $txt['attachment_manager_settings'], '</a></td>' , $context['selected'] == 'attachment_settings' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		// Show the avatar settings button.
		echo $context['selected'] == 'avatar_settings' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['selected'] == 'avatar_settings' ? 'active_' : '' , 'back"><a href="' . $scripturl . '?action=manageattachments;sa=avatars">', $txt['attachment_manager_avatar_settings'], '</a></td>' , $context['selected'] == 'avatar_settings' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		// Show the browse button.
		echo $context['selected'] == 'browse' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['selected'] == 'browse' ? 'active_' : '' , 'back"><a href="' . $scripturl . '?action=manageattachments;sa=browse">', $txt['attachment_manager_browse'], '</a></td>' , $context['selected'] == 'browse' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		// Show the maintenance button.
		echo $context['selected'] == 'maintenance' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['selected'] == 'maintenance' ? 'active_' : '' , 'back"><a href="' . $scripturl . '?action=manageattachments;sa=maintenance">', $txt['attachment_manager_maintenance'], '</a></td>' , $context['selected'] == 'maintenance' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		// the end of tabs
		echo '
			<td class="maintab_last">&nbsp;</td>
		</tr>
	</table><br />';
	}
	// if not use the old style
	else
	{
		echo'	
			<td><a href="' . $scripturl . '?action=helpadmin;help=manage_files" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt[119] . '" border="0" align="top" /></a> ', $txt['smf201'], '</td>
		</tr>
		<tr class="catbg">
			<td align="left">';

		// Show the attachment settings button.
		echo '
				', $context['selected'] == 'attachment_settings' ? '<a href="' . $scripturl . '?action=manageattachments"><img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /></a> ' : '', '<a href="' . $scripturl . '?action=manageattachments">', $txt['attachment_manager_settings'], '</a> | ';

		// Show the avatar settings button.
		echo '
				', $context['selected'] == 'avatar_settings' ? '<a href="' . $scripturl . '?action=manageattachments;sa=avatars"><img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /></a> ' : '', '<a href="' . $scripturl . '?action=manageattachments;sa=avatars">', $txt['attachment_manager_avatar_settings'], '</a> | ';

		// Show the browse button.
		echo '
				', $context['selected'] == 'browse' ? '<a href="' . $scripturl . '?action=manageattachments;sa=browse"><img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /></a> ' : '', '<a href="' . $scripturl . '?action=manageattachments;sa=browse">', $txt['attachment_manager_browse'], '</a> | ';

		// Show the maintenance button.
		echo '
				', $context['selected'] == 'maintenance' ? '<a href="' . $scripturl . '?action=manageattachments;sa=maintenance"><img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /></a> ' : '', '<a href="' . $scripturl . '?action=manageattachments;sa=maintenance">', $txt['attachment_manager_maintenance'], '</a>
			</td>
		</tr>
		<tr class="windowbg">
			<td class="smalltext" style="padding: 2ex;">
				', $context['description'], '
			</td>
		</tr>
	</table>
	<br />';
	}
}

function template_manage_files_below()
{
	global $context, $settings, $options;
}

function template_attachments()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
<form action="', $scripturl, '?action=manageattachments" method="post" accept-charset="', $context['character_set'], '">
	<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
		<tr class="titlebg">
			<td colspan="2"><a href="', $scripturl, '?action=helpadmin;help=attachmentEnable" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="Help" /></a> ', $txt['attachment_manager_settings'], '</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentEnable">', $txt['attachment_mode'], ':</label></td>
			<td>
				<select name="attachmentEnable" id="attachmentEnable">
					<option value="0"', empty($modSettings['attachmentEnable']) ? ' selected="selected"' : '', '>', $txt['attachment_mode_deactivate'], '</option>
					<option value="1"', !empty($modSettings['attachmentEnable']) && $modSettings['attachmentEnable'] == 1 ? ' selected="selected"' : '', '>', $txt['attachment_mode_enable_all'], '</option>
					<option value="2"', !empty($modSettings['attachmentEnable']) && $modSettings['attachmentEnable'] == 2 ? ' selected="selected"' : '', '>', $txt['attachment_mode_disable_new'], '</option>
				</select>
			</td>
		</tr><tr class="windowbg2">
			<td colspan="2"><hr /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentCheckExtensions">', $txt['attachmentCheckExtensions'], ':</label></td>
			<td><input type="checkbox" name="attachmentCheckExtensions" id="attachmentCheckExtensions" value="1" class="check"', empty($modSettings['attachmentCheckExtensions']) ? '' : ' checked="checked"', ' /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentExtensions">', $txt['attachmentExtensions'], '</label>:</td>
			<td><input type="text" name="attachmentExtensions" id="attachmentExtensions" value="', $modSettings['attachmentExtensions'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td colspan="2"><hr /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentUploadDir"', $context['valid_upload_dir'] ? '' : ' style="color: red; font-weight: bold;"', '>', $txt['attachmentUploadDir'], '</label>:</td>
			<td><input type="text" name="attachmentUploadDir" id="attachmentUploadDir" value="', $modSettings['attachmentUploadDir'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentDirSizeLimit">', $txt['attachmentDirSizeLimit'], '</label>:</td>
			<td><input type="text" name="attachmentDirSizeLimit" id="attachmentDirSizeLimit" value="', $modSettings['attachmentDirSizeLimit'], '" size="6" /> ', $txt['smf211'], '</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentPostLimit">', $txt['attachmentPostLimit'], '</label>:</td>
			<td><input type="text" name="attachmentPostLimit" id="attachmentPostLimit" value="', $modSettings['attachmentPostLimit'], '" size="6" /> ', $txt['smf211'], '</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentSizeLimit">', $txt['attachmentSizeLimit'], '</label>:</td>
			<td><input type="text" name="attachmentSizeLimit" id="attachmentSizeLimit" value="', $modSettings['attachmentSizeLimit'], '" size="6" /> ', $txt['smf211'], '</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentNumPerPostLimit">', $txt['attachmentNumPerPostLimit'], '</label>:</td>
			<td><input type="text" name="attachmentNumPerPostLimit" id="attachmentNumPerPostLimit" value="', $modSettings['attachmentNumPerPostLimit'], '" size="6" /></td>
		</tr><tr class="windowbg2">
			<td colspan="2"><hr /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentShowImages">', $txt['attachmentShowImages'], ':</label></td>
			<td><input type="checkbox" name="attachmentShowImages" id="attachmentShowImages" value="1" class="check"', empty($modSettings['attachmentShowImages']) ? '' : ' checked="checked"', ' /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentThumbnails">', $txt['attachmentThumbnails'], '</label>:</td>
			<td><input type="checkbox" name="attachmentThumbnails" id="attachmentThumbnails" value="1" class="check"', empty($modSettings['attachmentThumbnails']) ? '' : ' checked="checked"', ' /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentThumbWidth">', $txt['attachmentThumbWidth'], '</label>:</td>
			<td><input type="text" name="attachmentThumbWidth" id="attachmentThumbWidth" value="', empty($modSettings['attachmentThumbWidth']) ? '0' : $modSettings['attachmentThumbWidth'], '" size="6" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentThumbHeight">', $txt['attachmentThumbHeight'], '</label>:</td>
			<td><input type="text" name="attachmentThumbHeight" id="attachmentThumbHeight" value="', empty($modSettings['attachmentThumbHeight']) ? '0' : $modSettings['attachmentThumbHeight'], '" size="6" /></td>
		</tr><tr class="windowbg2">
			<td colspan="2" align="center">
				<input type="submit" name="attachmentSettings" value="', $txt['attachment_manager_save'], '" />
				<input type="hidden" name="sa" value="attachments" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>
</form>';
}

function template_avatars()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
<form action="', $scripturl, '?action=manageattachments" method="post" accept-charset="', $context['character_set'], '">
	<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
		<tr class="titlebg">
			<td colspan="2"><a href="', $scripturl, '?action=helpadmin;help=avatar_allow_server_stored" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="Help" /></a> ', $txt['avatar_server_stored'], '</td>';
	if (!$context['gd_installed'])
		echo '
		</tr><tr class="windowbg2">
			<td colspan="2" align="center" style="color: red; padding: 2em;">', $txt['avatar_gd_warning'], '</td>';

	if ($context['can_change_permissions'])
	{
		echo '
		<tr class="windowbg2">
			<td width="50%" valign="top" align="right"><label for="profile_server_avatar">', $txt['avatar_server_stored_groups'], '</label>:</td>
			<td>';

		theme_inline_permissions('profile_server_avatar');

		echo '
			</td>
		</tr>';
	}

	echo '
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_directory"', $context['valid_avatar_dir'] ? '' : ' style="color: red; font-weight: bold;"', '>', $txt['avatar_directory'], '</label>:</td>
			<td><input type="text" name="avatar_directory" id="avatar_directory" value="', $modSettings['avatar_directory'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_url">', $txt['avatar_url'], '</label>:</td>
			<td><input type="text" name="avatar_url" id="avatar_url" value="', $modSettings['avatar_url'], '" size="40" /></td>
		</tr>
		<tr>
			<td colspan="2" class="titlebg"><a href="', $scripturl, '?action=helpadmin;help=avatar_allow_external_url" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="Help" /></a> ', $txt['avatar_external'], '</td>
		</tr>';
	if ($context['can_change_permissions'])
	{
		echo '
		<tr class="windowbg2">
			<td width="50%" valign="top" align="right"><label for="external_url_groups">', $txt['avatar_external_url_groups'], '</label>:</td>
			<td>';

		theme_inline_permissions('profile_remote_avatar');

		echo '
			</td>
		</tr>';
	}
	echo '
		<tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_download_external">', $txt['avatar_download_external'], ' <a href="', $scripturl, '?action=helpadmin;help=avatar_download_external" onclick="return reqWin(this.href);" class="help">(?)</a>:</label></td>
			<td><input type="checkbox" name="avatar_download_external" id="avatar_download_external" value="1" class="check"', empty($modSettings['avatar_download_external']) ? '' : ' checked="checked"', ' onchange="updateStatus()" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_max_width_external">', $txt['avatar_max_width_external'], '</label>:<div class="smalltext" style="font-weight: bold;">', $txt['avatar_dimension_note'], '</div></td>
			<td>
				<input type="text" name="avatar_max_width_external" id="avatar_max_width_external" value="', $modSettings['avatar_max_width_external'], '" size="6" />
			</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_max_height_external">', $txt['avatar_max_height_external'], '</label>:<div class="smalltext" style="font-weight: bold;">', $txt['avatar_dimension_note'], '</div></td>
			<td>
				<input type="text" name="avatar_max_height_external" id="avatar_max_height_external" value="', $modSettings['avatar_max_height_external'], '" size="6" />
			</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_action_too_large">', $txt['avatar_action_too_large'], '</label></td>
			<td>
				<select name="avatar_action_too_large" id="avatar_action_too_large">
					<option value="option_refuse"', $modSettings['avatar_action_too_large'] == 'option_refuse' ? ' selected="selected"' : '', '>', $txt['option_refuse'], '</option>
					<option value="option_html_resize"', $modSettings['avatar_action_too_large'] == 'option_html_resize' ? ' selected="selected"' : '', '>', $txt['option_html_resize'], '</option>
					<option value="option_js_resize"', $modSettings['avatar_action_too_large'] == 'option_js_resize' ? ' selected="selected"' : '', '>', $txt['option_js_resize'], '</option>
					<option value="option_download_and_resize"', $modSettings['avatar_action_too_large'] == 'option_download_and_resize' ? ' selected="selected"' : '', '>', $txt['option_download_and_resize'], '</option>
				</select>
			</td>
		</tr><tr>
			<td colspan="2" class="titlebg"><a href="', $scripturl, '?action=helpadmin;help=avatar_allow_upload" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="Help" /></a> ', $txt['avatar_upload'], '</td>';

	if ($context['can_change_permissions'])
	{
		echo '
		<tr class="windowbg2">
			<td width="50%" valign="top" align="right"><label for="profile_upload_avatar">', $txt['avatar_upload_groups'], '</label>:</td>
			<td>';

		theme_inline_permissions('profile_upload_avatar');

		echo '
			</td>
		</tr>';
	}

	echo '
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_max_width_upload">', $txt['avatar_max_width_upload'], '</label>:<div class="smalltext" style="font-weight: bold;">', $txt['avatar_dimension_note'], '</div></td>
			<td><input type="text" name="avatar_max_width_upload" id="avatar_max_width_upload" value="', $modSettings['avatar_max_width_upload'], '" size="6" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_max_height_upload">', $txt['avatar_max_height_upload'], '</label>:<div class="smalltext" style="font-weight: bold;">', $txt['avatar_dimension_note'], '</div></td>
			<td><input type="text" name="avatar_max_height_upload" id="avatar_max_height_upload" value="', $modSettings['avatar_max_height_upload'], '" size="6" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_resize_upload">', $txt['avatar_resize_upload'], ':</label><div class="smalltext" style="font-weight: bold;', $context['gd_installed'] ? '' : 'color: red;', '">', $txt['avatar_resize_upload_note'], '</div></td>
			<td><input type="checkbox" name="avatar_resize_upload" id="avatar_resize_upload" value="1" class="check"', empty($modSettings['avatar_resize_upload']) ? '' : ' checked="checked"', ' /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="avatar_download_png">', $txt['avatar_download_png'], ' <a href="', $scripturl, '?action=helpadmin;help=avatar_download_png" onclick="return reqWin(this.href);" class="help">(?)</a>:</label></td>
			<td><input type="checkbox" name="avatar_download_png" id="avatar_download_png" value="1" class="check"', empty($modSettings['avatar_download_png']) ? '' : ' checked="checked"', ' /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="custom_avatar_enabled">', $txt['custom_avatar_enabled'], '</label></td>
			<td>
				<select name="custom_avatar_enabled" id="custom_avatar_enabled" onchange="updateStatus()">
					<option value="0"', empty($modSettings['custom_avatar_enabled']) ? ' selected="selected"' : '', '>', $txt['option_attachment_dir'], '</option>
					<option value="1"', empty($modSettings['custom_avatar_enabled']) ? '' : ' selected="selected"', '>', $txt['option_specified_dir'], '</option>
				</select>
			</td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right">
				<label for="custom_avatar_dir"', $context['valid_custom_avatar_dir'] ? '' : ' style="color: red; font-weight: bold;"', '>', $txt['custom_avatar_dir'], '</label>:<br />
				<span class="smalltext">', $txt['custom_avatar_dir_desc'], '</span>
			</td>
			<td><input type="text" name="custom_avatar_dir" id="custom_avatar_dir" value="', empty($modSettings['custom_avatar_dir']) ? '' : $modSettings['custom_avatar_dir'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="custom_avatar_url">', $txt['custom_avatar_url'], '</label>:</td>
			<td><input type="text" name="custom_avatar_url" id="custom_avatar_url" value="', empty($modSettings['custom_avatar_url']) ? '' : $modSettings['custom_avatar_url'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td colspan="2" align="center">
				<input type="submit" name="avatarSettings" value="', $txt['attachment_manager_save'], '" />
				<input type="hidden" name="sa" value="avatars" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>
</form>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	function updateStatus()
	{
		document.getElementById("avatar_max_width_external").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("avatar_max_height_external").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("avatar_action_too_large").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("custom_avatar_dir").disabled = document.getElementById("custom_avatar_enabled").value == 0;
		document.getElementById("custom_avatar_url").disabled = document.getElementById("custom_avatar_enabled").value == 0;

	}
	window.onload = updateStatus;
// ]]></script>
';
}

function template_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
<form action="', $scripturl, '?action=manageattachments;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', ';sa=remove" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');">
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
		<tr class="titlebg">
			<td colspan="5">', $txt['attachment_manager_browse_files'], '</td>
		</tr>';

	// shall we use the tabs?
	if (!empty($settings['use_tabs']))
	{
		echo '
	</table>';

		echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 1ex; margin-left: 10px;">
		<tr>
			<td class="maintab_first">&nbsp;</td>';

		echo $context['browse_type'] == 'attachments' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'attachments' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=manageattachments;sa=browse;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $txt['attachment_manager_attachments'], '</a></td>' , $context['browse_type'] == 'attachments' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo $context['browse_type'] == 'avatars' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'avatars' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=manageattachments;sa=browse;avatars;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $txt['attachment_manager_avatars'], '</a></td>' , $context['browse_type'] == 'avatars' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo $context['browse_type'] == 'thumbs' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'thumbs' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=manageattachments;sa=browse;thumbs;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $txt['attachment_manager_thumbs'], '</a></td>' , $context['browse_type'] == 'thumbs' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo '
			<td class="maintab_last">&nbsp;</td>
		</tr>
	</table>';

		echo '
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
		<tr class="titlebg">';
	}
	// if not, use the old style
	else
	{	
		echo '
		<tr class="catbg">
			<td colspan="5">
				<a href="', $scripturl, '?action=manageattachments;sa=browse;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'attachments' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_attachments'], '</a>&nbsp;|&nbsp;
				<a href="', $scripturl, '?action=manageattachments;sa=browse;avatars;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_avatars'], '</a>&nbsp;|&nbsp;
				<a href="', $scripturl, '?action=manageattachments;sa=browse;thumbs;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'thumbs' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_thumbs'], '</a>
			</td>
		</tr><tr class="titlebg">';
	}
	
	echo '
			<td nowrap="nowrap"><a href="', $scripturl, '?action=manageattachments;sa=browse;', $context['browse_type'], ';sort=name', $context['sort_by'] == 'name' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['smf213'], $context['sort_by'] == 'name' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=manageattachments;sa=browse;', $context['browse_type'], ';sort=size', $context['sort_by'] == 'size' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['smf214'], $context['sort_by'] == 'size' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=manageattachments;sa=browse;', $context['browse_type'], ';sort=member', $context['sort_by'] == 'member' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? $txt['attachment_manager_member'] : $txt[279], $context['sort_by'] == 'member' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=manageattachments;sa=browse;', $context['browse_type'], ';sort=date', $context['sort_by'] == 'date' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? $txt['attachment_manager_last_active'] : $txt[317], $context['sort_by'] == 'date' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap" align="center"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></td>
		</tr>';
	$alternate = false;
	foreach ($context['posts'] as $post)
	{
		echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td>', $post['attachment']['link'], empty($post['attachment']['width']) || empty($post['attachment']['height']) ? '' : ' <span class="smalltext">' . $post['attachment']['width'] . 'x' . $post['attachment']['height'] . '</span>', '</td>
			<td align="right">', $post['attachment']['size'], $txt['smf211'], '</td>
			<td>', $post['poster']['link'], '</td>
			<td class="smalltext">', $post['time'], $context['browse_type'] != 'avatars' ? '<br />' . $txt['smf88'] . ' ' . $post['link'] : '', '</td>
			<td align="center"><input type="checkbox" name="remove[', $post['attachment']['id'], ']" class="check" /></td>
		</tr>';
		$alternate = !$alternate;
	}
	echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td align="right" colspan="5">
				<input type="submit" name="remove_submit" value="', $txt['smf138'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="type" value="', $context['browse_type'], '" />
				<input type="hidden" name="start" value="', $context['start'], '" />
			</td>
		</tr>
		<tr class="catbg">
			<td align="left" colspan="5" style="padding: 5px;"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>
</form>';
}

function template_maintenance()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['smf203'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%" valign="top" style="padding-bottom: 2ex;">
				<table border="0" cellspacing="0" cellpadding="3">
					<tr>
						<td>', $txt['smf204'], ':</td><td>', $context['num_attachments'], '</td>
					</tr><tr>
						<td>', $txt['attachment_manager_total_avatars'], ':</td><td>', $context['num_avatars'], '</td>
					</tr><tr>
						<td>', $txt['smf205'], ':</td><td>', $context['attachment_total_size'], ' ', $txt['smf211'], ' <a href="', $scripturl, '?action=manageattachments;sa=repair;sesc=', $context['session_id'], '">[', $txt['attachment_manager_repair'], ']</a></td>
					</tr><tr>
						<td>', $txt['smf206'], ':</td><td>', isset($context['attachment_space']) ? $context['attachment_space'] . ' ' . $txt['smf211'] : $txt['smf215'], '</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['smf207'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%" valign="top">
				<form action="', $scripturl, '?action=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt[72], ': <input type="text" name="notice" value="', $txt['smf216'], '" size="40" /><br />
					', $txt['smf209'], ' <input type="text" name="age" value="25" size="4" /> ', $txt[579], ' <input type="submit" name="submit" value="', $txt[31], '" />
					<input type="hidden" name="type" value="attachments" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="byAge" />
				</form>
				<form action="', $scripturl, '?action=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt[72], ': <input type="text" name="notice" value="', $txt['smf216'], '" size="40" /><br />
					', $txt['smf210'], ' <input type="text" name="size" id="size" value="100" size="4" /> ', $txt['smf211'], ' <input type="submit" name="submit" value="', $txt[31], '" />
					<input type="hidden" name="type" value="attachments" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="bySize" />
				</form>
				<form action="', $scripturl, '?action=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt['attachment_manager_avatars_older'], ' <input type="text" name="age" value="45" size="4" /> ', $txt[579], ' <input type="submit" name="submit" value="', $txt[31], '" />
					<input type="hidden" name="type" value="avatars" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="byAge" />
				</form>
			</td>
		</tr>
	</table>';
}

function template_attachment_repair()
{
	global $context, $txt, $scripturl;

	// If we've completed just let them know!
	if ($context['completed'])
	{
		echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments_complete'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%">
				', $txt['repair_attachments_complete_desc'], '
			</td>
		</tr>
	</table>';
	}
	// What about if no errors were even found?
	elseif (!$context['errors_found'])
	{
		echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments_complete'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%">
				', $txt['repair_attachments_no_errors'], '
			</td>
		</tr>
	</table>';
	}
	// Otherwise, I'm sad to say, we have a problem!
	else
	{
		echo '
	<form action="', $scripturl, '?action=manageattachments;sa=repair;fixErrors=1;step=0;substep=0;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments'], '</td>
		</tr><tr>
			<td class="windowbg2">
				', $txt['repair_attachments_error_desc'], '
			</td>
		</tr>';

		// Loop through each error reporting the status
		foreach ($context['repair_errors'] as $error => $number)
		{
			if (!empty($number))
			echo '
		<tr class="windowbg2">
			<td>
				<input type="checkbox" name="to_fix[]" id="', $error, '" value="', $error, '" />
				<label for="', $error, '">', sprintf($txt['attach_repair_' . $error], $number), '</label>
			</td>
		</tr>';
		}

		echo '
		<tr>
			<td align="center" class="windowbg2">
				<input type="submit" value="', $txt['repair_attachments_continue'], '" />
				<input type="submit" name="cancel" value="', $txt['repair_attachments_cancel'], '" />
			</td>
		</tr>
	</table>
	</form>';
	}
}

?>