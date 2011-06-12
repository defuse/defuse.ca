<?php
// Version: 1.1.11; SplitTopics

function template_ask()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=splittopics;sa=execute;topic=', $context['current_topic'], '.0" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="at" value="', $context['message']['id'], '" />
		<table border="0" width="400" cellspacing="0" cellpadding="3" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['smf251'], '</td>
			</tr><tr class="windowbg">
				<td align="center" style="padding-top: 2ex; padding-bottom: 1ex;">
					<b><label for="subname">', $txt['smf254'], '</label>:</b> <input type="text" name="subname" id="subname" value="', $context['message']['subject'], '" size="25" /><br />
					<br />
					<input type="radio" name="step2" value="onlythis" checked="checked" class="check" /> ', $txt['smf255'], '<br />
					<input type="radio" name="step2" value="afterthis" class="check" /> ', $txt['smf256'], '<br />
					<input type="radio" name="step2" value="selective" class="check" /> ', $txt['smf257'], '<br />
					<br />
					<input type="submit" value="', $txt['smf251'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="400" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
			<tr class="titlebg">
				<td>', $txt['smf251'], '</td>
			</tr><tr>
				<td class="windowbg" valign="middle" align="center">
					', $txt['smf259'], '<br /><br />
					<a href="', $scripturl, '?board=', $context['current_board'], '.0">', $txt[101], '</a><br />
					<a href="', $scripturl, '?topic=', $context['old_topic'], '.0">', $txt['smf260'], '</a><br />
					<a href="', $scripturl, '?topic=', $context['new_topic'], '.0">', $txt['smf258'], '</a>
				</td>
			</tr>
		</table>';
}

