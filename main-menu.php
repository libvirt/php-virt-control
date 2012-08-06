  <!-- LIST OF CONNECTIONS -->
  <div id="conn-list">
    <p><?php echo $lang->get('conns') ?></p>
    <?php
	$tmp = $db->list_connections(true);

	$conn_ids = array();
	foreach ($conns as $conn) {
		$conn_ids[] = $conn['id'];
	}

	if (sizeof($tmp) > 0) {
		foreach ($tmp as $item) {
			$lmid = $item['id'];
			$lmname = $item['name'];

			if (in_array($lmid, $conn_ids))
				echo "<img src='graphics/open.png' /><a href=\"?attach=$lmid\">$lmname</a> <a href=\"?detach=$lmid\">[x]</a><br />";
			else
				echo "<img src='graphics/closed.png' /><a href=\"?attach=$lmid\">$lmname</a><br />";
		}
	}
	else
		echo '-';
    ?>
  </div>

  <div id="conn-detail">

  <!-- MENU -->
  <div id="main-menu">
    <a href="?"><?php echo $lang->get('main_menu') ?></a>
    | <a href="?page=domain-list"><?php echo $lang->get('domain_list') ?></a>
    | <a href="?page=network-list"><?php echo $lang->get('network_list') ?></a>
    | <a href="?page=users"><?php echo $lang->get('users') ?></a>
    | <a href="?page=settings"><?php echo $lang->get('settings') ?></a>
    | <a href="?page=info"><?php echo $lang->get('info') ?></a>
    | <a href="?action=logout"><?php echo $lang->get('logout') ?></a>
    <div style="float:right;text-align: right; width:220px;font-size:11px;font-style:italic">
	<?php if (isset($uri)) echo $lang->get('connected_to').'<br />'.$uri; ?>
    </div>
  </div>
