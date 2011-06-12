<html>
<head>
<style>
body{
	color: white;
	background-color:black;
}
</style>
</head>
<body>
<center>
<?php
$ip = $_SERVER['REMOTE_ADDR'];
echo "IP address: <br />" . $ip;
echo "<br /><br />Hostname: <br />" . GetHostByAddr($ip);
echo "<br /><br />" . $_SERVER['HTTP_USER_AGENT'];
?>
</center>
</body>
</html>
