<?php
	if (!verify_user($db, USER_PERMISSION_NETWORK_CREATE))
		exit;

	$lvNetwork = new LibvirtNetwork($lv, $lang);
	$lvNetworkData = $lvNetwork->createNewNetwork($_POST);

	if ($lvNetworkData) {
		$skip = $lvNetworkData['skip'];
		$msg  = $lvNetworkData['msg'];
	}
	else
		$skip = $msg = false;
?>

<?php
    if ($msg):
?>
    <div id="msg"><b><?php echo $lang->get('msg') ?>: </b><?php echo $msg ?></div>
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

		name = 'setup_'+what;
		d = document.getElementById(name);
		if (d != null)
			d.style.display = style;
	}

	function net_ip_change(val) {
		if (val == 1) {
			document.getElementById('net_ip_cidr').style.display = 'table-row';
			document.getElementById('net_ip_direct').style.display = 'none';
		} else {
			document.getElementById('net_ip_cidr').style.display = 'none';
			document.getElementById('net_ip_direct').style.display = 'table-row';
		}
	}

	function check_values() {
		if (document.getElementById('net_name').value == '') {
			alert('Network name is not set!');
			return false;
		}

		cidr  = document.getElementById('net_cidr').value;
		bIP   = (document.getElementById('net_ip').value != '');
		bMask = (document.getElementById('net_mask').value != '');

		sCidr = (document.getElementById('ipdef_val').value == 1);
		if (sCidr) {
			if (cidr == '') {
				alert('CIDR definition missing');
				return false;
			}
			if (cidr.indexOf("/") == -1) {
				alert('Invalid CIDR definition');
				return false;
			}
		}

		if (!sCidr && !(bIP && bMask)) {
			if (!bIP)
				alert('No IP address defined!');
			if (!bMask)
				alert('No network mask defined!');
			return false;
		}


		return true;
	}
-->
</script>

<div id="content">

<div class="section"><?php echo $lang->get('create-new-network') ?></div>

<form method="POST" onsubmit="return check_values()">

<table id="form-table">
<tr>
    <td align="right"><?php echo $lang->get('name') ?>: </td>
    <td><input type="text" name="name" id="net_name" /></td>
</tr>

<tr>
    <td align="right"><?php echo $lang->get('net-ip-range-def') ?>:</td>
    <td>
      <select name="ip_range_cidr" onchange="net_ip_change(this.value)" id="ipdef_val">
	<option value="1"><?php echo $lang->get('net-ip-cidr') ?></option>
	<option value="0"><?php echo $lang->get('net-ip-direct') ?></option>
      </select>
    </td>
</tr>

<tr id="net_ip_cidr">
    <td align="right"><?php echo $lang->get('net-ipdef-cidr') ?>:</td>
    <td><input type="text" name="net_cidr" id="net_cidr" /></td>
</tr>

<tr id="net_ip_direct" style="display: none">
    <td>&nbsp;</td>
    <td>
    <table>
	<tr>
	    <td align="right"><?php echo $lang->get('net-ip') ?>:</td>
	    <td><input type="text" name="net_ip" id="net_ip" /></td>
 	</tr>
	<tr>
	    <td align="right"><?php echo $lang->get('net-mask') ?>:</td>
	    <td><input type="text" name="net_mask" id="net_mask" /></td>
        </tr>
    </table>
    <td>
</tr>

<tr>
    <td align="right"><?php echo $lang->get('setup').' '.$lang->get('dhcp') ?>:</td>
    <td>
      <select name="setup_dhcp" onchange="change_divs('dhcp', this.value)">
        <option value="0"><?php echo $lang->get('No') ?></option>
        <option value="1"><?php echo $lang->get('Yes') ?></option>
      </select>
    </td>
</tr>

<tr id="setup_dhcp" style="display: none">
    <td>&nbsp;</td>
    <td>
	<table>
	<tr>
	    <td align="right"><?php echo $lang->get('net-dhcp-start') ?>:</td>
	    <td>
	      <input type="text" name="net_dhcp_start" />
	    </td>
	</tr>
	<tr>
	    <td align="right"><?php echo $lang->get('net-dhcp-end') ?>:</td>
	    <td>
	      <input type="text" name="net_dhcp_end" />
	    </td>
	</tr>
	</table>
</tr>

<tr>
    <td align="right"><?php echo $lang->get('net-forward') ?>:</td>
    <td>
      <select name="forward">
        <option value="none"><?php echo $lang->get('net-forward-none') ?></option>
        <option value="nat"><?php echo $lang->get('net-forward-nat') ?></option>
	<option value="route"><?php echo $lang->get('net-forward-route') ?></option>
      </select>
    </td>
</tr>

<tr>
    <td align="right"><?php echo $lang->get('net-dev') ?>:</td>
    <td>
      <input type="text" name="net_forward_dev" /> (<?php echo $lang->get('net-forward-dev-empty-msg') ?>)
    </td>
</tr>

</div>

<tr align="center">
    <td colspan="2">
    <input type="submit" value=" <?php echo $lang->get('create-net') ?> " />
    </td>
</tr>

<input type="hidden" name="sent" value="1" />
</form>
</table>

<?php
  endif;
?>

</div>
