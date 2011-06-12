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
//converts a number to a hex byte
function intToHex(num)
{
	num = num % 16;
	var set = new Array('A','B','C','D','E','F');
	if(num > 9)
	{
		return set[num - 10];
	}
	else
	{
		return num + '';
	}
}

//returns the integer value of a single hex character
function hexToInt(hexChar)
{
	hexChar = hexChar.toUpperCase();
	var set = new Array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
	for(var i = 0; i < set.length; i++)
	{
		if(hexChar == set[i])
		{
			return i;
		}
	}
	return "nothex";
}

//pass - password + salt or randomness
// n = number of bits desired (must be a multiple of 4)
// returns the key in hex format
function MakeNKey(pass,n)
{
	n = n/4; //n is now the number of hex digits
	var hashing = "";
	var rounds = 0;
	//keep appending hashes of the password plus the previous hash to create a larger hash
	while(hashing.length < n || rounds < 2)
	{
		var shaHash = SHA256(pass + hashing + pass.length);
		hashing = hashing + shaHash;
		rounds = rounds + 1;
	}
	
	//fill the array with 0xF
	var result = new Array(n);
	for(var i = 0; i < n; i++)
	{
		result[i] = 'F';
	}
	
	//fold the large hash into the right size using XOR
	for(var i = 0; i < hashing.length; i++)
	{
		var curVal = hexToInt(result[i%n]) ;
		var hashVal = hexToInt(hashing[i]);
		var curRes = intToHex(curVal ^ hashVal);
		result[i%n] = curRes;
	}

	//build the final hash from the array
	var finalHash = "";
	for(var i = 0; i < n; i++)
	{
		finalHash = finalHash +  result[i];
	}
	return finalHash;
}

//returns SHA256 hash in hex
function SHA256(pass)
{
	var shaObj = new jsSHA(pass);
    	var hashvalue = shaObj.getHash("SHA-256", "HEX");
	return hashvalue;
}
