<?php
    Upvote::render_arrows(
        "bochshacking",
        "defuse_pages",
        "Bochs Hacking Guide",
        "Hacking the Bochs x86 CPU emulator.",
        "https://defuse.ca/bochs-hacking-guide.htm"
    );
?>
<div class="pagedate">
January 2, 2013
</div>
<h1>Bochs Hacking Guide</h1>

<p>
This guide will get you started hacking the <a
href="http://bochs.sourceforge.net/">Bochs</a> IA-32 (x86) processor emulator.
It is a result of a <a href="/instruction-filters.htm">research project</a>,
aided by <a href="http://pages.cpsc.ucalgary.ca/~locasto/">Dr. Michael E.
Locasto</a>, in which I modified Bochs and the Linux kernel to implement an
exploit defense system based on disabling some of the CPU's instructions. If you
want to use Bochs to experiment with architecture changes, this guide will get
you going in the right direction.
</p>

<p>
Bochs is similar to virtual machine software like VirtualBox and VMWare, except
that all instructions are emulated by C++ code. Therefore, Bochs is very slow,
but also extremely versatile. Once you know your way around Bochs, you can do
all sorts of cool things like modifying the behavior of existing instructions,
or even adding totally new instructions to the architecture.
</p>

<p>
This guide will walk you through the steps of compiling Bochs and running
a modern Linux kernel inside it. After that, you will be introduced to Bochs'
source code, and you will be shown how to modify and add CPU instructions.
</p>

<p>
This guide was written for Linux-based operating systems so it assumes the
reader has basic Linux skills (can use the command line, can compile software,
knows what the kernel is, etc.), although it should apply just as well to other
operating systems.
</p>

<h2>Compiling Bochs</h2>

<p>
If your Linux distribution's software repositories include Bochs, you can use
that instead of compiling the source. However, we will be modifying Bochs later
on in the guide, so you should compile it yourself.
</p>

<p>
To get started, download the latest stable version of the Bochs source code from
<a href="http://sourceforge.net/projects/bochs/files/bochs/">Bochs' SourceForge
Page</a>. At the time of writing, the current release is 2.6.2, so that's the
one this guide will be using.
</p>

<p>
After you've downloaded the source code, extract it:
</p>

<pre>
$ tar xvf bochs-2.6.2.tar.gz 
$ cd bochs-2.6.2
</pre>

<p>
Bochs provides a bunch of build configurations for various operating systems:
</p>

<pre>
$ ls .conf.*
.conf.amigaos     .conf.linux  .conf.macosx   .conf.sparc         .conf.win32-vcpp
.conf.everything  .conf.macos  .conf.nothing  .conf.win32-cygwin
</pre>

<p>
These are scripts which run the "configure" script with various command line
arguments enabling or disabling certain features. We will be writing our own
script, though, since none of these work very well in all cases. Create a file
called "my_configure.sh" and put this inside it:
</p>

<pre>
#!/bin/bash

# This is the folder that bochs will install to. Feel free to change this to
# anything you like. By default, we install bochs to a &#039;my_build&quot; folder in the
# current directory.
install_path=$(realpath ./my_build)

# Create the installation folder if it doesn&#039;t exist.
mkdir -p &quot;$install_path&quot;

# Run Boch&#039;s configure script with the features that we need.
./configure \
&nbsp;&nbsp;--enable-smp \
&nbsp;&nbsp;--enable-x86-64 \
&nbsp;&nbsp;--enable-all-optimizations \
&nbsp;&nbsp;--enable-long-phy-address \
&nbsp;&nbsp;--enable-configurable-msrs \
&nbsp;&nbsp;--enable-disasm \
&nbsp;&nbsp;--enable-fpu \
&nbsp;&nbsp;--enable-alignment-check \
&nbsp;&nbsp;--enable-3dnow \
&nbsp;&nbsp;--enable-svm \
&nbsp;&nbsp;--enable-vmx=2 \
&nbsp;&nbsp;--enable-avx \
&nbsp;&nbsp;--enable-a20-pin \
&nbsp;&nbsp;--enable-pci \
&nbsp;&nbsp;--enable-clgd54xx \
&nbsp;&nbsp;--enable-voodoo \
&nbsp;&nbsp;--enable-usb \
&nbsp;&nbsp;--enable-usb-ohci \
&nbsp;&nbsp;--enable-usb-xhci \
&nbsp;&nbsp;--enable-cdrom \
&nbsp;&nbsp;--enable-sb16 \
&nbsp;&nbsp;--enable-es1370 \
&nbsp;&nbsp;--enable-show-ips \
&nbsp;&nbsp;--with-all-libs &nbsp; \
&nbsp;&nbsp;--prefix=&quot;$install_path&quot;
</pre>

