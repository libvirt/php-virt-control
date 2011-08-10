<div id="content">

<div class="section"><?= $lang->get('info') ?></div>

<?php
	if ($errmsg)
		echo '<div id="msg"><b>'.$lang->get('msg').': </b>'.$errmsg.'</div>';
?>

<?= $lang->get('info_msg'); ?>

<div class="section"><?= $lang->get('conns'); ?></div>

<?php
	if (array_key_exists('remove_conn', $_GET))
		$db->remove_connection( (int)$_GET['remove_conn'] );

	$tmp = $db->list_connections(true);

        $spaces = '&nbsp; &nbsp; &nbsp;';
        echo '<table id="form-table">
                          <tr>
                            <th>'.$spaces.$lang->get('connname').$spaces.'</th>
                            <th>'.$spaces.$lang->get('hypervisor').$spaces.'</th>
                            <th>'.$spaces.$lang->get('host_type').$spaces.'</th>
			    <th>'.$spaces.$lang->get('host').$spaces.'</th>
                            <th>'.$spaces.$lang->get('logfile').$spaces.'</th>
			    <th>'.$spaces.$lang->get('actions').$spaces.'</td>
                          </tr>
                ';

	for ($i = 0; $i < sizeof($tmp); $i++) {
		$name = $tmp[$i]['name'];
		$hv = $tmp[$i]['hypervisor'];
		$remote = $tmp[$i]['remote'];
		$method = $tmp[$i]['method'];
		$user = $tmp[$i]['user'];
		$host = $tmp[$i]['host'];
		$logfile = $tmp[$i]['logfile'];
		$id = $tmp[$i]['id'];

		echo '<tr align="center">
			<td>'.$name.'</td>
                        <td>'.$hv.'</td>
                        <td>'.($remote ? $lang->get('type_remote') : $lang->get('type_local')).'</td>
			<td>'.($host ? $host : '-').'</td>
                        <td>'.($logfile ? $logfile : '-').'</td>
			<td>
				<a href="?connect='.$id.'">'.$lang->get('connect').'</a> |
				<a href="?remove_conn='.$id.'">'.$lang->get('conn_remove').'</a>
			</td>
                      </tr>';
	}

	if (sizeof($tmp) == 0)
		echo "<tr align=\"center\"><td colspan=\"6\">".$lang->get('conn_none')."</td></tr>";

	unset($tmp);

	echo '</table>';
?>

<div class="section"><?= $lang->get('conn_setup') ?></div>

<?php
	$nm = array_key_exists('lvcname', $_POST) ? $_POST['lvcname'] : false;
	$hv = array_key_exists('lvchypervisor', $_POST) ? $_POST['lvchypervisor'] : false;
	$rh = array_key_exists('lvcremotehost', $_POST) ? $_POST['lvcremotehost'] : false;
	$rm = array_key_exists('lvcremotemethod', $_POST) ? $_POST['lvcremotemethod'] : false;
	$rp = array_key_exists('lvcrequirepwd', $_POST) ? $_POST['lvcrequirepwd'] : false;
	$un = array_key_exists('lvcusername', $_POST) ? $_POST['lvcusername'] : false;
	$hn = array_key_exists('lvchostname', $_POST) ? $_POST['lvchostname'] : false;
	$lg = array_key_exists('lvclogging', $_POST) ? $_POST['lvclogging'] : false;

	if (array_key_exists('connect', $_GET)) {
		$tmp = $db->list_connections();
		$rid = (int)$_GET['connect'];

		for ($i = 0; $i < sizeof($tmp); $i++) {
			if ($tmp[$i]['id'] == $rid) {
				$id = $tmp[$i]['id'];
        		        $hv = $tmp[$i]['hypervisor'];
                		$rh = $tmp[$i]['remote'];
	                	$rm = $tmp[$i]['method'];
				$rp = $tmp[$i]['require_pwd'];
        	        	$un = $tmp[$i]['user'];
	        	        $hn = $tmp[$i]['host'];
        	        	$lg = $tmp[$i]['logfile'];
			}
		}
		
		unset($tmp);
	}

	$skip_rest = false;
	if ($hv) {
		if ($lv->test_connection_uri($hv, $rh, $rm, $un, $rp, $hn)) {
			$uri = $lv->generate_connection_uri($hv, $rh, $rm, $un, $hn);
			$_SESSION['connection_uri'] = $uri;
			$_SESSION['connection_logging'] = $lg;
			echo '<p>'.$lang->get('changed_uri').' <b>'.$uri.'</b></p>';

			if ((array_key_exists('lvcname', $_POST)) && ($_POST['lvcname']))
				if ($db->add_connection($_POST['lvcname'], $hv, $rh, $rm, $rp, $un, $hn, $lg))
					echo '<p>'.$lang->get('conn_saved').'</p>';

			echo '<a href="?">'.$lang->get('click_reload').'</a>';
			$skip_rest = true;
		}
		else {
			echo '<p>'.$lang->get('conn_failed').': '.$uri.'</p>';
		}
	}

	$ds = ($rh) ? 'table-row' : 'none';
