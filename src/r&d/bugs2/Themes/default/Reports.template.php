<?php
// Version: 1.1; Reports

// Choose which type of report to run?
function template_report_type()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=reports" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tborder">
				<tr class="titlebg">
					<td colspan="2">', $txt['generate_reports'], '</td>
				</tr>
				<tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;" colspan="2">', $txt['generate_reports_desc'], '</td>
				</tr>
				<tr class="titlebg">
					<td colspan="2">', $txt['generate_reports_type'], ':</td>
				</tr>';

	$alternate = false;
	// Go through each type of report they can run.
	foreach ($context['report_types'] as $type)
	{
		echo '
				<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" valign="top">
					<td width="20">
						<input type="radio" id="rt_', $type['id'], '" name="rt" value="', $type['id'], '"', $type['is_first'] ? ' checked="checked"' : '', ' class="check" />
					</td>
					<td align="left" width="100%">
						<label for="rt_', $type['id'], '">
							<b>', $type['title'], '</b>';
		if (isset($type['description']))
			echo '
							<br /><span class="smalltext">', $type['description'], '</span>';
		echo '
						</label>
					</td>
				</tr>';

		$alternate = !$alternate;
	}
	echo '
				<tr class="titlebg">
					<td align="right" colspan="2">
						<input type="submit" name="continue" value="', $txt['generate_reports_continue'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

// This is the standard template for showing reports in.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<div class="tborder">
			<div class="titlebg" style="padding: 4px;">
				<div style="float: left;"><b>', $txt['results'], '</b></div>
				<div style="text-align: right;">&nbsp;';
	if (empty($settings['use_tabs']))
		echo '

					<a href="', $scripturl, '?action=reports;rt=', $context['report_type'], ';st=print" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/print.gif" alt="' . $txt[465] . '" border="0" />' : $txt[465]), '</a>';
	echo '
				</div>
			</div>
		</div>';
	if (!empty($settings['use_tabs']))
		echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
			<td align="right">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td class="maintab_first">&nbsp;</td>
						<td valign="top" class="maintab_back">
							<a href="', $scripturl, '?action=reports;rt=', $context['report_type'], ';st=print" target="_blank">', $txt[465], '</a>
						</td>
						<td class="maintab_last">&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr></table><br />';

	// Go through each table!
	foreach ($context['tables'] as $table)
	{
		echo '
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="bordercolor">';

		if (!empty($table['title']))
			echo '
			<tr class="catbg">
				<td colspan="', $table['column_count'], '">', $table['title'], '</td>
			</tr>';

		// Now do each row!
		$row_number = 0;
		$alternate = false;
		foreach ($table['data'] as $row)
		{
			if ($row_number == 0 && !empty($table['shading']['top']))
				echo '
			<tr class="titlebg" valign="top">';
			else
				echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" valign="top">';

			// Now do each column.
			$column_number = 0;
			foreach ($row as $key => $data)
			{
				// If this is a special seperator, skip over!
				if (!empty($data['seperator']) && $column_number == 0)
				{
					echo '
				<td colspan="', $table['column_count'], '" class="catbg">
					<b>', $data['value'], ':</b>
				</td>';
					break;
				}

				// Shaded?
				if ($column_number == 0 && !empty($table['shading']['left']))
					echo '
				<td align="', $table['align']['shaded'], '" class="titlebg" ', $table['width']['shaded'] != 'auto' ? 'width="' . $table['width']['shaded'] . '"' : '', '>
					', $data['value'] == $table['default_value'] ? '' : ($data['value'] . (empty($data['value']) ? '' : ':')), '
				</td>';
				else
					echo '
				<td align="', $table['align']['normal'], '" ', $table['width']['normal'] != 'auto' ? 'width="' . $table['width']['normal'] . '"' : '', ' ', !empty($data['style']) ? 'style="' . $data['style'] . '"' : '', '>
					', $data['value'], '
				</td>';

				$column_number++;
			}

			echo '
			</tr>';

			$row_number++;
			$alternate = !$alternate;
		}
		echo '
		</table>
		<br />';
	}
}

