<?php
//Secure Homepage by FireXware
//This code is in the public domain.
//This PHP code provides a simple way to create a website with many pages, without having to add html design code to each page.
//Many homemade, similar PHP scripts have vulnerabilities that allow an attacker to gain control of the website. This was designed to solve that problem.
//This allows the website's owner to focus on creating content and not worrying about repetitive code or security.
//Using this code requires beggning knowledge of PHP. Just read through the comments and find all of the 'TODO:' markings and do them!

//Connect to MySQL
//TODO: change this information.

//TODO: set the title of the page (normally shown on the top of the window and on top of tabs)
$TITLE = "Eagle Engineering - Traffic, Transportation, and Highways";

$META_DESCRIPTION = "Eagle Engineering - Traffic, Transportation, and Highways.";

//name variable will contain the name of the page
$name = "";

//grab the name of the page the user wants from a URL variable
if(isset($_GET['page']))
	$name = $_GET['page'];
//folder where the pages are kept
//TODO: fill that folder with .html files for *the content* on each page
$root = "pages/";

//default path if no page is specified (aka home)
$path = "home.html";

//holds the identification string for the comments (so each page can have their own set of comments)
//empty string to not show comments on the page
$commentid = "";

//this is our firewall, we use a switch to turn the name of a page into the path where the .html content is located
//this protects us against RFI and LFI
switch($name)
{
	//TODO: map each page name to the name of the file within the $root folder.
	//Also, set $commentid for each page, leave it as "" to disable comments for that page
	//Two pages may use the same commentid, they will show the same set of comments.
	case "":
		$name = "home";
		$path = "home.html";
		break;
	case "home":
		$path = "home.html";
		break;
	case "about":
		$path = "about.html";
		break;
	default: //page name wasn't valid. 404 and exit
		$name = ""; // destroy any possibly bad user input asap.
		$commentid = "";
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		$path = "404.html";
		break;
}
//combine the folder and the name of the file within the folder to create the full name
$fullpath = $root . $path;


//handles when the user adds a comment
/*
if(isset($_POST['submit']) && !empty($commentid))
{
	$commentname = sqlsani(smartslashes($_POST['name']));
	if(empty($commentname))
		$commentname = "Anonymous";
	$comment = sqlsani(smartslashes($_POST['comment']));
	mysql_query("INSERT INTO comments (name, comment, commentid) VALUES('$commentname', '$comment', '$commentid')");
}*/

//-----SECURITY AND HELPER FUNCTIONS----//

	//XSS sanitize, makes sure $data can be printed on the screen without any chance of XSS
	//returns sanitized string
	function xsssani($data)
	{
		$data = htmlspecialchars($data, ENT_QUOTES);
		$data = str_replace("\r\n", "<br />", $data);
		$data = str_replace("\n", "<br />", $data);
		$data = str_replace("\r", "<br />", $data);
		return $data;
	}

	//SQL sanitize, makes sure that $data is safe to use in a mysql query
	//returns the sanitized string
	function sqlsani($data)
	{
		return mysql_real_escape_string($data);
	}

	//shows the comments for a comment id
	//uses the xsssani function to ensure that there are no XSS vulnerabilities
	function showcomments($id)
	{
		$id = sqlsani($id);
		$comments = mysql_query("SELECT * FROM comments WHERE commentid='$id'");
		if(mysql_num_rows($comments) > 0)
		{
			echo '<h2>Comments</h2>';
			while($c = mysql_fetch_array($comments))
			{
				echo '<h4>' . xsssani($c['name']) . ' says:</h4>' . xsssani($c['comment']) . '<br /><br />';
			}
		}
	}

	//checks whether smart slashes are enabled and removes them if they are
	function smartslashes($data)
	{
		if(get_magic_quotes_gpc())
		{
			return stripslashes($data);
		}
		else
		{
			return $data;
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $TITLE; ?></title>
<meta name="description" content="<?php echo $META_DESCRIPTION; ?>" >

<link rel="stylesheet" media="all" type="text/css" href="mainmenu.css" />
<link rel="stylesheet" media="all" type="text/css" href="main.css" />

</head>
<body>

<!-- ########################## GRC Masthead Menu ########################## -->

<div class="menuminwidth0"><div class="menuminwidth1"><div class="menuminwidth2">
<div id="masthead">
	<a href="index.php"><img id="mastheadlogo" src="images/logo.jpg"  alt="Eagle Engineering" title="" /></a>
	<div style="position:absolute; left:350px; top:5px;"><img src="images/font1.jpg" alt="EXPERIENCE ... the difference" /></div>
	<div style="position:absolute; left:400px; top:30px;">
		<table cellpadding=0 cellspacing=0 >
		<tr><td><b>Phone:&nbsp;&nbsp;&nbsp;</b></td><td>(403) 123-4568</td></tr>
		<tr><td><b>Fax:&nbsp;&nbsp;&nbsp;</b></td><td>(403) 123-4324</td></tr>
		<tr><td><b>Email:&nbsp;&nbsp;&nbsp;</b></td><td>admin@yeagleengineering.ca</td></tr>
		</table>
	</div>
</div>

<div class="menu">

<ul>
	<li class="headerlink" ><a href="index.php">Home<!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul class="leftbutton">
			<li><a href="#">&nbsp;About</a></li>
			<li><a href="#">&nbsp;Contact</a></li>
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="#">Services<!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="#">&nbsp;Engineering</a></li>
			<li><a href="#">&nbsp;Construction</a></li>

		</ul>
	<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="#">Projects<!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="#"><span class="drop"><span>HWY 1</span>&raquo;</span><!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
				<ul>
					<li><a href="#">&nbsp;BLAH</a></li>
					<li><a href="#">&nbsp;BLAH</a></li>
					<li><a href="#">&nbsp;BLAH</a></li>
				</ul>

				<!--[if lte IE 6]></td></tr></table></a><![endif]-->
			</li>
		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>

<ul>
	<li class="headerlink" ><a href="#">Employment Opportunities<!--[if gt IE 6]><!--></a><!--<![endif]--><!--[if lt IE 7]><table border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
		<ul>
			<li><a href="#">&nbsp;Engineering</a></li>
			<li><a href="#">&nbsp;Construction</a></li>

		</ul>
	<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
</ul>


</div> <!-- close "menu" div -->
<hr style="display:none" />
</div></div></div> <!-- close the "minwidth" wrappers -->
<!-- ###################### END OF GRC MASTHEAD MENU  ###################### -->

<div id="content" style="margin: 0 auto; width: 80%; padding-top:20px;">
<?php
	//TODO: Above and below this php section, put the html design code.

	//displays the page
	//not vulnerable to LFI or RFI, as all of filepath came from constant strings hard-coded into this file.
	if(file_exists($fullpath))
	{
		include($fullpath);
	}
	echo "<br /><br /><br />";
	//show the previously posted comments and a box to add new comments if comments are enabled
	if(!empty($commentid))
	{
		//show the previously posted comments if there are any
		showcomments($commentid);
		echo '<h2>Add a Comment</h2><form action="index.php?page=' . xsssani($name) . '" method="post">
			Name: <br /><input type="text" name="name" maxlength="30" /><br />
			Comment: <br />
			<textarea cols="80" rows="10" name="comment"></textarea><br />
			<input type="submit" name="submit" value="Submit" />
		</form>';
	}
?>
</div>
</body>
</html>
