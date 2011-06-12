<?php
// Version: 1.1; PersonalMessage

// This is the main sidebar for the personal messages section.
function template_pm_above()
{
	global $context, $settings, $options, $txt;

	echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	echo '
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
				<td width="125" valign="top">
					<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" width="100">';
	// Loop through every main area - giving a nice section heading.
	foreach ($context['pm_areas'] as $section)
	{
		echo '
						<tr>
							<td class="catbg">', $section['title'], '</td>
						</tr>
						<tr class="windowbg2">
							<td class="smalltext" style="padding-bottom: 2ex;">';
		// Each sub area.
		foreach ($section['areas'] as $i => $area)
		{
			if (empty($area))
				echo '<br />';
			// Special case for the capacity bar.
			elseif (!empty($area['limit_bar']))
			{
				// !!! Hardcoded colors = bad.
				echo '
								<br /><br />
								<div align="center">
									<b>', $txt['pm_capacity'], '</b>
									<div align="left" style="border: 1px solid black; height: 7px; width: 100px;">
										<div style="border: 0; background-color: ', $context['limit_bar']['percent'] > 85 ? '#A53D05' : ($context['limit_bar']['percent'] > 40 ? '#EEA800' : '#468008'), '; height: 7px; width: ', $context['limit_bar']['bar'], 'px;"></div>
									</div>
									<span', ($context['limit_bar']['percent'] > 90 ? ' style="color: red;"' : ''), '>', $context['limit_bar']['text'], '</span>
								</div>
								<br />';
			}
			else
			{
				if ($i == $context['pm_area'])
					echo '
								<b>', $area['link'], (empty($area['unread_messages']) ? '' : ' (<b>' . $area['unread_messages'] . '</b>)'), '</b><br />';
				else
					echo '
								', $area['link'], (empty($area['unread_messages']) ? '' : ' (<b>' . $area['unread_messages'] . '</b>)'), '<br />';
			}
		}
		echo '
							</td>
						</tr>';
	}
	echo '
					</table>
					<br />
				</td>
				<td valign="top">';
}

// Just the end of the index bar, nothing special.
function template_pm_below()
{
	global $context, $settings, $options;

	echo '
				</td>
			</tr></table>';
}

