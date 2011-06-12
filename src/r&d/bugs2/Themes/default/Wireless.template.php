<?php
// Version: 1.1; Wireless

// This is the header for WAP 1.1 output. You can view it with ?wap in the URL.
function template_wap_above()
{
	global $context, $settings, $options;

	// Show the xml declaration...
	echo '<?xml version="1.0"?', '>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>';
}

// This is the board index (main page) in WAP 1.1.
function template_wap_boardindex()
{
	global $context, $settings, $options, $scripturl;

	// This is the "main" card...
	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['forum_name'], '</b><br /></p>';

	// Show an anchor for each category.
	foreach ($context['categories'] as $category)
	{
		// Skip it if it's empty.
		if (!empty($category['boards']))
			echo '
		<p><a href="#c', $category['id'], '">', $category['name'], '</a><br /></p>';
	}

	// Okay, that's it for the main card.
	echo '
	</card>';

	// Now fill out the deck of cards with the boards in each category.
	foreach ($context['categories'] as $category)
	{
		// Begin the card, and make the name available.
		echo '
	<card id="c', $category['id'], '" title="', strip_tags($category['name']), '">
		<p><b>', strip_tags($category['name']), '</b><br /></p>';

		// Now show a link for each board.
		foreach ($category['boards'] as $board)
			echo '
		<p><a href="', $scripturl, '?board=', $board['id'], '.0;wap">', $board['name'], '</a><br /></p>';

		echo '
	</card>';
	}
}

// This is the message index (list of topics in a board) for WAP 1.1.
function template_wap_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['name'], '</b></p>
		<p>', $txt[139], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /></p>';

	if (isset($context['boards']) && count($context['boards']) > 0)
	{
		foreach ($context['boards'] as $board)
			echo '
		<p>- <a href="', $scripturl, '?board=', $board['id'], '.0;wap">', $board['name'], '</a><br /></p>';
		echo '
		<p><br /></p>';
	}

	if (!empty($context['topics']))
		foreach ($context['topics'] as $topic)
			echo '
		<p><a href="', $scripturl, '?topic=', $topic['id'], '.0;wap">', $topic['first_post']['subject'], '</a> - ', $topic['first_post']['member']['name'], '<br /></p>';

	echo '
		<p>', $txt[139], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '</p>
	</card>';
}

function template_wap_display()
{
	global $context, $settings, $options, $txt;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['subject'], '</b></p>
		<p>', $txt[139], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /><br /></p>';

	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<div class="quote">', '<div class="code">', '</div>'), array('', '', '<br />--<br />'), $message['body']), '<br>');

		echo '
		<p><u>', $message['member']['name'], '</u>:<br /></p>
		<p>', $wireless_message, '<br /><br /></p>';
	}

	echo '
		<p>', $txt[139], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '</p>
	</card>';
}

function template_wap_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="login" title="', $context['page_title'], '">';

	if (isset($context['login_error']))
		echo '
		<p><b>', $context['login_error'], '</b></p>';

	echo '
		<p>', $txt[35], ':<br />
		<input type="text" name="user" /></p>

		<p>', $txt[36], ':<br />
		<input type="password" name="passwrd" /></p>

		<p><do type="accept" label="', $txt[34], '">
			<go method="post" href="', $scripturl, '?action=login2;wap">
				<postfield name="user" value="$user" />
				<postfield name="passwrd" value="$passwrd" />
				<postfield name="cookieneverexp" value="1" />
			</go>
		</do></p>
	</card>';
}

function template_wap_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="recent" title="', $context['page_title'], '">
		<p><b>', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</b></p>';

	if (empty($context['topics']))
		echo '
		<p>', $txt[334], '</p>';
	else
	{
		echo '
			<p>', $txt[139], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /><br /></p>';
		foreach ($context['topics'] as $topic)
		{
			echo '
			<p><a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;imode#new">', $topic['first_post']['subject'], '</a></p>';
		}
	}

	echo '
	</card>';
}

function template_wap_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['error_title'], '</b></p>
		<p>', $context['error_message'], '</p>
		<p><a href="', $scripturl, '?wap">', $txt['wireless_error_home'], '</a></p>
	</card>';
}

