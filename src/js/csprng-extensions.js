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

for(var i = 0; i < 100; i++)
{
    sjcl.random.addEntropy(Math.random() * 4294967296, 0);
}

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


secureRandomString = function(length, acceptedChars)
{
    var s = Array();
    for(var i = 0; i < length; i++)
    {
        s.push(acceptedChars.substr(secureRandom(0, acceptedChars.length - 1), 1));
    }
    return s.join('');
}
