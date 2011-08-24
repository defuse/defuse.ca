<?php
    set_time_limit(20);

    $eqn = "";
    if(isset($_POST['eqn']))
        $eqn = $_POST['eqn'];

    $base = 10;
    $interval = 3;
    if(isset($_POST['base']))
    {
        switch($_POST['base'])
        {
            case "dec":
                $base = 10;
                $interval = 3;
                break;
            case "hex":
                $base = 16;
                $interval = 4;
                break;
            case "oct":
                $base = 8;
                $interval = 2;
                break;
            default:
                $base = 10;
                $interval = 3;
        }
    }
?>

<h1>Online Big Number Calculator</h1>
<form action="/big-number-calculator.htm" method="post">
    <div style="text-align: center;">
    <strong>Expression:&nbsp;&nbsp;&nbsp;&nbsp;</strong>
    <input type="text" name="eqn" style="width:400px;" value="<?php echo htmlentities($eqn, ENT_QUOTES); ?>"/> 
    
    <input type="submit" name="submit" value="Calculate" />

    <div style="margin: 10px;">
    <p>
    Output Base: 
    <select name="base">
        <option value="dec" <?php if($base == 10) echo 'selected="selected"'; ?> >Decimal</option>
        <option value="hex" <?php if($base == 16) echo 'selected="selected"'; ?> >Hexadecimal</option>
        <option value="oct" <?php if($base == 8) echo 'selected="selected"'; ?> >Octal</option>
    </select>
    </p>
    </div>
    </div>
</form>

<table style="margin: 0 auto; margin-bottom: 20px;">
    <tr><th>Base Prefixes:&nbsp;</th><td>0x - Hexadecimal, 0 - Octal</td></tr>
    <tr><th>Math Operations:&nbsp;</th><td>+, -, *, /, ^, (, )</td></tr>
    <tr><th>Bitwise Operations:&nbsp;</th><td>OR, AND, XOR, SHL, SHR, &lt;&lt;, &gt;&gt;, |, &amp;</td></tr>
</table>
<?php
if(isset($_POST['submit']))
{
?>
<div id="bignumber" style="padding: 10px; font-family: monospace; border: solid black 2px;">
<?
    //TODO: define ! function for fixnum and bignum
    $eqn = strtolower($eqn);
    $eqn = str_replace("^", "**", $eqn);
    $eqn = str_replace("xor", "^", $eqn);
    $eqn = str_replace("or", "|", $eqn);
    $eqn = str_replace("and", "&", $eqn);
    $eqn = str_replace("shl", "<<", $eqn);
    $eqn = str_replace("shr", ">>", $eqn);
    
    if(containsUnsafeChars($eqn))
    {
        echo "Sorry, what you entered wasn't recognized as a valid mathematical expression.";
    }
    else
    {
        $max_time = 10;
        $res = "";
        $ruby = popen("ulimit -t $max_time; ruby -e \"x = ($eqn); puts x if x.is_a?(Float); puts x.to_s($base) if x.is_a?(Fixnum) or x.is_a?(Bignum)\"", "r");
        stream_set_blocking($ruby, 0);

        $tooLong = false;
        $start = time();
        while(!feof($ruby) && !$tooLong)
        {
            $res .= fread($ruby, 1000);
            time_nanosleep(0, 10000);
            if(time() > $start + $max_time)
                $tooLong = true;
        }
        pclose($ruby);

        $res = trim($res);

        if(!$tooLong && !is_blank($res) && strpos($res, "warning") === false && strpos($res, "error") === false && strpos($res, "Infinity") === false)
        {
            if($res == "true" || $res == "false")
            {
                $res = breakLines($res, 60);
            }
            else
            {
                $res = groupDigits($res, $interval, " ");
            }
            echo $res;
        }
        elseif($tooLong)
        {
            echo "Sorry, it's taking too long to calculate that number.";
        }
        elseif(is_blank($res))
        {
            echo "Sorry, what you entered wasn't recognized as a valid mathematical expression.";
        }
        else
        {
            echo "Sorry, we can't calculate numbers THAT big!";
        }
    }
?>
</div>
<?
}
function is_blank($val)
{
    return empty($val) && !is_numeric($val);
}
function isDigitOnly($text)
{
    for($i = 0; $i < strlen($text); $i++)
    {
        $ch = substr($text, $i, 1);
        if(!(ord($ch) >= ord('0') && ord($ch) <= ord('9')))
            return false;
    }
    return true;
}

//FIXME: Handle decimals better. (Split on the .)
function groupDigits($text, $interval, $sep)
{
    $result = Array();

    $outDigits = strlen($text) % $interval;
    if($outDigits > 0)
        $result[] = str_repeat('&nbsp;', $interval - $outDigits) . substr($text, 0, $outDigits) . $sep;

    $text = substr($text, $outDigits);
    for($i = 0; $i < strlen($text); $i++)
    {
        if($i % $interval == 0 && $i != 0)
            $result[] = $sep;
        $result[] = substr($text, $i, 1);
    }
    return implode('', $result);
}
    
function breakLines($text, $lineLength)
{
    $out = Array();
    for($i = 0; $i < strlen($text); $i += $lineLength)
    {
        $out[] = substr($text, $i, $lineLength);
    }
    return implode('<br />', $out);
}

function containsUnsafeChars($eqn)
{
    $whitelist = "1234567890()*^|&%/+-<>. x";
    for($i = 0; $i < strlen($eqn); $i++)
    {
        $ch = substr($eqn, $i, 1);
        if(strpos($whitelist, $ch) === false)
            return true;
    }
    return false;
}
?>

