Linux: Fix pipe character on ASUS laptops
##########################################
:slug: linux-fix-pipe-character-on-asus-laptops
:author: Taylor Hornby
:date: 2012-07-27 00:00
:category: linux
:tags: keyboard

Is your pipe/backslash key not typing pipes and backslashes? Is it typing
less-than and greater-than symbols? If so, add...

.. code:: text

    key <LSGT> { [ backslash, bar, backslash, bar] };

..to `/usr/share/X11/xkb/symbols/us` in the `kxb_symbols` `"basic"` section. 

This seems to be a problem with ASUS laptops, I've seen two of them doing this. 

Reference: http://forums.fedoraforum.org/showthread.php?t=240415
