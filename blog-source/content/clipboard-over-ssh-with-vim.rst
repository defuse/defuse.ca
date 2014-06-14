Clipboard over SSH with Vim
############################
:slug: clipboard-over-ssh-with-vim
:author: Taylor Hornby
:date: 2012-04-28 00:00
:category: linux
:tags: clipboard, ssh

The following is a simple and easy way to get clipboard sharing with Vim working
between two Linux installations (specifically a desktop and remote server).

First install ``xclip`` on both your local machine and the server:

.. code:: bash

    apt-get install xclip

Installing ``xclip`` will bring along some dependencies like ``x11-common``, but
don't worry, you don't need to run an X server on the server to get this
working.

Next, enable X11 forwarding on the server. Add the following to
``/etc/ssh/sshd_config``:

.. code:: bash

    X11Forwarding yes

Then add the following to the server's ``~/.vimrc``:

.. code:: bash

    vmap "+y :!xclip -f -sel clip
    map "+p :r!xclip -o -sel clip

This will remap the ``"+y`` (copy) and ``"+p`` (paste) commands to use
``xclip``.

Now you're done. SSH into the server with ``ssh -X``, fire up vim, and see if it
works. If you don't want to type ``-X`` every time, you can add ``ForwardX11
yes`` to ``/etc/ssh/ssh_config`` on your local machine to make it always try to
forward X.

Source: http://vim.wikia.com/wiki/GNU/Linux_clipboard_copy/paste_with_xclip
