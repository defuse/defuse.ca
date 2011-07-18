/* Command Line Password Generator for Windows and UNIX-like systems.
 * Copyright (C) 2011  FireXware <firexware@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Compiling with Visual C++:
 *  cl.exe passgen.cpp advapi32.lib
 * Compiling with G++:
 *  g++ passgen.cpp -o passgen
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#ifdef _WIN32
#include <windows.h>
#include <wincrypt.h>
#endif

#define RANDOM_BYTES 80
#define PASSWORD_LENGTH 64

/*
 * Divides the string of bytes 'number' by 'divisor'
 * 'quotient' is set to the result of the division with the digits reversed (for simplicity).
 * Returns the remainder.
 * Both number and quotient can point to the same array.
 */
unsigned char divide(unsigned char number[RANDOM_BYTES], unsigned char quotient[RANDOM_BYTES], unsigned char divisor)
{
  // Work on a copy of number so changing quotient doesn't change the number if
  // they both point to the same array.
  unsigned char tmp[RANDOM_BYTES];
  memcpy(tmp, number, RANDOM_BYTES);

  unsigned char remainder = 0;
  unsigned int total = 0;
  for(int i = 0; i < RANDOM_BYTES; i++)
  {
    total = (remainder * 256 + tmp[i]);
    quotient[RANDOM_BYTES - i - 1] = total / divisor;
    remainder = total % divisor;
  }

  // Free secret data from memory
  memset(tmp, 0, RANDOM_BYTES);
  return remainder;
}

/*
 * Formats the string of random bytes 'randomBytes' into a password.
 * set - A list of characters that can be in the password.
 * setlength - the number of elements in set.
 * result - gets filled with the password.
 * Always creates a password of length PASSWORD_LENGTH.
 */
void formatBytes(unsigned char randomBytes[RANDOM_BYTES], 
                  char* set, unsigned char setlength,
                  unsigned char result[PASSWORD_LENGTH])
{
  for(int i = 0; i < PASSWORD_LENGTH; i++)
  {
    result[i] = set[divide(randomBytes, randomBytes, setlength)];
  }
}

/*
 * Fills 'buffer' with cryptographically secure random bytes.
 * buffer - gets filled with random bytes.
 * bufferlength - length of buffer
 */
bool getRandom(unsigned char* buffer, unsigned int bufferlength)
{
  puts("Reading random data...");
#ifdef _WIN32
  HCRYPTPROV hCryptCtx = NULL;
  CryptAcquireContext(&hCryptCtx, NULL, MS_DEF_PROV, PROV_RSA_FULL, CRYPT_VERIFYCONTEXT);
  if(hCryptCtx == NULL)
    return false;
  CryptGenRandom(hCryptCtx, bufferlength, buffer);
  CryptReleaseContext(hCryptCtx, 0);
#else
  FILE* random = fopen("/dev/random", "r");
  if(random == NULL)
    return false;
  unsigned int read = fread(buffer, sizeof(unsigned char), bufferlength, random);
  if(read != bufferlength)
    return false;
  fclose(random);
#endif
  return true;
}

void showHelp()
{
  puts("Usage: passgen <type>");
  puts("Where <type> is one of:");
  puts("--hex 256 bit hex string");
  puts("--ascii 64 character ascii printable string");
  puts("--alpha 64 character alpha-numeric string");
  puts("--help show this page");
}

int main(int argc, char* argv[])
{
  if(argc != 2)
  {
    showHelp();
    return 1;
  }

  char set[100]; 
  int setlength = 0;
  if(strncmp(argv[1],"--help", 6) == 0)
  {
    showHelp();
    return 0;
  }
  else if(strncmp(argv[1], "--ascii",5) == 0)
  {
    strcpy(set, "!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~");
    setlength = 94;
  }
  else if(strncmp(argv[1], "--hex", 3) == 0)
  {
    strcpy(set, "ABCDEF0123456789");
    setlength = 16;
  }
  else if(strncmp(argv[1], "--alpha", 5) == 0)
  {
    strcpy(set, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
    setlength = 62;
  }
  else
  {
    showHelp();
    return 1;
  }

  unsigned char rand[RANDOM_BYTES];
  if(getRandom(rand, RANDOM_BYTES))
  {
    unsigned char result[PASSWORD_LENGTH];
    formatBytes(rand, set, setlength, result);
    for(int i = 0; i < PASSWORD_LENGTH; i++)
    {
      printf("%c", result[i]);
    }
    printf("\n");
    memset(result, 0, PASSWORD_LENGTH);
  }
  else
  {
    puts("Error reading from /dev/random.");
    return 2;
  }
  memset(rand, 0, RANDOM_BYTES);
  return 0;
}

