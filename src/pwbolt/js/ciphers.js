    //This file is part of Password Bolt.

    //Password Bolt is free software: you can redistribute it and/or modify
    //it under the terms of the GNU General Public License as published by
    //the Free Software Foundation, either version 3 of the License, or
    //(at your option) any later version.

    //Password Bolt is distributed in the hope that it will be useful,
    //but WITHOUT ANY WARRANTY; without even the implied warranty of
    //MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    //GNU General Public License for more details.

    //You should have received a copy of the GNU General Public License
    //along with Password Bolt.  If not, see <http://www.gnu.org/licenses/>.

//To comply with 'nothing up my sleeve policy, the salts are digits of PI.
//First 250 digits of PI
var BLOWFISH_SALT = "1415926535897932384626433832795028841971693993751058209749445923078164062862089986280348253421170679821480865132823066470938446095505822317253594081284811174502841027019385211055596446229489549303819644288109756659334461284756482";
//Next 250 digits of pi
var MAC_SALT = "337867831652712019091456485669234603486104543266482133936072602491412737245870066063155881748815209209628292540917153643678925903600113305305488204665213841469519415116094330572703657595919530921861173819326117931051185480744623799627";
//AES ENCRYPTION HAS BEEN REMOVED
//I don't trust the AES code in this crypto library, 
//it doesn't comply with test vectors so I will not use it.
//Using blowfish with a 448 bit key is VERY strong, 
//it would be much easier to recover the key with malware then with brute force
//The blowfish code doesn't comply with the test vectors, but I have analysed it and the mistake they made is not critical. (they just reversed which half of the block is which)

//uses the MakeNKey function in keygen.js to generate a 448 bit key 
//from the password and encrypts plainText with blowfish in CBC mode
function superEncrypt(plainText,password)
{
	var blowfishKey = MakeNKey(password + BLOWFISH_SALT,448);
	var mackey = MakeNKey(password + MAC_SALT,256);
	plainText = blowfishEncrypt(plainText,blowfishKey);
	var mac = SHA256(mackey + plainText + plainText.length);
	return mac + plainText;
}

//verifies that the cipherText hasn't been changed, returns the decrypted text.
function superDecrypt(cipherText,password)
{
	var mac = cipherText.substring(0,64);
	cipherText = cipherText.substring(64);
	var blowfishKey = MakeNKey(password + BLOWFISH_SALT,448);
	var mackey = MakeNKey(password + MAC_SALT,256);
	var realmac = SHA256(mackey + cipherText + cipherText.length);
	if(mac == realmac)
	{
		cipherText = blowfishDecrypt(cipherText,blowfishKey);
		return cipherText;
	}
	else
	{
		return "##!!wrong!!##";
	}
	
}

function blowfishEncrypt(data, key)
{
	var key = chars_from_hex(key);
	var bf = new Blowfish(key);
    	var ciphertext = bf.encrypt(data);
	return ciphertext;
}

function blowfishDecrypt(data,key)
{
	key = chars_from_hex(key);
	var bf = new Blowfish(key);
	var plainText = bf.decrypt(data);
	//remove null characters from end of output
   	plainText = plainText.replace(/\0*$/g, '');
	return plainText;
}


//**** FUNCTIONS NEEDED FOR BLOWFISH ***\\

function chars_from_hex(inputstr) {
	var char_str = "";
    inputstr = inputstr.replace(/^(0x)?/g, '');//remove '0x' which might be at start of a hex string
	inputstr = inputstr.replace(/[^A-Fa-f0-9]/g, '');
	inputstr = inputstr.split('');
	for(var i=0; i<inputstr.length; i+=2) {
		char_str += String.fromCharCode(parseInt(inputstr[i]+""+inputstr[i+1], 16));
    }
	return char_str;
}
function hex_from_chars(inputstr) {
	var hex_str = "";
	var i, n;
	var hex_digits = "0123456789abcdef";
	hex_digits = hex_digits.split('');
	inputstr = inputstr.split('');
	for(var i=0; i<inputstr.length; i++) {
		if(i % 32 == 0 && i > 0) hex_str += '\n';
		n = String.charCodeAt(inputstr[i]);
		hex_str += hex_digits[(n >> 4) & 0xf] + hex_digits[n & 0xf];
	}
	return hex_str;
}
