<?php
// Version: 1.1; ManageBans

function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=ban;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['ban_remove_selected_confirm'], '\');">
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="catbg3">
				<td colspan="8"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr><tr class="titlebg">';
	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
				</th>';
		elseif ($column['sortable'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['link'], '
				</th>';
		else
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['label'], '
				</th>';
	}
	echo '
				<th><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
			</tr>';

	while ($ban = $context['get_ban']())
	{
		echo '
			<tr>
				<td align="left" valign="top" class="windowbg">', $ban['name'], '</td>
				<td align="left" valign="top" class="windowbg2">', $ban['notes'], '</td>
				<td align="left" valign="top" class="windowbg2">', $ban['reason'], '</td>
				<td align="left" valign="top" class="windowbg2">', $ban['added'], '</td>
				<td align="left" valign="top" class="windowbg">', $ban['expires'], '</td>
				<td align="center" valign="top" class="windowbg">', $ban['num_entries'], '</td>
				<td align="center" valign="top" class="windowbg2">
					&nbsp;<a href="', $scripturl, '?action=ban;sa=edit;sort=', $context['sort_by'], $context['sort_direction'] == 'up' ? ';desc' : '',';bg=', $ban['id'], '">', $txt[17], '</a>
				</td>
				<td align="center" valign="top" class="windowbg2"><input type="checkbox" name="remove[]" value="', $ban['id'], '" class="check" /></td>
			</tr>';
	}
	echo '
			<tr class="catbg3">
				<td colspan="8" align="left">
					<div style="float: left;">
						<b>', $txt[139], ':</b> ', $context['page_index'], '
					</div>
					<div style="float: right;">
						<input type="submit" name="removeBans" value="', $txt['ban_remove_selected'], '" />
					</div>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_ban_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>', $context['ban']['is_new'] ? $txt['ban_add_new'] : $txt['ban_edit'] . ' \'' . $context['ban']['name'] . '\'', '</td>
		</tr><tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '" onsubmit="if (this.ban_name.value == \'\') {alert(\'', $txt['ban_name_empty'], '\'); return false;} if (this.partial_ban.checked &amp;&amp; !(this.cannot_post.checked || this.cannot_register.checked || this.cannot_login.checked)) {alert(\'', $txt['ban_restriction_empty'], '\'); return false;}">
					<table cellpadding="4">
						<tr>
							<th align="right">', $txt['ban_name'], ':</th>
							<td align="left"><input type="text" name="ban_name" value="', $context['ban']['name'], '" size="50" /></td>
						</tr><tr>
							<th align="right" valign="top">', $txt['ban_expiration'], ':</th>
							<td align="left"><input type="radio" name="expiration" value="never" id="never_expires" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'never' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="never_expires">', $txt['never'], '</label><br />
							<input type="radio" name="expiration" value="one_day" id="expires_one_day" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'still_active_but_we_re_counting_the_days' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="expires_one_day">', $txt['ban_will_expire_within'], '</label>: <input type="text" name="expire_date" id="expire_date" size="3" value="', $context['ban']['expiration']['days'], '" /> ', $txt['ban_days'], '<br />
							<input type="radio" name="expiration" value="expired" id="already_expired" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'expired' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="already_expired">', $txt['ban_expired'], '</label>
							</td>
						</tr><tr>
							<th align="right" valign="bottom">', $txt['ban_reason'], ':</th>
							<td align="left">
								<div class="smalltext">', $txt['ban_reason_desc'], '</div>
								<input type="text" name="reason" value="', $context['ban']['reason'], '" size="50" />
							</td>
						</tr><tr>
							<th align="right" valign="middle">', $txt['ban_notes'], ':</th>
							<td align="left">
								<div class="smalltext">', $txt['ban_notes_desc'], '</div>
								<textarea name="notes" cols="50" rows="3">', $context['ban']['notes'], '</textarea>
							</td>
						</tr><tr>
							<th align="right" valign="top">', $txt['ban_restriction'], ':</th>
							<td align="left">
								<input type="radio" name="full_ban" id="full_ban" value="1" onclick="updateStatus();"', $context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="full_ban">', $txt['ban_full_ban'], '</label><br />
								<input type="radio" name="full_ban" id="partial_ban" value="0" onclick="updateStatus();"', !$context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="partial_ban">', $txt['ban_partial_ban'], '</label><br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_post" id="cannot_post" value="1"', $context['ban']['cannot']['post'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_post">', $txt['ban_cannot_post'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=ban_cannot_post" onclick="return reqWin(this.href);">?</a>)<br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_register" id="cannot_register" value="1"', $context['ban']['cannot']['register'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_register">', $txt['ban_cannot_register'], '</label><br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_login" id="cannot_login" value="1"', $context['ban']['cannot']['login'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_login">', $txt['ban_cannot_login'], '</label><br />
							</td>
						</tr>';
	if (!empty($context['ban_suggestions']))
	{
		echo '
						<tr>
							<th align="right" valign="top">', $txt['ban_triggers'], ':</th>
							<td>
								<table cellpadding="4">
									<tr>
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="main_ip_check" value="main_ip" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_ip'], ':<br />
											<input type="text" name="main_ip" value="', $context['ban_suggestions']['main_ip'], '" size="50" onfocus="document.getElementById(\'main_ip_check\').checked = true;" />
										</td>
									</tr><tr>';
		if (empty($modSettings['disableHostnameLookup']))
			echo '
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="hostname_check" value="hostname" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_hostname'], ':<br />
											<input type="text" name="hostname" value="', $context['ban_suggestions']['hostname'], '" size="50" onfocus="document.getElementById(\'hostname_check\').checked = true;" />
										</td>
									</tr><tr>';
		echo '
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="email_check" value="email" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_email'], ':<br />
											<input type="text" name="email" value="', $context['ban_suggestions']['email'], '" size="50" onfocus="document.getElementById(\'email_check\').checked = true;" />
										</td>
									</tr><tr>
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="user_check" value="user" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_username'], ':<br />';
		if (empty($context['ban_suggestions']['member']['id']))
			echo '
											<input type="text" name="user" id="user" value="" size="40" onfocus="document.getElementById(\'user_check\').checked = true;" />&nbsp;<a href="', $scripturl, '?action=findmember;input=user;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a>';
		else
			echo '
											', $context['ban_suggestions']['member']['link'], '
											<input type="hidden" name="bannedUser" value="', $context['ban_suggestions']['member']['id'], '" />';
		echo '
										</td>
									</tr>';
		if (!empty($context['ban_suggestions']['message_ips']))
		{
			echo '
									<tr>
										<th align="left" colspan="2"><br />', $txt['ips_in_messages'], ':</th>
									</tr>';
			foreach ($context['ban_suggestions']['message_ips'] as $ip)
				echo '
									<tr>
										<td><input type="checkbox" name="ban_suggestion[ips][]" value="', $ip, '" class="check" /></td>
										<td align="left">', $ip, '</td>
									</tr>';
		}
		if (!empty($context['ban_suggestions']['error_ips']))
		{
			echo '
									<tr>
										<th align="left" colspan="2"><br />', $txt['ips_in_errors'], ':</th>
									</tr>';
			foreach ($context['ban_suggestions']['error_ips'] as $ip)
				echo '
									<tr>
										<td><input type="checkbox" name="ban_suggestion[ips][]" value="', $ip, '" class="check" /></td>
										<td align="left">', $ip, '</td>
									</tr>';
		}
		echo '
								</table>
							</td>
						</tr>';
	}
	echo '
						<tr>
							<td colspan="2" align="right"><input type="submit" name="', $context['ban']['is_new'] ? 'add_ban' : 'modify_ban', '" value="', $context['ban']['is_new'] ? $txt['ban_add'] : $txt['ban_modify'], '" /></td>
						</tr>
					</table>', $context['ban']['is_new'] ? '<br />
					' . $txt['ban_add_notes'] : '', '
					<input type="hidden" name="old_expire" value="', $context['ban']['expiration']['days'], '" />
					<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';
	if (!$context['ban']['is_new'] && empty($context['ban_suggestions']))
	{
		echo '
		<tr>
			<td align="center" style="padding: 0px;">
				<form action="', $scripturl, '?action=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '" style="padding: 0px;margin: 0px;" onsubmit="return confirm(\'', $txt['ban_remove_selected_triggers_confirm'], '\');">
					<table cellpadding="4" cellspacing="1" width="100%"><tr class="titlebg">
						<td width="65%" align="left">', $txt['ban_banned_entity'], '</td>
						<td width="15%" align="center">', $txt['ban_hits'], '</td>
						<td width="15%" align="center">', $txt['ban_actions'], '</td>
						<td width="5%" align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'ban_items\');" class="check" /></td>
					</tr>';
		if (empty($context['ban_items']))
			echo '
					<tr class="windowbg2"><td colspan="4">(', $txt['ban_no_triggers'], ')</td></tr>';
		else
		{
			foreach ($context['ban_items'] as $ban_item)
			{
				echo '
						<tr class="windowbg2" align="left">
							<td>';
				if ($ban_item['type'] == 'ip')
					echo '<b>', $txt[512], ':</b>&nbsp;', $ban_item['ip'];
				elseif ($ban_item['type'] == 'hostname')
					echo '<b>', $txt['hostname'], ':</b>&nbsp;', $ban_item['hostname'];
				elseif ($ban_item['type'] == 'email')
					echo '<b>', $txt[69], ':</b>&nbsp;', $ban_item['email'];
				elseif ($ban_item['type'] == 'user')
					echo '<b>', $txt[35], ':</b>&nbsp;', $ban_item['user']['link'];
				echo '
						</td>
						<td class="windowbg" align="center">', $ban_item['hits'], '</td>
						<td class="windowbg" align="center"><a href="', $scripturl, '?action=ban;sa=edittrigger;bg=', $context['ban']['id'], ';bi=', $ban_item['id'], '">', $txt['ban_edit_trigger'], '</a></td>
						<td align="center" class="windowbg2"><input type="checkbox" name="ban_items[]" value="', $ban_item['id'], '" class="check" /></td>
					</tr>';
			}
		}
		echo '
					<tr class="catbg3">
						<td colspan="4" align="right">
							<div style="float: left;">
								[<a href="', $scripturl, '?action=ban;sa=edittrigger;bg=', $context['ban']['id'], '"><b>', $txt['ban_add_trigger'], '</b></a>]
							</div>
							<input type="submit" name="remove_selection" value="', $txt['ban_remove_selected_triggers'], '" />
							</div>
						</td>
					</tr>
					</table>
					<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';

	}
	echo '
	</table>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function updateStatus()
		{
			document.getElementById("expire_date").disabled = !document.getElementById("expires_one_day").checked;
			document.getElementById("cannot_post").disabled = document.getElementById("full_ban").checked;
			document.getElementById("cannot_register").disabled = document.getElementById("full_ban").checked;
			document.getElementById("cannot_login").disabled = document.getElementById("full_ban").checked;
		}
		window.onload = updateStatus;
	// ]]></script>';
}

