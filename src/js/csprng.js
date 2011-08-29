/*
 * Cryptographically Secure Random Number Generator using SJCL
 * License: Public Domain
 * 
 * Based on TrueCrypt's CSPRNG Design:
 *          http://www.truecrypt.org/docs/?s=random-number-generator
 *
 * NOTE: This code needs the Stanford JavaScript Crypto Library to be loaded.
 *          http://crypto.stanford.edu/sjcl/
*/

// Setup our 'CSPRNG' pseudo-namespace.
var CSPRNG;
if (!CSPRNG) var CSPRNG = {};

// The number of BYTES of state. Must be a multiple of 32 (256 bits).
CSPRNG.RNG_BYTES = 32 * 4; //32*4=128 bytes = 1024 bits.
CSPRNG.rngOffset = 0;

// The state. Each element is one BYTE.
CSPRNG.rngState = [];
for(var i = 0; i < CSPRNG.RNG_BYTES; i++)
    CSPRNG.rngState[i] = 0;

/*
 * Add entropy to the CSPRNG state.
 * n - An integer containing any amount of random entropy.
 */
CSPRNG.addEntropy = function(n)
{
    // JavaScript numbers have 53 bits of precision. Take 7 bytes (56 bits)
    for(var i = 0; i < 7; i++)
    {
        // Add the i-th (least significant) byte into the state modulo 256
        CSPRNG.rngState[CSPRNG.rngOffset] = (CSPRNG.rngState[CSPRNG.rngOffset] + (n >> (i * 8))) % 256;
        CSPRNG.rngOffset++;
        if(CSPRNG.rngOffset >= CSPRNG.RNG_BYTES)
        {
            CSPRNG.mixState();
        }
    }
}

/*
 * Generate a random number.
 * Returns a number between min and max (both inclusive).
 */
CSPRNG.secureRandom = function(min, max)
{
    if(min > max)
    {
        throw "MinBiggerThanMax";
        return null;
    }

    // Make sure we have mixed the state at least once.
    CSPRNG.mixState();

    // Compute (the state as a base-256 number) mod (max-min+1)
    var diff = max - min;
    var total = 0;
    var remainder = 0;
    for(var i = 0; i < CSPRNG.RNG_BYTES; i++)
    {
        total = (remainder * 256) + CSPRNG.rngState[i];
        remainder = total % (diff+1);
    }

    // Get rid of the data used to select the random number.
    CSPRNG.mixState();

    return min + remainder;
}

/* 
 * Mix the RNG state with SHA256. 
 * Based on TrueCrypt's CSPRNG Design: http://www.truecrypt.org/docs/?s=random-number-generator
 */
CSPRNG.mixState = function()
{
    // Over each 256-bit block of the state...
    for(var i = 0; i < CSPRNG.RNG_BYTES; i+=32)
    {
        // Get the SHA256 hash of the state as a HEX string.
        x = sjcl.codec.hex.fromBits(sjcl.hash.sha256.hash(CSPRNG.rngState));

        // XOR the SHA256 into the current block of the state
        for(var j = 0; j < 32; j++)
        {
            // J-th byte of the hash
            CSPRNG.rngState[i+j] ^= parseInt(x.substr(2 * j, 2), 16);
        }

        //NOTE: This obviously isn't the most efficient way to get the bytes of the hash.
        //      I did it this way because SJCL doesn't have ANY method for converting 
        //      their 'bitArray' into a simple array of bytes. I COULD have treated the
        //      bitArray as an array of 8 32-bit integers, but I fear that the extra 
        //      information they store in the bitArray format would compromise randomness.
        //
        //      MORE: http://bitwiseshiftleft.github.com/sjcl/doc/symbols/sjcl.bitArray.html
    }
    CSPRNG.rngOffset = 0; 
}

/*
function rndseed(e)
{
    if(window.event)
        e = window.event;
    if(typeof(e.clientX) != "undefined")
        CSPRNG.addEntropy(e.clientX);
    if(typeof(e.clientY) != "undefined")
        CSPRNG.addEntropy(e.clientY);
    CSPRNG.addEntropy((new Date()).getTime());
}
*/
