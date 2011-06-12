<?php
// Version: 1.1; Modlog

function template_main()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=modlog" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="order" value="', $context['order'], '" />
			<input type="hidden" name="dir" value="', $context['dir'], '" />
			<input type="hidden" name="start" value="', $context['start'], '" />
			<div class="tborder">
				<table border="0" cellspacing="1" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td>
							<div style="float: left;"><a href="', $scripturl, '?action=helpadmin;help=modlog" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt[119] . '" align="top" /></a> ', $txt['modlog_moderation_log'], '</div>
							<div align="right">', empty($context['search_params']) ? $txt['modlog_total_entries'] : $txt['modlog_search_result'], ': ', $context['entry_count'], '</div>
						</td>
					</tr>
					<tr class="windowbg">
						<td class="smalltext" style="padding: 2ex;">', $txt['modlog_moderation_log_desc'], '</td>
					</tr>';

	// Only display page numbers if not a result of a search.
	if (!empty($context['page_index']))
		echo '
					<tr class="catbg">
						<td>', $txt[139], ': ', $context['page_index'], '</td>
					</tr>';
	echo '
				</table>
				<table border="0" cellspacing="1" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td width="10" align="center"><input type="checkbox" name="all" class="check" onclick="invertAll(this, this.form);" /></td>';

	foreach ($context['columns'] as $column)
	{
		if (!empty($column['not_sortable']))
			echo '
						<td>', $column['label'], '</td>';
		else
		{
			echo '
						<td><a href="' . $column['href'] . '">';
			if ($column['selected'])
				echo '<b>', $column['label'], '</b> <img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" />';
			else
				echo $column['label'];
			echo '</a></td>';
		}
	}

	echo '
					</tr>';

	foreach ($context['entries'] as $entry)
	{
		echo '
					<tr class="windowbg2">
						<td rowspan="2" class="windowbg" align="center"><input type="checkbox" class="check" name="delete[]" value="', $entry['id'], '"', $entry['editable'] ? '' : ' disabled="disabled"', ' /></td>
						<td>', $entry['action'], '</td>
						<td>', $entry['time'], '</td>
						<td>', $entry['moderator']['link'], '</td>
						<td>', $entry['position'], '</td>
						<td>', $entry['ip'], '</td>
					</tr>
					<tr>
						<td colspan="5" class="windowbg">';

		foreach ($entry['extra'] as $key => $value)
			echo '
							<i>', $key, '</i>: ', $value;
		echo '
						</td>
					</tr>';
	}

	if (empty($context['entries']))
		echo '
					<tr>
						<td class="windowbg2" align="center" colspan="7">
							<b>', $txt['modlog_no_entries_found'], '</b>
						</td>
					</tr>';

	echo '
				</table>
				<table border="0" cellspacing="1" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td align="right" valign="bottom">
							<div style="float: left;">
								', $txt['modlog_search'], ' (', $txt['modlog_by'], ': ', $context['search']['label'], '):
								<input type="text" name="search" size="18" value="', $context['search']['string'], '" /> <input type="submit" value="', $txt['modlog_go'], '" />
							</div>

							<input type="submit" name="remove" value="', $txt['modlog_remove'], '" />
							<input type="submit" name="removeall" value="', $txt['modlog_removeall'], '" />
						</td>
					</tr>';

	if (!empty($context['page_index']))
		echo '
					<tr class="catbg">
						<td>', $txt[139], ': ', $context['page_index'], '</td>
					</tr>';

	echo '
				</table>
			</div>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

?>