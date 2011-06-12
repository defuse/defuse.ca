<?php
// Version: 1.1; Errors

// !!!
/*	This template file contains only the sub template fatal_error. It is
	shown when an error occurs, and should show at least a back button and
	$context['error_message'].
*/

// Show an error message.....
function template_fatal_error()
{
	global $context, $settings, $options, $txt;

	echo '
<div>
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $context['error_title'], '</td>
		</tr>
		<tr class="windowbg">
			<td style="padding: 3ex;">
				', $context['error_message'], '
			</td>
		</tr>
	</table>
</div>';

	// Show a back button (using javascript.)
	echo '
<div align="center" style="margin-top: 2ex;"><a href="javascript:history.go(-1)">', $txt[250], '</a></div>';
}

function template_error_log()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';start=', $context['start'], $context['has_filter'] ? $context['filter']['href'] : '', '" method="post" accept-charset="', $context['character_set'], '" onsubmit="if (lastClicked == \'remove_all\' &amp;&amp; !confirm(\'', $txt['sure_about_errorlog_remove'], '\')) return false; else return true;">
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var lastClicked = "";
			// ]]></script>
			<table border="0" cellspacing="1" cellpadding="5" class="bordercolor" align="center">
				<tr class="catbg">
					<td colspan="2"><a href="', $scripturl, '?action=helpadmin;help=error_log" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['errlog1'], '</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="2" style="padding: 2ex;">', $txt['errlog2'], '</td>
				</tr>';

	if ($context['has_filter'])
		echo '
				<tr>
					<td colspan="2" class="windowbg2">
						<b>', $txt['applying_filter'], ':</b> ', $context['filter']['entity'], ' ', $context['filter']['value']['html'], ' (<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', '">', $txt['clear_filter'], '</a>)
					</td>
				</tr>';
	echo '
				<tr>
					<td colspan="2" class="titlebg">
						<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
							<td>', $txt[139], ': ', $context['page_index'], '</td>
						</tr></table>
					</td>
				</tr>';

	if (!empty($context['errors']))
		echo '
				<tr>
					<td colspan="2" align="left" class="windowbg2">
						<div style="float: right;"><input type="submit" value="', $txt['remove_selection'], '" onclick="lastClicked = \'remove_selection\';" /> <input type="submit" name="delall" value="', $context['has_filter'] ? $txt['remove_filtered_results'] : $txt['smf219'], '" onclick="lastClicked = \'remove_all\';" /></div>
						<label for="check_all1"><input type="checkbox" id="check_all1" onclick="invertAll(this, this.form, \'delete[]\'); this.form.check_all2.checked = this.checked;" class="check" /> <b>', $txt[737], '</b></label>
					</td>
				</tr>';

	foreach ($context['errors'] as $error)
	{
		echo '
				<tr>
					<td width="15" align="center" class="windowbg2">
						<input type="checkbox" name="delete[]" value="', $error['id'], '" class="check" />
					</td><td class="windowbg2" width="100%"><table width="100%" class="windowbg2" border="0" cellspacing="7" cellpadding="0">
						<tr>
							<td class="windowbg2" width="50%">
								<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=ID_MEMBER;value=', $error['member']['id'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_member'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_member'], '" /></a>
								<b>', $error['member']['link'], '</b>
							</td><td class="windowbg2" width="50%" align="left">
								<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? '' : ';desc', $context['has_filter'] ? $context['filter']['href'] : '', '" title="', $txt['reverse_direction'], '"><img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
								<b>', $error['time'], '</b>
							</td>
						</tr><tr>
							<td class="windowbg2" width="50%">
								<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=ip;value=', $error['member']['ip'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_ip'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_ip'], '" /></a>
								<b><a href="', $scripturl, '?action=trackip;searchip=', $error['member']['ip'], '">', $error['member']['ip'], '</a></b>&nbsp;&nbsp;
							</td><td class="windowbg2" width="50%">';

		if ($error['member']['session'] != '')
			echo '
								<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=session;value=', $error['member']['session'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_session'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_session'], '" /></a>
								', $error['member']['session'];

		echo '
							</td>
						</tr><tr>
							<td class="windowbg2" colspan="2"><div style="overflow: hidden; width: 100%; white-space: nowrap;">
								<a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=url;value=', $error['url']['href'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_url'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_url'], '" /></a>
								<a href="', $error['url']['html'], '">', $error['url']['html'], '</a>
								</div></td>
						</tr><tr>
							<td class="windowbg2" colspan="2">
								<div style="float: left;"><a href="', $scripturl, '?action=viewErrorLog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=message;value=', $error['message']['href'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_message'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_message'], '" /></a></div>
								<div style="float: left; margin-left: 1ex;">', $error['message']['html'], '</div>
							</td>
						</tr>
					</table></td>
				</tr>';
	}

	if (!empty($context['errors']))
		echo '
				<tr>
					<td colspan="2" class="windowbg2">
						<div style="float: right;"><input type="submit" value="', $txt['remove_selection'], '" onclick="lastClicked = \'remove_selection\';" /> <input type="submit" name="delall" value="', $context['has_filter'] ? $txt['remove_filtered_results'] : $txt['smf219'], '" onclick="lastClicked = \'remove_all\';" /></div>
						<label for="check_all2"><input type="checkbox" id="check_all2" onclick="invertAll(this, this.form, \'delete[]\'); this.form.check_all1.checked = this.checked;" class="check" /> <b>', $txt[737], '</b></label>
					</td>
				</tr>';
	else
		echo '
				<tr>
					<td colspan="2" class="windowbg2">', $txt[151], '</td>
				</tr>';

	echo '
				<tr>
					<td colspan="2" class="titlebg">
						<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
							<td>', $txt[139], ': ', $context['page_index'], '</td>
						</tr></table>
					</td>
				</tr>
			</table><br />';
	if ($context['sort_direction'] == 'down')
		echo '
			<input type="hidden" name="desc" value="1" />';
	echo '
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

?>