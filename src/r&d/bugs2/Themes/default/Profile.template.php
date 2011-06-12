<?php
// Version: 1.1.2; Profile

// Template for the profile side bar - goes before any other profile template.
function template_profile_above()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// Assuming there are actually some areas the user can visit...
	if (!empty($context['profile_areas']))
	{
		echo '
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="180" valign="top">
					<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" width="170">';

		// Loop through every area, displaying its name as a header.
		foreach ($context['profile_areas'] as $section)
		{
			echo '
						<tr>
							<td class="catbg">', $section['title'], '</td>
						</tr>
						<tr class="windowbg2">
							<td class="smalltext">';

			// For every section of the area display it, and bold it if it's the current area.
			foreach ($section['areas'] as $i => $area)
				if ($i == $context['menu_item_selected'])
					echo '
								<b>', $area, '</b><br />';
				else
					echo '
								', $area, '<br />';
			echo '
								<br />
							</td>
						</tr>';
		}
		echo '
					</table>
				</td>
				<td width="100%" valign="top">';
	}
	// If no areas exist just open up a containing table.
	else
	{
		echo '
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="100%" valign="top">';
	}

	// If an error occurred whilst trying to save previously, give the user a clue!
	if (!empty($context['post_errors']))
	{
		echo '
					<table width="85%" cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td>', template_error_message(), '</td>
						</tr>
					</table>';
	}
}

// Template for closing off table started in profile_above.
function template_profile_below()
{
	global $context, $settings, $options;

	echo '
				</td>
			</tr>
		</table>';
}

// This template displays users details without any option to edit them.
function template_summary()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// First do the containing table and table header.
	echo '
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor">
	<tr class="titlebg">
		<td width="420" height="26">
			<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
			', $txt['summary'], ' - ', $context['member']['name'], '
		</td>
		<td align="center" width="150">', $txt[232], '</td>
	</tr>';

	// Do the left hand column - where all the important user info is displayed.
	echo '
	<tr>
		<td class="windowbg" width="420">
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td><b>', $txt[68], ': </b></td>
					<td>', $context['member']['name'], '</td>
				</tr>';
	if (!empty($modSettings['titlesEnable']) && $context['member']['title'] != '')
	{
		echo '
				<tr>
					<td><b>', $txt['title1'], ': </b></td>
					<td>', $context['member']['title'], '</td>
				</tr>';
	}
	echo '
				<tr>
					<td><b>', $txt[86], ': </b></td>
					<td>', $context['member']['posts'], ' (', $context['member']['posts_per_day'], ' ', $txt['posts_per_day'], ')</td>
				</tr><tr>
					<td><b>', $txt[87], ': </b></td>
					<td>', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</td>
				</tr>';

	// If the person looking is allowed, they can check the members IP address and hostname.
	if ($context['can_see_ip'])
	{
		echo '
				<tr>
					<td width="40%">
						<b>', $txt[512], ': </b>
					</td><td>
						<a href="', $scripturl, '?action=trackip;searchip=', $context['member']['ip'], '" target="_blank">', $context['member']['ip'], '</a>
					</td>
				</tr><tr>
					<td width="40%">
						<b>', $txt['hostname'], ': </b>
					</td><td width="55%">
						<div title="', $context['member']['hostname'], '" style="width: 100%; overflow: hidden; font-style: italic;">', $context['member']['hostname'], '</div>
					</td>
				</tr>';
	}

	// If karma enabled show the members karma.
	if ($modSettings['karmaMode'] == '1')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' </b>
					</td><td>
						', ($context['member']['karma']['good'] - $context['member']['karma']['bad']), '
					</td>
				</tr>';
	elseif ($modSettings['karmaMode'] == '2')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' </b>
					</td><td>
						+', $context['member']['karma']['good'], '/-', $context['member']['karma']['bad'], '
					</td>
				</tr>';
	echo '
				<tr>
					<td><b>', $txt[233], ': </b></td>
					<td>', $context['member']['registered'], '</td>
				</tr><tr>
					<td><b>', $txt['lastLoggedIn'], ': </b></td>
					<td>', $context['member']['last_login'], '</td>
				</tr>';

	// Is this member requiring activation and/or banned?
	if (!empty($context['activate_message']) || !empty($context['member']['bans']))
	{
		echo '
				<tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

		// If the person looking at the summary has permission, and the account isn't activated, give the viewer the ability to do it themselves.
		if (!empty($context['activate_message']))
			echo '
				<tr>
					<td colspan="2">
						<span style="color: red;">', $context['activate_message'], '</span>&nbsp;(<a href="' . $scripturl . '?action=profile2;sa=activateAccount;userID=' . $context['member']['id'] . ';sesc=' . $context['session_id'] . '" ', ($context['activate_type'] == 4 ? 'onclick="return confirm(\'' . $txt['profileConfirm'] . '\');"' : ''), '>', $context['activate_link_text'], '</a>)
					</td>
				</tr>';

		// If the current member is banned, show a message and possibly a link to the ban.
		if (!empty($context['member']['bans']))
		{
			echo '
				<tr>
					<td colspan="2">
						<span style="color: red;">', $txt['user_is_banned'], '</span>&nbsp;[<a href="#" onclick="document.getElementById(\'ban_info\').style.display = document.getElementById(\'ban_info\').style.display == \'none\' ? \'\' : \'none\';return false;">' . $txt['view_ban'] . '</a>]
					</td>
				</tr>
				<tr id="ban_info" style="display: none;">
					<td colspan="2">
						<b>', $txt['user_banned_by_following'], ':</b>';

			foreach ($context['member']['bans'] as $ban)
				echo '
						<br /><span class="smalltext">', $ban['explanation'], '</span>';

			echo '
					</td>
				</tr>';
		}
	}

	// Messenger type information.
	echo '
				<tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr><tr>
					<td><b>', $txt[513], ':</b></td>
					<td>', $context['member']['icq']['link_text'], '</td>
				</tr><tr>
					<td><b>', $txt[603], ': </b></td>
					<td>', $context['member']['aim']['link_text'], '</td>
				</tr><tr>
					<td><b>', $txt['MSN'], ': </b></td>
					<td>', $context['member']['msn']['link_text'], '</td>
				</tr><tr>
					<td><b>', $txt[604], ': </b></td>
					<td>', $context['member']['yim']['link_text'], '</td>
				</tr><tr>
					<td><b>', $txt[69], ': </b></td>
					<td>';

	// Only show the email address if it's not hidden.
	if ($context['member']['email_public'])
		echo '
						<a href="mailto:', $context['member']['email'], '">', $context['member']['email'], '</a>';
	// ... Or if the one looking at the profile is an admin they can see it anyway.
	elseif (!$context['member']['hide_email'])
		echo '
						<i><a href="mailto:', $context['member']['email'], '">', $context['member']['email'], '</a></i>';
	else
		echo '
						<i>', $txt[722], '</i>';

	// Some more information.
	echo '
					</td>
				</tr><tr>
					<td><b>', $txt[96], ': </b></td>
					<td><a href="', $context['member']['website']['url'], '" target="_blank">', $context['member']['website']['title'], '</a></td>
				</tr><tr>
					<td><b>', $txt[113], ' </b></td>
					<td>
						<i>', $context['can_send_pm'] ? '<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['member']['online']['image_href'] . '" alt="' . $context['member']['online']['text'] . '" align="middle" />' : $context['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['text'] . '</span>' : '', '</i>';

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['user']['is_owner'])
		echo '
						&nbsp;&nbsp;<a href="', $scripturl, '?action=buddy;u=', $context['member']['id'], ';sesc=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	echo '
					</td>
				</tr><tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr><tr>
					<td><b>', $txt[231], ': </b></td>
					<td>', $context['member']['gender']['name'], '</td>
				</tr><tr>
					<td><b>', $txt[420], ':</b></td>
					<td>', $context['member']['age'] . ($context['member']['today_is_birthday'] ? ' &nbsp; <img src="' . $settings['images_url'] . '/bdaycake.gif" width="40" alt="" />' : ''), '</td>
				</tr><tr>
					<td><b>', $txt[227], ':</b></td>
					<td>', $context['member']['location'], '</td>
				</tr><tr>
					<td><b>', $txt['local_time'], ':</b></td>
					<td>', $context['member']['local_time'], '</td>
				</tr><tr>';

	if (!empty($modSettings['userLanguage']))
		echo '
					<td><b>', $txt['smf225'], ':</b></td>
					<td>', $context['member']['language'], '</td>
				</tr><tr>';

	echo '
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

	// Show the users signature.
	echo '
				<tr>
					<td colspan="2" height="25">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout: fixed;">
							<tr>
								<td style="padding-bottom: 0.5ex;"><b>', $txt[85], ':</b></td>
							</tr><tr>
								<td colspan="2" width="100%" class="smalltext"><div class="signature">', $context['member']['signature'], '</div></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>';

	// Now print the second column where the members avatar/text is shown.
	echo '
		<td class="windowbg" valign="middle" align="center" width="150">
			', $context['member']['avatar']['image'], '<br /><br />
			', $context['member']['blurb'], '
		</td>
	</tr>';

	// Finally, if applicable, span the bottom of the table with links to other useful member functions.
	echo '
	<tr class="titlebg">
		<td colspan="2">', $txt[597], ':</td>
	</tr>
	<tr>
		<td class="windowbg2" colspan="2">';
	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
			<a href="', $scripturl, '?action=pm;sa=send;u=', $context['member']['id'], '">', $txt[688], '.</a><br />
			<br />';
	echo '
			<a href="', $scripturl, '?action=profile;u=', $context['member']['id'], ';sa=showPosts">', $txt[460], ' ', $txt[461], '.</a><br />
			<a href="', $scripturl, '?action=profile;u=', $context['member']['id'], ';sa=statPanel">', $txt['statPanel_show'], '.</a><br />
			<br />
		</td>
	</tr>
