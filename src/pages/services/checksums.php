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

    $supported_hashes = hash_algos();
    $supported_hashes[] = "NTLM";
    $supported_hashes[] = "LM";
    $supported_hashes[] = "md5(md5())";
    $supported_hashes[] = "MySQL4.1+";
    usort($supported_hashes, "hash_display_order");

    function hash_display_order($a, $b)
    {
        /* Put common hashes first for easy access. This defines their order. */
        static $COMMON_HASHES = array(
            "md5", "LM", "NTLM",
            "sha1", "sha256", "sha384", "sha512",
            "md5(md5())", "MySQL4.1+", "ripemd160", "whirlpool"
        ); 
        /* If they're both common, order them according to the array. */
        if (in_array($a, $COMMON_HASHES) && in_array($b, $COMMON_HASHES)) {
            return array_search($a, $COMMON_HASHES) - array_search($b, $COMMON_HASHES);
        /* If $a is common and $b isn't, put $a first. */
        } elseif (in_array($a, $COMMON_HASHES) && !in_array($b, $COMMON_HASHES)) {
            return -1;
        /* If $b is common and $a isn't, put $b first. */
        } elseif (!in_array($a, $COMMON_HASHES) && in_array($b, $COMMON_HASHES)) {
            return 1;
        /* Otherwise (they aren't common), order them lexicographically. */
        } else {
            return strnatcasecmp($a, $b);
        }
    }

    function extended_hash($hashType, $word, $binary)
    {
        if(in_array($hashType, hash_algos())) {
            return hash($hashType, $word, $binary);
        } elseif($hashType == "NTLM") {
            $hash = NTLMHash($word);
            if($binary == FALSE)
                $hash = bin2hex($hash);
            return $hash;
        } elseif($hashType == "md5(md5())") {
            return hash("md5", hash("md5", $word), $binary);
        } elseif($hashType == "MySQL4.1+") {
            return hash("sha1", hash("sha1", $word, true), $binary);
        } elseif($hashType == "LM") {
            $hash = LMHash($word);
            if ($binary == FALSE)
                $hash = bin2hex($hash);
            return $hash;
        }
    }

    function NTLMHash($Input)
    {
        // Convert the password from UTF8 to UTF16 (little endian)
        $Input=@iconv('UTF-8','UTF-16LE',$Input);
        $MD4Hash=hash('md4',$Input, true);
        return $MD4Hash;
    }

    function LMhash($string)
    {
        $string = strtoupper(substr($string,0,14));

        $p1 = LMhash_DESencrypt(substr($string, 0, 7));
        $p2 = LMhash_DESencrypt(substr($string, 7, 7));

        return $p1.$p2;
    }

    function LMhash_DESencrypt($string)
    {
        $key = array();
        $tmp = array();
        $len = strlen($string);

        for ($i=0; $i<7; ++$i)
            $tmp[] = $i < $len ? ord($string[$i]) : 0;

        $key[] = $tmp[0] & 254;
        $key[] = ($tmp[0] << 7) | ($tmp[1] >> 1);
        $key[] = ($tmp[1] << 6) | ($tmp[2] >> 2);
        $key[] = ($tmp[2] << 5) | ($tmp[3] >> 3);
        $key[] = ($tmp[3] << 4) | ($tmp[4] >> 4);
        $key[] = ($tmp[4] << 3) | ($tmp[5] >> 5);
        $key[] = ($tmp[5] << 2) | ($tmp[6] >> 6);
        $key[] = $tmp[6] << 1;
    
        $is = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($is, MCRYPT_RAND);
        $key0 = "";
    
        foreach ($key as $k)
            $key0 .= chr($k);
        $crypt = mcrypt_encrypt(MCRYPT_DES, $key0, "KGS!@#$%", MCRYPT_MODE_ECB, $iv);

        return $crypt;
    }

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

<?php
    Upvote::render_arrows(
        "onlinechecksums",
        "defuse_pages",
        "Online Hash Calculator",
        "A tool for computing hashes (MD5, SHA1, SHA2, etc.) of text and files.",
        "https://defuse.ca/checksums.htm"
    );
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
    echo "<table border=\"0\" cellpadding=\"10\" style=\"font-family:monospace;\" >";
    foreach($supported_hashes as $hashtype)
    {
        $hash = extended_hash($hashtype, $data, false);
        echo "<tr><th>$hashtype</th><td>$hash</td></tr>";
    }
    echo "</table>";
}
else
{
    echo "<h2>Supported Hash Algorithms</h2><p>";
    foreach($supported_hashes as $hashtype)
    {
        echo "$hashtype ";
    }
    echo "</p>";
}
?>
