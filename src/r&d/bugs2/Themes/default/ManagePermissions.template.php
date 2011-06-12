<?php
// Version: 1.1; ManagePermissions

function template_permission_index()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="' . $scripturl . '?action=permissions;sa=quick" method="post" accept-charset="', $context['character_set'], '" name="permissionForm" id="permissionForm">
			<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tborder">';
	if (!empty($context['board']))
		echo '
				<tr class="catbg">
					<td colspan="6" style="padding: 4px;">', $txt['permissions_boards'], ': <span style="color: red">', $context['board']['name'], '</span></td>
				</tr>';
	echo '
				<tr class="catbg3">
					<td valign="middle">', $txt['membergroups_name'], '</td>
					<td width="10%" align="center" valign="middle">', $txt['membergroups_members_top'], '</td>
					<td width="16%" align="center"', empty($modSettings['permission_enable_deny']) ? '' : ' class="smalltext"', '>
						', $txt['membergroups_permissions'], empty($modSettings['permission_enable_deny']) ? '' : '<br />
						<div style="float: left; width: 50%;">' . $txt['permissions_allowed'] . '</div> ' . $txt['permissions_denied'], '
					</td>';

	if (!empty($context['board']))
		echo '
					<td width="6%" align="center" valign="middle">', $txt['permissions_access'], '</td>';

	echo '
					<td width="10%" align="center" valign="middle">', $txt['permissions_modify'], '</td>
					<td width="4%" align="center" valign="middle">
						<input type="checkbox" class="check" onclick="invertAll(this, this.form, \'group\');" /></td>
				</tr>';

	foreach ($context['groups'] as $group)
	{
		echo '
				<tr>
					<td class="windowbg2">', $group['name'], $group['id'] == -1 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_guests" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 0 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_regular_members" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 1 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_administrator" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 3 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_moderator" onclick="return reqWin(this.href);">?</a>)' : ''))), '</td>
					<td class="windowbg" align="center">', $group['can_search'] ? $group['link'] : $group['num_members'], '</td>
					<td class="windowbg2" align="center"', $group['id'] == 1 ? ' style="font-style: italic;"' : '', '>';
		if (empty($modSettings['permission_enable_deny']))
			echo '
						', $group['num_permissions']['allowed'];
		else
			echo '
						<div style="float: left; width: 50%;">', $group['num_permissions']['allowed'], '</div> ', empty($group['num_permissions']['denied']) || $group['id'] == 1 ? $group['num_permissions']['denied'] : ($group['id'] == -1 ? '<span style="font-style: italic;">' . $group['num_permissions']['denied'] . '</span>' : '<span style="color: red;">' . $group['num_permissions']['denied'] . '</span>');
		echo '
					</td>';

	if (!empty($context['board']))
	{
		echo '
					<td class="windowbg" align="center">';

		// Don't show the checkbox for admins and moderators, doesn't make sense!
		if ($group['id'] != 1 && $group['id'] != 3)
			echo '
						<input type="checkbox" name="access[', $group['id'], ']" value="', $group['id'], '" ', $group['access'] ? ' checked="checked"' : '', ' class="check" />';

		echo '
					</td>';
	}

		echo '
					<td class="windowbg2" align="center">', $group['allow_modify'] ? '<a href="' . $scripturl . '?action=permissions;sa=modify;group=' . $group['id'] . (empty($context['board']) ? '' : ';boardid=' . $context['board']['id']) . '">' . $txt['permissions_modify'] . '</a>' : '', '</td>
					<td class="windowbg" align="center">', $group['allow_modify'] ? '<input type="checkbox" name="group[]" value="' . $group['id'] . '" class="check" />' : '', '</td>
				</tr>';
	}

	echo '
				<tr class="windowbg">
					<td colspan="6" style="padding-top: 1ex; padding-bottom: 1ex; text-align: right;">
						<table width="100%" cellspacing="0" cellpadding="3" border="0"><tr><td>
							<div style="margin-bottom: 1ex;"><b>', $txt['permissions_with_selection'], '...</b></div>
							', $txt['permissions_apply_pre_defined'], ' <a href="' . $scripturl . '?action=helpadmin;help=permissions_quickgroups" onclick="return reqWin(this.href);">(?)</a>:
							<select name="predefined">
								<option value="">(' . $txt['permissions_select_pre_defined'] . ')</option>
								<option value="restrict">' . $txt['permitgroups_restrict'] . '</option>
								<option value="standard">' . $txt['permitgroups_standard'] . '</option>
								<option value="moderator">' . $txt['permitgroups_moderator'] . '</option>
								<option value="maintenance">' . $txt['permitgroups_maintenance'] . '</option>
							</select><br /><br />';

	if (!empty($context['board']) && !empty($context['copy_boards']))
	{
		echo '
							', $txt['permissions_copy_from_board'], ':
							<select name="from_board">
								<option value="empty">(', $txt['permissions_select_board'], ')</option>';
		foreach ($context['copy_boards'] as $board)
			echo '
								<option value="', $board['id'], '">', $board['name'], '</option>';
		echo '
							</select><br /><br />';
	}

	echo '
							', $txt['permissions_like_group'], ':
							<select name="copy_from">
								<option value="empty">(', $txt['permissions_select_membergroup'], ')</option>';
	foreach ($context['groups'] as $group)
	{
		if ($group['id'] != 1)
			echo '
								<option value="', $group['id'], '">', $group['name'], '</option>';
	}

	echo '
							</select><br /><br />
							<select name="add_remove">
								<option value="add">', $txt['permissions_add'], '...</option>
								<option value="clear">', $txt['permissions_remove'], '...</option>';
	if (!empty($modSettings['permission_enable_deny']))
		echo '
								<option value="deny">', $txt['permissions_deny'], '...</option>';
	echo '
							</select>&nbsp;<select name="permissions">
								<option value="">(', $txt['permissions_select_permission'], ')</option>';
	foreach ($context['permissions'] as $permissionType)
	{
		if ($permissionType['id'] == 'membergroup' && !empty($context['board']))
			continue;

		foreach ($permissionType['columns'] as $column)
		{
			foreach ($column as $permissionGroup)
			{
				echo '
								<option value="" disabled="disabled">[', $permissionGroup['name'], ']</option>';
				foreach ($permissionGroup['permissions'] as $perm)
					if ($perm['has_own_any'])
						echo '
								<option value="', $permissionType['id'], '/', $perm['own']['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], ' (', $perm['own']['name'], ')</option>
								<option value="', $permissionType['id'], '/', $perm['any']['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], ' (', $perm['any']['name'], ')</option>';
					else
						echo '
								<option value="', $permissionType['id'], '/', $perm['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], '</option>';
			}
		}
	}
	echo '
							</select>
						</td><td valign="bottom" width="16%">
							<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
								function checkSubmit()
								{
									if ((document.forms.permissionForm.predefined.value != "" && (document.forms.permissionForm.copy_from.value != "empty" || document.forms.permissionForm.permissions.value != "")) || (document.forms.permissionForm.copy_from.value != "empty" && document.forms.permissionForm.permissions.value != ""))
									{
										alert("', $txt['permissions_only_one_option'], '");
										return false;
									}
									if (document.forms.permissionForm.predefined.value == "" && document.forms.permissionForm.copy_from.value == "" && document.forms.permissionForm.permissions.value == "")
									{
										alert("', $txt['permissions_no_action'], '");
										return false;
									}
									if (document.forms.permissionForm.permissions.value != "" && document.forms.permissionForm.add_remove.value == "deny")
										return confirm("', $txt['permissions_deny_dangerous'], '");

									return true;
								}
							// ]]></script>
							<input type="submit" value="', $txt['permissions_set_permissions'], '" onclick="return checkSubmit();" />
						</td></tr></table>
					</td>
				</tr>
			</table>';

	if (!empty($context['board']))
		echo '
			<input type="hidden" name="boardid" value="', $context['board']['id'], '" />';

	echo '
			<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
		</form>';
}