</table>';
}

// Template for showing all the posts of the user, in chronological order.
function template_showPosts()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="3" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['showPosts'], '
				</td>
			</tr>';

	// Only show posts if they have made some!
	if (!empty($context['posts']))
	{
		// Page numbers.
		echo '
			<tr class="catbg3">
				<td colspan="3">
					', $txt[139], ': ', $context['page_index'], '
				</td>
			</tr>
		</table>';

		// Button shortcuts
		$quote_button = create_button('quote.gif', 145, 'smf240', 'align="middle"');
		$reply_button = create_button('reply_sm.gif', 146, 146, 'align="middle"');
		$remove_button = create_button('delete.gif', 121, 31, 'align="middle"');
		$notify_button = create_button('notify_sm.gif', 131, 125, 'align="middle"');

		// For every post to be displayed, give it its own subtable, and show the important details of the post.
		foreach ($context['posts'] as $post)
		{
			echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="0" class="bordercolor" align="center">
			<tr>
				<td width="100%">
					<table border="0" width="100%" cellspacing="0" cellpadding="4" class="bordercolor" align="center">
						<tr class="titlebg2">
							<td style="padding: 0 1ex;">
								', $post['counter'], '
							</td>
							<td width="75%" class="middletext">
								&nbsp;<a href="', $scripturl, '#', $post['category']['id'], '">', $post['category']['name'], '</a> / <a href="', $scripturl, '?board=', $post['board']['id'], '.0">', $post['board']['name'], '</a> / <a href="', $scripturl, '?topic=', $post['topic'], '.', $post['start'], '#msg', $post['id'], '">', $post['subject'], '</a>
							</td>
							<td class="middletext" align="right" style="padding: 0 1ex; white-space: nowrap;">
								', $txt[30], ': ', $post['time'], '
							</td>
						</tr>
						<tr>
							<td width="100%" height="80" colspan="3" valign="top" class="windowbg2">
								<div class="post">', $post['body'], '</div>
							</td>
						</tr>
						<tr>
							<td colspan="3" class="windowbg2" align="', !$context['right_to_left'] ? 'right' : 'left', '"><span class="middletext">';

			if ($post['can_delete'])
				echo '
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;start=', $context['start'], ';delete=', $post['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt[154], '?\');">', $remove_button, '</a>';
			if ($post['can_delete'] && ($post['can_mark_notify'] || $post['can_reply']))
				echo '
								', $context['menu_separator'];
			if ($post['can_reply'])
				echo '
					<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '">', $reply_button, '</a>', $context['menu_separator'], '
					<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], ';sesc=', $context['session_id'], '">', $quote_button, '</a>';
			if ($post['can_reply'] && $post['can_mark_notify'])
				echo '
								', $context['menu_separator'];
			if ($post['can_mark_notify'])
				echo '
					<a href="' . $scripturl . '?action=notify;topic=' . $post['topic'] . '.' . $post['start'] . '">' . $notify_button . '</a>';

			echo '
							</span></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';
		}

		// Show more page numbers.
		echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr>
				<td colspan="3" class="catbg3">
					', $txt[139], ': ', $context['page_index'], '
				</td>
			</tr>
		</table>';
	}
	// No posts? Just end the table with a informative message.
	else
		echo '
			<tr class="windowbg2">
				<td>
					', $txt[170], '
				</td>
			</tr>
		</table>';
}

