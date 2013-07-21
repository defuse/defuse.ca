<?php
    Upvote::render_arrows(
        "xplanecombat",
        "defuse_pages",
        "Combat in X-Plane 10",
        "How to configure X-Plane 10 for combat.",
        "https://defuse.ca/x-plane-combat.htm"
    );
?>
<h1>Combat in X-Plane 10</h1>

<p>
This is a tutorial for people who have X-Plane 10 and want to battle AI enemies
with fighter jets.
</p>

<p>
How to do this is not obvious at all, and I can't find a guide on the Internet
that encompasses the entire setup. So I will try to explain everything here.
First, we go over how to find an aircraft with weapons, and how to install
weapons on an existing craft. Next, we enable enemy aircraft that try to shoot
you down. Finally, we configure the controls necessary for combat and describe
how to use them.
</p>

<h2>Installing Weapons</h2>

<p>
Most fighters will come with weapons attached, but some don't. Most notably, the
FA-22 does not have any weapons by default. A good jet that comes with weapons
is the <a href="http://forums.x-plane.org/index.php?app=downloads&showfile=10982"> Blue Angels FA-18F</a>.
</p>

<p>
To give an aircraft weapons:
</p>

<ol>
    <li>Open the aircraft in Plane Maker.</li>
    <li>Under the 'Expert' menu, select 'Default Weapons'.</li>
    <li>Select the empty checkbox to the left of the rightmost textbox.</li>
    <li>Select a default weapon for that slot.</li>
</ol>

<p>
If you want to experiment with creating your own weapons, go in 'Expert'
&gt; 'Build Weapons'. This is not necessary though, some weapons should already be
present in the default installation.
</p>

<p>
To check the status of the weapons while in X-Plane, select 'Aircraft' &gt;
'Weight and Fuel', then switch to the 'Ordinance' tab. If you run out of
ammo/missiles during combat, you can click 'Re-Arm to Default Specs' to
replenish without restarting the simulation.
</p>

<h2>Enemies</h2>

<p>
To add enemies, start X-Plane and go into 'Aircraft' &gt; 'Aircraft &amp;
Situations'. Switch to the 'Other Aircraft' tab. Change the 'number of aircraft'
setting so that there are as many extra aircraft as you want (that number
includes your own aircraft). Check the 'save all craft in preferences' box, so
that your aircraft selection is saved and not re-generated when you restart
X-Plane.
</p>

<p>
On the same tab, you should now see a list of aircraft. Select the aircraft you
want to fight against (you probably want something with weapons, so your enemies
can fight back). Assign yourself to a color team (e.g. blue), and assign all of
your enemies to a different team (e.g. red) by selecting the appropriate box
beside the aircraft.
</p>

<p>
The aircraft you just added will be present in the simulation every time it
starts. You can usually find them flying around not too far away from the runway
you take off from. In the next section, we'll explain how to use your radar to
find them.
</p>

<h2>Combat</h2>

<p>
In this section, we explain how to use the radar to locate and target enemy
aircraft, and how to select and fire weapons.
</p>

<h3>Necessary Bindings</h3>

<p>
To engage in combat, you will need to bind (at least) the following operations
to your joystick or keyboard:
</p>

<ul>
    <li>Weapon select up.</li>
    <li>Weapon select down.</li>
    <li>Target select up.</li>
    <li>Target select down.</li>
    <li>Fire all armed selections!</li>
</ul>

<h3>Weapon Select</h3>

<p>
Use the 'Weapon select up' and 'Weapon select down' keys to select a weapon.
Usually there is a knob in the cockpit that points to the selected weapon. Once
you have selected a weapon, press 'Fire all armed selections!' to fire.
</p>

<p>
The default FA-22 is a special case. It doesn't obey the weapon select keys. You
have to manually flip the switches beside the throttle to enable the guns and/or
missiles. The switches are not visible in the standard view. You have to press
'q' to look left.
</p>

<p>
To fire air-to-air missiles, you have to be targeting an aircraft. Targeting is
explained in the next section.
</p>

<h3>Radar and Targeting</h3>

<p>
Your jet should have a radar screen in the cockpit that shows you the relative
location of other aircraft. There is usually a knob that controls the range of
the radar, so that you can choose between a more-accurate view of nearby planes
and a less-accurate view of aircraft that are further away.
</p>

<p>
In most jets, selecting a weapon switches the radar to TCAS-only, so that only
other aircraft are shown (airports and other annoying things that you don't care
about in combat are not shown.).
</p>

<p>
When you can see other aircraft in your radar, press 'Target select up' and
'Target select down' to target them. Once you have an aircraft targeted, it will
be highlighted in red on the radar, and (in most jets), the HUD will draw a red
square around the target when it's in your field of view and a line pointing to
it when it isn't.
</p>

<h2>Visual Guide</h2>

<h3>FA-18F Cockpit</h3>

<img src="/images/F-18_18.png" alt="FA-18F X-Plane Cockpit" />

<h3>FA-22 Weapon Selection</h3>

<img src="/images/FA-22A_39.png" alt="FA-22A X-Plane Weapon Selection" />
