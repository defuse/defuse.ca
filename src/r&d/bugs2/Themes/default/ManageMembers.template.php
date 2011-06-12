<?php
// Version: 1.1; ManageMembers

function template_view_members()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=viewmembers', $context['params_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="catbg">
				<td align="left" colspan="8">
					<b>', $txt[139], ':</b> ', $context['page_index'], '
				</td>
			</tr>
			<tr class="titlebg">';
	foreach ($context['columns'] as $column)
	{
		echo '
				<td valign="top">
					<a href="', $column['href'], '">';
		if ($column['selected'])
			echo $column['label'], ' <img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" />';
		else
			echo $column['label'];
		echo '</a>
				</td>';
	}
	if ($context['can_delete_members'])
		echo '
				<td align="center">
					<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />
				</td>';
	else
		echo '
				<td></td>';
	echo '
			</tr>';
	if (empty($context['members']))
		echo '
			<tr>
				<td class="windowbg" colspan="8">(', $txt['search_no_results'], ')</td>
			</tr>';
	else
	{
		foreach ($context['members'] as $member)
		{
				echo '
			<tr>
				<td class="windowbg" width="5%">
					', $member['id'], '
				</td>
				<td class="windowbg2">
					<a href="', $member['href'], '">', $member['username'], '</a>
				</td>
				<td class="windowbg2">
					<a href="', $member['href'], '">', $member['name'], '</a>
				</td>
				<td class="windowbg">
					<a href="mailto:', $member['email'], '">', $member['email'], '</a>
				</td>
				<td class="windowbg2">
					<a href="', $scripturl, '?action=trackip;searchip=', $member['ip'], '">', $member['ip'], '</a>
				</td>
				<td class="windowbg2">
					', $member['last_active'], '
				</td>
				<td class="windowbg2">
					', $member['posts'], '
				</td>';
		if ($context['can_delete_members'])
			echo '
				<td align="center" class="windowbg" width="5%">
					<input type="checkbox" name="delete[]" value="', $member['id'], '" class="check" />
				</td>';
		else
			echo '
				<td class="windowbg"></td>';
		echo '
			</tr>';
		}
		echo '
			<tr>
				<td class="windowbg2" align="right" colspan="8">', $context['can_delete_members'] ? '
					<input type="submit" name="delete_members" value="' . $txt[608] . '" onclick="return confirm(\'' . $txt['confirm_delete_members'] . '\');" />' : '', '
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sort" value="', $context['sort_by'], '" />
					<input type="hidden" name="start" value="', $context['start'], '" />', $context['sort_direction'] == 'up' ? '
					<input type="hidden" name="desc" value="1" />' : '', '
				</td>
			</tr>';
	}
	echo '
		</table>
	</form>';
}

