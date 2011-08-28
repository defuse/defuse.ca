//NOTE: This requires the Stanford JavaScript Crypto library.

var CSPRNG;
if (!CSPRNG) var CSPRNG = {};
CSPRNG.RNG_BYTES = 128;
CSPRNG.rngOffset = 0;
CSPRNG.rngState = [];
for(var i = 0; i < CSPRNG.RNG_BYTES; i++)
    CSPRNG.rngState[i] = 0;

CSPRNG.addEntropy = function(n)
{
    //JavaScript stores integers up to 53 bits.
    for(var i = 0; i < 7; i++)
    {
        CSPRNG.rngState[CSPRNG.rngOffset] = (CSPRNG.rngState[CSPRNG.rngOffset] + (n >> (i * 8))) % 256;
        CSPRNG.rngOffset++;
        if(CSPRNG.rngOffset >= CSPRNG.RNG_BYTES)
        {
            CSPRNG.mixState();
        }
    }
}

//Get a secure random number. min, max both inclusive and MUST be positive.
CSPRNG.secureRandom = function(min, max)
{
    //FIXME: validate that they are not neg and max>min
    CSPRNG.mixState();
    var diff = max - min;

    // Compute rngState (as a base-256 number) mod diff
    var total = 0;
    var remainder = 0;
    for(var i = 0; i < CSPRNG.RNG_BYTES; i++)
    {
        total = (remainder * 256) + CSPRNG.rngState[i];
        remainder = total % (diff+1);
    }

    CSPRNG.mixState();
    return min + remainder;
}

// Mixes the RNG state with SHA256. Based on TrueCrypt's CSPRNG:
// http://www.truecrypt.org/docs/?s=random-number-generator
CSPRNG.mixState = function()
{
    for(var i = 0; i < CSPRNG.RNG_BYTES; i+=32)
    {
        x = sjcl.hash.sha256.hash(CSPRNG.rngState);
        for(var j = 0; j < 32; j++)
        {
            //Get the J-th byte of the checksum
            var b = (Math.abs(x[Math.floor(j/4)]) >> (j % 4)) % 256;
            // XOR it into the state
            CSPRNG.rngState[i+j] ^= b;
        }

    }
    CSPRNG.rngOffset = 0; 
}