<p>
This will configure Bochs to install to the "my_build" folder in the current
directory. The other arguments enable most of Bochs' features, except, notably,
networking. I can't get the network component to work no matter how much I try,
so I'll leave it out of this guide.
</p>

<p>
To build Bochs, just run:
</p>

<pre>
$ ./my_configure.sh
$ make -j 4
$ make install
</pre>

<p>
This will compile Bochs and place the binaries in "./my_build/bin". Change into
that folder, then, in the next section, we will make a disk image and install
Tiny Core Linux in Bochs.
</p>

<pre>
$ cd ./my_build/bin
</pre>

<h2>Creating a Disk Image</h2>

<p>
To install an operating system in a Bochs VM, it will need a hard disk. Bochs
uses regular flat files as hard disk images. However, we can't just use any size
of file. We have to create one that fits a specific hard disk geometry
(cylinders, heads, and sectors per track).
</p>

<p>
The size of a disk image is equal to:
<p>

<pre>
disk image size (bytes) = cylinders * heads * spt * 512
</pre>

<p>
To make things easy, we will fix the number of heads at 8 and the number of
sectors per track at 63. We will then select the disk size by varying the
cylinder count. If we want an N megabyte disk, then we calculate the number of
cylinders as follows:
</p>

<pre>
cylinders = N * 1024 * 1024 / 512 / 8 / 63
</pre>

<p>
We can put this into a script called "create_disk_image.sh" to automate it:
<p>

<pre>
#!/bin/bash
# Usage: ./create_disk_sectors &lt;megabytes&gt; &lt;path&gt;
# For example, to create a 4GB disk image, run:
# &nbsp;./create_disk_sectors 4096 disk.img

megs=$1
heads=8
sectors_per_track=63
cylinders=$(expr $megs \* 1024 \* 1024 / 512 / $heads / $sectors_per_track)
sectors=$(expr $cylinders \* $heads \* $sectors_per_track)
dd if=/dev/zero of=$2 bs=512 count=$sectors

echo &quot;Disk image file [$2] was created.&quot;
echo &quot;Geometry: cylinders=$cylinders, heads=$heads, spt=$sectors_per_track&quot;
</pre>

<p>
Now, if we want to create a 1GB disk image to install an operating system, we
can run:
</p>

<pre>
$ ./create_disk_image.sh 1024 bochs-disk-image.img
2097144+0 records in
2097144+0 records out
1073737728 bytes (1.1 GB) copied, 3.47051 s, 309 MB/s
Disk image file [bochs-disk-image.img] was created.
Geometry: cylinders=4161, heads=8, spt=63
</pre>

<p>
Make sure you save the "Geometry" line of the output, as it's needed to
configure Bochs to use the disk file, which we will do in the next section.
</p>

<h2>Installing Tiny Core Linux</h2>

<p>
Now that we've compiled Bochs and generated a disk image, we're ready to create
the Bochs configuration and install an operating system. In this section, we'll
install <a href="http://distro.ibiblio.org/tinycorelinux/">Tiny Core Linux</a>.
</p>

<p>
Start downloading
<a href="http://distro.ibiblio.org/tinycorelinux/5.x/x86/release/CorePlus-current.iso">
CorePlus-current.iso</a> right now, since it will take some time.
</p>

<h3>Creating a "bochsrc" file</h3>

<p>
The first thing we need to do is create a "bochsrc" file. This is the Bochs
configuration file. It defines all of the various hardware components of the
virtual system being emulated. Here's an example bochsrc file, that will work
with Tiny Core Linux and the disk image we created in the last section.
</p>

<pre>
# System configuration.
romimage: file=$BXSHARE/BIOS-bochs-latest
vgaromimage: file=$BXSHARE/VGABIOS-lgpl-latest
cpu: model=corei7_ivy_bridge_3770k, ips=120000000
clock: sync=slowdown
megs: 256
boot: cdrom, disk

# Hard disks.
ata0: enabled=1, ioaddr1=0x1f0, ioaddr2=0x3f0, irq=14
ata0-master: type=disk, path=&quot;bochs-disk-image.img&quot;, cylinders=4161, heads=8, spt=63

# CDROM
ata1: enabled=1, ioaddr1=0x170, ioaddr2=0x370, irq=15
ata1-master: type=cdrom, path=&quot;CorePlus-current.iso&quot;, status=inserted
</pre>