function template_search_members()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=viewmembers" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="sa" value="query" /><div class="tborder">
		<table width="100%" cellpadding="4" cellspacing="0" class="windowbg">
			<tr class="titlebg">
				<td colspan="5">', $txt['search_for'], ':</td>
			</tr>
						<tr>
							<td colspan="5" align="right"><span class="smalltext">(', $txt['wild_cards_allowed'], ')</span></td>
						</tr><tr>
							<th align="right">', $txt['member_id'], ':</th>
							<td align="center">
								<select name="types[mem_id]">
									<option value="--">&lt;</option>
									<option value="-">&lt;=</option>
									<option value="=" selected="selected">=</option>
									<option value="+">&gt;=</option>
									<option value="++">&gt;</option>
								</select>
							</td>
							<td align="left"><input type="text" name="mem_id" value="" size="6" /></td>
							<th align="right">', $txt[35], ':</th>
							<td align="left"><input type="text" name="membername" value="" /> </td>
						</tr><tr>
							<th align="right">', $txt['age'], ':</th>
							<td align="center">
								<select name="types[age]">
									<option value="--">&lt;</option>
									<option value="-">&lt;=</option>
									<option value="=" selected="selected">=</option>
									<option value="+">&gt;=</option>
									<option value="++">&gt;</option>
								</select>
							</td>
							<td align="left"><input type="text" name="age" value="" size="6" /></td>
							<th align="right">', $txt['email_address'], ':</th>
							<td align="left"><input type="text" name="email" value="" /></td>
						</tr><tr>
							<th align="right">', $txt[26], ':</th>
							<td align="center">
								<select name="types[posts]">
									<option value="--">&lt;</option>
									<option value="-">&lt;=</option>
									<option value="=" selected="selected">=</option>
									<option value="+">&gt;=</option>
									<option value="++">&gt;</option>
								</select>
							</td>
							<td align="left"><input type="text" name="posts" value="" size="6" /></td>
							<th align="right">', $txt[96], ':</th>
							<td align="left"><input type="text" name="website" value="" /></td>
						</tr><tr>
							<th align="right">', $txt[233], ':</th>
							<td align="center">
								<select name="types[reg_date]">
									<option value="--">&lt;</option>
									<option value="-">&lt;=</option>
									<option value="=" selected="selected">=</option>
									<option value="+">&gt;=</option>
									<option value="++">&gt;</option>
								</select>
							</td>
							<td align="left"><input type="text" name="reg_date" value="" /> <span class="smalltext">', $txt['date_format'], '</span></td>
							<th align="right">', $txt[227], ':</th>
							<td align="left"><input type="text" name="location" value="" /></td>
						</tr><tr>
							<th align="right">', $txt['viewmembers_online'], ':</th>
							<td align="center">
								<select name="types[last_online]">
									<option value="--">&lt;</option>
									<option value="-">&lt;=</option>
									<option value="=" selected="selected">=</option>
									<option value="+">&gt;=</option>
									<option value="++">&gt;</option>
								</select>
							</td>
							<td align="left"><input type="text" name="last_online" value="" /> <span class="smalltext">', $txt['date_format'], '</span></td>
							<th align="right">', $txt['ip_address'], ':</th>
							<td align="left"><input type="text" name="ip" value="" /></td>
						</tr><tr>
							<th align="right">', $txt[231], ':</th>
							<td align="left" colspan="2">
								<label for="gender-0"><input type="checkbox" name="gender[]" value="0" id="gender-0" checked="checked" class="check" /> ', $txt['undefined_gender'], '</label>&nbsp;&nbsp;
								<label for="gender-1"><input type="checkbox" name="gender[]" value="1" id="gender-1" checked="checked" class="check" /> ', $txt[238], '</label>&nbsp;&nbsp;
								<label for="gender-2"><input type="checkbox" name="gender[]" value="2" id="gender-2" checked="checked" class="check" /> ', $txt[239], '</label>
							</td>
							<th align="right">', $txt['messenger_address'], ':</th>
							<td align="left"><input type="text" name="messenger" value="" /></td>
						</tr><tr>
							<th align="right">', $txt['activation_status'], ':</th>
							<td align="left" colspan="2">
								<label for="activated-0"><input type="checkbox" name="activated[]" value="1" id="activated-0" checked="checked" class="check" /> ', $txt['activated'], '</label>&nbsp;&nbsp;
								<label for="activated-1"><input type="checkbox" name="activated[]" value="0" id="activated-1" checked="checked" class="check" /> ', $txt['not_activated'], '</label>
							</td>
			</tr>
		</table></div>

		<table width="100%" cellpadding="0" cellspacing="0" class="tborder" style="margin-top: 2ex;">
			<tr class="catbg3">
				<td colspan="2" height="28"><b>', $txt['member_part_of_these_membergroups'], ':</b></td>
			</tr>
			<tr>
				<td class="windowbg" width="50%" valign="top">
					<table width="100%" cellpadding="3" cellspacing="1" border="0" >
						<tr class="titlebg">
							<th>', $txt['membergroups'], '</th>
							<th width="40">', $txt['primary'], '</th>
							<th width="40">', $txt['additional'], '</th>
						</tr>';

			foreach ($context['membergroups'] as $membergroup)
				echo '
						<tr class="windowbg2">
							<td>', $membergroup['name'], '</td>
							<td align="center">
								<input type="checkbox" name="membergroups[1][]" value="', $membergroup['id'], '" checked="checked" class="check" />
							</td>
							<td align="center">
								', $membergroup['can_be_additional'] ? '<input type="checkbox" name="membergroups[2][]" value="' . $membergroup['id'] . '" checked="checked" class="check" />' : '', '
							</td>
						</tr>';

			echo '
						<tr class="windowbg2">
							<td><em>', $txt[737], '</em></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'membergroups[1]\');" checked="checked" class="check" /></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'membergroups[2]\');" checked="checked" class="check" /></td>
						</tr>
					</table>
				</td>
				<td class="windowbg" valign="top">

					<table width="100%" cellpadding="3" cellspacing="1" border="0">
						<tr class="titlebg">
							<th colspan="2">', $txt['membergroups_postgroups'], '</th>
						</tr>';

			foreach ($context['postgroups'] as $postgroup)
				echo '
						<tr class="windowbg2">
							<td>', $postgroup['name'], '</td>
							<td width="40" align="center">
								<input type="checkbox" name="postgroups[]" value="', $postgroup['id'], '" checked="checked" class="check" />
							</td>
						</tr>';

			echo '
						<tr class="windowbg2">
							<td><em>', $txt[737], '</em></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'postgroups[]\');" checked="checked" class="check" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<div align="center" style="margin: 2ex;"><input type="submit" value="', $txt['182'], '" /></div>
	</form>';
}

