<?php
include 'libs.php';
session_start();
$sessid=session_id();
?>
<html>
<head>
<script type="text/javascript">
var url ='http://localhost/timeres.php';
function Ajaxcmnt(){
//Ajaxtps();
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
		document.getElementById('cmnt').innerHTML=xmlcmnt.responseText;
	}
}
xmlcmnt.open("GET",url,true);
xmlcmnt.send(null);
}

setInterval("Ajaxcmnt()",1000); 

</script>
	<title>alpha</title>
</head>
<body>
<div id="cmnt">Time</div>
<form action="upload.php" method="post" enctype="multipart/form-data">
Choose a file to scan: <input name="file" type="file" /><br />
Scan Archive? <input name="archive" type="checkbox" /><br />
Permlink? <input name="perm" type="checkbox" /><br />
<input type="hidden" name="sess" value="<?php echo $sessid; ?>"/>
<input type="submit" name="submit" value="Scan" />
</form>
</body>
</html>