function template_wap_below()
{
	global $context, $settings, $options;

	echo '
</wml>';
}

// The cHTML protocol used for i-mode starts here.
function template_imode_above()
{
	global $context, $settings, $options;

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD Compact HTML 1.0 Draft//EN">
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $context['page_title'], '</title>
	</head>
	<body>';
}

function template_imode_boardindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['forum_name'], '</font></td></tr>';
	$count = 0;
	foreach ($context['categories'] as $category)
	{
		if (!empty($category['boards']) || $category['is_collapsed'])
			echo '
			<tr bgcolor="#b6dbff"><td>', $category['can_collapse'] ? '<a href="' . $scripturl . '?action=collapse;c=' . $category['id'] . ';sa=' . ($category['is_collapsed'] ? 'expand' : 'collapse') . ';imode">' : '', $category['name'], $category['can_collapse'] ? '</a>' : '', '</td></tr>';

		foreach ($category['boards'] as $board)
		{
			$count++;
			echo '
			<tr><td>', $board['new'] ? '<font color="#ff0000">' : '', $count < 10 ? '&#' . (59105 + $count) . ';' : '<b>-</b>', $board['new'] ? '</font>' : ($board['children_new'] ? '<font color="#ff0000">.</font>' : ''), ' <a href="', $scripturl, '?board=', $board['id'], '.0;imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $board['name'], '</a></td></tr>';
		}
	}
	echo '
			<tr bgcolor="#6d92aa"><td>', $txt['wireless_options'], '</td></tr>';
	if ($context['user']['is_guest'])
		echo '
			<tr><td><a href="', $scripturl, '?action=login;imode">', $txt['wireless_options_login'], '</a></td></tr>';
	else
	{
		if ($context['allow_pm'])
			echo '
			<tr><td><a href="', $scripturl, '?action=pm;imode">', empty($context['user']['unread_messages']) ? $txt['wireless_pm_inbox'] : sprintf($txt['wireless_pm_inbox_new'], $context['user']['unread_messages']), '</a></td></tr>';
		echo '
			<tr><td><a href="', $scripturl, '?action=unread;imode">', $txt['wireless_recent_unread_posts'], '</a></td></tr>
			<tr><td><a href="', $scripturl, '?action=unreadreplies;imode">', $txt['wireless_recent_unread_replies'], '</a></td></tr>
			<tr><td><a href="', $scripturl, '?action=logout;sesc=', $context['session_id'], ';imode">', $txt['wireless_options_logout'], '</a></td></tr>';
	}
	echo '
		</table>';
}

function template_imode_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['name'], '</font></td></tr>';

	if (!empty($context['boards']))
	{
		echo '
		<tr bgcolor="#b6dbff"><td>', $txt['parent_boards'], '</td></tr>';
		foreach ($context['boards'] as $board)
			echo '
		<tr><td>', $board['new'] ? '<font color="#ff0000">-</font> ' : ($board['children_new'] ? '-<font color="#ff0000">.</font>' : '- '), '<a href="', $scripturl, '?board=', $board['id'], '.0;imode">', $board['name'], '</a></td></tr>';
	}

	$count = 0;
	if (!empty($context['topics']))
	{
		echo '
			<tr bgcolor="#b6dbff"><td>', $txt[64], '</td></tr>
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
			<tr><td>', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.0;imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a>', $topic['new'] && $context['user']['is_logged'] ? ' [<a href="' . $scripturl . '?topic=' . $topic['id'] . '.msg' . $topic['new_from'] . ';imode#new">' . $txt[302] . '</a>]' : '', '</td></tr>';
		}
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>&#59115; <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td>&#59104; <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td><b>[*]</b> <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', $context['can_post_new'] ? '
			<tr><td><a href="' . $scripturl . '?action=post;board=' . $context['current_board'] . '.0;imode">' . $txt[33] . '</a></td></tr>' : '', '
		</table>';
}

function template_imode_display()
{
	global $context, $settings, $options, $scripturl, $board, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['subject'], '</font></td></tr>
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<div class="quote">', '<div class="code">', '</div>'), '<br />', $message['body']), '<br>');

		echo '
			<tr><td>', $message['first_new'] ? '
				<a name="new"></a>' : '', '
				<b>', $message['member']['name'], '</b>:<br />
				', $wireless_message, '
			</td></tr>';
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>&#59115; <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_index'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td><a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td><a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', $context['can_reply'] ? '
			<tr><td><a href="' . $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';imode">' . $txt[146] . '</a></td></tr>' : '', '
		</table>';
}

