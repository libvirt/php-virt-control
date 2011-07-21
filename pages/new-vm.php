<?php
  $skip = false;
  $msg = false;
  if (array_key_exists('sent', $_POST)) {
	$features = array('apic', 'acpi', 'pae', 'hap');

	$iso_path = ini_get('libvirt.iso_path');

	$img = $iso_path.'/'.$_POST['install_img'];

	$feature = array();
	for ($i = 0; $i < sizeof($features); $i++)
		if (array_key_exists('feature_'.$features[$i], $_POST))
			$feature[] = $features[$i];

	$nic = array();
	if ($_POST['setup_nic']) {
		$nic['mac'] = $_POST['nic_mac'];
		$nic['type'] = $_POST['nic_type'];
		$nic['network'] = $_POST['nic_net'];
	}
	$disk = array();
	if ($_POST['setup_disk']) {
		if ($_POST['new_vm_disk']) {
			$disk['image'] = $_POST['name'].'.'.$_POST['disk_driver'];
			$disk['size'] = (int)$_POST['img_data'];
			$disk['bus'] = $_POST['disk_bus'];
			$disk['driver'] = $_POST['disk_driver'];
		}
		else {
			$disk['image'] = $_POST['img_data'];
			$disk['size'] = 0;
			$disk['bus'] = $_POST['disk_bus'];
			$disk['driver'] = $_POST['disk_driver'];
		}
	}

	$tmp = $lv->domain_new($_POST['name'], $img, $_POST['cpu_count'], $feature, $_POST['memory'], $_POST['maxmem'], $_POST['clock_offset'], $nic, $disk);
	if (!$tmp)
		$msg = $lv->get_last_error();
	else {
		$skip = true;
		$msg = 'Domain has been created successfully';
	}
  }

  $isos = libvirt_get_iso_images();

  $ci  = $lv->get_connect_information();
  $maxcpu = $ci['hypervisor_maxvcpus'];
  unset($ci);
?>

<?php
    if ($msg):
?>
    <div id="msg"><b><?= $lang->get('msg') ?>: </b><?= $msg ?></div>
<?php
    endif;
?>

<?php
    if (!$skip):
?>
<script>
<!--
	function change_divs(what, val) {
		if (val == 1)
			style = 'table-row';
		else
			style = 'none';

		for (i = 1; i < 10; i++) {
			name = 'setup_'+what+i;
			d = document.getElementById(name);
			if (d == null)
				break;
			d.style.display = style;
		}
	}

	function vm_disk_change(val) {
		if (val == 0) {
			document.getElementById('vm_disk_existing').style.display = 'inline';
			document.getElementById('vm_disk_create').style.display = 'none';
		} else {
			document.getElementById('vm_disk_existing').style.display = 'none';
			document.getElementById('vm_disk_create').style.display = 'inline';
		}
	}
-->
</script>

<div id="content">

<div class="section"><?= $lang->get('create-new-vm') ?></div>

<form method="POST">

<table id="form-table">
<tr>
    <td align="right"><?= $lang->get('name') ?>: </td>
    <td><input type="text" name="name" /></td>
</tr>

<tr>
    <td align="right"><?= $lang->get('install-image') ?>: </td>
    <td>
		<select name="install_img">
<?php
		for ($i = 0; $i < sizeof($isos); $i++)
			echo "<option value=\"{$isos[$i]}\">{$isos[$i]}</option>";
?>
		</select>
	</td>

<tr>
    <td align="right"><?= $lang->get('vcpus') ?>: </td>
    <td>
		<select name="cpu_count">
<?php
        for ($i = 1; $i <= $maxcpu; $i++)
            echo '<option value='.$i.'>'.$i.'</option>';
?>
		</select>
</td>

<tr>
    <td align="right"><?= $lang->get('features') ?>:</td>
    <td>
        <input class="checkbox" type="checkbox" value="1" name="feature_apic" checked="checked" /> APIC<br />
        <input class="checkbox" type="checkbox" value="1" name="feature_acpi" checked="checked" /> ACPI<br />
        <input class="checkbox" type="checkbox" value="1" name="feature_pae" checked="checked" /> PAE<br />
        <input class="checkbox" type="checkbox" value="1" name="feature_hap" /> HAP
    </td>
</tr>

