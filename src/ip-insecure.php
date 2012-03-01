<html>
<head>
    <title>IP</title>
    <link rel="stylesheet" media="all" type="text/css" href="/main.css" />
</head>
<body>
<div style="font-size: 30pt; text-align: center;">
HTTP IP:
<?php
    echo htmlentities($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
?>
</div>
</body>
</html>
