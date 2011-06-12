<?php
// Version: 1.1; ManageMembergroups

function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<div class="tborder">
			<form action="' . $scripturl . '?action=membergroups;sa=add;generalgroup" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
				<table width="100%" cellpadding="2" cellspacing="1" border="0">
					<tr class="titlebg"><td colspan="4" style="padding: 4px;">', $txt['membergroups_regular'], '</td></tr>
					<tr class="catbg3">
						<td width="42%">', $txt['membergroups_name'], '</td>
						<td width="12%" align="center">', $txt['membergroups_stars'], '</td>
						<td width="10%" align="center">', $txt['membergroups_members_top'], '</td>
						<td width="10%" align="center">', $txt[17], '</td>
					</tr>';
	foreach ($context['groups']['regular'] as $group)
	{
		echo '
					<tr>
						<td class="windowbg2">', empty($group['color']) ? ( $group['can_search'] ? $group['link'] : $group['name'] ) : '<span style="color: ' . $group['color'] . '">' . ( $group['can_search'] ? $group['link'] : $group['name']) . '</span>', $group['id'] == 1 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_administrator" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 3 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_moderator" onclick="return reqWin(this.href);">?</a>)' : ''), '</td>
						<td class="windowbg2" align="left">', $group['stars'], '</td>
						<td class="windowbg" align="center">', $group['num_members'], '</td>
						<td class="windowbg2" align="center"><a href="' . $scripturl . '?action=membergroups;sa=edit;group=' . $group['id'] . '">' . $txt['membergroups_modify'] . '</a></td>
					</tr>';
	}

	echo '
					<tr class="windowbg">
						<td colspan="4" align="right" style="padding-top: 1ex; padding-bottom: 2ex;">
							<input type="submit" value="', $txt['membergroups_add_group'], '" style="margin: 4px;" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
				<input type="hidden" name="postgroup" value="0" />
				<input type="hidden" name="generalgroup" value="1" />
			</form>
		</div><br />
		<div class="tborder">
			<form action="' . $scripturl . '?action=membergroups;sa=add" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
				<table width="100%" border="0" cellpadding="2" cellspacing="1">
					<tr class="titlebg"><td colspan="5" style="padding: 4px;">', $txt['membergroups_post'], '</td></tr>
					<tr class="catbg3">
						<td width="42%">', $txt['membergroups_name'], '</td>
						<td width="12%" align="center">', $txt['membergroups_stars'], '</td>
						<td width="10%" align="center">', $txt['membergroups_members_top'], '</td>
						<td width="12%" align="center">', $txt['membergroups_min_posts'], '</td>
						<td width="10%" align="center">', $txt[17], '</td>
					</tr>';
	foreach ($context['groups']['post'] as $group)
	{
		echo '
					<tr>
						<td class="windowbg2">', empty($group['color']) ? ($group['can_search'] ? $group['link'] : $group['name']) : '<span style="color: ' . $group['color'] . '">' . ($group['can_search'] ? $group['link'] : $group['name']) . '</span>', '</td>
						<td class="windowbg2" align="left">', $group['stars'], '</td>
						<td class="windowbg" align="center">', $group['num_members'], '</td>
						<td class="windowbg" align="center">', $group['min_posts'], '</td>
						<td class="windowbg2" align="center"><a href="' . $scripturl . '?action=membergroups;sa=edit;group=' . $group['id'] . '">' . $txt['membergroups_modify'] . '</a></td>
					</tr>';
	}

	echo '
					<tr class="windowbg">
						<td colspan="5" align="right" style="padding-top: 1ex; padding-bottom: 2ex;">
							<input type="submit" value="', $txt['membergroups_add_group'], '" style="margin: 4px;" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
				<input type="hidden" name="postgroup" value="1" />
			</form>
		</div>';
}