<p>
This configuration defines a system with 256 megabytes of RAM, a hard disk, and
a CDROM drive. The CPU is set to emulate the features of the Intel Core i7 Ivy
Bridge 3770K processor. The "clock:" line determines how Bochs will synchronize
the VM's clock with the host system's clock. When set to "slowdown", performance
is sacrificed in favor of reproducibility and keeping the clock in sync with the
host system. The "boot:" line sets the boot order.
</p>

<p>
Make sure the hard disk "path" is set to the path of the disk image file you
created in the last section, and that the cylinders, heads, and spt settings are
the ones from the "Geometry:" line of create_disk_image.sh's output. Also make
sure that the CDROM "path" is set to the location of the CorePlus-current.iso
that you downloaded.
</p>

<p>
For more information about the bochsrc settings, see 
<a href="http://bochs.sourceforge.net/doc/docbook/user/bochsrc.html">
the bochsrc documentation</a>.
</p>

<h3>Booting and Installing Tiny Core Linux</h3>

<p>
After you've created the bochsrc file and waited for the Tiny Core Linux ISO to
download, you're ready to boot the virtual machine.
</p>

<pre>
$ ./bochs -f ./bochsrc -q
</pre>

<p>
The Tiny Core Linux CD will begin booting. Select "Boot Core Plus with default
FLWM topside."
</p>

<center>
<img src="/images/bochs-tinycore-booting.png" alt="Tiny Core Linux Booting" />
</center>

<p>
After a few minutes, you should be presented with a Tiny Core Linux's desktop.
</p>

<p>
Hold the CTRL key while clicking the middle mouse button (mouse wheel) on the
Bochs window to be able to control the mouse inside Bochs. Press it again to
release your mouse cursor back to the host system.
</p>

<center>
<img src="/images/bochs-tinycore-desktop.png" width="768" alt="Tiny Core Linux Desktop" />
</center>

<p>
Click the "TC_Install" icon (second from the right) to start installing Tiny
Core Linux to disk. The installation process is very straightforward. Leave
"Frugal" selected, check "Whole Disk", then select "sda". The next pages can be
left on the default settings. On the last page, click "Proceed" to install.
</p>

<p>
If all went well, Tiny Core Linux is now installed to the disk image. To boot
into it, shut Bochs down, edit the bochsrc to comment out the "ata1-master" line
(the CDROM), then start Bochs again.
</p>

<p>
Now you have a recent Linux kernel running in Bochs! You can either stop here
and play with it, or continue on to the next sections where we compile the Linux
kernel ourself and install it in the Tiny Core Linux VM we just created.
</p>

<h2>Installing a Custom Kernel in Tiny Core Linux</h2>

<p>
<b>Note:</b> You can skip this section and move right on to the "Modifying
Bochs" section if you aren't interested in making kernel modifications.
</p>

<h3>Building the Linux Kernel</h3>

<p>
At the time of writing, Tiny Core Linux comes with version 3.8.13 of the Linux
kernel. We will be building version 3.12.4 and installing it in the Tiny Core
Linux VM we created earlier.
</p>

<p>
Tiny Core Linux has their own Wiki page about
<a href="http://wiki.tinycorelinux.net/wiki:custom_kernel">
compiling a custom kernel</a>, which this section of the guide is based off of,
and may be helpful to the reader.
</p>

<p>
Building the Linux kernel is easy. First, download and extract it:
</p>

<pre>
$ wget https://www.kernel.org/pub/linux/kernel/v3.x/linux-3.12.4.tar.xz
$ tar xvf linux-3.12.4.tar.xz
$ cd linux-3.12.4.tar.xz
</pre>

<p>
Download Tiny Core's
<a href="http://tinycorelinux.net/5.x/x86/release/src/kernel/">kernel configuration</a>
and save it in .config.
</p>

<pre>
$ wget http://tinycorelinux.net/5.x/x86/release/src/kernel/config-3.8.13-tinycore
$ mv config-3.8.13-tinycore .config
</pre>

