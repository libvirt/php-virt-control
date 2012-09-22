<?php
	$devs = $lv->domain_get_host_devices($name);
?>
  <!-- CONTENTS -->
  <div id="content">

    <form action="#" method="POST">

    <div class="section"><?php echo $lang->get('host-devices-title') ?></div>

<?php
	for ($i = 0; $i < sizeof($devs['pci']); $i++):
?>
    <div class="item">
      <div class="label">PCI Device #<?php echo $i+1 ?>:</div>
      <div class="value"><?php echo $devs['pci'][$i]['product'].' from '.$devs['pci'][$i]['vendor'] ?></div>
      <div class="nl" />
    </div>
<?php
	endfor;
?>

<?php
	for ($i = 0; $i < sizeof($devs['usb']); $i++):
?>
    <div class="item">
      <div class="label">USB Device #<?php echo $i+1 ?>:</div>
      <div class="value"><?php echo $devs['usb'][$i]['product'].' from '.$devs['usb'][$i]['vendor'] ?></div>
      <div class="nl" />
    </div>
<?php
	endfor;
?>

<?php
	if (sizeof($devs['usb']) + sizeof($devs['pci']) == 0):
?>
    <div class="item">
      <div class="label"><?php echo $lang->get('host-devices') ?>:</div>
      <div class="value"><?php echo $lang->get('hostdev-none') ?></div>
      <div class="nl" />
    </div>
<?php
    endif;
?>

    <!-- ACTIONS SECTION -->
    <div class="section"><?php echo $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('changes') ?>:</div>
      <div class="value">
        <?php echo $lang->get('details-readonly') ?>
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
