Windows Magnifier Hack
#######################
:slug: windows-magnifier-password-reset.rst
:author: Taylor Hornby
:date: 2013-02-19 00:00
:category: security
:tags: windows

Steps to reset a Windows account password given physical access to the box:

1. Boot a Linux live cd and mount C:\ somewhere.
2. Find magnifier.exe (somewhere in System32) and replace it with cmd.exe.
3. Boot to the Windows login screen.
4. Press WinKey + U
5. Start the magnifier, which should run cmd.exe.
6. `Change the password in the Command Prompt`_.

.. _`Change the password in the Command Prompt`: http://support.microsoft.com/kb/149427

Note that this will destroy all data encrypted with `EFS`_.

.. _`EFS`: https://en.wikipedia.org/wiki/Encrypting_File_System

I'm not sure who thought of this first, but if I knew, I would credit them here.
