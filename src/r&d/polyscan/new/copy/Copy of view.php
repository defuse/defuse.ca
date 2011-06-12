<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');
$safeuser = sqli(GetUsername());


if(isset($_GET['id'])) //if they are requiesting the id
{
	$deobfid = deobf($_GET['id']);
	$safeid = sqli($deobfid); //grab the id, this could be a recently uploaded file or an old file in archive
	$xssid = xss($_GET['id']);
	//grab the data for this id
	$query = mysql_query("SELECT * FROM queue WHERE id='$safeid'");
	
	if(!is_numeric($deobfid) || mysql_num_rows($query) == 0) //die if it doesnt exist (they are hacking us)
	{
		if(is_numeric($deobfid))
		{
			echo "Invalid Id.";
		}
		else
		{
			ReportIntrusion("INVALID_ID", $_GET['id']);
			echo "Hacking attempt. IP recorded.";
		}
		die();
	}

	$data = mysql_fetch_array($query);
	
	
	if($data['complete'] ==  69) //if the file was JUST uploaded (first view)
	{
		$safesha256 = sqli($data['sha256']); //calculate the hash so we can check if it exists
		
		//search the user's previously uploaded hashes AND public hashes
		$check = mysql_query("SELECT * FROM queue WHERE sha256 = '$safesha256' AND id != '$safeid' AND (username='$safeuser' OR username='public') AND complete != '69' ORDER BY id DESC");
		
		if(isset($_GET['rescan']) != true && mysql_num_rows($check) > 0) //a newer file was found, so we have to ask
		{
			//TODO: show time since last scan
			$last = mysql_fetch_array($check);
			$id = xss(obf($last['id']));
			$oldurl = "index.php?p=view&id=$id";
			echo 'Scan results for this file already exist. <a href="index.php?p=view&id=' . $xssid . '&rescan=true">Re-Scan the file. OR <a href="' . $oldurl . '">VIEW OLD RESULTs';
			die();
		}
		else if(isset($_GET['rescan']) && mysql_num_rows($check) > 0) //they have made their choice
		{
			if($_GET['rescan'] == 'true')
			{
				if(GetUsername() != 'public' && !HasUnlimited())
					RemoveCredit(1);
				//change complete to 0, show this id
				mysql_query("UPDATE queue SET complete='0' WHERE id='$safeid' AND complete='69'");
				//end of script will display stats for this id
			}
			else
			{
				echo "h4x.";
			}
		}
		else
		{
			mysql_query("UPDATE queue SET complete='0' WHERE id='$safeid'");
		}
	}
	
	//----END OF VERIFICATION + RESCAN---//

	
	$safeid = sqli($deobfid );

	$info = mysql_query("SELECT * FROM queue WHERE id='$safeid'");
	$data = mysql_fetch_array($info);
	$safename = xss($data['filename']);
	echo "<h2>Scan Results For: $safename</h2>";
		if(strlen($data['filedata']) > 0 && $data['keepuntil'] - time() > 0 && $data['complete'] == 1)
	{
		echo "Click <a href=\"index.php?p=rescan&id=$xssid\">here</a> to scan the file again.";
	}
	$sha256 = xss($data['sha256']);
	$sha1 = xss($data['sha1']);
	$md5 = xss($data['md5']);
	$filesize = xss($data['filesize']);
	
			echo "<table>
				<tr><td>File Name:</td><td>$safename</td></tr>
				<tr><td>SHA256:</td><td>$sha256</td></tr>
				<tr><td>SHA1:</td><td>$sha1</td></tr>
				<tr><td>MD5:</td><td>$md5</td></tr>
				<tr><td>File size:</td><td>$filesize Bytes</td></tr>
				</table>";
		echo '<br />';

}


?>
<h2>Scan Results</h2>
<div id="scanres">

</div>
<script type="text/javascript">
function Ajaxcmnt(){
var url ="http://localhost/new/scanres.php?id=<?php echo xss($_GET['id']); ?>";

	try{	
		xmlcmnt=new XMLHttpRequest();// Firefox, Opera 8.0+, Safari
	}
	catch (e){
		try{
			xmlcmnt=new ActiveXObject("Msxml2.XMLHTTP"); // Internet Explorer
		}
		catch (e){
		    try{
				xmlcmnt=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e){
				alert("No AJAX!?");
				return false;
			}
		}
	}

xmlcmnt.onreadystatechange=function(){
	if(xmlcmnt.readyState==4){
		document.getElementById('scanres').innerHTML=xmlcmnt.responseText;
	}
}
xmlcmnt.open("GET",url,true);
xmlcmnt.send(null);
}
setInterval("Ajaxcmnt()",5000); 
</script>