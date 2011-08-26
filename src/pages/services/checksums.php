<?php
    $sanidata = "";

    if(isset($_POST['data']))
    {
        $data = $_POST['data'];
        if(isset($_POST['normalize']) && $_POST['normalize'] == "yes")
        {
            $data = str_replace("\r", "", $data);
            $data = str_replace("\n", "", $data);
        }
        $sanidata = htmlentities($data, ENT_QUOTES);
    }
    elseif(isset($_FILES['filetohash']['tmp_name']) && file_exists($_FILES['filetohash']['tmp_name']))
    {
        if(filesize($_FILES['filetohash']['tmp_name']) > 5 * 1024 * 1024)
        {
            @unlink($_FILES['filetohash']['tmp_name']);
            die('File is too big. Max: 5MB. <a href="checksums.htm">go back</a>.');
        }
        $data = file_get_contents($_FILES['filetohash']['tmp_name']);
        @unlink($_FILES['filetohash']['tmp_name']);
        $sanidata = "";
    }
?>

<h1>Online Text &amp; File Checksum Calculator</h1>
<p>This page lets you hash ASCII text or a file with many different hash algorithms. Checksums are commonly used to verify the integrety of data. The most common use is to verify that a file has been downloaded without error. The data you enter here is 100% private, neither the data nor hash values are ever recorded.</p>
<p><strong>Enter some ASCII or UNICODE text...</strong></p>
<form action="checksums.htm#checksums" method="post">
    <textarea name="data" rows="15" cols="40" style="width:100%;" ><?php echo $sanidata; ?></textarea>
    <br />
    <input type="checkbox" name="normalize" value="yes" />Remove line endings <br /><input type="submit" name="submit" value="Calculate checksums.." />
</form>
<h3>File (5MB MAX)</h3>
<form action="checksums.htm#checksums" method="post" enctype="multipart/form-data">
<input type="file" name="filetohash" /><input type="submit" name="hashfile" value="Calculate checksums..." />
</form>
<?php
if(isset($data))
{
    $filename = "";
    if(isset($_FILES['filetohash']['name']))
    {
        $filename = "(" . htmlentities($_FILES['filetohash']['name'], ENT_QUOTES) . ")";
    }
    echo "<a name=\"checksums\"></a><h2>Checkums $filename</h2>";
    $hashes = hash_algos();
    echo "<table border=\"0\" cellpadding=\"10\" style=\"font-family:monospace;\" >";
    foreach($hashes as $hashtype)
    {
        $hash = hash($hashtype, $data);
        if(strlen($hash) > 64)
        {
            $hash = substr($hash,0,64) . "<br />" . substr($hash,64);
        }
        echo "<tr><th>$hashtype</th><td>$hash</td></tr>";
    }
    echo "</table>";
}
else
{
    echo "<h2>Supported Hash Algorithms</h2><p>";
    $hashes = hash_algos();
    foreach($hashes as $hashtype)
    {
        echo "$hashtype ";
    }
    echo "</p>";
}
?>