// Header of the print page!
function template_print_above()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $context['page_title'], '</title>
		<style type="text/css">
			body
			{
				color: black;
				background-color: white;
			}
			body, td, .normaltext
			{
				font-family: Verdana, arial, helvetica, serif;
				font-size: small;
			}
			*, a:link, a:visited, a:hover, a:active
			{
				color: black !important;
			}
			table
			{
				empty-cells: show;
			}
			.smalltext, .quoteheader, .codeheader
			{
				font-size: x-small;
			}
			.largetext
			{
				font-size: large;
			}
			hr
			{
				height: 1px;
				border: 0;
				color: black;
				background-color: black;
			}
			.catbg
			{
				background-color: #D6D6D6;
				font-weight: bold;
			}
			.titlebg, tr.titlebg td, .titlebg a:link, .titlebg a:visited
			{
				font-style: normal;
				background-color: #F5EDED;
			}
			.bordercolor
			{
				background-color: #333;
			}
			.windowbg
			{
				color: black;
				background-color: white;
			}
			.windowbg2
			{
				color: black;
				background-color: #F1F1F1;
			}
		</style>';

	/* Internet Explorer 4/5 and Opera 6 just don't do font sizes properly. (they are big...)
		Thus, in Internet Explorer 4, 5, and Opera 6 this will show fonts one size smaller than usual.
		Note that this is affected by whether IE 6 is in standards compliance mode.. if not, it will also be big.
		Standards compliance mode happens when you use xhtml... */
	if ($context['browser']['needs_size_fix'])
		echo '
		<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/fonts-compat.css" />';

	echo '
	</head>
	<body>';
}

function template_print()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Go through each table!
	foreach ($context['tables'] as $table)
	{
		echo '
		<div style="overflow: visible; ', $table['max_width'] != 'auto' ? 'width: ' . $table['max_width'] . 'px;' : '', '">
			<table border="0" cellspacing="1" cellpadding="4" width="100%" class="bordercolor">';

		if (!empty($table['title']))
			echo '
				<tr class="catbg">
					<td colspan="', $table['column_count'], '">
						', $table['title'], '
					</td>
				</tr>';

		// Now do each row!
		$alternate = false;
		$row_number = 0;
		foreach ($table['data'] as $row)
		{
			if ($row_number == 0 && !empty($table['shading']['top']))
				echo '
				<tr class="titlebg" valign="top">';
			else
				echo '
				<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" valign="top">';

			// Now do each column!!
			$column_number = 0;
			foreach ($row as $key => $data)
			{
				// If this is a special seperator, skip over!
				if (!empty($data['seperator']) && $column_number == 0)
				{
					echo '
					<td colspan="', $table['column_count'], '" class="catbg">
						<b>', $data['value'], ':</b>
					</td>';
					break;
				}

				// Shaded?
				if ($column_number == 0 && !empty($table['shading']['left']))
					echo '
					<td align="', $table['align']['shaded'], '" class="titlebg" ', $table['width']['shaded'] != 'auto' ? 'width="' . $table['width']['shaded'] . '"' : '', '>
						', $data['value'] == $table['default_value'] ? '' : ($data['value'] . (empty($data['value']) ? '' : ':')), '
					</td>';
				else
					echo '
					<td align="', $table['align']['normal'], '" ', $table['width']['normal'] != 'auto' ? 'width="' . $table['width']['normal'] . '"' : '', ' ', !empty($data['style']) ? 'style="' . $data['style'] . '"' : '', '>
						', $data['value'], '
					</td>';

				$column_number++;
			}

			echo '
				</tr>';

			$row_number++;
			$alternate = !$alternate;
		}
		echo '
			</table>
		</div><br />';
	}
}

// Footer of the print page.
function template_print_below()
{
	global $context, $settings, $options;

	echo '
		<div align="center" style="margin-top: 2ex;" class="smalltext">', theme_copyright(), '</div>
	</body>
</html>';
}

?>