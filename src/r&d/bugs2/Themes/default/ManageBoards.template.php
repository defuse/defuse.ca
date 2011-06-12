<?php
// Version: 1.1; ManageBoards

// Template for listing all the current categories and boards.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Table header.
	echo '
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="titlebg">
				<td width="100%">
					' . $txt['boardsEdit'] . '
				</td>
			</tr>';
	if (!empty($context['move_board']))
		echo '
			<tr class="windowbg2" height="30">
				<td>', $context['move_title'], ' [<a href="', $scripturl, '?action=manageboards">', $txt['mboards_cancel_moving'], '</a>]</td>
			</tr>';
			
	// Loop through every categories, listing the boards in each as we go.
	foreach ($context['categories'] as $category)
	{
		// Link to modify the category.
		echo '
			<tr>
				<td class="catbg" height="18">
					<a href="' . $scripturl . '?action=manageboards;sa=cat;cat=' . $category['id'] . '">', $category['name'], '</a> <a href="' . $scripturl . '?action=manageboards;sa=cat;cat=' . $category['id'] . '">', $txt['catModify'], '</a>
				</td>
			</tr>';

		// Boards table header.
		echo '
			<tr>
				<td class="windowbg2" width="100%" valign="top">
					<form action="', $scripturl, '?action=manageboards;sa=newboard;cat=', $category['id'], '" method="post" accept-charset="', $context['character_set'], '">
						<table width="100%" border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td style="padding-left: 1ex;" colspan="4"><b>', $txt['mboards_name'], '</b></td>
							</tr>';

		if (!empty($category['move_link']))
			echo '
							<tr class="windowbg2">
								<td colspan="4" style="padding-left: 5px;"><a href="', $category['move_link']['href'], '" title="', $category['move_link']['label'], '"><img src="', $settings['images_url'], '/smiley_select_spot.gif" alt="', $category['move_link']['label'], '" border="0" style="padding: 0px; margin: 0px;" /></a></td>
							</tr>';

		$alternate = false;

		// List through every board in the category, printing its name and link to modify the board.
		foreach ($category['boards'] as $board)
		{
			$alternate = !$alternate;

			echo '
							<tr class="windowbg', $alternate ? '' : '2', '">
								<td style="padding-left: ', 5 + 30 * $board['child_level'], 'px;', $board['move'] ? 'color: red;' : '', '">', $board['name'], !empty($modSettings['recycle_board']) && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] == $board['id'] ? '&nbsp;&nbsp;&nbsp;<a href="' . $scripturl . '?action=manageboards;sa=settings"><img src="' . $settings['images_url'] . '/post/recycled.gif" alt="' . $txt['recycle_board'] . '" border="0" /></a>' : '', '</td>
								<td width="10%" align="right">', !empty($modSettings['permission_enable_by_board']) && $context['can_manage_permissions'] ? '<a href="' . $scripturl . '?action=permissions;sa=switch;to=local;boardid=' . $board['id'] . ';sesc=' . $context['session_id'] . '"' . ($board['local_permissions'] ? '' : ' onclick="return confirm(\'' . $txt['mboards_permissions_confirm'] . '\');" style="font-style: italic;"') . '>' . $txt['mboards_permissions'] . '</a>' : '', '</td>
								<td width="10%" align="right"><a href="', $scripturl, '?action=manageboards;move=', $board['id'], '">', $txt['mboards_move'], '</a></td>
								<td width="10%" style="padding-right: 1ex;" align="right"><a href="', $scripturl, '?action=manageboards;sa=board;boardid=', $board['id'], '">', $txt['mboards_modify'], '</a></td>
							</tr>';
			if (!empty($board['move_links']))
			{
				$alternate = !$alternate;
				echo '
							<tr class="windowbg', $alternate ? '' : '2', '">
								<td style="padding-left: ', 5 + 30 * $board['move_links'][0]['child_level'], 'px;" colspan="4">';
				foreach ($board['move_links'] as $link)
					echo '<a href="', $link['href'], '" style="padding-right: 13px;padding-left: 0px;" title="', $link['label'], '"><img src="', $settings['images_url'], '/board_select_spot' , $link['child_level']>0 ? '_child' : '' , '.gif" alt="', $link['label'], '" border="0" style="padding: 0px; margin: 0px;" /></a>';
				echo '
								</td>
							</tr>';
			}
		}

		// Button to add a new board.
		echo '
							<tr>
								<td colspan="4" align="right"><br /><input type="submit" value="', $txt['mboards_new_board'], '" /></td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>';
	}
	echo '
		</table>';
}

