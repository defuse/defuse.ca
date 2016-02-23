<?php

require_once('pastebin.php');

if (php_sapi_name() !== "cli") {
    exit(1);
}

if (count($argv) !== 2) {
    echo "Usage: php delete_paste.php <url key>\n";
    exit(1);
}

delete_post($argv[1]);
