Random .vimrc Tips
######################################################
:slug: random-vimrc-tips
:author: Taylor Hornby
:date: 2012-04-29 00:00
:category: programming
:tags: vim

Here are some of my favorite .vimrc lines.

Save quickly from any mode by pressing CTRL+\:

.. code:: text

    imap <C-\> <Esc>:w<Cr>
    map <C-\> <Esc>:w<Cr>

Make indenting and de-indenting in visual mode preserve the selection:

.. code:: text

    vnoremap > ><CR>gv
    vnoremap < <<CR>gv

Quick case insensitive search with double slash or double question mark:

.. code:: text

    map // /\c
    map ?? ?\c

Make the j and k keys move up by a line on the screen rather than a line in the
file (very useful when navigating a long line that has wrapped many times):

.. code:: text

    map j gj
    map k gk

Highlight column 80 (Vim 7.3+ only):

.. code:: text

    if v:version >= 703
        set cc=80
        hi ColorColumn ctermbg=Gray ctermfg=Black guibg=#404040
        command Skinny set cc=73
        command Wide set cc=80
    endif

Don’t show the splash screen:

.. code:: text

    set shortmess+=I

Keep the cursor 4 lines away from the top or bottom to automatically preserve
context when editing near the top or bottom of the window:

.. code:: text

    set scrolloff=4

Make CTRL+u and CTRL+d less confusing:

.. code:: text

    map <C-u> 10<C-Y>10k
    map <C-d> 10<C-E>10j

Scroll the screen with ALT+{j,k}:

.. code:: text

    map <A-j> 2<C-E>
    map <A-k> 2<C-Y>

Easy paste toggle:

.. code:: text

    " Allow middle-click pasting of large texts in terminal
    set pastetoggle=<F5>
    " Clear paste mode when going back to normal mode
    au InsertLeave * set nopaste

Switch windows quickly with CTRL+{h,j,k,l}:

.. code:: text

    " This breaks backspace in a terminal, but I never use backspace in normal mode
    map <C-h> <C-W>h
    map <C-j> <C-W>j
    map <C-k> <C-W>k
    map <C-l> <C-W>l
    " This would break in a terminal where ^H is backspace.
    if has("gui_running")
        imap <C-h> <Esc><C-W>h
    endif
    imap <C-j> <Esc><C-W>j
    imap <C-k> <Esc><C-W>k
    imap <C-l> <Esc><C-W>l

`Click here for my entire Vim configuration.`_

.. _`Click here for my entire Vim configuration.`: https://defuse.ca/vimrc.htm