// Template for showing all the buddies of the current user.
function template_editBuddies()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="8" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['editBuddies'], '
				</td>
			</tr>
			<tr class="catbg3">
				<td width="20%">', $txt[68], '</td>
				<td>', $txt['online8'], '</td>
				<td>', $txt[69], '</td>
				<td align="center">', $txt[513], '</td>
				<td align="center">', $txt[603], '</td>
				<td align="center">', $txt[604], '</td>
				<td align="center">', $txt['MSN'], '</td>
				<td></td>
			</tr>';

	// If they don't have any buddies don't list them!
	if (empty($context['buddies']))
		echo '
			<tr class="windowbg">
				<td colspan="8" align="center"><b>', $txt['no_buddies'], '</b></td>
			</tr>';

	// Now loop through each buddy showing info on each.
	$alternate = false;
	foreach ($context['buddies'] as $buddy)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>', $buddy['link'], '</td>
				<td align="center"><a href="', $buddy['online']['href'], '"><img src="', $buddy['online']['image_href'], '" alt="', $buddy['online']['label'], '" title="', $buddy['online']['label'], '" /></a></td>
				<td align="center">', ($buddy['hide_email'] ? '' : '<a href="mailto:' . $buddy['email'] . '"><img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . ' ' . $buddy['name'] . '" /></a>'), '</td>
				<td align="center">', $buddy['icq']['link'], '</td>
				<td align="center">', $buddy['aim']['link'], '</td>
				<td align="center">', $buddy['yim']['link'], '</td>
				<td align="center">', $buddy['msn']['link'], '</td>
				<td align="center"><a href="', $scripturl, '?action=profile;u=', $context['member']['id'], ';sa=editBuddies;remove=', $buddy['id'], '"><img src="', $settings['images_url'], '/icons/delete.gif" alt="', $txt['buddy_remove'], '" title="', $txt['buddy_remove'], '" /></a></td>
			</tr>';

		$alternate = !$alternate;
	}

	echo '
		</table>';

	// Add a new buddy?
	echo '
	<br />
	<form action="', $scripturl, '?action=profile;u=', $context['member']['id'], ';sa=editBuddies" method="post" accept-charset="', $context['character_set'], '">
		<table width="65%" cellpadding="4" cellspacing="0" class="tborder" align="center">
			<tr class="titlebg">
				<td colspan="2">', $txt['buddy_add'], '</td>
			</tr>
			<tr class="windowbg">
				<td width="45%">
					<b>', $txt['who_member'], ':</b>
				</td>
				<td width="55%">
					<input type="text" name="new_buddy" id="new_buddy" size="25" />
					<a href="', $scripturl, '?action=findmember;input=new_buddy;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="top" /></a>
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2" align="right">
					<input type="submit" value="', $txt['buddy_add_button'], '" />
				</td>
			</tr>
		</table>
	</form>';
}
// This template shows an admin information on a users IP addresses used and errors attributed to them.
function template_trackUser()
{
	global $context, $settings, $options, $scripturl, $txt;

	// The first table shows IP information about the user.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="left" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						<b>', $txt['view_ips_by'], ' ', $context['member']['name'], '</b>
					</td>
				</tr>';

	// The last IP the user used.
	echo '
				<tr>
					<td class="windowbg2" align="left" width="200">', $txt['most_recent_ip'], ':</td>
					<td class="windowbg2" align="left">
						<a href="', $scripturl, '?action=trackip;searchip=', $context['last_ip'], ';">', $context['last_ip'], '</a>
					</td>
				</tr>';

	// Lists of IP addresses used in messages / error messages.
	echo '
				<tr>
					<td class="windowbg2" align="left">', $txt['ips_in_messages'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['ips']) > 0 ? implode(', ', $context['ips']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr><tr>
					<td class="windowbg2" align="left">', $txt['ips_in_errors'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['error_ips']) > 0 ? implode(', ', $context['error_ips']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr>';

	// List any members that have used the same IP addresses as the current member.
	echo '
				<tr>
					<td class="windowbg2" align="left">', $txt['members_in_range'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['members_in_range']) > 0 ? implode(', ', $context['members_in_range']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr>
			</table>
		</td></tr></table>
		<br />';

	// The second table lists all the error messages the user has caused/received.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['errors_by'], ' ', $context['member']['name'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['errors_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						', $txt[139], ': ', $context['page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt[72], '</td>
					<td>', $txt[317], '</td>
				</tr>';

	// If there arn't any messages just give a message stating this.
	if (empty($context['error_messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_errors_from_user'], '</i></td></tr>';

	// Otherwise print every error message out.
	else
		// For every error message print the IP address that caused it, the message displayed and the date it occurred.
		foreach ($context['error_messages'] as $error)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $error['ip'], ';">', $error['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $error['message'], '<br />
						<a href="', $error['url'], '">', $error['url'], '</a>
					</td>
					<td class="windowbg2">', $error['time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>';
}

// The template for trackIP, allowing the admin to see where/who a certain IP has been used.
function template_trackIP()
{
	global $context, $settings, $options, $scripturl, $txt;

	// This function always defaults to the last IP used by a member but can be set to track any IP.
	echo '
		<form action="', $scripturl, '?action=trackip" method="post" accept-charset="', $context['character_set'], '">';

	// The first table in the template gives an input box to allow the admin to enter another IP to track.
	echo '
			<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
				<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
					<tr class="titlebg">
						<td>', $txt['trackIP'], '</td>
					</tr><tr>
						<td class="windowbg2">
							', $txt['enter_ip'], ':&nbsp;&nbsp;<input type="text" name="searchip" value="', $context['ip'], '" />&nbsp;&nbsp;<input type="submit" value="', $txt['trackIP'], '" />
						</td>
					</tr>
				</table>
			</td></tr></table>
		</form>
		<br />';

	// The table inbetween the first and second table shows links to the whois server for every region.
	if ($context['single_ip'])
	{
		echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['whois_title'], ' ', $context['ip'], '
					</td>
				</tr><tr>
					<td class="windowbg2">';
		foreach ($context['whois_servers'] as $server)
			echo '
						<a href="', $server['url'], '" target="_blank"', isset($context['auto_whois_server']) && $context['auto_whois_server']['name'] == $server['name'] ? ' style="font-weight: bold;"' : '', '>', $server['name'], '</a><br />';
		echo '
					</td>
				</tr>
			</table>
		</td></tr></table>
		<br />';
	}

	// The second table lists all the members who have been logged as using this IP address.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['members_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['display_name'], '</td>
				</tr>';
	if (empty($context['ips']))
		echo '
				<tr><td class="windowbg2" colspan="2"><i>', $txt['no_members_from_ip'], '</i></td></tr>';
	else
		// Loop through each of the members and display them.
		foreach ($context['ips'] as $ip => $memberlist)
			echo '
				<tr>
					<td class="windowbg2"><a href="', $scripturl, '?action=trackip;searchip=', $ip, ';">', $ip, '</a></td>
					<td class="windowbg2">', implode(', ', $memberlist), '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>
		<br />';

	// The third table in the template displays a list of all the messages sent using this IP (can be quite long).
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['messages_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['messages_from_ip_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						<b>', $txt[139], ':</b> ', $context['message_page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['rtm8'], '</td>
					<td>', $txt[319], '</td>
					<td>', $txt[317], '</td>
				</tr>';

	// No message means nothing to do!
	if (empty($context['messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_messages_from_ip'], '</i></td></tr>';
	else
		// For every message print the IP, member who posts it, subject (with link) and date posted.
		foreach ($context['messages'] as $message)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $message['ip'], '">', $message['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $message['member']['link'], '
					</td>
					<td class="windowbg2">
						<a href="', $scripturl, '?topic=', $message['topic'], '.msg', $message['id'], '#msg', $message['id'], '">
							', $message['subject'], '
						</a>
					</td>
					<td class="windowbg2">', $message['time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>
		<br />';

	// The final table in the template lists all the error messages caused/received by anyone using this IP address.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['errors_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['errors_from_ip_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						', $txt[139], ': ', $context['error_page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['display_name'], '</td>
					<td>', $txt[72], '</td>
					<td>', $txt[317], '</td>
				</tr>';
	if (empty($context['error_messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_errors_from_ip'], '</i></td></tr>';
	else
		// For each error print IP address, member, message received and date caused.
		foreach ($context['error_messages'] as $error)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $error['ip'], '">', $error['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $error['member']['link'], '
					</td>
					<td class="windowbg2">
						', $error['message'], '<br />
						<a href="', $error['url'], '">', $error['url'], '</a>
					</td>
					<td class="windowbg2">', $error['error_time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>';
}

function template_showPermissions()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="90%" border="0" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
			<tr class="titlebg">
				<td colspan="2" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['showPermissions'], '
					</td>
			</tr>';
	if ($context['member']['has_all_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_all'], '</td>
			</tr>';
	}
	else
	{
		if (!empty($context['no_access_boards']))
		{
			echo '
			<tr class="catbg">
				<td align="left" colspan="2">', $txt['showPermissions_restricted_boards'], '</td>
			</tr><tr class="windowbg">
				<td colspan="2" class="smalltext">
					', $txt['showPermissions_restricted_boards_desc'], ':<br />';
			foreach ($context['no_access_boards'] as $no_access_board)
				echo '
					<a href="', $scripturl, '?board=', $no_access_board['id'], '">', $no_access_board['name'], '</a>', $no_access_board['is_last'] ? '' : ', ';
			echo '
				</td>
			</tr>';
		}

		// General Permissions section.
		echo '
			<tr class="catbg">
				<td align="left" colspan="2">', $txt['showPermissions_general'], '</td>
			</tr>';
		if (!empty($context['member']['permissions']['general']))
		{
			echo '
			<tr class="titlebg">
				<td width="50%">', $txt['showPermissions_permission'], '</td>
				<td width="50%"></td>
			</tr>';

			foreach ($context['member']['permissions']['general'] as $permission)
			{
				echo '
			<tr>
				<td class="windowbg" valign="top">
					', $permission['is_denied'] ? '<del>' . $permission['id'] . '</del>' : $permission['id'], '<br />
					<span class="smalltext">', $permission['name'], '</span>
				</td>
				<td class="windowbg2" valign="top"><span class="smalltext">';
				if ($permission['is_denied'])
					echo '
					<span style="color: red; font-weight: bold;">', $txt['showPermissions_denied'], ': </span>', implode(', ', $permission['groups']['denied']);
				else
					echo '
					<span style="font-weight: bold;">', $txt['showPermissions_given'], ': </span>', implode(', ', $permission['groups']['allowed']);
				echo '
				</span></td>
			</tr>';
			}
		}
		else
			echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_none_general'], '</td>
			</tr>';

		// Board permission section.
		echo '
			<tr class="catbg">
				<td align="left" colspan="2">
					<a name="board_permissions"></a>
					<form action="' . $scripturl . '?action=profile;u=', $context['member']['id'], ';sa=showPermissions#board_permissions" method="post" accept-charset="', $context['character_set'], '">
						', $txt['showPermissions_select'], ':
						<select name="board" onchange="if (this.options[this.selectedIndex].value) this.form.submit();">
							<option value="0"', $context['board'] == 0 ? ' selected="selected"' : '', '>', $txt['showPermissions_global'], '</option>';
		if (!empty($context['boards']))
			echo '
							<option value="" disabled="disabled">---------------------------</option>';

		// Fill the box with any local permission boards.
		foreach ($context['boards'] as $board)
			echo '
							<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['name'], '</option>';

		echo '
						</select>
					</form>
				</td>
			</tr>';
		if (!empty($context['member']['permissions']['board']))
		{
			echo '
			<tr class="titlebg">
				<td>', $txt['showPermissions_permission'], '</td>
				<td></td>
			</tr>';
			foreach ($context['member']['permissions']['board'] as $permission)
			{
				echo '
			<tr>
				<td class="windowbg" valign="top">
					', $permission['is_denied'] ? '<del>' . $permission['id'] . '</del>' : $permission['id'], '<br />
					<span class="smalltext">', $permission['name'], '</span>
				</td>
				<td class="windowbg2" valign="top"><span class="smalltext">';
				if ($permission['is_denied'])
				{
					echo '
					<span style="color: red; font-weight: bold;">', $txt['showPermissions_denied'], ': </span>', implode(', ', $permission['groups']['denied']), '<br />';
				}
				else
				{
					echo '
					<span style="font-weight: bold;">', $txt['showPermissions_given'], ': </span>', implode(', ', $permission['groups']['allowed']), '<br />';
				}
				echo '
				</span></td>
			</tr>';
			}
		}
		else
			echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_none_board'], '</td>
			</tr>';
	}
	echo '
		</table><br />';
}

// Template for user statistics, showing graphs and the like.
function template_statPanel()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="4" height="26">&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['statPanel_generalStats'], ' - ', $context['member']['name'], '</td>
			</tr>';

	// First, show a few text statistics such as post/topic count.
	echo '
		<tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_info.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" valign="top" colspan="3">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td nowrap="nowrap">', $txt['statPanel_total_time_online'], ':</td>
							<td align="right">', $context['time_logged_in'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_total_posts'], ':</td>
							<td align="right">', $context['num_posts'], ' ', $txt['statPanel_posts'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_total_topics'], ':</td>
							<td align="right">', $context['num_topics'], ' ', $txt['statPanel_topics'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_users_polls'], ':</td>
							<td align="right">', $context['num_polls'], ' ', $txt['statPanel_polls'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_users_votes'], ':</td>
							<td align="right">', $context['num_votes'], ' ', $txt['statPanel_votes'], '</td>
						</tr>
					</table>
				</td>
			</tr>';

	// This next section draws a graph showing what times of day they post the most.
	echo '
			<tr class="titlebg">
				<td colspan="4" width="100%">', $txt['statPanel_activityTime'], '</td>
			</tr><tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_views.gif" width="20" height="20" alt="" /></td>
				<td colspan="3" class="windowbg2" width="100%" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">';

	// If they haven't post at all, don't draw the graph.
	if (empty($context['posts_by_time']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	// Otherwise do!
	else
	{
		echo '
						<tr>
							<td width="2%" valign="bottom"></td>';

		// Loops through each hour drawing the bar to the correct height.
		foreach ($context['posts_by_time'] as $time_of_day)
			echo '
							<td width="4%" valign="bottom" align="center"><img src="', $settings['images_url'], '/bar.gif" width="12" height="', $time_of_day['posts_percent'], '" alt="" /></td>';
		echo '
							<td width="2%" valign="bottom"></td>
						</tr><tr>
							<td width="2%" valign="bottom"></td>';
		// The labels.
		foreach ($context['posts_by_time'] as $time_of_day)
			echo '
							<td width="4%" valign="bottom" align="center" style="border-color: black; border-style: solid; border-width: 1px ', $time_of_day['hour'] != 23 ? '1px' : '0px', ' 0px 0px">', $time_of_day['hour'], '</td>';
		echo '
							<td width="2%" valign="bottom"></td>
						</tr><tr>
							<td width="100%" colspan="26" align="center"><b>', $txt['statPanel_timeOfDay'], '</b></td>
						</tr>';
	}
	echo '
					</table>
				</td>
			</tr>';

	// The final section is two columns with the most popular boards by posts and activity (activity = users posts / total posts).
	echo '
			<tr class="titlebg">
				<td colspan="2" width="50%">', $txt['statPanel_topBoards'], '</td>
				<td colspan="2" width="50%">', $txt['statPanel_topBoardsActivity'], '</td>
			</tr><tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_replies.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	if (empty($context['popular_boards']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	else
	{
		// Draw a bar for every board.
		foreach ($context['popular_boards'] as $board)
		{
			echo '
						<tr>
							<td width="60%" valign="top">', $board['link'], '</td>
							<td width="20%" valign="top">', $board['posts'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $board['posts_percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
							<td width="20%" align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="top">', empty($context['hide_num_posts']) ? $board['posts'] : '', '</td>
						</tr>';
		}
	}
	echo '
					</table>
				</td>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_replies.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" width="100%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	if (empty($context['board_activity']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	else
	{
		// Draw a bar for every board.
		foreach ($context['board_activity'] as $activity)
		{
			echo '
						<tr>
							<td width="60%" valign="top">', $activity['link'], '</td>
							<td width="20%" valign="top">', $activity['percent'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $activity['percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
							<td width="20%" align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="top">', $activity['percent'], '%</td>
						</tr>';
		}
	}
	echo '
					</table>
				</td>
			</tr>
		</table>';
}

// Template for changing user account information.
function template_account()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// Javascript for checking if password has been entered / taking admin powers away from themselves.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function checkProfileSubmit()
			{';

	// If this part requires a password, make sure to give a warning.
	if ($context['user']['is_owner'] && $context['require_password'])
		echo '
				// Did you forget to type your password?
				if (document.forms.creator.oldpasswrd.value == "")
				{
					alert("', $txt['smf244'], '");
					return false;
				}';

	// This part checks if they are removing themselves from administrative power on accident.
	if ($context['allow_edit_membergroups'] && $context['user']['is_owner'] && $context['member']['group'] == 1)
		echo '
				if (typeof(document.forms.creator.ID_GROUP) != "undefined" && document.forms.creator.ID_GROUP.value != "1")
					return confirm("', $txt['deadmin_confirm'], '");';

	echo '
				return true;
			}
		// ]]></script>';

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" onsubmit="return checkProfileSubmit();">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt[79], '
					</td>
				</tr>';

	// Display Name, language and date user registered.
	echo '
				<tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $txt['account_info'], '
					</td>
				</tr>
				<tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table width="100%" cellpadding="3" cellspacing="0" border="0">';

	// Only show these settings if you're allowed to edit the account itself (not just the membergroups).
	if ($context['allow_edit_account'])
	{
		if ($context['user']['is_admin'] && !empty($context['allow_edit_username']))
			echo '
							<tr>
								<td colspan="2" align="center" style="color: red">', $txt['username_warning'], '</td>
							</tr>
							<tr>
								<td width="40%">
									<b>', $txt[35], ': </b>
								</td>
								<td>
									<input type="text" name="memberName" size="30" value="', $context['member']['username'], '" />
								</td>
							</tr>';
		else
			echo '
							<tr>
								<td width="40%">
									<b>', $txt[35], ': </b>', $context['user']['is_admin'] ? '
									<div class="smalltext">(<a href="' . $scripturl . '?action=profile;u=' . $context['member']['id'] . ';sa=account;changeusername" style="font-style: italic;">' . $txt['username_change'] . '</a>)</div>' : '', '
								</td>
								<td>
									', $context['member']['username'], '
								</td>
							</tr>';

		echo '
							<tr>
								<td>
									<b', (isset($context['modify_error']['no_name']) || isset($context['modify_error']['name_taken']) ? ' style="color: red;"' : ''), '>', $txt[68], ': </b>
									<div class="smalltext">', $txt[518], '</div>
								</td>
								<td>', ($context['allow_edit_name'] ? '<input type="text" name="realName" size="30" value="' . $context['member']['name'] . '" maxlength="60" />' : $context['member']['name']), '</td>
							</tr>';

		// Allow the administrator to change the date they registered on and their post count.
		if ($context['user']['is_admin'])
			echo '
							<tr>
								<td><b>', $txt[233], ':</b></td>
								<td><input type="text" name="dateRegistered" size="30" value="', $context['member']['registered'], '" /></td>
							</tr>
							<tr>
								<td><b>', $txt[86], ': </b></td>
								<td><input type="text" name="posts" size="4" value="', $context['member']['posts'], '" /></td>
							</tr>';

		// Only display if admin has enabled "user selectable language".
		if (!empty($modSettings['userLanguage']) && count($context['languages']) > 1)
		{
			echo '
							<tr>
								<td width="40%"><b>', $txt[349], ':</b></td>
								<td>
									<select name="lngfile">';

			// Fill a select box with all the languages installed.
			foreach ($context['languages'] as $language)
				echo '
										<option value="', $language['filename'], '"', $language['selected'] ? ' selected="selected"' : '', '>', $language['name'], '</option>';
			echo '
									</select>
								</td>
							</tr>';
		}
	}

	// Only display member group information/editing with the proper permissions.
	if ($context['allow_edit_membergroups'])
	{
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>
								<td valign="top">
									<b>', $txt['primary_membergroup'], ': </b>
									<div class="smalltext">(<a href="', $scripturl, '?action=helpadmin;help=moderator_why_missing" onclick="return reqWin(this.href);">', $txt['moderator_why_missing'], '</a>)</div>
								</td>
								<td>
									<select name="ID_GROUP">';
		// Fill the select box with all primary member groups that can be assigned to a member.
		foreach ($context['member_groups'] as $member_group)
			echo '
										<option value="', $member_group['id'], '"', $member_group['is_primary'] ? ' selected="selected"' : '', '>
											', $member_group['name'], '
										</option>';
		echo '
									</select>
								</td>
							</tr><tr>
								<td valign="top"><b>', $txt['additional_membergroups'], ':</b></td>
								<td>
									<div id="additionalGroupsList">
										<input type="hidden" name="additionalGroups[]" value="0" />';
		// For each membergroup show a checkbox so members can be assigned to more than one group.
		foreach ($context['member_groups'] as $member_group)
			if ($member_group['can_be_additional'])
				echo '
										<label for="additionalGroups-', $member_group['id'], '"><input type="checkbox" name="additionalGroups[]" value="', $member_group['id'], '" id="additionalGroups-', $member_group['id'], '"', $member_group['is_additional'] ? ' checked="checked"' : '', ' class="check" /> ', $member_group['name'], '</label><br />';
		echo '
									</div>
									<a href="javascript:void(0);" onclick="document.getElementById(\'additionalGroupsList\').style.display = \'block\'; document.getElementById(\'additionalGroupsLink\').style.display = \'none\'; return false;" id="additionalGroupsLink" style="display: none;">', $txt['additional_membergroups_show'], '</a>
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										document.getElementById("additionalGroupsList").style.display = "none";
										document.getElementById("additionalGroupsLink").style.display = "";
									// ]]></script>
								</td>
							</tr>';
	}

	// Show this part if you're not only here for assigning membergroups.
	if ($context['allow_edit_account'])
	{
		// Show email address box.
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>
								<td width="40%"><b', (isset($context['modify_error']['bad_email']) || isset($context['modify_error']['no_email']) || isset($context['modify_error']['email_taken']) ? ' style="color: red;"' : ''), '>', $txt[69], ': </b><div class="smalltext">', $txt[679], '</div></td>
								<td><input type="text" name="emailAddress" size="30" value="', $context['member']['email'], '" /></td>
							</tr>';

		// If the user is allowed to hide their email address from the public give them the option to here.
		if ($context['allow_hide_email'])
		{
			echo '
							<tr>
								<td width="40%"><b>', $txt[721], '</b></td>
								<td><input type="hidden" name="hideEmail" value="0" /><input type="checkbox" name="hideEmail"', $context['member']['hide_email'] ? ' checked="checked"' : '', ' value="1" class="check" /></td>
							</tr>';
	}

		// Option to show online status - if they are allowed to.
		if ($context['allow_hide_online'])
		{
			echo '
							<tr>
								<td width="40%"><b>', $txt['show_online'], '</b></td>
								<td><input type="hidden" name="showOnline" value="0" /><input type="checkbox" name="showOnline"', $context['member']['show_online'] ? ' checked="checked"' : '', ' value="1" class="check" /></td>
							</tr>';
		}

		// Show boxes so that the user may change his or her password.
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>
								<td width="40%"><b', (isset($context['modify_error']['bad_new_password']) ? ' style="color: red;"' : ''), '>', $txt[81], ': </b><div class="smalltext">', $txt[596], '</div></td>
								<td><input type="password" name="passwrd1" size="20" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt[82], ': </b></td>
								<td><input type="password" name="passwrd2" size="20" /></td>
							</tr>';

		// This section allows the user to enter secret question/answer so they can reset a forgotten password.
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt['pswd1'], ':</b><div class="smalltext">', $txt['secret_desc'], '</div></td>
								<td><input type="text" name="secretQuestion" size="50" value="', $context['member']['secret_question'], '" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt['pswd2'], ':</b><div class="smalltext">', $txt['secret_desc2'], '</div></td>
								<td><input type="text" name="secretAnswer" size="20" /><span class="smalltext" style="margin-left: 4ex;"><a href="', $scripturl, '?action=helpadmin;help=secret_why_blank" onclick="return reqWin(this.href);">', $txt['secret_why_blank'], '</a></span></td>
							</tr>';
	}
	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
						</table>
					</td>
				</tr>
			</table>
		</form>';
}

// Template for forum specific options - avatar, signature etc.
function template_forumProfile()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" enctype="multipart/form-data">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt[79], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $txt['forumProfile_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table border="0" width="100%" cellpadding="5" cellspacing="0">';

	// This is the avatar selection table that is only displayed if avatars are enabled!
	if (!empty($context['member']['avatar']['allow_server_stored']) || !empty($context['member']['avatar']['allow_upload']) || !empty($context['member']['avatar']['allow_external']))
	{
		// If users are allowed to choose avatars stored on the server show selection boxes to choice them from.
		if (!empty($context['member']['avatar']['allow_server_stored']))
		{
			echo '
							<tr>
								<td width="40%" valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0" style="height: 25ex;"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_server_stored" value="server_stored"', ($context['member']['avatar']['choice'] == 'server_stored' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;">
											<b', (isset($context['modify_error']['bad_avatar']) ? ' style="color: red;"' : ''), '><label for="avatar_choice_server_stored">', $txt[229], ':</label></b>
											<div style="margin: 2ex;"><img name="avatar" id="avatar" src="', !empty($context['member']['avatar']['allow_external']) && $context['member']['avatar']['choice'] == 'external' ? $context['member']['avatar']['external'] : $modSettings['avatar_url'] . '/blank.gif', '" alt="Do Nothing" /></div>
										</td>
									</tr></table>
								</td>
								<td>
									<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
										<td style="width: 20ex;">
											<select name="cat" id="cat" size="10" onchange="changeSel(\'\');" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'server_stored\');">';
			// This lists all the file catergories.
			foreach ($context['avatars'] as $avatar)
				echo '
												<option value="', $avatar['filename'] . ($avatar['is_dir'] ? '/' : ''), '"', ($avatar['checked'] ? ' selected="selected"' : ''), '>', $avatar['name'], '</option>';
			echo '
											</select>
										</td>
										<td>
											<select name="file" id="file" size="10" style="display: none;" onchange="showAvatar()" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'server_stored\');" disabled="disabled"><option></option></select>
										</td>
									</tr></table>
								</td>
							</tr>';
		}

		// If the user can link to an off server avatar, show them a box to input the address.
		if (!empty($context['member']['avatar']['allow_external']))
		{
			echo '
							<tr>
								<td valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_external" value="external"', ($context['member']['avatar']['choice'] == 'external' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;"><b><label for="avatar_choice_external">', $txt[475], ':</label></b><div class="smalltext">', $txt[474], '</div></td>
									</tr></table>
								</td>
								<td valign="top">
									<input type="text" name="userpicpersonal" size="45" value="', $context['member']['avatar']['external'], '" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'external\');" onchange="if (typeof(previewExternalAvatar) != \'undefined\') previewExternalAvatar(this.value);" />
								</td>
							</tr>';
		}

		// If the user is able to upload avatars to the server show them an upload box.
		if (!empty($context['member']['avatar']['allow_upload']))
			echo '
							<tr>
								<td valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_upload" value="upload"', ($context['member']['avatar']['choice'] == 'upload' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;"><b><label for="avatar_choice_upload">', $txt['avatar_will_upload'], ':</label></b></td>
									</tr></table>
								</td>
								<td valign="top">
									', ($context['member']['avatar']['ID_ATTACH'] > 0 ? '<img src="' . $context['member']['avatar']['href'] . '" /><input type="hidden" name="ID_ATTACH" value="' . $context['member']['avatar']['ID_ATTACH'] . '" /><br /><br />' : ''), '
									<input type="file" size="48" name="attachment" value="" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'upload\');" />
								</td>
							</tr>';
	}

	// Personal text...
	echo '
							<tr>
								<td width="40%"><b>', $txt[228], ': </b></td>
								<td><input type="text" name="personalText" size="50" maxlength="50" value="', $context['member']['blurb'], '" /></td>
							</tr>
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

	// Gender, birthdate and location.
	echo '
							<tr>
								<td width="40%">
									<b>', $txt[563], ':</b>
									<div class="smalltext">', $txt[566], ' - ', $txt[564], ' - ', $txt[565], '</div>
								</td>
								<td class="smalltext">
									<input type="text" name="bday3" size="4" maxlength="4" value="', $context['member']['birth_date']['year'], '" /> -
									<input type="text" name="bday1" size="2" maxlength="2" value="', $context['member']['birth_date']['month'], '" /> -
									<input type="text" name="bday2" size="2" maxlength="2" value="', $context['member']['birth_date']['day'], '" />
								</td>
							</tr><tr>
								<td width="40%"><b>', $txt[227], ': </b></td>
								<td><input type="text" name="location" size="50" value="', $context['member']['location'], '" /></td>
							</tr>
							<tr>
								<td width="40%"><b>', $txt[231], ': </b></td>
								<td>
									<select name="gender" size="1">
										<option value="0"></option>
										<option value="1"', ($context['member']['gender']['name'] == 'm' ? ' selected="selected"' : ''), '>', $txt[238], '</option>
										<option value="2"', ($context['member']['gender']['name'] == 'f' ? ' selected="selected"' : ''), '>', $txt[239], '</option>
									</select>
								</td>
							</tr><tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

	// All the messenger type contact info.
	echo '
							<tr>
								<td width="40%"><b>', $txt[513], ': </b><div class="smalltext">', $txt[600], '</div></td>
								<td><input type="text" name="ICQ" size="24" value="', $context['member']['icq']['name'], '" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt[603], ': </b><div class="smalltext">', $txt[601], '</div></td>
								<td><input type="text" name="AIM" maxlength="16" size="24" value="', $context['member']['aim']['name'], '" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt['MSN'], ': </b><div class="smalltext">', $txt['smf237'], '.</div></td>
								<td><input type="text" name="MSN" size="24" value="', $context['member']['msn']['name'], '" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt[604], ': </b><div class="smalltext">', $txt[602], '</div></td>
								<td><input type="text" name="YIM" maxlength="32" size="24" value="', $context['member']['yim']['name'], '" /></td>
							</tr><tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

	// Input box for custom titles, if they can edit it...
	if (!empty($modSettings['titlesEnable']) && $context['allow_edit_title'])
		echo '
							<tr>
								<td width="40%"><b>' . $txt['title1'] . ': </b></td>
								<td><input type="text" name="usertitle" size="50" value="' . $context['member']['title'] . '" /></td>
							</tr>';

	// Show the signature box.
	echo '
							<tr>
								<td width="40%" valign="top">
									<b>', $txt[85], ':</b>
									<div class="smalltext">', $txt[606], '</div><br />
									<br />';

	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'creator\', \'signature\');" />';

	echo '
								</td>
								<td>
									<textarea class="editor" onkeyup="calcCharLeft();" name="signature" rows="5" cols="50">', $context['member']['signature'], '</textarea><br />';

	// If there is a limit at all!
	if (!empty($context['max_signature_length']))
		echo '
									<span class="smalltext">', $txt[664], ' <span id="signatureLeft">', $context['max_signature_length'], '</span></span>';

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';

	// Some javascript used to count how many characters have been used so far in the signature.
	echo '
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										function tick()
										{
											if (typeof(document.forms.creator) != "undefined")
											{
												calcCharLeft();
												setTimeout("tick()", 1000);
											}
											else
												setTimeout("tick()", 800);
										}

										function calcCharLeft()
										{
											var maxLength = ', $context['max_signature_length'], ';
											var oldSignature = "", currentSignature = document.forms.creator.signature.value;

											if (!document.getElementById("signatureLeft"))
												return;

											if (oldSignature != currentSignature)
											{
												oldSignature = currentSignature;

												if (currentSignature.replace(/\r/, "").length > maxLength)
													document.forms.creator.signature.value = currentSignature.replace(/\r/, "").substring(0, maxLength);
												currentSignature = document.forms.creator.signature.value.replace(/\r/, "");
											}

											setInnerHTML(document.getElementById("signatureLeft"), maxLength - currentSignature.length);
										}

										setTimeout("tick()", 800);
									// ]]></script>
								</td>
							</tr>';

	// Website details.
	echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>
							<tr>
								<td width="40%"><b>', $txt[83], ': </b><div class="smalltext">', $txt[598], '</div></td>
								<td><input type="text" name="websiteTitle" size="50" value="', $context['member']['website']['title'], '" /></td>
							</tr><tr>
								<td width="40%"><b>', $txt[84], ': </b><div class="smalltext">', $txt[599], '</div></td>
								<td><input type="text" name="websiteUrl" size="50" value="', $context['member']['website']['url'], '" /></td>
							</tr>';

	// If karma is enabled let the admin edit it...
	if ($context['user']['is_admin'] && !empty($modSettings['karmaMode']))
	{
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>
								<td valign="top"><b>', $modSettings['karmaLabel'], '</b></td>
								<td>
									', $modSettings['karmaApplaudLabel'], ' <input type="text" name="karmaGood" size="4" value="', $context['member']['karma']['good'], '" onchange="setInnerHTML(document.getElementById(\'karmaTotal\'), this.value - this.form.karmaBad.value);" style="margin-right: 2ex;" /> ', $modSettings['karmaSmiteLabel'], ' <input type="text" name="karmaBad" size="4" value="', $context['member']['karma']['bad'], '" onchange="this.form.karmaGood.onchange();" /><br />
									(', $txt[94], ': <span id="karmaTotal">', ($context['member']['karma']['good'] - $context['member']['karma']['bad']), '</span>)
								</td>
							</tr>';
	}

	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
						</table>
					</td>
				</tr>
			</table>';

	/* If the user is allowed to choose avatars stored on the server, the below javascript is used to update the
		file listing of avatars as the user changes catergory. It also updates the preview image as they choose
		different files on the select box. */
	if (!empty($context['member']['avatar']['allow_server_stored']))
		echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var files = ["' . implode('", "', $context['avatar_list']) . '"];
				var avatar = document.getElementById("avatar");
				var cat = document.getElementById("cat");
				var selavatar = "' . $context['avatar_selected'] . '";
				var avatardir = "' . $modSettings['avatar_url'] . '/";
				var size = avatar.alt.substr(3, 2) + " " + avatar.alt.substr(0, 2) + String.fromCharCode(117, 98, 116);
				var file = document.getElementById("file");

				if (avatar.src.indexOf("blank.gif") > -1)
					changeSel(selavatar);
				else
					previewExternalAvatar(avatar.src)

				function changeSel(selected)
				{
					if (cat.selectedIndex == -1)
						return;

					if (cat.options[cat.selectedIndex].value.indexOf("/") > 0)
					{
						var i;
						var count = 0;

						file.style.display = "inline";
						file.disabled = false;

						for (i = file.length; i >= 0; i = i - 1)
							file.options[i] = null;

						for (i = 0; i < files.length; i++)
							if (files[i].indexOf(cat.options[cat.selectedIndex].value) == 0)
							{
								var filename = files[i].substr(files[i].indexOf("/") + 1);
								var showFilename = filename.substr(0, filename.lastIndexOf("."));
								showFilename = showFilename.replace(/[_]/g, " ");

								file.options[count] = new Option(showFilename, files[i]);

								if (filename == selected)
								{
									if (file.options.defaultSelected)
										file.options[count].defaultSelected = true;
									else
										file.options[count].selected = true;
								}

								count++;
							}

						if (file.selectedIndex == -1 && file.options[0])
							file.options[0].selected = true;

						showAvatar();
					}
					else
					{
						file.style.display = "none";
						file.disabled = true;
						document.getElementById("avatar").src = avatardir + cat.options[cat.selectedIndex].value;
						document.getElementById("avatar").style.width = "";
						document.getElementById("avatar").style.height = "";
					}
				}

				function showAvatar()
				{
					if (file.selectedIndex == -1)
						return;

					document.getElementById("avatar").src = avatardir + file.options[file.selectedIndex].value;
					document.getElementById("avatar").alt = file.options[file.selectedIndex].text;
					document.getElementById("avatar").alt += file.options[file.selectedIndex].text == size ? "!" : "";
					document.getElementById("avatar").style.width = "";
					document.getElementById("avatar").style.height = "";
				}

				function previewExternalAvatar(src)
				{
					if (!document.getElementById("avatar"))
						return;

					var maxHeight = ', !empty($modSettings['avatar_max_height_external']) ? $modSettings['avatar_max_height_external'] : 0, ';
					var maxWidth = ', !empty($modSettings['avatar_max_width_external']) ? $modSettings['avatar_max_width_external'] : 0, ';
					var tempImage = new Image();

					tempImage.src = src;
					if (maxWidth != 0 && tempImage.width > maxWidth)
					{
						document.getElementById("avatar").style.height = parseInt((maxWidth * tempImage.height) / tempImage.width) + "px";
						document.getElementById("avatar").style.width = maxWidth + "px";
					}
					else if (maxHeight != 0 && tempImage.height > maxHeight)
					{
						document.getElementById("avatar").style.width = parseInt((maxHeight * tempImage.width) / tempImage.height) + "px";
						document.getElementById("avatar").style.height = maxHeight + "px";
					}
					document.getElementById("avatar").src = src;
				}
			// ]]></script>';
	echo '
		</form>';

	if ($context['show_spellchecking'])
		echo '
		<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
}

// Template for showing theme settings. Note: template_options() actually adds the theme specific options.
function template_theme()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var localTime = new Date();
		var serverTime = new Date("', $context['current_forum_time'], '");
		
		function autoDetectTimeOffset()
		{
			// Get the difference between the two, set it up so that the sign will tell us who is ahead of who
			var diff = Math.round((localTime.getTime() - serverTime.getTime())/3600000);

			// Make sure we are limiting this to one day\'s difference
			diff %= 24;

			document.forms.creator.timeOffset.value = diff;
		}
	// ]]></script>';

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" border="0" align="top" />&nbsp;
						', $txt[79], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $txt['theme_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table border="0" width="100%" cellpadding="3">';

	// Are they allowed to change their theme? // !!! Change to permission?
	if ($modSettings['theme_allow'] || $context['user']['is_admin'])
	{
		echo '
							<tr>
								<td colspan="2" width="40%"><b>', $txt['theme1a'], ':</b> ', $context['member']['theme']['name'], ' <a href="', $scripturl, '?action=theme;sa=pick;u=', $context['member']['id'], ';sesc=', $context['session_id'], '">', $txt['theme1b'], '</a></td>
							</tr>';
	}

	// Are multiple smiley sets enabled?
	if (!empty($modSettings['smiley_sets_enable']))
	{
		echo '
							<tr>
								<td colspan="2" width="40%">
									<b>', $txt['smileys_current'], ':</b>
									<select name="smileySet" onchange="document.getElementById(\'smileypr\').src = this.selectedIndex == 0 ? \'', $settings['images_url'], '/blank.gif\' : \'', $modSettings['smileys_url'], '/\' + (this.selectedIndex != 1 ? this.options[this.selectedIndex].value : \'', !empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default'], '\') + \'/smiley.gif\';">';
		foreach ($context['smiley_sets'] as $set)
			echo '
										<option value="', $set['id'], '"', $set['selected'] ? ' selected="selected"' : '', '>', $set['name'], '</option>';
		echo '
									</select> <img id="smileypr" src="', $context['member']['smiley_set']['id'] != 'none' ? $modSettings['smileys_url'] . '/' . ($context['member']['smiley_set']['id'] != '' ? $context['member']['smiley_set']['id'] : (!empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default'])) . '/smiley.gif' : $settings['images_url'] . '/blank.gif', '" alt=":)" align="top" style="padding-left: 20px;" />
								</td>
							</tr>';
	}

	if ($modSettings['theme_allow'] || $context['user']['is_admin'] || !empty($modSettings['smiley_sets_enable']))
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

	// Allow the user to change the way the time is displayed.
	echo '
							<tr>
								<td width="40%">
									<b>', $txt[486], ':</b><br />
									<a href="', $scripturl, '?action=helpadmin;help=time_format" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="', !$context['right_to_left'] ? 'left' : 'right', '" style="', !$context['right_to_left'] ? 'padding-right' : 'padding-left', ': 1ex;" /></a>
									<span class="smalltext">', $txt[479], '</span>
								</td>
								<td>
									<select name="easyformat" onchange="document.forms.creator.timeFormat.value = this.options[this.selectedIndex].value;" style="margin-bottom: 4px;">';
	// Help the user by showing a list of common time formats.
	foreach ($context['easy_timeformats'] as $time_format)
		echo '
										<option value="', $time_format['format'], '"', $time_format['format'] == $context['member']['time_format'] ? ' selected="selected"' : '', '>', $time_format['title'], '</option>';
	echo '
									</select><br />
									<input type="text" name="timeFormat" value="', $context['member']['time_format'], '" size="30" />
								</td>
							</tr><tr>
								<td width="40%"><b', (isset($context['modify_error']['bad_offset']) ? ' style="color: red;"' : ''), '>', $txt[371], ':</b><div class="smalltext">', $txt[519], '</div></td>
								<td class="smalltext"><input type="text" name="timeOffset" size="5" maxlength="5" value="', $context['member']['time_offset'], '" /> <a href="javascript:void(0);" onclick="autoDetectTimeOffset(); return false;">', $txt['timeoffset_autodetect'], '</a><br />', $txt[741], ': <i>', $context['current_forum_time'], '</i></td>
							</tr><tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

	echo '
							<tr>
								<td colspan="2">
									<table width="100%" cellspacing="0" cellpadding="3">
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_board_desc]" value="0" />
												<label for="show_board_desc"><input type="checkbox" name="default_options[show_board_desc]" id="show_board_desc" value="1"', !empty($context['member']['options']['show_board_desc']) ? ' checked="checked"' : '', ' class="check" /> ', $txt[732], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_children]" value="0" />
												<label for="show_children"><input type="checkbox" name="default_options[show_children]" id="show_children" value="1"', !empty($context['member']['options']['show_children']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_children'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_avatars]" value="0" />
												<label for="show_no_avatars"><input type="checkbox" name="default_options[show_no_avatars]" id="show_no_avatars" value="1"', !empty($context['member']['options']['show_no_avatars']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_no_avatars'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_signatures]" value="0" />
												<label for="show_no_signatures"><input type="checkbox" name="default_options[show_no_signatures]" id="show_no_signatures" value="1"', !empty($context['member']['options']['show_no_signatures']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_no_signatures'], '</label>
											</td>
										</tr>';

	if ($settings['allow_no_censored'])
		echo '
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_censored]" value="0" />
												<label for="show_no_censored"><input type="checkbox" name="default_options[show_no_censored]" id="show_no_censored" value="1"' . (!empty($context['member']['options']['show_no_censored']) ? ' checked="checked"' : '') . ' class="check" /> ' . $txt['show_no_censored'] . '</label>
											</td>
										</tr>';

	echo '
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[return_to_post]" value="0" />
												<label for="return_to_post"><input type="checkbox" name="default_options[return_to_post]" id="return_to_post" value="1"', !empty($context['member']['options']['return_to_post']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['return_to_post'], '</label>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[no_new_reply_warning]" value="0" />
												<label for="no_new_reply_warning"><input type="checkbox" name="default_options[no_new_reply_warning]" id="no_new_reply_warning" value="1"', !empty($context['member']['options']['no_new_reply_warning']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['no_new_reply_warning'], '</label>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[view_newest_first]" value="0" />
												<label for="view_newest_first"><input type="checkbox" name="default_options[view_newest_first]" id="view_newest_first" value="1"', !empty($context['member']['options']['view_newest_first']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['recent_posts_at_top'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[view_newest_pm_first]" value="0" />
												<label for="view_newest_pm_first"><input type="checkbox" name="default_options[view_newest_pm_first]" id="view_newest_pm_first" value="1"', !empty($context['member']['options']['view_newest_pm_first']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['recent_pms_at_top'], '</label>
											</td>
										</tr><tr>
											<td colspan="2"><label for="calendar_start_day">', $txt['calendar_start_day'], ':</label>
												<select name="default_options[calendar_start_day]" id="calendar_start_day">
													<option value="0"', empty($context['member']['options']['calendar_start_day']) ? ' selected="selected"' : '', '>', $txt['days'][0], '</option>
													<option value="1"', !empty($context['member']['options']['calendar_start_day']) && $context['member']['options']['calendar_start_day'] == 1 ? ' selected="selected"' : '', '>', $txt['days'][1], '</option>
													<option value="6"', !empty($context['member']['options']['calendar_start_day']) && $context['member']['options']['calendar_start_day'] == 6 ? ' selected="selected"' : '', '>', $txt['days'][6], '</option>
												</select>
											</td>
										</tr><tr>
											<td colspan="2"><label for="display_quick_reply">', $txt['display_quick_reply'], '</label>
												<select name="default_options[display_quick_reply]" id="display_quick_reply">
													<option value="0"', empty($context['member']['options']['display_quick_reply']) ? ' selected="selected"' : '', '>', $txt['display_quick_reply1'], '</option>
													<option value="1"', !empty($context['member']['options']['display_quick_reply']) && $context['member']['options']['display_quick_reply'] == 1 ? ' selected="selected"' : '', '>', $txt['display_quick_reply2'], '</option>
													<option value="2"', !empty($context['member']['options']['display_quick_reply']) && $context['member']['options']['display_quick_reply'] == 2 ? ' selected="selected"' : '', '>', $txt['display_quick_reply3'], '</option>
												</select>
											</td>
										</tr><tr>
											<td colspan="2"><label for="display_quick_mod">', $txt['display_quick_mod'], '</label>
												<select name="default_options[display_quick_mod]" id="display_quick_mod">
													<option value="0"', empty($context['member']['options']['display_quick_mod']) ? ' selected="selected"' : '', '>', $txt['display_quick_mod_none'], '</option>
													<option value="1"', !empty($context['member']['options']['display_quick_mod']) && $context['member']['options']['display_quick_mod'] == 1 ? ' selected="selected"' : '', '>', $txt['display_quick_mod_check'], '</option>
													<option value="2"', !empty($context['member']['options']['display_quick_mod']) && $context['member']['options']['display_quick_mod'] != 1 ? ' selected="selected"' : '', '>', $txt['display_quick_mod_image'], '</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>';

	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
						</table>
					</td>
				</tr>
			</table>
		</form>';
}

function template_notification()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// The main containing header.
	echo '
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt[79], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $txt['notification_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" width="100%">
						<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">';

	// Allow notification on announcements to be disabled?
	if (!empty($modSettings['allow_disableAnnounce']))
		echo '
							<input type="hidden" name="notifyAnnouncements" value="0" />
							<label for="notifyAnnouncements"><input type="checkbox" id="notifyAnnouncements" name="notifyAnnouncements"', !empty($context['member']['notify_announcements']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['notifyXAnn4'], '</label><br />';

	// More notification options.
	echo '
							<input type="hidden" name="notifyOnce" value="0" />
							<label for="notifyOnce"><input type="checkbox" id="notifyOnce" name="notifyOnce"', !empty($context['member']['notify_once']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['notifyXOnce1'], '</label><br />

							<input type="hidden" name="default_options[auto_notify]" value="0" />
							<label for="auto_notify"><input type="checkbox" id="auto_notify" name="default_options[auto_notify]" value="1"', !empty($context['member']['options']['auto_notify']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['auto_notify'], '</label><br />';

	if (empty($modSettings['disallow_sendBody']))
		echo '
							<input type="hidden" name="notifySendBody" value="0" />
							<label for="notifySendBody"><input type="checkbox" id="notifySendBody" name="notifySendBody"', !empty($context['member']['notify_send_body']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['notify_send_body'], '</label><br />';

	echo '
							<br />
							<label for="notifyTypes">', $txt['notify_send_types'], ':</label>
							<select name="notifyTypes" id="notifyTypes">
								<option value="1"', $context['member']['notify_types'] == 1 ? ' selected="selected"' : '', '>', $txt['notify_send_type_everything'], '</option>
								<option value="2"', $context['member']['notify_types'] == 2 ? ' selected="selected"' : '', '>', $txt['notify_send_type_everything_own'], '</option>
								<option value="3"', $context['member']['notify_types'] == 3 ? ' selected="selected"' : '', '>', $txt['notify_send_type_only_replies'], '</option>
								<option value="4"', $context['member']['notify_types'] == 4 ? ' selected="selected"' : '', '>', $txt['notify_send_type_nothing'], '</option>
							</select><br />

							<div align="', !$context['right_to_left'] ? 'right' : 'left', '">
								<input type="submit" style="margin: 0 1ex 1ex 1ex;" value="', $txt['notifyX1'], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="userID" value="', $context['member']['id'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</div>
						</form>
					</td>
				</tr>
			</table>
			<br />
			<table border="0" width="85%" cellspacing="0" cellpadding="0" align="center" class="bordercolor"><tr><td>
				<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr><td class="catbg" width="100%">', $txt['notifications_topics'], '</td></tr>
					</table>
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr>
							<td class="windowbg" width="20" valign="middle" align="center" rowspan="', $context['num_rows']['topic'], '">
								<img src="', $settings['images_url'], '/icons/notify_sm.gif" width="20" height="20" alt="" />
							</td>';
	if (!empty($context['topic_notifications']))
	{
		echo '
							<td class="titlebg" width="71%">' . $txt[70] . '</td>
							<td class="titlebg" width="24%">' . $txt[109] . '</td>
							<td class="titlebg" width="5%"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></td>
						</tr>';
		foreach ($context['topic_notifications'] as $topic)
		{
			echo '
						<tr>
							<td class="windowbg" valign="middle" width="48%">
								', $topic['link'];

			if ($topic['new'])
				echo ' <a href="', $topic['new_href'], '"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="', $txt[302], '" /></a>';

			echo '<br />
								<span class="smalltext"><i>' . $txt['smf88'] . ' ' . $topic['board']['link'] . '</i></span>
							</td>
							<td class="windowbg2" valign="middle" width="14%">' . $topic['poster']['link'] . '</td>
							<td class="windowbg2" valign="middle" width="5%">
								<input type="checkbox" name="notify_topics[]" value="', $topic['id'], '" class="check" />
							</td>
						</tr>';
		}

		echo '
						<tr class="catbg">
							<td colspan="3">
								<b>', $txt[139], ':</b> ', $context['page_index'], '
							</td>
						</tr>
						<tr>
							<td colspan="3" class="windowbg2" align="right">
								<input type="submit" name="edit_notify_topics" value="', $txt['notifications_update'], '" />
							</td>
						</tr>';
	}
	else
		echo '
							<td width="100%" colspan="3" class="windowbg2">
								', $txt['notifications_topics_none'], '<br />
								<br />', $txt['notifications_topics_howto'], '<br />
								<br />
							</td>
						</tr>';
	echo '
					</table>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="userID" value="', $context['member']['id'], '" />
					<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
				</form>
			</td></tr></table><br />
			<table border="0" width="85%" cellspacing="0" cellpadding="0" align="center" class="bordercolor"><tr><td>
				<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr><td class="catbg" width="100%">', $txt['notifications_boards'], '</td></tr>
					</table>
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr>
							<td class="windowbg" width="20" valign="middle" align="center" rowspan="', $context['num_rows']['board'], '">
								<img src="', $settings['images_url'], '/icons/notify_sm.gif" width="20" height="20" alt="" />
							</td>';

	if (!empty($context['board_notifications']))
	{
		echo '
							<td class="titlebg" width="95%">' . $txt['smf82'] . '</td>
							<td class="titlebg" width="5%"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></td>
						</tr>';
		foreach ($context['board_notifications'] as $board)
		{
			echo '
						<tr>
							<td class="windowbg" valign="middle" width="48%">', $board['link'];

		if ($board['new'])
			echo ' <a href="', $board['href'], '"><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="', $txt[302], '" /></a>';

		echo '</td>
							<td class="windowbg2" valign="middle" width="5%">
								<input type="checkbox" name="notify_boards[]" value="', $board['id'], '" />
							</td>
						</tr>';
		}

		echo '
						<tr>
							<td colspan="2" class="windowbg2" align="right">
								<input type="submit" name="edit_notify_boards" value="', $txt['notifications_update'], '" />
							</td>
						</tr>';
	}
	else
		echo '
							<td width="100%" colspan="2" class="windowbg2">
								', $txt['notifications_boards_none'], '<br />
								<br />', $txt['notifications_boards_howto'], '<br />
								<br />
							</td>
						</tr>';
	echo '
					</table>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="userID" value="', $context['member']['id'], '" />
					<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
				</form>
			</td></tr></table><br />';
}

// Template for options related to personal messages.
function template_pmprefs()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt[79], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">
						', $txt['pmprefs_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table border="0" width="100%" cellpadding="3">';

	// A text box for the user to input usernames of everyone they want to ignore personal messages from.
	echo '
							<tr>
								<td valign="top">
									<b>', $txt[325], ':</b>
									<div class="smalltext">
										', $txt[326], '<br />
										<br />
										<a href="', $scripturl, '?action=findmember;input=pm_ignore_list;delim=\\\\n;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="middle" /> ', $txt['find_members'], '</a>
									</div>
								</td>
								<td>
									<textarea name="pm_ignore_list" id="pm_ignore_list" rows="10" cols="50">', $context['ignore_list'], '</textarea>
								</td>
							</tr>';

	// Extra options available to the user for personal messages.
	echo '
							<tr>
								<td colspan="2">
									<input type="hidden" name="default_options[copy_to_outbox]" value="0" />
									<label for="copy_to_outbox"><input type="checkbox" name="default_options[copy_to_outbox]" id="copy_to_outbox" value="1"', !empty($context['member']['options']['copy_to_outbox']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['copy_to_outbox'], '</label><br />
									<input type="hidden" name="default_options[popup_messages]" value="0" />
									<label for="popup_messages"><input type="checkbox" name="default_options[popup_messages]" id="popup_messages" value="1"', !empty($context['member']['options']['popup_messages']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['popup_messages'], '</label><br />
									<label for="pm_email_notify">', $txt[327], '</label>
									<select name="pm_email_notify" id="pm_email_notify">
										<option value="0"', empty($context['send_email']) ? ' selected="selected"' : '', '>', $txt['email_notify_never'], '</option>
										<option value="1"', !empty($context['send_email']) && ($context['send_email'] == 1 || (empty($modSettings['enable_buddylist']) && $context['send_email'] > 1)) ? ' selected="selected"' : '', '>', $txt['email_notify_always'], '</option>';

	if (!empty($modSettings['enable_buddylist']))
		echo '
										<option value="2"', !empty($context['send_email']) && $context['send_email'] > 1 ? ' selected="selected"' : '', '>', $txt['email_notify_buddies'], '</option>';

	echo '
									</select><br />
								</td>
							</tr>';

	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
						</table>
					</td>
				</tr>
			</table>
		</form>';
}

// Template to show for deleting a users account - now with added delete post capability!
function template_deleteAccount()
{
	global $context, $settings, $options, $scripturl, $txt, $scripturl;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['deleteAccount'], '
					</td>
				</tr>';
	// If deleting another account give them a lovely info box.
	if (!$context['user']['is_owner'])
	echo '
					<tr class="windowbg">
						<td class="smalltext" colspan="2" style="padding-top: 2ex; padding-bottom: 2ex;">
							', $txt['deleteAccount_desc'], '
						</td>
					</tr>';
	echo '
				<tr>
					<td class="windowbg2">
						<table width="100%" cellspacing="0" cellpadding="3"><tr>
							<td align="center" colspan="2">';

	// If they are deleting their account AND the admin needs to approve it - give them another piece of info ;)
	if ($context['needs_approval'])
		echo '
								<div style="color: red; border: 2px dashed red; padding: 4px;">', $txt['deleteAccount_approval'], '</div><br />
							</td>
						</tr><tr>
							<td align="center" colspan="2">';

	// If the user is deleting their own account warn them first - and require a password!
	if ($context['user']['is_owner'])
	{
		echo '
								<span style="color: red;">', $txt['own_profile_confirm'], '</span><br /><br />
							</td>
						</tr><tr>
							<td class="windowbg2" align="', !$context['right_to_left'] ? 'right' : 'left', '">
								<b', (isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' style="color: red;"' : ''), '>', $txt['smf241'], ': </b>
							</td>
							<td class="windowbg2" align="', !$context['right_to_left'] ? 'left' : 'right', '">
								<input type="password" name="oldpasswrd" size="20" />&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="submit" value="', $txt[163], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="userID" value="', $context['member']['id'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</td>';
	}
	// Otherwise an admin doesn't need to enter a password - but they still get a warning - plus the option to delete lovely posts!
	else
	{
		echo '
								<div style="color: red; margin-bottom: 2ex;">', $txt['deleteAccount_warning'], '</div>
							</td>
						</tr>';

		// Only actually give these options if they are kind of important.
		if ($context['can_delete_posts'])
			echo '
						<tr>
							<td colspan="2" align="center">
								', $txt['deleteAccount_posts'], ': <select name="remove_type">
									<option value="none">', $txt['deleteAccount_none'], '</option>
									<option value="posts">', $txt['deleteAccount_all_posts'], '</option>
									<option value="topics">', $txt['deleteAccount_topics'], '</option>
								</select>
							</td>
						</tr>';

		echo '
						<tr>
							<td colspan="2" align="center">
								<label for="deleteAccount"><input type="checkbox" name="deleteAccount" id="deleteAccount" value="1" class="check" onclick="if (this.checked) return confirm(\'', $txt['deleteAccount_confirm'], '\');" /> ', $txt['deleteAccount_member'], '.</label>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="windowbg2" align="center" style="padding-top: 2ex;">
								<input type="submit" value="', $txt['smf138'], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="userID" value="', $context['member']['id'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</td>';
	}
	echo '
						</tr></table>
					</td>
				</tr>
			</table>
		</form>';
}

// Template for the password box/save button stuck at the bottom of every profile page.
function template_profile_save()
{
	global $context, $settings, $options, $txt;

	echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>';

	// Only show the password box if it's actually needed.
	if ($context['user']['is_owner'] && $context['require_password'])
		echo '
								<td width="40%">
									<b', isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' style="color: red;"' : '', '>', $txt['smf241'], ': </b>
									<div class="smalltext">', $txt['smf244'], '</div>
								</td>
								<td>
									<input type="password" name="oldpasswrd" size="20" style="margin-right: 4ex;" />';
	else
		echo '
								<td align="right" colspan="2">';

	echo '
									<input type="submit" value="', $txt[88], '" />
									<input type="hidden" name="sc" value="', $context['session_id'], '" />
									<input type="hidden" name="userID" value="', $context['member']['id'], '" />
									<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
								</td>
							</tr>';
}

// Small template for showing an error message upon a save problem in the profile.
function template_error_message()
{
	global $context, $txt;

	echo '
		<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed red; color: red;">
			<span style="text-decoration: underline;">', $txt['profile_errors_occurred'], ':</span>
			<ul>';

		// Cycle through each error and display an error message.
		foreach ($context['post_errors'] as $error)
			//if (isset($txt['profile_error_' . $error]))
				echo '
				<li>', $txt['profile_error_' . $error], '.</li>';

		echo '
			</ul>
		</div>';
}

?>