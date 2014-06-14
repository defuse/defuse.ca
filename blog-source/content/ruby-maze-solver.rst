Ruby Maze Solver
######################################################
:slug: ruby-maze-solver
:author: Taylor Hornby
:date: 2012-05-27 00:00
:category: programming
:tags: search

Here's a recursive-backtracking ASCII maze solver I wrote to practice ruby. The
``o``'s are the solution.

Output
=======

.. code:: text

    $ ruby maze.rb maze.txt
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++oooooooo  ++++++++++++++++++++++ ++++++++++++++++++++++++        ++++ ++++++
    +oooo++ +++o++++++++++++++++++++++++ ++++ +++++++++++++++++++ +++ +++++++ ++++++
    ++++ ++   oo+++++++++++++++++++ ++++ ++++                 +++ +++ +++++++ ++++++
    ++++ ++ ++o++++++++ooooooo+++++ ++++ ++++ +++++++++++++++++++ +++         ++++++
    ++   +++++o++++++++o+++++o+++++           ++++ ++++++++++++++ ++++++++ +++++++++
    ++++++++++o++++++++o+++++o+++++ +++++ ++++++++ +++oooooooooooooo++++++ +++++++++
    ++++++++++o+oooooooo+++++ooooooo+++++ ++++++++ +++o++++++++ +++o++++++ +++++++++
    ++++++++++ooo++++++++++++ +++++o+++++ +++++++++   o++++++++++++o++++++ +++++++++
    +++++++++++++++++++++++++      o++++++++++++++++++o      ++++++o       +++++++++
    +++++++ +++++++++++++++++++++++o++++++oooooo++++++o+++++ ++++++o++++++++++++++++
    +++++++ +++++++++++++++++++++++o++++++o++++o++++++o+++++ ++++++o++++++++++++++++
    ++      ++++++++++oooooooooooooo++++++o++++o++++++o+++++ ++++++oooooooo+++++++++
    +++++++ ++++++++++o+++++++++++++++++++o++++oooooooo+++++++++++++ +++++o+++++++++
    +++++++           o+++++ooooooooooooooo+++++++ +++++++           ++Xooo+++++++++
    ++++++++++++++++++ooooooo+++++++++++++++++++++ ++++++++++++++++++++F++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

Code
=====

