<?php
require_once( 'libs/HtmlEscape.php' );
require_once( 'libs/Assembler.php' );

$x86checked = "";
$x64checked = "";
$archName = "";

// Whitelist the architecture type (either x86 or x64).
if (isset($_POST['arch']))
{
    switch ($_POST['arch'])
    {
    case "x86":
        $archName = "x86";
        $x86checked = "checked=checked";
        break;
    case "x64":
        $archName = "x64";
        $x64checked = "checked=checked";
        break;
    default:
        $x86checked = "checked=checked";
        $archName = "x86";
    }
}
else
{
    $x86checked = "checked=checked";
}
?>

<?php
    Upvote::render_arrows(
        "onlinex86assembler",
        "defuse_pages",
        "Online x86 / x64 Assembler",
        "Assemble x86 and x64 code in your browser!",
        "https://defuse.ca/online-x86-assembler.htm"
    );
?>
<h1>Online x86 / x64 Assembler</h1>

<p>
This tool takes some x86 or x64 assembly instructions and converts them to their
binary representation (machine code).  It uses GCC (AS) to assemble the code you
give it and objdump to disassemble the resulting object file, so you can see
which bytes correspond to which instructions.
</p>

<p><strong>Enter your assembly code</strong> (intel syntax):</p>

<form action="/online-x86-assembler.htm#disassembly" method="post">
    <textarea 
        name="instructions"
        rows="15" cols="80"
        style="color: black; background-color: white; border: dashed 1px black; width: 100%;"
    ><?php
        // Refill the textbox with their code so they don't have to re-type it.
        if (isset($_POST['instructions']))
        {
            echo htmlentities($_POST['instructions'], ENT_QUOTES);
        }
    ?></textarea>
    <p style="text-align: right;">
        Architecture:
        <input type="radio" name="arch" value="x86" <?php echo $x86checked; ?> /> x86
        <input type="radio" name="arch" value="x64" <?php echo $x64checked; ?> /> x64
        <input type="submit" name="submit" value="Assemble" />
    </p>
</form>

<?php

if (isset($_POST['submit']) && isset($_POST['instructions']) && strlen($_POST['instructions']) != 0)
{
    // Anchor so we can move the user's view right to the results.
    echo '<a name="disassembly"></a>';

    $instructions = $_POST['instructions'];

    try {
        $assembler = new Assembler();
        $assembler->setArch($archName);
        $result = $assembler->assemble($instructions);
        printAssemblyResults($result);
    } catch (InvalidModeException $ex) {
        printError("Something went wrong.");
    } catch (UnsafeCodeException $ex) {
        printError(
            "Sorry, your input is too big or contains unsafe directives! \n" .
            "The period (.) character must not appear anywhere in your source code."
        );
    } catch (AssemblyFailureException $ex) {
        printError($ex->getMessage());
    }
}

function printAssemblyResults($results)
{
    $safe_justBytes = htmlentities($results['hex_zero_bold'], ENT_QUOTES);
    $safe_byteString = htmlentities($results['string'], ENT_QUOTES);
    $safe_arrayDef = htmlentities($results['array'], ENT_QUOTES);
    $safe_code = HtmlEscape::escapeText($results['code'], true, 4);
    ?>
        <div style="padding: 10px;">
        <div style="font-size: 16pt; padding-bottom: 10px;">Assembly:</div>
            <div style="font-family: monospace;">
                <p><b>Raw Hex</b> (zero bytes in bold):</p>
                    <p><?php echo $safe_justBytes; ?>&nbsp;&nbsp;&nbsp;</p>
                <p><b>String Constant:</b></p>
                    <p>&quot;<?php echo $safe_byteString; ?>&quot;</p>
                <p><b>Array Constant:</b> </p>
                <p>
                <?php echo $safe_arrayDef; ?>
                </p>
            </div>
        <div style="font-size: 16pt; padding-bottom: 10px; padding-top: 10px;">Disassembly:</div>
            <div style="font-family: monospace;">
            <p>
                <?php echo $safe_code; ?>
            </p>
            </div>
        </div>
    <?
}

function printError($output)
{
    $safe_output = HtmlEscape::escapeText($output, true, 4);
    ?>
        <div style="background-color: #FFCCCC; border: solid red 1px; padding: 10px;">
        <div style="font-family: monospace;">
            <?php echo $safe_output; ?>
        </div>
        </div>
    <?
}

?>
