<?php
require_once('/etc/creds.php');
$creds = Creds::getCredentials("pphos");
$conn = mysql_connect($creds[C_HOST], $creds[C_USER], $creds[C_PASS]) or die('Sorry! Problem connecting to the database...');
mysql_select_db($creds[C_DATB], $conn) or die ('Sorry! Problem connecting to the database...');
unset($creds);

$alt = 0;
function PrintSite($name, $desc, $alexa, $domain, $min, $max, $charset, $usercount)
	{
        global $alt;
        $alt = ($alt + 1) % 2;
        $altclass = "";
        if($alt == 1)
            $altclass = "alt";
        echo "<tr class=\"pphostr $altclass\">";
		$name = htmlspecialchars($name, ENT_QUOTES);
		$domain = htmlspecialchars($domain, ENT_QUOTES);
        echo "<td class=\"hosval\">$name</td>";
		$desc = htmlspecialchars($desc, ENT_QUOTES);
        if(empty($desc))
            $desc = "&nbsp;";
		echo "<td class=\"hosval\">$desc</td>";
		if($alexa != 0)
		{
			$alexa = GroupDigits($alexa);
			echo "<td class=\"hosval\">$alexa</td>";
		}
        else
        {
            echo "<td class=\"hosval\">&nbsp;</td>";
        }
		if($min != 0)
		{
			$min = (int)$min;
			echo "<td class=\"hosval\">$min</td>";
		}
        else
        {
            echo "<td class=\"hosval\">&nbsp;</td>";
        }

		if($max != 0)
		{
			$max = (int)$max;
			echo "<td class=\"hosval\">$max</td>";
		}
        else
        {
            echo "<td class=\"hosval\">&nbsp;</td>";
        }

		if(empty($charset))
		{
			$charset = "None.";
		}

		if($charset != "skip")
		{
			$charset = htmlspecialchars($charset, ENT_QUOTES);
			echo "<td class=\"hosval\">$charset</td>";
		}
        else
        {
            echo "<td class=\"hosval\">&nbsp;</td>";
        }

		if($usercount != 0)
		{
			$usercount = GroupDigits($usercount);
			echo "<td class=\"hosval\">$usercount</td>";
		}
        else
        {
            echo "<td class=\"hosval\">&nbsp;</td>";
        }
        echo "</tr>";
	}
function GroupDigits($num)
	{
		$num = $num . "";
		$out = "";
		for($i = 1; $i <= strlen($num); $i++)
		{
			$out = $num[strlen($num) - $i] . $out;
			if(($i) % 3 == 0 && $i != strlen($num))
				$out = ",".$out;
		}
		return $out;
	}
?>

<?php
    Upvote::render_arrows(
        "pphos",
        "defuse_pages",
        "Password Policy Hall of Shame",
        "A list of web sites that store passwords in plain text, restrict password length, or restrict password characters",
        "https://defuse.ca/password-policy-hall-of-shame.htm"
    );
?>
<h1 style="text-align: center; border-bottom: none;">Password Policy Hall of <span style="color:red;">SHAME</span></h1>

<div style="border: solid red 5px; margin: 20px;">
<span style="font-size: 20pt;">THIS PAGE IS DEPRECATED</span>
<p>
I no longer have time to keep updating this page. I could automate it,
but I don't have time to do that either. So for now this page will remain
frozen, and will probably contain incorrect information.
</p>
<p>
If you want to pick up the project, <a href="/contact.htm">email me</a> and I'll
redirect this page to yours.
</p>
</div>

<div style="width:600px; text-align:center; margin: 0 auto; border-top: #AA0000 solid 5px; border-bottom: #AA0000 solid 5px;">
<span style="font-size: 20px;">Storing passwords in PLAIN TEXT is <span style="color:red;"><b>NOT SAFE</b></span>.</span>
<br />
<span style="font-size: 20px;">It's time to make online services clean up their act!</span>
</div>
<p>This is a user-submitted list of websites and services that enforce a password policy that is detrimental to password security. This includes password policies that exclude special characters or enforce a maximum length. As explained on the <a href="/passwordrestrictions.htm">password restrictions</a> page, these unreasonable password policies are signs that the passwords are being stored in <b>plain text</b>, not <a href="http://crackstation.net/hashing-security.html">hashed with salt</a>.</p>

<p>Cryptographic hash functions will take <b>any input</b> and produce a fixed-length cryptographic signature of the input. If the passwords are being hashed, there is no need for password restrictions, so we can assume any websites that impose these restrictions are storing passwords in plain text...until they prove otherwise.</p>

<h2>Statistics</h2>
<div style="text-align: left; font-size: 18px; ">
<div style="margin-bottom: 10px;"><b>Of the top 59 account-based websites...</b></div>
<ul>
<li>Over 50% limit passwords to 20 characters or less.</li>
<li>24% don't allow passwords to contain symbols.</li>
</ul>
</div>
<p>Of the top 100 websites as rated by Alexa, 59 allow users to create accounts that are unique to that site (e.g. ebay.com and ebay.de are counted as one). Of those 59 websites, 49 (83%) impose an upper bound on password length. Over 50% limit passwords to 20 characters or less. 14 (24%) restrict passwords to alpha-numeric characters only. It has been confirmed that at least two of the 59 sites store passwords in plain text.</p>
<center>
<img src="/images/passlengtht100.png" style="margin-right: 10px;" alt="Password Length Limit Alexa Top 100 Pie Chart"/>
<img src="/images/passchart100.png" alt="Password Character Restrictions Alexa Top 100 Pie Chart" />
<p><strong><a href="/downloads/top100_rawdata.zip">Download the raw data</a></strong></p>
</center>