function template_select()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=splittopics;sa=splitSelection;board=', $context['current_board'], '.0" method="post" accept-charset="', $context['character_set'], '"><input type="hidden" name="topic" value="', $context['current_topic'], '" />
		<table width="100%"><tr><td colspan="2" align="center">
			<input type="hidden" name="subname" value="', $context['new_subject'], '" />
			<input type="submit" value="', $txt['smf251'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</td></tr><tr><td valign="top" width="50%">
			<table id="table_not_selected" border="0" width="98%" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['smf251'], ' - ', $txt['smf257'], '
					</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2" valign="middle">
						', $txt['smf261'], '
					</td>
				</tr>
				<tr class="catbg">
					<td colspan="2" height="18">
						<b>', $txt[139], ':</b> <span id="pageindex_not_selected">', $context['not_selected']['page_index'], '</span>
					</td>
				</tr>';
	foreach ($context['not_selected']['messages'] as $message)
		echo '
				<tr class="windowbg" id="not_selected_', $message['id'], '">
					<td class="smalltext">
						', $message['subject'], ' - ', $message['poster'], '
						<div class="post">', $message['body'], '</div>
					</td>
					<td valign="middle" align="center" width="5%"><a href="', $scripturl, '?action=splittopics;sa=selectTopics;subname=', $context['topic']['subject'], ';topic=', $context['topic']['id'], '.', $context['not_selected']['start'], ';start2=', $context['selected']['start'], ';move=down;msg=', $message['id'], '" onclick="return select(\'down\', ', $message['id'], ');"><img src="', $settings['images_url'], '/split_select.gif" alt="-&gt;" /></a></td>
				</tr>';
	echo '
			</table>
		</td><td valign="top" width="50%">
			<table id="table_selected" border="0" width="98%" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['split_selected_posts'], ' (<a href="', $scripturl, '?action=splittopics;sa=selectTopics;subname=', $context['topic']['subject'], ';topic=', $context['topic']['id'], '.', $context['not_selected']['start'], ';start2=', $context['selected']['start'], ';move=reset;msg=0" onclick="return select(\'reset\', 0);">', $txt['split_reset_selection'], '</a>)
					</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2" valign="middle">
						', $txt['split_selected_posts_desc'], '
					</td>
				</tr>
				<tr class="catbg">
					<td colspan="2" height="18">
						<b>', $txt[139], ':</b> <span id="pageindex_selected">', $context['selected']['page_index'], '</span>
					</td>
				</tr>';
	if (!empty($context['selected']['messages']))
		foreach ($context['selected']['messages'] as $message)
			echo '
				<tr class="windowbg" id="selected_', $message['id'], '">
					<td width="5%" valign="middle" align="center"><a href="', $scripturl, '?action=splittopics;sa=selectTopics;subname=', $context['topic']['subject'], ';topic=', $context['topic']['id'], '.', $context['not_selected']['start'], ';start2=', $context['selected']['start'], ';move=up;msg=', $message['id'], '" onclick="return select(\'up\', ', $message['id'], ');"><img src="', $settings['images_url'], '/split_deselect.gif" alt="&lt;-" /></a></td>
					<td class="smalltext">
						', $message['subject'], ' - ', $message['poster'], '
						<div class="post">', $message['body'], '</div>
					</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>
		</form>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var start = new Array();
			start[0] = ', $context['not_selected']['start'], ';
			start[1] = ', $context['selected']['start'], ';

			function select(direction, msg_id)
			{
				if (window.XMLHttpRequest)
				{
					getXMLDocument("', $scripturl, '?action=splittopics;sa=selectTopics;subname=', $context['topic']['subject'], ';topic=', $context['topic']['id'], '." + start[0] + ";start2=" + start[1] + ";move=" + direction + ";msg=" + msg_id + ";xml", onDocReceived);
					return false;
				}
				else
					return true;
			}
			function onDocReceived(XMLDoc)
			{
				var i, j, pageIndex;
				for (i = 0; i < 2; i++)
				{
					pageIndex = XMLDoc.getElementsByTagName("pageIndex")[i];
					setInnerHTML(document.getElementById("pageindex_" + pageIndex.getAttribute("section")), pageIndex.firstChild.nodeValue);
					start[i] = pageIndex.getAttribute("startFrom");
				}
				var numChanges = XMLDoc.getElementsByTagName("change").length, curChange;
				var curSection, curAction, curId, curTable, curRow, curRowIndex, buttonCell, textCell, curData, numRows;
				for (i = 0; i < numChanges; i++)
				{
					curChange = XMLDoc.getElementsByTagName("change")[i];
					curSection = curChange.getAttribute("section");
					curAction = curChange.getAttribute("curAction");
					curId = curChange.getAttribute("id");
					curTable = document.getElementById("table_" + curSection);
					numRows = curTable.rows.length;
					if (curAction == "remove")
						curTable.deleteRow(document.getElementById(curSection + "_" + curId).rowIndex);
					// Insert a message.
					else
					{
						// By default insert the row at the end of the table.
						curRowIndex = -1;
						for (j = curSection == "selected" ? 2 : 3; j < numRows; j++)
						{
							if (parseInt(curTable.rows[j].id.substr(curSection.length + 1)) < curId)
							{
								// This would be a nice place to insert the row.
								curRowIndex = j;
								// We\'re done for now. Escape the loop.
								j = numRows + 1;
							}
						}
						curRow = curTable.insertRow(curRowIndex);
						curRow.className = "windowbg";
						curRow.id = curSection + "_" + curId;
						if (curSection == "selected")
						{
							buttonCell = curRow.insertCell(-1);
							textCell = curRow.insertCell(-1);
						}
						else
						{
							textCell = curRow.insertCell(-1);
							buttonCell = curRow.insertCell(-1);
						}
						setInnerHTML(buttonCell, "<a href=\\"', $scripturl, '?action=splittopics;sa=selectTopics;subname=', $context['topic']['subject'], ';topic=', $context['topic']['id'], '.', $context['not_selected']['start'], ';start2=', $context['selected']['start'], ';move=" + (curSection == "selected" ? "up" : "down") + ";msg=" + curId + "\\" onclick=\\"return select(\'" + (curSection == "selected" ? "up" : "down") + "\', " + curId + ");\\"><img src=\\"', $settings['images_url'], '/split_" + (curSection == "selected" ? "de" : "") + "select.gif\\" alt=\\"" + (curSection == "selected" ? "&lt;-" : "-&gt;") + "\\" border=\\"0\\" /></a>");
						buttonCell.width = "5%";
						buttonCell.vAlign = "middle";
						buttonCell.align = "center";
						setInnerHTML(textCell, curChange.getElementsByTagName("subject")[0].firstChild.nodeValue + " - " + curChange.getElementsByTagName("poster")[0].firstChild.nodeValue + "<div class=\\"post\\">" + curChange.getElementsByTagName("body")[0].firstChild.nodeValue + "</div>");
						textCell.className = "smalltext";
						// !!! Should something be here?
						//textCell.alt
					}
				}
			}
		// ]]></script>';
}

function template_merge_done()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="400" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
			<tr class="titlebg">
				<td>' . $txt['smf252'] . '</td>
			</tr><tr>
				<td class="windowbg" valign="middle" align="center">
					<br />
					' . $txt['smf264'] . '<br />
					<br />
					<a href="' . $scripturl . '?board=' . $context['target_board'] . '.0">' . $txt[101] . '</a><br />
					<a href="' . $scripturl . '?topic=' . $context['target_topic'] . '.0">' . $txt['smf265'] . '</a>
				</td>
			</tr>
		</table>';
}

