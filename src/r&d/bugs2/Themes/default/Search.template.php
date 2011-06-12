<?php
// Version: 1.1.1; Search

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function selectBoards(ids)
		{
			var toggle = true;

			for (i = 0; i < ids.length; i++)
				toggle = toggle & document.forms.searchform["brd" + ids[i]].checked;

			for (i = 0; i < ids.length; i++)
				document.forms.searchform["brd" + ids[i]].checked = !toggle;
		}

		function expandCollapseBoards()
		{
			var current = document.getElementById("searchBoardsExpand").style.display != "none";

			document.getElementById("searchBoardsExpand").style.display = current ? "none" : "";
			document.getElementById("exandBoardsIcon").src = smf_images_url + (current ? "/expand.gif" : "/collapse.gif");
		}
	// ]]></script>
	<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '" name="searchform" id="searchform">
		<table cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td>', theme_linktree(), '</td>
			</tr>
		</table>

		<table border="0" cellspacing="0" cellpadding="6" align="center" class="tborder">
			<tr class="titlebg">
				<td>', !empty($settings['use_buttons']) ? '<img src="' . $settings['images_url'] . '/buttons/search.gif" align="right" style="margin-right: 4px;" alt="" />' : '', $txt[183], '</td>
			</tr>';

	if (!empty($context['search_errors']))
	{
		echo '
			<tr>
				<td class="windowbg">
					<div style="color: red; margin: 1ex 0 2ex 3ex;">
						', implode('<br />', $context['search_errors']['messages']), '
					</div>
				</td>
			</tr>';
	}

	echo '
			<tr>
				<td class="windowbg">';

	if ($context['simple_search'])
	{
		echo '
					<b>', $txt[582], ':</b><br />
					<table border="0" cellpadding="5" cellspacing="0"><tr>
						<td><input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" /></td>
						<td>&nbsp;<input type="submit" name="submit" value="', $txt[182], '" /></td>
					</tr>';
		if (empty($modSettings['search_simple_fulltext']))
			echo '
					<tr>
						<td align="right" class="smalltext">', $txt['search_example'], '</td>
						<td></td>
					</tr>';
		echo '
					</table><br /><br />
					<a href="', $scripturl, '?action=search;advanced" onclick="this.href += \';search=\' + escape(document.searchform.search.value);">', $txt['smf298'], '</a>
					<input type="hidden" name="advanced" value="0" />';
	}
	else
	{
		echo '
					<input type="hidden" name="advanced" value="1" />
					<table cellpadding="1" cellspacing="3" border="0">
						<tr>
							<td>
								<b>', $txt[582], ':</b>
							</td><td>
							</td><td>
									<b>', $txt[583], ':</b>
							</td>
						</tr><tr>
							<td>
								<input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" />
								<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
									if (typeof(window.addEventListener) == "undefined")
									{
										if (window.attachEvent)
										{
											window.addEventListener = function (sEvent, funcHandler, bCapture)
											{
												window.attachEvent("on" + sEvent, funcHandler);
											}
										}
										else
										{
											window.addEventListener = function (sEvent, funcHandler, bCapture) 
											{
												window["on" + sEvent] = funcHandler;
											}
										}
									}
									function initSearch()
									{
										if (document.forms.searchform.search.value.indexOf("%u") != -1)
											document.forms.searchform.search.value = unescape(document.forms.searchform.search.value);
									}
									window.addEventListener("load", initSearch, false);
								// ]]></script>
							</td><td style="padding-right: 2ex;">
								<select name="searchtype">
									<option value="1"', empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt[343], '</option>
									<option value="2"', !empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt[344], '</option>
								</select>
							</td><td>
								<input type="text" name="userspec" value="', empty($context['search_params']['userspec']) ? '*' : $context['search_params']['userspec'], '" size="40" />
							</td>
						</tr>';
		if (empty($modSettings['search_simple_fulltext']))
			echo '
						<tr>
							<td align="right" class="smalltext" style="padding: 0px;">', $txt['search_example'], '</td>
							<td colspan="2"></td>
						</tr>';
		echo '
						<tr>
							<td colspan="3"><br />
								<div style="text-align: left; width: 45%; float: right; margin-right: 2ex;">
									<div class="small_header" style="margin-bottom: 2px;"><b>', $txt['search_post_age'], ': </b></div><br />
									', $txt['search_between'], ' <input type="text" name="minage" value="', empty($context['search_params']['minage']) ? '0' : $context['search_params']['minage'], '" size="5" maxlength="5" />&nbsp;', $txt['search_and'], '&nbsp;<input type="text" name="maxage" value="', empty($context['search_params']['maxage']) ? '9999' : $context['search_params']['maxage'], '" size="5" maxlength="5" /> ', $txt[579], '.
								</div>
								<div style="width: 45%;">
									<div class="small_header" style="margin-bottom: 2px;"><b>', $txt['search_options'], ':</b></div>
									<label for="show_complete"><input type="checkbox" name="show_complete" id="show_complete" value="1"', !empty($context['search_params']['show_complete']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['search_show_complete_messages'], '</label><br />
									<label for="subject_only"><input type="checkbox" name="subject_only" id="subject_only" value="1"', !empty($context['search_params']['subject_only']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['search_subject_only'], '</label>
								</div>
							</td>
						</tr><tr>
							<td style="padding-top: 2ex;" colspan="2"><b>', $txt['search_order'], ':</b></td>
							<td></td>
						</tr><tr>
							<td colspan="2">
								<select name="sort">
									<option value="relevance|desc">', $txt['search_orderby_relevant_first'], '</option>
									<option value="numReplies|desc">', $txt['search_orderby_large_first'], '</option>
									<option value="numReplies|asc">', $txt['search_orderby_small_first'], '</option>
									<option value="ID_MSG|desc">', $txt['search_orderby_recent_first'], '</option>
									<option value="ID_MSG|asc">', $txt['search_orderby_old_first'], '</option>
								</select>
							</td>
							<td></td>
						</tr>
					</table><br />';

		// If $context['search_params']['topic'] is set, that means we're searching just one topic.
		if (!empty($context['search_params']['topic']))
			echo '
					', $txt['search_specific_topic'], ' &quot;', $context['search_topic']['link'], '&quot;.<br />
					<input type="hidden" name="topic" value="', $context['search_topic']['id'], '" />';
		else
		{
			echo '	
					<fieldset class="windowbg2" style="padding: 10px; margin-left: 5px; margin-right: 5px;">
						<a href="javascript:void(0);" onclick="expandCollapseBoards(); return false;"><img src="', $settings['images_url'], '/expand.gif" id="exandBoardsIcon" alt="" /></a> <a href="javascript:void(0);" onclick="expandCollapseBoards(); return false;"><b>', $txt[189], '</b></a><br />

						<table id="searchBoardsExpand" width="100%" border="0" cellpadding="1" cellspacing="0" align="center" style="margin-top: 1ex; display: none;">';

			$alternate = true;
			foreach ($context['board_columns'] as $board)
			{
				if ($alternate)
					echo '
							<tr>';
				echo '
								<td width="50%">';

				if (!empty($board) && empty($board['child_ids']))
					echo '
									<label for="brd', $board['id'], '" style="margin-left: ', $board['child_level'], 'ex;"><input type="checkbox" id="brd', $board['id'], '" name="brd[', $board['id'], ']" value="', $board['id'], '"', $board['selected'] ? ' checked="checked"' : '', ' class="check" />', $board['name'], '</label>';
				elseif (!empty($board))
					echo '
									<a href="javascript:void(0);" onclick="selectBoards([', implode(', ', $board['child_ids']), ']); return false;" style="text-decoration: underline;">', $board['name'], '</a>';

				echo '
								</td>';
				if (!$alternate)
					echo '
							</tr>';

				$alternate = !$alternate;
			}

			echo '
						</table><br />
						<input type="checkbox" name="all" id="check_all" value="" checked="checked" onclick="invertAll(this, this.form, \'brd\');" class="check" /><i> <label for="check_all">', $txt[737], '</label></i><br />
					</fieldset> ';
		}

		echo '
					<br />
					<div style="padding: 2px;"><input type="submit" name="submit" value="', $txt[182], '" /></div>';
	}

	echo '
				</td>
			</tr>
		</table>
	</form>';
}

