/*  passgen.h

    The definitive password generator for brute force attacks. This code is in the public domain.
    Created by FireXware. Mar. 21, 2010, contact: firexware@hotmail.com

    Although just as efficient at brute forcing as the linear (aaaa, aaab, aaac..) approach, this class takes a different approach which is statistically much faster.
    This class takes a character set, and a password length and will generate passwords in a psudorandom order, without repititions.

    It's based on the math:

    i = i + P mod n

    Where P is a prime number greater then n. n is any positive integer.
    If you loop over that equation, it will produce every value from 1 to n in a non-linear order (not 1,2,3,4,5..)
    I've never seen this theory proven anywhere, but it makes sense because P will not be divisible by n

    The randomness is very low quality, and should not be used for cryptographic applications.
    To implement this with high quality randomization, use a key to make a lookup table:
        password[i] = next[password[i]] //next[] contains the character set in a random order (from a key or /dev/random)
        To find the next password, an attacker would only have to brute force the next[] table, (set length - 1)! possible combinations

    Credits:
    - The idea isn't new, so credits to whoever thought of it first :P

    Usage:

        unsigned char set[] = "abcdefghijklmnopqrstuvwxyz"; //define the character set
        int setlen = 26; //ignore the \0
        int passlen = 4; //length of password to try
        PassGen p(set,setlen,passlen); //create a password generator

        unsigned char* password = new unsigned char[passlen]; //create the container to hold the passwords made by p
        while(p.GetNext(password)) //fill password with the next password
        {
            for(int j = 0; j < passlen; j++) //output the password (it is not null terminated)
               cout << password[j];
            cout << endl;
        }

    To use it with parallel processing, the job can be split into multiple peices.
    Use GetThreadStates to get a an array of state strings, each representing its own section of the job
    For example, this is the same as the code above, but split into two parts that can be run at the same time:
        unsigned char set[] = "abcdefghijklmnopqrstuvwxyz";
        int setlen = 26; //ignore the \0
        int passlen = 4;
        char** states = PassGen::GetThreadStates(set,setlen,passlen,2);
        PassGen p(states[0]);
        PassGen g(states[1]);
        unsigned char* password = new unsigned char[passlen];

        while(p.GetNext(password))
        {
            for(int j = 0; j < passlen; j++)
                cout << password[j];
            cout << endl;
        }


        while(g.GetNext(password))
        {
            for(int j = 0; j < passlen; j++)
               cout << password[j];
            cout << endl;
        }

*/

#include <math.h>
#include <stdlib.h>
#include <time.h>


//For debugging:
/*#include <iostream>
using namespace std;*/


class PassGen
{
    protected: //disable copying cuz we use stuff on the heap.
        PassGen(PassGen& c);
        PassGen operator=(PassGen& c);
    public:

        /*
        Starts a new password generator from a character set, and a password length.
            charSet is the character set to use, for example, to brute force alphanumerc passwords, pass in "abcdefghijklmnopqrstuvwxyz1234567890"
            setLen is the length of your character set (ignore the \0 at the end if there is one)
            passLen is the length of password you are brute forcing, for example 4 would obviously create passwords like 'abaf'
        */
        PassGen(unsigned char* charSet, int setLen, int passLen, bool random);

        /*
        Starts a password generator from a previously saved state, also used for dividing the job into peices.
            state is a null terminated state string returned by GetState or GetThreadStates
        */
        PassGen(char* state);

        /*
        Gets the next password to try
            container is a unsigned char array, the same length as the passwords you want (specified when constructing the object)
            container will be filled with the next password.
            returns true if there are more passwords to be generated, false if it is the last password
        */
        bool GetNext(unsigned char* container);

        /*
            Returns the current state of the password generator, so it can be saved and restored
        */
        char* GetState();

        /*
        Generates an array of state strings, so you can use them with the 2nd constructor for parallel processing.
            charSet is the character set
            setLen is the length of the character set
            passLen is the length of the passwords to generate
            numThreads is the number of threads (or jobs) to generate
        */
        static char** GetThreadStates(unsigned char* charSet, int setLen, int passLen, int numThreads);

        /*
            Returns an estimate of how far complete the process is in percent format
        */
        double GetProgressPercent();

        /*
            Returns the password length
        */
        int PasswordLength()
        {
            return passwordLength_;
        }

        /*
            Makes GetNext start to return true again, until it finishes once more
        */
        void Reset()
        {
            repeating = false;
        }

        //deconstructor
        ~PassGen()
        {
            //deleting set_ is the user's responsibility, they only gave us a pointer to it.
            delete prime_; delete pass_;
        }

