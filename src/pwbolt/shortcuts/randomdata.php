<div  class="box">
<div class="headerbar"><h3>Password Generator</h3></div>
	<div class="insidebox">
	<table  cellspacing="10" style="font-family:monospace; font-size:9pt;">

<?php
require_once('libs/security.php');
//leave print first so the user will tend to go for that one (more secure)
echo '<tr><td><b>All Printable:</b></td><td>'. security::xsssani(formatPrint(security::SuperRand(2) )) . '</td></tr>';
echo '<tr><td><b>Alpha-Numeric:</b></td><td>'. security::xsssani(formatAlphaNumerical(security::SuperRand(2))) . '</td></tr>';
echo '<tr><td><b>Hex:</b></td><td>'. formatHex(security::SuperRand()) . '</td></tr>';

function formatHex($string)
{
	return bin2hex($string);
}

function formatPrint($key)
{
	$printable = "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
	return security::format($printable, $key);
}

function formatAlphaNumerical($key)
{
	$alphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	return security::format($alphanum,$key);
}

?>
	</table>
	</div>
</div>
