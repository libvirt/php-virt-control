<?php
	$name = $vm;

	$tmp = $lvObject->getNicInfo($name);
	$numNics = sizeof($tmp);
	if (!$tmp)
		$numNics = 0;
?>

	<div id="s-nics" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-nic-details') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-nic-num') ?>:</td>
			<td class="field">
				<?php echo $numNics ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
<?php
	for ($i = 0; $i < sizeof($tmp); $i++):
		$nic = $tmp[$i];

		if (!empty($nic)):
?>
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-network-nic') ?> #<?php echo $i + 1 ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-network-mac') ?>:</td>
			<td class="field">
				<?php echo $nic['mac'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-network-net') ?>:</td>
			<td class="field">
				<?php echo $nic['network'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-network-type') ?>:</td>
			<td class="field">
				<?php echo $nic['nic_type'] ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('actions') ?>:</td>
			<td class="field">
				<input type="button" onclick="askNetworkDeviceDeletion('<?php echo $nic['mac'] ?>')" style="cursor: pointer" value=" <?php echo $lang->get('vm-network-remove') ?> " />
			</td>
		</tr>
<?php
		endif;
	endfor;
?>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-network-add') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-network-add-data') ?>: </td>
			<td class="field">

			<table id="connections-edit">
			<tr>
				<td class="title"><?php echo $lang->get('vm-network-mac') ?>: </td>
				<td class="field"><input type="text" name="network-mac" id="network-mac" value="<?php echo$lvObject->generateRandomMacAddr() ?>" /></td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('vm-network-net') ?>: </td>
				<td class="field">
					<select name="network-net" id="network-net">
<?php
			$nets = $lvObject->getNetworks();
			for ($i = 0; $i < sizeof($nets); $i++)
				echo '<option value="'.$nets[$i].'">'.$nets[$i].'</option>';
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('vm-network-type') ?>: </td>
				<td class="field">
					<select name="network-type" id="network-type">
<?php
			$models = $lvObject->getNicModels();
			for ($i = 0; $i < sizeof($models); $i++)
				echo '<option value="'.$models[$i].'">'.$models[$i].'</option>';
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title">&nbsp;</td>
				<td class="field" style="padding-top: 25px">
					<input type="button" onclick="addNetworkDevice()" style="cursor: pointer" value=" <?php echo $lang->get('vm-network-add') ?> " />
				</td>
			</tr>
			</table>

			</td>
		</tr>
	</table>

	</div>