function template_folder()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
<form action="', $scripturl, '?action=pm;sa=pmactions;f=', $context['folder'], ';start=', $context['start'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '" method="post" accept-charset="', $context['character_set'], '" name="pmFolder">
	<table border="0" width="100%" cellpadding="2" cellspacing="1" class="bordercolor">
		<tr class="titlebg">
			<td align="center" width="2%">&nbsp;</td>
			<td style="width: 32ex;"><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=date', $context['sort_by'] == 'date' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', $txt[317], $context['sort_by'] == 'date' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td width="46%"><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', $txt[319], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=name', $context['sort_by'] == 'name' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', ($context['from_or_to'] == 'from' ? $txt[318] : $txt[324]), $context['sort_by'] == 'name' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td align="center" width="24"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></td>
		</tr>';
	if (!$context['show_delete'])
		echo '
		<tr>
			<td class="windowbg" colspan="5">', $txt[151], '</td>
		</tr>';
	$next_alternate = false;
	while ($message = $context['get_pmessage']())
	{
		echo '
		<tr class="', $message['alternate'] == 0 ? 'windowbg' : 'windowbg2', '">
			<td align="center" width="2%">', $message['is_replied_to'] ? '<img src="' . $settings['images_url'] . '/icons/pm_replied.gif" style="margin-right: 4px;" alt="' . $txt['pm_replied'] . '" />' : '<img src="' . $settings['images_url'] . '/icons/pm_read.gif" style="margin-right: 4px;" alt="' . $txt['pm_read'] . '" />', '</td>
			<td>', $message['time'], '</td>
			<td><a href="#msg', $message['id'], '">', $message['subject'], '</a></td>
			<td>', ($context['from_or_to'] == 'from' ? $message['member']['link'] : (empty($message['recipients']['to']) ? '' : implode(', ', $message['recipients']['to']))), '</td>
			<td align="center"><input type="checkbox" name="pms[]" id="deletelisting', $message['id'], '" value="', $message['id'], '"', $message['is_selected'] ? ' checked="checked"' : '', ' onclick="document.getElementById(\'deletedisplay', $message['id'], '\').checked = this.checked;" class="check" /></td>
		</tr>';
		$next_alternate = $message['alternate'];
	}

	echo '
	</table>
	<div class="bordercolor" style="padding: 1px; ', $context['browser']['needs_size_fix'] && !$context['browser']['is_ie6'] ? 'width: 100%;' : '', '">
		<table width="100%" cellpadding="2" cellspacing="0" border="0"><tr class="catbg" valign="middle">
			<td>
				<div style="float: left;">', $txt[139], ': ', $context['page_index'], '</div>
				<div style="float: right;">&nbsp;';

	if ($context['show_delete'])
	{
		if (!empty($context['currently_using_labels']) && $context['folder'] != 'outbox')
		{
			echo '
				<select name="pm_action" onchange="if (this.options[this.selectedIndex].value) this.form.submit();" onfocus="loadLabelChoices();">
					<option value="">', $txt['pm_sel_label_title'], ':</option>
					<option value="" disabled="disabled">---------------</option>';

			echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_apply'], ':</option>';
			foreach ($context['labels'] as $label)
				if ($label['id'] != $context['current_label_id'])
					echo '
					<option value="add_', $label['id'], '">&nbsp;', $label['name'], '</option>';
			echo '
					<option value="" disabled="disabled">', $txt['pm_msg_label_remove'], ':</option>';
			foreach ($context['labels'] as $label)
				echo '
					<option value="rem_', $label['id'], '">&nbsp;', $label['name'], '</option>';
			echo '
				</select>
				<noscript>
					<input type="submit" value="', $txt['pm_apply'], '" />
				</noscript>';
		}

		echo '
				<input type="submit" name="del_selected" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="if (!confirm(\'', $txt['smf249'], '\')) return false;" />';
	}

	echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					var allLabels = {};
					function loadLabelChoices()
					{
						var listing = document.forms.pmFolder.elements;
						var theSelect = document.forms.pmFolder.pm_action;
						var add, remove, toAdd = {length: 0}, toRemove = {length: 0};

						if (theSelect.childNodes.length == 0)
							return;';

	// This is done this way for internationalization reasons.
	echo '
						if (typeof(allLabels[-1]) == "undefined")
						{
							for (var o = 0; o < theSelect.options.length; o++)
								if (theSelect.options[o].value.substr(0, 4) == "rem_")
									allLabels[theSelect.options[o].value.substr(4)] = theSelect.options[o].text;
						}

						for (var i = 0; i < listing.length; i++)
						{
							if (listing[i].name != "pms[]" || !listing[i].checked)
								continue;

							var alreadyThere = [], x;
							for (x in currentLabels[listing[i].value])
							{
								if (typeof(toRemove[x]) == "undefined")
								{
									toRemove[x] = allLabels[x];
									toRemove.length++;
								}
								alreadyThere[x] = allLabels[x];
							}

							for (x in allLabels)
							{
								if (typeof(alreadyThere[x]) == "undefined")
								{
									toAdd[x] = allLabels[x];
									toAdd.length++;
								}
							}
						}

						while (theSelect.options.length > 2)
							theSelect.options[2] = null;

						if (toAdd.length != 0)
						{
							theSelect.options[theSelect.options.length] = new Option("', $txt['pm_msg_label_apply'], '", "");
							setInnerHTML(theSelect.options[theSelect.options.length - 1], "', $txt['pm_msg_label_apply'], '");
							theSelect.options[theSelect.options.length - 1].disabled = true;

							for (i in toAdd)
							{
								if (i != "length")
									theSelect.options[theSelect.options.length] = new Option(toAdd[i], "add_" + i);
							}
						}

						if (toRemove.length != 0)
						{
							theSelect.options[theSelect.options.length] = new Option("', $txt['pm_msg_label_remove'], '", "");
							setInnerHTML(theSelect.options[theSelect.options.length - 1], "', $txt['pm_msg_label_remove'], '");
							theSelect.options[theSelect.options.length - 1].disabled = true;

							for (i in toRemove)
							{
								if (i != "length")
									theSelect.options[theSelect.options.length] = new Option(toRemove[i], "rem_" + i);
							}
						}
					}
				// ]]></script>';

		echo '
				</div>
			</td>
		</tr></table>
	</div><br />

	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var currentLabels = {};
	// ]]></script>';

	if ($context['get_pmessage'](true))
	{
		echo '
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="bordercolor">
			<tr class="titlebg">
				<td width="16%">&nbsp;', $txt[29], '</td>
				<td>', $txt[118], '</td>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="bordercolor">';

		// Cache some handy buttons.
		$quote_button = create_button('quote.gif', 145, 'smf240', 'align="middle"');
		$reply_button = create_button('im_reply.gif', 146, 146, 'align="middle"');
		$reply_all_button = create_button('im_reply_all.gif', 'reply_to_all', 'reply_to_all', 'align="middle"');
		$forward_button = create_button('quote.gif', 145, 145, 'align="middle"');
		$delete_button = create_button('delete.gif', 154, 31, 'align="middle"');

		while ($message = $context['get_pmessage']())
		{
			$windowcss = $message['alternate'] == 0 ? 'windowbg' : 'windowbg2';

			echo '
		<tr><td style="padding: 1px 1px 0 1px;">
			<a name="msg', $message['id'], '"></a>
			<table width="100%" cellpadding="3" cellspacing="0" border="0">
				<tr><td colspan="2" class="', $windowcss, '">
					<table width="100%" cellpadding="4" cellspacing="1" style="table-layout: fixed;">
						<tr>
							<td valign="top" width="16%" rowspan="2" style="overflow: hidden;">
								<b>', $message['member']['link'], '</b>
								<div class="smalltext">';
			if (isset($message['member']['title']) && $message['member']['title'] != '')
				echo '
									', $message['member']['title'], '<br />';
			if (isset($message['member']['group']) && $message['member']['group'] != '')
				echo '
									', $message['member']['group'], '<br />';

			if (!$message['member']['is_guest'])
			{
				// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
				if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
					echo '
									', $message['member']['post_group'], '<br />';
				echo '
									', $message['member']['group_stars'], '<br />';

				// Is karma display enabled? Total or +/-?
				if ($modSettings['karmaMode'] == '1')
					echo '
									<br />
									', $modSettings['karmaLabel'], ' ', $message['member']['karma']['good'] - $message['member']['karma']['bad'], '<br />';
				elseif ($modSettings['karmaMode'] == '2')
					echo '
									<br />
									', $modSettings['karmaLabel'], ' +', $message['member']['karma']['good'], '/-', $message['member']['karma']['bad'], '<br />';

				// Is this user allowed to modify this member's karma?
				if ($message['member']['karma']['allow'])
					echo '
									<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pm=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a> <a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pm=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

				// Show online and offline buttons?
				if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
				echo '
									', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $message['member']['online']['image_href'] . '" style="margin-top: 4px;" alt="' . $message['member']['online']['text'] . '" />' : $message['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $message['member']['online']['text'] . '</span>' : '', '<br /><br />';

				// Show the member's gender icon?
				if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '')
					echo '
									', $txt[231], ': ', $message['member']['gender']['image'], '<br />';

				// Show how many posts they have made.
				echo '
									', $txt[26], ': ', $message['member']['posts'], '<br />
									<br />';

				// Show avatars, images, etc.?
				if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']))
					echo '
									', $message['member']['avatar']['image'], '<br />';

				// Show their personal text?
				if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
					echo '
									', $message['member']['blurb'], '<br />
									<br />';
				echo '
									', $message['member']['icq']['link'], '
									', $message['member']['msn']['link'], '
									', $message['member']['yim']['link'], '
									', $message['member']['aim']['link'], '<br />';

				// Show the profile, website, email address, and personal message buttons.
				if ($settings['show_profile_buttons'])
				{
					echo '
									<a href="', $message['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt[27] . '" title="' . $txt[27] . '" />' : $txt[27]), '</a>';
					if ($message['member']['website']['url'] != '')
						echo '
									<a href="', $message['member']['website']['url'], '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt[515] . '" title="' . $message['member']['website']['title'] . '" />' : $txt[515]), '</a>';
					if (empty($message['member']['hide_email']))
						echo '
									<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . '" />' : $txt[69]), '</a>';
					if (!$context['user']['is_guest'] && $context['can_send_pm'])
						echo '
									<a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $message['member']['online']['label'] . '" />' : $message['member']['online']['label'], '</a>';
				}
			}
			elseif (empty($message['member']['hide_email']))
				echo '
									<br />
									<br />
									<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . '" />' : $txt[69]), '</a>';
			echo '
								</div>
							</td>
							<td class="', $windowcss, '" valign="top" width="85%" height="100%">
								<table width="100%" border="0"><tr>
									<td align="left" valign="middle">
										<b>', $message['subject'], '</b>';

			// Show who the message was sent to.
			echo '
										<div class="smalltext">&#171; <b> ', $txt['sent_to'], ':</b> ';

			// People it was sent directly to....
			if (!empty($message['recipients']['to']))
				echo implode(', ', $message['recipients']['to']);
			// Otherwise, we're just going to say "some people"...
			elseif ($context['folder'] != 'outbox')
				echo '(', $txt['pm_undisclosed_recipients'], ')';

			echo ' <b> ', $txt[30], ':</b> ', $message['time'], ' &#187;</div>';

			// If we're in the outbox, show who it was sent to besides the "To:" people.
			if (!empty($message['recipients']['bcc']))
				echo '
										<div class="smalltext">&#171; <b> ', $txt[1502], ':</b> ', implode(', ', $message['recipients']['bcc']), ' &#187;</div>';

			if (!empty($message['is_replied_to']))
				echo '
										<div class="smalltext">&#171; ', $txt['pm_is_replied_to'], ' &#187;</div>';

			echo '
									</td>
									<td align="right" valign="bottom" height="20" nowrap="nowrap" style="font-size: smaller;">';

			// Show reply buttons if you have the permission to send PMs.
			if ($context['can_send_pm'])
			{
				// You can't really reply if the member is gone.
				if (!$message['member']['is_guest'])
				{
					// Were than more than one recipient you can reply to? (Only in the "button style", or text)
					if ($message['number_recipients'] > 1 && (!empty($settings['use_buttons']) || !$settings['use_image_buttons']))
						echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=all">', $reply_all_button, '</a>', $context['menu_separator'];
					echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=', $context['folder'] == 'outbox' ? '' : $message['member']['id'], '">', $quote_button, '</a>', $context['menu_separator'], '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';u=', $message['member']['id'], '">', $reply_button, '</a> ', $context['menu_separator'];
				}
				// This is for "forwarding" - even if the member is gone.
				else
					echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote">', $forward_button, '</a>', $context['menu_separator'];
			}
			echo '
										<a href="', $scripturl, '?action=pm;sa=pmactions;pm_actions[', $message['id'], ']=delete;f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';sesc=', $context['session_id'], '" onclick="return confirm(\'', addslashes($txt[154]), '?\');">', $delete_button, '</a>
										<input style="vertical-align: middle;" type="checkbox" name="pms[]" id="deletedisplay', $message['id'], '" value="', $message['id'], '" class="check" onclick="document.getElementById(\'deletelisting', $message['id'], '\').checked = this.checked;" />
									</td>
								</tr></table>
								<hr width="100%" size="1" class="hrcolor" />
								<div class="personalmessage">', $message['body'], '</div>
							</td>
						</tr>
						<tr class="', $windowcss, '">
							<td valign="bottom" class="smalltext" width="85%">
								', (!empty($modSettings['enableReportPM']) && $context['folder'] != 'outbox' ? '<div align="right"><a href="' . $scripturl . '?action=pm;sa=report;l=' . $context['current_label_id'] . ';pmsg=' . $message['id'] . '" class="smalltext">' . $txt['pm_report_to_admin'] . '</a></div>' : '');

			// Show the member's signature?
			if (!empty($message['member']['signature']) && empty($options['show_no_signatures']))
				echo '
								<hr width="100%" size="1" class="hrcolor" />
								<div class="signature">', $message['member']['signature'], '</div>';

			echo '
							</td>
						</tr>';

		// Add an extra line at the bottom if we have labels enabled.
		if ($context['folder'] != 'outbox' && !empty($context['currently_using_labels']))
		{
			echo '
						<tr class="', $windowcss, '">
							<td valign="bottom" colspan="2" width="100%" align="right">';
			// Add the label drop down box.
			if (!empty($context['currently_using_labels']))
			{
				echo '
								<select name="pm_actions[', $message['id'], ']" onchange="if (this.options[this.selectedIndex].value) form.submit();">
									<option value="">', $txt['pm_msg_label_title'], ':</option>
									<option value="" disabled="disabled">---------------</option>';
				// Are there any labels which can be added to this?
				if (!$message['fully_labeled'])
				{
					echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_apply'], ':</option>';
					foreach ($context['labels'] as $label)
					{
						if (!isset($message['labels'][$label['id']]))
							echo '
										<option value="', $label['id'], '">&nbsp;', $label['name'], '</option>';
					}
				}
				// ... and are there any that can be removed?
				if (!empty($message['labels']) && (count($message['labels']) > 1 || !isset($message['labels'][-1])))
				{
					echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_remove'], ':</option>';
					foreach ($message['labels'] as $label)
						echo '
									<option value="', $label['id'], '">&nbsp;', $label['name'], '</option>';
				}
				echo '
								</select>
								<noscript>
								<input type="submit" value="', $txt['pm_apply'], '" />
								</noscript>';
			}
			echo '
							</td>
						</tr>';
		}

		echo '
					</table>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						currentLabels[', $message['id'], '] = {';

		if (!empty($message['labels']))
		{
			$first = true;
			foreach ($message['labels'] as $label)
			{
				echo $first ? '' : ',', '
								"', $label['id'], '": "', $label['name'], '"';
				$first = false;
			}
		}

		echo '
						};
					// ]]></script>
				</td></tr>
			</table>
		</td></tr>';
		}

		echo '
			<tr><td style="padding: 0 0 1px 0;"></td></tr>
	</table>

	<div class="tborder" style="padding: 1px; margin-top: 1ex;">
		<table cellpadding="3" cellspacing="0" border="0" width="100%">
			<tr class="catbg" valign="middle">
				<td height="25">
					<div style="float: left;">', $txt[139], ': ', $context['page_index'], '</div>
					<div style="float: right;"><input type="submit" name="del_selected" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="if (!confirm(\'', $txt['smf249'], '\')) return false;" /></div>
				</td>
			</tr>
		</table>
	</div>';
	}

	echo '
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>';
}