function template_admin_browse()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=viewmembers" method="post" accept-charset="', $context['character_set'], '" name="postForm" id="postForm">
		<div class="tborder" style="padding: 1px;"><table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
			<tr class="catbg">
				<td colspan="6">
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						function onSelectChange()
						{
							if (document.forms.postForm.todo.value == "")
								return;

							var message = "";';
	// We have special messages for approving deletion of accounts - it's surprisingly logical - honest.
	if ($context['current_filter'] == 4)
		echo '
							if (document.forms.postForm.todo.value.indexOf("reject") != -1)
								message = "', $txt['admin_browse_w_delete'], '";
							else
								message = "', $txt['admin_browse_w_reject'], '";';
	// Otherwise a nice standard message.
	else
		echo '
							if (document.forms.postForm.todo.value.indexOf("delete") != -1)
								message = "', $txt['admin_browse_w_delete'], '";
							else if (document.forms.postForm.todo.value.indexOf("reject") != -1)
								message = "', $txt['admin_browse_w_reject'], '";
							else if (document.forms.postForm.todo.value == "remind")
								message = "', $txt['admin_browse_w_remind'], '";
							else
								message = "', $context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate'], '";';
	echo '
							if (confirm(message + " ', $txt['admin_browse_warn'], '"))
								document.forms.postForm.submit();
						}';

	// If there are lots of outstanding members - offer a quick and easy way to get rid of them.
	if ($context['num_members'] > 20)
	{
		echo '
						function onOutstandingSubmit()
						{
							if (document.forms.postFormOutstanding.todo.value == "")
								return;

							var message = "";
							if (document.forms.postFormOutstanding.todo.value.indexOf("delete") != -1)
								message = "', $txt['admin_browse_w_delete'], '";
							else if (document.forms.postFormOutstanding.todo.value.indexOf("reject") != -1)
								message = "', $txt['admin_browse_w_reject'], '";
							else if (document.forms.postFormOutstanding.todo.value == "remind")
								message = "', $txt['admin_browse_w_remind'], '";
							else
								message = "', $context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate'], '";

							if (confirm(message + " ', $txt['admin_browse_outstanding_warn'], '"))
								return true;
							else
								return false;
						}';
	}
	echo '
					// ]]></script>

				', $txt[139], ': ', $context['page_index'], '
				</td>
			</tr>
			<tr class="titlebg">';

	foreach ($context['columns'] as $column)
	{
		echo '
				<td valign="top">
					<a href="', $column['href'], '">';

		if ($column['selected'])
			echo $column['label'], ' <img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" />';
		else
			echo $column['label'];

		echo '</a>
				</td>';
	}

	echo '
				<td><input type="checkbox" class="check" onclick="invertAll(this, this.form, \'todo\');" /></td>
			</tr>';

	if (empty($context['members']))
		echo '
			<tr class="windowbg2">
				<td colspan="6" align="center">', $context['browse_type'] == 'approve' ? $txt['admin_browse_no_members_approval'] : $txt['admin_browse_no_members_activate'], '</td>
			</tr>';
	else
	{
		foreach ($context['members'] as $member)
			echo '
			<tr>
				<td class="windowbg2" width="5%">', $member['id'], '</td>
				<td class="windowbg">
					<a href="', $member['href'], '">', $member['username'], '</a>
				</td>
				<td class="windowbg"><a href="mailto:', $member['email'], '">', $member['email'], '</a></td>
				<td class="windowbg2"><a href="', $scripturl, '?action=trackip;searchip=', $member['ip'], '">', $member['ip'], '</a></td>
				<td class="windowbg">', $member['dateRegistered'], '</td>
				<td class="windowbg" width="5%">
					<input type="checkbox" value="', $member['id'], '" name="todoAction[]" class="check" />
				</td>
			</tr>';

		echo '
			<tr class="windowbg2">
				<td align="right" colspan="6">
					<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td align="left">';
		// Is there any need to show filters?
		if (!empty($context['available_filters']) && count($context['available_filters']) > 1)
		{
			echo '
								<b>', $txt['admin_browse_filter_by'], ':</b>
								<select name="filter" onchange="this.form.submit();">';
			foreach ($context['available_filters'] as $filter)
				echo '
									<option value="', $filter['type'], '"', $filter['selected'] ? ' selected="selected"' : '', '>', $filter['desc'], ' - ', $filter['amount'], ' ', $filter['amount'] == 1 ? $txt['user'] : $txt['users'], '</option>';
			echo '
								</select>
								<noscript><input type="submit" value="', $txt[161], '" name="filter" /></noscript>';
		}
		// What about if we only have one filter, but it's not the "standard" filter - show them what they are looking at.
		if (!empty($context['show_filter']) && !empty($context['available_filters']))
			echo '
								<span class="smalltext"><b>', $txt['admin_browse_filter_show'], ':</b> ', $context['available_filters'][0]['desc'], '</span>';

		echo '
							</td>
							<td align="right">
								<select name="todo" onchange="onSelectChange();">
									<option selected="selected" value="">', $txt['admin_browse_with_selected'], ':</option>
									<option value="" disabled="disabled">-----------------------------</option>';
		foreach ($context['allowed_actions'] as $key => $desc)
			echo '
									<option value="', $key, '">', $desc, '</option>';
	echo '
								</select>
								<noscript><input type="submit" value="', $txt[161], '" /></noscript>
								<input type="hidden" name="type" value="', $context['browse_type'], '" />
								<input type="hidden" name="sort" value="', $context['sort_by'], '" />
								<input type="hidden" name="start" value="', $context['start'], '" />
								<input type="hidden" name="orig_filter" value="', $context['current_filter'], '" />
								<input type="hidden" name="sa" value="approve" />', $context['sort_direction'] == 'up' ? '
								<input type="hidden" name="desc" value="1" />' : '', '
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		}

	echo '
			<tr class="catbg">
				<td colspan="6">', $txt[139], ': ', $context['page_index'], '</td>
			</tr>
		</table></div>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';

	// If we have lots of outstanding members try and make the admin's life easier.
	if ($context['num_members'] > 20)
	{
		echo '
	<form action="', $scripturl, '?action=viewmembers" method="post" accept-charset="', $context['character_set'], '" name="postFormOutstanding" id="postFormOutstanding" onsubmit="return onOutstandingSubmit();">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['admin_browse_outstanding'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="left" width="50%">
					', $txt['admin_browse_outstanding_days_1'], ':
				</td>
				<td align="left">
					<input type="text" name="time_passed" value="14" maxlength="4" size="3" /> ', $txt['admin_browse_outstanding_days_2'], '.
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="left" width="50%">
					', $txt['admin_browse_outstanding_perform'], ':
				</td>
				<td align="left">
					<select name="todo">
						', $context['browse_type'] == 'activate' ? '
						<option value="ok">' . $txt['admin_browse_w_activate'] . '</option>' : '', '
						<option value="okemail">', $context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate'], ' ', $txt['admin_browse_w_email'], '</option>', $context['browse_type'] == 'activate' ? '' : '
						<option value="require_activation">' . $txt['admin_browse_w_approve_require_activate'] . '</option>', '
						<option value="reject">', $txt['admin_browse_w_reject'], '</option>
						<option value="rejectemail">', $txt['admin_browse_w_reject'], ' ', $txt['admin_browse_w_email'], '</option>
						<option value="delete">', $txt['admin_browse_w_delete'], '</option>
						<option value="deleteemail">', $txt['admin_browse_w_delete'], ' ', $txt['admin_browse_w_email'], '</option>', $context['browse_type'] == 'activate' ? '
						<option value="remind">' . $txt['admin_browse_w_remind'] . '</option>' : '', '
					</select>
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="center" colspan="2">
					<input type="submit" value="', $txt['admin_browse_outstanding_go'], '" />
					<input type="hidden" name="type" value="', $context['browse_type'], '" />
					<input type="hidden" name="sort" value="', $context['sort_by'], '" />
					<input type="hidden" name="start" value="', $context['start'], '" />
					<input type="hidden" name="orig_filter" value="', $context['current_filter'], '" />
					<input type="hidden" name="sa" value="approve" />', $context['sort_direction'] == 'up' ? '
					<input type="hidden" name="desc" value="1" />' : '', '
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
	}
}

?>