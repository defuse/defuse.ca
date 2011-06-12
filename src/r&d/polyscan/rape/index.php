<?php
include 'libs.php';
?>
<html>
<head>
	<title>alpha</title>
</head>
<body>
<?php
nextlast();
?>
<form action="upload.php" method="post" enctype="multipart/form-data">
Choose a file to scan: <input name="file" type="file" /><br />
Scan Archive? <input name="archive" type="checkbox" /><br />
<input type="submit" name="submit" value="Scan" />
</form>
</body>
</html>