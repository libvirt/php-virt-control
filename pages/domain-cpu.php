<?php
	$name = $vm;

	$ci  = $lvObject->getConnectInformation();
	$max = $ci['hypervisor_maxvcpus'];
	if (($max == -1) && ($ci['hypervisor'] == 'QEMU')) {
		$mtsa = $lvObject->connectGetMachineTypes();
		$has_kvm = false;
		if (!empty($mtsa)) {
			$mts = array_keys($mtsa);
			$has_kvm = array_key_exists('kvm', $mtsa[$mts[0]]);
		}

		if ($has_kvm)
			$type = 'kvm';
		else
			$type = 'qemu';

		$max = $lvObject->getVCPUCountForMachineType($type, $arch);
	}
	unset($ci);

	$info = $lvObject->domainGetInfo($name);
	$guest_cpu_count = $info['nrVirtCpu'];
	unset($info);
	$ci  = $lvObject->hostGetNodeInfo();
	$cpus = $ci['cpus'];
	unset($ci);

	unset($info);
?>

	<div id="s-cpu" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('host-information') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('cpu-cores') ?>:</td>
			<td class="field">
				<?php echo $cpus ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('max-vcpus') ?>:</td>
			<td class="field"><?php echo $max ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-details') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('guest-vcpus') ?>:</td>
			<td class="field">
				<select name="guest_vcpus" id="guest_vcpus">
			<?php
				for ($i = 1; $i <= $max; $i++)
					echo '<option value='.$i.' '.($i == $guest_cpu_count ? 'selected="selected"' : '').'>'.$i.'</option>';
			?>
				</select>
				<input type="hidden" name="guest_vcpus_old" value="<?php echo $cpus ?>" />
			</td>
		</tr>
		<tr>
			<td class="title">&nbsp;</td>
			<td class="field" style="padding-top: 25px">
				<input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('save-changes') ?>" />
			</td>
		</tr>
	</table>

	</div>

