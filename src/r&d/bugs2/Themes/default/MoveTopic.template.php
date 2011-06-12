<?php
// Version: 1.1; MoveTopic

// Show an interface for selecting which board to move a post to.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=movetopic2;topic=', $context['current_topic'], '.0" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<table border="0" width="400" cellspacing="0" cellpadding="4" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt[132], '</td>
			</tr><tr>
				<td class="windowbg" valign="middle" align="center" style="padding-bottom: 1ex; padding-top: 2ex;">
					<b>', $txt[133], ':</b> <select name="toboard">';

	// Show dashes (-) before the board name if it's a child.
	foreach ($context['boards'] as $board)
		echo '
						<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['category'], ' ', str_repeat('-', 1 + $board['child_level']), ' ', $board['name'], '</option>';

	echo '
					</select><br />
					<br />
					<label for="reset_subject"><input type="checkbox" name="reset_subject" id="reset_subject" onclick="document.getElementById(\'subjectArea\').style.display = this.checked ? \'block\' : \'none\';" class="check" /> ', $txt['moveTopic2'], '.</label><br />
					<div id="subjectArea" style="display: none; margin-top: 1ex; margin-bottom: 2ex;">
						', $txt['moveTopic3'], ': <input type="text" name="custom_subject" size="30" value="', $context['subject'], '" /><br />
						<label for="enforce_subject"><input type="checkbox" name="enforce_subject" id="enforce_subject" class="check" /> ', $txt['moveTopic4'], '.</label>
					</div>';

	// Disable the reason textarea when the postRedirect checkbox is unchecked...
	echo '
					<label for="postRedirect"><input type="checkbox" name="postRedirect" id="postRedirect" checked="checked" onclick="document.getElementById(\'reasonArea\').style.display = this.checked ? \'block\' : \'none\';" class="check" /> ', $txt['moveTopic1'], '.</label><br />
					<div id="reasonArea" style="margin-top: 1ex;">
						', $txt['smf57'], '<br />
						<textarea name="reason" rows="3" cols="40">', $txt['movetopic_default'], '</textarea><br />
					</div>
					<br />
					<input type="submit" value="', $txt[132], '" onclick="return submitThisOnce(this);" accesskey="s" />
				</td>
			</tr>
		</table>';

	if ($context['back_to_topic'])
		echo '
		<input type="hidden" name="goback" value="1" />';

	echo '
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
	</form>';
}

?>