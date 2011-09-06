<!-- TODO: Randomize the defaults (or most secure=default) so attacks can't be optimized to the default configuration --> 

<!-- Seed the RNG with random data from defuse.ca -->
<script type="text/javascript" language="javascript">
    sjcl.random.addEntropy("<?php echo bin2hex(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));?>", 0);
</script>

<!-- Load the stanford javascript cryto library, and our extensions to it. -->
<script type="text/javascript" language="javascript" src="/js/sjcl.js"></script>
<script type="text/javascript" language="javascript" src="/js/leet.js"></script>
<script type="text/javascript" language="javascript" src="/js/csprng-extensions.js"></script>

<!-- Load the password blocks functionality script -->
<script type="text/javascript" language="javascript" src="/js/wordlist.js"></script>
<script type="text/javascript" language="javascript" src="/js/passwordblocks.js"></script>

<h1>Password Building Blocks</h1>

<noscript>
<div style="background-color: #ffcfcf; border: solid #700000 5px; margin: 20px; padding:10px;">
<center><span style="font-size:20px;">Please enable JavaScript!</span></center>
<p>
    To use this password generator, you will need to enable JavaScript in your web browser. I understand that most security concious users, like myself, browse with JavaScript disabled, but the Password Building Blocks page really <em>does</em> need JavaScript to work.
</p>
</div>
</noscript>

<p style="text-align:center;"><span style="font-size: 20px;">Sit down, relax, take a deep breath... It's time to make a password!</span></p>

<h2>Step 1: Create Building Blocks</h2>

<div style="background-color: #cee3ff; border: solid #00439d 1px; padding: 5px; margin: 10px; width: 700px; margin: 0 auto; text-align: center;">
<strong>TIP:</strong> Move your mouse around the page to add extra randomness to the random number generator.
</div>

<br />

<div id="blockfactory" class="blocksection">
    <div id="ftheader">
        <span class="ftlink" id="randomfactorylink" style="background-color: #CCCCCC;" onclick="randomFactory();">Random</span>
        <span class="ftlink" id="wordfactorylink" onclick="wordFactory();">Word</span>
        <span class="ftlink" id="paddingfactorylink" onclick="paddingFactory();">Padding</span>
        <span class="ftlink" id="customfactorylink" onclick="customFactory();">Custom</span>
    </div>
    <div id="randomfactory" class="typefactory" style="display:block;">
        <table cellspacing="10">
        <tr>
            <td><strong>Include:</strong></td>
            <td>
                    <input type="checkbox" id="lowletters" checked="checked"><label for="lowletters">Lowercase letters</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="upletters" checked="checked"><label for="upletters">Uppercase letters</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="numbers" checked="checked"><label for="numbers">Numbers</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="symbols" checked="checked"><label for="symbols">Symbols</label>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
        <tr>
            <td><strong>Custom Characters:</strong></td>
            <td>
                <input type="text" id="customchars" size="20" /> <span class="fakelink" onclick="hexcustom();">Hex</span>
            </td>
        </tr>
        <tr>
            <td><strong>Length:</strong></td>
            <td>
                <input type="text"id="randblocklength" size="3" value="5" />
            </td>
        </tr>
        </table>
        <center><input type="button" value="Generate Block" style="width: 300px;" onclick="generateRandomBlock();" /></center>
    </div>
    <div id="wordfactory" class="typefactory">
        <table cellspacing="10">
            <tr>
                <td><strong>Language:</strong></td>
                <td>
                        <input type="checkbox" id="langenglish" checked="checked"><label for="langenglish">English</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" id="langfrench" ><label for="langfrench">French</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" id="langspanish" ><label for="langspanish">Spanish</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" id="langlatin" ><label for="langlatin">Latin</label>&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>
            <tr>
                <td><strong>Extras:</strong></td>
                <td>
                    <input type="checkbox" id="langcase" checked="checked"><label for="langcase">Random Case</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="langleet" checked="checked"><label for="langleet">L33T</label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="langreverse" ><label for="langreverse">Reverse</label>&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>
        </table>
        <center><input type="button" value="Generate Block" style="width: 300px;" onclick="generateWordBlock();" /></center>
    </div>
    <div id="paddingfactory" class="typefactory">
    padding
    </div>
    <div id="customfactory" class="typefactory">
    custom
    </div>


    <div id="blockpreview">
            <div id="previewBlock"></div><br />
            <input type="button" value="Add \/" onclick="addPreviewBlockToPassword();"/>
    </div>
</div>
<h2>Step 2: Arrange Building Blocks</h2>
<div style="background-color: #cee3ff; border: solid #00439d 1px; padding: 5px; margin: 10px; width: 700px; margin: 0 auto; text-align: center;">
<strong>TIP:</strong> Get 5 stars for an ultra-secure password.
</div>
<br />
<div id="blocksorter" class="blocksection">
<div id="blockview">
    <p>Please create password building blocks!</p>
</div>

<div style="text-align: center; padding: 20px;">
            <img src="/images/passwordblocks/leftarrow.gif" alt="Shift Left" title="Shift the selected block left" onclick="shiftLeft();" />
            <img src="/images/passwordblocks/shuffle.gif" alt="Shuffle" title="Shuffle all blocks"  style="margin-left: 20px; margin-right: 20px;" onclick="shuffle();"/>
            <img src="/images/passwordblocks/rightarrow.gif" alt="Shift Right" title="Shift the selected block right" onclick="shiftRight();" />
</div>

<input style="float: right;" type="button" value="Delete Block" onclick="deleteSelected();" />
<input style="float: right;" type="button" value="Clear All" />
<input type="button" value="Practice" />

</div>

<h2>Step 3: Practice Your Password</h2>
<div id="passwordpractice" class="blocksection">
    <p style="text-align: center;">Please arrange your building blocks into a password and send them to the practice arena.</p>
</div>

<h2>Technical Details</h2>

<p>Make a note that this teaches people that passwords can be memorized in MUSCLE memory, they don't actually have to recall the password.</p>

show password security in arrange building blocks

<pre>
 - do BLIND practice: black out the password everywhere on the screen
 - give them a paragraph of text to read to see if they'll forget their password
 - remember random LEET for words
</pre>
