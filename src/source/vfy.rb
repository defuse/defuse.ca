#!/usr/bin/env ruby

# Author: havoc 
# WWW: https://defuse.ca/backup-verify-script.htm
# Date: Jul 28, 2012
# License: Public domain / Do whatever you want.
#
# Backup validator script. Compares two folders "original" and "backup".
# Alerts the user of any files or directories that are in "original" but not in
# "backup" (extra files in "backup" are ignored). If a file exists in both
# "original" and "backup," they are compared by checking their lengths and by a
# random sample of their contents, and the user is alerted if they differ.
# 
# Output prefixes:
#   DIR: - Directory in original missing from backup.
#   FILE: - File in original missing from, or different, in backup.
#   SKIP: - Skipping directory specified by --ignore.
#   SYMLINK: - Symlink to directory skipped and not not following (no --follow).
#   ERROR: - Error reading file or directory.
#   DEBUG: - Debug information only shown when called with --verbose.

require 'optparse'

# The number of bytes to compare during each random sample comparison.
SampleSize = 32

###############################################################################
#                         Command Line Option Parsing                         #
###############################################################################
$options = {}

optparse = OptionParser.new do |opts|
  opts.banner = "Usage: #{__FILE__} [options] <original> <backup>\n"

  $options[:verbose] = false
  opts.on( '-v', '--verbose', 'Print what is being done' ) do
    $options[:verbose] = true
  end

  $options[:machine] = false
  opts.on( '-m', '--machine', "Output summary in machine-readable format" ) do 
    $options[:machine] = true
  end

  # By default, don't follow symlinks, so we don't end up in infinite loops.
  # The user can override this behaviour if they know there are no loops.
  $options[:follow] = false
  opts.on( '-f', '--[no-]follow', 'Follow symlinks' ) do |val|
    $options[:follow] = val
  end

  # If a folder in original doesn't exist in backup, the number of items in 
  # the folder will be counted and added to the diff total if invoked with -c
  $options[:count] = false
  opts.on( '-c', '--count', 'Count files in unmatched directories' ) do
    $options[:count] = true
  end

  # Ignored directories can be specified either as a subfolder of original or 
  # backup. The option can be specified multiple times.
  $options[:ignore] = []
  opts.on( '-i', '--ignore DIR', "Don't process DIR" ) do |ignore|
    $options[:ignore] << File.expand_path( ignore )
  end

  $options[:samples] = 0
  opts.on(
    '-s',
    '--samples COUNT',
    "Comparison sample count (default: #{$options[:samples]})"
  ) do |count|
    $options[:samples] = count.to_i
  end

  opts.on( '-h', '--help', 'Display this screen' ) do
    STDOUT.puts opts
    exit
  end
end

begin
  optparse.parse!
rescue OptionParser::InvalidOption
  STDERR.puts "Invalid option"
  STDERR.puts optparse
  exit
end

if ARGV.length < 2
  STDERR.puts "You must specify original and backup folders."
  STDERR.puts optparse
  exit
end

$original = File.expand_path( ARGV[0] )
$backup = File.expand_path( ARGV[1] )

[$original, $backup].each do |dir|
  unless File.directory? dir
    STDERR.puts "[#{dir}] is not a directory."
    STDERR.puts optparse
    exit
  end
end

STDERR.puts "WARNING: Comparing a directory to itself." if $original == $backup

###############################################################################
#                             Directory Comparison                            #
###############################################################################

# Global variables to hold statistics for the summary report at the end.
$diffCount = 0
$itemCount = 0
$skippedCount = 0
$errorCount = 0

# Returns true if fileA and fileB both exist, both are the same size, and pass
# the random sample comparison test.
def sameFile( fileA, fileB )
  # Both exist.
  return false unless File.exists?( fileA ) and File.exists?( fileB )
  # Both are the same size.
  aBytes = File.stat( fileA ).size
  bBytes = File.stat( fileB ).size
  return false unless aBytes == bBytes

  # Random sample comparison.
  same = true
  $options[:samples].times do 
    start = rand( aBytes ) 
    length = [aBytes, start + SampleSize].min - start + 1
    aSample = File.read( fileA, length, start )
    bSample = File.read( fileB, length, start )
    same = same && aSample == bSample
  end
  return same
rescue
  STDOUT.puts "ERROR: Can't read file [#{fileA}]"
  $errorCount += 1
  return true # So we don't get two messages for the same file
end

# Returns the number of items in the directory (and subdirectories of) 'dir'
def countItems( dir )
  if $options[:verbose]
    STDOUT.puts "DEBUG: Counting files in [#{dir}]"
  end

  count = 0
  Dir.foreach( dir ) do |item|
    next if item == "." or item == ".."
    count += 1
    fullPath = File.join( dir, item )
    count += countItems( fullPath ) if File.directory? fullPath
  end
  return count
end

# Recursively compare directories specified by a path relative to $original and
# $backup.
def compareDirs( relative = "" )
  # Combine the base path with the relative path
  original = File.expand_path( File.join( $original, relative ) )
  backup = File.expand_path( File.join( $backup, relative ) )

  if $options[:verbose]
    STDOUT.puts "DEBUG: Comparing [#{original}] to [#{backup}]" 
  end

  # Return if this directory has been excluded
  if $options[:ignore].include?( original ) or $options[:ignore].include?( backup )
    $skippedCount += 1
    STDOUT.puts "SKIP: Skipping comparison of [#{original}] and [#{backup}]"
    return
  end

  # Make sure both directories exist
  unless File.directory?( original ) and File.directory?( backup )
    STDOUT.puts "DIR: [#{original}] not found in [#{backup}]"
    $diffCount += 1 
    $diffCount += countItems( original ) if $options[:count]
    return
  end

  # If both directories exist, we check their contents
  begin
    Dir.foreach( original ) do |item|
      next if item == "." or item == ".."
      $itemCount += 1

      origPath = File.join( original, item )
      backupPath = File.join( backup, item )

      if File.directory? origPath
        if File.symlink?( origPath ) and not $options[:follow]
          $skippedCount += 1
          STDOUT.puts "SYMLINK: [#{origPath}] skipped."
          next
        end
        compareDirs( File.join( relative, item ) )
      else # It's a file
        unless sameFile( origPath, backupPath )
          $diffCount += 1
          STDOUT.puts "FILE: [#{origPath}] not found at, or doesn't match [#{backupPath}]"
        end
      end
    end # Dir.foreach
  rescue Errno::EACCES
    STDOUT.puts "ERROR: Can't read directory [#{original}]"
    $errorCount += 1
  end
end # compareDirs

def printSummary
  differPercent = ($diffCount.to_f / $itemCount.to_f * 100).round( 2 )
  if $options[:machine]
    STDOUT.puts "SUMMARY: items:#{$itemCount}, diff:#{$diffCount}, " +
                "diffpct:#{differPercent}, skip:#{$skippedCount}, " + 
                "err:#{$errorCount}"
  else
    STDOUT.puts "\nSUMMARY:"
    STDOUT.puts "    Items processed: #{$itemCount}"
    STDOUT.puts "    Differences: #{$diffCount} (#{differPercent}%)"
    STDOUT.puts "    Similarities: #{$itemCount - $diffCount}"
    STDOUT.puts "    Skipped: #{$skippedCount}"
    STDOUT.puts "    Errors: #{$errorCount}"
  end
end

# Exit gracefully on CTRL+C
trap( "SIGINT" ) do
  STDOUT.puts "\n\nCaught SIGINT. Stopping."
  printSummary
  exit
end

compareDirs
printSummary