function template_merge()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="' . $scripturl . '?action=mergetopics;from=' . $context['origin_topic'] . ';targetboard=' . $context['target_board'] . ';board=' . $context['current_board'] . '.0" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="540" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
				<tr class="catbg3">
					<td>' . $txt['smf252'] . '</td>
				</tr>
				<tr>
					<td class="windowbg">' . $txt['smf276'] . '</td>
				</tr>
				<tr>
					<td colspan="2" class="titlebg">
						<table cellpadding="0" cellspacing="0" border="0"><tr>
							<td><b>' . $txt[139] . ':</b> ' . $context['page_index'] . '</td>
						</tr></table>
					</td>
				</tr>
				<tr>
					<td class="windowbg" valign="middle" align="center">
						<table border="0">
							<tr>
								<td align="right"><br /><b>' . $txt['smf266'] . ':</b> <br /></td>
								<td align="left"><input type="hidden" name="from" value="' . $context['origin_topic'] . '" /><br />' . $context['origin_subject'] . '</td>
							</tr><tr>';

	if (!empty($context['boards']) && count($context['boards']) > 1)
	{
		echo '
								<td align="right"><br /><b>' . $txt['smf267'] . ':</b></td>
								<td align="left">
									<br />
									<select name="targetboard" onchange="this.form.submit();">';
		foreach ($context['boards'] as $board)
			echo '
										<option value="', $board['id'], '"', $board['id'] == $context['target_board'] ? ' selected="selected"' : '', '>', $board['category'], ' - ', $board['name'], '</option>';
		echo '
									</select> <noscript><input type="submit" value="', $txt[462], '" /></noscript>
								</td>';
	}

	echo '
							</tr><tr>
								<td align="right" valign="top"><br /><b>' . $txt['smf269'] . ':</b></td>
								<td align="left" style="white-space: nowrap;">
									<br />
									<table>';

	$merge_button = create_button('merge.gif', 'smf252', '');
	foreach ($context['topics'] as $topic)
		echo '
										<tr>
											<td valign="bottom">
												<a href="' . $scripturl . '?action=mergetopics;sa=options;board=' . $context['current_board'] . '.0;from=' . $context['origin_topic'] . ';to=' . $topic['id'] . ';sesc=' . $context['session_id'] . '">' . $merge_button . '</a>&nbsp;
											</td>
											<td valign="middle" style="white-space: nowrap;">
												<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0" target="_blank">' . $topic['subject'] . '</a> ' . $txt[109] . ' ' . $topic['poster']['link'] . '
											</td>
										</tr>';
	echo '
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="titlebg">
						<table cellpadding="0" cellspacing="0" border="0"><tr>
							<td><b>' . $txt[139] . ':</b> ' . $context['page_index'] . '</td>
						</tr></table>
					</td>
				</tr>
			</table>
		</form>';
}

