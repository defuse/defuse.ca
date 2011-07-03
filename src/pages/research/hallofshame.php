<?php
$user = "pphos";
$pass = "3cjBPCoqKu34G";
$database = "pphos";
$conn = mysql_connect("localhost", $user, $pass) or die('Sorry! Problem connecting to the database...');
mysql_select_db($database, $conn) or die ('Sorry! Problem connecting to the database...');
function PrintSite($name, $desc, $alexa, $domain, $min, $max, $charset, $usercount)
	{
		$name = mysql_real_escape_string($name);
		echo "<li class=\"hosli\"><h3>$name</h3>";
		if(!empty($desc))
		{
			$desc = htmlspecialchars($desc, ENT_QUOTES);
			echo "<p class=\"hosdesc\">$desc</p>";
		}
		echo "<table>";
		if($alexa != 0)
		{
			$alexa = GroupDigits($alexa);
			echo "<tr><td class=\"hosfielddesc\">Alexa Rank:</td><td class=\"hosval\">$alexa</td></tr>";
		}
		if(!empty($domain))
		{
			$domain = htmlspecialchars($domain, ENT_QUOTES);
			echo "<tr><td class=\"hosfielddesc\">Domain Name:</td><td class=\"hosval\">$domain</td></tr>";
		}
		if($min != 0)
		{
			$min = (int)$min;
			echo "<tr><td class=\"hosfielddesc\">Minimum Password Length:</td><td class=\"hosval\">$min</td></tr>";
		}
		if($max != 0)
		{
			$max = (int)$max;
			echo "<tr><td class=\"hosfielddesc\">Maximum Password Length:</td><td class=\"hosval\">$max</td></tr>";
		}
		if(empty($charset))
		{
			$charset = "None.";
		}
		if($charset != "skip")
		{
			$charset = htmlspecialchars($charset, ENT_QUOTES);
			echo "<tr><td class=\"hosfielddesc\">Character Restrictions:</td><td class=\"hosval\">$charset</td></tr>";
		}
		if($usercount != 0)
		{
			$usercount = GroupDigits($usercount);
			echo "<tr><td class=\"hosfielddesc\">Estimate Number of Users:</td><td class=\"hosval\">$usercount</td></tr>";
		}
		echo "</table></li>";
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

<h1 style="text-align: center; border-bottom: none;">Password Policy Hall of <span style="color:red;">SHAME</span></h1>
<div style="width:600px; text-align:center; margin: 0 auto; border-top: #AA0000 solid 5px; border-bottom: #AA0000 solid 5px;">
<span style="font-size: 20px;">Storing passwords in PLAIN TEXT is <span style="color:red;"><b>NOT SAFE</b></span>.</span>
<br />
<span style="font-size: 20px;">It's time to make online services clean up their act!</span>
</div>
<p>This is a user-submitted list of websites and services that enforce a password policy that is detrimental to password security. This includes password policies that exclude special characters or enforce a maximum length. These unreasonable password policies are signs that the passwords are being stored in <b>plain text</b>, not <a href="http://crackstation.net/hashing-security.html">hashed with salt</a>.</p>

<p>Cryptographic hash functions will take <b>any input</b> and produce a fixed-length cryptographic signature of the input. If the passwords are being hashed, there is no need for password restrictions, so we can assume any websites that impose these restrictions are storing passwords in plain text...until they prove otherwise.</p>

<h2>Top 100 Websites</h2>
<div style="text-align: center; font-size: 30px;">83% of the top 100 websites limit password length.</div>
<p>Of the top 100 websites as rated by Alexa, 59 allow users to create accounts that are unique to that site (e.g. ebay.com and ebay.de are counted as one). Of those 59 websites, 49 (83%) impose an upper bound on password length. 14 (24%) restrict passwords to alpha-numeric passwords only. It has been confirmed that at least two of the 59 sites store passwords in plain text.</p>
<p><strong><a href="/downloads/top100_rawdata.zip">Download the raw data</a></strong></p>
<center>
<img src="/images/passlengtht100.png" style="margin: 10px;" alt="Password Length Limit Alexa Top 100 Pie Chart"/>
<img src="/images/passchart100.png" style="margin: 10px;" alt="Password Character Restrictions Alexa Top 100 Pie Chart" />
</center>
<!--<center>
<b>Upper Bound Frequency</b>
<table border="1">
<tr><th>Max. Password Length</th><th>Count</th></tr>
<tr><td>No Limit</td><td>10
<tr><td>250</td><td>1</td></tr>
<tr><td>128</td><td>1</td></tr>
<tr><td>100</td><td>3</td></tr>
<tr><td>64</td><td>1</td></tr>
<tr><td>50</td><td>1</td></tr>
<tr><td>40</td><td>3</td></tr>
<tr><td>32</td><td>2</td></tr>
<tr><td>31</td><td>1</td></tr>
<tr><td>25</td><td>2</td></tr>
<tr><td>24</td><td>1</td></tr>
<tr><td>20</td><td>11</td></tr>
<tr><td>16</td><td>11</td></tr>
<tr><td>15</td><td>2</td></tr>
<tr><td>14</td><td>2</td></tr>
<tr><td>12</td><td>4</td></tr>
<tr><td>10</td><td>2</td></tr>
<tr><td>8</td><td>1</td></tr>
</table>
</center>-->

<h2>100-Site Random Sample</h2>
<p>Of a random 100-site sample of the Alexa top 1,000,000 list, 19 support accounts. Of those 19:</p>
<ul>
	<li>1 sends the current password in clear text via email when password recovery is used.</li>
	<li>2 do not accept passwords with symbols.</li>
	<li>12 have a maximum password length of less than or equal to 60.</li>
	<li>11 have a maximum password length of less than or equal to 20.</li>
	<li>3 have a maximum password length of less than or equal to 16.</li>
	<li>1 has a maximum password length of exactly 10.</li>
</ul>
<p>Raw data will be made available shortly.</p>
<!--<h3>Top 50</h3>
<p>Of the top 50 sites as rated by Alexa, 48 support accounts. Of those 48, there are 30 that use unique login back ends. Of those 30:</p>
<ul>
	<li>24 restrict password length.</li>
	<li>1 does not accept passwords with symbols.</li>
	<li>18 have maximum password lengths less than or equal to 40.</li>
	<li>14 have maximum password lengths less than or equal to 20.</li>
	<li>10 have maximum password lengths of exactly 16.</li>
	<li>The smallest maximum password length is 14.</li>
</ul>
<p>Google, Amazon, and LinkedIn all have maximum password lengths greater than or equal to 100. Limits greater than 100 seem (to me) to be more of a "sanity check" than an actual database field size limit. It makes sense, because they probably don't want users logging in with 10 kilobyte passwords.</p>
<p>Interestingly, nearly every Chinese website has the exact same password size restrictions - 6 to 16 characters. Could this be a sign of government password escrow?</p>
<p>Raw data will be made available shortly.</p>-->
<h3>Social Networking Websites</h3>
<p>Statistics for <a href="https://secure.wikimedia.org/wikipedia/en/wiki/List_of_social_networking_websites">Wikipedia's list of social networking websites</a> are coming soon.</p>
<h3>Maximum Length Histogram</h3>
<p>The following histogram shows how many websites in our database restrict passwords to a given length.</p>
<?php
	$q = mysql_query("SELECT max FROM hos ORDER BY max");
	$current = -1;
	$total = 0;
	while($q && $site = mysql_fetch_array($q))
	{
		$sitemax = (int)$site['max'];
		if($sitemax == 0)
			continue;
		if($sitemax != $current)
		{
			if($total > 0)
			{
				$barwidth = 30 + $total * 10;
				echo "<div style=\"height:20px; padding-left: 3px; background-color: black; color: white; width:${barwidth}px\">$current</div>";
			}
			$current = $sitemax;
			$total = 0;
		}
		$total++;
	}
	$barwidth = 30 + $total * 10;
	echo "<div style=\"height:20px; padding-left: 3px; background-color: black; color: white; width:${barwidth}px\">$current</div>";	
?>
<h2><span style="color:red;">Confirmed Plaintext</span></h2>
<ul>
<?php
	$q = mysql_query("SELECT * FROM plaintext ORDER BY alexarank ASC");
	while($q && $site = mysql_fetch_array($q))
	{
		PrintSite($site['name'], $site['desc'], $site['alexarank'], $site['domain'], 0, 0, "skip", $site['usercount']);
	}
?>
</ul>

<h2><span style="color:red;">Unreasonable Restrictions: Probably Plaintext</span></h2>
<ul class="hosul">
<?php
	$q = mysql_query("SELECT * FROM hos ORDER BY alexarank ASC");
	while($q && $site = mysql_fetch_array($q))
	{
		PrintSite($site['name'], $site['desc'], $site['alexarank'], $site['domain'], $site['min'], $site['max'], $site['charset'], $site['usercount']);
	}
?>
</ul>

<h2>How to Help</h2>
<p>If you use one of these services, please write to them. Ask them to remove the password restrictions and ensure that they are hashing the passwords properly. Encouraging your friends to do the same, and sharing this page will help too. These are big companies; they won't change anything unless we work together to apply pressure.</p>
<p>Please submit websites! You can find my email address on the <a href="/contact.htm">contact page</a>. I am especially interested in the <a href="http://www.alexa.com/topsites">Alexa Top 500 Websites</a>. </p>

<p>Please send me: </p>

<ul>
	<li>The name of the company, service, or website</li>
	<li>An explanation of the unreasonable restrictions they are imposing</li>
	<li>A link to a page on their website where I can verify the password policy</li>
</ul>

<p>All submissions are GREATLY appreciated, THANKS!!!!</p>

<h2>How do I get my company/website off the list?</h2>
<p>To get your company or website's name off this list, you must remove the password restrictions in question or give us a good reason why you cannot remove them. You also have to clearly describe to us HOW your passwords are being stored in your database. We will work with you to verify the facts, and will remove you from the list promptly. We will not comply with any kind of take-down order. Read <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Freedom_of_speech_in_the_United_States">this</a> and <a href="https://secure.wikimedia.org/wikipedia/en/wiki/Section_Two_of_the_Canadian_Charter_of_Rights_and_Freedoms">this</a>.</p>

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
