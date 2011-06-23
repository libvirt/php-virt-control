<?php
  $devs = $lv->domain_get_host_devices($name);
?>
  <!-- CONTENTS -->
  <div id="content">

    <form action="#" method="POST">

    <div class="section">Machine host devices</div>

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
      <div class="label">Host devices:</div>
      <div class="value">None</div>
      <div class="nl" />
    </div>
<?
    endif;
?>

    <!-- ACTIONS SECTION -->
    <div class="section">Actions</div>
    <div class="item">
      <div class="label">Changes:</div>
      <div class="value">
        None (this page is currently read-only)
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
