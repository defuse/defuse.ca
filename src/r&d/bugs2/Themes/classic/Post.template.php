<?php
// Version: 1.1; Post

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	if ($context['show_spellchecking'])
		echo '
		<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/spellcheck.js"></script>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	// Start with message icons - and any missing from this theme.
	echo '
			var icon_urls = {';
	foreach ($context['icons'] as $icon)
		echo '
				"', $icon['value'], '": "', $icon['url'], '"', $icon['is_last'] ? '' : ',';
	echo '
			};';

	// The actual message icon selector.
	echo '
			function showimage()
			{
				document.images.icons.src = icon_urls[document.forms.postmodify.icon.options[document.forms.postmodify.icon.selectedIndex].value];
			}';

	// The functions used to preview a posts without loading a new page.
	echo '
			var current_board = ', empty($context['current_board']) ? 'null' : $context['current_board'], ';
			var make_poll = ', $context['make_poll'] ? 'true' : 'false', ';
			var txt_preview_title = "', $txt['preview_title'], '";
			var txt_preview_fetch = "', $txt['preview_fetch'], '";

			function previewPost()
			{', $context['browser']['is_firefox'] ? '
				// Firefox doesn\'t render <marquee> that have been put it using javascript
				if (document.forms.postmodify.elements["message"].value.indexOf("[move]") != -1)
				{
					return submitThisOnce(document.forms.postmodify);
				}' : '', '
				if (window.XMLHttpRequest)
				{
					// Opera didn\'t support setRequestHeader() before 8.01.
					if (typeof(window.opera) != "undefined")
					{
						var test = new XMLHttpRequest();
						if (typeof(test.setRequestHeader) != "function")
							return submitThisOnce(document.forms.postmodify);
					}
					// !!! Currently not sending poll options and option checkboxes.
					var i, x = new Array();
					var textFields = ["subject", "message", "icon", "guestname", "email", "evtitle", "question", "topic"];
					var numericFields = [
						"board", "topic", "num_replies",
						"eventid", "calendar", "year", "month", "day",
						"poll_max_votes", "poll_expire", "poll_change_vote", "poll_hide"
					];
					var checkboxFields = [
						"ns",
					];

					for (i in textFields)
						if (document.forms.postmodify.elements[textFields[i]])
							x[x.length] = textFields[i] + "=" + escape(textToEntities(document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
					for (i in numericFields)
						if (document.forms.postmodify.elements[numericFields[i]] && typeof(document.forms.postmodify[numericFields[i]].value) != "undefined")
							x[x.length] = numericFields[i] + "=" + parseInt(document.forms.postmodify.elements[numericFields[i]].value);
					for (i in checkboxFields)
						if (document.forms.postmodify.elements[checkboxFields[i]] && document.forms.postmodify.elements[checkboxFields[i]].checked)
							x[x.length] = checkboxFields[i] + "=" + document.forms.postmodify.elements[checkboxFields[i]].value;

					sendXMLDocument(smf_scripturl + "?action=post2" + (current_board ? ";board=" + current_board : "") + (make_poll ? ";poll" : "") + ";preview;xml", x.join("&"), onDocSent);

					document.getElementById("preview_section").style.display = "";
					setInnerHTML(document.getElementById("preview_subject"), txt_preview_title);
					setInnerHTML(document.getElementById("preview_body"), txt_preview_fetch);

					return false;
				}
				else
					return submitThisOnce(document.forms.postmodify);
			}
			function onDocSent(XMLDoc)
			{
				if (!XMLDoc)
				{
					document.forms.postmodify.preview.onclick = new function ()
					{
						return true;
					}
					document.forms.postmodify.preview.click();
				}

				// Show the preview section.
				var i, preview = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("preview")[0];
				setInnerHTML(document.getElementById("preview_subject"), preview.getElementsByTagName("subject")[0].firstChild.nodeValue);

				var bodyText = "";
				for (i = 0; i < preview.getElementsByTagName("body")[0].childNodes.length; i++)
					bodyText += preview.getElementsByTagName("body")[0].childNodes[i].nodeValue;

				setInnerHTML(document.getElementById("preview_body"), bodyText);
				document.getElementById("preview_body").className = "post";

				// Show a list of errors (if any).
				var errors = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("errors")[0];
				var numErrors = errors.getElementsByTagName("error").length, errorList = new Array();
				for (i = 0; i < numErrors; i++)
					errorList[errorList.length] = errors.getElementsByTagName("error")[i].firstChild.nodeValue;
				document.getElementById("errors").style.display = numErrors == 0 ? "none" : "";
				document.getElementById("error_serious").style.display = errors.getAttribute("serious") == 1 ? "" : "none";
				setInnerHTML(document.getElementById("error_list"), numErrors == 0 ? "" : errorList.join("<br />"));

				// Show a warning if the topic has been locked.
				document.getElementById("lock_warning").style.display = errors.getAttribute("topic_locked") == 1 ? "" : "none";

				// Adjust the color of captions if the given data is erroneous.
				var captions = errors.getElementsByTagName("caption"), numCaptions = errors.getElementsByTagName("caption").length;
				for (i = 0; i < numCaptions; i++)
					if (document.getElementById("caption_" + captions[i].getAttribute("name")))
						document.getElementById("caption_" + captions[i].getAttribute("name")).style.color = captions[i].getAttribute("color");

				if (errors.getElementsByTagName("post_error").length == 1)
					document.forms.postmodify.message.style.border = "1px solid red";
				else if (document.forms.postmodify.message.style.borderColor == "red" || document.forms.postmodify.message.style.borderColor == "red red red red")
				{
					if (typeof(document.forms.postmodify.message.runtimeStyle) == "undefined")
						document.forms.postmodify.message.style.border = null;
					else
						document.forms.postmodify.message.style.borderColor = "";
				}

				// Set the new number of replies
				if (document.forms.postmodify.elements["num_replies"])
					document.forms.postmodify.num_replies.value = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("num_replies")[0].firstChild.nodeValue;

				var newPosts = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("new_posts")[0] ? XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("new_posts")[0].getElementsByTagName("post") : {length: 0};
				var numNewPosts = newPosts.length;
				if (numNewPosts != 0)
				{
					var newTable = \'<span id="new_replies"></span><table width="100%" class="windowbg" cellspacing="0" cellpadding="2" align="center" style="table-layout: fixed;">\';
					for (i = 0; i < numNewPosts; i++)
						newTable += \'<tr class="catbg"><td colspan="2" align="left" class="smalltext"><div style="float: right;">', $txt[280], ': \' + newPosts[i].getElementsByTagName("time")[0].firstChild.nodeValue + \' <img src="', $settings['images_url'], '/', $context['user']['language'], '/new.gif" alt="', $txt['preview_new'], '" border="0" /></div>', $txt[279], ': \' + newPosts[i].getElementsByTagName("poster")[0].firstChild.nodeValue + \'</td></tr><tr class="windowbg2"><td colspan="2" class="smalltext" id="msg\' + newPosts[i].getAttribute("id") + \'" width="100%"><div align="right" class="smalltext"><a href="#top" onclick="return insertQuoteFast(\\\'\' + newPosts[i].getAttribute("id") + \'\\\');">', $txt[260], '</a></div><div class="post">\' + newPosts[i].getElementsByTagName("message")[0].firstChild.nodeValue + \'</div></td></tr>\';
					newTable += \'</table>\';
					setOuterHTML(document.getElementById("new_replies"), newTable);
				}

				if (typeof(smf_codeFix) != "undefined")
					smf_codeFix();
			}';

	// A function needed to discern HTML entities from non-western characters.
	echo '
			function saveEntities()
			{
				var textFields = ["subject", "message", "guestname", "evtitle", "question"];
				for (i in textFields)
					if (document.forms.postmodify.elements[textFields[i]])
						document.forms.postmodify[textFields[i]].value = document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#");
				for (var i = document.forms.postmodify.elements.length - 1; i >= 0; i--)
					if (document.forms.postmodify.elements[i].name.indexOf("options") == 0)
						document.forms.postmodify.elements[i].value = document.forms.postmodify.elements[i].value.replace(/&#/g, "&#38;#");
			}';

	if (!empty($settings['additional_options_collapsable']))
		echo '
			var currentSwap = false;
			function swapOptions()
			{
				document.getElementById("postMoreExpand").src = smf_images_url + "/" + (currentSwap ? "collapse.gif" : "expand.gif");
				document.getElementById("postMoreExpand").alt = currentSwap ? "-" : "+";

				document.getElementById("postMoreOptions").style.display = currentSwap ? "" : "none";

				if (document.getElementById("postAttachment"))
					document.getElementById("postAttachment").style.display = currentSwap ? "" : "none";
				if (document.getElementById("postAttachment2"))
					document.getElementById("postAttachment2").style.display = currentSwap ? "" : "none";

				if (typeof(document.forms.postmodify.additional_options) != "undefined")
					document.forms.postmodify.additional_options.value = currentSwap ? "1" : "0";

				currentSwap = !currentSwap;
			}';

	// If this is a poll - use some javascript to ensure the user doesn't create a poll with illegal option combinations.
	if ($context['make_poll'])
		echo '
			function pollOptions()
			{
				var expireTime = document.getElementById("poll_expire");

				if (isEmptyText(expireTime) || expireTime.value == 0)
				{
					document.forms.postmodify.poll_hide[2].disabled = true;
					if (document.forms.postmodify.poll_hide[2].checked)
						document.forms.postmodify.poll_hide[1].checked = true;
				}
				else
					document.forms.postmodify.poll_hide[2].disabled = false;
			}

			var pollOptionNum = 0, pollTabIndex;
			function addPollOption()
			{
				if (pollOptionNum == 0)
				{
					for (var i = 0; i < document.forms.postmodify.elements.length; i++)
						if (document.forms.postmodify.elements[i].id.substr(0, 8) == "options-")
						{
							pollOptionNum++;
							pollTabIndex = document.forms.postmodify.elements[i].tabIndex;
						}
				}
				pollOptionNum++

				setOuterHTML(document.getElementById("pollMoreOptions"), \'<br /><label for="options-\' + pollOptionNum + \'">', $txt['smf22'], ' \' + pollOptionNum + \'</label>: <input type="text" name="options[\' + pollOptionNum + \']" id="options-\' + pollOptionNum + \'" value="" size="25" tabindex="\' + pollTabIndex + \'" /><span id="pollMoreOptions"></span>\');
			}';

	if ($context['make_event'])
		echo '
			var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

			function generateDays()
			{
				var days = 0, selected = 0;
				var dayElement = document.getElementById("day"), yearElement = document.getElementById("year"), monthElement = document.getElementById("month");

				monthLength[1] = 28;
				if (yearElement.options[yearElement.selectedIndex].value % 4 == 0)
					monthLength[1] = 29;

				selected = dayElement.selectedIndex;
				while (dayElement.options.length)
					dayElement.options[0] = null;

				days = monthLength[monthElement.value - 1];

				for (i = 1; i <= days; i++)
					dayElement.options[dayElement.length] = new Option(i, i);

				if (selected < days)
					dayElement.selectedIndex = selected;
			}';

	echo '
		// ]]></script>

		<form action="', $scripturl, '?action=', $context['destination'], ';', empty($context['current_board']) ? '' : 'board=' . $context['current_board'], '" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);saveEntities();" enctype="multipart/form-data">
			<table width="75%" align="center" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="bottom" colspan="2">
						', theme_linktree(), '
					</td>
				</tr>
			</table>';

	echo '
		<div id="preview_section"', isset($context['preview_message']) ? '' : ' style="display: none;"', '>
			<table width="75%" cellpadding="3" cellspacing="1" border="0" align="center" class="bordercolor">
				<tr class="titlebg">
					<td id="preview_subject">', empty($context['preview_subject']) ? '' : $context['preview_subject'], '</td>
				</tr>
				<tr>
					<td class="windowbg">
						<div class="post" id="preview_body">', empty($context['preview_message']) ? '' : $context['preview_message'], '</div>
					</td>
				</tr>
			</table><br />
		</div>';

	if ($context['make_event'] && (!$context['event']['new'] || !empty($context['current_board'])))
		echo '
			<input type="hidden" name="eventid" value="', $context['event']['id'], '" />';

	echo '
			<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" class="bordercolor">
				<tr class="titlebg">
					<td>' . $context['page_title'] . '</td>
				</tr>
				<tr>
					<td class="windowbg">' . (isset($context['current_topic']) ? '
						<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '') . '
						<table border="0" cellpadding="3" width="100%">';

	// If an error occurred, explain what happened.
	echo '
							<tr', empty($context['post_error']['messages']) ? ' style="display: none"' : '', ' id="errors">
								<td></td>
								<td align="left">
									<div style="padding: 0px; font-weight: bold;', empty($context['error_type']) || $context['error_type'] != 'serious' ? ' display: none;' : '', '" id="error_serious">
										', $txt['error_while_submitting'], '
									</div>
									<div style="color: red; margin: 1ex 0 2ex 3ex;" id="error_list">
										', empty($context['post_error']['messages']) ? '' : implode('<br />', $context['post_error']['messages']), '
									</div>
								</td>
							</tr>';

	// If it's locked, show a message to warn the replyer.
	echo '
							<tr', $context['locked'] ? '' : ' style="display: none"', ' id="lock_warning">
								<td></td>
								<td align="left">
									', $txt['smf287'], '
								</td>
							</tr>';

	// Guests have to put in their name and email...
	if (isset($context['name']) && isset($context['email']))
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;', isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) || isset($context['post_error']['bad_name']) ? 'color: red;' : '', '" id="caption_guestname">
									', $txt[68], ':
								</td>
								<td>
									<input type="text" name="guestname" size="25" value="', $context['name'], '" />
								</td>
							</tr>';

		if (empty($modSettings['guest_post_no_email']))
			echo '
							<tr>
								<td align="right" style="font-weight: bold;', isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? 'color: red;' : '', '" id="caption_email">
									', $txt[69], ':
								</td>
								<td>
									<input type="text" name="email" size="25" value="', $context['email'], '" />
								</td>
							</tr>';
	}

	// Are you posting a calendar event?
	if ($context['make_event'])
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;', isset($context['post_error']['no_event']) ? 'color: red;' : '', '" id="caption_evtitle">
									', $txt['calendar12'], '
								</td>
								<td class="smalltext">
									<input type="text" name="evtitle" maxlength="30" size="30" value="', $context['event']['title'], '" />
								</td>
							</tr><tr>
								<td></td>
								<td class="smalltext">
									<input type="hidden" name="calendar" value="1" />', $txt['calendar10'], '&nbsp;
									<select name="year" id="year" onchange="generateDays();">';

		// Show a list of all the years we allow...
		for ($year = $modSettings['cal_minyear']; $year <= $modSettings['cal_maxyear']; $year++)
			echo '
										<option value="', $year, '"', $year == $context['event']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

		echo '
									</select>&nbsp;
									', $txt['calendar9'], '&nbsp;
									<select name="month" id="month" onchange="generateDays();">';

		// There are 12 months per year - ensure that they all get listed.
		for ($month = 1; $month <= 12; $month++)
			echo '
										<option value="', $month, '"', $month == $context['event']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

		echo '
									</select>&nbsp;
									', $txt['calendar11'], '&nbsp;
									<select name="day" id="day">';

		// This prints out all the days in the current month - this changes dynamically as we switch months.
		for ($day = 1; $day <= $context['event']['last_day']; $day++)
			echo '
										<option value="', $day, '"', $day == $context['event']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

		echo '
									</select>
								</td>
							</tr>';

		// If events can span more than one day then allow the user to select how long it should last.
		if (!empty($modSettings['cal_allowspan']))
		{
			echo '
							<tr>
								<td align="right"><b>', $txt['calendar54'], '</b></td>
								<td class="smalltext">
									<select name="span">';

			for ($days = 1; $days <= $modSettings['cal_maxspan']; $days++)
				echo '
										<option value="', $days, '"', $days == $context['event']['span'] ? ' selected="selected"' : '', '>', $days, '</option>';

			echo '
									</select>
								</td>
							</tr>';
		}

		// If this is a new event let the user specify which board they want the linked post to be put into.
		if ($context['event']['new'] && $context['is_new_post'])
		{
			echo '
							<tr>
								<td align="right"><b>', $txt['calendar13'], '</b></td>
								<td class="smalltext">
									<select name="board">';

			foreach ($context['event']['boards'] as $board)
				echo '
										<option value="', $board['id'], '"', $board['id'] == $context['event']['board'] ? ' selected="selected"' : '', '>', $board['cat']['name'], ' - ', $board['prefix'], $board['name'], '</option>';

			echo '
									</select>
								</td>
							</tr>';
		}
	}

	// Now show the subject box for this post.
	echo '
							<tr>
								<td align="right" style="font-weight: bold;', isset($context['post_error']['no_subject']) ? 'color: red;' : '', '" id="caption_subject">
									', $txt[70], ':
								</td>
								<td>
									<input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' size="80" maxlength="80" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<b>', $txt[71], ':</b>
								</td>
								<td>
									<select name="icon" id="icon" onchange="showimage()">';

	// Loop through each message icon allowed, adding it to the drop down list.
	foreach ($context['icons'] as $icon)
		echo '
										<option value="', $icon['value'], '"', $icon['value'] == $context['icon'] ? ' selected="selected"' : '', '>', $icon['name'], '</option>';

	echo '
									</select>
									<img src="', $context['icon_url'], '" name="icons" border="0" hspace="15" alt="" />
								</td>
							</tr>';

	// If this is a poll then display all the poll options!
	if ($context['make_poll'])
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;', isset($context['post_error']['no_question']) ? 'color: red;' : '', '" id="caption_question">
									', $txt['smf21'], ':
								</td>
								<td align="left">
									<input type="text" name="question" value="', isset($context['question']) ? $context['question'] : '', '" size="80" />
								</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td>';

		// Loop through all the choices and print them out.
		foreach ($context['choices'] as $choice)
		{
			echo '
									<label for="options-', $choice['id'], '">', $txt['smf22'], ' ', $choice['number'], '</label>: <input type="text" name="options[', $choice['id'], ']" id="options-', $choice['id'], '" value="', $choice['label'], '" size="25" />';

			if (!$choice['is_last'])
				echo '<br />';
		}

		echo '
									<span id="pollMoreOptions"></span> <a href="javascript:addPollOption(); void(0);">(', $txt['poll_add_option'], ')</a>
								</td>
							</tr>
							<tr>
								<td align="right"><b>', $txt['poll_options'], ':</b></td>
								<td class="smalltext"><input type="text" name="poll_max_votes" size="2" value="', $context['poll_options']['max_votes'], '" /> ', $txt['poll_options5'], '</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext">', $txt['poll_options1a'], ' <input type="text" id="poll_expire" name="poll_expire" size="2" value="', $context['poll_options']['expire'], '" onchange="pollOptions();" /> ', $txt['poll_options1b'], '</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext"><label for="poll_change_vote"><input type="checkbox" id="poll_change_vote" name="poll_change_vote"', !empty($context['poll_options']['change_vote']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_options7'], '</label></td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext">
									<input type="radio" id="poll_hide" name="poll_hide" value="0"', $context['poll_options']['hide'] == 0 ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_options2'], '<br />
									<input type="radio" id="poll_hide" name="poll_hide" value="1"', $context['poll_options']['hide'] == 1 ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_options3'], '<br />
									<input type="radio" id="poll_hide" name="poll_hide" value="2"', $context['poll_options']['hide'] == 2 ? ' checked="checked"' : '', empty($context['poll_options']['expire']) ? ' disabled="disabled"' : '', ' class="check" /> ', $txt['poll_options4'], '<br />
									<br />
								</td>
							</tr>';
	}

	// The below function prints the BBC, smileys and the message itself out.
	theme_postbox($context['message']);

	// If this message has been edited in the past - display when it was.
	if (isset($context['last_modified']))
		echo '
									<tr>
										<td valign="top" align="right">
											<b>', $txt[211], ':</b>
										</td>
										<td>
											', $context['last_modified'], '
										</td>
									</tr>';

	// If the admin has enabled the hiding of the additional options - show a link and image for it.
	if (!empty($settings['additional_options_collapsable']))
		echo '
									<tr>
										<td colspan="2" style="padding-left: 5ex;">
											<a href="javascript:swapOptions();"><img src="', $settings['images_url'], '/expand.gif" alt="+" border="0" id="postMoreExpand" /></a> <a href="javascript:swapOptions();"><b>', $txt['post_additionalopt'], '</b></a>
										</td>
									</tr>';

	// Display the check boxes for all the standard options - if they are available to the user!
	echo '
									<tr>
										<td></td>
										<td>
											<div id="postMoreOptions">
												<table width="80%" cellpadding="0" cellspacing="0" border="0">
													<tr>
														<td class="smalltext">', $context['can_notify'] ? '<input type="hidden" name="notify" value="0" /><label for="check_notify"><input type="checkbox" name="notify" id="check_notify"' . ($context['notify'] || !empty($options['auto_notify']) ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['smf14'] . '</label>' : '', '</td>
														<td class="smalltext">', $context['can_lock'] ? '<input type="hidden" name="lock" value="0" /><label for="check_lock"><input type="checkbox" name="lock" id="check_lock"' . ($context['locked'] ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['smf15'] . '</label>' : '', '</td>
													</tr>
													<tr>
														<td class="smalltext"><label for="check_back"><input type="checkbox" name="goback" id="check_back"' . ($context['back_to_topic'] || !empty($options['return_to_post']) ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['back_to_topic'] . '</label></td>
														<td class="smalltext">', $context['can_sticky'] ? '<input type="hidden" name="sticky" value="0" /><label for="check_sticky"><input type="checkbox" name="sticky" id="check_sticky"' . ($context['sticky'] ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['sticky_after2'] . '</label>' : '', '</td>
													</tr>
													<tr>
														<td class="smalltext"><label for="check_smileys"><input type="checkbox" name="ns" id="check_smileys"', $context['use_smileys'] ? '' : ' checked="checked"', ' value="NS" class="check" /> ', $txt[277], '</label></td>', '
														<td class="smalltext">', $context['can_move'] ? '<input type="hidden" name="move" value="0" /><label for="check_move"><input type="checkbox" name="move" id="check_move" value="1" class="check" /> ' . $txt['move_after2'] . '</label>' : '', '</td>
													</tr>', $context['can_announce'] && $context['is_first_post'] ? '
													<tr>
														<td class="smalltext"><label for="check_announce"><input type="checkbox" name="announce_topic" id="check_announce" value="1" class="check" /> ' . $txt['announce_topic'] . '</label></td>
														<td class="smalltext"></td>
													</tr>' : '', '
												</table>
											</div>
										</td>
									</tr>';

	// If this post already has attachments on it - give information about them.
	if (!empty($context['current_attachments']))
	{
		echo '
							<tr id="postAttachment">
								<td align="right" valign="top">
									<b>', $txt['smf119b'], ':</b>
								</td>
								<td class="smalltext">
									<input type="hidden" name="attach_del[]" value="0" />
									', $txt['smf130'], ':<br />';
		foreach ($context['current_attachments'] as $attachment)
			echo '
									<input type="checkbox" name="attach_del[]" value="', $attachment['id'], '"', empty($attachment['unchecked']) ? ' checked="checked"' : '', ' class="check" /> ', $attachment['name'], '<br />';
		echo '
									<br />
								</td>
							</tr>';
	}

	// Is the user allowed to post any additional ones? If so give them the boxes to do it!
	if ($context['can_post_attachment'])
	{
		echo '
							<tr id="postAttachment2">
								<td align="right" valign="top">
									<b>', $txt['smf119'], ':</b>
								</td>
								<td class="smalltext">
									<input type="file" size="48" name="attachment[]" />';

		// Show more boxes only if they aren't approaching their limit.
		if ($context['num_allowed_attachments'] > 1)
			echo '
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										var allowed_attachments = ', $context['num_allowed_attachments'], ' - 1;

										function addAttachment()
										{
											if (allowed_attachments <= 0)
												return alert("', $txt['more_attachments_error'], '");

											setOuterHTML(document.getElementById("moreAttachments"), \'<br /><input type="file" size="48" name="attachment[]" /><span id="moreAttachments"></span>\');
											allowed_attachments = allowed_attachments - 1;

											return true;
										}
									// ]]></script>
									<span id="moreAttachments"></span> <a href="javascript:addAttachment(); void(0);">(', $txt['more_attachments'], ')</a><br />
									<noscript><input type="file" size="48" name="attachment[]" /><br /></noscript>';
		else
			echo '
									<br />';

		// Show some useful information such as allowed extensions, maximum size and amount of attachments allowed.
		if (!empty($modSettings['attachmentCheckExtensions']))
			echo '
									', $txt['smf120'], ': ', $context['allowed_extensions'], '<br />';
		echo '
									', $txt['smf121'], ': ', $modSettings['attachmentSizeLimit'], ' ' . $txt['smf211'], !empty($modSettings['attachmentNumPerPostLimit']) ? ', ' . $txt['maxAttachPerPost'] . ': ' . $modSettings['attachmentNumPerPostLimit'] : '', '
								</td>
							</tr>';
	}

	// Finally, the submit buttons - and some javascript to hide the additional options on load.
	echo '
							<tr>
								<td align="center" colspan="2">';
	if (!empty($settings['additional_options_collapsable']) && !$context['show_additional_options'])
		echo '
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										swapOptions();
									// ]]></script>';
	echo '
									<span class="smalltext"><br />' . $txt['smf16'] . '</span><br />
									<input type="submit" name="post" value="', $context['submit_label'], '" onclick="return submitThisOnce(this);" accesskey="s" />
									<input type="submit" name="preview" value="', $txt[507], '" onclick="return event.ctrlKey || previewPost();" accesskey="p" />';

	// Option to delete an event if user is editing one.
	if ($context['make_event'] && !$context['event']['new'])
		echo '
									<input type="submit" name="deleteevent" value="', $txt['calendar22'], '" onclick="return confirm(\'', $txt['calendar21'], '\');" />';

	// Spell check button if the option is enabled.
	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'postmodify\', \'message\');" />';

	echo '
								</td>
							</tr>
							<tr>
								<td colspan="2"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>';

	// Assuming this isn't a new topic pass across the number of replies when the topic was created.
	if (isset($context['num_replies']))
		echo '
			<input type="hidden" name="num_replies" value="', $context['num_replies'], '" />';

	echo '
			<input type="hidden" name="additional_options" value="', $context['show_additional_options'] ? 1 : 0, '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
		</form>';

	// A hidden form to post data to the spell checking window.
	if ($context['show_spellchecking'])
		echo '
		<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

	// If the user is replying to a topic show the previous posts.
	if (isset($context['previous_posts']) && count($context['previous_posts']) > 0)
	{
		echo '
			<br /><br />

			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				function insertQuoteFast(messageid)
				{
					if (window.XMLHttpRequest)
						getXMLDocument("', $scripturl, '?action=quotefast;quote=" + messageid + ";sesc=', $context['session_id'], ';xml", onDocReceived);
					else
						reqWin("', $scripturl, '?action=quotefast;quote=" + messageid + ";sesc=', $context['session_id'], '", 240, 90);

					return true;
				}
				function onDocReceived(XMLDoc)
				{
					var text = "";
					for (var i = 0; i < XMLDoc.getElementsByTagName("quote")[0].childNodes.length; i++)
						text += XMLDoc.getElementsByTagName("quote")[0].childNodes[i].nodeValue;

					replaceText(text, document.forms.postmodify.message);
				}
			// ]]></script>

			<table cellspacing="1" cellpadding="0" width="75%" align="center" class="bordercolor">
				<tr>
					<td>
						<table class="windowbg" cellspacing="0" cellpadding="2" width="100%" align="center">
							<tr class="titlebg">
								<td colspan="2">' . $txt[468] . '</td>
							</tr>
						</table>
						<span id="new_replies"></span>
						<table class="windowbg" cellspacing="0" cellpadding="2" width="100%" align="center">';
		foreach ($context['previous_posts'] as $post)
			echo '
							<tr class="catbg">
								<td align="left" class="smalltext">
									', $txt[279], ': ', $post['poster'], '
								</td>
								<td align="right" class="smalltext">
									', $txt[280], ': ', $post['time'], $post['is_new'] ? ' <img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif" alt="' . $txt['preview_new'] . '" border="0" />' : '', '
								</td>
							</tr><tr class="windowbg2">
								<td align="right" colspan="2" class="smalltext">
									<a href="#top" onclick="return insertQuoteFast(', $post['id'], ');">' . $txt[260] . '</a>
								</td>
							</tr><tr class="windowbg2">
								<td colspan="2" class="smalltext" id="msg' . $post['id'] . '">
									<div class="post">' . $post['message'] . '</div>
								</td>
							</tr>';
		echo '
						</table>
					</td>
				</tr>
			</table>';
	}
}

