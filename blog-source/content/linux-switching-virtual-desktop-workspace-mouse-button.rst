Switching Desktops/Workspaces with Mouse Buttons
#################################################
:slug: linux-switch-virtual-desktop-workspace-mouse-button
:author: Taylor Hornby
:date: 2013-04-29 00:00
:category: linux
:tags: xbindkeys

I had to uninstall compiz today, and I absolutely can't live without being able
to switch virtual desktops (also known as workspaces) with the buttons on the
side of my mouse. Here's how you do it:

First, install **xbindkeys** and **xdotool**:

.. code:: bash

    apt-get install xbindkeys xdotool

Then create a **.xbindkeysrc** file in your home folder:

.. code:: bash

    vim ~/.xbindkeysrc

Into this file, put the following:

.. code:: text

    # -- Move Right --
    # This is the general command that works with any number of workspaces:
    # "xdotool set_desktop $(expr $(expr $(xdotool get_desktop) + 1) % $(xdotool get_num_desktops))"
    # This is the optimized command for 5 workspaces:
    "xdotool set_desktop $(expr $(expr $(xdotool get_desktop) + 1) % 5)"
        b:8
    
    # -- Move Left --
    # This is the general command that works with any number of workspaces:
    # "xdotool set_desktop $(expr $(expr $(xdotool get_desktop) + $(expr $(xdotool get_num_desktops) - 1)) % $(xdotool get_num_desktops))"
    # This is the optimized command for 5 workspaces:
    "xdotool set_desktop $(expr $(expr $(xdotool get_desktop) + 4) % 5)"
        b:9

In the above config, replace "8" with the number of the button you want to use
to move right, and "9" with the number of the button you want to use to move
left. If you don't know what the button numbers are, run <b>xev</b>, press the
one you want while hovering your mouse over the window, and you'll see it in the
output.

The command that's uncommented works with a 5-workspace setup. If you want it to
work with X desktops either comment that one out and uncomment the general
command, or replace "5" with X and "4" with X - 1.

Then add **xbindkeys** (no arguments) to your startup applications. If you want windows to "edge switch" (switch workspaces when you drag them across the side of your screen), look at `brightside`_.

.. _`brightside`: http://lifehacker.com/263508/add-screen-actions-with-brightside

Note: `HOWTO: Mouse buttons to change workspaces in gnome-shell`_ helped me figure out how to do this.

.. _`HOWTO: Mouse buttons to change workspaces in gnome-shell`: http://forum.pinguyos.com/Thread-HOWTO-Mouse-buttons-to-change-workspaces-in-gnome-shell
