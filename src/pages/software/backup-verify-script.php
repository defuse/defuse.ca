<?php
    Upvote::render_arrows(
        "backupverifyscript",
        "defuse_pages",
        "Backup Verifier Script (Ruby)",
        "A Ruby script that compares two directories and reports the differences.",
        "https://defuse.ca/backup-verify-script.htm"
    );
?>
<h1>[Ruby] Backup Verifier Script</h1>

<p>
The following ruby script compares two directories recursively, and alerts the
user of any differences.  It compares files by size and (optionally) by a random
sample of contents. The results are summarized into a difference percentage so
it can be used to easily determine if a backup is valid and recent.
</p>

<h2>Usage</h2>

<div class="code">
Usage: /usr/bin/vfy [options] &lt;original&gt; &lt;backup&gt;<br />
&nbsp;&nbsp; &nbsp;-v, --verbose &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Print what is being done<br />
&nbsp;&nbsp; &nbsp;-m, --machine &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Output summary in machine-readable format<br />
&nbsp;&nbsp; &nbsp;-f, --[no-]follow &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Follow symlinks<br />
&nbsp;&nbsp; &nbsp;-x, --one-filesystem &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Stay on one file system (in &lt;original&gt;)<br />
&nbsp;&nbsp; &nbsp;-c, --count &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Count files in unmatched directories<br />
&nbsp;&nbsp; &nbsp;-i, --ignore DIR &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Don&#039;t process DIR<br />
&nbsp;&nbsp; &nbsp;-s, --samples COUNT &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Comparison sample count (default: 0)<br />
&nbsp;&nbsp; &nbsp;-h, --help &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Display this screen<br />
</div>

<h2>Sample Summary Output</h2>

<div class="code">
SUMMARY:<br />
&nbsp;&nbsp; &nbsp;Items processed: 457382<br />
&nbsp;&nbsp; &nbsp;Differences: 1183 (0.26%)<br />
&nbsp;&nbsp; &nbsp;Similarities: 456199<br />
&nbsp;&nbsp; &nbsp;Skipped: 635<br />
&nbsp;&nbsp; &nbsp;Errors: 1<br />
</div>

<h2>Script</h2>

<div style="text-align: center; font-weight: bold; padding-bottom: 20px;">
    <a href="/source/vfy.rb">Download vfy.rb</a>
</div>

<?php
    printSourceFile("source/vfy.rb", true);
?>
