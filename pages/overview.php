<div id="content">

<div class="section">Connections</div>

<?php
	if ($errmsg)
		echo '<div id="msg"><b>Message: </b>'.$errmsg.'</div>';
?>

<p>This is the virtual machine controller tool written in PHP language. You can manage virtual machines (guests) on your machines using this
web-based controlling interface. For the navigation please use the upper menu and select the domain from the <i>Domain list</i> link to see
the virtual machines available on the current machine. You can also see the information about the hypervisor connection, host machine and
libvirt PHP module (used by this system) on the <i>Information</i> page.</p>

<p>The hypervisor on the machine running Apache with PHP is being probed automatically if applicable however you can override the definition
to connect to any other hypervisor on remote machine. To achieve this you need to select a connection and change the host using the form
below. If you experience any issues (e.g. not working connectivity to SSH-based remote host) please make sure you're having all the prerequisites
met. For more reference please check <a href="http://libvirt.org/auth.html" target="_blank">libvirt authentication documentation.</a></p>

<?php
	$hv = array_key_exists('lvchypervisor', $_POST) ? $_POST['lvchypervisor'] : false;
	$rh = array_key_exists('lvcremotehost', $_POST) ? $_POST['lvcremotehost'] : false;
	$rm = array_key_exists('lvcremotemethod', $_POST) ? $_POST['lvcremotemethod'] : false;
	$un = array_key_exists('lvcusername', $_POST) ? $_POST['lvcusername'] : false;
	$hn = array_key_exists('lvchostname', $_POST) ? $_POST['lvchostname'] : false;
	$lg = array_key_exists('lvclogging', $_POST) ? $_POST['lvclogging'] : false;

	if ($hv) {
		$uri = $lv->generate_connection_uri($hv, $rh, $rm, $un, $hn);
		$test = libvirt_connect($uri);
		$ok = is_resource($test);
		unset($test);

		if ($ok) {
			$_SESSION['connection_uri'] = $uri;
			$_SESSION['connection_logging'] = $lg;
			echo '<p>Changed connection URI to <b>'.$uri.'</b></p>';

			echo '<a href="?">Click here to reload and connect using new URI</a>';
			die('</div>');
		}
		else {
			echo '<p>Connection to <b>'.$uri.'</b> failed. Not changing URI...</p>';
		}
	}

	$ds = ($rh) ? 'table-row' : 'none';
?>

<p />

<script language="javascript">
<!--
  function change_remote(el) {
    val = el.value;

    if (val == 0)
      style = 'none';
    else
      style = 'table-row';

    document.getElementById('remote1').style.display = style;
    document.getElementById('remote2').style.display = style;
    document.getElementById('remote3').style.display = style;
  }
-->
</script>

<form method="POST">
<table id="form-table">
  <tr>
    <th colspan="2">Change host connection</th>
  </tr>
  <tr>
    <td>Hypervisor: </td>
    <td align="right">
      <select name="lvchypervisor">
        <option value="xen" <?= ($hv == 'xen') ? ' selected="selected"' : '' ?>>Xen</option>
        <option value="qemu" <?= ($hv == 'qemu') ? ' selected="selected"' : '' ?>>QEMU/KVM</option>
      </select>
    </td>
  </tr>
  <tr>
    <th colspan="2">Host options</th>
  </tr>
  <tr>
    <td>Host type: </td>
    <td align="right">
      <select name="lvcremotehost" onchange="change_remote(this)">
        <option value="0" <?= ($rh == '0') ? ' selected="selected"' : '' ?>>Local host</option>
        <option value="1" <?= ($rh == '1') ? ' selected="selected"' : '' ?>>Remote host</option>
      </select>
    </td>
  </tr>
  <tr id="remote1" style="display: <?= $ds ?>">
    <td>Connection method: </td>
    <td align="right">
      <select name="lvcremotemethod">
        <option value="ssh" <?= ($rm == 'ssh') ? ' selected="selected"' : '' ?>>SSH</option>
        <option value="tcp" <?= ($rm == 'tcp') ? ' selected="selected"' : '' ?>>TCP (SASL, Kerberos, ...)</option>
        <option value="tls" <?= ($rm == 'tls') ? ' selected="selected"' : '' ?>>SSL/TLS with certificates</option>
      </select>
    </td>
  </tr>
  <tr id="remote2" style="display: <?= $ds ?>">
    <td>User name: </td>
    <td align="right">
      <input type="text" name="lvcusername" value="<?= $un ?>" />
    </td>
  </tr>
  <tr id="remote3" style="display: <?= $ds ?>">
    <td>Host: </td>
    <td align="right">
      <input type="text" name="lvchostname" value="<?= $hn ?>" />
    </td>
  </tr>
  <tr>
    <th colspan="2">Logging options</th>
  </tr>
  <tr>
    <td>Log file: </td>
    <td align="right">
      <input type="text" name="lvclogging" value="<?= $lg ?>" title="Leave empty to disable logging" />
    </td>
  </tr>
  <tr align="center">
    <td colspan="2">
      <input type="submit" value=" Connect to the new host " />
    </td>
  </tr>
</table>
</form>

</div>

