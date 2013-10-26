<?php
	$allowed = true;
	if (!$user->checkUserPermission(USER_PERMISSION_VM_CREATE))
		$allowed = false;

	$skip = false;
	if (!$allowed) {
		$skip = true;
		echo "<div id=\"msg-error\"><b>{$lang->get('msg')}: </b>{$lang->get('permission-denied')}</div>";
	}

	if (!$skip):
	$msg = '';
	$val = $lvObject->createNewVM($_POST);
	if ($val == 2) {
		$msg = $lang->get('domain-add-failed').': '.$lvObject->getLastError();
		$isError = true;
	}
	else
	if ($val == 1) {
		$msg = $lang->get('domain-add-ok');
		$isError = false;
	}

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

		$max = $lvObject->getVCPUCountForMachineType($type);
	}
	unset($ci);

	$ci  = $lvObject->hostGetNodeInfo();
	$cpus = $ci['cpus'];
	unset($ci);

	unset($info);

	$isos = libvirt_get_iso_images();

	$DEFAULT_GUEST_MEM = 512;
?>

<script>
<!--
	function changeDivs(what, val) {
		if (val == 1)
			style = 'table-row';
		else
			style = 'none';

		name = 'setup_'+what;
		d = document.getElementById(name);
		if (d != null)
			d.style.display = style;
	}

	function vmDiskChange(val) {
		if (val == 0) {
			document.getElementById('vm_disk_existing').style.display = 'inline';
			document.getElementById('vm_disk_create').style.display = 'none';
		} else {
			document.getElementById('vm_disk_existing').style.display = 'none';
			document.getElementById('vm_disk_create').style.display = 'inline';
		}
	}

	function generateMacAddr() {
		var xmlhttp;
		if (window.XMLHttpRequest)
			xmlhttp = new XMLHttpRequest();
		else
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById('nic_mac_addr').value = xmlhttp.responseText;
			}
		}

		xmlhttp.open("GET", '<?php echo $_SERVER['REQUEST_URI'] ?>&getMac=1',true);
		xmlhttp.send();
	}
-->
</script>

<h1><?php echo $lang->get('domains') ?></h1>

<?php
	if ($msg):
?>
<div id="msg-<?php echo $isError ? 'error' : 'info' ?>"><?php echo $msg ?></div>
<?php
	endif;
?>

	<form method="POST">

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('domain-add') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('name') ?>:</td>
			<td class="field">
				<input type="text" name="name" />
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('install-image') ?>:</td>
			<td class="field">
				<select name="install_img">
					<option value="-"> <?php echo $lang->get('select-option') ?> </option>
<?php
		for ($i = 0; $i < sizeof($isos); $i++) {
			if (strlen($isos[$i]) > 0)
				echo "<option value=\"{$isos[$i]}\">{$isos[$i]}</option>";
		}
?>
				</select>
			</tr>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('guest-vcpus') ?>:</td>
			<td class="field">
				<select name="cpu_count">
