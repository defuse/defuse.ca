// Extends the Stanford JavaScript Crypto Library's CSPRNG.
"use strict";

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

var secureRandom = function(min, max)
{
    if(min > max)
    {
        throw "MinBiggerThanMax";
        return null;
    }

    var randomness = sjcl.random.randomWords(16, 0);

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


var secureRandomString = function(length, acceptedChars)
{
    var uniqChars = uniq(acceptedChars);
    var s = Array();
    for(var i = 0; i < length; i++)
    {
        s.push(uniqChars.substr(secureRandom(0, uniqChars.length - 1), 1));
    }
    return s.join('');
}

function uniq(s)
{
    var unique = Array();
    for(var i = 0; i < s.length; i++)
    {
        var ch = s.substr(i, 1);
        if(!arrayContains(unique, ch))
        {
            unique.push(ch);
        }
    }
    return unique.join('');
}

function arrayContains(array, search)
{
    for(var i = 0; i < array.length; i++)
    {
        if(array[i] === search)
        {
            return true;
        }
    }
    return false;
}

function makeWordLeet(word)
{
    letters = word.split("");
    for(var i = 0; i < letters.length; i++)
    {
        if(secureRandom(0,1) == 1)
        {
            leetOptions = LEET_TABLE[letters[i]];
            if(leetOptions.length >= 1)
                letters[i] = leetOptions[secureRandom(0, leetOptions.length - 1)];
        }
    }
    return letters.join('');
}
