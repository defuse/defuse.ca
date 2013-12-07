<div class="pagedate">
December 6, 2013
</div>
<h1>Bochs Disk Images</h1>

<p>
These disk images are for use with the <a
href="http://bochs.sourceforge.net/">Bochs x86 Emulator</a>. I am providing them
to make it easier to get started with hacking Bochs. All of them were built and
tested using Bochs 2.6.2 compiled with the following configure options:
</p>

<pre>
./configure \
  --enable-smp \
  --enable-x86-64 \
  --enable-all-optimizations \
  --enable-long-phy-address \
  --enable-configurable-msrs \
  --enable-disasm \
  --enable-fpu \
  --enable-alignment-check \
  --enable-3dnow \
  --enable-svm \
  --enable-vmx=2 \
  --enable-avx \
  --enable-a20-pin \
  --enable-pci \
  --enable-clgd54xx \
  --enable-voodoo \
  --enable-usb \
  --enable-usb-ohci \
  --enable-usb-xhci \
  --enable-cdrom \
  --enable-sb16 \
  --enable-es1370 \
  --enable-show-ips \
  --with-all-libs
</pre>

<h2>Tiny Core Linux (CorePlus) 5.1 (32-bit)</h2>

<p>
Tiny Core Linux is a very small Linux distribution with a recent kernel
(3.8.13). It is very good for testing hardware/kernel mods. See the project's
Wiki for instructions on
<a href="http://wiki.tinycorelinux.net/wiki:custom_kernel">
    compiling a custom kernel
</a>.
</p>

<center>
<a href="/files/CorePlus.img.xz"><strong>Download CorePlus.img.xz (15MiB)</strong></a>
</center>

<h3>Tiny Core Bochs Configuration</h3>

<pre>
# System configuration.
romimage: file=$BXSHARE/BIOS-bochs-latest
vgaromimage: file=$BXSHARE/VGABIOS-lgpl-latest
cpu: model=corei7_ivy_bridge_3770k, ips=120000000
clock: sync=slowdown
megs: 128
boot: disk

# Hard disks.
ata0: enabled=1, ioaddr1=0x1f0, ioaddr2=0x3f0, irq=14
ata0-master: type=disk, path=&quot;CorePlus.img&quot;, cylinders=406, heads=8, spt=63
</pre>

<h2>Debian Wheezy (32-bit)</h2>

<p>
This is the current stable release of the Debian Linux distribution. No
windowing system is installed, since that is too much for Bochs to handle. It
just boots to a shell.
</p>

<center>
<a href="/files/DebianWheezy.img.xz"><strong>Download DebianWheezy.img.xz (172MiB)</strong></a>
</center>

<h3>Default Users</h3>

<pre>
Username: root
Password: toor

Username: bochs
Password: bochs
</pre>

<h3>Debian Wheezy Bochs Configuration</h3>

<pre>
# System configuration.
romimage: file=$BXSHARE/BIOS-bochs-latest
vgaromimage: file=$BXSHARE/VGABIOS-lgpl-latest
cpu: model=corei7_ivy_bridge_3770k, ips=120000000
clock: sync=slowdown
megs: 1024
boot: disk

# Hard disks.
ata0: enabled=1, ioaddr1=0x1f0, ioaddr2=0x3f0, irq=14
ata0-master: type=disk, path=&quot;DebianWheezy.img&quot;, cylinders=4161, heads=8, spt=63
</pre>