function template_search()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function expandCollapseLabels()
		{
			var current = document.getElementById("searchLabelsExpand").style.display != "none";

			document.getElementById("searchLabelsExpand").style.display = current ? "none" : "";
			document.getElementById("expandLabelsIcon").src = smf_images_url + (current ? "/expand.gif" : "/collapse.gif");
		}
	// ]]></script>
<form action="', $scripturl, '?action=pm;sa=search2" method="post" accept-charset="', $context['character_set'], '" name="pmSearchForm">
	<table border="0" width="75%" align="center" cellpadding="3" cellspacing="0" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $txt['pm_search_title'], '</td>
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
					<b>', $txt['pm_search_text'], ':</b><br />
					<input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" />&nbsp;
					<input type="submit" name="submit" value="', $txt['pm_search_go'], '" /><br />
					<a href="', $scripturl, '?action=pm;sa=search;advanced" onclick="this.href += \';search=\' + escape(document.forms.pmSearchForm.search.value);">', $txt['pm_search_advanced'], '</a>
					<input type="hidden" name="advanced" value="0" />';
	}
	else
	{
		echo '
					<input type="hidden" name="advanced" value="1" />
					<table cellpadding="1" cellspacing="3" border="0">
						<tr>
							<td>
								<b>', $txt['pm_search_text'], ':</b>
							</td>
							<td></td>
							<td>
								<b>', $txt['pm_search_user'], ':</b>
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
										if (document.forms.pmSearchForm.search.value.indexOf("%u") != -1)
											document.forms.pmSearchForm.search.value = unescape(document.forms.pmSearchForm.search.value);
									}
									window.addEventListener("load", initSearch, false);
								// ]]></script>
							</td><td style="padding-right: 2ex;">
								<select name="searchtype">
									<option value="1"', empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt['pm_search_match_all'], '</option>
									<option value="2"', !empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt['pm_search_match_any'], '</option>
								</select>
							</td><td>
								<input type="text" name="userspec" value="', empty($context['search_params']['userspec']) ? '*' : $context['search_params']['userspec'], '" size="40" />
							</td>
						</tr><tr>
							<td style="padding-top: 2ex;" colspan="2"><b>', $txt['pm_search_options'], ':</b></td>
							<td style="padding-top: 2ex;"><b>', $txt['pm_search_post_age'], ': </b></td>
						</tr><tr>
							<td colspan="2">
								<label for="show_complete"><input type="checkbox" name="show_complete" id="show_complete" value="1"', !empty($context['search_params']['show_complete']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_search_show_complete'], '</label><br />
								<label for="subject_only"><input type="checkbox" name="subject_only" id="subject_only" value="1"', !empty($context['search_params']['subject_only']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_search_subject_only'], '</label>
							</td>
							<td>
								', $txt['pm_search_between'], ' <input type="text" name="minage" value="', empty($context['search_params']['minage']) ? '0' : $context['search_params']['minage'], '" size="5" maxlength="5" />&nbsp;', $txt['pm_search_between_and'], '&nbsp;<input type="text" name="maxage" value="', empty($context['search_params']['maxage']) ? '9999' : $context['search_params']['maxage'], '" size="5" maxlength="5" /> ', $txt['pm_search_between_days'], '.
							</td>
						</tr><tr>
							<td style="padding-top: 2ex;" colspan="2"><b>', $txt['pm_search_order'], ':</b></td>
							<td></td>
						</tr><tr>
							<td colspan="2">
								<select name="sort">
		<!--- <option value="relevance|desc">', $txt['pm_search_orderby_relevant_first'], '</option> --->
									<option value="ID_PM|desc">', $txt['pm_search_orderby_recent_first'], '</option>
									<option value="ID_PM|asc">', $txt['pm_search_orderby_old_first'], '</option>
								</select>
							</td>
							<td></td>
						</tr>';

		// Do we have some labels setup? If so offer to search by them!
		if ($context['currently_using_labels'])
		{
			echo '
						<tr>
							<td colspan="4">
					<a href="javascript:void(0);" onclick="expandCollapseLabels(); return false;"><img src="', $settings['images_url'], '/expand.gif" id="expandLabelsIcon" alt="" /></a> <a href="javascript:void(0);" onclick="expandCollapseLabels(); return false;"><b>', $txt['pm_search_choose_label'], '</b></a><br />

					<table id="searchLabelsExpand" width="90%" border="0" cellpadding="1" cellspacing="0" align="center" ', $context['check_all'] ? 'style="display: none;"' : '', '>';

			$alternate = true;
			foreach ($context['search_labels'] as $label)
			{
				if ($alternate)
					echo '
						<tr>';
				echo '
							<td width="50%">
								<label for="searchlabel_', $label['id'], '"><input type="checkbox" id="searchlabel_', $label['id'], '" name="searchlabel[', $label['id'], ']" value="', $label['id'], '" ', $label['checked'] ? 'checked="checked"' : '', ' class="check" />
								', $label['name'], '</label>
							</td>';
				if (!$alternate)
					echo '
						</tr>';

				$alternate = !$alternate;
			}

			// If we haven't ended cleanly fix it...
			if ($alternate % 2 == 0)
				echo '
						<td width="50%"></td>
					</tr>';

			echo '
					</table>

					<br />
					<input type="checkbox" name="all" id="check_all" value="" ', $context['check_all'] ? 'checked="checked"' : '', ' onclick="invertAll(this, this.form, \'searchlabel\');" class="check" /><i> <label for="check_all">', $txt[737], '</label></i><br />
							</td>
						</tr>';
		}

		echo '
					</table>
					<br />

					<div style="padding: 2px;"><input type="submit" name="submit" value="', $txt['pm_search_go'], '" /></div>';
	}

	echo '
				</td>
			</tr>
		</table>
	</form>';
}

