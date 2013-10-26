<?php
	$name = $vm;

	$ci  = $lvObject->hostGetNodeInfo();
	$memory = round($ci['memory'] / 1024);
	unset($ci);
	$info = $lvObject->domainGetInfo($name);
	$guest_memory = round($info['memory'] / 1024);
	$guest_maxmem = round($info['maxMem'] / 1024);
	unset($info);
?>

	<div id="s-memory" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('host-information') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('total-memory') ?>:</td>
			<td class="field">
				<?php echo $memory ?> MiB
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-details') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('memory-current') ?> (MiB):</td>
			<td class="field"><input type="text" name="guest_memory" id="guest_memory" value="<?php echo $guest_memory ?>" /></td>
			<input type="hidden" id="guest_memory_old" value="<?php echo $guest_memory ?>" />
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('memory-max') ?> (MiB):</td>
			<td class="field"><input type="text" name="guest_maxmem" id="guest_maxmem" value="<?php echo $guest_maxmem ?>" /></td>
			<input type="hidden" id="guest_maxmem_old" value="<?php echo $guest_maxmem ?>" />
		</tr>
		<tr>
			<td class="field" style="padding-top: 25px" colspan="2" align="center">
				<input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('save-changes') ?>" />
			</td>
		</tr>
	</table>

	</div>

