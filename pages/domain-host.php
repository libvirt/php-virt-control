<?php
	$devs = $lvObject->domainGetHostDevices($vm);
?>
	<div id="s-host" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-host-devices') ?></td>
		</tr>
<?php
	for ($i = 0; $i < sizeof($devs['pci']); $i++):
?>
		<tr>
			<td class="title"><?php echo $lang->get('vm-dev-pci').' #'.($i+1) ?>:</td>
			<td class="field">
				<?php echo $devs['pci'][$i]['product'].' ('.$devs['pci'][$i]['vendor'].')' ?>
			</td>
		</tr>
<?php
	endfor;
?>

<?php
	for ($i = 0; $i < sizeof($devs['usb']); $i++):
?>
		<tr>
			<td class="title"><?php echo $lang->get('vm-dev-usb').' #'.($i+1) ?>:</td>
			<td class="field">
				<?php echo $devs['usb'][$i]['product'].' ('.$devs['usb'][$i]['vendor'].')' ?>
			</td>
		</tr>
<?php
	endfor;
?>

<?php
	if (sizeof($devs['usb']) + sizeof($devs['pci']) == 0):
?>
		<tr>
			<td class="field" colspan="2" style="padding-left: 25px">
				<?php echo $lang->get('no-host-devices') ?>
			</td>
		</tr>
<?php
    endif;
?>

		</tr>
	</table>

	</div>
