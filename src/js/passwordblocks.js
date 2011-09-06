"use strict";
//TODO: -- JS RNG: show 200 randomly colored circles and make them click them

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

    this.getStarredElement = function(addEvent)
    {
        var table = document.createElement('table');
        table.className = "starblock";

        var tBody = document.createElement('tbody');
        table.appendChild(tBody);

        var topRow = tBody.insertRow(0);
        var topRowCell = topRow.insertCell(-1);
        topRowCell.style.textAlign = "center";

        var bottomRow = tBody.insertRow(1);
        var bottomRowCell = bottomRow.insertCell(-1);

        var elem = this.getElement(addEvent);
        bottomRowCell.appendChild(elem);
    
        var numEightBits = Math.round(this.entropy / 8);
        var stars = Math.floor(numEightBits / 2);

        for(var i = 0; i < stars; i++)
        {
            var starImg = document.createElement('img');
            starImg.src = "/images/passwordblocks/star.gif";
            topRowCell.appendChild(starImg);
        }

        if(numEightBits % 2 == 1)
        {
            var halfStarImg = document.createElement('img');
            halfStarImg.src = "/images/passwordblocks/halfstar.gif";
            topRowCell.appendChild(halfStarImg);
        }

        return table;
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
    var blockView = document.getElementById("blockview");
    while(blockView.firstChild)
        blockView.removeChild(blockView.firstChild);
    for(var i = 0; i < allBlocks.length; i++)
    {
        var toShow = allBlocks[i];
        blockView.appendChild(toShow.getStarredElement(true));

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
    var block = document.getElementById("previewBlock");
    removeAllChildNodes(block);
    if(theBlock == null)
    {
        previewBlock = null;
        document.getElementById("blockpreview").style.display = "none";
    }
    else
    {
        previewBlock = theBlock;
        var disp = theBlock.getStarredElement();
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
    charSet += document.getElementById("customchars").value;
    if(charSet === "")
    {
        alert('Please select one or more character types!');
    }
    else
    {
        var length = parseInt(document.getElementById("randblocklength").value);
        charSet = uniq(charSet);
        if(!isNaN(length) && length > 0 && length <= 64)
        {
            var block = secureRandomString(length, charSet);
            var entropy = length * Math.log(charSet.length) / Math.log(2);
            var newBlock = new PasswordBlock(block, entropy); 
            setPreviewBlock(newBlock);
        }
        else
        {
            alert('Please provide a valid block length between 1 and 64.');
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

function hexcustom()
{
    document.getElementById("lowletters").checked = false;
    document.getElementById("upletters").checked = false;
    document.getElementById("numbers").checked = false;
    document.getElementById("symbols").checked = false;
    document.getElementById("customchars").value = "0123456789ABCDEF";
}

function generateWordBlock()
{
    var idx = secureRandom(0, ENG_WORDLIST.length);
    var word = ENG_WORDLIST[idx];
    if(document.getElementById("langreverse").checked)
    {
        word = word.split('').reverse().join('');
    }
    if(document.getElementById("langleet").checked)
    {
        word = makeWordLeet(word);
    }
    var entropy = Math.log(ENG_WORDLIST.length) / Math.log(2);
    var newBlock = new PasswordBlock(word, entropy); 
    setPreviewBlock(newBlock);
}
