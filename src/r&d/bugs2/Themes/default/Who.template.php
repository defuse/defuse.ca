<?php
// Version: 1.1; Who

// The only template in the file.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Display the table header and linktree.
	echo '
	<div style="padding: 3px;">', theme_linktree(), '</div>
	<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder">
		<tr class="titlebg">
			<td width="30%"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '">', $txt['who_user'], ' ', $context['sort_by'] == 'user' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td style="width: 14ex;"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '">', $txt['who_time'], ' ', $context['sort_by'] == 'time' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td>', $txt['who_action'], '</td>
		</tr>';

	// This is used to alternate the color of the background.
	$alternate = true;

	// For every member display their name, time and action (and more for admin).
	foreach ($context['members'] as $member)
	{
		// $alternate will either be true or false. If it's true, use "windowbg2" and otherwise use "windowbg".
		echo '
		<tr class="windowbg', $alternate ? '2' : '', '">
			<td>';

		// Guests don't have information like icq, msn, y!, and aim... and they can't be messaged.
		if (!$member['is_guest'])
		{
			echo '
				<div style="float: right; width: 14ex;">
					', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $member['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $member['online']['image_href'] . '" alt="' . $member['online']['text'] . '" align="middle" />' : $member['online']['text'], $context['can_send_pm'] ? '</a>' : '', '
					', $member['icq']['link'], ' ', $member['msn']['link'], ' ', $member['yim']['link'], ' ', $member['aim']['link'], '
				</div>';
		}

		echo '
				<span', $member['is_hidden'] ? ' style="font-style: italic;"' : '', '>', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . $txt[92] . ' ' . $member['name'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '</span>';

		if (!empty($member['ip']))
			echo '
				(<a href="' . $scripturl . '?action=trackip;searchip=' . $member['ip'] . '" target="_blank">' . $member['ip'] . '</a>)';

		echo '
			</td>
			<td nowrap="nowrap">', $member['time'], '</td>
			<td>', $member['action'], '</td>
		</tr>';

		// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, etc.)
		$alternate = !$alternate;
	}

	echo '
		<tr class="titlebg">
			<td colspan="3"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>';
}

?>