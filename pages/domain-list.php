<?php
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

  $msg = '';
  $frm = '';
  if ($action == 'domain-start') {
    $name = $_GET['dom'];
    $msg = $lv->domain_start($name) ? 'Domain has been started successfully' :
		'Error while starting domain: '.$lv->get_last_error();
  }

  if ($action == 'domain-stop') {
    $name = $_GET['dom'];
    $msg = $lv->domain_shutdown($name) ? 'Domain command to stop has been completed successfully' :
		'Error while stopping domain: '.$lv->get_last_error();
  }

  if ($action == 'domain-destroy') {
    $name = $_GET['dom'];
    $msg = $lv->domain_destroy($name) ? 'Domain has been destroyed successfully' :
                'Error while destroying domain: '.$lv->get_last_error();
  }

  if ($action == 'domain-dump') {
    $name = $_GET['dom'];

    $inactive = (!$lv->domain_is_running($name)) ? true : false;

    $xml = $lv->domain_get_xml($name, $inactive);
    $frm = '<div class="section">'.$lang->get('dom_xmldesc').' - <i>'.$name.'</i></div><form method="POST">
            <table id="form-table"><tr><td>'.$lang->get('dom_xmldesc').': </td>
            <td><textarea readonly="readonly" name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
            </tr></form></table>';
  }

  if ($action == 'domain-edit') {
    $name = $_GET['dom'];

    $inactive = (!$lv->domain_is_running($name)) ? true : false;

    if (array_key_exists('xmldesc', $_POST)) {
        $msg = $lv->domain_change_xml($name, $_POST['xmldesc']) ? 'Domain definition has been changed' :
                                      'Error changing domain definition: '.$lv->get_last_error();

    }
    else {
        $xml = $lv->domain_get_xml($name, $inactive);
        $frm = '<div class="section">'.$lang->get('dom_editxml').' - <i>'.$name.'</i></div><form method="POST"><table id="form-table"><tr><td>'.$lang->get('dom_xmldesc').': </td>
             <td><textarea name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
             <input type="submit" value=" '.$lang->get('dom_editxml').' "></tr></form></table>';
    }
  }
?>
<div id="content">

<?php
    if ($msg):
?>
    <div id="msg"><b><?= $lang->get('msg') ?>: </b><?= $msg ?></div>
<?php
    endif;
?>

<?php
    if ($frm)
	echo $frm;
?>

<div class="section"><?= $lang->get('domain_list') ?></div>

<table id="domain-list">
  <tr>
    <td colspan="2" align="left">
      <a href="?page=new-vm"><?= $lang->get('create-new-vm') ?></a>
    </td>
  </tr>
  <tr>
    <th><?= $lang->get('name') ?></th>
    <th><?= $lang->get('arch') ?></th>
    <th><?= $lang->get('vcpus') ?></th>
    <th><?= $lang->get('mem') ?></th>
    <th><?= $lang->get('disk/s') ?></th>
    <th><?= $lang->get('nics') ?></th>
    <th><?= $lang->get('state') ?></th>
    <th><?= $lang->get('id') ?></th>
    <th><?= $lang->get('actions') ?></th>
  </tr>
<?php
		$doms = $lv->get_domains();
		$num = 0;
		if ($doms) {
			sort($doms);
			for ($i = 0; $i < sizeof($doms); $i++) {
				$name = $doms[$i];
				$res = $lv->get_domain_object($name);
				$uuid = libvirt_domain_get_uuid_string($res);
				$dom = $lv->domain_get_info($res, $name);
				$mem = number_format($dom['memory'] / 1024, 2, '.', ' ').' MiB';
				$cpu = $dom['nrVirtCpu'];
				$id = $lv->domain_get_id($res, $name);
				$arch = $lv->domain_get_arch($res);
				if (!$id)
					$id = '-';
				$state = $lv->domain_state_translate($dom['state']);
				$nics = $lv->get_network_cards($res);
				if (!$nics)
					$nics = 0;
				if (($diskcnt = $lv->get_disk_count($res)) > 0) {
					$disks = $diskcnt.' / '.$lv->get_disk_capacity($res);
					$diskdesc = $lang->get('cur_phys_size').': '.$lv->get_disk_capacity($res, true);
				}
				else {
					$disks = $lang->get('diskless');
					$diskdesc = '';
				}

				$running = $lv->domain_is_running($res, $name);
				if (!$running) {
					$actions  = '<a href="?page='.$page.'&amp;action=domain-start&amp;dom='.$name.'">'.$lang->get('dom_start').'</a> | ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'">'.$lang->get('dom_dumpxml').'</a> | ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-edit&amp;dom='.$name.'">'.$lang->get('dom_editxml').'</a> | ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=domain-stop&amp;dom='.$name.'">'.$lang->get('dom_stop').'</a> | ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-destroy&amp;dom='.$name.'">'.$lang->get('dom_destroy').'</a> | ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'">'.$lang->get('dom_dumpxml').'</a> | ';

					if ($lv->supports('screenshot'))
						$actions .= '<a href="?name='.$name.'&amp;page=screenshot">'.$lang->get('dom_screenshot').'</a> | ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}

				echo "<tr>
        	                                <td class=\"name\">
                	                            <a href=\"?name=$name\">$name</a>
                        	                </td>
                                	        <td>$arch</td>
                                        <td>$cpu</td>
                                        <td>$mem</td>
                                        <td align=\"center\" title='$diskdesc'>$disks</td>
                                        <td align=\"center\">$nics</td>
                                        <td>$state</td>
                                        <td align=\"center\">$id</td>
                                        <td>
                                           $actions
                                        </td>
                                  </tr>";
				$num++;
	                }
		}

		if ($num == 0)
			echo "<tr><td colspan=\"9\">".$lang->get('dom_none')."</td></tr>";
	?>
</table>

</div>