.. code:: ruby

    #!/usr/bin/env ruby

    # ------------------------------ Example Maze ------------------------------------
    # ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # ++++          ++++++++++++++++++++++ ++++++++++++++++++++++++        ++++ ++++++
    # +X   ++ +++ ++++++++++++++++++++++++ ++++ +++++++++++++++++++ +++ +++++++ ++++++
    # ++++ ++     +++++++++++++++++++ ++++ ++++                 +++ +++ +++++++ ++++++
    # ++++ ++ ++ ++++++++       +++++ ++++ ++++ +++++++++++++++++++ +++         ++++++
    # ++   +++++ ++++++++ +++++ +++++           ++++ ++++++++++++++ ++++++++ +++++++++
    # ++++++++++ ++++++++ +++++ +++++ +++++ ++++++++ +++              ++++++ +++++++++
    # ++++++++++ +        +++++       +++++ ++++++++ +++ ++++++++ +++ ++++++ +++++++++
    # ++++++++++   ++++++++++++ +++++ +++++ +++++++++    ++++++++++++ ++++++ +++++++++
    # +++++++++++++++++++++++++       ++++++++++++++++++       ++++++        +++++++++
    # +++++++ +++++++++++++++++++++++ ++++++      ++++++ +++++ ++++++ ++++++++++++++++
    # +++++++ +++++++++++++++++++++++ ++++++ ++++ ++++++ +++++ ++++++ ++++++++++++++++
    # ++      ++++++++++              ++++++ ++++ ++++++ +++++ ++++++        +++++++++
    # +++++++ ++++++++++ +++++++++++++++++++ ++++        +++++++++++++ +++++ +++++++++
    # +++++++            +++++               +++++++ +++++++           ++    +++++++++
    # ++++++++++++++++++       +++++++++++++++++++++ ++++++++++++++++++++F++++++++++++
    # ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    def main
      printUsage unless ARGV[0]

      begin
        maze = File.open(ARGV[0], 'r') { |f| f.read }
      rescue
        puts "Cannot read file."
        printUsage
      end

      solver = MazeSolver.new(maze)
      puts solver.solve ? solver : "Maze has no solution"
    end

    def printUsage
      puts "USAGE: ruby maze.rb <mazefile>"
      exit
    end

    # A recursive backtracking maze solver.
    # The maze is specified as an ASCII grid (each line is one row), where:
    # - 'X' marks the starting position of the player.
    # - 'F' marks the goal.
    # - ' ' (space) marks the area where the player is allowed to move.
    # - Everything else marks area where the player is NOT allowed to move.
    # The maze string must not contain the character 'o' (lowercase oh) since it is
    # used to mark the solution path.
    class MazeSolver

      PLAYER_CHAR = 'X'
      TRACK_MARKER = 'o'
      FINISH_CHAR = 'F'
      SPACE_CHAR = ' '

      DELTA_ROW = 0
      DELTA_COL = 1

      # Movement directions are specified internally by a 'delta', which is an
      # array where [0] is the change in row and [1] is the change in column.
      # The player is only allowed to move up, down, left, and right.
      DIRECTIONS = [ [0, -1], [0, 1], [1, 0], [-1, 0] ]

      def initialize(maze)
        # Split the maze string into an array of arrays of characters.
        @maze = maze.split("\n").map { |row| row.split('') }
        findPlayer
      end

      # Find the player on the maze grid and check that the maze is valid.
      def findPlayer
        playerFound = false
        finishFound = false
        @maze.each_with_index do |row, rowIndex|
          row.each_with_index do |col, colIndex|
            case col
              when PLAYER_CHAR then
                raise 'More than one player' if playerFound
                playerFound = true
                @playerRow = rowIndex
                @playerCol = colIndex
              when FINISH_CHAR then finishFound = true
              when TRACK_MARKER then raise 'Track marker playerFound in maze'
            end
          end
        end
        raise 'No player' unless playerFound
      end

      # Solve the maze. Returns true if it has been solved, false if it cannot be solved.
      def solve
        possibleMovements.each do |delta|
          moveBy delta
          break if solve
          undoMoveBy delta
        end
        adjacentToFinish?
      end

      # Return an array of valid movement deltas (to SPACE_CHARs) from the current position
      def possibleMovements
        deltas = []
        DIRECTIONS.each do |delta|
          deltas.push(delta) if canMoveBy? delta
        end
        deltas
      end

      def moveBy(delta)
        raise "Invalid movement" unless canMoveBy? delta
        @maze[@playerRow][@playerCol] = TRACK_MARKER
        @maze[@playerRow += delta[DELTA_ROW]][@playerCol += delta[DELTA_COL]] = PLAYER_CHAR
      end

      def undoMoveBy(delta)
        reverse = delta.map { |x| -x }
        @maze[@playerRow][@playerCol] = SPACE_CHAR
        @maze[@playerRow += reverse[DELTA_ROW]][@playerCol += reverse[DELTA_COL]] = PLAYER_CHAR
      end

      def adjacentToFinish?
        DIRECTIONS.each do |delta|
          return true if @maze[@playerRow + delta[DELTA_ROW]][@playerCol + delta[DELTA_COL]] == FINISH_CHAR
        end
        return false
      end

      def canMoveBy?(delta)
        delta[DELTA_ROW] != delta[DELTA_COL] and
        @maze[@playerRow + delta[DELTA_ROW]][@playerCol + delta[DELTA_COL]] == SPACE_CHAR
      end

      def to_s
        @maze.map { |row| row.join('') }.join("\n")
      end

    end

    main 