    protected:
        bool repeating;
        unsigned char* prime_; //large prime number that gets added modulo setLength_^passwordLength_
        unsigned char* pass_; //array indexes
        unsigned char* set_; //the character set as defined by the user
        int setLength_; //length of the character set
        int passwordLength_; //length of the passwords to generate

        unsigned char* counter_; //counter for checking % complete and if done or not
        int numThreads_; //number of threads, gets added to the counter

        //gets the state string for a set of data
        static char* getStateFromData(int passwordLength, unsigned char* primeNumber, unsigned char* password, int setLength, unsigned char* charset, int numThreads);

        //finds the next prime after or equal to min
        static long nextPrime(long min);

        //returns true if a number is prime.
        static bool isprime(long p);

};

PassGen::PassGen(unsigned char* charSet, int setLen, int passLen, bool random) : set_(charSet), setLength_(setLen), passwordLength_(passLen)
{
    repeating = false;
    //to implement a linear search feature, just make prime
    srand(time(NULL));
    pass_ = new unsigned char[passLen];
    prime_ = new unsigned char[passwordLength_];
    counter_ = new unsigned char[passwordLength_];
    numThreads_ = 1;
    long p = passLen;

    if(random)
    {
    for(int i = 0; i < passwordLength_; i++)
    {
        p = nextPrime(p + 1 + (rand() % 314159265)); // a random prime number greater than the last (so we dont get the same number)
        prime_[i] = p % setLen;

        pass_[i] = rand() % setLen; //start the password off in a random position
    }
    }
    else
    {
        for(int i = 0; i < passwordLength_; i++)
        {
                prime_[i] = 0;
                pass_[i] = 0;
        }
        prime_[passwordLength_ - 1] = 1;
    }

    //init pass to some random value
    for(int i = 0; i < passwordLength_; i++)
        pass_[i] = rand() % setLength_;

}

PassGen::PassGen(char* state)
{
    repeating = false;
    //passwordlength|prime_|password|charsetlength|charset|numThreads|NULL
    passwordLength_ = (int)(*(state + 3) << 24 | *(state + 2) << 16 | *(state + 1) << 8 | *state);
    prime_ = new unsigned char[passwordLength_];
    pass_ = new unsigned char[passwordLength_];
    counter_ = new unsigned char[passwordLength_];
    for(int i = 0;i < passwordLength_; i++)
    {
        prime_[i] = state[4 + i];
        pass_[i] = state[4 + passwordLength_ + i];
    }
    int curpos = 4 + passwordLength_ + passwordLength_;
    setLength_ = (int)(*(state + 3 + curpos) << 24 | *(state + 2 + curpos) << 16 | *(state + 1 + curpos) << 8 | *(state + curpos));
    set_ = new unsigned char[setLength_];
    for(int i = 0; i < setLength_; i++)
    {
        set_[i] = state[curpos + 4 + i];
    }
    curpos = curpos + setLength_ + 4;
    numThreads_ = (int)(*(state + 3 + curpos) << 24 | *(state + 2 + curpos) << 16 | *(state + 1 + curpos) << 8 | *(state + curpos));

}


bool PassGen::GetNext(unsigned char* container)
{
    //i = i + P mod n will produce a random sequence from 1 to n
    //basically add the prime numbers modulo setLength_, as if the prime_ array was a big number of base setLength_
    int carry = 0;
    int sum = 0;

    int ctrcarry = numThreads_, ctrsum = 0; //adding numThreads_ every time because if the process is split, you want it to account for the other ones too
    bool complete = true; //if all digits in the counter are 0, it has reached it's max

    for(int i = passwordLength_ - 1; i >= 0; i--)//loop from the last index of the password and counter to 0
    {
        //add the prime numbers to the password
        sum = carry + pass_[i] + prime_[i];
        pass_[i] = sum % setLength_;
        carry = sum / setLength_;

        container[i] = set_[pass_[i]]; //pass_[i] won't be changed after this, so it's safe to put this here

        // add one to the counter
        ctrsum = ctrcarry + counter_[i];
        counter_[i] = ctrsum % setLength_;
        ctrcarry = ctrsum / setLength_;
        complete &= (i == passwordLength_ - 1 ? counter_[i] < numThreads_: counter_[i] == 0); //because adding a pair number mod an odd number will return to 1
    }

    if(!repeating)
    {
        repeating = complete;
        return true;
    }
    else
    {
        return false;
    }
}

char* PassGen::GetState()
{
    return  getStateFromData(passwordLength_, prime_, pass_, setLength_, set_, numThreads_); //we dont need to worry about numthreads, because prime already = numThreads * prime
}

