  <!-- MENU -->

  <div id="menu">
  <a href="?name=<?php echo $name ?>"><?php echo $lang->get('menu-overview') ?></a>
  <!--
  | <a href="?name=<?php echo $name ?>&amp;page=performance">Performance</a>
  -->
  | <a href="?name=<?php echo $name ?>&amp;page=processor"><?php echo $lang->get('menu-processor') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=memory"><?php echo $lang->get('menu-memory') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=boot-options"><?php echo $lang->get('menu-boot') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=disk-devices"><?php echo $lang->get('menu-disk') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=network-devices"><?php echo $lang->get('menu-network') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=multimedia-devices"><?php echo $lang->get('menu-multimedia') ?></a>
  | <a href="?name=<?php echo $name ?>&amp;page=host-devices"><?php echo $lang->get('menu-hostdev') ?></a>
<?php
	if (($lv->domain_is_running($res, $name) && ($lv->supports('screenshot'))))
		echo '<br /> <a href="?name='.$name.'&amp;page=screenshot">'.$lang->get('menu-screenshot').'</a>';
?>
  </div>
