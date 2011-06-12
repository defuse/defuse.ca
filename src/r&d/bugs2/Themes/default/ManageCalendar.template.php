<?php
// Version: 1.1; ManageCalendar

function template_manage_holidays()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Listing of all holidays...
	echo '
<form action="', $scripturl, '?action=managecalendar;sa=holidays" method="post" accept-charset="', $context['character_set'], '">
	<table width="100%" cellspacing="0" cellpadding="4" border="0" class="tborder">
		<tr class="titlebg">
			<td colspan="3">', $txt['current_holidays'], '</td>
		</tr><tr class="catbg3">
			<td colspan="3" height="32">', $txt[139], ': ', $context['page_index'], '</td>
		</tr><tr class="titlebg">
			<td align="left">', $txt['holidays_title'], '</td>
			<td align="left">', $txt[317], '</td>
			<td align="center" width="4%"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></td>
		</tr>';

	// Now print out all the holidays.
	$alternate = false;
	foreach ($context['holidays'] as $holiday)
	{
		echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td align="left"><a href="', $scripturl, '?action=managecalendar;sa=editholiday;holiday=', $holiday['id'], '">', $holiday['title'], '</a></td>
			<td align="left">', $holiday['date'], '</td>
			<td align="center" width="4%"><input type="checkbox" name="holiday[', $holiday['id'], ']" class="check" /></td>
		</tr>';
		$alternate = !$alternate;
	}

	echo '
		<tr class="titlebg">
			<td align="left"><a href="', $scripturl, '?action=managecalendar;sa=editholiday">', $txt['holidays_add'], '</a></td>
			<td colspan="2" align="right">
				<input type="submit" name="delete" style="font-weight: normal;" value="', $txt['quickmod_delete_selected'], '" onclick="if (!confirm(\'', $txt['holidays_delete_confirm'], '\')) return false;" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>
</form>';
}

// Editing or adding holidays.
function template_edit_holiday()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Start with javascript for getting the calendar dates right.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
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
			}
		// ]]></script>';

	// Show a form for all the holiday information.
	echo '
<form action="', $scripturl, '?action=managecalendar;sa=editholiday" method="post" accept-charset="', $context['character_set'], '">
	<table width="60%" cellspacing="0" cellpadding="4" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $context['page_title'], '</td>
		</tr><tr class="windowbg2">
			<td width="25%" align="right">', $txt['holidays_title_label'], ':</td>
			<td><input type="text" name="title" value="', $context['holiday']['title'], '" size="60" /></td>
		</tr><tr class="windowbg2">
			<td align="right">', $txt['calendar10'], '</td>
			<td>
				<select name="year" id="year" onchange="generateDays();">
					<option value="0000"', $context['holiday']['year'] == '0000' ? ' selected="selected"' : '', '>', $txt['every_year'], '</option>';
		// Show a list of all the years we allow...
		for ($year = $modSettings['cal_minyear']; $year <= $modSettings['cal_maxyear']; $year++)
			echo '
					<option value="', $year, '"', $year == $context['holiday']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

		echo '
				</select>&nbsp;
				', $txt['calendar9'], '&nbsp;
				<select name="month" id="month" onchange="generateDays();">';

		// There are 12 months per year - ensure that they all get listed.
		for ($month = 1; $month <= 12; $month++)
			echo '
					<option value="', $month, '"', $month == $context['holiday']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

		echo '
				</select>&nbsp;
				', $txt['calendar11'], '&nbsp;
				<select name="day" id="day" onchange="generateDays();">';

		// This prints out all the days in the current month - this changes dynamically as we switch months.
		for ($day = 1; $day <= $context['holiday']['last_day']; $day++)
			echo '
				<option value="', $day, '"', $day == $context['holiday']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

		echo '
			</select>
		</td>
		</tr><tr class="windowbg2">
			<td colspan="2" align="center">';
	if ($context['is_new'])
		echo '
				<input type="submit" value="', $txt['holidays_button_add'], '" />';
	else
		echo '
				<input type="submit" name="edit" value="', $txt['holidays_button_edit'], '" />
				<input type="submit" name="delete" value="', $txt['holidays_button_remove'], '" />
				<input type="hidden" name="holiday" value="', $context['holiday']['id'], '" />';
	echo '
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>
</form>';
}

