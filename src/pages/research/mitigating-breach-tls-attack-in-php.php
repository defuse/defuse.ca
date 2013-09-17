<?php
    Upvote::render_arrows(
        "breachmitigate",
        "defuse_pages",
        "Mitigating BREACH in PHP",
        "PHP code for mitigating the BREACH attack.",
        "https://defuse.ca/mitigating-breach-tls-attack-in-php.htm"
    );
?>
<div class="pagedate">
August 9, 2013
</div>
<h1>Mitigating the BREACH Attack in PHP</h1>

<p>
The
<a href="https://media.blackhat.com/us-13/US-13-Prado-SSL-Gone-in-30-seconds-A-BREACH-beyond-CRIME-WP.pdf">BREACH attack</a>
takes advantage of HTTP compression to extract secrets from web pages delivered
over SSL/TLS. The following PHP code is provided to help mitigate the attack
until a better solution is found. It is an implementation of the technique
discussed in the "Masking Secrets" section (3.4) of the BREACH paper. It also
provides an experimental function called <tt>breach_visual_html()</tt> that might allow
for easy protection of secrets that need to be displayed to the user.
</p>

<div style="text-align: center;">
<strong><a href="/source/breach.php">Download breach.php</a></strong>
</div>

<?php
    printSourceFile("source/breach.php");
?>

<h2>Sample Output</h2>

<p>Here's some <tt>breach_visual_html()</tt> output (view source):</p>

<div style="padding-left: 30px;">
<?php require_once('libs/breach.php'); ?>
<h2><?php echo breach_visual_html("Sample Header"); ?></h2>
<p><?php echo breach_visual_html("Sample paragraph text."); ?></p>
</div>
