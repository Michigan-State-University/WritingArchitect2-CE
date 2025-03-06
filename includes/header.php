<?php if (!isset($GLOBALS['page_title']))    $GLOBALS['page_title'] = ''; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
	<div class="container-fluid">
		<!-- <button class="btn btn-primary" id="sidebarToggle">Toggle Menu</button>-->
		<h2 class="mt-4"><?php echo $GLOBALS['page_title'] ?></h2>
	</div>
	<div id="force_right">
		<table width="240">
			<tr>
				<td nowrap><?php echo $GLOBALS['USER_FIRST_NAME'] ?> <?php echo $GLOBALS['USER_LAST_NAME'] ?></td>
				<td nowrap valign="middle" rowspan="2"><a href="logout.php?id=<?php echo $GLOBALS["SESSION_ID"] ?>"><img src="../images/icn_logout.png"></a></td>
			</tr>
			<tr>
				<td><?php echo $GLOBALS['USER_ORGANIZATION'] ?></td>
			</tr>
		</table>

	</div>
</nav>