<tr>
    <td align="right"><?= $lang->get('mem') ?> (MiB):</td>
    <td><input type="text" name="memory" value="512" /></td>
</tr>

<tr>
    <td align="right"><?= $lang->get('mem_alloc_max') ?> (MiB):</td>
    <td><input type="text" name="maxmem" value="512" /></td>
</tr>

<tr>
    <td align="right"><?= $lang->get('clock-offset') ?>:</td>
    <td>
        <select name="clock_offset">
          <option value="utc">UTC</option>
          <option value="localtime">localtime</option>
        </select>
    </td>
</tr>

<tr>
    <td align="right"><?= $lang->get('setup').' '.$lang->get('nic') ?>:</td>
    <td>
      <select name="setup_nic" onchange="change_divs('network', this.value)">
	<option value="0"><?= $lang->get('No') ?></option>
	<option value="1"><?= $lang->get('Yes') ?></option>
      </select>
    </td>
</tr>

<tr id="setup_network1" style="display: none">
    <td align="right"><?= $lang->get('vm_network_mac') ?>:</td>
    <td><input type="text" name="nic_mac" value="<?= $lv->generate_random_mac_addr() ?>"/></td>
</tr>

<tr id="setup_network2" style="display: none">
    <td align="right"><?= $lang->get('vm_network_type') ?>:</td>
    <td>
                      <select name="nic_type">';

<?php
	$models = $lv->get_nic_models();
        for ($i = 0; $i < sizeof($models); $i++)
                echo '<option value="'.$models[$i].'">'.$models[$i].'</option>';
?>
                      </select>
    </td>
</tr>

<tr id="setup_network3" style="display: none">
    <td align="right"><?= $lang->get('vm_network_net') ?>:</td>
    <td>
                      <select name="nic_net">';

<?php
        $nets = $lv->get_networks();
        for ($i = 0; $i < sizeof($nets); $i++)
                echo '<option value="'.$nets[$i].'">'.$nets[$i].'</option>';
?>
                      </select>
    </td>
</tr>

<tr>
    <td align="right"><?= $lang->get('setup').' '.$lang->get('disk') ?>:</td>
    <td>
      <select name="setup_disk" onchange="change_divs('disk', this.value)">
        <option value="0"><?= $lang->get('No') ?></option>
        <option value="1"><?= $lang->get('Yes') ?></option>
      </select>
    </td>
</tr>

<tr id="setup_disk1" style="display: none">
        <td align="right"><?= $lang->get('new-vm-disk')?>: </td>
        <td>
		<select name="new_vm_disk" onchange="vm_disk_change(this.value)">
			<option value="0"><?= $lang->get('new-vm-existing') ?></option>
			<option value="1"><?= $lang->get('new-vm-create') ?></option>
		</select>
	</td>
</tr>

<tr id="setup_disk2" style="display: none">
	<td align="right">
		<span id="vm_disk_existing">
		<?= $lang->get('vm_disk_image')?>:
		</span>
		<span id="vm_disk_create" style="display: none">
		<?= $lang->get('vm-disk-size') ?> (MiB): 
		</span>
	</td>
	<td><input type="text" name="img_data" /></td>
</tr>

<tr id="setup_disk3" style="display: none">
	<td align="right"><?= $lang->get('vm_disk_location') ?>: </td>
	<td>
		<select name="disk_bus">
			<option value="ide">IDE Bus</option>
			<option value="scsi">SCSI Bus</option>
		</select>
	</td>
</tr>
<tr id="setup_disk4" style="display: none">
	<td align="right"><?= $lang->get('vm_disk_type') ?>: </td>
	<td>
		<select name="disk_driver">
			<option value="raw">raw</option>
			<option value="qcow">qcow</option>
			<option value="qcow2">qcow2</option>
		</select>
	</td>
</tr>
<tr id="setup_disk5" style="display: none">
	<td align="right"><?= $lang->get('vm_disk_dev') ?>: </td>
	<td>hda</td>
</tr>

</div>

<tr align="center">
    <td colspan="2">
    <input type="submit" value=" <?= $lang->get('create-vm') ?> " />
    </td>
</tr>

<input type="hidden" name="sent" value="1" />
</form>

<?php
  else:
?>
  <br /><a href="?name=<?= $_POST['name'] ?>"><?= $lang->get('vm_details') ?></a>
<?php
  endif;
?>

</div>