function template_ban_edit_trigger()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" align="center" cellspacing="0" cellpadding="4" class="tborder" width="60%">
			<tr class="titlebg">
				<td>', $context['ban_trigger']['is_new'] ? $txt['ban_add_trigger'] : $txt['ban_edit_trigger_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<table cellpadding="4">
						<tr>
							<td valign="bottom"><input type="radio" name="bantype" value="ip_ban"', $context['ban_trigger']['ip']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_ip'], ':<br />
								<input type="text" name="ip" value="', $context['ban_trigger']['ip']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'ip_ban\');" />
							</td><td>
							</td>
						</tr><tr>';
				if (empty($modSettings['disableHostnameLookup']))
				echo '
							<td valign="bottom"><input type="radio" name="bantype" value="hostname_ban"', $context['ban_trigger']['hostname']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_hostname'], ':<br />
								<input type="text" name="hostname" value="', $context['ban_trigger']['hostname']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'hostname_ban\');" />
							</td><td>
							</td>
						</tr><tr>';
				echo '
							<td valign="bottom"><input type="radio" name="bantype" value="email_ban"', $context['ban_trigger']['email']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_email'], ':<br />
								<input type="text" name="email" value="', $context['ban_trigger']['email']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'email_ban\');" />
							</td><td>
							</td>
						</tr><tr>
							<td valign="bottom"><input type="radio" name="bantype" value="user_ban"', $context['ban_trigger']['banneduser']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_username'], ':<br />
								<input type="text" name="user" id="user" value="', $context['ban_trigger']['banneduser']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'user_ban\');" />
							</td><td valign="bottom">
								<a href="', $scripturl, '?action=findmember;input=user;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a>
							</td>
						</tr><tr>
							<td colspan="3" align="right"><br />
								<input type="submit" name="', $context['ban_trigger']['is_new'] ? 'add_new_trigger' : 'edit_trigger', '" value="', $context['ban_trigger']['is_new'] ? $txt['ban_add_trigger_submit'] : $txt['ban_edit_trigger_submit'], '" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<input type="hidden" name="bi" value="' . $context['ban_trigger']['id'] . '" />
		<input type="hidden" name="bg" value="' . $context['ban_trigger']['group'] . '" />
		<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
	</form>';
}

