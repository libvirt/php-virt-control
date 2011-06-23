  <!-- MENU -->
  <div id="menu">
  <a href="?name=<?= $name ?>">Overview</a>
  <!--
  | <a href="?name=<?= $name ?>&amp;page=performance">Performance</a>
  -->
  | <a href="?name=<?= $name ?>&amp;page=processor">Processor</a>
  | <a href="?name=<?= $name ?>&amp;page=memory">Memory</a>
  | <a href="?name=<?= $name ?>&amp;page=boot-options">Boot options</a>
  | <a href="?name=<?= $name ?>&amp;page=disk-devices">Disk devices</a>
  | <a href="?name=<?= $name ?>&amp;page=network-devices">Network devices</a>
  | <a href="?name=<?= $name ?>&amp;page=multimedia-devices">Multimedia devices</a>
  | <a href="?name=<?= $name ?>&amp;page=host-devices">Host devices</a>
<?php
  if (($lv->domain_is_running($res, $name) && ($lv->supports('screenshot'))))
    echo '| <a href="?name='.$name.'&amp;page=screenshot">Screenshot</a>';
?>
  </div>
