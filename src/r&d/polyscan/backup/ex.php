<?php
$dir = "C:/scans/v2kXWZUE/";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
		if($file !== "." && $file !== ".." && $file !== "index.php" && filetype($dir . $file) !== 'dir'){
		print 'done';
        }
		}
        closedir($dh);
    }
}

?>