function template_by_board()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tborder" style="margin-top: 2ex;">
				<tr class="catbg3">
					<td>', $txt[20], '</td>
					<td colspan="4" style="width: 18ex; text-align: center;">', $txt['permissions_switch'], '</td>
				</tr>';
	foreach ($context['boards'] as $board)
	{
			echo '
				<tr class="windowbg2">
					<td align="left" class="windowbg">
						<b><a', $board['use_local_permissions'] ? ' href="' . $scripturl . '?action=permissions;boardid=' . $board['id'] . '"' : '', ' name="', $board['id'], '"> ', str_repeat('-', $board['child_level']), ' ' . $board['name'] . '</a></b>', empty($modSettings['permission_enable_by_board']) ? '' : ' (' . ($board['use_local_permissions'] ? $txt['permissions_local'] : $txt['permissions_global']) . ')', '
					</td>';
		if (empty($modSettings['permission_enable_by_board']))
			echo '
					<td align="center" style="font-weight: ', $board['permission_mode'] == 'normal' ? 'bold' : 'normal', ';"><a href="', $scripturl, '?action=permissions;sa=board;mode=0;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permission_mode_normal'], '</a></td>
					<td align="center" style="font-weight: ', $board['permission_mode'] == 'no_polls' ? 'bold' : 'normal', ';"><a href="', $scripturl, '?action=permissions;sa=board;mode=2;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permission_mode_no_polls'], '</a></td>
					<td align="center" style="font-weight: ', $board['permission_mode'] == 'reply_only' ? 'bold' : 'normal', ';"><a href="', $scripturl, '?action=permissions;sa=board;mode=3;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permission_mode_reply_only'], '</a></td>
					<td align="center" style="font-weight: ', $board['permission_mode'] == 'read_only' ? 'bold' : 'normal', ';"><a href="', $scripturl, '?action=permissions;sa=board;mode=4;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permission_mode_read_only'], '</a></td>';
		else
			echo '
					<td colspan="2" align="center" style="font-weight: ', $board['use_local_permissions'] ? 'normal' : 'bold', ';"><a href="', $scripturl, '?action=permissions;sa=switch;to=global;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permissions_global'], '</a></td>
					<td colspan="2" align="center" style="font-weight: ', $board['use_local_permissions'] ? 'bold' : 'normal', ';"><a href="', $scripturl, '?action=permissions;sa=switch;to=local;boardid=', $board['id'], ';sesc=', $context['session_id'], '#', $board['id'], '">', $txt['permissions_local'], '</a></td>';
		echo '
				</tr>';
	}

	echo '
			</table>';
}

