  <!-- MENU -->
  <div id="menu">
  <a href="?name=<?php echo $name ?>"><?php echo $lang->get('menu_overview') ?></a>
  <!-- 
  <br /> <a href="?name=<?php echo $name ?>&amp;page=performance">Performance</a>
  -->
  <br /> <a href="?name=<?php echo $name ?>&amp;page=processor"><?php echo $lang->get('menu_processor') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=memory"><?php echo $lang->get('menu_memory') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=boot-options"><?php echo $lang->get('menu_boot') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=disk-devices"><?php echo $lang->get('menu_disk') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=network-devices"><?php echo $lang->get('menu_network') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=multimedia-devices"><?php echo $lang->get('menu_multimedia') ?></a>
  <br /> <a href="?name=<?php echo $name ?>&amp;page=host-devices"><?php echo $lang->get('menu_hostdev') ?></a>
<?php
  if (($lv->domain_is_running($res, $name) && ($lv->supports('screenshot'))))
    echo '<br /> <a href="?name='.$name.'&amp;page=screenshot">'.$lang->get('menu_screenshot').'</a>';
?>
  </div>
