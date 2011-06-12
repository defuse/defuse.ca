<?php
$dir = "C:\scans\bQf8fdDw";


if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
		if($file !== "." && $file !== ".." && $file !== "index.php" && filetype($dir . $file) == 'dir'){
		$cont = file_get_contents($dir.$file);
		$cont = sqlsani(bin2hex($cont));
		AddToQueue($file, $cont, $filename, $rnd);
        }
		}
        closedir($dh);
    }
}

?>