<?php
	$name = $vm;

	$tmp = $lvObject->getDiskStats($name);
	$tmp2 = $lvObject->getCdromStats($name, true);
	$numDisks = sizeof($tmp);

	$addmsg = (sizeof($tmp2) > 0) ? ' (disk) + '.(sizeof($tmp2)).' (cd-rom)' : '';
?>

	<div id="s-disks" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-disk-details') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-num') ?>:</td>
			<td class="field">
				<?php echo $numDisks.$addmsg ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
<?php
	for ($i = 0; $i < sizeof($tmp); $i++):
		$disk = $tmp[$i];
		$bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
		<tr>
			<td colspan="2" class="section"><?php echo $bus ?> Disk <?php echo $i + 1 ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-storage') ?>:</td>
			<td class="field">
				<?php echo $disk['file'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-type') ?>:</td>
			<td class="field">
				<?php echo $disk['type'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-dev') ?>:</td>
			<td class="field">
				<?php echo $disk['device'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-capacity') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['capacity'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-allocation') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['allocation'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-physical') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['physical'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('actions') ?>:</td>
			<td class="field">
				<input type="button" onclick="askBlockDeviceDeletion('<?php echo $disk['device'] ?>')" style="cursor: pointer" value=" <?php echo $lang->get('vm-disk-remove') ?> " />
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
<?php
	endfor;

	for ($i = 0; $i < sizeof($tmp2); $i++):
		$disk = $tmp2[$i];
		$bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
		<tr>
			<td colspan="2" class="section"><?php echo $bus ?> CD-ROM <?php echo $i + 1 ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-storage') ?>:</td>
			<td class="field">
				<?php echo ($disk['file']) ? $disk['file'] : '-' ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-type') ?>:</td>
			<td class="field">
				<?php echo $disk['type'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-dev') ?>:</td>
			<td class="field">
				<?php echo $disk['device'] ?>
			</td>
		</tr>

		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-capacity') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['capacity'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-allocation') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['allocation'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-physical') ?>:</td>
			<td class="field">
				<?php echo $lvObject->formatSize($disk['physical'], 2) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('actions') ?>:</td>
			<td class="field">
				<input type="button" onclick="askBlockDeviceDeletion('<?php echo $disk['device'] ?>')" style="cursor: pointer" value=" <?php echo $lang->get('vm-disk-remove') ?> " />
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
<?php
	endfor;
?>

		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-disk-add') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-disk-add-data') ?>: </td>
			<td class="field">

			<table id="connections-edit">
			<tr>
				<td class="title"><?php echo $lang->get('vm-disk-image') ?>: </td>
				<td class="field"><input type="text" name="disk-img" id="disk-img" /></td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('vm-disk-bus') ?>: </td>
				<td class="field">
					<select name="disk-bus" id="disk-bus">
						<option value="ide">IDE</option>
						<option value="scsi">SCSI</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('vm-disk-type') ?>: </td>
				<td class="field">
					<select name="disk-driver" id="disk-driver">
						<option value="raw">raw</option>
						<option value="qcow">qcow</option>
						<option value="qcow2">qcow2</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('vm-disk-dev') ?>: </td>
				<td class="field"><input type="text" name="disk-dev" value="hdb" id="disk-dev" /></td>
			</tr>
			<tr>
				<td class="title">&nbsp;</td>
				<td class="field" style="padding-top: 25px">
					<input type="button" onclick="addBlockDevice()" style="cursor: pointer" value=" <?php echo $lang->get('vm-disk-add') ?> " />
				</td>
			</tr>
			</table>

			</td>
		</tr>
	</table>

	</div>

