<?php
// Version: 1.1; ManageSearch

function template_modify_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=managesearch;sa=settings" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['settings'], '</td>
			</tr>';

	if ($context['can_change_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<th width="50%" align="right" valign="top"><label for="search_posts_groups">', $txt['groups_search_posts'], ':</label></th>
				<td>';
		theme_inline_permissions('search_posts');
		echo '
				</td>
			</tr>';
	}

	echo '
			<tr class="windowbg2">
				<th width="50%" align="right"><label for="simpleSearch_check">', $txt['simpleSearch'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=simpleSearch" onclick="return reqWin(this.href);">?</a>):</th>
				<td><input type="checkbox" name="simpleSearch" id="simpleSearch_check"', empty($modSettings['simpleSearch']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<th align="right"><label for="search_results_per_page_input">', $txt['search_results_per_page'], ':</label></th>
				<td><input type="text" name="search_results_per_page" id="search_results_per_page_input" value="', $modSettings['search_results_per_page'], '" size="10" /></td>
			</tr><tr class="windowbg2">
				<th align="right">
					<label for="search_max_results_input">', $txt['search_max_results'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['search_max_results_disable'], '</div>
				</th>
				<td valign="top"><input type="text" name="search_max_results" id="search_max_results_input" value="', empty($modSettings['search_max_results']) ? '0' : $modSettings['search_max_results'], '" size="10" /></td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt['search_settings_save'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_modify_weights()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=managesearch;sa=weights" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="3">', $txt['search_weights'], '</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_frequency'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_frequency" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_frequency" id="weight1_val" value="', empty($modSettings['search_weight_frequency']) ? '0' : $modSettings['search_weight_frequency'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight1">', $context['relative_weights']['search_weight_frequency'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_age'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_age" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_age" id="weight2_val" value="', empty($modSettings['search_weight_age']) ? '0' : $modSettings['search_weight_age'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight2">', $context['relative_weights']['search_weight_age'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_length'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_length" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_length" id="weight3_val" value="', empty($modSettings['search_weight_length']) ? '0' : $modSettings['search_weight_length'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight3">', $context['relative_weights']['search_weight_length'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_subject'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_subject" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_subject" id="weight4_val" value="', empty($modSettings['search_weight_subject']) ? '0' : $modSettings['search_weight_subject'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight4">', $context['relative_weights']['search_weight_subject'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_first_message'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_first_message" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_first_message" id="weight5_val" value="', empty($modSettings['search_weight_first_message']) ? '0' : $modSettings['search_weight_first_message'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight5">', $context['relative_weights']['search_weight_first_message'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['search_weight_sticky'], ' (<a href="', $scripturl, '?action=helpadmin;help=search_weight_sticky" onclick="return reqWin(this.href);">?</a>):</td>
				<td><input type="text" name="search_weight_sticky" id="weight6_val" value="', empty($modSettings['search_weight_sticky']) ? '0' : $modSettings['search_weight_sticky'], '" onchange="calculateNewValues()" size="3" /></td>
				<td id="weight6">', $context['relative_weights']['search_weight_sticky'], '%</td>
			</tr><tr class="windowbg2">
				<td align="right"><b>', $txt['search_weights_total'], '</b></td>
				<td id="weighttotal" style="font-weight: bold;">', $context['relative_weights']['total'], '</td>
				<td style="font-weight: bold;">100%</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="3">
					<input type="submit" name="save" value="', $txt['search_weights_save'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function calculateNewValues()
		{
			var total = 0;
			for (var i = 1; i <= 6; i++)
			{
				total += parseInt(document.getElementById(\'weight\' + i + \'_val\').value);
			}
			setInnerHTML(document.getElementById(\'weighttotal\'), total);
			for (var i = 1; i <= 6; i++)
			{
				setInnerHTML(document.getElementById(\'weight\' + i), (Math.round(1000 * parseInt(document.getElementById(\'weight\' + i + \'_val\').value) / total) / 10) + \'%\');
			}
		}
	// ]]></script>';
}

function template_select_search_method()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=managesearch;sa=method" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="3">', $txt['search_method'], '</td>
			</tr>';
	if (!empty($context['table_info']))
		echo '
			<tr class="windowbg2">
				<td colspan="3">
					<b>', $txt['search_method_messages_table_space'], ':</b> ', $context['table_info']['data_length'], ' ', $txt['search_method_kilobytes'], ' <br />
					<b>', $txt['search_method_messages_index_space'], ':</b> ', $context['table_info']['index_length'], ' ', $txt['search_method_kilobytes'], '<br />', $context['double_index'] ? '
					' . $txt['search_double_index'] . '<br />' : '', '
					<br />
				</td>
			</tr>';
	echo '
			<tr class="windowbg2">
				<th width="47%" align="right">', $txt['search_index'], ':<div class="smalltext" style="font-weight: normal;"><a href="', $scripturl, '?action=helpadmin;help=search_why_use_index" onclick="return reqWin(this.href);">', $txt['search_create_index_why'], '</a></div></th>
				<td width="3%" align="center" valign="top" class="windowbg"><input type="radio" name="search_index" value=""', empty($modSettings['search_index']) ? ' checked="checked"' : '', ' /></td>
				<td>
					', $txt['search_index_none'], '
				</td>
			</tr><tr class="windowbg2">
				<td></td>
				<td width="3%" align="center" valign="top" class="windowbg"><input type="radio" name="search_index" value="fulltext"', !empty($modSettings['search_index']) && $modSettings['search_index'] == 'fulltext' ? ' checked="checked"' : '', empty($context['fulltext_index']) ? ' onclick="alert(\'' . $txt['search_method_fulltext_warning'] . '\'); selectRadioByName(this.form.search_index, \'fulltext\');"': '', ' /></td>
				<td>
					', $txt['search_method_fulltext_index'], '<br />
					<span class="smalltext">';
	if (empty($context['fulltext_index']) && empty($context['cannot_create_fulltext']))
		echo '
						<b>', $txt['search_index_label'], ':</b> ',  $txt['search_method_no_index_exists'], ' [<a href="', $scripturl, '?action=managesearch;sa=createfulltext;sesc=', $context['session_id'], '">', $txt['search_method_fulltext_create'], '</a>]';
	elseif (empty($context['fulltext_index']) && !empty($context['cannot_create_fulltext']))
		echo '
						<b>', $txt['search_index_label'], ':</b> ', $txt['search_method_fulltext_cannot_create'];
	else
		echo '
						<b>', $txt['search_index_label'], ':</b> ', $txt['search_method_index_already_exsits'], ' [<a href="', $scripturl, '?action=managesearch;sa=removefulltext;sesc=', $context['session_id'], '">', $txt['search_method_fulltext_remove'], '</a>]<br />
						<b>', $txt['search_index_size'], ':</b> ', $context['table_info']['fulltext_length'], ' ', $txt['search_method_kilobytes'];
	echo '
					</span>
				</td>
			</tr><tr class="windowbg2">
				<td align="right"></td>
				<td width="3%" align="center" valign="top" class="windowbg"><input type="radio" name="search_index" value="custom"', !empty($modSettings['search_index']) && $modSettings['search_index'] == 'custom' ? ' checked="checked"' : '', $context['custom_index'] ? '' : ' onclick="alert(\'' . $txt['search_index_custom_warning'] . '\'); selectRadioByName(this.form.search_method, \'1\');"', ' /></td>
				<td>
					', $txt['search_index_custom'], '<br />
					<span class="smalltext">';
	if ($context['custom_index'])
		echo '
						<b>', $txt['search_index_label'], ':</b> ', $txt['search_method_index_already_exsits'], ' [<a href="', $scripturl, '?action=managesearch;sa=removecustom;sesc=', $context['session_id'], '">', $txt['search_index_custom_remove'], '</a>]<br />
						<b>', $txt['search_index_size'], ':</b> ', $context['table_info']['custom_index_length'], ' ', $txt['search_method_kilobytes'];
	elseif ($context['partial_custom_index'])
		echo '
						<b>', $txt['search_index_label'], ':</b> ', $txt['search_method_index_partial'], ' [<a href="', $scripturl, '?action=managesearch;sa=removecustom;sesc=', $context['session_id'], '">', $txt['search_index_custom_remove'], '</a>] [<a href="', $scripturl, '?action=managesearch;sa=createmsgindex;resume;sesc=', $context['session_id'], '">', $txt['search_index_custom_resume'], '</a>]<br />
						<b>', $txt['search_index_size'], ':</b> ', $context['table_info']['custom_index_length'], ' ', $txt['search_method_kilobytes'];
	else
		echo '
						<b>', $txt['search_index_label'], ':</b> ',  $txt['search_method_no_index_exists'], ' [<a href="', $scripturl, '?action=managesearch;sa=createmsgindex">', $txt['search_index_create_custom'], '</a>]';
	echo '
					</span>
				</td>
			</tr><tr class="windowbg2">
				<th align="right"><label for="search_force_index_check">', $txt['search_force_index'], ':</label></th>
				<td colspan="2"><input type="checkbox" name="search_force_index" id="search_force_index_check" value="1"', empty($modSettings['search_force_index']) ? '' : ' checked="checked"', ' /></td>
			</tr><tr class="windowbg2">
				<th align="right"><label for="search_match_words_check">', $txt['search_match_words'], ':</label></th>
				<td colspan="2"><input type="checkbox" name="search_match_words" id="search_match_words_check" value="1"', empty($modSettings['search_match_words']) ? '' : ' checked="checked"', ' /></td>
			</tr><tr class="windowbg2">
				<td></td>
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt['search_method_save'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_create_index()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=managesearch;sa=createmsgindex;step=1" method="post" accept-charset="', $context['character_set'], '" name="create_index">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['search_create_index'], '</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="predefine_select">', $txt['search_predefined'], ':</label></th>
				<td>
					<select name="bytes_per_word" id="predefine_select">
						<option value="2">', $txt['search_predefined_small'], '</option>
						<option value="4" selected="selected">', $txt['search_predefined_moderate'], '</option>
						<option value="5">', $txt['search_predefined_large'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt['search_create_index_start'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_create_index_progress()
{
	global $context, $settings, $options, $scripturl, $txt;
	echo '
	<form action="', $scripturl, '?action=managesearch;sa=createmsgindex;step=1" method="post" accept-charset="', $context['character_set'], '" name="autoSubmit">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td>', $txt['search_create_index'], '</td>
			</tr><tr>
				<td class="windowbg2" align="center">
					', $txt['search_create_index_not_ready'], '
				</td>
			</tr><tr>
				<td class="windowbg2" align="center">
					<b>', $txt['search_create_index_progress'], ': ', $context['percentage'], '%
				</td>
			</tr><tr>
				<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
					<input type="submit" name="b" value="', $txt['search_create_index_continue'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="step" value="', $context['step'], '" />
		<input type="hidden" name="start" value="', $context['start'], '" />
		<input type="hidden" name="bytes_per_word" value="', $context['index_settings']['bytes_per_word'], '" />
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var countdown = 10;
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.b.value = "', $txt['search_create_index_continue'], ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';

}

function template_create_index_done()
{
	global $context, $settings, $options, $scripturl, $txt;
	echo '
	<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
		<tr class="titlebg">
			<td>', $txt['search_create_index'], '</td>
		</tr><tr>
			<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
				', $txt['search_create_index_done'], '
			</td>
		</tr><tr>
			<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
				<a href="', $scripturl, '?action=managesearch;sa=method">', $txt['search_create_index_done_link'], '</a>
			</td>
		</tr>
	</table>';
}

?>