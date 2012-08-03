<?php
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;
  $msg = '';
  $frm = '';
  if ($action == 'domain-start') {
    $name = $_GET['dom'];
    $msg = $lv->domain_start($name) ? $lang->get('dom_start_ok') :
           $lang->get('dom_start_err').': '.$lv->get_last_error();
  }

  if ($action == 'domain-stop') {
    $name = $_GET['dom'];
    $msg = $lv->domain_shutdown($name) ? $lang->get('dom_shutdown_ok') :
           $lang->get('dom_shutdown_err').': '.$lv->get_last_error();
  }

  if ($action == 'domain-destroy') {
    $name = $_GET['dom'];
    $msg = $lv->domain_destroy($name) ? $lang->get('dom_destroy_ok') :
           $lang->get('dom_destroy_err').': '.$lv->get_last_error();
  }

  if (($action == 'domain-undefine') && (verify_user($db, USER_PERMISSION_VM_DELETE))) {
    $name = $_GET['dom'];
    if ((!array_key_exists('confirmed', $_GET)) || ($_GET['confirmed'] != 1)) {
        $frm = '<div class="section">'.$lang->get('dom_undefine').'</div>
                <table id="form-table">
                <tr>
                  <td colspan="3">'.$lang->get('dom_undefine_question').' '.$lang->get('name').': <u>'.$name.'</u></td>
                </tr>
                <tr align="center">
                  <td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1">'.$lang->get('delete').'</a></td>
                  <td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1&amp;deldisks=1">'.$lang->get('delete_with_disks').'</a></td>
                  <td><a href="?page='.$page.'">'.$lang->get('No').'</a></td>
                </td>
                </table>';
    }
    else {
	$err = '';
	if (array_key_exists('deldisks', $_GET) && $_GET['deldisks'] == 1) {
		$disks = $lv->get_disk_stats($name);

		for ($i = 0; $i < sizeof($disks); $i++) {
			$img = $disks[$i]['file'];

			if (!$lv->remove_image($img, array(2) ))
				$err .= $img.': '.$lv->get_last_error();
		}
	}
        $msg = $lv->domain_undefine($name) ? $lang->get('dom_undefine_ok') :
               $lang->get('dom_undefine_err').': '.$lv->get_last_error();

	if ($err)
		$msg .= ' (err: '.$err.')';
    }
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
        $msg = $lv->domain_change_xml($name, $_POST['xmldesc']) ? $lang->get('dom_define_changed') :
               $lang->get('dom_define_change_err').': '.$lv->get_last_error();

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
    <div id="msg"><b><?php echo $lang->get('msg') ?>: </b><?php echo $msg ?></div>
<?php
    endif;
?>

<?php
    if ($frm)
	echo $frm;
?>

<div class="section"><?php echo $lang->get('domain_list') ?></div>

<?php
        if (verify_user($db, USER_PERMISSION_VM_CREATE)):
?>
<?php
        endif;
?>

<div style="padding: 10px; font-size: 14px; font-weight: bold; width: 100%; border: 1px solid grey;margin-bottom: 10px;">
<a href="?page=new-vm"><?php echo $lang->get('create-new-vm') ?></a>
</div>

<table id="domain-list">
  <tr>
    <th><?php echo $lang->get('name') ?></th>
    <th><?php echo $lang->get('arch') ?></th>
    <th><?php echo $lang->get('vcpus') ?></th>
    <th><?php echo $lang->get('mem') ?></th>
    <th><?php echo $lang->get('disk/s') ?></th>
    <th><?php echo $lang->get('nics') ?></th>
    <th><?php echo $lang->get('state') ?></th>
    <th><?php echo $lang->get('id') ?></th>
    <th><?php echo $lang->get('actions') ?></th>
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
					$actions  = '<a href="?page='.$page.'&amp;action=domain-start&amp;dom='.$name.'"><img src="graphics/play.png" title="'.$lang->get('dom_start').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('dom_dumpxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_VM_EDIT))
						$actions .= '<a href="?page='.$page.'&amp;action=domain-edit&amp;dom='.$name.'"><img src="graphics/edit.png" title="'.$lang->get('dom_editxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_VM_DELETE))
						$actions .= '<a href="?page='.$page.'&amp;action=domain-undefine&amp;dom='.$name.'"><img src="graphics/undefine.png" title="'.$lang->get('dom_undefine').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=domain-stop&amp;dom='.$name.'"><img src="graphics/stop.png" title="'.$lang->get('dom_stop').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-destroy&amp;dom='.$name.'"><img src="graphics/destroy.png" title="'.$lang->get('dom_destroy').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('dom_dumpxml').'" /></a> ';

					if ($lv->supports('screenshot'))
						$actions .= '<a href="?name='.$name.'&amp;page=screenshot"><img src="graphics/screenshot.png" title="'.$lang->get('dom_screenshot').'" /></a>';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}

				echo '<tr class="';
				if (($i % 2) == 0) echo 'odd'; 
				else echo 'even';
				echo '">';
				echo   "<td class=\"name\">
					";

				if (verify_user($db, USER_PERMISSION_VM_EDIT))
					echo "
                	                            <a href=\"?name=$name\">$name</a>";
				else
					echo '<b><i>'.$name.'</i></b>';

				echo "
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