function template_browse_triggers()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
<form action="', $scripturl, '?action=ban;sa=browse;entity=', $context['selected_entity'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['ban_remove_selected_triggers_confirm'], '\');">
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="100%">
		<tr class="titlebg">
			<td colspan="4">', $txt['ban_trigger_browse'], '</td>
		</tr><tr class="catbg3">
			<td colspan="4">
				<a href="', $scripturl, '?action=ban;sa=browse;entity=ip">', $context['selected_entity'] == 'ip' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '', $txt[512], '</a>&nbsp;|&nbsp;<a href="', $scripturl, '?action=ban;sa=browse;entity=hostname">', $context['selected_entity'] == 'hostname' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '', $txt['hostname'], '</a>&nbsp;|&nbsp;<a href="', $scripturl, '?action=ban;sa=browse;entity=email">', $context['selected_entity'] == 'email' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '', $txt[69], '</a>&nbsp;|&nbsp;<a href="', $scripturl, '?action=ban;sa=browse;entity=member">', $context['selected_entity'] == 'member' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '', $txt[35], '</a>
			</td>
		</tr><tr class="titlebg">
			<th align="left">', $txt['ban_banned_entity'], '</th>
			<th align="left">', $txt['ban_name'], '</th>
			<th>', $txt['ban_hits'], '</th>
			<th align="center"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></th>
		</tr>';
	if (empty($context['ban_items']))
		echo '
				<tr class="windowbg2"><td colspan="4">(', $txt['ban_no_triggers'], ')</td></tr>';
	else
	{
		foreach ($context['ban_items'] as $ban_item)
		{
			echo '
		<tr class="windowbg2">
			<td>', $ban_item['entity'], '</td>
			<td>', $ban_item['group']['link'], '</td>
			<td align="center" class="windowbg">', $ban_item['hits'], '</td>
			<td align="center"><input type="checkbox" name="remove[]" value="', $ban_item['id'], '" class="check" /></td>
		</tr>';
		}
	}
	echo '
		<tr class="windowbg">
			<td align="right" colspan="4">
				<input type="submit" name="remove_triggers" value="', $txt['ban_remove_selected_triggers'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="start" value="', $context['start'], '" />
			</td>
		</tr>
		<tr class="catbg3">
			<td align="left" colspan="5" style="padding: 5px;"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>
