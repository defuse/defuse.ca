<script type="text/javascript" >
function selectz(frm) 
{
	var chk = document.getElementById("selectall").checked;
	frm.asquared.checked = chk;
	frm.avira.checked = chk;
	frm.bitdefender.checked = chk;
	frm.esafe.checked = chk;
	frm.eset.checked = chk;
	frm.fprot.checked = chk;
	frm.ikarus.checked = chk;
	frm.kav.checked = chk;
	frm.mcafee.checked = chk;
	frm.norton.checked = chk;
	frm.panda.checked = chk;
	frm.quickheal.checked = chk;
	frm.solo.checked = chk;
	frm.sophos.checked = chk;
	frm.vba32.checked = chk;
	frm.virusbuster.checked = chk;
}
</script>
Choose which A/V to scan with:<br />
<?php
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');

echo '<form action="rescan.php?id=' . $xssid . '" method="post">';
?>
<input onClick="selectz(this.form);" type="checkbox" id="selectall" name="checkall" value="" checked />
Select All<br />

<table>

<tr><td><input type="checkbox" name="asquared" value="true" checked />&nbsp;ASquared</td><td><input type="checkbox" name="avira" value="true" checked />&nbsp;Avira</td></tr>
<tr><td><input type="checkbox" name="bitdefender" value="true" checked />&nbsp;Bitdefender</td><td><input type="checkbox" name="esafe" value="true" checked />&nbsp;Esafe</td></tr>
<tr><td><input type="checkbox" name="eset" value="true" checked />&nbsp;Eset NOD32</td><td><input type="checkbox" name="fprot" value="true" checked />&nbsp;F-Prot</td></tr>
<tr><td><input type="checkbox" name="ikarus" value="true" checked />&nbsp;Ikarus</td><td><input type="checkbox" name="kav" value="true" checked />&nbsp;Kaspersky</td></tr>
<tr><td><input type="checkbox" name="mcafee" value="true" checked />&nbsp;Mcafee</td><td><input type="checkbox" name="norton" value="true" checked />&nbsp;Norton</td></tr>
<tr><td><input type="checkbox" name="panda" value="true" checked />&nbsp;Panda</td><td><input type="checkbox" name="quickheal" value="true" checked />&nbsp;Quickheal</td></tr>
<tr><td><input type="checkbox" name="solo" value="true" checked />&nbsp;Solo</td><td><input type="checkbox" name="sophos" value="true" checked />&nbsp;Sophos</td></tr>
<tr><td><input type="checkbox" name="vba32" value="true" checked />&nbsp;VBA32</td><td><input type="checkbox" name="virusbuster" value="true" checked />&nbsp;VirusBuster</td></tr>
<tr><td><input type="submit" name="submit" value="Scam" /></td></tr>
</table>

</form>