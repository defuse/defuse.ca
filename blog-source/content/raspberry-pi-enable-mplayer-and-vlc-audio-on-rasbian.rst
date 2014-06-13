Raspberry PI: Enable MPlayer and VLC audio on Raspbian
#######################################################
:slug: raspberry-pi-enable-mplayer-and-vlc-audio-on-rasbian
:author: Taylor Hornby
:date: 2012-08-02 00:00
:category: linux
:tags: raspberry pi, raspbian

.. NOTE: The slug misspells raspbian intentionally so it matches the old URL.

If mplayer is giving you an error like the following... 

.. code:: text

    AO: [pulse] Init failed: Connection refused
    Failed to initialize audio driver 'pulse'
    [AO_ALSA] Unable to set hw-parameters: Invalid argument
    Failed to initialize audio driver 'alsa'
    [AO SDL] Samplerate: 44100Hz Channels: Stereo Format floatle
    [AO SDL] using aalib audio driver.
    [AO SDL] Unsupported audio format: 0x1d.
    [AO SDL] Unable to open audio: No available audio device
    Failed to initialize audio driver 'sdl:aalib'
    Could not open/initialize audio device -> no sound.
    Audio: no sound
    Video: no video

... add the following to /etc/asound.conf (create it if it does not exist): 

.. code:: text

    pcm.!default {
        type hw
        card 0
    }
    
    ctl.!default {
        type hw
        card 0
    }

Then, run: 

.. code:: text

    modprobe snd_bcm2835

Then add the following ``/etc/modules`` (unless it's already there):

.. code:: text

    snd-bcm2835

`Source - "ALSA on Raspbian"`_.

.. _`Source - "ALSA on Raspbian"`: http://www.raspberrypi.org/phpBB3/viewtopic.php?f=66&t=7107
