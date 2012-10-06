<?php
if(isset($_POST['submit']))
{
    $finfo = $_FILES["imgfile"];
    $allowedExts = array("jpg", "jpeg", "gif", "png");
    $extension = explode(".", $finfo["name"]);
    $extension = $extension[count($extension) - 1];
    if(
        (
            ($finfo["type"] == "image/gif") ||
            ($finfo["type"] == "image/jpeg") ||
            ($finfo["type"] == "image/png") ||
            ($finfo["type"] == "image/pjpeg")
        ) &&
        $finfo["size"] < 5*1024*1024 &&
        in_array($extension, $allowedExts))
    {
        if($finfo["error"] > 0)
            die('wtf not an image...');

        $key = bin2hex(mcrypt_create_iv(32));
        $filename = "filez/$key.$extension";
        copy($finfo["tmp_name"], $filename);
        header("Location: $filename");
        die();
    }
    else
    {
        die('wtf not an image...');
    }
}
?>
<html>
<head>
</head>
<body>
Upload an image:
<form action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="imgfile">
    <input type="submit" name="submit" value="Upload" />
</form>
</body>
</html>
