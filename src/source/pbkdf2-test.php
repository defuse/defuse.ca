<?php

require('pbkdf2.php');

// The following test vectors were taken from RFC 6070.
// https://www.ietf.org/rfc/rfc6070.txt

$pbkdf2_vectors = array(
    array(
        'algorithm' => 'sha1', 
        'password' => "password", 
        'salt' => "salt", 
        'iterations' => 1, 
        'keylength' => 20, 
        'output' => "0c60c80f961f0e71f3a9b524af6012062fe037a6" 
        ),
    array(
        'algorithm' => 'sha1', 
        'password' => "password", 
        'salt' => "salt", 
        'iterations' => 2, 
        'keylength' => 20, 
        'output' => "ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957"
        ),
    array(
        'algorithm' => 'sha1', 
        'password' => "password", 
        'salt' => "salt", 
        'iterations' => 4096, 
        'keylength' => 20, 
        'output' => "4b007901b765489abead49d926f721d065a429c1"
        ),
    array(
        'algorithm' => 'sha1', 
        'password' => "passwordPASSWORDpassword", 
        'salt' => "saltSALTsaltSALTsaltSALTsaltSALTsalt", 
        'iterations' => 4096, 
        'keylength' => 25, 
        'output' => "3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038"
        ), 
    array(
        'algorithm' => 'sha1', 
        'password' => "pass\0word", 
        'salt' => "sa\0lt", 
        'iterations' => 4096, 
        'keylength' => 16, 
        'output' => "56fa6aa75548099dcc37d7f03425e0c3"
        ),            
);

foreach($pbkdf2_vectors as $test) {
    $realOut = pbkdf2(
        $test['algorithm'],
        $test['password'],
        $test['salt'],
        $test['iterations'],
        $test['keylength'],
        false
    );

    if($realOut == $test['output'])
        echo "PASS\n";
    else
        echo "FAIL\n";
}

?>
