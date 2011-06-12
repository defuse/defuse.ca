//Random password generator for *nix using /dev/random
//To make this code work with windows, modify the getRandom method to use whatever cryptographically secure RNG is included in windows.
//Created by FireXware
//contact: firexware@hotmail.com
#include <iostream>

#include <string.h>
#include <fstream>
using namespace std;

#define random_data 128 //use 1024 random bits to create them (higher = reduces the bias when the set length isnt a multiple of the amount of random data)
#define password_length 64 //return 64 character long passwords

/*
To visualize the bias problem, imagine this line is all of the possible combinations of the 1024 bits (0 to 2^1024)
|------------------------------------|
| NORMAL                         | O |
|------------------------------------|

The 'NORMAL' section is where the bar can be divided evenly by the total possible values of the password (0 to S^L where S is the character set length and L is the password length)
The section marked 'O' is the part of a (0 to S^L) section that isnt complete, because a whole repetition will not fit.
The longer the bar is, the shorter 'O' gets compared to the whole thing, making the chance of producing a biased password is extremely low.
*/


unsigned char divide(unsigned char* number, unsigned char* quotient, int len, unsigned char divisor);
char* formatBytes(unsigned char* random512, char* set, unsigned char setlength);
bool getRandom(unsigned char* buffer, unsigned int bufferlength);
void showHelp();


int main(int argc, char* argv[])

{

    if(argc > 1)
    {
        char* set; //TODO: don't use the deprecated way of assigning the string to this
        int setlength = 0;
        if(strncmp(argv[1],"--help", 6) == 0)
        {
            showHelp();
        }
        else if(strncmp(argv[1], "--ascii",5) == 0)
        {
            set = "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
            setlength = 94;
        }
        else if(strncmp(argv[1], "--hex", 3) == 0)
        {
            set = "ABCDEF0123456789";
            setlength = 16;
        }
        else if(strncmp(argv[1], "--alpha", 5) == 0)
        {
            set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            setlength = 62;
        }
        else
        {
            showHelp();
        }

        if(set != 0 && setlength > 0)
        {
            unsigned char* rand = new unsigned char[random_data];
            if(getRandom(rand,random_data))
            {
                char* result = formatBytes(rand, set, setlength);
                for(int i = 0; i < password_length; i++)
                {
                    cout << result[i];
                }
                cout << endl;
                delete [] result;
            }
            else
            {
                cout << "Error reading from /dev/random." << endl;
                return 1;
            }
            delete [] rand;
        }
    }
    else showHelp();

    return 0;

}

//divide - divides a byte array by a byte using long division
//  number - the byte array containing the number to divide
//  quotient - the result of the division, can be the same as number to store the result in the same array. must be the same length as number
//  len - the length of 'number'
//  divisor - the number to divide 'number' by
//  returns - the remainder of the division
unsigned char divide(unsigned char* number, unsigned char* quotient, int len, unsigned char divisor)
{
    unsigned char remainder = 0;
    int total = 0;
    for(int i = 0; i < len; i++)
    {
        total = (remainder * 256 + number[i]);
        quotient[i] = total / divisor;
        remainder = total % divisor;
    }
    return remainder;
}

//formats 512 bits into a character set
//  random512 - an array of 64 bytes
//  set - a character array containing the character set
//  setlength - the length of the character set <256
//  returns - the least significant portion of random512 formatted into the character set
char* formatBytes(unsigned char* random512, char* set, unsigned char setlength)
{
    char* mods = new char[password_length];
    for(int i = 0; i < password_length; i++)
    {
        mods[i] = set[divide(random512, random512, random_data, setlength)];
    }
    //now random512 conains the data that we don't need. If we wanted to convert the entire number to the new base then we would continue the loop.
    return mods;
}

//gets a random byte array from /dev/random
//  buffer - a buffer to store the random data
//  bufferlength - the length of the buffer, also the amount of random data required.
bool getRandom(unsigned char* buffer, unsigned int bufferlength)
{
    ifstream devRand ("/dev/random", ios::in | ios::binary);
    return devRand.read((char*)buffer,bufferlength);
}

void showHelp()
{
    cout << "passgen pulls 1024 bits of random data from /dev/random and converts into one of the following usable formats:" << endl
    << "--hex 256 bit hex string" << endl
    << "--ascii 64 character ascii printable string" << endl
    << "--alpha 64 character alpha-numeric string" << endl
    << "--help show this page" << endl;
}