double PassGen::GetProgressPercent()
{
    //estimate the large math: 2342348 / 3141592 = approx. 234/314
    //take the most significant 8 bits of the counter, divide them by the 0 most signifigant bits of the maximum {passlen, passlen, passlen...
    //divide that by numThreads_
    //multiply by 100 to get percent
    int n = log(0xFFFFFFFF)/log(setLength_); //calculate how many digits of the counter will fit inside an integer

    double done = 0, max = 0;
    int offset = 0;

    //find the first "digit" of the number
    while(counter_[offset] <= 0)
    {
        offset++;
    }

    //find the denominator of our fraction (numerator is a n or passwordLength_ digit numer)
    max = pow(setLength_, (passwordLength_ < n ? passwordLength_ : n ));

    //calculate the integer value for the numerator
    for(int i = 0; i < n && i + offset < passwordLength_ ; i++)
    {
        done += counter_[i + offset] * pow(setLength_, passwordLength_ - offset - 1 - i);
    }

    return done / max * 100.0; //should return an approximative percent.
}
char** PassGen::GetThreadStates(unsigned char* charSet, int setLen, int passLen, int numThreads)
{
    char** states = new char*[numThreads];
    unsigned char newprime[passLen]; //the prime number
    unsigned char randomstart[passLen]; //the starting point of the threads

    long p = passLen;
    for(int i = 0; i < passLen; i++)
    {
        p = nextPrime(p + 1 + (rand() % 314159265)); // a random prime number greater than the last (so we dont get the same number)
        newprime[i] = p % setLen;
        randomstart[i] = (rand() % setLen);
    }

    unsigned char mulPrime[passLen];
    for(int i = 0; i < passLen; i++)
        mulPrime[i] = newprime[i];

    //mulPrime = newprime * numThreads, so that the thread doesnt need to multply the prime numbers by the number of threads, we can do it in advance to speed things up
    for(int i = 0; i < numThreads - 1; i++)//loop numThreads - 1 times, -1 because the prime number has already been added once
    {
        int sum = 0, carry = 0;
        for(int d = passLen - 1; d >= 0; d--)
        {
            sum = carry + mulPrime[d] + newprime[d];
            mulPrime[d] = sum % setLen;
            carry = sum / setLen;
        }
    }

    for(int i = 0; i < numThreads; i++) //create each state string and add it to the array
    {
        states[i] = getStateFromData(passLen, mulPrime, randomstart, setLen, charSet, numThreads);

        //increment the starting point, so the threds can do the mod numThreads passwords
        int sum = 0, carry = 0;
        for(int d = passLen - 1; d >= 0; d--)
        {
            sum = carry + randomstart[d] + newprime[d];
            randomstart[d] = sum % setLen;
            carry = sum / setLen;
        }
    }
    return states;
}







//--------------PRIVATE METHODS------------------------

char* PassGen::getStateFromData(int passwordLength, unsigned char* primeNumber, unsigned char* password, int setLength, unsigned char* charset, int numThreads)
{
    //passwordlength|prime_|password|charsetlength|charset|numthreads|NULL
    int size = 4 + passwordLength + passwordLength + 4 + setLength + 4 + 1;
    char* state = new char[size];
    state[0] = passwordLength % 256;
    state[1] = (passwordLength >> 8) % 256;
    state[2] = (passwordLength >> 16) % 256;
    state[3] = (passwordLength >> 24) % 256;

    for(int i = 0; i < passwordLength; i++)
    {
        state[4 + i] = primeNumber[i];
        state[4 + i + passwordLength] = password[i];
    }

    int curpos = 4 + passwordLength + passwordLength;
    state[curpos + 0] = setLength % 256;
    state[curpos + 1] = (setLength >> 8) % 256;
    state[curpos + 2] = (setLength >> 16) % 256;
    state[curpos + 3] = (setLength >> 24) % 256;
    curpos = curpos + 4;

    for(int i = 0; i < setLength; i++)
    {
        state[curpos + i] = charset[i];
    }
    curpos = curpos + setLength;

    state[curpos + 0] = numThreads % 256;
    state[curpos + 1] = (numThreads >> 8) % 256;
    state[curpos + 2] = (numThreads >> 16) % 256;
    state[curpos + 3] = (numThreads >> 24) % 256;
    state[curpos + 4] = '\0';
    return state;
}

long PassGen::nextPrime(long min)
{
    long i = min;
    while(!isprime(i))
    {
        i++;
    }
    return i;
}

bool PassGen::isprime(long p)
{
    if( p % 2 == 0) //save half the work
        return false;
    for(long i = 3; i <= sqrt(p); i+=2)
    {
        if( p % i == 0) //number is divisible by i
        {
            return false;
        }
    }
    return true;
}

