#!/bin/bash

# Script for pulling Havoc's vim config from http://defuse.ca/vimrc.htm
# This will destroy the current user's vim configuration (~/.vim, ~/.vimrc)
# and replace it with Havoc's.

# Use unique temp file names so the script can be run by many users at the
# same time.
TMPID="$RANDOM$(whoami)"

# Grab the vimrc
wget -O ~/.vimrc http://defuse.ca/source/vimrc

# Grab the .vim folder, extract it in /tmp, then move to ~
wget -O /tmp/vimupdate_$TMPID.zip http://defuse.ca/source/vim.zip
unzip -o /tmp/vimupdate_$TMPID.zip -d /tmp/vimupdate_$TMPID
rm -rf ~/.vim/*
mv /tmp/vimupdate_$TMPID/* ~/.vim/

# Clean up the temporary files
rm -f /tmp/vimupdate_$TMPID.zip
rm -rf /tmp/vimupdate_$TMPID