<p>
Run `make oldconfig`. Answer all of the questions with the default answer (Just
hold the Enter key until it's done). Now run `make menuconfig`. If you want to
make any extra changes, use this menu to make them, otherwise exit from the
menu.
</p>

<p>
After you have done this, open the .config file in a text editor and change the
CONFIG_CC_OPTIMIZE_FOR_SIZE=y line to end in =n. By default, Tiny Core Linux's
config wants to build an extremely small kernel. We have of plenty space, so we
can turn off this optimization.
</p>

<p>
The next two commands actually compile the kernel and some kernel modules. If
you get asked more questions, just pick the default answer.
</p>

<pre>
$ make bzImage -j 4
$ make modules -j 4
</pre>

<p>
<b>Note:</b> If you are compiling an older kernel on a 64-bit system, you may
have to add ARCH="x86" to all of your make commands.
</p>

<p>
Next, make a folder to hold the compiled kernel modules and install them there.
</p>

<pre>
$ mkdir ./my_modules
$ make INSTALL_MOD_PATH="./my_modules" modules_install firmware_install
</pre>

<p>
That's it! The kernel has been compiled. The bzImage is in
./arch/x86/boot/bzImage, and the modules are in ./my_modules. We will need these
in the next two sections, when we install them into our Tiny Core Linux VM.
</p>

<h3>Mounting The Bochs Disk Image</h3>

<p>
So far we've created a Tiny Core Linux virtual machine with Bochs and compiled
the Linux kernel. The next step is to install the kernel we compiled into the
VM. To do that, we have to get access to the VM's filesystem. Fortunately, there
are some utilities that make this very easy. We can use the kpartx tool to mount
the raw disk image file.
</p>

<p>
<b>Note:</b> On Arch Linux, the kpartx tool is part of the "multipath-tools"
package in the AUR. In Debian, you can apt-get install kpartx.
</p>

<p>
Start off with a shell script to mount the image. Save this in mount.sh.
</p>

<pre>
#!/bin/bash
kpartx -a -v bochs-disk-image.img
mount -o loop /dev/mapper/loop0p1 /mnt/myimage
</pre>

<p>
You may have to change the "loop0p1" to "loop1p1" or "loop2p1" depending on what
else you are using your computer for.
</p>

<p>
Then, save this unmounting script in unmount.sh.
</p>

<pre>
#!/bin/bash
umount /mnt/myimage
kpartx -d -v disk.img
</pre>

<p>
The script mounts the disk image to /mnt/myimage, so create that directory.
</p>

<pre>
$ mkdir /mnt/myimage
</pre>

<p>
Finally, run the mount.sh script to mount the disk image. You might have to run
it as root. In the next section, we will install the new kernel.
</p>

<h3>Installing the New Kernel</h3>

<p>
Tiny Core Linux is different from other Linux distributions in the way its root
filesystem works. If you look in the mounted image, you'll find a structure like
this:
</p>

<pre>
# tree /mnt/myimage/
/mnt/myimage/
├── lost+found
└── tce
    ├── boot
    │   ├── core.gz
    │   ├── extlinux
    │   │   ├── extlinux.conf
    │   │   └── ldlinux.sys
    │   └── vmlinuz
    ├── mydata.tgz
    ├── onboot.lst
    ├── ondemand
    ├── optional
    │   ├── aterm.tcz
    │   ├── ...
    │   └── Xvesa.tcz.md5.txt
    └── xwbar.lst
</pre>

<p>
When you start the machine, this isn't what you see mounted at /. Instead, the
filesystem is somehow generated at boot time from these files. If you want to
know more, see the Tiny Core Linux documentation. For this guide, all we need to
know is that tce/boot/vmlinuz is the kernel bzImage, and we have to add the
kernel modules to core.gz, which is a gzipped cpio file.
</p>

<p>
Backup the old files so you can recover if you screw up the kernel install.
</p>

<pre>
# cp /mnt/myimage/tce/boot/core.gz /mnt/myimage/tce/boot/core.gz-backup
# cp /mnt/myimage/tce/boot/vmlinuz /mnt/myimage/tce/boot/vmlinuz-backup
</pre>

<p>
To install the new kernel, first replace vmlinuz with the new bzImage.
</p>

<pre>
# cp arch/x86/boot/bzImage /mnt/myimage/tce/boot/vmlinuz
</pre>

<p>
Now, we'll need to rebuild the core.gz file with the new kernel modules. Copy it
over to your system and extract it:
</p>

<pre>
# mkdir core_extract
# cd core_extract
# cp /mnt/myimage/tce/boot/core.gz core.gz
# gunzip core.gz
# cpio -idv &lt; core
</pre>

<p>
Copy the new kernel modules into lib/modules and the new firmware into
lib/firmware.
</p>

<pre>
# mv ../my_modules/lib/firmware/ lib/
# mv ../my_modules/lib/modules/3.12.4-tinycore/ lib/modules/
</pre>

<p>
Put everything back into a cpio file, and gzip it like it was. Then install it
back to the disk image.
</p>

<pre>
# find . | cpio -ov --format='newc' &lt; core
# gzip core
# mv core.gz /mnt/myimage/tce/boot/core.gz
</pre>

<p>
Unmount the disk image using the unmount.sh script we made in the last section,
then start the VM. If you did everything right, it should be running the kernel
you compiled.
</p>

<center>
<img
    src="/images/bochs-tinycore-newkernel.png"
    alt="New Kernel Running in Tiny Core Linux"
    width="768"
/>
</center>

<p>
So far we've created a Tiny Core Linux VM and installed a custom kernel in it.
In the next section, we'll get to the really fun part, and the whole point of
this guide: Modifying Bochs. We'll change the behavior of some instructions, add
some new ones, and test our changes in the VM we just made.
</p>

<h2>Modifying Bochs</h2>

<p>
In this section you'll be introduced to the Bochs source code and walked through
the process of modifying and adding instructions to the CPU. You'll be using the
virtual machine we created above to test out your modifications.
</p>

<p>
Open up the Bochs source code directory (which you were using in the "Compiling
Bochs" section), and you'll see something like this (subdirectories are in
bold).
</p>

<pre>
$ ls
aclocal.m4 &nbsp; &nbsp; &nbsp; <b>cpu</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; logio.cc &nbsp; &nbsp; &nbsp; &nbsp;pc_system.cc
<b>bios</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; cpudb.h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ltdl.c &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;pc_system.h
bochs.h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;crc.cc &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;ltdlconf.h.in &nbsp; plugin.cc
<b>build</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>disasm</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;ltdl.h &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;plugin.h
<b>bx_debug</b> &nbsp; &nbsp; &nbsp; &nbsp; <b>doc</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ltmain.sh &nbsp; &nbsp; &nbsp; README
bxversion.h.in &nbsp; <b>docs-html</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; main.cc &nbsp; &nbsp; &nbsp; &nbsp; README-plugins
bxversion.rc.in &nbsp;extplugin.h &nbsp; &nbsp; &nbsp; &nbsp; Makefile.in &nbsp; &nbsp; README.rfb
CHANGES &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;gdbstub.cc &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<b>memory</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;README-wxWidgets
config.cc &nbsp; &nbsp; &nbsp; &nbsp;<b>gui</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <b>misc</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;TESTFORM.txt
config.guess &nbsp; &nbsp; <b>host</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;msrs.def &nbsp; &nbsp; &nbsp; &nbsp;TODO
config.h.in &nbsp; &nbsp; &nbsp;install-sh &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;osdep.cc &nbsp; &nbsp; &nbsp; &nbsp;win32_enh_dbg.rc
config.sub &nbsp; &nbsp; &nbsp; <b>instrument</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;osdep.h &nbsp; &nbsp; &nbsp; &nbsp; win32res.rc
configure &nbsp; &nbsp; &nbsp; &nbsp;<b>iodev</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; param_names.h &nbsp; wxbochs.rc
configure.in &nbsp; &nbsp; LICENSE &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; PARAM_TREE.txt
COPYING &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;load32bitOShack.cc &nbsp;<b>patches</b>
</pre>

<p>
There's lots of code here, but we'll be focusing on the cpu folder, which
contains the C++ implementations of all of the x86 instructions. Let me
highlight the important files.
</p>

<ul>
    <li>
        <p><strong>cpu/ia_opcodes.h</strong></p>
        <p>
            This file specifies all of the CPU opcodes (instructions) and which
            C++ methods implement them.
        </p>
    </li>
    <li>
        <p><strong>cpu/cpu.h</strong></p>
        <p>
            The BX_CPU_C class, representing the system's CPU, is declared in
            this file. If you want to make modifications to other parts of the
            CPU, like adding a new register, this is where you do it. This file
            also contains the declarations of all the C++ methods implementing
            instructions.
        </p>
    </li>
    <li>
        <p><strong>cpu/fetchdecode.cc</strong></p>
        <p>
            In fetchdecode.cc, there is a table which maps the actual opcode
            numbers to the opcode structure created in ia_opcodes.h. This file
            also houses the fetch-decode cycle of the CPU.
        </p>
    </li>
    <li>
        <p><strong>cpu/fetchdecode64.cc</strong></p>
        <p>
            This is like fetchdecode.cc, except for 64-bit CPUs. Any changes you
            make in fetchdecode.cc should usually be mirrored in this file.
        </p>
    </li>
</ul>

<p>
Carefully read through these files, and you'll have a good understanding of how
the CPU is implemented in Bochs. The methods that implement actual instructions
are declared in cpu.h, but are defined in many .cc files in the cpu folder. For
example, cpu/arith32.cc defines the implementation of the 32-bit arithmetic
instructions, including INC, ADD, SUB, etc. Read through some of these files as
well, to get an idea of how instructions are implemented.
</p>

<h3>Turning XOR into ADD</h3>

<p>
With this basic understanding of the CPU in Bochs, we can start making changes.
Just for fun, we'll change the XOR instruction to ADD the operands instead. This
will probably break everything we try to run in our VM, but it will be fun to
see how and where things break.
</p>

<p>
<b>Note:</b> This is a pretty lame example of modifying an instruction, so if
you've already thought of another instruction you'd like to modify, try that
instead!
</p>

<p>
The implementation of the various variants of the XOR instruction are spread
across logical8.cc, logical16.cc, logical32.cc, and logical64.cc. We'll only
change the 32-bit register-XOR-register variant. So open up logical32.cc. You'll
see the definitions for a bunch of 32-bit XOR variants:
</p>

<pre>
BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_EdGdM(bxInstruction_c *i)
// ...

BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_GdEdR(bxInstruction_c *i)
// ...

BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_GdEdM(bxInstruction_c *i)
// ...

BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_EdIdM(bxInstruction_c *i)
// ...

BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_EdIdR(bxInstruction_c *i)
// ...
</pre>

<p>
What are these weird "EdGdM" things? They are abbreviations of the format of the
operand.  For example "Ed" means, essentially, a doubleword (32-bit) memory
address or register. "Gd" means a doubleword (32-bit) register. The full list
can be found in Appendix A, section A.2 of the 
<a href="http://download.intel.com/products/processor/manual/325383.pdf">
Intel IA-32 Architectures Software Developer's Manual Volume 2.</a>
</p>

<p>
To keep things simple, we'll only change the "EdGdM" variant. Its
implementation looks like this:
</p>

<pre>
BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::XOR_EdGdM(bxInstruction_c *i)
{
&nbsp;&nbsp;Bit32u op1_32, op2_32;

&nbsp;&nbsp;bx_address eaddr = BX_CPU_CALL_METHODR(i-&gt;ResolveModrm, (i));

&nbsp;&nbsp;op1_32 = read_RMW_virtual_dword(i-&gt;seg(), eaddr);
&nbsp;&nbsp;op2_32 = BX_READ_32BIT_REG(i-&gt;src());
&nbsp;&nbsp;<b>op1_32 ^= op2_32;</b>
&nbsp;&nbsp;write_RMW_virtual_dword(op1_32);

&nbsp;&nbsp;SET_FLAGS_OSZAPC_LOGIC_32(op1_32);

&nbsp;&nbsp;BX_NEXT_INSTR(i);
}
</pre>

<p>
Just change the "op1_32 ^= op2_32;" line (in bold) to "op1_32 += op2_32;". Now,
all the XOR instructions of this variant become ADDs. You can also make Bochs
log the fact that your modified instruction was executed by adding a line like
the following.
</p>

<pre>
BX_PANIC((&quot;My modified XOR instruction executed!&quot;));
</pre>

<p>
BX_PANIC will halt the processor and present you with a message box that lets
you continue the execution or quit the simulator.
</p>

<center>
<img src="/images/bochs-panic.png" alt="Bochs Panic Message" />
</center>

<p>
You can also use BX_ERROR, BX_INFO, and BX_DEBUG, which correspond to Bochs'
different log levels. You can
<a href="http://bochs.sourceforge.net/doc/docbook/user/bochsrc.html#BOCHSOPT-LOG">
set the log level and log file in the bochsrc</a>. By default, logs are sent to
standard output.
</p>

<p>
With the change made, re-compile Bochs and see if the VM will boot.
</p>

<pre>
$ ./my_configure.sh   # (You made this in the "Compiling Bochs" section.)
$ make -j 4
$ make install
$ cd my_build/bin
# ./bochs -f bochsrc -q
</pre>

<p>
Surprisingly, the VM boots successfully (at least for me) despite the broken XOR
instruction getting executed hundreds of times. You may have different results.
</p>

<p>
This was a simple (and rather dumb) example of how to modify an instruction in
Bochs. Revert the instruction's code back to the way it was, re-compile Bochs,
then in the next section, we will add a whole new instruction to the CPU.
</p>

<h3>Adding Registers and Instructions</h3>

<p>
In this example, we'll add a new register to the CPU, along with two new
instructions for moving its value in and out of the EAX register. We'll call the
new register "ENX" (N for "new"), and the two new instructions will be:
</p>

<ul>
    <li>RDENX: Copy the value of the ENX register into the EAX register.</li>
    <li>WRENX: Copy the value of the EAX register into the ENX register.</li>
</ul>

<h4>Adding the ENX Register</h4>

<p>
Adding a general purpose register to Bochs is easy. To do so, open cpu.h and
find:
</p>

<pre>
#if BX_SUPPORT_X86_64
# define BX_GENERAL_REGISTERS 16
#else
# define BX_GENERAL_REGISTERS 8
#endif
</pre>

<p>
This is the number of general purpose registers. Change it to:
</p>

<pre>
#if BX_SUPPORT_X86_64
# define BX_GENERAL_REGISTERS 17
#else
# define BX_GENERAL_REGISTERS 8
#endif
</pre>

<p>
Next, find the register number definitions:
</p>

<pre>
#define BX_32BIT_REG_EAX 0
#define BX_32BIT_REG_ECX 1
#define BX_32BIT_REG_EDX 2
#define BX_32BIT_REG_EBX 3
...
</pre>

<p>
Add the following to name the new register:
</p>

<pre>
#define BX_32BIT_REG_ENX 8
#define BX_64BIT_REG_RNX 16
</pre>

<p>
That's all you have to do to add a general purpose register. If you want to add
some CPU-specific storage that isn't a register (e.g. an internal buffer or
cache), you can just add it as a member variable to the CPU class in cpu.h
</p>