<?php
	for ($i = 1; $i < ($max + 1); $i++) {
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
?>
				</select>
				(<?php echo $lang->get('host-cpus') ?>: <?php echo $cpus ?>)
			</tr>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('cpu-features') ?>:</td>
			<td class="field-checkbox">
				<input class="checkbox" type="checkbox" value="1" name="feature_apic" checked="checked" /> APIC<br />
				<input class="checkbox" type="checkbox" value="1" name="feature_acpi" checked="checked" /> ACPI<br />
				<input class="checkbox" type="checkbox" value="1" name="feature_pae" checked="checked" /> PAE<br />
				<input class="checkbox" type="checkbox" value="1" name="feature_hap" /> HAP
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('memory-current') ?> (MiB):</td>
			<td class="field"><input type="text" name="guest_memory" id="guest_memory" value="<?php echo $DEFAULT_GUEST_MEM ?>" /></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('memory-max') ?> (MiB):</td>
			<td class="field"><input type="text" name="guest_maxmem" id="guest_maxmem" value="<?php echo $DEFAULT_GUEST_MEM ?>" /></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('clock-offset') ?>:</td>
			<td class="field">
				<select name="clock_offset">
				<option value="utc">UTC</option>
				<option value="localtime">localtime</option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="title"><?php echo $lang->get('vm-soundhw-type') ?>:</td>
			<td class="field">
				<select name="sound_type">
					<option value="none"><?php echo $lang->get('vm-soundhw-type-none') ?></option>';

				<?php
					$models = $lvObject->getSoundHwModels();
					if (is_array($models)) {
						for ($i = 0; $i < sizeof($models); $i++)
							echo '<option value="'.$models[$i]['name'].'">'.$models[$i]['description'].'</option>';
					}
				?>
				</select>
			</td>
		</tr>

		<tr>
			<td class="title"><?php echo $lang->get('setup-nic') ?>:</td>
			<td class="field">
				<select name="setup_nic" onchange="changeDivs('network', this.value)">
				<option value="0"><?php echo $lang->get('no') ?></option>
				<option value="1"><?php echo $lang->get('yes') ?></option>
				</select>
			</td>
		</tr>

		<tr id="setup_network" style="display: none">
			<td>&nbsp;</td>
			<td>
				<table id="connections-edit">
					<tr>
						<td class="title"><?php echo $lang->get('vm-network-mac') ?>:</td>
						<td class="field">
							<input type="text" name="nic_mac" value="<?php echo $lvObject->generateRandomMacAddr() ?>" id="nic_mac_addr" />
							<input type="button" onclick="generateMacAddr()" style="cursor:pointer" value="<?php echo $lang->get('network-generate-mac') ?>">
						</td>
					</tr>
					<tr>
						<td class="title"><?php echo $lang->get('vm-network-type') ?>:</td>
						<td class="field">
							<select name="nic_type">';

							<?php
								$models = $lvObject->getNicModels();
								if (is_array($models)) {
									for ($i = 0; $i < sizeof($models); $i++)
										echo '<option value="'.$models[$i].'">'.$models[$i].'</option>';
								}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="title"><?php echo $lang->get('vm-network-net') ?>:</td>
						<td class="field">
							<select name="nic_net">';

							<?php
								$nets = $lvObject->getNetworks();
								for ($i = 0; $i < sizeof($nets); $i++)
								echo '<option value="'.$nets[$i].'">'.$nets[$i].'</option>';
							?>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td class="title"><?php echo $lang->get('setup-disk') ?>:</td>
			<td class="field">
				<select name="setup_nic" onchange="changeDivs('disk', this.value)">
				<option value="0"><?php echo $lang->get('no') ?></option>
				<option value="1"><?php echo $lang->get('yes') ?></option>
				</select>
			</td>
		</tr>

		<tr id="setup_disk" style="display: none">
			<td>&nbsp;</td>
			<td>
				<table id="connections-edit">
				<tr>
					<td class="title"><?php echo $lang->get('new-vm-disk')?>: </td>
					<td class="field">
						<select name="new_vm_disk" onchange="vmDiskChange(this.value)">
							<option value="0"><?php echo $lang->get('disk-image-use-existing') ?></option>
							<option value="1"><?php echo $lang->get('disk-image-create') ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="title">
						<span id="vm_disk_existing">
							<?php echo $lang->get('disk-image-path')?>:
						</span>
						<span id="vm_disk_create" style="display: none">
							<?php echo $lang->get('disk-image-size') ?> (MiB): 
						</span>
					</td>
					<td class="field">
						<input type="text" name="img_data" />
					</td>
				</tr>
				<tr>
					<td class="title"><?php echo $lang->get('drive-location') ?>: </td>
					<td class="field">
						<select name="disk_bus">
							<option value="ide">IDE Bus</option>
							<option value="scsi">SCSI Bus</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="title"><?php echo $lang->get('disk-image-type') ?>: </td>
					<td class="field">
						<select name="disk_driver">
							<option value="raw">raw</option>
							<option value="qcow">qcow</option>
							<option value="qcow2">qcow2</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="title"><?php echo $lang->get('guest-device') ?>: </td>
					<td class="field">hda</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('persistent') ?>:</td>
			<td class="field">
				<select name="setup_persistent">
					<option value="0"><?php echo $lang->get('no') ?></option>
					<option value="1" selected="selected"><?php echo $lang->get('yes') ?></option>
				</select>
			</td>
		</tr>



		<tr>
			<td class="title">&nbsp;</td>
			<td class="field"><input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('domain-add') ?>" /></td>
			<input type="hidden" name="sent" value="1" />
		</tr>
	</table>

	</form>
<?php
	endif;
?>
