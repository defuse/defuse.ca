<?php
/*
 * Defuse.ca
 * Copyright (C) 2013  Taylor Hornby
 * 
 * This file is part of Defuse.
 * 
 * Defuse is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * Defuse is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
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
