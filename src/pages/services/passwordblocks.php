<!-- Load the stanford javascript cryto library -->
<script language="javascript" src="/js/sjcl.js"></script>
<script language="javascript" src="/js/csprng-extensions.js"></script>

<script language="javascript">

// Entropy provided by web server RNG
sjcl.random.addEntropy("<?php echo bin2hex(mcrypt_create_iv(1024, MCRYPT_DEV_URANDOM));?>", 0);

var allBlocks = [];
var previewBlock = null;
var blockCount = 0;
var selectedIndex = null;

function PasswordBlock(text, entropy)
{
    this.text = text;
    this.entropy = entropy;
    this.selected = false;
    this.blockID = blockCount++;

    this.getElement = function(addEvent)
    {
        var div1 = document.createElement('span');
        var text = document.createTextNode(this.text);
        div1.id = "block" + this.blockID;
        if(addEvent)
            div1.onclick = function() { blockClicked(this); };
        if(this.selected)
        {
            div1.className = 'selectedBlock';
        }
        else
        {
            div1.className = 'unselectedBlock';
        }
        div1.appendChild(text);
        return div1;
    }

    this.copy = function()
    {
        var newBlock = new PasswordBlock(this.text, this.entropy)
        newBlock.selected = this.selected;
        return newBlock;
    }
}

function blockClicked(sender)
{
    for(var i = 0; i < allBlocks.length; i++)
    {
        var block = allBlocks[i];
        if("block" + block.blockID == sender.id)
        {
            block.selected = true;
            selectedIndex = i;
        }
        else
        {
            block.selected = false;
        }
    }
    updateBlockView();
}

function updateBlockView()
{
    blockView = document.getElementById("blockview");
    while(blockView.firstChild)
        blockView.removeChild(blockView.firstChild);
    for(var i = 0; i < allBlocks.length; i++)
    {
        toShow = allBlocks[i];
        blockView.appendChild(toShow.getElement(true));

        // Space to allow word-wrapping
        blockView.appendChild(document.createTextNode(" "));
    }
}

function addPreviewBlockToPassword()
{
    allBlocks.push(previewBlock.copy());
    updateBlockView();
}

function setPreviewBlock(theBlock)
{
    block = document.getElementById("previewBlock");
    removeAllChildNodes(block);
    if(theBlock == null)
    {
        previewBlock = null;
        document.getElementById("blockpreview").style.display = "none";
    }
    else
    {
        previewBlock = theBlock;
        disp = theBlock.getElement();
        document.getElementById("previewBlock").appendChild(disp);
        document.getElementById("blockpreview").style.display = "block";
    }
}

function generateRandomBlock()
{
    var charSet = "";
    if(document.getElementById("lowletters").checked)
        charSet += "abcdefghijklmnopqrstuvwxyz";
    if(document.getElementById("upletters").checked)
        charSet += "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if(document.getElementById("numbers").checked)
        charSet += "0123456789";
    if(document.getElementById("symbols").checked)
        charSet += "~`!@#$%^&*()_+-={}[]|\\:;\"'<,>.?/";
    //FIXME: Ensure that CharSet (not just customchars) are all unique chars
    charSet += document.getElementById("customchars").value;
    if(charSet === "")
    {
        alert('Please select one or more character types!');
    }
    else
    {
        length = parseInt(document.getElementById("randblocklength").value);
        if(!isNaN(length) && length > 0 && length <= 100)
        {
            block = secureRandomString(length, charSet);
            entropy = length * Math.log(charSet.length) / Math.log(2);
            newBlock = new PasswordBlock(block, entropy); //FIXME
            setPreviewBlock(newBlock);
        }
        else
        {
            alert('Please provide a valid block length between 1 and 100.');
        }
    }
}

function removeAllChildNodes(obj)
{
    if(obj.hasChildNodes())
    {
        while(obj.childNodes.length > 0)
        {
            obj.removeChild(obj.firstChild);
        }
    }
}

function shiftLeft()
{
    if(selectedIndex != null)
    {
        if(selectedIndex > 0)
        {
            var tmp = allBlocks[selectedIndex - 1];
            allBlocks[selectedIndex - 1] = allBlocks[selectedIndex];
            allBlocks[selectedIndex] = tmp;
            selectedIndex--;
        }
    }
    updateBlockView();
}

function shiftRight()
{
    if(selectedIndex != null)
    {
        if(selectedIndex < allBlocks.length - 1)
        {
            var tmp = allBlocks[selectedIndex + 1];
            allBlocks[selectedIndex + 1] = allBlocks[selectedIndex];
            allBlocks[selectedIndex] = tmp;
            selectedIndex++;
        }
    }
    updateBlockView();
}

function shuffle()
{
    for(var i = allBlocks.length - 1; i >= 1; i--)
    {
        var j = secureRandom(0, i);
        tmp = allBlocks[i];
        allBlocks[i] = allBlocks[j];
        allBlocks[j] = tmp;
    }
    updateBlockView();
}

function deleteSelected()
{
    if(selectedIndex != null)
    {
        allBlocks.splice(selectedIndex, 1);
        selectedIndex = null;
    }
    updateBlockView();
}

// ================ FACTORY LINKS ================

function showFactory(id)
{
    document.getElementById("randomfactory").style.display = "none";
    document.getElementById("wordfactory").style.display = "none";
    document.getElementById("paddingfactory").style.display = "none";
    document.getElementById("customfactory").style.display = "none";

    document.getElementById("randomfactorylink").style.backgroundColor = "#FFFFFF";
    document.getElementById("wordfactorylink").style.backgroundColor = "#FFFFFF";
    document.getElementById("paddingfactorylink").style.backgroundColor = "#FFFFFF";
    document.getElementById("customfactorylink").style.backgroundColor = "#FFFFFF";
    
    document.getElementById(id).style.display = "block";
    document.getElementById(id + "link").style.backgroundColor = "#CCCCCC";
}

function randomFactory()
{
    showFactory("randomfactory");
}

function wordFactory()
{
    showFactory("wordfactory");
}

function paddingFactory()
{
    showFactory("paddingfactory");
}

function customFactory()
{
    showFactory("customfactory");
}

</script>

<h1>Password Building Blocks</h1>

<noscript>
<div style="background-color: #ffcfcf; border: solid #700000 5px; margin: 20px; padding:10px;">
<center><span style="font-size:20px;">Please enable JavaScript!</span></center>
<p>
    To use this password generator, you will need to enable JavaScript in your web browser. I understand that most security concious users, like myself, browse with JavaScript disabled, but the Password Building Blocks page really <em>does</em> need JavaScript to function.
</p>
</div>
</noscript>

<p style="text-align:center;"><span style="font-size: 20px;">Sit down, relax, take a deep breath... It's time to make a password!</span></p>

<p>You're only minutes away from having an extremely secure but highly memorable password. Just follow <strong>3 easy steps...</strong></p>

<h2>Step 1: Create Building Blocks</h2>
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
                <input type="text" id="customchars" size="20" />
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
   WERD
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

<h2>How Does it Work?</h2>

<p>Make a note that this teaches people that passwords can be memorized in MUSCLE memory, they don't actually have to recall the password.</p>

show password security in arrange building blocks

<pre>
 - do BLIND practice: black out the password everywhere on the screen
 - give them a paragraph of text to read to see if they'll forget their password
 - remember random LEET for words
</pre>
