This AES class comes in 2 flavors:

1. AES.class.php - PHP 5 class file, compatible with 32-bit and 64-bit systems
2. AES_4.class.php - PHP 4 class file, compatible with 32-bit and 64-bit systems.



Please note that PHP is not known for its efficiency.  Being that it is a loose-typed scripting language
it exibits very poor execution time in regards to array and bit-manipulation (which is all AES is).

If you are looking for a very fast AES cipher for PHP, you won't find one.  With that being said, I hope
you enjoy this class and use it wherever appropriate.  

For the most optimal performance you should stream your input data (plaintext/ciphertext) through 
encryptBlock() and decryptBlock() instead of using encrypt() and decrypt().



