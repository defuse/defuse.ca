<h1>Server Statistics</h1>

<?php

if(isset($_POST['p']) && md5("fh8f8hf" . $_POST['p']) == "7da89b20da373c455a31fc33301d2d10")
{
?>
    <h2>uptime</h2>
    <div class="code">
    <pre>
<?php
        passthru("uptime");
    ?>
    </pre>
    </div>

    <h2>ifconfig</h2>
    <div class="code">
    <pre>
<?php
        passthru("ifconfig");
    ?>
    </pre>
    </div>

    <h2>who</h2>
    <div class="code">
    <pre>
<?php
        passthru("who");
    ?>
    </pre>
    </div>

    <h2>netstat</h2>
    <div class="code">
    <pre>
<?php
        passthru("netstat -tn");
    ?>
    </pre>
    </div>
<?
}
else
{
?>
    <form action="/statzzz.htm" method="post">
        <input type="password" name="p" value="">
        <input type="submit" name="" value="Go" />
    </form>
<?
}

?>
