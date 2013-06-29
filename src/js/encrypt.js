/* This is a wrapper around the Stanford JavaScript Cryptography Library...
 *              https://crypto.stanford.edu/sjcl/
 * ...to provide simple text encryption and decryption.
 *
 * To use it, just download sjcl and include it in your HTML...
 *      <head>
 *       ...
 *         <script type="text/javascript" src="sjcl.js"></script>
 *       ...
 *      </head>
 * ...and provide at least 256 bits of entropy from the server with
 * sjcl.random.addEntropy(). You should also call sjcl.random.startCollectors().
 */

var encrypt;
if (!encrypt) var encrypt = {};

/* Encrypts a string.
 * Parameters:
 *   password  - A string containing the encryption password.
 *   salt      - A random and unique (per encryption) string.
 *   iv        - A random block (128 bits) encoded in hex.
 *   plaintext - The string to encrypt.
 * Returns: The encrypted string, encoded in base64.
 */
encrypt.encrypt = function(password, plaintext) {
    /* Convert the plaintext string into a bitArray */
    var pt_bits = sjcl.codec.utf8String.toBits(plaintext);

    /* Genrate a random salt and iv */
    var salt_bits = sjcl.random.randomWords(8);
    var iv_bits = sjcl.random.randomWords(4);

    /* Generate the key from the password and salt with PBKDF2 */
    var key = sjcl.misc.pbkdf2(password, salt_bits, 5000, 128);

    /* Encrypt the plaintext */
    var aes = new sjcl.cipher.aes(key);
    var ct_bits = sjcl.mode.ocb2.encrypt(aes, pt_bits, iv_bits, [], 64);

    return encrypt.pack_ciphertext(5000, salt_bits, iv_bits, ct_bits);
};

/* Decrypts a string that was encrypted with encrypt.encrypt().
 * Parameters:
 *   password   - A string containing the encryption password.
 *   salt       - The same 'salt' value that was given to encrypt.encrypt().
 *   iv         - The 'iv' value that was given to encrypt.encrypt().
 *   ciphertext - The string returned from encrypt.encrypt().
 */
encrypt.decrypt = function(password, ciphertext) {
    var unpacked = encrypt.unpack_ciphertext(ciphertext);

    /* Generate the key from the password and salt with PBKDF2 */
    var key = sjcl.misc.pbkdf2(password, unpacked.salt_bits, unpacked.iterations, 128);

    /* Decrypt the ciphertext */
    var aes = new sjcl.cipher.aes(key);
    var pt_bits = sjcl.mode.ocb2.decrypt(aes, unpacked.ct_bits, unpacked.iv_bits, [], 64);

    /* Return the plaintext in string form */
    return sjcl.codec.utf8String.fromBits(pt_bits);
};

/* Used internally to pack the all the parameters into one string. */
encrypt.pack_ciphertext = function(iterations, salt_bits, iv_bits, ct_bits) {
    return iterations.toString() + ':' +
           sjcl.codec.hex.fromBits(salt_bits) + ':' + 
           sjcl.codec.hex.fromBits(iv_bits) + ':' + 
           sjcl.codec.hex.fromBits(ct_bits);
};

/* Used internally to unpack a string produced by encrypt.pack_ciphertext(). */
encrypt.unpack_ciphertext = function(packed_ciphertext) {
    var split = packed_ciphertext.split(":");
    if (split.length !== 4) {
        throw new sjcl.exception.corrupt("Incomplete ciphertext.");
    }
    var unpacked = {};
    unpacked.iterations = parseInt(split[0]);
    unpacked.salt_bits = sjcl.codec.hex.toBits(split[1]);
    unpacked.iv_bits = sjcl.codec.hex.toBits(split[2]);
    unpacked.ct_bits = sjcl.codec.hex.toBits(split[3]);
    return unpacked;
};