<h4>Adding the RDENX and WRENX Instructions</h4>

<p>
Next, we'll add two 32-bit instructions that use our new RNX register.
</p>

<p>
First, we need to choose opcodes for our new instructions. We'll use 0F3B and
0F3C, since they are currently undefined for Intel CPUs:
</p>

<ul>
    <li>0F 3B: RDENX</li>
    <li>0F 3C: WRENX</li>
</ul>

<p>
To add these to the CPU, first add the implementations. Add the function
prototypes to cpu.h alongside the others (search for "BX_INSF_TYPE NOP").
</p>

<pre>
BX_SMF BX_INSF_TYPE RDENX(bxInstruction_c *) BX_CPP_AttrRegparmN(1);
BX_SMF BX_INSF_TYPE WRENX(bxInstruction_c *) BX_CPP_AttrRegparmN(1);
</pre>

<p>
Add the implementations to data_xfer32.cc:
</p>

<pre>
BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::RDENX(bxInstruction_c *i)
{
    // Copy ENX into EAX.
    Bit32u enx = get_reg32(BX_32BIT_REG_ENX);
    set_reg32(BX_32BIT_REG_EAX, enx);

    BX_NEXT_INSTR(i);
}

BX_INSF_TYPE BX_CPP_AttrRegparmN(1) BX_CPU_C::WRENX(bxInstruction_c *i)
{
    // Copy EAX into ENX.
    Bit32u eax = get_reg32(BX_32BIT_REG_EAX);
    set_reg32(BX_32BIT_REG_ENX, eax);

    BX_NEXT_INSTR(i);
}
</pre>

