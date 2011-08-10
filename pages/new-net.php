<?php
  $skip = false;
  $msg = false;

  if (array_key_exists('sent', $_POST)) {
	if ($_POST['ip_range_cidr'])
		$ipinfo = $_POST['net_cidr'];
	else
		$ipinfo = array('ip' => $_POST['net_ip'], 'netmask' => $_POST['net_mask']);

	$dhcpinfo = ($_POST['setup_dhcp']) ? $_POST['net_dhcp_start'].'-'.$_POST['net_dhcp_end'] : false;

	$tmp = $lv->network_new($_POST['name'], $ipinfo, $dhcpinfo, $_POST['forward'], $_POST['net_forward_dev']);
	if (!$tmp)
		$msg = $lv->get_last_error();
	else {
		$skip = true;
		$msg = $lang->get('net_created');
	}
  }
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

	function net_ip_change(val) {
		if (val == 1) {
			document.getElementById('net_ip_cidr').style.display = 'table-row';
			document.getElementById('net_ip_direct1').style.display = 'none';
			document.getElementById('net_ip_direct2').style.display = 'none';
		} else {
			document.getElementById('net_ip_cidr').style.display = 'none';
			document.getElementById('net_ip_direct1').style.display = 'table-row';
			document.getElementById('net_ip_direct2').style.display = 'table-row';
		}
	}
-->
</script>

<div id="content">

<div class="section"><?= $lang->get('create-new-network') ?></div>

<form method="POST">

<table id="form-table">
<tr>
    <td align="right"><?= $lang->get('name') ?>: </td>
    <td><input type="text" name="name" /></td>
</tr>

<tr>
    <td align="right"><?= $lang->get('net_ip_range_def') ?>:</td>
    <td>
      <select name="ip_range_cidr" onchange="net_ip_change(this.value)">
	<option value="1"><?= $lang->get('net_ip_cidr') ?></option>
	<option value="0"><?= $lang->get('net_ip_direct') ?></option>
      </select>
    </td>
</tr>

<tr id="net_ip_cidr">
    <td align="right"><?= $lang->get('net_ipdef_cidr') ?>:</td>
    <td><input type="text" name="net_cidr" /></td>
</tr>

<tr id="net_ip_direct1" style="display: none">
    <td align="right"><?= $lang->get('net_ip') ?>:</td>
    <td><input type="text" name="net_ip" /></td>
</tr>

<tr id="net_ip_direct2" style="display: none">
    <td align="right"><?= $lang->get('net_mask') ?>:</td>
    <td><input type="text" name="net_mask" /></td>
</tr>

<tr>
    <td align="right"><?= $lang->get('setup').' '.$lang->get('dhcp') ?>:</td>
    <td>
      <select name="setup_dhcp" onchange="change_divs('dhcp', this.value)">
        <option value="0"><?= $lang->get('No') ?></option>
        <option value="1"><?= $lang->get('Yes') ?></option>
      </select>
    </td>
</tr>

<tr id="setup_dhcp1" style="display: none">
    <td align="right"><?= $lang->get('net_dhcp_start') ?>:</td>
    <td>
      <input type="text" name="net_dhcp_start" />
    </td>
</tr>

<tr id="setup_dhcp2" style="display: none">
    <td align="right"><?= $lang->get('net_dhcp_end') ?>:</td>
    <td>
      <input type="text" name="net_dhcp_end" />
    </td>
</tr>

<tr>
    <td align="right"><?= $lang->get('net_forward') ?>:</td>
    <td>
      <select name="forward">
        <option value="none"><?= $lang->get('net_forward_none') ?></option>
        <option value="nat"><?= $lang->get('net_forward_nat') ?></option>
	<option value="route"><?= $lang->get('net_forward_route') ?></option>
      </select>
    </td>
</tr>

<tr>
    <td align="right"><?= $lang->get('net_dev') ?>:</td>
    <td>
      <input type="text" name="net_forward_dev" />
    </td>
</tr>

</div>

<tr align="center">
    <td colspan="2">
    <input type="submit" value=" <?= $lang->get('create-net') ?> " />
    </td>
</tr>

<input type="hidden" name="sent" value="1" />
</form>
</table>

<?php
  endif;
?>

</div>