// Template for editing/adding a category on the forum.
function template_modify_category()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Print table header.
	echo '
<form action="', $scripturl, '?action=manageboards;sa=cat2" method="post" accept-charset="', $context['character_set'], '">
	<input type="hidden" name="cat" value="', $context['category']['id'], '" />

	<table border="0" width="500" cellspacing="0" cellpadding="0" class="bordercolor" align="center">
		<tr><td>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr class="titlebg">
					<td>', isset($context['category']['is_new']) ? $txt['mboards_new_cat_name'] : $txt['catEdit'], '</td>
				</tr><tr>
					<td class="windowbg" valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>';
	// If this isn't the only category, let the user choose where this category should be positioned down the board index.
	if (count($context['category_order']) > 1)
	{
		echo '
								<td>
									<b>', $txt[43], '</b><br />
									<br /><br />
								</td>
								<td valign="top" align="right">
									<select name="cat_order">';
		// Print every existing category into a select box.
		foreach ($context['category_order'] as $order)
			echo '
										<option', $order['selected'] ? ' selected="selected"' : '', ' value="', $order['id'], '">', $order['name'], '</option>';
		echo '
									</select>
								</td>
							</tr><tr>';
	}
	// Allow the user to edit the category name and/or choose whether you can collapse the category.
	echo '
								<td>
									<b>', $txt[44], ':</b><br />
									', $txt[672], '<br /><br />
								</td>
								<td valign="top" align="right">
									<input type="text" name="cat_name" value="', $context['category']['editable_name'], '" size="30" tabindex="1" />
								</td>
							</tr><tr>
								<td>
									<b>' . $txt['collapse_enable'] . '</b><br />
									' . $txt['collapse_desc'] . '<br /><br />
								</td>
								<td valign="top" align="right">
									<input type="checkbox" name="collapse"', $context['category']['can_collapse'] ? ' checked="checked"' : '', ' tabindex="2" class="check" />
								</td>
							</tr>';

	// Table footer.
	echo '
							<tr>
								<td colspan="2" align="right">
									<br />';
	if (isset($context['category']['is_new']))
		echo '
									<input type="submit" name="add" value="', $txt['mboards_add_cat_button'], '" onclick="return !isEmptyText(this.form.cat_name);" tabindex="3" />';
	else
		echo '
									<input type="submit" name="edit" value="', $txt[17], '" onclick="return !isEmptyText(this.form.cat_name);" tabindex="3" />
									<input type="submit" name="delete" value="', $txt['mboards_delete_cat'], '" onclick="return confirm(\'', $txt['catConfirm'], '\');" />';
	echo '
								</td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />';
	// If this category is empty we don't bother with the next confirmation screen.
	if ($context['category']['is_empty'])
		echo '
						<input type="hidden" name="empty" value="1" />';
	echo '
					</td>
				</tr>
			</table>
		</td></tr>
	</table>
</form>';
}

// A template to confirm if a user wishes to delete a category - and whether they want to save the boards.
function template_confirm_category_delete()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Print table header.
	echo '
<form action="', $scripturl, '?action=manageboards;sa=cat2" method="post" accept-charset="', $context['character_set'], '">
	<input type="hidden" name="cat" value="', $context['category']['id'], '" />

	<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td>', $txt['mboards_delete_cat'], '</td>
		</tr><tr class="windowbg">
			<td class="windowbg" valign="top">
				', $txt['mboards_delete_cat_contains'], ':
				<ul>';

	foreach ($context['category']['children'] as $child)
		echo '
					<li>', $child, '</li>';

	echo '
				</ul>
			</td>
		</tr>
	</table>
	<br />
	<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td>', $txt['mboards_delete_what_do'], ':</td>
		</tr>
		<tr>
			<td class="windowbg2">
				<label for="delete_action0"><input type="radio" id="delete_action0" name="delete_action" value="0" class="check" checked="checked" />', $txt['mboards_delete_option1'], '</label><br />
				<label for="delete_action1"><input type="radio" id="delete_action1" name="delete_action" value="1" class="check"', count($context['category_order']) == 1 ? ' disabled="disabled"' : '', ' />', $txt['mboards_delete_option2'], '</label>:
				<select name="cat_to" ', count($context['category_order']) == 1 ? 'disabled="disabled"' : '', '>';

	foreach ($context['category_order'] as $cat)
		if ($cat['id'] != 0)
			echo '
					<option value="', $cat['id'], '">', $cat['true_name'], '</option>';

	echo '
				</select>
			</td>
		</tr>
		<tr>
			<td align="center" class="windowbg2">
				<input type="submit" name="delete" value="', $txt['mboards_delete_confirm'], '" />
				<input type="submit" name="cancel" value="', $txt['mboards_delete_cancel'], '" />
			</td>
		</tr>
	</table>

	<input type="hidden" name="confirmation" value="1" />
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>';
}

