<?php
session_start();
include 'libs.php';
include 'aes.php';
$id = sqlsani($_GET['id']);
$type = sqlsani($_GET['type']);
$rescan = sqlsani($_GET['rescan']);
?>
<html>
<head>
<script type="text/javascript">
var url ='http://localhost/scanres.php?type=<?php echo $type; ?>&id=<?php echo $id;?>&rescan=<?php echo $rescan; ?>';
var url1 ='http://localhost/scanstop.php?type=<?php echo $type; ?>&id=<?php echo $id;?>';

function Ajaxcmnt(){
Ajaxtps();
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

var q = setInterval("Ajaxcmnt()",1000); 

function Ajaxtps(){
var xmllHttp;
	try{	
		xmllHttp=new XMLHttpRequest();// Firefox, Opera 8.0+, Safari
	}
	catch (e){
		try{
			xmllHttp=new ActiveXObject("Msxml2.XMLHTTP"); // Internet Explorer
		}
		catch (e){
		    try{
				xmllHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e){
				alert("No AJAX!?");
				return false;
			}
		}
	}

xmllHttp.onreadystatechange=function(){
	if(xmllHttp.readyState==4){
;
		var m = xmllHttp.responseText;
		if(m=="1")
		{
		clearInterval(q);
		}

	}
}
xmllHttp.open("GET",url1,true);
xmllHttp.send(null);
}
</script>
<title>scanning</title>
</head>
<body>
<div id="cmnt"></div>
</body>
</html>