function template_imode_post()
{
	global $context, $settings, $options, $scripturl, $txt;

	// !!! $modSettings['guest_post_no_email']
	echo '
		<form action="', $scripturl, '?action=', $context['destination'], ';board=', $context['current_board'], '.0;imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">', $context['locked'] ? '
				<tr><td>' . $txt['smf287'] . '</td></tr>' : '', isset($context['name']) ? '
				<tr><td>' . (isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) ? '<font color="#cc0000">' . $txt[35] . '</font>' : $txt[35]) . ':</td></tr>
				<tr><td><input type="text" name="guestname" value="' . $context['name'] . '" /></td></tr>' : '', isset($context['email']) ? '
				<tr><td>' . (isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? '<font color="#cc0000">' . $txt[69] . '</font>' : $txt[69]) . ':</td></tr>
				<tr><td><input type="text" name="email" value="' . $context['email'] . '" /></td></tr>' : '', '
				<tr><td>', isset($context['post_error']['no_subject']) ? '<font color="#FF0000">' . $txt[70] . '</font>' : $txt[70], ':</td></tr>
				<tr><td><input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' maxlength="80" /></td></tr>
				<tr><td>', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? '<font color="#ff0000">' . $txt[72] . '</font>' : $txt[72], ':</td></tr>
				<tr><td><textarea name="message" rows="3" cols="20">', $context['message'], '</textarea></td></tr>
				<tr><td>
					<input type="submit" name="post" value="', $context['submit_label'], '" />
					<input type="hidden" name="icon" value="wireless" />
					<input type="hidden" name="goback" value="', $context['back_to_topic'] || !empty($options['return_to_post']) ? '1' : '0', '" />
					<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />', isset($context['current_topic']) ? '
					<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '', '
					<input type="hidden" name="notify" value="', $context['notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
				</td></tr>
				<tr><td>
					&#59115; ', isset($context['current_topic']) ? '<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.new;imode">' . $txt['wireless_navigation_topic'] . '</a>' : '<a href="' . $scripturl . '?board=' . $context['current_board'] . '.0;imode" accesskey="0">' . $txt['wireless_navigation_index'] . '</a>', '
				</td></tr>
			</table>
		</form>';
}

function template_imode_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=login2;imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr bgcolor="#b6dbff"><td>', $txt[34], '</td></tr>';
	if (isset($context['login_error']))
		echo '
				<tr><td><b><font color="#ff00000">', $context['login_error'], '</b></td></tr>';
	echo '
				<tr><td>', $txt[35], ':</td></tr>
				<tr><td><input type="text" name="user" size="10" /></td></tr>
				<tr><td>', $txt[36], ':</td></tr>
				<tr><td><input type="password" name="passwrd" size="10" /></td></tr>
				<tr><td><input type="submit" value="', $txt[34], '" /><input type="hidden" name="cookieneverexp" value="1" /></td></tr>
				<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
				<tr><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>
			</table>
		</form>';
}

