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
<body>

<?php
if(isset($_GET['e']))
{
    header('Location: s.php?s=' . urlencode(base64_encode($_GET['e'])));
}
elseif(isset($_GET['s']))
{
    ?>
    <div style="font-size: 300pt;">
    <b><?echo htmlspecialchars(base64_decode($_GET['s']), ENT_QUOTES); ?></b>
    </div>
    <?
}
else
{
?>
    <form action="s.php" method="get">
    <input type="text" name="e" value="Shout!" />
    <input type="submit" value="shout" />
    </form>
<?
}
?>
</body>
</html>
