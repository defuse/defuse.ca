var res ='http://localhost/sendmail.php?type=<?php echo $type; ?>&hash=<?php echo $id;?>';
function sendemail(){
var smailHttp;
	try{	
		smailHttp=new XMLHttpRequest();// Firefox, Opera 8.0+, Safari
	}
	catch (e){
		try{
			smailHttp=new ActiveXObject("Msxml2.XMLHTTP"); // Internet Explorer
		}
		catch (e){
		    try{
				smailHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e){
				alert("No AJAX!?");
				return false;
			}
		}
	}

smailHttp.onreadystatechange=function(){
	if(smailHttp.readyState==4){
;
		document.getElementById('mail').innerHTML= = smailHttp.responseText;
		

	}
}
smailHttp.open("GET",url1,true);
smailHttp.send(null);
}