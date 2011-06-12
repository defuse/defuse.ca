<?php
include("./AES_4.class.php");

$z = "abcdefgh01234567"; // 128-bit key
//$z = "abcdefghijkl012345678901"; // 192-bit key
//$z = "abcdefghijuklmno0123456789012345"; // 256-bit key

$aes = new AES($z);

//$data = file_get_contents("./example.txt");
$data ="lemme check the aes encryption";
$start = microtime(true);
echo "\n\nCipher-Text:\n" . $aes->encrypt($data) . "\n";
echo "\n\nPlain-Text:\n" . $aes->decrypt($aes->encrypt($data)) . "\n";
$end = microtime(true);

echo "\n\nExecution time: " . ($end - $start);

?>