function template_imode_pm()
{
	global $context, $settings, $options, $scripturl, $txt, $user_info;

	if ($_REQUEST['action'] == 'findmember')
	{
		echo '
		<form action="', $scripturl, '?action=findmember;sesc=', $context['session_id'], ';imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['wireless_pm_search_member'], '</font></td></tr>
				<tr bgcolor="#b6dbff"><td>', $txt['find_members'], '</td></tr>
				<tr><td>
					<b>', $txt['wireless_pm_search_name'], ':</b>
					<input type="text" name="search" value="', isset($context['last_search']) ? $context['last_search'] : '', '" />', empty($_REQUEST['u']) ? '' : '
					<input type="hidden" name="u" value="' . $_REQUEST['u'] . '" />', '
				</td></tr>
				<tr><td><input type="submit" value="', $txt[182], '" /></td></tr>';
		if (!empty($context['last_search']))
		{
			echo '
				<tr bgcolor="#b6dbff"><td>', $txt['find_results'], '</td></tr>';
			if (empty($context['results']))
				echo '
				<tr bgcolor="#b6dbff"><td>[-] ', $txt['find_no_results'], '</tr></td>';
			else
			{
				echo '
				<tr bgcolor="#b6dbff"><td>', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ', '</tr></td>';
				$count = 0;
				foreach ($context['results'] as $result)
				{
					$count++;
					echo '
				<tr bgcolor="#b6dbff"><td>
					', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?action=pm;sa=send;u=', empty($_REQUEST['u']) ? $result['id'] : $_REQUEST['u'] . ',' . $result['id'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $result['name'], '</a>
				</tr></td>';
				}
			}
		}
		echo '
				<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
				<tr><td>[0] <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>';
		if (!empty($context['results']))
			echo empty($context['links']['next']) ? '' : '
				<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></tr></td>', empty($context['links']['prev']) ? '' : '
				<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></tr></td>';
		echo '
			</table>
		</form>';
	}
	elseif (!empty($_GET['sa']))
	{
		echo '
				<table border="0" cellspacing="0" cellpadding="0">';
		if ($_GET['sa'] == 'addbuddy')
		{
			echo '
					<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['wireless_pm_add_buddy'], '</font></td></tr>
					<tr bgcolor="#b6dbff"><td>', $txt['wireless_pm_select_buddy'], '</td></tr>';
			$count = 0;
			foreach ($context['buddies'] as $buddy)
			{
				$count++;
				if ($buddy['selected'])
					echo '
					<tr><td>[-] <span style="color: gray">', $buddy['name'], '</span></tr></td>';
				else
					echo '
					<tr><td>
						', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $buddy['add_href'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $buddy['name'], '</a>
					</tr></td>';
			}
			echo '
					<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
					<tr><td>[0] <a href="', $context['pm_href'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>
				</table>';
		}
		if ($_GET['sa'] == 'send' || $_GET['sa'] == 'send2')
		{
			echo '
				<form action="', $scripturl, '?action=pm;sa=send2;imode" method="post">
					<table border="0" cellspacing="0" cellpadding="0">
						<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt[321], '</tr></td>', empty($context['post_error']['messages']) ? '' : '
						<tr><td><font color="#ff0000">' . implode('<br />', $context['post_error']['messages']) . '</font></tr></td>', '
						<tr><td>
							<b>', $txt[150], ':</b> ', empty($context['to']) ? $txt['wireless_pm_no_recipients'] : $context['to'], empty($_REQUEST['u']) ? '' : '<input type="hidden" name="u" value="' . implode(',', $_REQUEST['u']) . '" />', '<br />
							<a href="', $scripturl, '?action=findmember', empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u']), ';sesc=', $context['session_id'], ';imode">', $txt['wireless_pm_search_member'], '</a>', empty($user_info['buddies']) ? '' : '<br />
							<a href="' . $scripturl . '?action=pm;sa=addbuddy' . (empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u'])) . ';imode">' . $txt['wireless_pm_add_buddy'] . '</a>', '
						</tr></td>
						<tr><td>
							<b>', $txt[70], ':</b> <input type="text" name="subject" value="', $context['subject'], '" />
						</tr></td>
						<tr><td>
							<b>', $txt[72], ':</b><br />
							<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
						</tr></td>
						<tr><td>
							<input type="submit" value="', $txt[148], '" />
							<input type="hidden" name="outbox" value="', $context['copy_to_outbox'] ? '1' : '0', '" />
							<input type="hidden" name="sc" value="', $context['session_id'], '" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
							<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
							<input type="hidden" name="folder" value="', $context['folder'], '" />
						</tr></td>';
			if ($context['reply'])
				echo '
						<tr bgcolor="#b6dbff"><td>', $txt['wireless_pm_reply_to'], '</tr></td>
						<tr><td><b>', $context['quoted_message']['subject'], '</b></tr></td>
						<tr><td>', $context['quoted_message']['body'], '</tr></td>';
			echo '
						<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
						<tr><td>[0] <a href="', $scripturl, '?action=pm;imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>
					</table>
				</form>';
		}
	}
	elseif (empty($_GET['pmsg']))
	{
		echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['wireless_pm_inbox'], '</tr></td>
			<tr><td>', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ', '</tr></td>';
		$count = 0;
		while ($message = $context['get_pmessage']())
		{
			$count++;
			echo '
			<tr><td>
				', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?action=pm;start=', $context['start'], ';pmsg=', $message['id'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $message['subject'], ' <i>', $txt['wireless_pm_by'], '</i> ', $message['member']['name'], '</a>
			</tr></td>';
		}
		echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
			<tr><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>', empty($context['links']['next']) ? '' : '
			<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></tr></td>', empty($context['links']['prev']) ? '' : '
			<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></tr></td>', $context['can_send_pm'] ? '
			<tr><td><a href="' . $scripturl . '?action=pm;sa=send;imode">' . $txt[321] . '</a></tr></td>' : '', '
		</table>';
	}
	else
	{
		$message = $context['get_pmessage']();
		$message['body'] = strtr(strip_tags(strtr($message['body'], array('</div>' => '[br]', '<div class="quoteheader">' => '[br]'))), array('[br]' => '<br />'));
		echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $message['subject'], '</tr></td>
			<tr bgcolor="#b6dbff"><td>
				<b>', $txt['wireless_pm_by'], ':</b> ', $message['member']['name'], '<br />
				<b>', $txt[30], ':</b> ', $message['time'], '
			</tr></td>
			<tr><td>
				', $message['body'], '
			</tr></td>
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
			<tr><td>[0] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>';
			if ($context['can_send_pm'])
				echo '
			<tr><td><a href="', $scripturl, '?action=pm;sa=send;pmsg=', $message['id'], ';u=', $message['member']['id'], ';reply;imode">', $txt['wireless_pm_reply'], '</a></tr></td>
		</table>';
	}
}

function template_imode_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</tr></td>';

	$count = 0;
	if (empty($context['topics']))
		echo '
			<tr><td>', $txt[334], '</td></tr>';
	else
	{
		echo '
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
			<tr><td>', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;imode#new"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a></td></tr>';
		}
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>[0] <a href="', $context['links']['up'], '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', '
		</table>';
}

function template_imode_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['error_title'], '</font></td></tr>
			<tr><td>', $context['error_message'], '</td></tr>
			<tr class="windowbg"><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_error_home'], '</a></td></tr>
		</table>';
}

