
<script language="javascript">
var RNG_BYTES = 1024;
var rngState = [];
for(var i = 0; i < RNG_BYTES; i++)
    rngState[i] = 0;
var rngOffset = 0;

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
    var newBlock = new PasswordBlock("azssss" + Math.floor(Math.random() * 20), 30);
    setPreviewBlock(newBlock);
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
        var j = Math.floor(Math.random() * (i+1)); //FIXME: Use crypto randomness
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

function rndseed(e)
{
    if(window.event)
        e = window.event;
    var x = e.clientX;
    var y = e.clientY;
    var time = (new Date()).getTime();
    addNumberToRngState(x);
    addNumberToRngState(y);
    addNumberToRngState(time);
}

function addNumberToRngState(n)
{
    //JavaScript stores integers as 53 bits.
    for(var i = 0; i < 7; i++)
    {
        rngState[rngOffset] = (rngState[rngOffset] + (n >> i * 8)) % 256;
        rngOffset++;
        if(rngOffset >= RNG_BYTES)
        {
            rngMix();
        }
    }
}

function rngMix()
{
    rngOffset = 0; //FIXME
}

</script>

<h1>Password Building Blocks</h1>

<noscript>
<h1>TODO: No JS Message!</h1>
</noscript>

<div id="passwordblocks">
<div id="blockfactory" class="blocksection">
<h2>Password Block Factory</h2>
<center><strong>Random | Custom | Padding</strong></center>
    <div id="randomfactory" class="typefactory">
        <table cellspacing="10">
        <tr>
            <td><strong>Include:</strong></td>
            <td>
                    <input type="checkbox" name="lowletters" checked="checked"> Lowercase letters&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" name="upletters" checked="checked"> Uppercase letters&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" name="numbers" checked="checked"> Numbers&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="checkbox" name="symbols" checked="checked"> Symbols&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
        <tr>
            <td><strong>Length:</strong></td>
            <td>
                <input type="text" size="3" value="5" />
            </td>
        </tr>
        </table>
        <center><input type="button" value="Generate Block" style="width: 300px;" onclick="generateRandomBlock();" /></center>
    </div>
    <div id="customfactory" class="typefactory">

    </div>
    <div id="paddingfactory" class="typefactory">

    </div>

    <div id="blockpreview">
            <div id="previewBlock"></div><br />
            <input type="button" value="Add \/" onclick="addPreviewBlockToPassword();"/>
    </div>
</div>

<div id="blocksorter" class="blocksection">
<h2>Password Block Sorter</h2>
<div id="blockview">
<span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> <span class="unselectedBlock">azssss0.49697726322815294</span> 
</div>

<div style="text-align: center; padding: 20px;">
            <img src="/images/passwordblocks/leftarrow.gif" alt="Shift Left" title="Shift the selected block left" onclick="shiftLeft();" />
            <img src="/images/passwordblocks/shuffle.gif" alt="Shuffle" title="Shuffle all blocks"  style="margin-left: 20px; margin-right: 20px;" onclick="shuffle();"/>
            <img src="/images/passwordblocks/rightarrow.gif" alt="Shift Right" title="Shift the selected block right" onclick="shiftRight();" />
</div>

<input style="float: right;" type="button" value="Delete Block" onclick="deleteSelected();" />
<input type="button" value="Practice" />

</div>

<div id="passwordpractice" class="blocksection">
<h2>Practice Your Password</h2>
</div>

<div id="passwordsecurity" class="blocksection">
<h2>Password Security Status</h2>
</div>

</div>

<p>Make a note that this teaches people that passwords can be memorized in MUSCLE memory, they don't actually have to recall the password.</p>