function template_search_results()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// This splits broadly into two types of template... complete results first.
	if (!empty($context['search_params']['show_complete']))
	{
		echo '
		<table border="0" width="98%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['pm_search_results'], '</td>
			</tr>
			<tr class="catbg" height="30">
				<td colspan="3"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table><br />';
	}
	else
	{
		echo '
		<table border="0" width="98%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['pm_search_results'], '</td>
			</tr>
			<tr class="catbg">
				<td colspan="3"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td width="30%">', $txt[317], '</td>
				<td width="50%">', $txt[319], '</td>
				<td width="20%">', $txt[318], '</td>
			</tr>';
	}

	$alternate = true;
	// Print each message out...
	foreach ($context['personal_messages'] as $message)
	{
		// We showing it all?
		if (!empty($context['search_params']['show_complete']))
		{
			// !!! This still needs to be made pretty.
			echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="left">
					<div style="float: left;">
					', $message['counter'], '&nbsp;&nbsp;<a href="', $message['href'], '">', $message['subject'], '</a>
					</div>
					<div style="float: right;">
						', $txt[176], ': ', $message['time'], '
					</div>
				</td>
			</tr>
			<tr class="catbg">
				<td>', $txt[318], ': ', $message['member']['link'], ', ', $txt[324], ': ';

			// Show the recipients.
			// !!! This doesn't deal with the outbox searching quite right for bcc.
			if (!empty($message['recipients']['to']))
				echo implode(', ', $message['recipients']['to']);
			// Otherwise, we're just going to say "some people"...
			elseif ($context['folder'] != 'outbox')
				echo '(', $txt['pm_undisclosed_recipients'], ')';

			echo '
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td>', $message['body'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="right" class="middletext">';

			if ($context['can_send_pm'])
			{
				$quote_button = create_button('quote.gif', 145, 145, 'align="middle"');
				$reply_button = create_button('im_reply.gif', 146, 146, 'align="middle"');

				// You can only reply if they are not a guest...
				if (!$message['member']['is_guest'])
					echo '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=', $context['folder'] == 'outbox' ? '' : $message['member']['id'], '">', $quote_button , '</a>', $context['menu_separator'], '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';u=', $message['member']['id'], '">', $reply_button , '</a> ', $context['menu_separator'];
				// This is for "forwarding" - even if the member is gone.
				else
					echo '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote">', $quote_button , '</a>', $context['menu_separator'];
			}

			echo '
				</td>
			</tr>
		</table><br />';
		}
		// Otherwise just a simple list!
		else
		{
			// !!! No context at all of the search?
			echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" valign="top">
				<td>', $message['time'], '</td>
				<td>', $message['link'], '</td>
				<td>', $message['member']['link'], '</td>
			</tr>';
		}

		$alternate = !$alternate;
	}

	// Finish off the page...
	if (!empty($context['search_params']['show_complete']))
	{
		// No results?
		if (empty($context['personal_messages']))
			echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="0" border="0" class="tborder" style="border-width: 0 1px 1px 1px;">
			<tr class="windowbg">
				<td>', $txt['pm_search_none_found'], '</td>
			</tr>
		</table><br />';

		echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="0" border="0" class="tborder" style="border-width: 0 1px 1px 1px;">
			<tr class="catbg" height="30">
				<td colspan="3"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table>';
	}
	else
	{
		if (empty($context['personal_messages']))
			echo '
			<tr class="windowbg2">
				<td colspan="3">', $txt['pm_search_none_found'], '</td>
			</tr>';

		echo '
			<tr class="catbg">
				<td colspan="3"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table>';
	}
}

