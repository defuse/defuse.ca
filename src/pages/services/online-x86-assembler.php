<?php
require_once( 'libs/HtmlEscape.php' );

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

    // Make sure the input is a reasonable size and safe to compile.
    if (strlen($instructions) < 10 * 1024 && isSafeCode($instructions))
    {
        // Random (hopefully unique) temporary file names.
        $tempnam = "/tmp/" . rand();
        // WARNING: So that GCC doesn't pipe the file through CPP (processing 
        // macros, etc), the extension must be .s (lowercase) not .S (uppercase).
        $source_path = $tempnam . ".s";
        $obj_path = $tempnam . ".o";

        // Write the assembly source code.
        $asmfile = ".intel_syntax noprefix\n_main:\n" . $instructions . "\n";
        file_put_contents($source_path, $asmfile);

        $ret = 1;
        $output = array();

        $archTick = "-m32";
        if ($archName == "x64")
            $archTick = "-m64";

        // Assemble the source with gcc.
        exec("gcc $archTick -c $source_path -o $obj_path 2>&1", $output, $ret);

        if ($ret == 0)
        {
            // Use objdump to disassemble it.
            exec("objdump -M intel -d $obj_path", $output, $ret);

            if ($ret == 0)
            {
                $strout = implode("\n", $output);
                printAsm($strout);
            }
            else
            {
                printError("Something went wrong!");
            }
        }
        else
        {
            $strout = implode("\n", $output);
            $strout = preg_replace('/\\/tmp\\/\\d+\\.s:(\d+:|)\s*/', "", $strout);
            $strout = str_replace("Assembler messages:\n", "", $strout);
            printError($strout);
        }

        // Delete the source and object files if they're there.
        if (file_exists($source_path))
            unlink($source_path);
        if (file_exists($obj_path))
            unlink($obj_path);
    }
    else 
    {
        printError(
            "Sorry, your input is too big or contains unsafe directives! \n" .
            "The period (.) character must not appear anywhere in your source code."
        );
    }

}

function isSafeCode($asm)
{
    // Whitelist 'safe' directives. A list of directives can be found here:
    // http://sourceware.org/binutils/docs-2.23.1/as/Pseudo-Ops.html#Pseudo-Ops
    // Notes:
    //  - .fill et al. are deemed unsafe because of potential DoS (huge output).
    $safe_directives = array(
        ".ascii", ".asciz", ".align", ".balign",
        ".byte", ".int", ".double", ".quad", ".octa", ".word"
    );

    foreach ($safe_directives as $directive)
        $asm = str_replace($directive, "", $asm);


    // These comments have special meaning to as. They don't seem dangerous but
    // reject anyway to be safe.
    if (strpos($asm,"#NO_APP",0) !== false || strpos($asm,"#APP",0) !== false)
        return false;

    // If the source contains any other directives, reject.
    // Yes, this will make it fail if there's a non-directive period in a string
    // constant, but if we want to check for that safely we need to go beyond
    // strpos and even regular expressions.
    return strpos($asm, ".", 0) === false; 
}

// Prints $output in a red box.
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

// Takes the output of objdump (all one string, lines separated by \n)
// and prints the assembly/disassembly results.
function printAsm($objdump_output)
{
    // Find where the actual code starts
    $code_start = strpos($objdump_output, "<_main>:\n");
    if ($code_start < 0)
        printError("Something went wrong!");
    $code_start += strlen("<_main>:\n");

    // Extract just the code.
    $code = substr($objdump_output, $code_start);
    $code = preg_replace('/(\\n|^)\\s*/', "\n", $code);
    $code = trim($code);

    $justBytes = "";
    $lines = explode("\n", $code);
    foreach ($lines as $line)
    {
        $matches = array();
        // Note: there might be a bug here if the mnemonic is equal to a hex byte.
        $res = preg_match('/([a-fA-F0-9]{2}(\s+|$))+/', $line, $matches);
        // Ignore the line if it doesn't have the expected run of hex digits.
        if ($res == 0 || $res == false)
            continue;
        $justBytes .= $matches[0];
    }

    $justBytes = strtoupper($justBytes);
    $justBytes = str_replace("00", "ZERO", $justBytes);
    $justBytes = str_replace(" ", "", $justBytes);
    $justBytes = str_replace("\t", "", $justBytes);
    $safe_justBytes = htmlentities($justBytes, ENT_QUOTES);
    $safe_justBytes = str_replace("ZERO", "<b>00</b>", $safe_justBytes);
    $justBytes = str_replace("ZERO", "00", $justBytes);

    $safe_byteString = "";
    $safe_arrayDef = "{";
    for ($i = 0; $i < strlen($justBytes); $i+=2)
    {
        $hex = htmlentities(substr($justBytes, $i, 2), ENT_QUOTES);
        $safe_byteString .= "\x" . $hex;
        $safe_arrayDef .= " 0x" . $hex;
        if ($i + 2 < strlen($justBytes))
            $safe_arrayDef .= ",";
    }
    $safe_arrayDef .= " }";

    $safe_code = HtmlEscape::escapeText($code, true, 4);
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
?>