function template_merge_extra_options()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=mergetopics;sa=execute;" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="100%" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
				<tr class="titlebg">
					<td>', $txt['smf252'], '</td>
				</tr><tr>
					<td class="catbg">', $txt['merge_topic_list'], '</td>
				</tr><tr>
					<td class="windowbg" style="padding: 15px;">
						<table border="0" cellspacing="1" cellpadding="2" width="100%" align="center" class="bordercolor">
							<tr class="titlebg">
								<td>' . $txt['merge_check'] . '</td>
								<td>' . $txt[70] . '</td>
								<td>' . $txt[109] . '</td>
								<td>' . $txt[111] . '</td>
								<td width="70">' . $txt['merge_include_notifications'] . '</td>
							</tr>';
	foreach ($context['topics'] as $topic)
		echo '
							<tr>
								<td class="windowbg2" valign="middle">
									<input type="checkbox" class="check" name="topics[]" value="' . $topic['id'] . '" checked="checked" />
								</td>
								<td class="windowbg2" valign="middle">
									<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0" target="_blank">' . $topic['subject'] . '</a>
								</td>
								<td class="windowbg2" valign="middle">
									' . $topic['started']['link'] . '<br />
									<span class="smalltext">' . $topic['started']['time'] . '</span>
								</td>
								<td class="windowbg2" valign="middle">
									' . $topic['updated']['link'] . '<br />
									<span class="smalltext">' . $topic['updated']['time'] . '</span>
								</td>
								<td class="windowbg2" valign="middle">
									<input type="checkbox" class="check" name="notifications[]" value="' . $topic['id'] . '" checked="checked" />
								</td>
							</tr>';
	echo '
						</table>
						<br />
						<br />';

	echo '
						', $txt['merge_select_subject'], ': <select name="subject" onchange="this.form.customSubject.disabled = this.options[this.selectedIndex].value != 0;">';
	foreach ($context['topics'] as $topic)
		echo '
							<option value="', $topic['id'], '"' . ($topic['selected'] ? ' selected="selected"' : '') . '>', $topic['subject'], '</option>';
	echo '
							<option value="0">', $txt['merge_custom_subject'], ':</option>
						</select> <input type="text" name="custom_subject" size="60" disabled="disabled" id="customSubject" /><br />
						<br />
						<input type="checkbox" class="check" name="enforce_subject" value="1" /> ', $txt['merge_enforce_subject'], '
					</td>
				</tr>';

	if (!empty($context['boards']) && count($context['boards']) > 1)
	{
		echo '
				<tr>
					<td class="catbg">' . $txt['merge_select_target_board'] . '</td>
				</tr><tr>
					<td class="windowbg"><table border="0" cellspacing="0" cellpadding="0">';
		foreach ($context['boards'] as $board)
			echo '
						<tr>
							<td>
								<input type="radio" name="board" value="' . $board['id'] . '"' . ($board['selected'] ? ' checked="checked"' : '') . ' class="check" /> ' . $board['name'] . '
							</td>
						</tr>';
		echo '
					</table></td>
				</tr>';
	}
	if (!empty($context['polls']))
	{
		echo '
				<tr>
					<td class="catbg">' . $txt['merge_select_poll'] . '</td>
				</tr><tr>
					<td class="windowbg"><table border="0" cellspacing="0" cellpadding="3">';
		foreach ($context['polls'] as $poll)
			echo '
						<tr>
							<td>
								<input type="radio" name="poll" value="' . $poll['id'] . '"' . ($poll['selected'] ? ' checked="checked"' : '') . ' class="check" /> ' . $poll['question'] . ' (' . $txt[118] . ': <a href="' . $scripturl . '?topic=' . $poll['topic']['id'] . '.0" target="_blank">' . $poll['topic']['subject'] . '</a>)
							</td>
						</tr>';
		echo '
						<tr>
							<td>
								<input type="radio" name="poll" value="-1" class="check" /> (' . $txt['merge_no_poll'] . ')
							</td>
						</tr>
					</table></td>
				</tr>';
	}
	echo '
				<tr>
					<td class="windowbg" align="right">
						<input type="submit" value="' . $txt['smf252'] . '" />
						<input type="hidden" name="sa" value="execute" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</td>
				</tr>
			</table>
		</form>';
}

?>