function template_imode_below()
{
	global $context, $settings, $options;

	echo '
	</body>
</html>';
}

// XHTMLMP (XHTML Mobile Profile) templates used for WAP 2.0 start here
function template_wap2_above()
{
	global $context, $settings, $options;

	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" href="', $settings['default_theme_url'], '/wireless.css" type="text/css" />
	</head>
	<body>';
}

function template_wap2_boardindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $context['forum_name'], '</p>';

	$count = 0;
	foreach ($context['categories'] as $category)
	{
		if (!empty($category['boards']) || $category['is_collapsed'])
			echo '
		<p class="titlebg">', $category['can_collapse'] ? '<a href="' . $scripturl . '?action=collapse;c=' . $category['id'] . ';sa=' . ($category['is_collapsed'] ? 'expand' : 'collapse') . ';wap2">' : '', $category['name'], $category['can_collapse'] ? '</a>' : '', '</p>';

		foreach ($category['boards'] as $board)
		{
			$count++;
			echo '
		<p class="windowbg">', $board['new'] ? '<span class="updated">' : '', $count < 10 ? '[' . $count . '' : '[-', $board['children_new'] ? '<span class="updated">' : '', '] ', $board['new'] || $board['children_new'] ? '</span>' : '', '<a href="', $scripturl, '?board=', $board['id'], '.0;wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $board['name'], '</a></p>';
		}
	}

	echo '
		<p class="titlebg">', $txt['wireless_options'], '</p>';
	if ($context['user']['is_guest'])
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=login;wap2">', $txt['wireless_options_login'], '</a></p>';
	else
	{
		if ($context['allow_pm'])
			echo '
			<p class="windowbg"><a href="', $scripturl, '?action=pm;wap2">', empty($context['user']['unread_messages']) ? $txt['wireless_pm_inbox'] : sprintf($txt['wireless_pm_inbox_new'], $context['user']['unread_messages']), '</a></p>';
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=unread;wap2">', $txt['wireless_recent_unread_posts'], '</a></p>
		<p class="windowbg"><a href="', $scripturl, '?action=unreadreplies;wap2">', $txt['wireless_recent_unread_replies'], '</a></p>
		<p class="windowbg"><a href="', $scripturl, '?action=logout;sesc=', $context['session_id'], ';wap2">', $txt['wireless_options_logout'], '</a></p>';
	}
}

