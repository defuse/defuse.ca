<?php
// Version: 1.1; Memberlist

// Displays a sortable listing of all members registered on the forum.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Show the link tree.
	echo '
	<div style="padding: 3px;">', theme_linktree(), '</div>';

	// shall we use the tabs?
	if (!empty($settings['use_tabs']))
	{
		// Display links to view all/search.
		echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
		<tr>
			<td class="mirrortab_first">&nbsp;</td>';

		foreach ($context['sort_links'] as $link)
		{
			if ($link['selected'])
				echo '
				<td class="mirrortab_active_first">&nbsp;</td>
				<td valign="top" class="mirrortab_active_back">
					<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">', $link['label'], '</a>
				</td>
				<td class="mirrortab_active_last">&nbsp;</td>';
			else
				echo '
				<td valign="top" class="mirrortab_back">
					<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">', $link['label'], '</a>
				</td>';
		}

		echo '
			<td class="mirrortab_last">&nbsp;</td>
		</tr>
	</table>';
	}

	echo '
	<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">';

	// Old style tabs?
	if (empty($settings['use_tabs']))
	{
		echo '
		<tr class="titlebg">
			<td colspan="12">';
				$links = array();
				foreach ($context['sort_links'] as $link)
					$links[] = ($link['selected'] ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '') . '<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">' . $link['label'] . '</a>';

				echo '
					', implode(' | ', $links), '
			</td>
		</tr>';
	}
	echo '
		<tr>
			<td colspan="12" class="', empty($settings['use_tabs']) ? 'catbg' : 'titlebg', '">';

		// Display page numbers and the a-z links for sorting by name if not a result of a search.
		if (!isset($context['old_search']))
			echo '
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td>', $txt[139], ': ', $context['page_index'], '</td>
						<td align="right">', $context['letter_links'] . '</td>
					</tr>
				</table>';
		// If this is a result of a search then just show the page numbers.
		else
			echo '
				', $txt[139], ': ', $context['page_index'];

		echo '
			</td>
		</tr>
		<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
			echo '
			<td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
				<a href="' . $column['href'] . '">' . $column['label'] . ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" /></a></td>';
		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '
		</tr>';

	// Assuming there are members loop through each one displaying their data.
	if (!empty($context['members']))
	{
		foreach ($context['members'] as $member)
			echo '
		<tr style="text-align: center;"', empty($member['sort_letter']) ? '' : ' id="letter' . $member['sort_letter'] . '"', '>
			<td class="windowbg2">
				', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $member['online']['text'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $member['online']['image_href'] . '" alt="' . $member['online']['text'] . '" align="middle" />' : $member['online']['label'], $context['can_send_pm'] ? '</a>' : '', '
			</td>
			<td class="windowbg" align="left">', $member['link'], '</td>
			<td class="windowbg2">', $member['hide_email'] ? '' : '<a href="mailto:' . $member['email'] . '"><img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . ' ' . $member['name'] . '" /></a>', '</td>
			<td class="windowbg">', $member['website']['url'] != '' ? '<a href="' . $member['website']['url'] . '" target="_blank"><img src="' . $settings['images_url'] . '/www.gif" alt="' . $member['website']['title'] . '" title="' . $member['website']['title'] . '" /></a>' : '', '</td>
			<td class="windowbg2">', $member['icq']['link'], '</td>
			<td class="windowbg2">', $member['aim']['link'], '</td>
			<td class="windowbg2">', $member['yim']['link'], '</td>
			<td class="windowbg2">', $member['msn']['link'], '</td>
			<td class="windowbg" align="left">', empty($member['group']) ? $member['post_group'] : $member['group'], '</td>
			<td class="windowbg" align="left">', $member['registered_date'], '</td>
			<td class="windowbg2" width="15">', $member['posts'], '</td>
			<td class="windowbg" width="100" align="left">
				', $member['posts'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $member['post_percent'] . '" height="15" alt="" />' : '', '
			</td>
		</tr>';
	}
	// No members?
	else
		echo '
		<tr>
			<td colspan="12" class="windowbg">', $txt[170], '</td>
		</tr>';

	// Show the page numbers again. (makes 'em easier to find!)
	echo '
		<tr>
			<td class="titlebg" colspan="12">', $txt[139], ': ', $context['page_index'], '</td>
		</tr>
	</table>';

	// If it is displaying the result of a search show a "search again" link to edit their criteria.
	if (isset($context['old_search']))
		echo '
			<br />
				<a href="', $scripturl, '?action=mlist;sa=search;search=', $context['old_search_value'], '">', $txt['mlist_search2'], '</a>';
}

// A page allowing people to search the member list.
function template_search()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Start the submission form for the search!
	echo '
		<form action="', $scripturl, '?action=mlist;sa=search" method="post" accept-charset="', $context['character_set'], '">';

	// Display that link tree...
	echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	// Display links to view all/search.
	if (!empty($settings['use_tabs']))
	{
		echo '
		<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
			<tr>
				<td class="mirrortab_first">&nbsp;</td>';

		foreach ($context['sort_links'] as $link)
		{
			if ($link['selected'])
				echo '
				<td class="mirrortab_active_first">&nbsp;</td>
				<td valign="top" class="mirrortab_active_back">
					<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">', $link['label'], '</a>
				</td>
				<td class="mirrortab_active_last">&nbsp;</td>';
			else
				echo '
				<td valign="top" class="mirrortab_back">
					<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">', $link['label'], '</a>
				</td>';
		}

		echo '
				<td class="mirrortab_last">&nbsp;</td>
			</tr>
		</table>
		<div class="tborder">';
	}
	else
	{
		echo '
		<div class="bordercolor" style="padding: 1px;">
			<div class="titlebg" style="padding: 4px 4px 4px 10px;">';
				$links = array();
				foreach ($context['sort_links'] as $link)
					$links[] = ($link['selected'] ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" /> ' : '') . '<a href="' . $scripturl . '?action=mlist' . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '">' . $link['label'] . '</a>';

				echo '
					', implode(' | ', $links), '
			</div>
		</div>
		<div class="bordercolor" style="padding: 1px">';
	}

	// Display the input boxes for the form.
	echo '
		
		<div class="windowbg" align="center" style="padding-bottom: 1ex;">
			<table width="440" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="2" align="left">
						<br />
						<b>', $txt[582], ':</b> <input type="text" name="search" value="', $context['old_search'], '" size="35" /> <input type="submit" name="submit" value="' . $txt[182] . '" style="margin-left: 20px;" /><br />
						<br />
					</td>
				</tr>
				<tr>
					<td align="left">
								<label for="fields-email"><input type="checkbox" name="fields[]" id="fields-email" value="email" checked="checked" class="check" /> ', $txt['mlist_search_email'], '</label><br />
								<label for="fields-messenger"><input type="checkbox" name="fields[]" id="fields-messenger" value="messenger" class="check" /> ', $txt['mlist_search_messenger'], '</label><br />
								<label for="fields-group"><input type="checkbox" name="fields[]" id="fields-group" value="group" class="check" /> ', $txt['mlist_search_group'], '</label>
					</td>
					<td align="left" valign="top">
								<label for="fields-name"><input type="checkbox" name="fields[]" id="fields-name" value="name" checked="checked" class="check" /> ', $txt['mlist_search_name'], '</label><br />
								<label for="fields-website"><input type="checkbox" name="fields[]" id="fields-website" value="website" class="check" /> ', $txt['mlist_search_website'], '</label>
					</td>
				</tr>
			</table>
		</div>
	</div>
</form>';
}

?>