</form>';
}

function template_ban_log()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=ban;sa=log" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="catbg3">
				<td colspan="7"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr><tr class="titlebg">
				<th>
					<a href="', $scripturl, '?action=ban;sa=log;sort=ip', $context['sort_direction'] == 'up' ? ';desc' : '', ';start=', $context['start'], '">' . $txt['ban_log_ip'], $context['sort'] == 'ip' ? '&nbsp;<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
				</th>
				<th>
					<a href="', $scripturl, '?action=ban;sa=log;sort=email', $context['sort_direction'] == 'up' ? ';desc' : '', ';start=', $context['start'], '">' . $txt['ban_log_email'], $context['sort'] == 'email' ? '&nbsp;<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
				</th>
				<th>
					<a href="', $scripturl, '?action=ban;sa=log;sort=name', $context['sort_direction'] == 'up' ? ';desc' : '', ';start=', $context['start'], '">' . $txt['ban_log_member'], $context['sort'] == 'name' ? '&nbsp;<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
				</th>
				<th>
					<a href="', $scripturl, '?action=ban;sa=log;sort=date', $context['sort_direction'] == 'up' ? ';desc' : '', ';start=', $context['start'], '">' . $txt['ban_log_date'], $context['sort'] == 'date' ? '&nbsp;<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
				</th>
				<th><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
			</tr>';
	if (empty($context['log_entries']))
		echo '
			<tr class="windowbg2">
				<td colspan="5">(', $txt['ban_log_no_entries'], ')</td>
			</tr>';
	else
	{
		foreach ($context['log_entries'] as $log)
			echo '
			<tr>
				<td class="windowbg"><a href="', $scripturl, '?action=trackip;searchip=', $log['ip'], '">', $log['ip'], '</a></td>
				<td class="windowbg2">', $log['email'], '</td>
				<td class="windowbg">', empty($log['member']['id']) ? '<i>' . $txt[470] . '</i>' : $log['member']['link'], '</td>
				<td class="windowbg2">', $log['date'], '</td>
				<td class="windowbg" align="center"><input type="checkbox" name="remove[]" value="', $log['id'], '" class="check" /></td>
			</tr>';
		echo '
			<tr class="windowbg2">
				<td colspan="5" align="right">
					<input type="submit" name="removeAll" value="', $txt['ban_log_remove_all'], '" onclick="return confirm(\'', $txt['ban_log_remove_all_confirm'], '\');" />
					<input type="submit" name="removeSelected" value="', $txt['ban_log_remove_selected'], '" onclick="return confirm(\'', $txt['ban_log_remove_selected_confirm'], '\');" />
				</td>
			</tr>';
	}
	echo '
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

?>