<p>
Next, add this to ia_opcodes.h.
</p>

<pre>
bx_define_opcode(BX_IA_RDENX, &amp;BX_CPU_C::RDENX, &amp;BX_CPU_C::RDENX, 0, BX_SRC_NONE, BX_SRC_NONE, BX_SRC_NONE, BX_SRC_NONE, 0)
bx_define_opcode(BX_IA_WRENX, &amp;BX_CPU_C::WRENX, &amp;BX_CPU_C::WRENX, 0, BX_SRC_NONE, BX_SRC_NONE, BX_SRC_NONE, BX_SRC_NONE, 0)
</pre>

<p>
Now, in fetchdecode.cc, replace <b>both occurrences</b> of:
</p>

<pre>
/* 0F 3B /w */ { 0, BX_IA_ERROR },
/* 0F 3C /w */ { 0, BX_IA_ERROR },
</pre>

<p>
with
</p>

<pre>
/* 0F 3B /w */ { 0, BX_IA_RDENX },
/* 0F 3C /w */ { 0, BX_IA_WRENX },
</pre>

<p>
<strong>Note:</strong> If you're adding an instruction that needs a ModR/M byte
(our RDENX and WRENX examples don't), you have to update the BxOpcodeHasModrm32
table.
</p>

<p>
That's it. These instructions should work now.
</p>

<h4>Testing the New Instructions</h4>

<p>
We just finished adding a new register and new instructions to Bochs. Compile
Bochs (see the previous sections if you've forgotten how), then we'll write
a program to test the new instructions.
</p>

<p>
Here's a program written in GNU assembly language to test the new instructions
and register: 
</p>

<pre>
.intel_syntax noprefix
.global main
.text

# If the new instructions work, this program should print:
# &quot;EAX is 1337!&quot;

main:
&nbsp;&nbsp; &nbsp;# Put 1337 into EAX
&nbsp;&nbsp; &nbsp;mov &nbsp; &nbsp; eax, 1337
&nbsp;&nbsp; &nbsp;# WRENX: Copy EAX&#039;s value (1337) into ENX
&nbsp;&nbsp; &nbsp;.byte 0x0F, 0x3C
&nbsp;&nbsp; &nbsp;# Zero EAX
&nbsp;&nbsp; &nbsp;mov &nbsp; &nbsp; eax, 0
&nbsp;&nbsp; &nbsp;# RDENX: Copy ENX&#039;s value (1337) into EAX.
&nbsp;&nbsp; &nbsp;.byte 0x0F, 0x3B

&nbsp;&nbsp; &nbsp;# Push EAX onto the stack (second argument to printf)
&nbsp;&nbsp; &nbsp;push &nbsp; &nbsp;eax

&nbsp;&nbsp; &nbsp;# Push the format string&#039;s address onto the stack (first arg to printf).
&nbsp;&nbsp; &nbsp;lea &nbsp; &nbsp; eax, format
&nbsp;&nbsp; &nbsp;push &nbsp; &nbsp;eax

&nbsp;&nbsp; &nbsp;# Call printf.
&nbsp;&nbsp; &nbsp;call &nbsp; &nbsp;printf

&nbsp;&nbsp; &nbsp;# Clean up the arguments on the stack.
&nbsp;&nbsp; &nbsp;add &nbsp; &nbsp; esp, 8

&nbsp;&nbsp; &nbsp;# Return 0 from main().
&nbsp;&nbsp; &nbsp;mov &nbsp; &nbsp; eax, 0
&nbsp;&nbsp; &nbsp;ret

format:
&nbsp;&nbsp; &nbsp;.asciz &quot;EAX is %d!\n&quot;
</pre>

<p>
Compile it with:
</p>

<pre>
gcc -m32 test.s -o test
</pre>

<p>
Copy the test binary into the VM. Put it in the root of the disk image and it
will show up in /mnt/sda1/test. Start Bochs and run it. You should get "EAX is
1337!" proving that the new register and instructions work.
</p>

<center>
<img src="/images/bochs-instructions-working.png" alt="Bochs Panic Message" />
</center>

<h3>Useful Functions</h3>

<p>
Here are some functions that might come in handy while writing Bochs
instructions. There many functions that are not listed here. Look for them in
the implementations of other instructions.
</p>

<ul>
    <li>
        <p><strong>Bit32u get_reg32(unsigned reg)</strong></p>
        <p>
            Returns the value of a 32-bit register. The 'reg' argument can be
            one of BX_32BIT_REG_EAX, BX_32BIT_REG_EBX, BX_32BIT_REG_ECX, etc.
        </p>
    </li>
    <li>
        <p><strong>void set_reg32(unsigned reg, Bit32u val)</strong></p>

        <p>
            Sets the value of a 32-bit register to 'val.' The 'reg' parameter
            accepts the same values as get_reg32's 'reg' parameter (see above).
        </p>
    </li>
    <li>
        <p><strong>void push_32(Bit32u value32)</strong></p>

        <p>
            Pushes a 32-bit value onto the stack.
        </p>
    </li>
    <li>
        <p><strong>Bit32u pop_32(void)</strong></p>

        <p>
            Pops a 32-bit value off of the stack.
        </p>
    </li>
    <li>
        <p><strong>void write_virtual_dword(unsigned seg, Bit32u (or Bit64u) offset, Bit32u data)</strong></p>

        <p>
            Writes a 32-bit value to memory. The 'seg' parameter specifies the
            segment, e.g. BX_SEG_REG_DS. The 'offset' parameter is the virtual
            address. The 'data' parameter is the 32-bit value to write at the
            address. 
        </p>
    </li>
    <li>
        <p><strong>Bit32u read_virtual_dword(unsigned seg, (or Bit64u) Bit32u offset)</strong></p>

        <p>
            Reads a 32-bit value from memory. The 'seg' specifies the segment,
            e.g. BX_SEG_REG_DS. The 'offset' parameter is the virtual address.
        </p>
    </li>
</ul>

<h2>Conclusion</h2>

<p>
If you followed this guide successfully, you should know how to get a (possibly
modified) modern Linux kernel running in Bochs, as well as have some idea about
how to experiment with changing the x86 architecture. Where you go from here is
up to you. The possibilities for research and learning are almost endless.
Certainly you can think of something clever or fun to do with your new skills!
</p>

<p>
If you have suggestions or questions related to this guide, please don't
hesitate to <a href="/contact.htm">contact me</a>.
</p>
