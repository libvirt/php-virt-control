  <!-- MENU -->
  <div id="menu">
  <a href="?name=<?= $name ?>"><?= $lang->get('menu_overview') ?></a>
  <!--
  | <a href="?name=<?= $name ?>&amp;page=performance">Performance</a>
  -->
  | <a href="?name=<?= $name ?>&amp;page=processor"><?= $lang->get('menu_processor') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=memory"><?= $lang->get('menu_memory') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=boot-options"><?= $lang->get('menu_boot') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=disk-devices"><?= $lang->get('menu_disk') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=network-devices"><?= $lang->get('menu_network') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=multimedia-devices"><?= $lang->get('menu_multimedia') ?></a>
  | <a href="?name=<?= $name ?>&amp;page=host-devices"><?= $lang->get('menu_hostdev') ?></a>
<?php
  if (($lv->domain_is_running($res, $name) && ($lv->supports('screenshot'))))
    echo '| <a href="?name='.$name.'&amp;page=screenshot">'.$lang->get('menu_screenshot').'</a>';
?>
  </div>
