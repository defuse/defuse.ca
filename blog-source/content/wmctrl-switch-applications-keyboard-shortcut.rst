Using 'wmctrl' to Quickly Switch Between Open Applications
###########################################################
:slug: wmctrl-switch-applications-keyboard-shortcut
:author: Taylor Hornby
:date: 2012-06-23 00:00
:category: linux
:tags: wmctrl

The ``wmctrl`` program allows you to programatically bring a running application
to the foreground on Linux. It works even with compiz enabled, and will switch
to the virtual desktop that the application is running in before bringing it to
the foreground. This makes it a perfect utility for mapping keyboard shortcuts
to quickly switch between applications you leave running all the time.

To install wmctrl on Debian:

.. code:: bash

    apt-get install wmctrl

For example, to switch to Thunderbird:

.. code:: bash

    wmctrl -a "Mozilla Thunderbird"

The ``-a`` argument makes wmctrl switch to the first window it finds with the
provided string in its title. See wmctrl's manpage for more information.

You can map a command like this to a keyboard shortcut using the (in Debian)
``System -> Preferences -> Keyboard Shortcuts`` utility.
