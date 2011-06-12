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
				<tr><td><b>Balance:</b></td><td><?php $balance = (double)StubShare::GetUserBalance($user); echo "$$balance"; ?></td></tr>
				<tr><td>My shared stubs:</td><td>$15.43</td></tr>
				<tr><td>My OLPC donation:</td><td>$58.00</td></tr>
				</table>
				<a href="addbalance.php">Add credit</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#">Transfer credit out</a>&nbsp;&nbsp;&nbsp;<a href="#">History</a>
				
			</div>
			<div class="box">
				<h2>Find Stubs</h2>
				<!--height: 28px; float:left; border: solid #cccccc 1px; padding-left: 5px;  font-size: 15px;-->
				<table style="width:100%;"><tr><td><input style="width: 100%" id="search" type="text" name="q" value="Search Stubs.." /></td>
				<td><img src="images/search_small.png"></td></tr></table><!--<input id="searchbutton" type="submit" name="search" value="Go" />-->
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
				<li><a href="profile.php?user=<?php echo htmlspecialchars($user); ?>">My Stub Store</a></li>
				<?php
				$q = mysql_query("SELECT * FROM formats");
				while($q && $finfo = mysql_fetch_array($q))
				{	
					$fid = (int)$finfo['id'];
					$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
					$safe_user = htmlspecialchars($user);
					echo "<li><a href=\"profile.php?user=$safe_user&fmt=$fid\">$fname</a></li>";
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
				<tr><td>Username:&nbsp;&nbsp;</td><td><input type="text" name="username" value="" style="width:70%;"/></td></tr>
				<tr><td>Password: </td><td><input type="password" name="password" value="" style="width:70%;"/></td></tr>
				</table>
				<input type="submit" name="submit" value="Login" /><br />
				</form>
			</div>
			<?
		}
		?>
	</div>
</div>
</body>
</html>
