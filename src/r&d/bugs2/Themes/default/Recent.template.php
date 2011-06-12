<?php
// Version: 1.1.9; Recent

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>
		<div class="middletext" style="margin-bottom: 1ex;">', $txt[139], ': ', $context['page_index'], '</div>';

	foreach ($context['posts'] as $post)
	{
		// This is far from ideal, but oh well - create buttons for the post.
		$button_set = array();

		if ($post['can_delete'])
			$button_set['delete'] = array('text' => 31, 'image' => 'delete.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[154] . '?\');"', 'url' => $scripturl . '?action=deletemsg;msg=' . $post['id'] . ';topic=' . $post['topic'] . ';recent;sesc=' . $context['session_id']);
		if ($post['can_reply'])
		{
			$button_set['reply'] = array('text' => 146, 'image' => 'reply_sm.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $post['topic'] . '.' . $post['start']);
			$button_set['quote'] = array('text' => 145, 'image' => 'quote.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $post['topic'] . '.' . $post['start'] . ';quote=' . $post['id'] . ';sesc=' . $context['session_id']);
		}
		if ($post['can_mark_notify'])
			$button_set['notify'] = array('text' => 131, 'image' => 'notify_sm.gif', 'lang' => true, 'url' => $scripturl . '?action=notify;topic=' . $post['topic'] . '.' . $post['start']);

		echo '
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
				<tr class="titlebg2">
						<td class="middletext">
								<div style="float: left; width: 3ex;">&nbsp;', $post['counter'], '&nbsp;</div>
								<div style="float: left;">&nbsp;', $post['category']['link'], ' / ', $post['board']['link'], ' / <b>', $post['link'], '</b></div>
								<div align="right">&nbsp;', $txt[30], ': ', $post['time'], '&nbsp;</div>
						</td>
				</tr>
				<tr>
						<td class="catbg" colspan="3">
							<span class="middletext"> ', $txt[109], ' ' . $post['first_poster']['link'] . ' - ' . $txt[22] . ' ' . $txt[525] . ' ' . $post['poster']['link'] . ' </span>
						</td>
				</tr>
				<tr>
						<td class="windowbg2" colspan="3" valign="top" height="80">
								<div class="post">' . $post['message'] . '</div>
						</td>
				</tr>';

		// Are we using tabs?
		if (!empty($settings['use_tabs']))
		{
			echo '
			</table>';

			if (!empty($button_set))
			echo '
			<table cellpadding="0" cellspacing="0" align="right" style="margin-right: 2ex;">
				<tr>
					', template_button_strip($button_set, 'top', true), '
				</tr>
			</table><br />';
		}
		else
		{
			if (!empty($button_set))
				echo '
				<tr>
					<td class="catbg" colspan="3" align="right">
						<table><tr>
						', template_button_strip($button_set, 'top', true), '
						</tr></table>
					</td>
				</tr>';

			echo '
			</table>';
		}

		echo '
			<br />';
	}
	echo '
			<div class="middletext">', $txt[139], ': ', $context['page_index'], '</div>';
}

function template_unread()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td>', theme_linktree(), '</td>
		</tr>
	</table>

	<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="middletext" valign="middle">' . $txt[139] . ': ' . $context['page_index'] . '</td>';

	if ($settings['show_mark_read'])
	{
		// Generate the button strip.
		$mark_read = array(
			'markread' => array('text' => !empty($context['no_board_limits']) ? 452 : 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';sesc=' . $context['session_id']),
		);

		echo '
			<td align="right" style="padding-right: 1ex;">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						', template_button_strip($mark_read, 'bottom'), '
					</tr>
				</table>
			</td>';
	}
	echo '
		</tr>
	</table>

	<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
		<tr><td>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr class="titlebg">';
	if (!empty($context['topics']))
		echo '
					<td width="10%" colspan="2">&nbsp;</td>
					<td>
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[70], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a>
					</td><td width="14%">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[109], $context['sort_by'] == 'starter' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a>
					</td><td width="4%" align="center">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[110], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a>
					</td><td width="4%" align="center">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[301], $context['sort_by'] == 'views' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a>
					</td><td width="24%">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[111], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a>
					</td>';
	else
		echo '
					<td width="100%" colspan="7">', $context['showing_all_topics'] ? $txt[151] : $txt['unread_topics_visit_none'], '</td>';
	echo '
				</tr>';

	foreach ($context['topics'] as $topic)
	{
		// Do we want to seperate the sticky and lock status out?
		if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'sticky') !== false)
			$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_sticky'));
		if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'locked') !== false)
			$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_locked'));

		echo '
				<tr>
					<td class="windowbg2" valign="middle" align="center" width="6%">
						<img src="' . $settings['images_url'] . '/topic/' . $topic['class'] . '.gif" alt="" />
					</td><td class="windowbg2" valign="middle" align="center" width="4%">
						<img src="' . $topic['first_post']['icon_url'] . '" alt="" align="middle" />
					</td><td class="windowbg' , $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '3' : '' , '" width="48%" valign="middle">' , $topic['is_locked'] && !empty($settings['seperate_sticky_lock']) ? '
						<img src="' . $settings['images_url'] . '/icons/quick_lock.gif" align="right" alt="" style="margin: 0;" />' : '' , $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '
						<img src="' . $settings['images_url'] . '/icons/show_sticky.gif" align="right" alt="" style="margin: 0;" />' : '', $topic['first_post']['link'], ' <a href="', $topic['new_href'], '"><img src="', $settings['images_url'], '/', $context['user']['language'], '/new.gif" alt="', $txt[302], '" /></a> <span class="smalltext">', $topic['pages'], ' ', $txt['smf88'], ' ', $topic['board']['link'], '</span></td>
					<td class="windowbg2" valign="middle" width="14%">
						', $topic['first_post']['member']['link'], '</td>
					<td class="windowbg" valign="middle" width="4%" align="center">
						', $topic['replies'], '</td>
					<td class="windowbg" valign="middle" width="4%" align="center">
						', $topic['views'], '</td>
					<td class="windowbg2" valign="middle" width="22%">
						<a href="', $topic['last_post']['href'], '"><img src="', $settings['images_url'], '/icons/last_post.gif" alt="', $txt[111], '" title="', $txt[111], '" style="float: right;" /></a>
						<span class="smalltext">
							', $topic['last_post']['time'], '<br />
							', $txt[525], ' ', $topic['last_post']['member']['link'], '
						</span>
					</td>
				</tr>';
	}

	if (!empty($context['topics']) && !$context['showing_all_topics'])
			echo '
				<tr class="titlebg">
					<td colspan="7" align="right" class="middletext"><a href="', $scripturl, '?action=unread;all', $context['querystring_board_limits'], '">', $txt['unread_topics_all'], '</a></td>
				</tr>';

		echo '
			</table>
		</td></tr>
	</table>

	<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="middletext" valign="middle">', $txt[139], ': ', $context['page_index'], '</td>';

	if ($settings['show_mark_read'])
		echo '
			<td align="right" style="padding-right: 1ex;">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						', template_button_strip($mark_read, 'top'), '
					</tr>
				</table>
			</td>';
		echo '
		</tr>
	</table><br />
	<div class="tborder"><div class="titlebg2">
		<table cellpadding="8" cellspacing="0" width="55%">
			<tr>
				<td align="left" style="padding-top: 2ex;" class="smalltext">', !empty($modSettings['enableParticipation']) ? '
					<img src="' . $settings['images_url'] . '/topic/my_normal_post.gif" alt="" align="middle" /> ' . $txt['participation_caption'] . '<br />' : '', '
					<img src="' . $settings['images_url'] . '/topic/normal_post.gif" alt="" align="middle" /> ' . $txt[457] . '<br />
					<img src="' . $settings['images_url'] . '/topic/hot_post.gif" alt="" align="middle" /> ' . $txt[454] . '<br />
					<img src="' . $settings['images_url'] . '/topic/veryhot_post.gif" alt="" align="middle" /> ' . $txt[455] . '
				</td>
				<td align="left" valign="top" style="padding-top: 2ex;" class="smalltext">
					<img src="' . $settings['images_url'] . '/icons/quick_lock.gif" alt="" align="middle" /> ' . $txt[456] . '<br />' . ($modSettings['enableStickyTopics'] == '1' ? '
					<img src="' . $settings['images_url'] . '/icons/' . (!empty($settings['seperate_sticky_lock']) ? 'quick_sticky' : 'normal_post_sticky') . '.gif" alt="" align="middle" /> ' . $txt['smf96'] . '<br />' : '') . ($modSettings['pollMode'] == '1' ? '
					<img src="' . $settings['images_url'] . '/topic/normal_poll.gif" alt="" align="middle" /> ' . $txt['smf43'] : '') . '
				</td>
			</tr>
		</table>
	</div></div>';
}