// Calendar settings.
function template_modify_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=managecalendar;sa=settings" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['calendar_settings'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><label for="cal_enabled">', $txt['setting_cal_enabled'], '</label>:</td>
				<td><input type="checkbox" name="cal_enabled" id="cal_enabled"', empty($modSettings['cal_enabled']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr>';
	if ($context['can_change_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td align="right" valign="top" width="50%">', $txt['groups_calendar_view'], ':</td>
				<td width="50%">';
		theme_inline_permissions('calendar_view');
		echo '
				</td>
			</tr><tr class="windowbg2">
				<td align="right" valign="top" width="50%">', $txt['groups_calendar_post'], ':</td>
				<td width="50%">';
		theme_inline_permissions('calendar_post');
		echo '
				</td>
			</tr><tr class="windowbg2">
				<td align="right" valign="top" width="50%">', $txt['groups_calendar_edit_own'], ':</td>
				<td width="50%">';
		theme_inline_permissions('calendar_edit_own');
		echo '
				</td>
			</tr><tr class="windowbg2">
				<td align="right" valign="top" width="50%">', $txt['groups_calendar_edit_any'], ':</td>
				<td width="50%">';
		theme_inline_permissions('calendar_edit_any');
		echo '
				</td>
			</tr>';
	}
	echo '
			<tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right"><label for="cal_daysaslink">', $txt['setting_cal_daysaslink'], '</label>:</td>
				<td><input type="checkbox" name="cal_daysaslink" id="cal_daysaslink"', empty($modSettings['cal_daysaslink']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td align="right"><label for="cal_showweeknum">', $txt['setting_cal_showweeknum'], '</label>:</td>
				<td><input type="checkbox" name="cal_showweeknum" id="cal_showweeknum"', empty($modSettings['cal_showweeknum']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_days_for_index'], ':</td>
				<td><input type="text" name="cal_days_for_index" value="', $modSettings['cal_days_for_index'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_showholidays'], ':</td>
				<td>
					<select name="cal_showholidays">
						<option value="never"', $context['cal_showholidays'] == 'never' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_never'], '</option>
						<option value="cal"', $context['cal_showholidays'] == 'cal' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_cal'], '</option>
						<option value="index"', $context['cal_showholidays'] == 'index' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_index'], '</option>
						<option value="all"', $context['cal_showholidays'] == 'all' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_all'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_showbdays'], ':</td>
				<td>
					<select name="cal_showbdays">
						<option value="never"', $context['cal_showbdays'] == 'never' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_never'], '</option>
						<option value="cal"', $context['cal_showbdays'] == 'cal' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_cal'], '</option>
						<option value="index"', $context['cal_showbdays'] == 'index' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_index'], '</option>
						<option value="all"', $context['cal_showbdays'] == 'all' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_all'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_showevents'], ':</td>
				<td>
					<select name="cal_showevents">
						<option value="never"', $context['cal_showevents'] == 'never' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_never'], '</option>
						<option value="cal"', $context['cal_showevents'] == 'cal' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_cal'], '</option>
						<option value="index"', $context['cal_showevents'] == 'index' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_index'], '</option>
						<option value="all"', $context['cal_showevents'] == 'all' ? ' selected="selected"' : '', '>', $txt['setting_cal_show_all'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_defaultboard'], ':</td>
				<td>
					<select name="cal_defaultboard">';
		foreach ($context['cal_boards'] as $id => $name)
			echo '
						<option value="', $id, '"', $id == $modSettings['cal_defaultboard'] ? ' selected="selected"' : '', '>', $name, '</option>';
		echo '
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td align="right"><label for="cal_allow_unlinked">', $txt['setting_cal_allow_unlinked'], '</label>:</td>
				<td><input type="checkbox" name="cal_allow_unlinked" id="cal_allow_unlinked"', empty($modSettings['cal_allow_unlinked']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td align="right"><label for="cal_showInTopic">', $txt['setting_cal_showInTopic'], '</label>:</td>
				<td><input type="checkbox" name="cal_showInTopic" id="cal_showInTopic"', empty($modSettings['cal_showInTopic']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_minyear'], ':</td>
				<td><input type="text" name="cal_minyear" value="', $modSettings['cal_minyear'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_maxyear'], ':</td>
				<td><input type="text" name="cal_maxyear" value="', $modSettings['cal_maxyear'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_bdaycolor'], ':</td>
				<td><input type="text" name="cal_bdaycolor" value="', $modSettings['cal_bdaycolor'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_eventcolor'], ':</td>
				<td><input type="text" name="cal_eventcolor" value="', $modSettings['cal_eventcolor'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_holidaycolor'], ':</td>
				<td><input type="text" name="cal_holidaycolor" value="', $modSettings['cal_holidaycolor'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td colspan="2"><hr width="90%" /></td>
			</tr><tr class="windowbg2">
				<td align="right"><label for="cal_allowspan">', $txt['setting_cal_allowspan'], '</label>:</td>
				<td><input type="checkbox" name="cal_allowspan" id="cal_allowspan"', empty($modSettings['cal_allowspan']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['setting_cal_maxspan'], ':</td>
				<td><input type="text" name="cal_maxspan" value="', $modSettings['cal_maxspan'], '" size="40" /></td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" value="', $txt['save_settings'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

?>