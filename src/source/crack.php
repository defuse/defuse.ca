<?php
/*
 * PHP Hash Cracker v1.1: https://defuse.ca/php-hash-cracker.htm
 * Usage: php crack.php <arguments>
 * Arguments:
 *     -w <wordlist>           Wordlist or "stdin" for standard input.
 *     -s <start line number>  Skip lines of the wordlist.
 *     -o <output file>        Save session/results to file.
 *     -f <output file>        Recover crashed session.
 *     -c <hash>               The hash to crack.
 *     -t <hash type>          The type of hash.
 *     -l <left salt>          Salt prepended to the password.
 *     -r <right salt>         Salt appended to the password.
 *     -d <s>                  Hash <s> with all supported hash types.
 *     -h                      Print help message.
 * ** All other arguments are ignored when using -f or -d **
 */
    // ========================= CONSTANTS ================================
    define("DEFUSE_URL", "https://defuse.ca/php-hash-cracker.htm");
    define("VERSION", "v1.1");

    // Print a status update every OUTPUT_RATE lines.
    define("OUTPUT_RATE", 1000000);
    // Save the session status every BACKUP_RATE lines.
    define("BACKUP_RATE", 1000000);


    // ========================= ENTRY POINT ===============================

    $opts = getopt("d:w:s:c:t:l:r:f:o:h::");
    $args = parseOptions($opts);

    checkHashFormat($args['hash_str'], $args['hash_type']);
    
    printPreamble($args['wordlist'], $args['start_line'], $args['hash_str'],
                    $args['hash_type'], $args['left_salt'],
                    $args['right_salt'], $args['output_file']);

    $res = crack($args['wordlist'], $args['start_line'], $args['hash_str'], 
                    $args['hash_type'], $args['left_salt'], 
                    $args['right_salt'], $args['output_file']);
    if($res == false)
        printUsage("Error reading wordlist.");

    // ===================== HASH CRACKING FUNCTIONS ========================

    function crack($wordlist, $start_line, $hash_str, $hash_type,  $left_salt, 
                    $right_salt, $output_file)
    {
        $fh = fopen($wordlist, "r");
        if($fh === false)
            return false;

        $line_number = 0;

        // Find the start line
        if($start_line > 0)
            echo "Seeking start line...\n";
        while($line_number < $start_line && ($line = fgets($fh)) !== false)
            $line_number++;
        
        if($line_number > 0)
            echo "Starting at line $line_number...\n";

        $success = false;
        // For each line in the file...
        while(($line = fgets($fh)) !== false)
        {
            // ... hash it ...
            $line = trim($line);
            $hash = computeHash($hash_type, 
                                $left_salt . $line . $right_salt, $hash_str);
            // ... and compare it with the hash being cracked.
            if($hash == $hash_str)
            {
                outputSuccess($line, $hash_str, $hash_type, $left_salt, $right_salt);
                writeSuccessToFile($output_file, $wordlist, $line_number, $hash_str, 
                                    $hash_type, $left_salt, $right_salt, $line);
                $success = true;
                break;
            }
            
            // Print a status update every OUTPUT_RATE lines in the wordlist.
            if($line_number > 0 && $line_number % OUTPUT_RATE == 0)
            {
                outputStatus($line, $line_number);
            }

            // Save the session every BACKUP_RATE lines in the wordlist.
            if($line_number % BACKUP_RATE == 0)
            {
                writeStatusToFile($output_file, $wordlist, $line_number, 
                                    $hash_str, $hash_type, $left_salt, $right_salt);
            }

            $line_number++;
        }

        fclose($fh);

        if($success == false)
        {
            outputFailure();
            writeFailureToFile($output_file, $wordlist, $hash_str, $hash_type, 
                                $left_salt, $right_salt);
        }

        return true;
    }

    function computeHash($hash_type, $plaintext, $cracking_hash)
    {
        //  If you want absolute maximum speed, comment out the 
        //  uneccessary code in this method.

        // Performance hack: 
        //  We don't want to call in_array for every hash, since it's slow.
        //  So we cache the result of in_array for the last $hash_type we
        //  were called with. 
        static $builtin_hash = null;
        static $last_type = null;
        if(is_null($builtin_hash) || $last_type != $hash_type)
        {
            $builtin_hash = in_array($hash_type, hash_algos());
            $last_type = $hash_type;
        }

        if($builtin_hash)
        {
            return hash($hash_type, $plaintext);
        }
        elseif($hash_type == "ntlm")
        {
            $hash = bin2hex(NTLMHash($plaintext));
            return $hash;
        }
        elseif($hash_type == "md5md5")
        {
            return hash("md5", hash("md5", $plaintext));
        }
        elseif($hash_type == "mysql41")
        {
            return hash("sha1", hash("sha1", $plaintext, true));
        }
        elseif($hash_type == "lm")
        {
             return LMhash($plaintext);        
        }
        elseif($hash_type == "crypt")
        {
            return crypt($plaintext, $cracking_hash);
        }
        else
        {
            return false;
        }
    }

    function printAllHashesOf($string)
    {
        printHash("NTLM", computeHash("ntlm", $string, ""));
        printHash("LM", computeHash("lm", $string, ""));
        printHash("MySQL 4.1", computeHash("mysql41", $string, ""));
        $hashes = hash_algos();
        foreach($hashes as $hashType)
        {
            printHash($hashType, computeHash($hashType, $string, ""));
        }
        printHash("md5(md5)", computeHash("md5md5", $string, ""));
        die;
    }

    function printHash($type, $value)
    {
        $type .= ":";
        $type .= str_repeat(" ", 20 - strlen($type));
        echo $type . $value . "\n";
    }

    // ===================== STATUS OUTPUT FUNCTIONS ========================

    function outputSuccess($password, $hash_str, $hash_type, $left_salt, $right_salt)
    {
        echo "\nPASSWORD FOUND: \t$password (0x", bin2hex($password), ")\n";
        echo "HASH:\t\t\t$hash_str\n";
        echo "HASH TYPE:\t\t$hash_type\n";
        if(!empty($left_salt))
            echo "LEFT SALT: \t\t$left_salt (0x", bin2hex($left_salt), ")\n";
        if(!empty($right_salt))
            echo "RIGHT SALT: \t\t$right_salt (0x", bin2hex($right_salt), ")\n";
    }

    function outputStatus($line, $line_number)
    {
        static $start_time = null;
        $padded_line = $line . str_repeat(" ", max(25 - strlen($line), 0));
        echo "Current Line: $line_number\t Current Password: $padded_line";
        if(!is_null($start_time))
        {
            $duration = microtime(true) - $start_time;
            $rate = (int)((OUTPUT_RATE / $duration) / 1000);
            echo " Rate: $rate k/s\n";
        }
        else
        {
            echo "\n";
        }
        $start_time = microtime(true);
    }

    function outputFailure()
    {
        echo "PASSWORD NOT FOUND.\n";
    }

    function printPreamble($wordlist, $start_line, $hash_str, $hash_type,  $left_salt, 
                    $right_salt, $output_file)
    {
        echo "Defuse Cyber-Security's Hash Cracking Script - " . VERSION . "\n";
        echo "Homepage: " . DEFUSE_URL . "\n\n";

        $now = date("F j, Y, g:i a");
        echo "Begin execution: $now \n";
        echo "Wordlist: $wordlist \n";
        echo "Start line: $start_line \n";
        echo "Hash: $hash_str \n";
        echo "Hash type: $hash_type \n";
        if(!empty($left_salt))
            echo "Left salt: $left_salt \n";
        if(!empty($right_salt))
            echo "Right salt: $right_salt \n";
        if(!is_null($output_file))
            echo "Session file: $output_file \n";

        echo "\n";
    }


    // ===================== FILE OUTPUT FUNCTIONS ========================

    function writeStatusToFile($file, $wordlist, $line_number, $hash_str, 
                                $hash_type, $left_salt, $right_salt)
    {
        if($file == null)
            return;
        $status = "Status: CRACKING\n" .
                    $wordlist . "\n" .
                    $line_number . "\n" .
                    $hash_type . "\n" .
                    bin2hex($hash_str) . "\n" .
                    bin2hex($left_salt) . "\n" .
                    bin2hex($right_salt) . "\n";
        file_put_contents($file, $status);
    }

    function writeSuccessToFile($file, $wordlist, $line_number, $hash_str, 
                                $hash_type, $left_salt, $right_salt, $password)
    {
        if($file == null)
            return;

        $status = "Status: FOUND\n" .
                    "\tPassword: $password (0x" . bin2hex($password) . ")\n" .
                    "\tIn wordlist: $wordlist\n" .
                    "\tLine number: $line_number\n" .
                    "\tHash: $hash_str\n" .
                    "\tHash type: $hash_type\n";
        if(!empty($left_salt))
            $status .= "\tLeft salt: $left_salt (0x" . bin2hex($left_salt) . ")\n";
        if(!empty($right_salt))
            $status .= "\tRight salt: $right_salt (0x" . bin2hex($right_salt) . ")\n";

        file_put_contents($file, $status);
    }

    function writeFailureToFile($file, $wordlist, $hash_str, $hash_type, $left_salt, $right_salt)
    {
        if($file == null)
            return;
        $status = "Status: NOT FOUND\n" . 
                    "\tIn wordlist: $wordlist\n" .
                    "\tHash: $hash_str\n" .
                    "\tHash type: $hash_type\n";
        if(!empty($left_salt))
            $status .= "\tLeft salt: $left_salt (0x" . bin2hex($left_salt) . ")\n";
        if(!empty($right_salt))
            $status .= "\tRight salt: $right_salt (0x" . bin2hex($right_salt) . ")\n";

        file_put_contents($file, $status);
    }

    // Prints the help message, with an optional error message, then exits.
    function printUsage($msg = "")
    {
        echo "PHP Hash Cracker " . VERSION . ": " . DEFUSE_URL . "\n";
        if(!empty($msg))
            echo "** $msg ** \n";
        echo "Usage: php crack.php <arguments>\n";
        echo "Arguments:\n" .
             "\t-w <wordlist>\t\t\tWordlist or \"stdin\" for standard input.\n" .
             "\t-s <start line number>\t\tSkip lines of the wordlist.\n" .
             "\t-o <output file>\t\tSave session/results to file.\n" .
             "\t-f <output file>\t\tRecover crashed session.\n" . 
             "\t-c <hash>\t\t\tThe hash to crack.\n" .
             "\t-t <hash type>\t\t\tThe type of hash.\n" .
             "\t-l <left salt>\t\t\tSalt prepended to the password.\n" .
             "\t-r <right salt>\t\t\tSalt appended to the password.\n" .
             "\t-d <s>\t\t\t\tHash <s> with all supported hash types.\n" .
             "\t-h              \t\tPrint help message.\n";
        echo "** All other arguments are ignored when using -f or -d **\n";
        echo "\nSupported Hash Types:\n";
        $hashes = hash_algos();
        echo "\tcrypt lm ntlm mysql41 md5md5";
        $i = 0;
        foreach($hashes as $hashtype)
        {
            if($i % 4 == 0)
            {
                echo "\n\t";
            }
            echo "$hashtype ";
            $i++;
        }
        echo "\n";
        echo "** Do not include the * in mysql41 hashes. **\n";
        die;
    }

    // ===================== OPTION PARSING FUNCTIONS ========================

    function parseOptions($opts)
    {
        $args = array();
        if(isset($opts['f']))
        {
            $args = parseSessionFile($opts, $opts['f']);
        }
        else
        {

            if(isset($opts['h']))
                printUsage();

            if(isset($opts['d']))
                printAllHashesOf($opts['d']);

            if(isset($opts['w']))
                $args['wordlist'] = ($opts['w'] == "stdin") ? 
                                        "php://stdin" : realpath($opts['w']);
            else
                printUsage("Please specify a wordlist.");

            if(isset($opts['o']))
            {
                if($args['wordlist'] == "php://stdin")
                    printUsage("Cannot use -o with standard input.");
                $args['output_file'] = $opts['o'];
                if(file_exists($args['output_file']))
                {
                    printUsage("Output file exists. Did you mean -f?");
                }
            }
            else
                $args['output_file'] = null;

            if(empty($args['wordlist']))
                printUsage("Invalid wordlist path.");

            if(isset($opts['c']))
                $args['hash_str'] = $opts['c'];
            else
                printUsage("Please specify a hash.");

            if(isset($opts['t']))
                $args['hash_type'] = strtolower($opts['t']);
            else
                printUsage("Please specify a hash type.");

            if(isset($opts['l']))
                $args['left_salt'] = $opts['l'];
            else
                $args['left_salt'] = "";

            if(isset($opts['r']))
                $args['right_salt'] = $opts['r'];
            else
                $args['right_salt'] = "";

            if(isset($opts['s']))
                $args['start_line'] = (int)$opts['l'];
            else
                $args['start_line'] = 0;
        }

        if($args['hash_type'] != "crypt")
        {
            $args['hash_str'] = strtolower($args['hash_str']);
        }

        return $args;
    }

    function parseSessionFile($opts, $path)
    {
        // See writeStatusToFile() for the file format.

        $file = file_get_contents($path);
        if($file === false)
            printUsage("Cannot read file.");

        if(strpos($file, "Status: CRACKING") !== false)
        {
            $args = array();
            $params = explode("\n", $file);
            if(count($params) < 7)
                die("Malformed file.\n");
            $args['wordlist'] = $params[1];
            $args['start_line'] = (int)$params[2];
            $args['hash_type'] = $params[3];
            $args['hash_str'] = hex2bin($params[4]);
            $args['left_salt'] = hex2bin($params[5]);
            $args['right_salt'] = hex2bin($params[6]);
            $args['output_file'] = $opts['f'];
            return $args;
        }
        else
        {
            echo $file;
            die;
        }
    }

    function checkHashFormat($hash_str, $hash_type)
    {
        // Make sure we support the hash type
        if(($sample = computeHash($hash_type, "", "")) === false)
            printUsage("Invalid hash type.");

        // Make sure the hash "looks right"
        if($hash_type != "crypt" && strlen($sample) != strlen($hash_str))
            printUsage("Hash string length is not the expected length.");
    }


    // ================== NTLM and LM Hash Functions ========================

    // NTLM Code Source: http://www.php.net/manual/en/ref.hash.php#82018
    function NTLMHash($Input) {
      // Convert the password from UTF8 to UTF16 (little endian)
      $Input=@iconv('UTF-8','UTF-16LE',$Input);
      $MD4Hash=hash('md4',$Input, true);
      return $MD4Hash;
    }

    // LM Code Source: http://www.php.net/manual/en/ref.hash.php#84587
    function LMhash($string)
    {
        $string = strtoupper(substr($string,0,14));
    
        $p1 = LMhash_DESencrypt(substr($string, 0, 7));
        $p2 = LMhash_DESencrypt(substr($string, 7, 7));
    
        return bin2hex($p1.$p2);
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

    function hex2bin($hex)
    {
        return pack("H*", $hex);
    }

?>