function template_new_group()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=membergroups;sa=add" method="post" accept-charset="', $context['character_set'], '">
			<table width="90%" cellpadding="4" cellspacing="0" border="0" class="tborder" align="center">
				<tr class="titlebg">
					<td colspan="2" align="center">', $txt['membergroups_new_group'], '</td>
				</tr><tr class="windowbg2">
					<th align="right" width="50%"><label for="group_name_input">', $txt['membergroups_group_name'], ':</label></th>
					<td><input type="text" name="group_name" id="group_name_input" size="30" /></td>
				</tr>';
	if ($context['undefined_group'])
		echo '
				<tr class="windowbg2">
					<th align="right"><label for="postgroup_based_check">', $txt['membergroups_edit_post_group'], ':</label></th>
					<td>
						<input type="hidden" name="postgroup_based" value="0" />
						<input type="checkbox" name="postgroup_based" id="postgroup_based_check" value="1" onclick="updateStatus();" class="check" />
					</td>
				</tr>';
	if ($context['post_group'] || $context['undefined_group'])
		echo '
				<tr class="windowbg2">
					<th align="right">', $txt['membergroups_min_posts'], ':</th>
					<td>
						<input type="text" name="min_posts" id="min_posts_input" size="5" />
					</td>
				</tr>';
	if (!$context['post_group'] || !empty($modSettings['permission_enable_postgroups']))
	{
		echo '
				<tr class="windowbg2">
					<th align="right" valign="top" style="padding-top: 1em;">
						<label for="permission_base">', $txt['membergroups_permissions'], ':</label>
						<div class="smalltext" style="font-weight: normal;">', $txt['membergroups_can_edit_later'], '</div>
					</th>
					<td>
						<fieldset id="permission_base">
							<legend>', $txt['membergroups_select_permission_type'], '</legend>
							<input type="radio" name="perm_type" id="perm_type_predefined" value="predefined" checked="checked" class="check" />
							<label for="perm_type_predefined">', $txt['membergroups_new_as_type'], ':</label>
							<select name="level" id="level_select" onclick="document.getElementById(\'perm_type_predefined\').checked = true;">
								<option value="restrict">', $txt['permitgroups_restrict'], '</option>
								<option value="standard" selected="selected">', $txt['permitgroups_standard'], '</option>
								<option value="moderator">', $txt['permitgroups_moderator'], '</option>
								<option value="maintenance">', $txt['permitgroups_maintenance'], '</option>
							</select><br />

							<input type="radio" name="perm_type" id="perm_type_copy" value="copy" class="check" />
							<label for="perm_type_copy">', $txt['membergroups_new_as_copy'], ':</label>
							<select name="copyperm" id="copyperm_select" onclick="document.getElementById(\'perm_type_copy\').checked = true;">
								<option value="-1">', $txt['membergroups_guests'], '</option>
								<option value="0">', $txt['membergroups_members'], '</option>';
		foreach ($context['groups'] as $group)
			echo '
								<option value="', $group['id'], '">', $group['name'], '</option>';
		echo '
							</select>
						</fieldset>
					</td>
				</tr>';
	}
	echo '
				<tr class="windowbg2">
					<th align="right" valign="top" style="padding-top: 1em;">
						', $txt['membergroups_new_board'], ':', $context['post_group'] ? '<div class="smalltext" style="font-weight: normal">' . $txt['membergroups_new_board_post_groups'] . '</div>' : '', '
					</th>
					<td>
						<fieldset id="visible_boards">
							<legend>', $txt['membergroups_new_board_desc'], '</legend>';
	foreach ($context['boards'] as $board)
		echo '
							<div style="margin-left: ', $board['child_level'], 'em;"><input type="checkbox" name="boardaccess[]" id="boardaccess_', $board['id'], '" value="', $board['id'], '" ', $board['selected'] ? ' checked="checked" disabled="disabled"' : '', ' class="check" /> <label for="boardaccess_', $board['id'], '">', $board['name'], '</label></div>';

	echo '<br />
							<input type="checkbox" id="checkall_check" class="check" onclick="invertAll(this, this.form, \'boardaccess\');" /> <label for="checkall_check"><i>', $txt[737], '</i></label>
						</fieldset>
					</td>
				</tr><tr class="windowbg2">
					<td colspan="2" align="right"><br /><input type="submit" value="', $txt['membergroups_add_group'], '" /></td>
				</tr>
			</table>';
	if ($context['undefined_group'])
	{
		echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				function updateStatus()
				{
					var postgroupBased = document.getElementById(\'postgroup_based_check\').checked;
					document.getElementById(\'min_posts_input\').disabled = !postgroupBased;';
		if (empty($modSettings['permission_enable_postgroups']))
			echo '
					document.getElementById(\'perm_type_predefined\').disabled = postgroupBased;
					document.getElementById(\'perm_type_copy\').disabled = postgroupBased;
					document.getElementById(\'level_select\').disabled = postgroupBased;
					document.getElementById(\'copyperm_select\').disabled = postgroupBased;';
		echo '
				}
				updateStatus();
			// ]]></script>';
	}
	echo '
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_edit_group()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=membergroups;sa=edit;group=', $context['group']['id'], '" method="post" accept-charset="', $context['character_set'], '" name="groupForm" id="groupForm">
			<table width="95%" border="0" cellspacing="0" cellpadding="3" class="tborder" align="center">
				<tr class="titlebg">
					<td colspan="2" align="center">', $txt['membergroups_edit_group'], ' - ', $context['group']['name'], '</td>
				</tr>
				<tr class="windowbg2">
					<th align="right" width="50%"><label for="group_name_input">', $txt['membergroups_edit_name'], ':</label></th>
					<td><input type="text" name="group_name" id="group_name_input" value="', $context['group']['editable_name'], '" size="30" /></td>
				</tr>';
	if ($context['group']['allow_post_group'])
		echo '
				<tr class="windowbg2">
					<th align="right"><label for="post_group_check">', $txt['membergroups_edit_post_group'], ':</label></th>
					<td><input type="checkbox" name="post_group" id="post_group_check" value="1"', $context['group']['is_post_group'] ? ' checked="checked"' : '', ' onclick="swapPostGroup(this.checked);" class="check" /></td>
				</tr>
				<tr class="windowbg2">
					<th align="right" id="min_posts_text"><label for="min_posts_input">', $txt['membergroups_min_posts'], ':</label></th>
					<td><input type="text" name="min_posts" id="min_posts_input"', $context['group']['is_post_group'] ? ' value="' . $context['group']['min_posts'] . '"' : '', ' size="6" /></td>
				</tr>';
	echo '
				<tr class="windowbg2">
					<th align="right"><label for="online_color_input">', $txt['membergroups_online_color'], ':</label></th>
					<td><input type="text" name="online_color" id="online_color_input" value="', $context['group']['color'], '" size="20" /></td>
				</tr>
				<tr class="windowbg2">
					<th align="right"><label for="star_count_input">', $txt['membergroups_star_count'], ':</label></th>
					<td style="padding-bottom: 0;"><input type="text" name="star_count" id="star_count_input" value="', $context['group']['star_count'], '" size="4" onkeyup="if (this.value.length > 2) this.value = 99;" onkeydown="this.onkeyup();" onchange="if (this.value != 0) this.form.star_image.onchange();" /></td>
				</tr>
				<tr class="windowbg2">
					<th align="right" style="padding-top: 1em;">
						<label for="star_image_input">', $txt['membergroups_star_image'], ':</label>
						<div class="smalltext" style="font-weight: normal;">', $txt['membergroups_star_image_note'], '</div>
					</th>
					<td>
						', $txt['membergroups_images_url'], '
						<input type="text" name="star_image" id="star_image_input" value="', $context['group']['star_image'], '" onchange="if (this.value &amp;&amp; this.form.star_count.value == 0) this.form.star_count.value = 1; else if (!this.value) this.form.star_count.value = 0; document.getElementById(\'star_preview\').src = smf_images_url + \'/\' + (this.value &amp;&amp; this.form.star_count.value > 0 ? this.value.replace(/\$language/g, \'', $context['user']['language'], '\') : \'blank.gif\');" size="20" />
						<img id="star_preview" src="', $settings['images_url'], '/', $context['group']['star_image'] == '' ? 'blank.gif' : $context['group']['star_image'], '" alt="*" />
					</td>
				</tr>
				<tr class="windowbg2">
					<th align="right" style="padding-top: 1em;">
						<label for="max_messages_input">', $txt['membergroups_max_messages'], ':</label>
						<div class="smalltext" style="font-weight: normal">', $txt['membergroups_max_messages_note'], '</div>
					</th>
					<td>
						<input type="text" name="max_messages" id="max_messages_input" value="', $context['group']['id'] == 1 ? 0 : $context['group']['max_messages'], '" size="6" ', $context['group']['id'] == 1 ? 'disabled="disabled"' : '', '/>
					</td>
				</tr>';
	if (!empty($context['boards']))
	{
		echo '
				<tr class="windowbg2">
					<th align="right" valign="top">
						', $txt['membergroups_new_board'], ':', $context['group']['is_post_group'] ? '<div class="smalltext" style="font-weight: normal">' . $txt['membergroups_new_board_post_groups'] . '</div>' : '', '
					</th>
					<td valign="top">
						<fieldset id="visible_boards">
							<legend><a href="javascript:void(0);" onclick="document.getElementById(\'visible_boards\').style.display = \'none\';document.getElementById(\'visible_boards_link\').style.display = \'block\'; return false;">', $txt['membergroups_new_board_desc'], '</a></legend>';
		foreach ($context['boards'] as $board)
			echo '
							<div style="margin-left: ', $board['child_level'], 'em;"><input type="checkbox" name="boardaccess[]" id="boardaccess_', $board['id'], '" value="', $board['id'], '" ', $board['selected'] ? ' checked="checked"' : '', ' class="check" /> <label for="boardaccess_', $board['id'], '">', $board['name'], '</label></div>';

		echo '<br />
							<input type="checkbox" id="checkall_check" class="check" onclick="invertAll(this, this.form, \'boardaccess\');" /> <label for="checkall_check"><i>', $txt[737], '</i></label>
						</fieldset>
						<a href="javascript:void(0);" onclick="document.getElementById(\'visible_boards\').style.display = \'block\'; document.getElementById(\'visible_boards_link\').style.display = \'none\'; return false;" id="visible_boards_link" style="display: none;">[ ', $txt['membergroups_select_visible_boards'], ' ]</a>
						<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
							document.getElementById("visible_boards_link").style.display = "";
							document.getElementById("visible_boards").style.display = "none";
						// ]]></script>
					</td>
				</tr>';
	}
	echo '
				<tr class="windowbg2">
					<td colspan="2" align="right" style="padding-top: 1ex;">
						<input type="submit" name="submit" value="', $txt['membergroups_edit_save'], '" />', $context['group']['allow_delete'] ? '
						<input type="submit" name="delete" value="' . $txt['membergroups_delete'] . '" onclick="return confirm(\'' . $txt['membergroups_confirm_delete'] . '\');" />' : '', '
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';

	if ($context['group']['allow_post_group'])
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function swapPostGroup(isChecked)
			{
				var min_posts_text = document.getElementById(\'min_posts_text\');
				document.forms.groupForm.min_posts.disabled = !isChecked;
				min_posts_text.style.color = isChecked ? "" : "#888888";
			}
			swapPostGroup(', $context['group']['is_post_group'] ? 'true' : 'false', ');
		// ]]></script>';
}

function template_group_members()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=membergroups;sa=members;group=', $context['group']['id'], '" method="post" accept-charset="', $context['character_set'], '">
			<table width="90%" cellpadding="4" cellspacing="1" border="0" class="bordercolor" align="center">
				<tr class="titlebg">
					<td colspan="6" align="left">', $context['page_title'], '</td>
				</tr>
				<tr class="catbg">
					<td colspan="6" align="left">', $txt[139], ': ', $context['page_index'], '</td>
				</tr>
				<tr class="titlebg">
					<td><a href="', $scripturl, '?action=membergroups;sa=members;start=', $context['start'], ';sort=name', $context['sort_by'] == 'name' && $context['sort_direction'] == 'up' ? ';desc' : '', ';group=', $context['group']['id'], '">', $txt[68], $context['sort_by'] == 'name' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
					<td><a href="', $scripturl, '?action=membergroups;sa=members;start=', $context['start'], ';sort=email', $context['sort_by'] == 'email' && $context['sort_direction'] == 'up' ? ';desc' : '', ';group=', $context['group']['id'], '">', $txt[69], $context['sort_by'] == 'email' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
					<td><a href="', $scripturl, '?action=membergroups;sa=members;start=', $context['start'], ';sort=active', $context['sort_by'] == 'active' && $context['sort_direction'] == 'up' ? ';desc' : '', ';group=', $context['group']['id'], '">', $txt['attachment_manager_last_active'], $context['sort_by'] == 'active' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
					<td><a href="', $scripturl, '?action=membergroups;sa=members;start=', $context['start'], ';sort=registered', $context['sort_by'] == 'registered' && $context['sort_direction'] == 'up' ? ';desc' : '', ';group=', $context['group']['id'], '">', $txt[233], $context['sort_by'] == 'registered' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
					<td', empty($context['group']['assignable']) ? ' colspan="2"' : '', '><a href="', $scripturl, '?action=membergroups;sa=members;start=', $context['start'], ';sort=posts', $context['sort_by'] == 'posts' && $context['sort_direction'] == 'up' ? ';desc' : '', ';group=', $context['group']['id'], '">', $txt[21], $context['sort_by'] == 'posts' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>';
	if (!empty($context['group']['assignable']))
		echo '
					<td width="4%" align="center"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></td>';
	echo '
				</tr>';

	if (empty($context['members']))
		echo '
				<tr class="windowbg2">
					<td colspan="6" align="center">', $txt['membergroups_members_no_members'], '</td>
				</tr>';

	foreach ($context['members'] as $member)
	{
		echo '
				<tr class="windowbg2">
					<td>', $member['name'], '</td>
					<td>', $member['email'], '</td>
					<td class="windowbg">', $member['last_online'], '</td>
					<td class="windowbg">', $member['registered'], '</td>
					<td', empty($context['group']['assignable']) ? ' colspan="2"' : '', '>', $member['posts'], '</td>';
		if (!empty($context['group']['assignable']))
			echo '
					<td align="center" width="4%"><input type="checkbox" name="rem[]" value="', $member['id'], '" class="check" /></td>';
		echo '
				</tr>';
	}

	if (!empty($context['group']['assignable']))
		echo '
				<tr class="titlebg">
					<td colspan="6" align="right">
						<input type="submit" name="remove" value="', $txt['membergroups_members_remove'], '!" style="font-weight: normal;" />
					</td>
				</tr>';
	echo '
			</table><br />';

	if (!empty($context['group']['assignable']))
	{
		echo '
			<table width="90%" cellpadding="4" cellspacing="0" border="0" class="tborder" align="center">
				<tr class="titlebg">
					<td align="left" colspan="2">', $txt['membergroups_members_add_title'], '</td>
				</tr><tr class="windowbg2">
					<td align="right" width="50%"><b>', $txt['membergroups_members_add_desc'], ':</b></td>
					<td align="left">
						<input type="text" name="toAdd" id="toAdd" size="30" />
						<a href="', $scripturl, '?action=findmember;input=toAdd;quote;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a>
					</td>
				</tr><tr class="windowbg2">
					<td colspan="2" align="center">
						<input type="submit" name="add" value="', $txt['membergroups_members_add'], '" />
					</td>
				</tr>
			</table>';
	}

	echo '
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_membergroup_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=membergroups;sa=settings" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['membergroups_settings'], '</td>
			</tr>';
	if ($context['can_change_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td width="50%" align="right" valign="top">', $txt['groups_manage_membergroups'], ':</td>
				<td width="50%">';
		theme_inline_permissions('manage_membergroups');
		echo '
				</td>
			</tr>';
	}
	echo '
			<tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_settings" value="', $txt['membergroups_settings_submit'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

?>