// Below is the template for adding/editing an board on the forum.
function template_modify_board()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// The main table header.
	echo '
<form action="', $scripturl, '?action=manageboards;sa=board2" method="post" accept-charset="', $context['character_set'], '">
	<input type="hidden" name="boardid" value="', $context['board']['id'], '" />

	<table border="0" width="540" cellspacing="0" cellpadding="0" class="bordercolor" align="center">
		<tr><td>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr class="titlebg">
					<td>', isset($context['board']['is_new']) ? $txt['mboards_new_board_name'] : $txt['boardsEdit'], '</td>
				</tr><tr>
					<td class="windowbg" valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">';

	// Option for choosing the category the board lives in.
	echo '
							<tr>
								<td>
									<b>', $txt['mboards_category'], '</b><br />
									<br /><br />
								</td>
								<td valign="top" align="right">
									<select name="new_cat" onchange="if (this.form.order) {this.form.order.disabled = this.options[this.selectedIndex].value != 0; this.form.boardOrder.disabled = this.options[this.selectedIndex].value != 0 || this.form.order.options[this.form.order.selectedIndex].value == \'\';}">';
		foreach ($context['categories'] as $category)
			echo '
										<option', $category['selected'] ? ' selected="selected"' : '', ' value="', $category['id'], '">', $category['name'], '</option>';
		echo '
									</select>
								</td>
							</tr><tr>';

	// If this isn't the only board in this category let the user choose where the board is to live.
	if ((isset($context['board']['is_new']) && count($context['board_order']) > 0) || count($context['board_order']) > 1)
	{
		echo '
								<td>
									<b>', $txt[43], '</b><br />
									<br /><br />
								</td>
								<td valign="top" align="right">';

	// The first select box gives the user the option to position it before, after or as a child of another board.
	echo '
									<select id="order" name="placement" onchange="this.form.boardOrder.disabled = this.options[this.selectedIndex].value == \'\';">
										', !isset($context['board']['is_new']) ? '<option value="">(' . $txt['mboards_unchanged'] . ')</option>' : '', '
										<option value="before">' . $txt['mboards_order_before'] . '...</option>
										<option value="child">' . $txt['mboards_order_child_of'] . '...</option>
										<option value="after">' . $txt['mboards_order_after'] . '...</option>
									</select>&nbsp;&nbsp;';

	// The second select box lists all the boards in the category.
	echo '
									<select id="boardOrder" name="board_order" ', isset($context['board']['is_new']) ? '' : 'disabled="disabled"', '>
										', !isset($context['board']['is_new']) ? '<option value="">(' . $txt['mboards_unchanged'] . ')</option>' : '';
		foreach ($context['board_order'] as $order)
			echo '
										<option', $order['selected'] ? ' selected="selected"' : '', ' value="', $order['id'], '">', $order['name'], '</option>';
		echo '
									</select>
								</td>
							</tr><tr>';
	}

	// Options for board name and description.
	echo '
								<td>
									<b>', $txt[44], ':</b><br />
									', $txt[672], '<br /><br />
								</td>
								<td valign="top" align="right">
									<input type="text" name="board_name" value="', $context['board']['name'], '" size="30" />
								</td>
							</tr><tr>
								<td>
									<b>', $txt['mboards_description'], '</b><br />
									', $txt['mboards_description_desc'], '<br /><br />
								</td>
								<td valign="top" align="right">
									<textarea name="desc" rows="2" cols="29">', $context['board']['description'], '</textarea>
								</td>
							</tr><tr>
								<td valign="top">
									<b>', $txt['mboards_groups'], '</b><br />
									', $txt['mboards_groups_desc'], '<br /><br />
								</td>
								<td valign="top" align="right">';

	// List all the membergroups so the user can choose who may access this board.
	foreach ($context['groups'] as $group)
		echo '
									<label for="groups_', $group['id'], '"><span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : '', '>', $group['name'], '</span> <input type="checkbox" name="groups[]" value="', $group['id'], '" id="groups_', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' /></label><br />';
	echo '
									<i>', $txt[737], '</i> <input type="checkbox" onclick="invertAll(this, this.form, \'groups[]\');" /><br />
									<br />
								</td>
							</tr>';

	if (empty($modSettings['permission_enable_by_board']))
	{
		echo '
							<tr>
								<td valign="top">
									<b>', $txt['mboards_permissions_title'], '</b><br />
									', $txt['mboards_permissions_desc'], '<br />
								</td>
								<td align="right">
									', $txt['permission_mode_normal'], ' <input type="radio" name="permission_mode" value="0" class="check"', $context['board']['permission_mode'] == 'normal' ? ' checked="checked"' : '', ' /><br />
									', $txt['permission_mode_no_polls'], ' <input type="radio" name="permission_mode" value="2" class="check"', $context['board']['permission_mode'] == 'no_polls' ? ' checked="checked"' : '', ' /><br />
									', $txt['permission_mode_reply_only'], ' <input type="radio" name="permission_mode" value="3" class="check"', $context['board']['permission_mode'] == 'reply_only' ? ' checked="checked"' : '', ' /><br />
									', $txt['permission_mode_read_only'], ' <input type="radio" name="permission_mode" value="4" class="check"', $context['board']['permission_mode'] == 'read_only' ? ' checked="checked"' : '', ' /><br />
									<br />
								</td>
							</tr>';
	}

	// Options to choose moderators, specifiy as announcement board and choose whether to count posts here.
	echo '
							<tr>
								<td>
									<b>', $txt['mboards_moderators'], '</b><br />
									', $txt['mboards_moderators_desc'], '<br /><br />
								</td>
								<td valign="top" align="right" style="white-space: nowrap;">
									<input type="text" name="moderators" id="moderators" value="', $context['board']['moderator_list'], '" size="30" />
									<a href="', $scripturl, '?action=findmember;input=moderators;quote;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a>
								</td>
							</tr><tr>
								<td>
									<b>', $txt['mboards_count_posts'], '</b><br />
									', $txt['mboards_count_posts_desc'], '<br /><br />
								</td>
								<td valign="top" align="right">
									<input type="checkbox" name="count" ', $context['board']['count_posts'] ? ' checked="checked"' : '', ' class="check" />
								</td>
							</tr>';

	// Here the user can choose to force this board to use a theme other than the default theme for the forum.
	echo '
							<tr>
								<td>
									<b>', $txt['mboards_theme'], '</b><br />
									', $txt['mboards_theme_desc'], '<br /><br />
								</td>
								<td valign="top" align="right">
									<select name="boardtheme">
										<option value="0"', $context['board']['theme'] == 0 ? ' selected="selected"' : '', '>', $txt['mboards_theme_default'], '</option>';

	foreach ($context['themes'] as $theme)
		echo '
										<option value="', $theme['id'], '"', $context['board']['theme'] == $theme['id'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';

	echo '
									</select>
								</td>
							</tr><tr>
								<td>
									<b>', $txt['mboards_override_theme'], '</b><br />
									', $txt['mboards_override_theme_desc'], '<br /><br />
								</td>
								<td valign="top" align="right">
									<input type="checkbox" name="override_theme"', $context['board']['override_theme'] ? ' checked="checked"' : '', ' class="check" />
								</td>
							</tr>';

	// Finish off the table.
	echo '
							<tr>
								<td colspan="2" align="right">
									<br />';
	if (isset($context['board']['is_new']))
		echo '
									<input type="hidden" name="cur_cat" value="', $context['board']['category'], '">
									<input type="submit" name="add" value="', $txt['mboards_new_board'], '" onclick="return !isEmptyText(this.form.board_name);" />';
	else
		echo '
									<input type="submit" name="edit" value="', $txt[17], '" onclick="return !isEmptyText(this.form.board_name);" />
									<input type="submit" name="delete" value="', $txt['mboards_delete_board'], '" onclick="return confirm(\'', $txt['boardConfirm'], '\');" />';
	echo '
								</td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />';

	// If this board has no children don't bother with the next confirmation screen.
	if ($context['board']['no_children'])
		echo '
						<input type="hidden" name="no_children" value="1" />';

	echo '
					</td>
				</tr>
			</table>
		</td></tr>
	</table>
