<h1>Online x86 Assembler</h1>

<p>
This tool takes some x86 assembly instructions and converts them to their binary
(byte) representations. It can be useful for exploit development, reverse engineering,
and cracking.
</p>

<p>
<strong>Enter your x86 instructions</strong> (intel syntax, one per line):
</p>

<form action="/online-x86-assembler.htm" method="post">
    <textarea name="instructions" rows="15" cols="80" style="color: black; background-color: white;
border: dashed 1px black; width: 100%;" ><?php
    if (isset($_POST['instructions']))
    {
        echo htmlentities($_POST['instructions'], ENT_QUOTES);
    }
    ?></textarea>
    <p style="text-align: right;">
        <input type="submit" name="submit" value="Assemble" />
    </p>
</form>

<?php

require_once( 'libs/HtmlEscape.php' );

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

    $safe_code = HtmlEscape::escapeText($code, true, 4);
?>
    <div style="background-color: #CCFFCC; border: solid #00FF00 1px; padding: 10px;">
    <div style="font-family: monospace;">
        <?php echo $safe_code; ?>
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

if (isset($_POST['submit']) && isset($_POST['instructions']) && strlen($_POST['instructions']) != 0)
{
    echo '<a name="disassembly"></a>';

    $instructions = $_POST['instructions'];

    // TODO: sanity check on the size and number of lines 

    $asmfile = ".intel_syntax noprefix\n_main:\n" . $instructions . "\n";

    $tempnam = "/tmp/" . rand();
    $source_path = $tempnam . ".s";
    $obj_path = $tempnam . ".o";

    file_put_contents($source_path, $asmfile);

    $ret = 1;
    $output = array();

    // Assemble the source with gcc.
    exec("gcc -m32 -c $source_path -o $obj_path 2>&1", $output, $ret);

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
?>
