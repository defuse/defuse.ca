/*
 * A super-fast, recursive, random Latin Square generator.
 * By: FireXware <firexware@gmail.com>
 * Jun 16, 2011.
 */

#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int makeLatin(int** square, int n, int position);

int main(int argc, char** argv)
{
    if(argc != 2)
    {
        printf("usage: latinsquare <n>\n");
        return 1;
    }

    int n = atoi(argv[1]);
    if(n < 1)
    {
        printf("n must be greater than zero.\n");
    }

    // Our square will be a 2-d array of integers
    int** square = (int**)malloc(n * sizeof(int*));

    int i = 0;
    int j = 0;

    for(i = 0; i < n; i++)
    {
        square[i] = malloc(n * sizeof(int));
    }

    // The recursive method finds a the "closest" latin square to the values
    // already in the square. So if we start with a random square, we get
    // a random latin square. If latin squares are evenly distributed 
    // throughout the "sqare-space" then this is equivalent to selecting
    // a random LS.
    //
    // Obviously, we'll need to use a better RNG if we want to do any kind of
    // crypto related stuff.

    srand(time(NULL));

    for(i = 0; i < n; i++)
    {
        for(j = 0; j < n; j++)
        {
            square[i][j] = rand() % n;
        }
    }

    makeLatin(square, n, 0);

    // Print out the square
    for(i = 0; i < n; i++)
    {
        for(j = 0; j < n; j++)
        {
            printf("%5d ", square[i][j]);
        }
        printf("\n");
    }

    free(square);
}

/*
 * Finds the "next" or "closest" LS to the values in square.
 * square - The square.
 * n - the size of the square.
 * position - The current cell number.
 * Recursive depth: n^2
 */
int makeLatin(int** square, int n, int position)
{
    // If we make it all the way down to the last cell,
    // we have found a LS!
    if(position == n * n)
    {
        return 1;
    }

    //Convert cell number to row and column indexes
    int row = position / n;
    int col = position % n;

    int i = 0; //general purpose counter

    // We'll be changing the value of this cell, so we have to remember 
    // it's initial value so we can restore the randomness later if needed.
    int oldValue = square[row][col]; 

    //Loop over every possible value the current cell can hold.
    //We start at the value that is already in square and wrap modulo n
    int iter = 0;
    for(iter = 0; iter < n; iter++)
    {
        //Check if a latin square could exist with the current cell's value
        int dupeFound = 0;
        
        //is there a duplicate in this row?
        for(i = 0; i < col; i++)
        {
            if(square[row][i] == square[row][col])
            {
                dupeFound = 1;
                break;
            }
        }

        //is there a duplicate in this column?
        for(i = 0; i < row; i++)
        {
            if(square[i][col] == square[row][col])
            {
                dupeFound = 1;
                break;
            }
        }

        if(dupeFound == 0) //no duplicate, move on to the next
        {
           if(makeLatin(square, n, position+1)) // Did the value of our cell allow a LS to be produced? 
           {
                return 1; 
           }

           // If the value of our cell didn't allow an LS to be produced, we'll have to increment and try again
        }

        square[row][col] = (square[row][col] + 1) % n;
    }

    square[row][col] = oldValue; // We have to backtrack, restore the initial state
    return 0;

    /*
     * CLAIM: This will always produce a valid Latin Square
     * Proof: The algorithm will only move on to the next cell if the current cell is valid based on the previous cells.
     * So: The algorithm will reach level i + 1 only if all cells up to and including i are valid.
     * So: When i reaches n^2, all cells up to and including n^2-1 (the last cell) are in a valid Latin Square state.
     *
     * This algorithm will halt only when:
     * 1. An LS is found, or
     * 2. It has iterated through every possible square
     *
     * We know that a Latin Square exists for every size of square, so the algorithm will always produce an LS.
     */
}