?>

<?php
	if (!$skip_rest):
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
    document.getElementById('remote4').style.display = style;
  }
-->
</script>

<form method="POST">
<table id="form-table">
  <tr>
    <th colspan="2"><?= $lang->get('change_conn') ?></th>
  </tr>
  <tr>
    <td><?= $lang->get('hypervisor') ?>: </td>
    <td align="right">
      <select name="lvchypervisor">
        <option value="xen" <?= ($hv == 'xen') ? ' selected="selected"' : '' ?>>Xen</option>
        <option value="qemu" <?= ($hv == 'qemu') ? ' selected="selected"' : '' ?>>QEMU/KVM</option>
      </select>
    </td>
  </tr>
  <tr>
    <th colspan="2"><?= $lang->get('host_opts') ?></th>
  </tr>
  <tr>
    <td><?= $lang->get('host_type') ?>: </td>
    <td align="right">
      <select name="lvcremotehost" onchange="change_remote(this)">
        <option value="0" <?= ($rh == '0') ? ' selected="selected"' : '' ?>><?= $lang->get('type_local') ?></option>
        <option value="1" <?= ($rh == '1') ? ' selected="selected"' : '' ?>><?= $lang->get('type_remote') ?></option>
      </select>
    </td>
  </tr>
  <tr id="remote1" style="display: <?= $ds ?>">
    <td><?= $lang->get('conn_method') ?>: </td>
    <td align="right">
      <select name="lvcremotemethod">
        <option value="ssh" <?= ($rm == 'ssh') ? ' selected="selected"' : '' ?>>SSH</option>
        <option value="tcp" <?= ($rm == 'tcp') ? ' selected="selected"' : '' ?>>TCP (SASL, Kerberos, ...)</option>
        <option value="tls" <?= ($rm == 'tls') ? ' selected="selected"' : '' ?>>SSL/TLS with certificates</option>
      </select>
    </td>
  </tr>
  <tr id="remote2" style="display: <?= $ds ?>">
    <td><?= $lang->get('user')?>: </td>
    <td align="right">
      <input type="text" name="lvcusername" value="<?= $un ?>" />
    </td>
  </tr>
  <tr id="remote3" style="display: <?= $ds ?>">
    <td><?= $lang->get('password')?>: </td>
    <td align="right">
      <input type="password" name="lvcrequirepwd" value="<?= $rp ?>" />
    </td>
  </tr>
  <tr id="remote4" style="display: <?= $ds ?>">
    <td><?= $lang->get('host') ?>: </td>
    <td align="right">
      <input type="text" name="lvchostname" value="<?= $hn ?>" />
    </td>
  </tr>
  <tr>
    <th colspan="2"><?= $lang->get('log_opts') ?></th>
  </tr>
  <tr>
    <td><?= $lang->get('logfile')?>: </td>
    <td align="right">
      <input type="text" name="lvclogging" value="<?= $lg ?>" title="<?= $lang->get('empty_disable_log') ?>" />
    </td>
  </tr>
  <tr>
    <th colspan="2"><?= $lang->get('save_conn') ?></th>
  </tr>
  <tr>
    <td><?= $lang->get('connname')?>: </td>
    <td align="right">
      <input type="text" name="lvcname" value="<?= $nm ?>" title="<?= $lang->get('empty_disable_save') ?>" />
    </td>
  </tr>
  <tr align="center">
    <td colspan="2">
      <input type="submit" value=" <?= $lang->get('connect_new') ?> " />
    </td>
  </tr>
</table>
</form>

<?php
	endif;
?>

</div>

