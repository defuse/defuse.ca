Vim: Make Rainbow Parentheses Work in PHP
##########################################
:slug: vim-rainbow-parentheses-work-in-php
:author: Taylor Hornby
:date: 2012-12-22 00:00
:category: programming
:tags: vim

**EDIT:** What I propose here doesn't actually fix it, it still fails when
there's a greater-than sign inside an if statement. I have spent over 8 hours
trying to get this to work and that is too much, so I am giving up. If you find
a solution, please email it to me.

I use this script to get rainbow parentheses in Vim:

- https://github.com/kien/rainbow_parentheses.vim
- http://www.vim.org/scripts/script.php?script_id=3772

It colors nested parentheses like this:

.. image:: https://defuse.ca/images/rainbow.png
    :alt: Vim Rainbow Parentheses

Unfortunately, it doesn't work with PHP, because the PHP syntax file only allows
its own syntax regions to appear inside the ``<?php`` and ``?>`` tags. The fix
is simple:

1. Copy the 'system' php syntax file (usually
   ``/usr/local/share/vim/vim73/syntax/php.vim``) to your personal
   ``~/.vim/syntax`` folder (create it if it does not exist).

1. Edit ``~/.vim/syntax/php.vim``, find the line that starts with ``syn cluster
   phpClTop`` and add ``level16`` to the end of the 'contains' list on that
   line. If you have set ``g:rbpt_max`` in your .vimrc, then you will need to
   change the "16" to that value.
