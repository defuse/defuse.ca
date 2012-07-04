<?php

require('pbkdf2.php');

function assert_true($result, $msg)
{
    if($result === true)
        echo "PASS: [$msg]\n";
    else
        echo "FAIL: [$msg]\n";
}

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

    assert_true($realOut === $test['output'], "PBKDF2 vector");
}

$good_hash = create_hash("foobar");
assert_true(validate_password("foobar", $good_hash), "Correct password");
assert_true(validate_password("foobar2", $good_hash) === false, "Wrong password");

$h1 = explode(":", create_hash(""));
$h2 = explode(":", create_hash(""));
assert_true($h1[HASH_PBKDF2_INDEX] != $h2[HASH_PBKDF2_INDEX], "Different hashes");
assert_true($h1[HASH_SALT_INDEX] != $h2[HASH_SALT_INDEX], "Different salts");

assert_true(slow_equals("",""), "Slow equals empty string");

assert_true(slow_equals("a", "b") === false, "Slow equals different");
assert_true(slow_equals("aa", "b") === false, "Slow equals different length 1");
assert_true(slow_equals("a", "bb") === false, "Slow equals different length 2");

echo "Example hash: $good_hash\n";

?>
