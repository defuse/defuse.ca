// Extends the Stanford JavaScript Crypto Library's CSPRNG.

try
{
    sjcl.random.startCollectors();
}
catch(err)
{
    alert('WARNING: FAILED TO START CSPRNG!');
}

//TODO: add from JS's CSPRNG
//TODO: Add times.
//TODO: browser-specific secure randoms.


secureRandom = function(min, max)
{
    if(min > max)
    {
        throw "MinBiggerThanMax";
        return null;
    }

    randomness = sjcl.random.randomWords(16, 0);

    // Compute (the random words as a base-65536 number) mod (max-min+1)
    var diff = max - min;
    var total = 0;
    var remainder = 0;
    for(var i = 0; i < randomness.length; i++)
    {
        total = (remainder * 65536) + (Math.abs(randomness[i]) % 65536);
        remainder = total % (diff+1);
    }

    return min + remainder;
}
