<html>
<head>
<style>
*{
margin: 0; padding: 0;
}
body{
	background-color:white;
	background-repeat: no-repeat;
	color:black;
}
#headrow{
	height: 120px;
}
#maintable{
	width: 100%;
}
#leftside{
width:100px;
vertical-align:top;
}
#rightside{
width:260px;
padding-left:20px;
vertical-align:top;
}
#tablecontent{
vertical-align:top;
}
</style>
<script type="text/javascript">
var url ='http://localhost/new/data.php';
function ajaxdata(){
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

setInterval("ajaxdata()",1000); 
</script>
</head>
<body>
<table id="maintable">
<!--<tr id="headrow"><td></td></tr>
<tr><td id="leftside">
<table style="text-align:center; width:100%">
<tr><td><a href="index.php">Home</a></td></tr>
<tr><td><a href="index.php?p=register">Register</a></td></tr>
<tr><td><a href="index.php?p=login">Login</a></td></tr>
<tr><td><a href="index.php?p=view_finances">Finances</a></td></tr>
</table> -->

</td><td id="tablecontent">
<?php
$page = "";
switch($_GET['p'])
{
	case "":
	case "index":
		$page = "upload.php";
		break;
	case "view":
		$page = "view.php";
		break;
	case "view_archive":
		$page = "view_archive.php";
		break;
	case "register":
		$page = "register.php";
		break;
	case "login":
		$page = "login.php";
		break;
	case "view_finances":
		$page = "view_finances.php";
		break;
	case "rescan":
		$page = "rescanselect.php";
		break;
	default:
		$page = "";
		break;
}

if(!empty($page))
{
	include($page);
}
else
{
	echo "Not Found!";
}

?>
</td>
<td id="rightside">
<b>Data</b>
<br />

<div id="cmnt">
Queue Size:<br />
Next Update:<br />
Last Update:</div>

</td>
</tr>
</table>
</body>
</html>