<h2>100-Site Random Sample</h2>
<p>Of a random 100-site sample of the Alexa top 1,000,000 list, 19 support accounts. Of those 19:</p>
<ul>
	<li>1 (5%) sends the current password in clear text via email when password recovery is used.</li>
	<li>2 (10%) do not accept passwords with symbols.</li>
	<li>11 (58%) have a maximum password length of less than or equal to 20.</li>
	<li>3 (16%) have a maximum password length of less than or equal to 16.</li>
	<li>1 (5%) has a maximum password length of exactly 10.</li>
</ul>
<p>Keep in mind that this is not a <em>true</em> random sample, since the selection was made from the top 1,000,000 sites.</p>

<h2><span style="color:red;">Confirmed Plaintext</span></h2>
<table class="pphostbl" cellspacing="0">
<tr class="pphostr"><th>Name</th><th>Info</th><th>Alexa Rank</th><th>Minimum Length</th><th>Maximum Length</th><th>Characters Restrictions</th><th>Users</th></tr>
<?php
	$q = mysql_query("SELECT * FROM plaintext ORDER BY alexarank ASC");
	while($q && $site = mysql_fetch_array($q))
	{
		PrintSite($site['name'], $site['desc'], $site['alexarank'], $site['domain'], $site['min'], $site['max'], $site['charset'], $site['usercount']);
	}
?>
</table>

<h2><span style="color:red;">Unreasonable Restrictions: Probably Plaintext</span></h2>
<table class="pphostbl" cellspacing="0">
<tr class="pphostr"><th>Name</th><th>Info</th><th>Alexa Rank</th><th>Minimum Length</th><th>Maximum Length</th><th>Characters Restrictions</th><th>Users</th></tr>
<?php
	$q = mysql_query("SELECT * FROM hos WHERE noshow='0' ORDER BY alexarank ASC");
	while($q && $site = mysql_fetch_array($q))
	{
		PrintSite($site['name'], $site['desc'], $site['alexarank'], $site['domain'], $site['min'], $site['max'], $site['charset'], $site['usercount']);
	}
?>
</table>

<h2>How to Help</h2>
<p>If you use one of these services, please write to them. Ask them to remove the password restrictions and ensure that they are hashing the passwords properly. Encouraging your friends to do the same, and sharing this page will help too. These are big companies; they won't change anything unless we work together to apply pressure.</p>
<p>Please submit websites! You can find my email address on the <a href="/contact.htm">contact page</a>. I am especially interested in the <a href="http://www.alexa.com/topsites">Alexa Top 500 Websites</a>. </p>

<p>Please send me: </p>

<ul>
	<li>The name of the company, service, or website.</li>
	<li>An explanation of the unreasonable restrictions they are imposing.</li>
	<li>A link to a page on their website where I can verify the password policy.</li>
</ul>

<h2>What's next?</h2>

<p>Over the next few months, I'll be soliciting feedback from the organizations listed in the password policy hall of shame to get a better idea why these restrictions are in place. Once I have that information, I'll start an online campaign to promote the use of <a href="http://crackstation.net/hashing-security.html">proper password hashing</a>.</p>

<h2>How do I get my company/website off the list?</h2>
<p>To get your company or website's name off this list, you must remove the password restrictions in question or give us a good reason why you cannot remove them. You also have to clearly describe to us HOW your passwords are being stored in your database. We will work with you to verify the facts, and will remove you from the list promptly if you are in fact hashing your users' passwords. We will not comply with any kind of take-down order without first consulting a lawyer. Read <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Freedom_of_speech_in_the_United_States">this</a> and <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Section_Two_of_the_Canadian_Charter_of_Rights_and_Freedoms">this</a>.</p>


<?php
function PrintTable($name, $alexa, $min, $max, $restrictions, $domain, $users)
{
	if($min == 0)
		$min = 'Unknown.';
	if($max == 0)
		$max = 'Unknown.';
	if($users == 0)
		$users = 'Unknown.';
	if($alexa == 0)
		$alexa = 'Unknown.';
	if(empty($restrictions))
	{
		$restrictions = "None.";
	}
	$name = htmlspecialchars($name, ENT_QUOTES);
	$alexa = htmlspecialchars($alexa, ENT_QUOTES);
	$min = htmlspecialchars($min, ENT_QUOTES);
	$max = htmlspecialchars($max, ENT_QUOTES);
	$domain = htmlspecialchars($domain, ENT_QUOTES);
	$users = htmlspecialchars($users, ENT_QUOTES);
	$restrictions = htmlspecialchars($restrictions, ENT_QUOTES);
	echo "<table>";
	echo "<tr><td><b>Domain:</b></td><td>$domain</td></tr>";
	echo "<tr><td><b>Alexa Rank:</b></td><td>$alexa</td></tr>";
	echo "<tr><td><b>Minimum Password Length:</b></td><td>$min</td></tr>";
	echo "<tr><td><b>Maximum Password Length:</b></td><td>$max</td></tr>";
	echo "<tr><td><b>Character Set Restrictions:</b></td><td>$restrictions</td></tr>";
	echo "<tr><td><b>Estimate User Count:</b></td><td>$users</td></tr>";

	echo "</table>";
}

?>