function template_results()
{
	global $context, $settings, $options, $txt, $scripturl;

	if (isset($context['did_you_mean']) || empty($context['topics']))
	{
		echo '
	<div class="tborder">
		<table width="100%" cellpadding="4" cellspacing="0" border="0" class="bordercolor" style="margin-bottom: 2ex;">
			<tr class="titlebg">
				<td>', $txt['search_adjust_query'], '</td>
			</tr>
			<tr>
				<td class="windowbg">';

		// Did they make any typos or mistakes, perhaps?
		if (isset($context['did_you_mean']))
			echo '
					', $txt['search_did_you_mean'], ' <a href="', $scripturl, '?action=search2;params=', $context['did_you_mean_params'], '">', $context['did_you_mean'], '</a>.<br />';

		echo '
					<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
						<b>', $txt[582], ':</b><br />
						<input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" />
						<input type="submit" name="submit" value="', $txt['search_adjust_submit'], '" />

						<input type="hidden" name="searchtype" value="', !empty($context['search_params']['searchtype']) ? $context['search_params']['searchtype'] : 0, '" />
						<input type="hidden" name="userspec" value="', !empty($context['search_params']['userspec']) ? $context['search_params']['userspec'] : '', '" />
						<input type="hidden" name="show_complete" value="', !empty($context['search_params']['show_complete']) ? 1 : 0, '" />
						<input type="hidden" name="subject_only" value="', !empty($context['search_params']['subject_only']) ? 1 : 0, '" />
						<input type="hidden" name="minage" value="', !empty($context['search_params']['minage']) ? $context['search_params']['minage'] : '0', '" />
						<input type="hidden" name="maxage" value="', !empty($context['search_params']['maxage']) ? $context['search_params']['maxage'] : '9999', '" />
						<input type="hidden" name="sort" value="', !empty($context['search_params']['sort']) ? $context['search_params']['sort'] : 'relevance', '" />';

		if (!empty($context['search_params']['brd']))
			foreach ($context['search_params']['brd'] as $board_id)
				echo '
						<input type="hidden" name="brd[', $board_id, ']" value="', $board_id, '" />';

		echo '
					</form>
				</td>
			</tr>
		</table>
	</div>';
	}

	if ($context['compact'])
	{
		echo '
	<div style="padding: 3px;">', theme_linktree(), '</div>
	<div class="middletext">', $txt[139], ': ', $context['page_index'], '</div>';

		// Quick moderation set to checkboxes? Oh, how fun :/.
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1)
			echo '
	<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="', $context['character_set'], '" name="topicForm" style="margin: 0;">';

		echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">';
		if (!empty($context['topics']))
		{
			echo '
				<td width="4%"></td>
				<td width="4%"></td>
				<td width="56%">', $txt[70], '</td>
				<td width="6%" align="center">', $txt['search_relevance'], '</td>
				<td width="12%">', $txt[109], '</td>
				<td width="18%" align="center">', $txt['search_date_posted'], '</td>';

			if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<td width="24" valign="middle" align="center">
						<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="check" />
				</td>';
			elseif (!empty($options['display_quick_mod']))
				echo '
				<td width="4%" valign="middle" align="center"></td>';
		}
		else
			echo '
				<td width="100%" colspan="5">', $txt['search_no_results'], '</td>';
		echo '
			</tr>';

		while ($topic = $context['get_topics']())
		{
			// Work out what the class is if we remove sticky and lock info.
			if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'sticky') !== false)
				$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_sticky'));
			if (!empty($settings['seperate_sticky_lock']) && strpos($topic['class'], 'locked') !== false)
				$topic['class'] = substr($topic['class'], 0, strrpos($topic['class'], '_locked'));

			echo '
			<tr>
				<td class="windowbg2" valign="top" align="center" width="4%">
					<img src="', $settings['images_url'], '/topic/', $topic['class'], '.gif" alt="" /></td>
				<td class="windowbg2" valign="top" align="center" width="4%">
					<img src="', $topic['first_post']['icon_url'], '" alt="" align="middle" /></td>
				<td class="windowbg' , $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '3' : '' , '" valign="middle">
					' , $topic['is_locked'] && !empty($settings['seperate_sticky_lock']) ? '<img src="' . $settings['images_url'] . '/icons/quick_lock.gif" align="right" alt="" style="margin: 0;" />' : '' , '
					' , $topic['is_sticky'] && !empty($settings['seperate_sticky_lock']) ? '<img src="' . $settings['images_url'] . '/icons/show_sticky.gif" align="right" alt="" style="margin: 0;" /><b>' : '' , $topic['first_post']['link'] , $topic['is_sticky'] ? '</b>' : '' , '
				<div class="smalltext"><i>', $txt['smf88'], ' ', $topic['board']['link'], '</i></div>';

			foreach ($topic['matches'] as $message)
			{
				echo '<br />
					<div class="quoteheader" style="margin-left: 20px;"><a href="', $scripturl, '?topic=', $topic['id'], '.msg', $message['id'], '#msg', $message['id'], '">', $message['subject_highlighted'], '</a> ', $txt[525], ' ', $message['member']['link'], '</div>';

				if ($message['body_highlighted'] != '')
					echo '
					<div class="quote" style="margin-left: 20px;">', $message['body_highlighted'], '</div>';
			}

			echo '
				</td>
				<td class="windowbg2" valign="top" width="6%" align="center">
					', $topic['relevance'], '
				</td><td class="windowbg" valign="top" width="12%">
					', $topic['first_post']['member']['link'], '
				</td><td class="windowbg" valign="top" width="18%" align="center">
					', $topic['first_post']['time'], '
				</td>';

			if (!empty($options['display_quick_mod']))
			{
				echo '
				<td class="windowbg" valign="middle" align="center" width="4%">';
				if ($options['display_quick_mod'] == 1)
						echo '
					<input type="checkbox" name="topics[]" value="', $topic['id'], '" class="check" />';
				else
				{
					if ($topic['quick_mod']['remove'])
						echo '
					<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=remove;sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><img src="', $settings['images_url'], '/icons/quick_remove.gif" width="16" alt="', $txt[63], '" title="', $txt[63], '" /></a>';
					if ($topic['quick_mod']['lock'])
						echo '
					<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=lock;sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><img src="', $settings['images_url'], '/icons/quick_lock.gif" width="16" alt="', $txt['smf279'], '" title="', $txt['smf279'], '" /></a>';
					if ($topic['quick_mod']['lock'] || $topic['quick_mod']['remove'])
						echo '<br />';
					if ($topic['quick_mod']['sticky'])
						echo '
					<a href="', $scripturl, '?action=quickmod;actions[', $topic['id'], ']=sticky;sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><img src="', $settings['images_url'], '/icons/quick_sticky.gif" width="16" alt="', $txt['smf277'], '" title="', $txt['smf277'], '" /></a>';
					if ($topic['quick_mod']['move'])
						echo '
					<a href="', $scripturl, '?action=movetopic;topic=', $topic['id'], '.0"><img src="', $settings['images_url'], '/icons/quick_move.gif" width="16" alt="', $txt[132], '" title="', $txt[132], '" /></a>';
				}
				echo '
				</td>';
			}

			echo '
			</tr>';
		}

		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
		{
			echo '
			<tr class="titlebg">
				<td colspan="8" align="right">
					<select name="qaction"', $context['can_move'] ? ' onchange="this.form.moveItTo.disabled = (this.options[this.selectedIndex].value != \'move\');"' : '', '>
						<option value="">--------</option>', $context['can_remove'] ? '
						<option value="remove">' . $txt['quick_mod_remove'] . '</option>' : '', $context['can_lock'] ? '
						<option value="lock">' . $txt['quick_mod_lock'] . '</option>' : '', $context['can_sticky'] ? '
						<option value="sticky">' . $txt['quick_mod_sticky'] . '</option>' : '',	$context['can_move'] ? '
						<option value="move">' . $txt['quick_mod_move'] . ': </option>' : '', $context['can_merge'] ? '
						<option value="merge">' . $txt['quick_mod_merge'] . '</option>' : '', '
						<option value="markread">', $txt['quick_mod_markread'], '</option>
					</select>';

			if ($context['can_move'])
			{
				echo '
					<select id="moveItTo" name="move_to" disabled="disabled">';
				foreach ($context['jump_to'] as $category)
					foreach ($category['boards'] as $board)
					{
						if (!$board['is_current'])
							echo '
						<option value="', $board['id'], '"', !empty($board['selected']) ? ' selected="selected"' : '', '>', str_repeat('-', $board['child_level'] + 1), ' ', $board['name'], '</option>';
					}
				echo '
					</select>';
			}

			echo '
					<input type="hidden" name="redirect_url" value="', $scripturl . '?action=search2;params=' . $context['params'], '" />
					<input type="submit" value="', $txt['quick_mod_go'], '" onclick="return this.form.qaction.value != \'\' &amp;&amp; confirm(\'', $txt['quickmod_confirm'], '\');" />
				</td>
			</tr>';
		}

		echo '
		</table>';

		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
			echo '
			<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
		</form>';

		echo '
		<div class="middletext">', $txt[139], ': ', $context['page_index'], '</div>';

		if ($settings['linktree_inline'])
			echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';
		echo '
		<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="smalltext" align="right" valign="middle">
					<form action="', $scripturl, '" method="get" accept-charset="', $context['character_set'], '">
						<label for="jumpto">', $txt[160], ':</label>
						<select name="jumpto" id="jumpto" onchange="if (this.selectedIndex > 0 &amp;&amp; this.options[this.selectedIndex].value) window.location.href = smf_scripturl + this.options[this.selectedIndex].value.substr(smf_scripturl.indexOf(\'?\') == -1 || this.options[this.selectedIndex].value.substr(0, 1) != \'?\' ? 0 : 1);">
							<option value="">', $txt[251], ':</option>';
		foreach ($context['jump_to'] as $category)
		{
			echo '
							<option value="" disabled="disabled">-----------------------------</option>
							<option value="#', $category['id'], '">', $category['name'], '</option>
							<option value="" disabled="disabled">-----------------------------</option>';
			foreach ($category['boards'] as $board)
				echo '
							<option value="?board=', $board['id'], '.0"> ', str_repeat('==', $board['child_level']), '=> ', $board['name'], '</option>';
		}
		echo '
						</select>
						&nbsp;
						<input type="button" value="', $txt[161], '" onclick="if (this.form.jumpto.options[this.form.jumpto.selectedIndex].value) window.location.href = \'', $scripturl, '\' + this.form.jumpto.options[this.form.jumpto.selectedIndex].value;" />
					</form>
				</td>
			</tr>
		</table>';
	}
	else
	{
		echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>
		<div class="middletext">', $txt[139], ': ', $context['page_index'], '</div>';

		if (empty($context['topics']))
			echo '
		<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
			<table border="0" width="100%" cellpadding="2" cellspacing="1" class="bordercolor"><tr class="windowbg2"><td><br />
				<b>(', $txt['search_no_results'], ')</b><br /><br />
			</td></tr></table>
		</td></tr></table>';

		while ($topic = $context['get_topics']())
		{
			foreach ($topic['matches'] as $message)
			{
				// Create buttons row.
				$quote_button = create_button('quote.gif', 145, 145, 'align="middle"');
				$reply_button = create_button('reply_sm.gif', 146, 146, 'align="middle"');
				$notify_button = create_button('notify_sm.gif', 131, 131, 'align="middle"');

				$buttonArray = array();
				if ($topic['can_reply'])
				{
					$buttonArray[] = '<a href="' . $scripturl . '?action=post;topic=' . $topic['id'] . '.' . $message['start'] . '">' . $reply_button . '</a>';
					$buttonArray[] = '<a href="' . $scripturl . '?action=post;topic=' . $topic['id'] . '.0;quote=' . $message['id'] . '/' . $message['start'] . ';sesc=' . $context['session_id'] . '">' . $quote_button . '</a>';
				}
				if ($topic['can_mark_notify'])
					$buttonArray[] = '<a href="' . $scripturl . '?action=notify;topic=' . $topic['id'] . '.' . $message['start'] . '">' . $notify_button . '</a>';

				echo '
			<div class="tborder">
				<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
					<tr>
						<td>
							<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
								<tr class="titlebg">
									<td>
										<div style="float: left; width: 3ex;">&nbsp;', $message['counter'], '&nbsp;</div>
										<div style="float: left;">&nbsp;', $topic['category']['link'], ' / ', $topic['board']['link'], ' / <a href="', $scripturl, '?topic=', $topic['id'], '.', $message['start'], ';topicseen#msg', $message['id'], '">', $message['subject_highlighted'], '</a></div>
										<div align="right">', $txt[30], ': ', $message['time'], '&nbsp;</div>
									</td>
								</tr><tr class="catbg">
									<td>
										<div style="float: left;">', $txt[109], ' ', $topic['first_post']['member']['link'], ', ', $txt[72], ' ', $txt[525], ' ', $message['member']['link'], '</div>
										<div align="right">', $txt['search_relevance'], ': ', $topic['relevance'], '</div>
									</td>
								</tr><tr>
									<td width="100%" valign="top" class="windowbg2">
										<div class="post">', $message['body_highlighted'], '</div>
									</td>
								</tr><tr class="windowbg">
									<td class="middletext" align="right">&nbsp;', implode($context['menu_separator'], $buttonArray), '</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>';
			}
		}

		echo '
			<div class="middletext">', $txt[139], ': ', $context['page_index'], '</div>';

		if ($settings['linktree_inline'])
			echo '
			<div style="padding: 3px;">', theme_linktree(), '</div>';
	}
}

?>