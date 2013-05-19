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
