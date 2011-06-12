<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>Max's AJAX File Uploader</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
   
<script language="javascript" type="text/javascript">
<!--
function startUpload(){
      document.getElementById('f1_upload_process').style.visibility = 'visible';
      document.getElementById('f1_upload_form').style.visibility = 'hidden';
      return true;
}

function stopUpload(success, rescan, hash, type){
      var result = '';
      if (success == 1){
		if(rescan == 1){
			result = 'This File has Already Analysed previously.<br /><a href="../rescan.php?type=file&id=' + hash + '">Rescan this again</a> || <a href="../scanner.php?type=file&id=' + $hash + '">Display Result</a>';
		} else {
			document.location='http://localhost/scanner.php?type=file&id=' + hash;
		}
        // result = '<span class="msg">The file was uploaded successfully!<\/span><br/><br/>';document.location='http://localhost/scanner.php?type=file&id=' + hash;
      }
      else {
         result = '<span class="emsg">There was an error during file upload!<\/span><br/><br/>';
      }
      document.getElementById('f1_upload_process').style.visibility = 'hidden';
      document.getElementById('f1_upload_form').innerHTML = result + '<label>File: <input name="file" type="file" size="30" /><\/label><label><input type="submit" name="submitBtn" class="sbtn" value="Upload" /><\/label>';
      document.getElementById('f1_upload_form').style.visibility = 'visible';      
      return true;   
}
//-->
</script>   
</head>

<body>
       <div id="container">
            <div id="header"><div id="header_left"></div>
            <div id="header_main">Max's AJAX File Uploader</div><div id="header_right"></div></div>
            <div id="content">
                <form action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
                     <p id="f1_upload_process">Loading...<br/><img src="loader.gif" /><br/></p>
                     <p id="f1_upload_form" align="center"><br/>
                         <label>File:  
                              <input name="file" type="file" size="30" />
                         </label>
                         <label>
                             <input type="submit" name="submitBtn" class="sbtn" value="Upload" />
                         </label>
                     </p>
                     
                     <iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
                 </form>
             </div>
             <div id="footer"><a href="http://www.ajaxf1.com" target="_blank">Powered by AJAX F1</a></div>
         </div>
                 
</body>   