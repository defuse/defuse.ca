<?php
include 'libs1.php';
$id = sqlsani($_GET['id']);
$type = sqlsani($_GET['type']);
?>
<html>
<head>


<script type="text/javascript">



var url ='http://localhost/scanres.php?type=<?php echo $type; ?>&hash=<?php echo $id;?>';
var url1 ='http://localhost/scanstop.php?type=<?php echo $type; ?>&hash=<?php echo $id;?>';
//var param = '<?php echo $id; ?>';
//var com = url + param;

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
		//setTimeout('Ajaxcmnt()',1000);
//Ajaxtps(); 
	}
}
xmlcmnt.open("GET",url,true);
xmlcmnt.send(null);
}
//Ajaxcmnt(); // Calls our GetShouts function.

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

xmllHttp.onreadystatechange=function(){ //ok fixed. sorry . continue *blush*
	if(xmllHttp.readyState==4){
		//document.getElementById('tps').innerHTML=xmllHttp.responseText;
		var m = xmllHttp.responseText;
		//alert(m);
		if(m=="1")
		{
		clearInterval(q);
		//clearInterval(n);
		//break;
		}

	}
}
xmllHttp.open("GET",url1,true);
xmllHttp.send(null);
}
//Ajaxtps(); // Calls our GetShouts function.
//setInterval("Ajaxtps()",1000); 
//alert(q);
</script>


<title>scanning</title>
</head>
<body>
<div id="cmnt"></div>
</body>
</html>