function template_postbox(&$message)
{
	global $context, $settings, $options, $txt;

	if ($context['show_bbc'])
	{
		echo '
			<tr>
				<td align="right" valign="top">
					<b>' . $txt[252] . ':</b>
				</td>
				<td align="left" valign="middle">';

		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt[253]),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt[254]),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt[255]),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt[441]),
			array(),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt[442]),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt[443]),
			'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt[439]),
			array(),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt[444]),
			'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt[445]),
			'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt[256]),
			'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt[446]),
			array(),
			'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt[531]),
			array(),
			'size' => array('code' => 'size', 'before' => '[size=10pt]', 'after' => '[/size]', 'description' => $txt[532]),
			'face' => array('code' => 'font', 'before' => '[font=Verdana]', 'after' => '[/font]', 'description' => $txt[533]),
		);
		$context['bbc_tags'][] = array(
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]', 'after' => '[/flash]', 'description' => $txt[433]),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt[435]),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt[257]),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt[258]),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp]', 'after' => '[/ftp]', 'description' => $txt[434]),
			array(),
			'table' => array('code' => 'table', 'before' => '[table]', 'after' => '[/table]', 'description' => $txt[436]),
			'tr' => array('code' => 'td', 'before' => '[tr]', 'after' => '[/tr]', 'description' => $txt[449]),
			'td' => array('code' => 'td', 'before' => '[td]', 'after' => '[/td]', 'description' => $txt[437]),
			array(),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt[447]),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt[448]),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt[440]),
			array(),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt[259]),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt[260]),
			array(),
			'list' => array('code' => 'list', 'before' => '[list]\n[li]', 'after' => '[/li]\n[li][/li]\n[/list]', 'description' => $txt[261]),
		);

		foreach ($context['bbc_tags'] as $i => $row)
		{
			foreach ($row as $image => $tag)
			{
				// Is this tag disabled?
				if (!empty($tag['code']) && !empty($context['disabled_tags'][$tag['code']]))
					continue;

				if (isset($tag['before']))
					echo '<a href="javascript:' . (isset($tag['after']) ? 'surround' : 'replace') . 'Text(\'' . $tag['before'] . '\'' . (isset($tag['after']) ? ', \'' . $tag['after'] . '\'' : '') . ', document.forms.' . $context['post_form'] . '.' . $context['post_box_name'] . ');"><img src="' . $settings['images_url'] . '/bbc/' . $image . '.gif" align="bottom" width="23" height="22" alt="' . $tag['description'] . '" border="0" /></a>';
			}

			if ($i != count($context['bbc_tags']) - 1)
				echo '<br />';
		}

		echo '
					<select onchange="surroundText(\'[color=\' + this.options[this.selectedIndex].value.toLowerCase() + \']\',\'[/color]\', document.forms.' . $context['post_form'] . '.' . $context['post_box_name'] . '); this.selectedIndex = 0;">
						<option value="" selected="selected">', $txt['change_color'], '</option>
						<option value="Black">' . $txt[262] . '</option>
						<option value="Red">' . $txt[263] . '</option>
						<option value="Yellow">' . $txt[264] . '</option>
						<option value="Pink">' . $txt[265] . '</option>
						<option value="Green">' . $txt[266] . '</option>
						<option value="Orange">' . $txt[267] . '</option>
						<option value="Purple">' . $txt[268] . '</option>
						<option value="Blue">' . $txt[269] . '</option>
						<option value="Beige">' . $txt[270] . '</option>
						<option value="Brown">' . $txt[271] . '</option>
						<option value="Teal">' . $txt[272] . '</option>
						<option value="Navy">' . $txt[273] . '</option>
						<option value="Maroon">' . $txt[274] . '</option>
						<option value="LimeGreen">' . $txt[275] . '</option>
					</select>
				</td>
			</tr>';
	}
	// Now start printing all of the smileys.
	if (!empty($context['smileys']['postform']))
	{
		echo '
			<tr>
				<td align="right"></td>
				<td valign="middle">';

		// Show each row of smileys ;).
		foreach ($context['smileys']['postform'] as $smiley_row)
		{
			foreach ($smiley_row['smileys'] as $smiley)
				echo '
					<a href="javascript:replaceText(\' ', $smiley['code'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], ');"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" align="bottom" alt="', $smiley['description'], '" title="', $smiley['description'], '" border="0" /></a>';

			// If this isn't the last row, show a break.
			if (empty($smiley_row['last']))
				echo '<br />';
		}

		// If the smileys popup is to be shown... show it!
		if (!empty($context['smileys']['popup']))
			echo '
					<a href="javascript:moreSmileys();">[', $txt['more_smileys'], ']</a>';

		echo '
				</td>
			</tr>';
	}

	// Show an extra link for additional smileys (if there are any).
	if (!empty($context['smileys']['popup']))
	{
		echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var smileys = [';

		foreach ($context['smileys']['popup'] as $smiley_row)
		{
			echo '
					[';
			foreach ($smiley_row['smileys'] as $smiley)
			{
				echo '
						["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['description'], '"]';
				if (empty($smiley['last']))
					echo ',';
			}

			echo ']';
			if (empty($smiley_row['last']))
				echo ',';
		}

		echo '];
				var smileyPopupWindow;

				function moreSmileys()
				{
					var row, i;

					if (smileyPopupWindow)
						smileyPopupWindow.close();

					smileyPopupWindow = window.open("", "add_smileys", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=200,resizable=no");
					smileyPopupWindow.document.write(\'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>\');
					smileyPopupWindow.document.write(\'\n\t<head>\n\t\t<title>', $txt['more_smileys_title'], '</title>\n\t\t<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />\n\t</head>\');
					smileyPopupWindow.document.write(\'\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">', $txt['more_smileys_pick'], '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">\');

					for (row = 0; row < smileys.length; row++)
					{
						for (i = 0; i < smileys[row].length; i++)
						{
							smileys[row][i][2] = smileys[row][i][2].replace(/"/g, \'&quot;\');
							smileyPopupWindow.document.write(\'<a href="javascript:void(0);" onclick="window.opener.replaceText(&quot; \' + smileys[row][i][0] + \'&quot;, window.opener.document.forms.', $context['post_form'], '.', $context['post_box_name'], '); window.focus(); return false;"><img src="', $settings['smileys_url'], '/\' + smileys[row][i][1] + \'" alt="\' + smileys[row][i][2] + \'" title="\' + smileys[row][i][2] + \'" style="padding: 4px;" border="0" /></a> \');
						}
						smileyPopupWindow.document.write("<br />");
					}

					smileyPopupWindow.document.write(\'</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();\\">', $txt['more_smileys_close_window'], '</a></td></tr>\n\t\t</table>\n\t</body>\n</html>\');
					smileyPopupWindow.document.close();
				}
			// ]]></script>';
	}

	echo '
			<tr>
				<td valign="top" align="right">
					<b' . (isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? ' style="color: red;"' : '') . '>' . $txt[72] . ':</b>
				</td>
				<td>
					<textarea class="editor" name="' . $context['post_box_name'] . '" rows="' . $context['post_box_rows'] . '" cols="' . $context['post_box_columns'] . '" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="', $context['tabindex']++, '">' . $message . '</textarea>
				</td>
			</tr>';
}

function template_spellcheck()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $txt['spell_check'], '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<style type="text/css">
			body, td
			{
				font-size: small;
				margin: 0;
			}
			.highlight
			{
				color: red;
				font-weight: bold;
			}
			#spellview
			{
				border-style: outset;
				border: 1px solid black;
				padding: 5px;
				width: 98%;
				height: 344px;
				overflow: auto;
			}';

	if ($context['browser']['needs_size_fix'])
		echo '
			@import(', $settings['default_theme_url'], '/fonts-compat.css);';

	echo '
		</style>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var spell_formname = window.opener.spell_formname;
			var spell_fieldname = window.opener.spell_fieldname;
		// ]]></script>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/script.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			', $context['spell_js'], '
		// ]]></script>
	</head>
	<body onload="nextWord(false);">
		<form action="#" method="post" accept-charset="', $context['character_set'], '" name="spellingForm" id="spellingForm" onsubmit="return false;" style="margin: 0;">
			<div id="spellview">&nbsp;</div>
			<table border="0" cellpadding="4" cellspacing="0" width="100%"><tr class="windowbg">
				<td width="50%" valign="top">
					', $txt['spellcheck_change_to'], '<br />
					<input type="text" name="changeto" style="width: 98%;" />
				</td>
				<td width="50%">
					', $txt['spellcheck_suggest'], '<br />
					<select name="suggestions" style="width: 98%;" size="5" onclick="if (this.selectedIndex != -1) this.form.changeto.value = this.options[this.selectedIndex].text;" ondblclick="replaceWord();">
					</select>
				</td>
			</tr></table>
			<div class="titlebg" align="right" style="padding: 4px;">
				<input type="button" name="change" value="', $txt['spellcheck_change'], '" onclick="replaceWord();" />
				<input type="button" name="changeall" value="', $txt['spellcheck_change_all'], '" onclick="replaceAll();" />
				<input type="button" name="ignore" value="', $txt['spellcheck_ignore'], '" onclick="nextWord(false);" />
				<input type="button" name="ignoreall" value="', $txt['spellcheck_ignore_all'], '" onclick="nextWord(true);" />
			</div>
		</form>
	</body>
</html>';
}

function template_quotefast()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $txt['retrieving_quote'], '</title>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/script.js"></script>
	</head>
	<body>
		', $txt['retrieving_quote'], '
		<div id="temporary_posting_area" style="display: none;"></div>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	if ($context['close_window'])
		echo '
			window.close();';
	else
	{
		// Lucky for us, Internet Explorer has an "innerText" feature which basically converts entities <--> text.  Use it if possible ;).
		echo '
			var quote = \'', $context['quote']['text'], '\';
			var stage = document.createElement ? document.createElement("DIV") : document.getElementById("temporary_posting_area");

			if (typeof(DOMParser) != "undefined" && typeof(window.opera) == "undefined")
			{
				var xmldoc = new DOMParser().parseFromString("<temp>" + \'', $context['quote']['mozilla'], '\'.replace(/\n/g, "_SMF-BREAK_").replace(/\t/g, "_SMF-TAB_") + "</temp>", "text/xml");
				quote = xmldoc.childNodes[0].textContent.replace(/_SMF-BREAK_/g, "\n").replace(/_SMF-TAB_/g, "\t");
			}
			else if (typeof(stage.innerText) != "undefined")
			{
				setInnerHTML(stage, quote.replace(/\n/g, "_SMF-BREAK_").replace(/\t/g, "_SMF-TAB_").replace(/</g, "&lt;").replace(/>/g, "&gt;"));
				quote = stage.innerText.replace(/_SMF-BREAK_/g, "\n").replace(/_SMF-TAB_/g, "\t");
			}

			if (typeof(window.opera) != "undefined")
				quote = quote.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, \'"\').replace(/&amp;/g, "&");

			window.opener.replaceText(quote, window.opener.document.forms.postmodify.message);

			window.focus();
			setTimeout("window.close();", 400);';
	}
	echo '
		// ]]></script>
	</body>
</html>';
}

function template_announce()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=announce;sa=send" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt['announce_title'], '</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt['announce_desc'], '</td>
				</tr><tr>
					<td class="windowbg2">
						', $txt['announce_this_topic'], ' <a href="', $scripturl, '?topic=', $context['current_topic'], '.0">', $context['topic_subject'], '</a><br />
					</td>
				</tr><tr>
					<td class="windowbg2">';

	foreach ($context['groups'] as $group)
				echo '
						<label for="who_', $group['id'], '"><input type="checkbox" name="who[', $group['id'], ']" id="who_', $group['id'], '" value="', $group['id'], '" checked="checked" class="check" /> ', $group['name'], '</label> <i>(', $group['member_count'], ')</i><br />';

	echo '
						<br />
						<label for="checkall"><input type="checkbox" id="checkall" class="check" onclick="invertAll(this, this.form);" checked="checked" /> <i>', $txt[737], '</i></label>
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" value="', $txt[105], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="topic" value="', $context['current_topic'], '" />
			<input type="hidden" name="move" value="', $context['move'], '" />
			<input type="hidden" name="goback" value="', $context['go_back'], '" />
		</form>';
}

function template_announcement_send()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="' . $scripturl . '?action=announce;sa=send" method="post" accept-charset="', $context['character_set'], '" name="autoSubmit" id="autoSubmit">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						', $txt['announce_sending'], ' <a href="', $scripturl, '?topic=', $context['current_topic'], '.0" target="_blank">', $context['topic_subject'], '</a>
					</td>
				</tr><tr>
					<td class="windowbg2"><b>', $context['percentage_done'], '% ', $txt['announce_done'], '</b></td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" name="b" value="', $txt['announce_continue'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="topic" value="', $context['current_topic'], '" />
			<input type="hidden" name="move" value="', $context['move'], '" />
			<input type="hidden" name="goback" value="', $context['go_back'], '" />
			<input type="hidden" name="start" value="', $context['start'], '" />
			<input type="hidden" name="membergroups" value="', $context['membergroups'], '" />
		</form>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var countdown = 2;
			doAutoSubmit();

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.forms.autoSubmit.submit();
				else if (countdown == -1)
					return;

				document.forms.autoSubmit.b.value = "', $txt['announce_continue'], ' (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
}

?>