Gaussian (Gauss-Jordan) Elimination in Ruby
############################################
:slug: gaussian-elimination-in-ruby
:author: Taylor Hornby
:date: 2012-11-11 00:00
:category: math
:tags: gaussian elimination, matrices

I had to do some Gaussian elimination for an assignment. So here's Gaussian
elimination in Ruby:

.. code:: ruby

    # Performs an in-place Gaussian elimination on an NxN matrix 'matrix' (2D array
    # of Numeric objects) and an N-element vector 'vector.' (array of N Numerics).
    def gaussianElimination(matrix, vector)
    0.upto(matrix.length - 2) do |pivotIdx|
        # Find the best pivot. This is the one who has the largest absolute value
        # relative to his row (scaled partial pivoting). This step can be omitted
        # to improve speed at the cost of increased error.
        maxRelVal = 0
        maxIdx = pivotIdx
        (pivotIdx).upto(matrix.length - 1) do |row|
        relVal = matrix[row][pivotIdx] / matrix[row].map{ |x| x.abs }.max
        if relVal >= maxRelVal
            maxRelVal = relVal
            maxIdx = row
        end
        end
    
        # Swap the best pivot row into place.
        matrix[pivotIdx], matrix[maxIdx] = matrix[maxIdx], matrix[pivotIdx]
        vector[pivotIdx], vector[maxIdx] = vector[maxIdx], vector[pivotIdx]
    
        pivot = matrix[pivotIdx][pivotIdx]
        # Loop over each row below the pivot row.
        (pivotIdx+1).upto(matrix.length - 1) do |row|
        # Find factor so that [this row] = [this row] - factor*[pivot row]
        # leaves 0 in the pivot column.
        factor = matrix[row][pivotIdx]/pivot
        # We know it will be zero.
        matrix[row][pivotIdx] = 0.0
        # Compute [this row] = [this row] - factor*[pivot row] for the other cols.
        (pivotIdx+1).upto(matrix[row].length - 1) do |col|
            matrix[row][col] -= factor*matrix[pivotIdx][col]
        end
        vector[row] -= factor*vector[pivotIdx]
        end
    end

    return [matrix,vector]
    end

    # Assumes 'matrix' is in row echelon form.
    def backSubstitution(matrix, vector)
    (matrix.length - 1).downto( 0 ) do |row|
        tail = vector[row]
        (row+1).upto(matrix.length - 1) do |col|
        tail -= matrix[row][col] * vector[col]
        matrix[row][col] = 0.0
        end
        vector[row] = tail / matrix[row][row]
        matrix[row][row] = 1.0
    end
    end
    
    # Example usage:
    require 'pp'
    
    # A system of equations: matrix * X = vector
    matrix = 
    [
        [1.0, 1.0, 1.0, 1.0],
        [0.0, 1.0, 2.0, 3.0],
        [1.0, 2.0, 4.0, 8.0],
        [0.0, 1.0, 4.0, 12.0],
    ]
    vector = [1.0, 0.0, 2.0, 0.0]

    # Create a backup for verification.
    matrix_backup = Marshal.load(Marshal.dump(matrix))
    vector_backup= vector.dup
    
    # Gaussian elemination to put the system in row echelon form.
    gaussianElimination(matrix, vector)
    # Back-substitution to solve the system.
    backSubstitution(matrix, vector)

    # Print the result.
    pp matrix
    pp vector

    # Verify the result.
    pass = true

    0.upto(matrix_backup.length - 1) do  |eqn|
    sum = 0 
    0.upto(matrix_backup[eqn].length - 1) do |term|
        sum += matrix_backup[eqn][term] * vector[term]
    end
    if (sum - vector_backup[eqn]).abs > 0.0000000001
        pass = false
        break
    end
    end

    if pass
    puts "Verification PASSED."
    else
    puts "Verification FAILED."
    end 