function template_wap2_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $context['name'], '</p>';

	if (!empty($context['boards']))
	{
		echo '
		<p class="titlebg">', $txt['parent_boards'], '</p>';
		foreach ($context['boards'] as $board)
			echo '
		<p class="windowbg">', $board['new'] ? '<span class="updated">[-] </span>' : ($board['children_new'] ? '[-<span class="updated">] </span>' : '[-] '), '<a href="', $scripturl, '?board=', $board['id'], '.0;wap2">', $board['name'], '</a></p>';
	}

	$count = 0;
	if (!empty($context['topics']))
	{
		echo '
		<p class="titlebg">', $txt[64], '</p>
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
		<p class="windowbg">', $count < 10 ? '[' . $count . '] ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.0;wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a>', $topic['new'] && $context['user']['is_logged'] ? ' [<a href="' . $scripturl . '?topic=' . $topic['id'] . '.msg' . $topic['new_from'] . ';wap2#new" class="new">' . $txt[302] . '</a>]' : '', '</p>';
		}
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '', $context['can_post_new'] ? '
		<p class="windowbg"><a href="' . $scripturl . '?action=post;board=' . $context['current_board'] . '.0;wap2">' . $txt[33] . '</a></p>' : '';
}

function template_wap2_display()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $context['subject'], '</p>
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
	$alternate = true;
	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<div class="quote">', '<div class="code">', '</div>'), '<br />', $message['body']), '<br>');

		echo $message['first_new'] ? '
		<a name="new"></a>' : '', '
		<p class="windowbg', $alternate ? '' : '2', '">
			<b>', $message['member']['name'], '</b>:<br />
			', $wireless_message, '
		</p>';
		$alternate = !$alternate;
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_index'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '', $context['can_reply'] ? '
		<p class="windowbg"><a href="' . $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';wap2">' . $txt[146] . '</a></p>' : '';
}

function template_wap2_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=login2;wap2" method="post">
			<p class="catbg">', $txt[34], '</p>';
	if (isset($context['login_error']))
		echo '
			<p class="windowbg" style="color: #ff0000;"><b>', $context['login_error'], '</b></p>';
	echo '
			<p class="windowbg">', $txt[35], ':</p>
			<p class="windowbg"><input type="text" name="user" size="10" /></p>
			<p class="windowbg">', $txt[36], ':</p>
			<p class="windowbg"><input type="password" name="passwrd" size="10" /></p>
			<p class="windowbg"><input type="submit" value="', $txt[34], '" /><input type="hidden" name="cookieneverexp" value="1" /></p>
			<p class="catbg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>
		</form>';
}

function template_wap2_post()
{
	global $context, $settings, $options, $scripturl, $txt;

	// !!! $modSettings['guest_post_no_email']
	echo '
		<form action="', $scripturl, '?action=', $context['destination'], ';board=', $context['current_board'], '.0;wap2" method="post">
			<p class="titlebg">', $context['page_title'], '</p>', $context['locked'] ? '
			<p class="windowbg">
				' . $txt['smf287'] . '
			</p>' : '', isset($context['name']) ? '
			<p class="windowbg"' . (isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) ? ' style="color: #ff0000"' : '') . '>
				' . $txt[35] . ': <input type="text" name="guestname" value="' . $context['name'] . '" />
			</p>' : '', isset($context['email']) ? '
			<p class="windowbg"' . (isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? ' style="color: #ff0000"' : '') . '>
				' . $txt[69] . ': <input type="text" name="email" value="' . $context['email'] . '" />
			</p>' : '', '
			<p class="windowbg"', isset($context['post_error']['no_subject']) ? ' style="color: #ff0000"' : '', '>
				', $txt[70], ': <input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' maxlength="80" />
			</p>
			<p class="windowbg"', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? ' style="color: #ff0000;"' : '', '>
				', $txt[72], ': <br />
				<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
			</p>
			<p class="windowbg">
				<input type="submit" name="post" value="', $context['submit_label'], '" />
				<input type="hidden" name="icon" value="wireless" />
				<input type="hidden" name="goback" value="', $context['back_to_topic'] || !empty($options['return_to_post']) ? '1' : '0', '" />
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />', isset($context['current_topic']) ? '
				<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '', '
				<input type="hidden" name="notify" value="', $context['notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
			</p>
			<p class="windowbg">[0] ', isset($context['current_topic']) ? '<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.new;wap2">' . $txt['wireless_navigation_topic'] . '</a>' : '<a href="' . $scripturl . '?board=' . $context['current_board'] . '.0;wap2" accesskey="0">' . $txt['wireless_navigation_index'] . '</a>', '</p>
		</form>';
}

