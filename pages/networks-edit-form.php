<?php
	$allowed = true;
	if ((!$user->checkUserPermission(USER_PERMISSION_NETWORK_CREATE)) && ($action == 'add'))
		$allowed = false;
	if ((!$user->checkUserPermission(USER_PERMISSION_NETWORK_EDIT)) && ($action == 'edit'))
		$allowed = false;

	$skip = false;
	if (!$allowed) {
		$skip = true;
		echo "<div id=\"msg-error\"><b>{$lang->get('msg')}: </b>{$lang->get('permission-denied')}</div>";
	}

	if (!$skip):

	$fwd = 'none';
	$data = array();
	$setup_dhcp = false;
	$ident = 'add';
	if (array_key_exists('name', $_GET)) {
		$id = urldecode($_GET['name']);

		$data = $lvObject->getNetworkInformation($id);
		$setup_dhcp = array_key_exists('dhcp_start', $data) ? true : false;
		$fwd = $data['forwarding'];
		$ident = 'edit';

		/* Hack but good for us */
		$_POST['edit'] = true;
	}

	$msg = false;
	$val = $lvObject->createNewNetwork($_POST);
	if ($val == 2) {
		$msg = $lang->get('network-'.$ident.'-failed').': '.$lvObject->getLastError();
		$isError = true;
	}
	else
	if ($val == 1) {
		$msg = $lang->get('network-'.$ident.'-ok');
		$isError = false;
	}
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

<h1><?php echo $lang->get('domains') ?></h1>

<?php
	if ($msg):
?>
<div id="msg-<?php echo $isError ? 'error' : 'info' ?>"><?php echo $msg ?></div>
<?php
	endif;
?>

	<form method="POST" onsubmit="return check_values()">

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('network-add') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('name') ?>: </td>
			<td class="field"><input type="text" name="name" id="net_name" value="<?php echo array_key_exists('name', $data) ? $data['name'] : '' ?>" /></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('net-ip-range-def') ?>:</td>
			<td class="field">
				<select name="ip_range_cidr" onchange="net_ip_change(this.value)" id="ipdef_val">
				<option value="1"><?php echo $lang->get('net-ip-cidr') ?></option>
				<option value="0"><?php echo $lang->get('net-ip-direct') ?></option>
				</select>
			</td>
		</tr>

		<tr id="net_ip_cidr">
			<td class="title"><?php echo $lang->get('net-ipdef-cidr') ?>:</td>
			<td class="field"><input type="text" name="net_cidr" id="net_cidr" value="<?php echo array_key_exists('ip_range', $data) ? $data['ip_range'] : '' ?>"  /></td>
		</tr>

		<tr id="net_ip_direct" style="display: none">
			<td>&nbsp;</td>
			<td>
				<table id="connections-edit">
					<tr>
						<td class="title"><?php echo $lang->get('net-ip') ?>:</td>
						<td class="field"><input type="text" name="net_ip" id="net_ip" value="<?php echo array_key_exists('ip', $data) ? $data['ip'] : '' ?>"  /></td>
					</tr>
					<tr>
						<td class="title"><?php echo $lang->get('net-mask') ?>:</td>
						<td class="field"><input type="text" name="net_mask" id="net_mask" value="<?php echo array_key_exists('netmask', $data) ? $data['netmask'] : '' ?>" /></td>
					</tr>
				</table>
			</td>
		</tr>

<tr>
		<td class="title"><?php echo $lang->get('net-setup-dhcp') ?>:</td>
		<td class="field">
			<select name="setup_dhcp" onchange="changeDivs('dhcp', this.value)">
				<option value="0" <?php echo ($setup_dhcp == false) ? ' selected="selected"' : '' ?>><?php echo $lang->get('No') ?></option>
				<option value="1" <?php echo ($setup_dhcp == true ) ? ' selected="selected"' : '' ?>><?php echo $lang->get('Yes') ?></option>
			</select>
		</td>
		</tr>
		<tr id="setup_dhcp" <?php echo ($setup_dhcp == false) ? ' style="display: none"' : '' ?>>
			<td>&nbsp;</td>
			<td>
				<table id="connections-edit">
					<tr>
						<td class="title"><?php echo $lang->get('net-dhcp-start') ?>:</td>
						<td class="field">
							<input type="text" name="net_dhcp_start" value="<?php echo array_key_exists('dhcp_start', $data) ? $data['dhcp_start'] : '' ?>"  />
						</td>
					</tr>
					<tr>
						<td class="title"><?php echo $lang->get('net-dhcp-end') ?>:</td>
						<td class="field">
							<input type="text" name="net_dhcp_end" value="<?php echo array_key_exists('dhcp_end', $data) ? $data['dhcp_end'] : '' ?>"  />
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('net-forward') ?>:</td>
			<td class="field">
				<select name="forward">
				<option value="none" <?php echo ($fwd == 'none') ? ' selected="selected"' : '' ?>><?php echo $lang->get('net-forward-none') ?></option>
				<option value="nat" <?php echo ($fwd == 'nat') ? ' selected="selected"' : '' ?>><?php echo $lang->get('net-forward-nat') ?></option>
				<option value="route" <?php echo ($fwd == 'route') ? ' selected="selected"' : '' ?>><?php echo $lang->get('net-forward-route') ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('net-dev') ?>:</td>
			<td class="field">
				<input type="text" name="net_forward_dev" value="<?php echo (array_key_exists('forward_dev', $data) && ($data['forward_dev'] != 'any interface')) ? $data['forward_dev'] : '' ?>"  />
				(<?php echo $lang->get('net-forward-dev-empty-msg') ?>)
			</td>
		</tr>


		<tr>
			<td class="title">&nbsp;</td>
			<td class="field"><input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('network-'.$ident) ?>" /></td>
			<input type="hidden" name="sent" value="1" />
		</tr>
	</table>

	</form>
<?php
	endif;
?>
