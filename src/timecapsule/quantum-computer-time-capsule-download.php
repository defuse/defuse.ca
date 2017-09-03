<?php
$date_utc = new \DateTime(null, new \DateTimeZone("UTC"));
$filename = "MessagesToTheQuantumComputingFuture-" . $date_utc->format("Y-m-d\TH-i-sP") . ".txt";

header("Content-Type: text/plain");
header("Content-disposition: attachment; filename=\"$filename\"");

echo "==== TIME CAPSULE FOR A QUANTUM-COMPUTING FUTURE ====\n\n";
echo file_get_contents("archive-header.txt");
echo "\n==== SOURCE CODE: timecapsule-save.js ====\n";
echo file_get_contents("timecapsule-save.js");
echo "\n==== SOURCE CODE: tweetnacl-time-capsule.js ====\n";
echo file_get_contents("tweetnacl-time-capsule.js");
echo "\n==== SOURCE CODE: tweetnacl-util-time-capsule.js ====\n";
echo file_get_contents("tweetnacl-util-time-capsule.js");
echo "\n==== MESSAGES FOR THE FUTURE ====\n\n";
// TODO
echo "\n==== ADDITIONAL INFORMATION ====\n\n";
echo file_get_contents("archive-footer.txt");
?>
