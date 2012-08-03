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
	<?php if (isset($uri)) echo "Connected to <br />".$uri; ?>
    </div>
  </div>