function template_replies()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<table width="100%" border="0" cellspacing="0" cellpadding="3" align="center">
		<tr>
			<td>', theme_linktree(), '</td>
		</tr>
	</table>

	<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="middletext" valign="middle">' . $txt[139] . ': ' . $context['page_index'] . '</td>';
	if (isset($context['topics_to_mark']) && !empty($settings['show_mark_read']))
	{
		$mark_read = array(
			'markread' => array('text' => 452, 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=unreadreplies;topics=' . $context['topics_to_mark'] . ';sesc=' . $context['session_id']),
		);
		echo '
			<td align="right" style="padding-right: 1ex;">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						', template_button_strip($mark_read, 'bottom'), '
					</tr>
				</table>
			</td>';
	}
	echo '
		</tr>
	</table>

	<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
		<tr><td>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr class="titlebg">';
	if (!empty($context['topics']))
			echo '
					<td width="10%" colspan="2">&nbsp;</td>
					<td><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[70], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
					<td width="14%"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[109], $context['sort_by'] == 'starter' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
					<td width="4%" align="center"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[110], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
					<td width="4%" align="center"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[301], $context['sort_by'] == 'views' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
					<td width="24%"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt[111], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>';
	else
		echo '
					<td width="100%" colspan="7">' . $txt[151] . '</td>';
	echo '
				</tr>';

	foreach ($context['topics'] as $topic)
	{
		// Seperate lock and sticky again?
		if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'sticky') !== false)
			$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_sticky'));
		if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'locked') !== false)
			$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_locked'));

		echo '
				<tr>
					<td class="windowbg2" valign="middle" align="center" width="6%">
						<img src="', $settings['images_url'], '/topic/', $topic['class'], '.gif" alt="" /></td>
					<td class="windowbg2" valign="middle" align="center" width="4%">
						<img src="', $topic['first_post']['icon_url'], '" alt="" align="middle" /></td>
					<td class="windowbg', $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '3' : '' , '" width="48%" valign="middle">
						' , $topic['is_locked'] && !empty($settings['seperate_sticky_lock']) ? '<img src="' . $settings['images_url'] . '/icons/quick_lock.gif" align="right" alt="" style="margin: 0;" />' : '' , '
						' , $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '<img src="' . $settings['images_url'] . '/icons/show_sticky.gif" align="right" alt="" style="margin: 0;" />' : '', ' ', $topic['first_post']['link'], ' <a href="', $topic['new_href'], '"><img src="', $settings['images_url'], '/', $context['user']['language'], '/new.gif" alt="', $txt[302], '" /></a> <span class="smalltext">', $topic['pages'], '
						', $txt['smf88'], ' ', $topic['board']['link'], '</span></td>
					<td class="windowbg2" valign="middle" width="14%">
						', $topic['first_post']['member']['link'], '</td>
					<td class="windowbg" valign="middle" width="4%" align="center">
						', $topic['replies'], '</td>
					<td class="windowbg" valign="middle" width="4%" align="center">
						', $topic['views'], '</td>
					<td class="windowbg2" valign="middle" width="22%">
						<a href="', $topic['last_post']['href'], '"><img src="', $settings['images_url'], '/icons/last_post.gif" alt="', $txt[111], '" title="', $txt[111], '" style="float: right;" /></a>
						<span class="smalltext">
								', $topic['last_post']['time'], '<br />
								', $txt[525], ' ', $topic['last_post']['member']['link'], '
						</span>
					</td>
				</tr>';
	}

	echo '
			</table>
		</td></tr>
	</table>

	<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="middletext" valign="middle">' . $txt[139] . ': ' . $context['page_index'] . '</td>';
	if (isset($context['topics_to_mark']) && !empty($settings['show_mark_read']))
		echo '
			<td align="right" style="padding-right: 1ex;">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						', template_button_strip($mark_read, 'top'), '
					</tr>
				</table>
			</td>';
	echo '
		</tr>
	</table><br />

	<div class="tborder"><div class="titlebg2">
		<table cellpadding="8" cellspacing="0" width="55%">
			<tr>
				<td align="left" style="padding-top: 2ex;" class="smalltext">', !empty($modSettings['enableParticipation']) ? '
					<img src="' . $settings['images_url'] . '/topic/my_normal_post.gif" alt="" align="middle" /> ' . $txt['participation_caption'] . '<br />' : '', '
					<img src="' . $settings['images_url'] . '/topic/normal_post.gif" alt="" align="middle" /> ' . $txt[457] . '<br />
					<img src="' . $settings['images_url'] . '/topic/hot_post.gif" alt="" align="middle" /> ' . $txt[454] . '<br />
					<img src="' . $settings['images_url'] . '/topic/veryhot_post.gif" alt="" align="middle" /> ' . $txt[455] . '
				</td>
				<td align="left" valign="top" style="padding-top: 2ex;" class="smalltext">
					<img src="' . $settings['images_url'] . '/icons/quick_lock.gif" alt="" align="middle" /> ' . $txt[456] . '<br />' . ($modSettings['enableStickyTopics'] == '1' ? '
					<img src="' . $settings['images_url'] . '/icons/' . (!empty($settings['seperate_sticky_lock']) ? 'quick_sticky' : 'normal_post_sticky') . '.gif" alt="" align="middle" /> ' . $txt['smf96'] . '<br />' : '') . ($modSettings['pollMode'] == '1' ? '
					<img src="' . $settings['images_url'] . '/topic/normal_poll.gif" alt="" align="middle" /> ' . $txt['smf43'] : '') . '
				</td>
			</tr>
		</table>
	</div></div>';
}

?>