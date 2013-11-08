<?php
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="rand.bin"');
    system("rdrand", $ret);
    if ($ret !== 0) {
        // fail safe
        echo mcrypt_create_iv(1024, MCRYPT_DEV_URANDOM);
        trigger_error("RDRAND is broken.", E_USER_ERROR);
    }
?>
