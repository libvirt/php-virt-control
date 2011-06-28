<?php
  $devs = $lv->domain_get_host_devices($name);
?>
  <!-- CONTENTS -->
  <div id="content">

    <form action="#" method="POST">

    <div class="section"><?= $lang->get('host_devices_title') ?></div>

<?php
    for ($i = 0; $i < sizeof($devs['pci']); $i++):
?>
    <div class="item">
      <div class="label">PCI Device #<?= $i+1 ?>:</div>
      <div class="value"><?= $devs['pci'][$i]['product'].' from '.$devs['pci'][$i]['vendor'] ?></div>
      <div class="nl" />
    </div>
<?php
    endfor;
?>

<?php
    for ($i = 0; $i < sizeof($devs['usb']); $i++):
?>
    <div class="item">
      <div class="label">USB Device #<?= $i+1 ?>:</div>
      <div class="value"><?= $devs['usb'][$i]['product'].' from '.$devs['usb'][$i]['vendor'] ?></div>
      <div class="nl" />
    </div>
<?php
    endfor;
?>

<?php
    if (sizeof($devs['usb']) + sizeof($devs['pci']) == 0):
?>
    <div class="item">
      <div class="label"><?= $lang->get('host_devices') ?>:</div>
      <div class="value"><?= $lang->get('hostdev_none') ?></div>
      <div class="nl" />
    </div>
<?
    endif;
?>

    <!-- ACTIONS SECTION -->
    <div class="section"><?= $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('changes') ?>:</div>
      <div class="value">
        <?= $lang->get('details_readonly') ?>
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