function template_modify_group()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.smf_usedDeny = false;

			function warnAboutDeny()
			{
				if (window.smf_usedDeny)
					return confirm("', $txt['permissions_deny_dangerous'], '");
				else
					return true;
			}
		// ]]></script>
		<form action="', $scripturl, '?action=permissions;sa=modify2;group=', $context['group']['id'], ';boardid=', $context['board']['id'], '" method="post" accept-charset="', $context['character_set'], '" name="permissionForm" id="permissionForm" onsubmit="return warnAboutDeny();">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="tborder">';
	if (!empty($modSettings['permission_enable_deny']) && $context['group']['id'] != -1)
		echo '
				<tr class="windowbg">
					<td colspan="2" class="smalltext" style="padding: 2ex;">', $txt['permissions_option_desc'], '</td>
				</tr>';
	foreach ($context['permissions'] as $permission_type)
	{
		if ($permission_type['show'])
		{
			echo '
				<tr class="catbg">
					<td colspan="2" align="center">';
			if ($context['local'])
				echo '
						', $txt['permissions_local_for'], ' \'<span style="color: red;">', $context['group']['name'], '</span>\' ', $txt['permissions_on'], ' \'<span style="color: red;">', $context['board']['name'], '</span>\'';
			else
				echo '
						', $permission_type['id'] == 'membergroup' ? $txt['permissions_general'] : $txt['permissions_board'], ' - <span style="color: red;">', $context['group']['name'], '</span>';
			echo '
					</td>
				</tr>
				<tr class="windowbg2">';
			foreach ($permission_type['columns'] as $column)
			{
				echo '
					<td valign="top" width="50%">
						<table width="100%" cellpadding="1" cellspacing="0" border="0">';
				foreach ($column as $permissionGroup)
				{
					echo '
							<tr class="windowbg2">
								<td colspan="2" width="100%" align="left"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;"><b>', $permissionGroup['name'], '</b></div></td>';
					if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
						echo '
								<td colspan="3" width="10"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">&nbsp;</div></td>';
					else
						echo '
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">', $txt['permissions_option_on'], '</div></td>
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">', $txt['permissions_option_off'], '</div></td>
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px; color: red;">', $txt['permissions_option_deny'], '</div></td>';
					echo '
							</tr>';

					if (!empty($permissionGroup['permissions']))
					{
						$alternate = false;
						foreach ($permissionGroup['permissions'] as $permission)
						{
							echo '
							<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td valign="top" width="10" style="padding-right: 1ex;">
									', $permission['show_help'] ? '<a href="' . $scripturl . '?action=helpadmin;help=permissionhelp_' . $permission['id'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt[119] . '" /></a>' : '', '
								</td>';
							if ($permission['has_own_any'])
							{
								echo '
								<td colspan="4" width="100%" valign="top" align="left">', $permission['name'], '</td>
							</tr><tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td></td>
								<td width="100%" class="smalltext" align="right">', $permission['own']['name'], ':</td>';

								if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
									echo '
								<td colspan="3"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" id="', $permission['own']['id'], '_on" class="check" /></td>';
								else
									echo '
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" id="', $permission['own']['id'], '_on" class="check" /></td>
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'denied' ? ' checked="checked"' : '', ' value="deny" class="check" /></td>';

								echo '
							</tr><tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td></td>
								<td width="100%" class="smalltext" align="right" style="padding-bottom: 1.5ex;">', $permission['any']['name'], ':</td>';

								if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
									echo '
								<td colspan="3" style="padding-bottom: 1.5ex;"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>';
								else
									echo '
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" onclick="document.forms.permissionForm.', $permission['own']['id'], '_on.checked = true;" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select']== 'denied' ? ' checked="checked"' : '', ' value="deny" id="', $permission['any']['id'], '_deny" onclick="window.smf_usedDeny = true;" class="check" /></td>';

								echo '
							</tr>';
							}
							else
							{
								echo '
								<td valign="top" width="100%" align="left" style="padding-bottom: 2px;">', $permission['name'], '</td>';

								if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
									echo '
								<td valign="top" style="padding-bottom: 2px;"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>';
								else
									echo '
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'denied' ? ' checked="checked"' : '', ' value="deny" onclick="window.smf_usedDeny = true;" class="check" /></td>';

								echo '
							</tr>';
							}

							$alternate = !$alternate;
						}
					}

					echo '
							<tr class="windowbg2">
								<td colspan="5" width="100%"><div style="border-top: 1px solid; padding-bottom: 1.5ex; margin-top: 2px;">&nbsp;</div></td>
							</tr>';
				}

				echo '
						</table>
					</td>';
			}
		}
	}
	echo '
				</tr><tr class="windowbg2">
					<td colspan="2" align="right"><input type="submit" value="', $txt['permissions_commit'], '" />&nbsp;</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_general_permission_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=permissions;sa=settings" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['permission_settings_title'], '</td>
			</tr>';
	if ($context['can_change_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td width="50%" align="right" valign="top">', $txt['groups_manage_permissions'], ':</td>
				<td width="50%">';
		theme_inline_permissions('manage_permissions');
		echo '
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr /></td>
			</tr>
';
	}
	echo '
			<tr class="windowbg2">
				<td width="50%" align="right"><label for="permission_enable_deny_check">', $txt['permission_settings_enable_deny'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=permissions_deny" onclick="return reqWin(this.href);">?</a>):</td>
				<td>
					<input type="checkbox" name="permission_enable_deny" id="permission_enable_deny_check"', empty($modSettings['permission_enable_deny']) ? '' : ' checked="checked"', ' class="check"', empty($modSettings['permission_enable_deny']) ? '' : ' onclick="if (!this.checked) alert(\'' . $txt['permission_disable_deny_warning'] . '\');"', '/>
				</td>
			</tr><tr class="windowbg2">
				<td width="50%" align="right"><label for="permission_enable_postgroups_check">', $txt['permission_settings_enable_postgroups'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=permissions_postgroups" onclick="return reqWin(this.href);">?</a>):</td>
				<td>
					<input type="checkbox" name="permission_enable_postgroups" id="permission_enable_postgroups_check"', empty($modSettings['permission_enable_postgroups']) ? '' : ' checked="checked"', ' class="check"', empty($modSettings['permission_enable_postgroups']) ? '' : ' onclick="if (!this.checked) alert(\'' . $txt['permission_disable_postgroups_warning'] . '\');"', '/>
				</td>
			</tr><tr class="windowbg2">
				<td width="50%" align="right"><label for="permission_enable_by_board_check">', $txt['permission_settings_enable_by_board'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=permissions_by_board" onclick="return reqWin(this.href);">?</a>):</td>
				<td>
					<input type="checkbox" name="permission_enable_by_board" id="permission_enable_by_board_check"', empty($modSettings['permission_enable_by_board']) ? '' : ' checked="checked"', ' class="check"', empty($modSettings['permission_enable_by_board']) ? '' : ' onclick="if (!this.checked) alert(\'' . $txt['permission_disable_by_board_warning'] . '\');"', '/>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_settings" value="', $txt['permission_settings_submit'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_inline_permissions()
{
	global $context, $settings, $options, $txt, $modSettings;

	echo '
		<fieldset id="', $context['current_permission'], '_groups">
			<legend><a href="javascript:void(0);" onclick="document.getElementById(\'', $context['current_permission'], '_groups\').style.display = \'none\';document.getElementById(\'', $context['current_permission'], '_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>';
	if (empty($modSettings['permission_enable_deny']))
		echo '
			<table width="100%" border="0">';
	else
		echo '
			<div class="smalltext" style="padding: 2em;">', $txt['permissions_option_desc'], '</div>
			<table width="100%" border="0">
				<tr>
					<th align="center">', $txt['permissions_option_on'], '</th>
					<th align="center">', $txt['permissions_option_off'], '</th>
					<th align="center" style="color: red;">', $txt['permissions_option_deny'], '</th>
					<td></td>
				</tr>';
	foreach ($context['member_groups'] as $group)
	{
		echo '
				<tr>';
		if (empty($modSettings['permission_enable_deny']))
			echo '
					<td align="center"><input type="checkbox" name="', $context['current_permission'], '[', $group['id'], ']" value="on"', $group['status'] == 'on' ? ' checked="checked"' : '', ' class="check" /></td>';
		else
			echo '
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="on"', $group['status'] == 'on' ? ' checked="checked"' : '', ' class="check" /></td>
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="off"', $group['status'] == 'off' ? ' checked="checked"' : '', ' class="check" /></td>
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="deny"', $group['status'] == 'deny' ? ' checked="checked"' : '', ' class="check" /></td>';
		echo '
					<td', $group['is_postgroup'] ? ' style="font-style: italic;"' : '', '>', $group['name'], '</td>
				</tr>';
	}
	echo '
			</table>
		</fieldset>

		<a href="javascript:void(0);" onclick="document.getElementById(\'', $context['current_permission'], '_groups\').style.display = \'block\'; document.getElementById(\'', $context['current_permission'], '_groups_link\').style.display = \'none\'; return false;" id="', $context['current_permission'], '_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById("', $context['current_permission'], '_groups").style.display = "none";
			document.getElementById("', $context['current_permission'], '_groups_link").style.display = "";
		// ]]></script>';
}

?>