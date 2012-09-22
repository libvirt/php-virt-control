<?php
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

	$lvDomain = new LibvirtDomain($lv, $lang, $action, $_GET);
	$lvDomainData = $lvDomain->getData();

	if (is_array($lvDomainData)) {
		$msg = $lvDomainData['msg'];
		$frm = $lvDomainData['frm'];
		$xml = $lvDomainData['xml'];
	}
	else
		$msg = $frm = $xml = false;
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

<div class="section"><?php echo $lang->get('domain-list') ?></div>

<?php
	if (verify_user($db, USER_PERMISSION_VM_CREATE)):
?>
<div style="padding: 10px; font-size: 14px; font-weight: bold; width: 100%; border: 1px solid grey;margin-bottom: 10px;">
<a href="?page=new-vm"><?php echo $lang->get('create-new-vm') ?></a>
</div>
<?php
	endif;
?>

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
					$diskdesc = $lang->get('cur-phys-size').': '.$lv->get_disk_capacity($res, true);
				}
				else {
					$disks = $lang->get('diskless');
					$diskdesc = '';
				}

				$running = $lv->domain_is_running($res, $name);
				if (!$running) {
					$actions  = '<a href="?page='.$page.'&amp;action=domain-start&amp;dom='.$name.'"><img src="graphics/play.png" title="'.$lang->get('dom-start').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('dom-dumpxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_VM_EDIT))
						$actions .= '<a href="?page='.$page.'&amp;action=domain-edit&amp;dom='.$name.'"><img src="graphics/edit.png" title="'.$lang->get('dom-editxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_VM_DELETE))
						$actions .= '<a href="?page='.$page.'&amp;action=domain-undefine&amp;dom='.$name.'"><img src="graphics/undefine.png" title="'.$lang->get('dom-undefine').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=domain-stop&amp;dom='.$name.'"><img src="graphics/stop.png" title="'.$lang->get('dom-stop').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-destroy&amp;dom='.$name.'"><img src="graphics/destroy.png" title="'.$lang->get('dom-destroy').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-dump&amp;dom='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('dom-dumpxml').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=domain-migrate&amp;dom='.$name.'"><img src="graphics/migrate.png" title="'.$lang->get('dom-migrate').'" /></a> ';

					if ($lv->supports('screenshot'))
						$actions .= '<a href="?name='.$name.'&amp;page=screenshot"><img src="graphics/screenshot.png" title="'.$lang->get('dom-screenshot').'" /></a>';

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
			echo "<tr><td colspan=\"9\">".$lang->get('dom-none')."</td></tr>";
	?>
</table>

</div>
