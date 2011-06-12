<?php
session_start();
include('libs.php');
include('aes.php');
$subject = "Virus Scanning Complete";
$to = "mail@server.com";
$from = "noreply@scan.blizma.com";


//data
$msg = "FILENAME: "  .$name    ."<br>\n";
$msg .= "EMAIL: "  .$detections    ."<br>\n";


//Headers
$headers  = "MIME-Version: 1.0
";
$headers .= "Content-type: text/html; charset=UTF-8
";
$headers .= "From: <".$from. ">" ;


//send
mail($to, $subject, $msg, $headers);


?>