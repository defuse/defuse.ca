<?php
/*
 * Unbiased random password generator.
 * This code is placed into the public domain by Defuse Security.
 * WWW: https://defuse.ca/
 */
class PasswordGenerator
{
    public static function getASCIIPassword($length)
    {
        $printable = "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
        return self::getCustomPassword(str_split($printable), $length);
    }

    public static function getAlphaNumericPassword($length)
    {
        $alphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        return self::getCustomPassword(str_split($alphanum), $length);
    }

    public static function getHexPassword($length)
    {
        $hex = "0123456789ABCDEF";
        return self::getCustomPassword(str_split($hex), $length);
    }

    /* 
     * Create a random password composed of a custom character set.
     * $characterSet - An *array* of strings the password can be composed of.
     * $length - The number of random strings (in $characterSet) to include in the password.
     * Returns false on error (always check!).
     */
    public static function getCustomPassword($characterSet, $length)
    {
        if($length < 1 || !is_array($characterSet))
            return false;

        $charSetLen = count($characterSet);
        if($charSetLen == 0)
            return false;

        $random = self::getRandomInts($length * 2);
        $mask = self::getMinimalBitMask($charSetLen - 1); 

        $password = "";

        // To generate the password, we repeatedly try random integers and use the ones within the range
        // 0 to $charSetLen - 1 to select an index into the character set. This is the only known way to
        // make a truly unbiased random selection from a set using random binary data.

        // A poorly implemented or malicious RNG could cause an infinite loop, leading to a denial of service.
        // We need to guarantee termination, so $iterLimit holds the number of further iterations we will allow.
        // It is extremely unlikely (about 2^-64) that more than $length*64 random ints are needed.
        $iterLimit = max($length, $length * 64);   // If length is close to PHP_INT_MAX we don't want to overflow.
        $randIdx = 0;
        while(strlen($password) < $length)
        {
            if($randIdx >= count($random))
            {
                $random = self::getRandomInts(2*($length - count($password)));
                $randIdx = 0;
            }

            // This is wasteful, but RNGs are fast and doing otherwise adds complexity and bias.
            $c = $random[$randIdx++] & $mask;
            // Only use the random number if it is in range, otherwise try another (next iteration).
            if($c < $charSetLen)
                $password .= $characterSet[$c];

            // Guarantee termination
            $iterLimit--;
            if($iterLimit <= 0)
                return false;
        }

        return $password;
    }

    // Returns the smallest bit mask of all 1s such that ($toRepresent & mask) = $toRepresent.
    // $toRepresent must be an integer greater than or equal to 1.
    private static function getMinimalBitMask($toRepresent)
    {
        if($toRepresent < 1)
            return false;
        $mask = 0x1;
        while($mask < $toRepresent)
            $mask = ($mask << 1) | 1;
        return $mask;
    }

    // Returns an array of $numInts random integers between 0 and PHP_INT_MAX
    private static function getRandomInts($numInts)
    {
        $rawBinary = mcrypt_create_iv($numInts * PHP_INT_SIZE, MCRYPT_DEV_URANDOM);
        $ints = array();
        for($i = 0; $i < $numInts; $i+=PHP_INT_SIZE)
        {
            $thisInt = 0;
            for($j = 0; $j < PHP_INT_SIZE; $j++)
            {
                $thisInt = ($thisInt << 8) | (ord($rawBinary[$i+$j]) & 0xFF);
            }
            // Absolute value in two's compliment (with min int going to zero)
            $thisInt = $thisInt & PHP_INT_MAX;
            $ints[] = $thisInt;
        }
        return $ints;
    }
}
?>
