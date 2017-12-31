<?php

require_once('/etc/creds.php');

// The webserver **MUST** be configured to never execute code within this
// directory (otherwise remote-code execution is possible), **AND** serve all
// files as *DOWNLOADS** (otherwise XSS is possible).
$STORAGE_DIR = realpath(dirname(__FILE__)) . "/friend-uploads";
$STORAGE_DIR_URL = "https://defuse.ca/friend-uploads";
$MAX_BYTES = 5*1024*1024*1024;

function sanitize_name($original_name)
{
    $whitelist = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+-_. ";
    $safe_name = "";
    for ($i = 0; $i < strlen($original_name); $i++) {
        $c = $original_name[$i];
        if (strpos($whitelist, $c) !== FALSE) {
            $safe_name .= $c;
        }
    }
    return $safe_name;
}

$creds = Creds::getCredentials("friendupload");
if ($_GET['upload_auth'] !== $creds[C_PASS]) {
    die("I haven't (yet) given you permission to use this uploader, sorry.");
}

if (isset($_POST['submit'])) {
    if ($_FILES['uploadedfile']['size'] <= 0 || $_FILES['uploadedfile']['size'] > $MAX_BYTES) {
        var_dump($_FILES);
        die("I'm sorry, that file is too big or empty.");
    }

    $safe_name = sanitize_name($_FILES['uploadedfile']['name']);

    if ($safe_name == "") {
        die("I'm sorry, the file name cannot be empty.");
    }

    rename($_FILES['uploadedfile']['tmp_name'], $STORAGE_DIR . "/" . $safe_name);
    $xss_safe_url = htmlentities($STORAGE_DIR_URL . "/" . $safe_name);
?>
    <html>
    <head>
        <title>Upload Successful</title>
    </head>
    <body>
        <p>Your upload was successful. Your file can be downloaded at this URL:</p>
        <p><a href="<?php echo $xss_safe_url; ?>"><?php echo $xss_safe_url; ?></a></p>
    </body>
    </html>
<?
}

?>
<html>
<head>
    <title>File Upload</title>
</head>
<body>
    <h1>Private File Uploader for Taylor's Trusted Friends</h1>
    <p>Use this page to upload a file to my web server. All uploads will be automatically removed after 1 week.</p>
    <form enctype="multipart/form-data" action="/friend-upload.php?upload_auth=<?php echo htmlentities($_GET['upload_auth'], ENT_QUOTES);?>" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (int)$MAX_BYTES; ?>" />
        <input type="file" name="uploadedfile" />
        <input type="submit" name="submit" value="Upload File (Max. 5GB)" />
    </form>
</body>
</html>
