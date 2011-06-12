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
				<a href="addbalance.php">Add credit</a>
			</div>
			<div class="box">
				<h2>Find Stubs</h2>
				<input id="search" type="text" name="q" value="Search..." />
				<input id="searchbutton" type="submit" name="search" value="Go" /><br /><br />
				<h2>Categories</h2>
				<ul class="linklist">				
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
				<?php
				$q = mysql_query("SELECT * FROM formats");
				while($q && $finfo = mysql_fetch_array($q))
				{	
					$fid = (int)$finfo['id'];
					$fname = htmlspecialchars($finfo['name'], ENT_QUOTES);
					echo "<li>$fname</li>";
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
			Username: <input type="text" name="username" value="" /> <br />
			Password: <input type="password" name="password" value="" /><br />
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
