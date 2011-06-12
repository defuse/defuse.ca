<?php
// Version: 1.1; Printpage

function template_print_above()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $txt[668], ' - ', $context['topic_subject'], '</title>
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
			.code
			{
				font-size: x-small;
				font-family: monospace;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
			}
			.quote
			{
				font-size: x-small;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
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
	<body>
		<h1 class="largetext">', $context['forum_name'], '</h1>
		<h2 class="normaltext">', $context['category_name'], ' => ', $context['board_name'], ' => ', $txt[195], ': ', $context['poster_name'], ' ', $txt[176], ' ', $context['post_time'] . '</h2>

		<table width="90%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>';
}

function template_main()
{
	global $context, $settings, $options, $txt;

	foreach ($context['posts'] as $post)
		echo '
					<br />
					<hr size="2" width="100%" />
					', $txt[196], ': <b>', $post['subject'], '</b><br />
					', $txt[197], ': <b>', $post['member'], '</b> ', $txt[176], ' <b>', $post['time'], '</b>
					<hr />
					<div style="margin: 0 5ex;">', $post['body'], '</div>';
}

function template_print_below()
{
	global $context, $settings, $options;

	echo '
					<br /><br />
					<div align="center" class="smalltext">', theme_copyright(), '</div>
				</td>
			</tr>
		</table>
	</body>
</html>';
}

?>