</form>';
}

// A template used when a user is deleting a board with child boards in it - to see what they want to do with them.
function template_confirm_board_delete()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Print table header.
	echo '
<form action="', $scripturl, '?action=manageboards;sa=board2" method="post" accept-charset="', $context['character_set'], '">
	<input type="hidden" name="boardid" value="', $context['board']['id'], '" />

	<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td>', $txt['mboards_delete_board'], '</td>
		</tr><tr class="windowbg">
			<td class="windowbg" valign="top">
				', $txt['mboards_delete_board_contains'], ':
				<ul>';

	foreach ($context['children'] as $child)
		echo '
					<li>', $child['node']['name'], '</li>';

	echo '
				</ul>
			</td>
		</tr>
	</table>
	<br />
	<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td>', $txt['mboards_delete_what_do'], ':</td>
		</tr>
		<tr>
			<td class="windowbg2">
				<label for="delete_action0"><input type="radio" id="delete_action0" name="delete_action" value="0" class="check" checked="checked" />', $txt['mboards_delete_board_option1'], '</label><br />
				<label for="delete_action1"><input type="radio" id="delete_action1" name="delete_action" value="1" class="check"', empty($context['can_move_children']) ? ' disabled="disabled"' : '', ' />', $txt['mboards_delete_board_option2'], '</label>:
				<select name="board_to" ', empty($context['can_move_children']) ? 'disabled="disabled"' : '', '>';

	foreach ($context['board_order'] as $board)
		if ($board['id'] != $context['board']['id'] && empty($board['is_child']))
			echo '
					<option value="', $board['id'], '">', $board['name'], '</option>';

	echo '
				</select>
			</td>
		</tr>
		<tr>
			<td align="center" class="windowbg2">
				<input type="submit" name="delete" value="', $txt['mboards_delete_confirm'], '" />
				<input type="submit" name="cancel" value="', $txt['mboards_delete_cancel'], '" />
			</td>
		</tr>
	</table>

	<input type="hidden" name="confirmation" value="1" />
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>';
}