function template_send()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	if ($context['show_spellchecking'])
		echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';

	// Show which messages were sent successfully and which failed.
	if (!empty($context['send_log']))
	{
		echo '
		<br />
		<table border="0" width="80%" cellspacing="1" cellpadding="3" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', $txt['pm_send_report'], '</td>
			</tr>
			<tr>
				<td class="windowbg">';
		foreach ($context['send_log']['sent'] as $log_entry)
			echo '<span style="color: green">', $log_entry, '</span><br />';
		foreach ($context['send_log']['failed'] as $log_entry)
			echo '<span style="color: red">', $log_entry, '</span><br />';
		echo '
				</td>
			</tr>
		</table><br />';
	}

	// Show the preview of the personal message.
	if (isset($context['preview_message']))
	echo '
		<br />
		<table border="0" width="80%" cellspacing="1" cellpadding="3" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', $context['preview_subject'], '</td>
			</tr>
			<tr>
				<td class="windowbg">
					', $context['preview_message'], '
				</td>
			</tr>
		</table><br />';

	// Main message editing box.
	echo '
		<table border="0" width="80%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td><img src="', $settings['images_url'], '/icons/im_newmsg.gif" alt="', $txt[321], '" title="', $txt[321], '" />&nbsp;', $txt[321], '</td>
			</tr><tr>
				<td class="windowbg">
					<form action="', $scripturl, '?action=pm;sa=send2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);saveEntities();">
						<table border="0" cellpadding="3" width="100%">';

	// If there were errors for sending the PM, show them.
	if (!empty($context['post_error']['messages']))
	{
		echo '
							<tr>
								<td></td>
								<td align="left">
									<b>', $txt['error_while_submitting'], '</b>
									<div style="color: red; margin: 1ex 0 2ex 3ex;">
										', implode('<br />', $context['post_error']['messages']), '
									</div>
								</td>
							</tr>';
	}

	// To and bcc. Include a button to search for members.
	echo '
							<tr>
								<td align="right"><b', (isset($context['post_error']['no_to']) || isset($context['post_error']['bad_to']) ? ' style="color: red;"' : ''), '>', $txt[150], ':</b></td>
								<td class="smalltext">
									<input type="text" name="to" id="to" value="', $context['to'], '" tabindex="', $context['tabindex']++, '" size="40" />&nbsp;
									<a href="', $scripturl, '?action=findmember;input=to;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=to;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
								</td>
							</tr><tr>
								<td align="right"><b', (isset($context['post_error']['bad_bcc']) ? ' style="color: red;"' : ''), '>', $txt[1502], ':</b></td>
								<td class="smalltext">
									<input type="text" name="bcc" id="bcc" value="', $context['bcc'], '" tabindex="', $context['tabindex']++, '" size="40" />&nbsp;
									<a href="', $scripturl, '?action=findmember;input=bcc;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> ', $txt[748], '
								</td>
							</tr>';
	// Subject of personal message.
	echo '
							<tr>
								<td align="right"><b', (isset($context['post_error']['no_subject']) ? ' style="color: red;"' : ''), '>', $txt[70], ':</b></td>
								<td><input type="text" name="subject" value="', $context['subject'], '" tabindex="', $context['tabindex']++, '" size="40" maxlength="50" /></td>
							</tr>';

	if ($context['visual_verification'])
	{
		echo '
							<tr>
								<td align="right" valign="top">
									<b>', $txt['pm_visual_verification_label'], ':</b>
								</td>
								<td>';
		if ($context['use_graphic_library'])
			echo '
									<img src="', $context['verificiation_image_href'], '" alt="', $txt['pm_visual_verification_desc'], '" /><br />';
		else
			echo '
									<img src="', $context['verificiation_image_href'], ';letter=1" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verificiation_image_href'], ';letter=2" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verificiation_image_href'], ';letter=3" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verificiation_image_href'], ';letter=4" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verificiation_image_href'], ';letter=5" alt="', $txt['pm_visual_verification_desc'], '" /><br />';
		echo '
									<a href="', $context['verificiation_image_href'], ';sound" onclick="return reqWin(this.href, 400, 120);">', $txt['pm_visual_verification_listen'], '</a><br /><br />
									<input type="text" name="visual_verification_code" size="30" tabindex="', $context['tabindex']++, '" />
									<div class="smalltext">', $txt['pm_visual_verification_desc'], '</div>
								</td>
							</tr>';
	}

	// Show BBC buttons, smileys and textbox.
	theme_postbox($context['message']);

	// Send, Preview, spellcheck buttons.
	echo '
							<tr>
								<td align="right" colspan="2">
									<input type="submit" value="', $txt[148], '" tabindex="', $context['tabindex']++, '" onclick="return submitThisOnce(this);" accesskey="s" />
									<input type="submit" name="preview" value="', $txt[507], '" tabindex="', $context['tabindex']++, '" onclick="return submitThisOnce(this);" accesskey="p" />';
	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" tabindex="', $context['tabindex']++, '" onclick="spellCheck(\'postmodify\', \'message\');" />';
	echo '
								</td>
							</tr>
							<tr>
								<td></td>
								<td align="left">
									<label for="outbox"><input type="checkbox" name="outbox" id="outbox" value="1" tabindex="', $context['tabindex']++, '"', $context['copy_to_outbox'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_save_outbox'], '</label>
								</td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
						<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
						<input type="hidden" name="f" value="', isset($context['folder']) ? $context['folder'] : '', '" />
						<input type="hidden" name="l" value="', isset($context['current_label_id']) ? $context['current_label_id'] : -1, '" />
					</form>
				</td>
			</tr>
		</table>';

	// Some hidden information is needed in order to make the spell checking work.
	if ($context['show_spellchecking'])
		echo '
		<form name="spell_form" id="spell_form" method="post" accept-charset="', $context['character_set'], '" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';

	// Show the message you're replying to.
	if ($context['reply'])
		echo '
		<br />
		<br />
		<table width="100%" border="0" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr>
				<td colspan="2" class="windowbg"><b>', $txt[319], ': ', $context['quoted_message']['subject'], '</b></td>
			</tr>
			<tr>
				<td class="windowbg2">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td class="windowbg2">', $txt[318], ': ', $context['quoted_message']['member']['name'], '</td>
							<td class="windowbg2" align="right">', $txt[30], ': ', $context['quoted_message']['time'], '</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="windowbg">', $context['quoted_message']['body'], '</td>
			</tr>
		</table>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function autocompleter(element)
			{
				if (typeof(element) != "object")
					element = document.getElementById(element);

				this.element = element;
				this.key = null;
				this.request = null;
				this.source = null;
				this.lastSearch = "";
				this.oldValue = "";
				this.cache = [];

				this.change = function (ev, force)
				{
					if (window.event)
						this.key = window.event.keyCode + 0;
					else
						this.key = ev.keyCode + 0;
					if (this.key == 27)
						return true;
					if (this.key == 34 || this.key == 8 || this.key == 13 || (this.key >= 37 && this.key <= 40))
						force = false;

					if (isEmptyText(this.element))
						return true;

					if (this.request != null && typeof(this.request) == "object")
						this.request.abort();

					var element = this.element, search = this.element.value.replace(/^("[^"]+",[ ]*)+/, "").replace(/^([^,]+,[ ]*)+/, "");
					this.oldValue = this.element.value.substr(0, this.element.value.length - search.length);
					if (search.substr(0, 1) == \'"\')
						search = search.substr(1);

					if (search == "" || search.substr(search.length - 1) == \'"\')
						return true;

					if (this.lastSearch == search)
					{
						if (force)
							this.select(this.cache[0]);

						return true;
					}
					else if (search.substr(0, this.lastSearch.length) == this.lastSearch && this.cache.length != 100)
					{
						// Instead of hitting the server again, just narrow down the results...
						var newcache = [], j = 0;
						for (var k = 0; k < this.cache.length; k++)
						{
							if (this.cache[k].substr(0, search.length) == search)
								newcache[j++] = this.cache[k];
						}

						if (newcache.length != 0)
						{
							this.lastSearch = search;
							this.cache = newcache;

							if (force)
								this.select(newcache[0]);

							return true;
						}
					}

					this.request = new XMLHttpRequest();
					this.request.onreadystatechange = function ()
					{
						element.autocompleter.handler(force);
					}

					this.request.open("GET", this.source + escape(textToEntities(search).replace(/&#(\d+);/g, "%#$1%")).replace(/%26/g, "%25%23038%25") + ";" + (new Date().getTime()), true);
					this.request.send(null);

					return true;
				}
				this.keyup = function (ev)
				{
					this.change(ev, true);

					return true;
				}
				this.keydown = function ()
				{
					if (this.request != null && typeof(this.request) == "object")
						this.request.abort();
				}
				this.handler = function (force)
				{
					if (this.request.readyState != 4)
						return true;

					var response = this.request.responseText.split("\n");
					this.lastSearch = this.element.value;
					this.cache = response;

					if (response.length < 2)
						return true;

					if (force)
						this.select(response[0]);

					return true;
				}
				this.select = function (value)
				{
					if (value == "")
						return;

					var i = this.element.value.length + (this.element.value.substr(this.oldValue.length, 1) == \'"\' ? 0 : 1);
					this.element.value = this.oldValue + \'"\' + value + \'"\';

					if (typeof(this.element.createTextRange) != "undefined")
					{
						var d = this.element.createTextRange();
						d.moveStart("character", i);
						d.select();
					}
					else if (this.element.setSelectionRange)
					{
						this.element.focus();
						this.element.setSelectionRange(i, this.element.value.length);
					}
				}

				this.element.autocompleter = this;
				this.element.setAttribute("autocomplete", "off");

				this.element.onchange = function (ev)
				{
					this.autocompleter.change(ev);
				}
				this.element.onkeyup = function (ev)
				{
					this.autocompleter.keyup(ev);
				}
				this.element.onkeydown = function (ev)
				{
					this.autocompleter.keydown(ev);
				}
			}

			if (window.XMLHttpRequest)
			{
				var toComplete = new autocompleter("to"), bccComplete = new autocompleter("bcc");
				toComplete.source = "', $scripturl, '?action=requestmembers;sesc=', $context['session_id'], ';search=";
				bccComplete.source = "', $scripturl, '?action=requestmembers;sesc=', $context['session_id'], ';search=";
			}

			function saveEntities()
			{
				var textFields = ["subject", "message"];
				for (i in textFields)
					if (document.forms.postmodify.elements[textFields[i]])
						document.forms.postmodify[textFields[i]].value = document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#");
			}
		// ]]></script>';
}

// This template asks the user whether they wish to empty out their folder/messages.
function template_ask_delete()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="80%" cellpadding="4" cellspacing="1" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', ($context['delete_all'] ? $txt[411] : $txt[412]), '</td>
			</tr>
			<tr>
				<td class="windowbg">
					', $txt[413], '<br />
					<br />
					<b><a href="', $scripturl, '?action=pm;sa=removeall2;f=', $context['folder'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';sesc=', $context['session_id'], '">', $txt[163], '</a> - <a href="javascript:history.go(-1);">', $txt[164], '</a></b>
				</td>
			</tr>
		</table>';
}

// This template asks the user what messages they want to prune.
function template_prune()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=pm;sa=prune" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['pm_prune_warning'], '\');">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="catbg">
				<td>', $txt['pm_prune'], '</td>
			</tr>
			<tr class="windowbg">
				<td>', $txt['pm_prune_desc1'], ' <input type="text" name="age" size="3" value="14" /> ', $txt['pm_prune_desc2'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="right"><input type="submit" value="', $txt['smf138'], '" /></td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Here we allow the user to setup labels, remove labels and change rules for labels (i.e, do quite a bit)
function template_labels()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=pm;sa=manlabels;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['pm_manage_labels'], '</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2" style="padding: 1ex;"><span class="smalltext">', $txt['pm_labels_desc'], '</span></td>
			</tr>
			<tr class="catbg3">
				<td colspan="2">
					<div style="float: right; width: 4%; text-align: center;"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></div>
					', $txt['pm_label_name'], '
				</td>
			</tr>';
	if (empty($context['labels']))
		echo '
			<tr class="windowbg2">
				<td colspan="2" align="center">', $txt['pm_labels_no_exist'], '</td>
			</tr>';
	else
	{
		$alternate = true;
		foreach ($context['labels'] as $label)
		{
			if ($label['id'] != -1)
			{
				echo '
				<tr class="', $alternate ? 'windowbg2' : 'windowbg', '">
					<td>
						<input type="text" name="label_name[', $label['id'], ']" value="', $label['name'], '" size="30" maxlength="30" />
					</td>
					<td width="4%" align="center"><input type="checkbox" class="check" name="delete_label[', $label['id'], ']" /></td>
				</tr>';
				$alternate = !$alternate;
			}
		}

		echo '
			<tr class="catbg3">
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt[10], '" style="font-weight: normal;" />
					<input type="submit" name="delete" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="return confirm(\'', $txt['pm_labels_delete'], '\');" />
				</td>
			</tr>';
	}
	echo '
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>
	<form action="', $scripturl, '?action=pm;sa=manlabels;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '" style="margin-top: 1ex;">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2" align="left">
					', $txt['pm_label_add_new'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" width="40%">
					<b>', $txt['pm_label_name'], ':</b>
				</td>
				<td align="left">
					<input type="text" name="label" value="" size="30" maxlength="20" />
				</td>
			</tr>
			<tr class="catbg3">
				<td colspan="2" align="right">
					<input type="submit" name="add" value="', $txt['pm_label_add_new'], '" style="font-weight: normal;" />
				</td>
			</tr>
		</table>
	</form>';
}

// Template for reporting a personal message.
function template_report_message()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=pm;sa=report;l=', $context['current_label_id'], '" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="pmsg" value="', $context['pm_id'], '" />
		<table border="0" width="80%" cellspacing="0" class="tborder" align="center" cellpadding="4">
			<tr class="titlebg">
				<td>', $txt['pm_report_title'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="left">
					<span class="smalltext">', $txt['pm_report_desc'], '</span>
				</td>
			</tr>';

	// If there is more than one admin on the forum, allow the user to choose the one they want to direct to.
	// !!! Why?
	if ($context['admin_count'] > 1)
	{
		echo '
			<tr class="windowbg">
				<td align="left">
					<b>', $txt['pm_report_admins'], ':</b>
					<select name="ID_ADMIN">
						<option value="0">', $txt['pm_report_all_admins'], '</option>';
		foreach ($context['admins'] as $id => $name)
			echo '
						<option value="', $id, '">', $name, '</option>';
		echo '
					</select>
				</td>
			</tr>';
	}

	echo '
			<tr class="windowbg">
				<td align="left">
					<b>', $txt['pm_report_reason'], ':</b>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<textarea name="reason" rows="4" cols="70" style="width: 80%;"></textarea>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<input type="submit" name="report" value="', $txt['pm_report_message'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Little template just to say "Yep, it's been submitted"
function template_report_message_complete()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="80%" cellspacing="0" class="tborder" align="center" cellpadding="4">
			<tr class="titlebg">
				<td>', $txt['pm_report_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="left">
					', $txt['pm_report_done'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<br /><a href="', $scripturl, '?action=pm;l=', $context['current_label_id'], '">', $txt['pm_report_return'], '</a>
				</td>
			</tr>
		</table>';
}

?>