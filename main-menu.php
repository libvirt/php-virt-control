  <!-- LIST OF CONNECTIONS -->
  <div id="conn-list">
    <p><span class="head"><?php echo $lang->get('conns') ?></span></p>
    <ul>
    <?php
	$tmp = $user->getConnectionNames();
	
	$conn_ids = $sess->get('Connections');
	if (empty($conn_ids))
		$conn_ids = array();
	
	if (sizeof($tmp) > 0) {
		foreach ($tmp as $item) {
			$lmid = $item['id'];
			$lmname = $item['name'];

			if (in_array($lmid, $conn_ids))
				echo "<li class='open'><a href=\"?attach=$lmid\">$lmname</a> <a href=\"?detach=$lmid\">[x]</a></li>";
			else
				echo "<li class='closed'><a href=\"?attach=$lmid\">$lmname</a></li>";
		}
	}
	else
		echo "<li class='no-vm'>".$lang->get('no-connection')."</li>";
	if ($user->checkUserPermission(USER_PERMISSION_SAVE_CONNECTION))
		echo "<li class='add-vm'><a href='?page=connections&amp;action=add'>".$lang->get('connection-add')."</a></li>";
    ?>
    </ul>
  </div>


  <div id="conn-detail">
  <!-- MENU -->
  <div id="main-menu">
    <a <?php echo ($page == 'home') ? ' class="active"' : '' ?> href="?"><?php echo $lang->get('home') ?></a>
    | <a <?php echo ($page == 'connections') ? ' class="active"' : '' ?> href="?page=connections"><?php echo $lang->get('connections') ?></a>
    | <a <?php echo ($page == 'domains') ? ' class="active"' : '' ?> href="?page=domains"><?php echo $lang->get('domains') ?></a>
    | <a <?php echo ($page == 'networks') ? ' class="active"' : '' ?> href="?page=networks"><?php echo $lang->get('networking') ?></a>
    <!--
    | <a <?php echo ($page == 'storage') ? ' class="active"' : '' ?> href="?page=storage"><?php echo $lang->get('storage') ?></a>
    -->
    | <a <?php echo ($page == 'info') ? ' class="active"' : '' ?> href="?page=info"><?php echo $lang->get('node-information') ?></a>
<?php
	if (ENABLE_TRANSLATOR_MODE):
?>
    | <a <?php echo ($page == 'translate') ? ' class="active"' : '' ?> href="?page=translate"><?php echo $lang->get('translator-mode') ?></a>
<?php
	endif;
?>
    <div style="float:right;text-align: right; width:220px;font-size:11px;font-style:italic">
	<?php if (isset($uri)) echo $lang->get('connected-to').'<br />'.$uri; ?>
    </div>

  </div>
