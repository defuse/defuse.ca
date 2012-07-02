#!/bin/bash

# Script for pulling Havoc's vim config from http://defuse.ca/vimrc.htm
# This will destroy the current user's vim configuration (~/.vim, ~/.vimrc)
# and replace it with Havoc's.

# Grab the vimrc
wget -O ~/.vimrc http://defuse.ca/source/vimrc

# Grab the .vim folder, extract it in /tmp, then move to ~
wget -O /tmp/vimupdate.zip http://defuse.ca/source/vim.zip
unzip -o /tmp/vimupdate.zip -d /tmp/vimupdate
rm -rf ~/.vim/*
mv /tmp/vimupdate/home/firexware/.vim/* ~/.vim/

# Clean up the temporary files
rm -f /tmp/vimupdate.zip
rm -rf /tmp/vimupdate
