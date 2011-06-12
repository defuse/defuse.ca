<?php
	require_once('stubshare.inc.php');
	require_once('db.inc.php');
?>
	</div>
	<div id="sidebar">
		<?php

		if($user != false)
		{
			?>
			<div class="box">
				<table style="width: 100%;">
				<tr><td><b>Credit Balance:</b></td><td><b><?php $balance = (double)StubShare::GetUserBalance($user); echo "$$balance"; ?></b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>My shared stubs:</td><td>$15.43</td></tr>
				<tr><td>My OLPC donation:</td><td>$58.00</td></tr>
				</table>
				By <a href="finance.php">month</a>&nbsp;&nbsp;|&nbsp;&nbsp;by <a href="finance.php">history</a>
				
			</div>
			<div  style="background-color:#FFFFFF; margin-left:20px; width:100%;">
				<table style="width:100%"><tr><td style="width:75% height: 30px; background-color:#EEEEEE; border: solid 1px #CCCCCC;" valign="center" ><input style="width: 100%"  id="search" type="text" name="q" value="Search Stubs.." /></td><td style="height:30px;"><a href="stubstore.php"><img src="images/search_small.png"></a></td></tr></table>
			</div>
			<div class="box">
				<h2>Find Stubs</h2>
				<!--height: 28px; float:left; border: solid #cccccc 1px; padding-left: 5px;  font-size: 15px;-->
				
				<!--<input id="searchbutton" type="submit" name="search" value="Go" />-->
				<h2>Browse</h2>
				<ul class="linklist">	
				<a href="stubstore.php"><li>All Content</li></a>			
				<?php
				$q = mysql_query("SELECT * FROM formats");
				while($q && $finfo = mysql_fetch_array($q))
				{	
					$fid = (int)$finfo['id'];
					$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
					echo "<a href=\"stubstore.php?fmt=$fid\"><li>$fname</li></a>";
				}
				?>
				</ul>
			</div>
			<div class="box">
				<h2>Manage my pages</h2>
				<ul class="linklist">
				<li><a href="profile.php?user=<?php echo htmlspecialchars($user); ?>"><b>My Stub Store</b></a></li>
				<?php
				$q = mysql_query("SELECT * FROM formats");
				while($q && $finfo = mysql_fetch_array($q))
				{	
					$fid = (int)$finfo['id'];
					$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
					$safe_user = htmlspecialchars($user);
					echo "<li><a href=\"profile.php?user=$safe_user&fmt=$fid\">My $fname</a></li>";
				}
				?>
				</ul>
			</div>

			<?
		}
		else
		{
			?>
			<div class="box">
			<h2>Login</h2>
			<?php
			if(isset($error))
			{
				echo '<b>' . $error . '</b>';
			}
			if(isset($_GET['c']))
			{
				echo '<b>' . 'Account created, please login.' .  '</b>';
			}
			?>
			<form action="index.php" method="post" >
				<table style="width:100%;">
				<tr><td>Username:&nbsp;&nbsp;</td><td style="width:70%;"><input type="text" name="username" value="" style="width:100%;" /></td></tr>
				<tr><td>Password: </td><td style="width:70%;"><input type="password" name="password" value="" style="width:100%;"/></td></tr>
				</table>
				<input type="submit" name="submit" value="Login" /><br />
				</form>
			</div>
			<?
		}
		?>
	</div>
</div>
</div>
</body>
</html>
