<?php

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1024.
     * $key_length - The length of the derived key in BYTES.
     * Returns: A $key_length-byte key derived from the password and salt (in binary).
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     */
    function pbkdf2($algorithm, $password, $salt, $count, $key_length)
    {
        $algorithm = strtolower($algorithm);
        if(!in_array($algorithm, hash_algos(), true))
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        if($count <= 0 || $key_length <= 0)
            die('PBKDF2 ERROR: Invalid parameters.');

        // number of blocks = ceil(key length / hash length)
        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = $key_length / $hash_length;
        if($key_length % $hash_length != 0)
            $block_count += 1;

        $output = "";
        for($i = 1; $i <= $block_count; $i++)
        {
            $output .= pbkdf2_f($password, $salt, $count, $i, $algorithm, $hash_length);
        }

        return substr($output, 0, $key_length);
    }

    /*
     * The pseudorandom function used by PBKDF2.
     * Definition: https://www.ietf.org/rfc/rfc2898.txt
     */
    function pbkdf2_f($password, $salt, $count, $i, $algorithm, $hash_length)
    {
        //$i encoded as 4 bytes, big endian.
        $last = $salt . chr(($i >> 24) % 256) . chr(($i >> 16) % 256) . chr(($i >> 8) % 256) . chr($i % 256);
        $xorsum = "";
        for($r = 0; $r < $count; $r++)
        {
            $u = hash_hmac($algorithm, $last, $password, true);
            $last = $u;
            if(empty($xorsum))
                $xorsum = $u;
            else
            {
                for($c = 0; $c < $hash_length; $c++)
                {
                    $xorsum[$c] = chr(ord(substr($xorsum, $c, 1)) ^ ord(substr($u, $c, 1)));
                }
            }
        }
        return $xorsum;
    }

?>