function template_modify_general_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=manageboards;sa=settings" method="post" accept-charset="', $context['character_set'], '"s>
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['settings'], '</td>
			</tr>';
	if ($context['can_change_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td width="50%" align="right" valign="top">', $txt['groups_manage_boards'], ':</td>
				<td width="50%">';
		theme_inline_permissions('manage_boards');
		echo '
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr /></td>
			</tr>';
	}
	echo '
			<tr class="windowbg2">
				<th width="50%" align="right"><label for="countChildPosts_check">', $txt['countChildPosts'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=countChildPosts" onclick="return reqWin(this.href);">?</a>)</span>:</th>
				<td>
					<input type="checkbox" name="countChildPosts" id="countChildPosts_check"', empty($modSettings['countChildPosts']) ? '' : ' checked="checked"', ' class="check" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="recycle_enable_check">', $txt['recycle_enable'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=recycle_enable" onclick="return reqWin(this.href);">?</a>)</span>:</th>
				<td>
					<input type="checkbox" name="recycle_enable" id="recycle_enable_check"', empty($modSettings['recycle_enable']) ? '' : ' checked="checked"', ' class="check" onclick="document.getElementById(\'recycle_board_select\').disabled = !this.checked;" />
				</td>
			</tr><tr class="windowbg2">
				<th align="right">', $txt['recycle_board'], ':</th>
				<td>
					<input type="hidden" name="recycle_board" value="', empty($modSettings['recycle_board']) ? '0' : $modSettings['recycle_board'], '" />
					<select name="recycle_board" id="recycle_board_select">
						<option></option>';
	foreach ($context['boards'] as $board)
		echo '
						<option value="', $board['id'], '"', $board['is_recycle'] ? ' selected="selected"' : '', '>', $board['category']['name'], ' - ', $board['name'], '</option>';
	echo '
					</select>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("recycle_board_select").disabled = !document.getElementById("recycle_enable_check").checked;
					// ]]></script>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_settings" value="', $txt['mboards_settings_submit'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

?>