function template_wap2_pm()
{
	global $context, $settings, $options, $scripturl, $txt, $user_info;

	if ($_REQUEST['action'] == 'findmember')
	{
		echo '
				<form action="', $scripturl, '?action=findmember;sesc=', $context['session_id'], ';wap2" method="post">
					<p class="catbg">', $txt['wireless_pm_search_member'], '</p>
					<p class="titlebg">', $txt['find_members'], '</p>
					<p class="windowbg">
						<b>', $txt['wireless_pm_search_name'], ':</b>
						<input type="text" name="search" value="', isset($context['last_search']) ? $context['last_search'] : '', '" />', empty($_REQUEST['u']) ? '' : '
						<input type="hidden" name="u" value="' . $_REQUEST['u'] . '" />', '
					</p>
					<p class="windowbg"><input type="submit" value="', $txt[182], '" /></p>
				</form>';
		if (!empty($context['last_search']))
		{
			echo '
				<p class="titlebg">', $txt['find_results'], '</p>';
			if (empty($context['results']))
				echo '
				<p class="windowbg">[-] ', $txt['find_no_results'], '</p>';
			else
			{
				echo '
				<p class="windowbg">', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ', '</p>';
				$count = 0;
				foreach ($context['results'] as $result)
				{
					$count++;
					echo '
				<p class="windowbg">
					[', $count < 10 ? $count : '-', '] <a href="', $scripturl, '?action=pm;sa=send;u=', empty($_REQUEST['u']) ? $result['id'] : $_REQUEST['u'] . ',' . $result['id'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $result['name'], '</a>
				</p>';
				}
			}
		}
		echo '
				<p class="titlebg">', $txt['wireless_navigation'], '</p>
				<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
		if (!empty($context['results']))
			echo empty($context['links']['next']) ? '' : '
			<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>', empty($context['links']['prev']) ? '' : '
			<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>';
	}
	elseif (!empty($_GET['sa']))
	{
		if ($_GET['sa'] == 'addbuddy')
		{
			echo '
					<p class="catbg">', $txt['wireless_pm_add_buddy'], '</p>
					<p class="titlebg">', $txt['wireless_pm_select_buddy'], '</p>';
			$count = 0;
			foreach ($context['buddies'] as $buddy)
			{
				$count++;
				if ($buddy['selected'])
					echo '
					<p class="windowbg">[-] <span style="color: gray">', $buddy['name'], '</span></p>';
				else
					echo '
					<p class="windowbg">
						[', $count < 10 ? $count : '-', '] <a href="', $buddy['add_href'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $buddy['name'], '</a>
					</p>';
			}
			echo '
					<p class="titlebg">', $txt['wireless_navigation'], '</p>
					<p class="windowbg">[0] <a href="', $context['pm_href'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
		}
		if ($_GET['sa'] == 'send' || $_GET['sa'] == 'send2')
		{
			echo '
				<form action="', $scripturl, '?action=pm;sa=send2;wap2" method="post">
					<p class="catbg">', $txt[321], '</p>', empty($context['post_error']['messages']) ? '' : '
					<p class="windowbg" style="color: red;">' . implode('<br />', $context['post_error']['messages']) . '</p>', '
					<p class="windowbg">
						<b>', $txt[150], ':</b> ', empty($context['to']) ? $txt['wireless_pm_no_recipients'] : $context['to'], empty($_REQUEST['u']) ? '' : '<input type="hidden" name="u" value="' . implode(',', $_REQUEST['u']) . '" />', '<br />
						<a href="', $scripturl, '?action=findmember', empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u']), ';sesc=', $context['session_id'], ';wap2">', $txt['wireless_pm_search_member'], '</a>', empty($user_info['buddies']) ? '' : '<br />
						<a href="' . $scripturl . '?action=pm;sa=addbuddy' . (empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u'])) . ';wap2">' . $txt['wireless_pm_add_buddy'] . '</a>', '
					</p>
					<p class="windowbg">
						<b>', $txt[70], ':</b> <input type="text" name="subject" value="', $context['subject'], '" />
					</p>
					<p class="windowbg">
						<b>', $txt[72], ':</b><br />
						<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
					</p>
					<p class="windowbg">
						<input type="submit" value="', $txt[148], '" />
						<input type="hidden" name="outbox" value="', $context['copy_to_outbox'] ? '1' : '0', '" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
						<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
						<input type="hidden" name="folder" value="', $context['folder'], '" />
					</p>';
			if ($context['reply'])
				echo '
					<p class="titlebg">', $txt['wireless_pm_reply_to'], '</p>
					<p class="windowbg"><b>', $context['quoted_message']['subject'], '</b></p>
					<p class="windowbg">', $context['quoted_message']['body'], '</p>';
			echo '
					<p class="titlebg">', $txt['wireless_navigation'], '</p>
					<p class="windowbg">[0] <a href="', $scripturl, '?action=pm;wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>
				</form>';
		}
	}
	elseif (empty($_GET['pmsg']))
	{
		echo '
			<p class="catbg">', $txt['wireless_pm_inbox'], '</p>
			<p class="windowbg">', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ', '</p>';
		$count = 0;
		while ($message = $context['get_pmessage']())
		{
			$count++;
			echo '
			<p class="windowbg">
				[', $count < 10 ? $count : '-', '] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';pmsg=', $message['id'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $message['subject'], ' <i>', $txt['wireless_pm_by'], '</i> ', $message['member']['name'], '</a>
			</p>';
		}
		echo '
			<p class="titlebg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', empty($context['links']['next']) ? '' : '
			<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>', empty($context['links']['prev']) ? '' : '
			<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>', $context['can_send_pm'] ? '
			<p class="windowbg"><a href="' . $scripturl . '?action=pm;sa=send;wap2">' . $txt[321] . '</a></p>' : '';
	}
	else
	{
		$message = $context['get_pmessage']();
		$message['body'] = strtr(strip_tags(strtr($message['body'], array('</div>' => '[br]', '<div class="quoteheader">' => '[br]'))), array('[br]' => '<br />'));
		echo '
			<p class="catbg">', $message['subject'], '</p>
			<p class="titlebg">
				<b>', $txt['wireless_pm_by'], ':</b> ', $message['member']['name'], '<br />
				<b>', $txt[30], ':</b> ', $message['time'], '
			</p>
			<p class="windowbg">
				', $message['body'], '
			</p>
			<p class="titlebg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
			if ($context['can_send_pm'])
				echo '
			<p class="windowbg"><a href="', $scripturl, '?action=pm;sa=send;pmsg=', $message['id'], ';u=', $message['member']['id'], ';reply;wap2">', $txt['wireless_pm_reply'], '</a></p>';
	}
}

function template_wap2_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</p>';

	$count = 0;
	if (empty($context['topics']))
		echo '
			<p class="windowbg">', $txt[334], '</p>';
	else
	{
		echo '
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
		<p class="windowbg">', ($count < 10 ? '[' . $count . '] ' : ''), '<a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;wap2#new"', ($count < 10 ? ' accesskey="' . $count . '"' : ''), '>', $topic['first_post']['subject'], '</a></p>';
		}
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '';
}

function template_wap2_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<p class="catbg">', $context['error_title'], '</p>
		<p class="windowbg">', $context['error_message'], '</p>
		<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_error_home'], '</a></p>';
}

function template_wap2_below()
{
	global $context, $settings, $options;

	echo '
	</body>
</html>';
}

?>