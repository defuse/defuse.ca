/*
 * 			Defuse Security's JavaScript library
 * 			       https://defuse.ca/
 *
 *                      PUBLIC DOMAIN CONTRIBUTION NOTICE							 
 * 	This work has been explicitly placed into the Public Domain for the
 * 	benefit of anyone who may find it useful for any purpose whatsoever.
 */

var fxw;
if (!fxw) var fxw = {};

fxw.allhtmlsani = function(text)
{
	var sani = [];
	var i = 0;
	for(i = 0; i < text.length; i++)
	{
		var curChar = text.charCodeAt(i);
		//Sanitize curChar if it isn't a CR, LF, TAB, or SPACE
		if(curChar != 10 && curChar != 13 && curChar != 9 && curChar != 32)
		{
			sani.push("&#" + curChar + ";"); 
		}
		else
		{
			sani.push(String.fromCharCode(curChar));
		}
	}
	text = sani.join('');

	//Now deal with spaces, tabs, and newlines
	text = text.replace(/\r\n/g, "\n");
	text = text.replace(/\r/g, "\n");
	text = text.replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;");
	text = text.replace(/\s\s/g, "&nbsp;&nbsp;");
	text = text.replace(/\n/g, "<br />");
	return text;
}

/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */
/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */
/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */
/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */
/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */
/* !!!! THESE ENCRYPTION FUNCTIONS ARE DEPRECATED. USE encrypt.js. !!!! */

//password - the password used to derrive the key
//salt - random salt that should be unique per encryption. MUST be 256 bits hex
//iv - random IV that should be unique per encryption. MUST be 256 bits hex
//plainText - the unicode text to encrypt
//returns - hex encoded ciphertext
fxw.encrypt = function(password, salt, iv, plainText) 
{
	var key = jsHash.sha2.arr_sha256(password + salt);
	var ivAsBytes = cryptoHelpers.toNumbers(iv);
	var ptUTF8Bytes = cryptoHelpers.convertStringToByteArray(cryptoHelpers.encode_utf8(plainText));
	var cipherText = cryptoHelpers.toHex(slowAES.encrypt(ptUTF8Bytes, slowAES.modeOfOperation.CBC, key, ivAsBytes));
	var checksum = jsHash.sha2.hex_sha256(password + salt + iv + cipherText);
	return checksum + salt + iv + cipherText;
}

//password - the password used for encryption
//cipherText - the ciphertext to check for validity
//returns true if the cipherText hasn't been tampered with, false otherwise
fxw.validate = function(password, cipherText)
{
	var checksum = cipherText.substring(0, 64);
	var validChecksum = jsHash.sha2.hex_sha256(password + cipherText.substring(64));
	return checksum == validChecksum;
}

//password - password used to create the decryption key
//cipherText - the cipherText to decrypt
//returns - the unicode string decrypted text
//WARNING: this function does NOT check for authenticity, always use fxw.validate first!!
fxw.decrypt = function(password, cipherText)
{
	var salt = cipherText.substring(64, 128);
	var iv = cipherText.substring(128, 192);
	var data = cryptoHelpers.toNumbers(cipherText.substring(192));
	var key = jsHash.sha2.arr_sha256(password + salt);
	var ivAsBytes = cryptoHelpers.toNumbers(iv);
	var utf8Plaintext = cryptoHelpers.convertByteArrayToString(slowAES.decrypt(data, slowAES.modeOfOperation.CBC, key, ivAsBytes));
	return cryptoHelpers.decode_utf8(utf8Plaintext);
}

