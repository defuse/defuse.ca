<h1>Online HTML Sanitize Tool (htmlspecialchars)</h1>
<p>This tool will take your text and convert all the special characters to their proper HTML codes, so you can paste text with special characters or HTML code onto your website.</p>

<?php
	if(isset($_POST['sanitize']))
	{
	?>
		<p><span style="background-color: #AAFFAA; width:400px; padding:5px; border: solid black 1px;">Your Text Has Been Sanitized!</span></p>
	<?
	}
	else
	{
	?>
		<p><strong>Enter Your Text:</strong></p>
	<?
	}
?>


<form action="html-sanitize.htm" method="post">
	
	<textarea name="data" rows="30" cols="40" style="width:100%;" >
<?php
$data = $_POST['data'];
$data = htmlspecialchars(htmlspecialchars($_POST['data'], ENT_QUOTES), ENT_QUOTES); 
if($_POST['br'] == "yes")
{
	$data = str_replace("\r\n", "\n", $data);
	$data = str_replace("\r", "\n", $data);
	$data = str_replace("\n", "<br />\n", $data);
}
$data = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $data);
echo $data;
?></textarea><br />
	<input type="submit" name="sanitize" value="HTML-Sanitize Text" /><input type="checkbox" name="br" value="yes" checked="checked" />Replace newlines with &lt;br /&gt;
</form>

