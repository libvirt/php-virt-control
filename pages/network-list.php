<?php
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

  $frm = '';
  $msg = '';

  if ($action == 'net-start') {
    $name = $_GET['net'];

    $msg = $lv->set_network_active($name, true) ? $lang->get('net_start_ok') :
	   $lang->get('net_start_err').': '.$lv->get_last_error();
  }

  if ($action == 'net-stop') {
    $name = $_GET['net'];

    $msg = $lv->set_network_active($name, false) ? $lang->get('net_stop_ok') :
           $lang->get('net_stop_err').': '.$lv->get_last_error();
  }

  if (($action == 'net-undefine') && (verify_user($db, USER_PERMISSION_NETWORK_CREATE))){
    $name = $_GET['net'];
    if ((!array_key_exists('confirmed', $_GET)) || ($_GET['confirmed'] != 1)) {
        $frm = '<div class="section">'.$lang->get('net_undefine').'</div>
                <table id="form-table">
                <tr>
                  <td colspan="3">'.$lang->get('net_undefine_question').' '.$lang->get('name').': <u>'.$name.'</u></td>
                </tr>
                <tr align="center">
                  <td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1">'.$lang->get('Yes').'</a></td>
                  <td><a href="?page='.$page.'">'.$lang->get('No').'</a></td>
                </td>
                </table>';
    }
    else {
        $msg = $lv->network_undefine($name) ? $lang->get('net_undefine_ok') :
               $lang->get('net_undefine_err').': '.$lv->get_last_error();
    }
  }

  if ($action == 'net-dumpxml') {
    $name = $_GET['net'];

    $xml = $lv->network_get_xml($name);
    $frm = '<div class="section">'.$lang->get('net_xmldesc').' - <i>'.$name.'</i></div><form method="POST">
            <table id="form-table"><tr><td>'.$lang->get('net_xmldesc').': </td>
            <td><textarea readonly="readonly" name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
            </tr></form></table>';
  }

  if (($action == 'net-editxml') && (verify_user($db, USER_PERMISSION_NETWORK_EDIT))) {
    $name = $_GET['net'];

    if (array_key_exists('xmldesc', $_POST)) {
        $msg = $lv->network_change_xml($name, $_POST['xmldesc']) ? $lang->get('net_define_changed') :
               $lang->get('net_define_change_err').': '.$lv->get_last_error();

    }
    else {
        $xml = $lv->network_get_xml($name);
        $frm = '<div class="section">'.$lang->get('net_editxml').' - <i>'.$name.'</i></div><form method="POST"><table id="form-table"><tr><td>'.$lang->get('net_xmldesc').': </td>
             <td><textarea name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
             <input type="submit" value=" '.$lang->get('net_editxml').' "></tr></form></table>';
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

<div class="section"><?php echo $lang->get('network_list') ?></div>

<table id="domain-list">
<?php
	if (verify_user($db, USER_PERMISSION_NETWORK_CREATE)):
?>
  <tr>
    <td colspan="2" align="left">
      <a href="?page=new-net"><?php echo $lang->get('create-new-network') ?></a>
    </td>
  </tr>
<?php
	endif;
?>
  <tr>
    <th><?php echo $lang->get('name') ?></th>
    <th><?php echo $lang->get('net_ip') ?></th>
    <th><?php echo $lang->get('net_mask') ?></th>
    <th><?php echo $lang->get('net_range') ?></th>
    <th><?php echo $lang->get('net_forward') ?></th>
    <th><?php echo $lang->get('net_dev') ?></th>
    <th><?php echo $lang->get('net_dhcp_range') ?></th>
    <th><?php echo $lang->get('net_active') ?></th>
    <th><?php echo $lang->get('actions') ?></th>
  </tr>
<?php
		$nets = $lv->get_networks();
		$num = 0;
		if ($nets) {
			sort($nets);
			for ($i = 0; $i < sizeof($nets); $i++) {
				$name = $nets[$i];
				$netinfo = $lv->get_network_information($name);
				$ip = $netinfo['ip'];
				$mask = $netinfo['netmask'];
				$range = $netinfo['ip_range'];
				$forward = $netinfo['forwarding'];
				$dev = $netinfo['forward_dev'];
				$dhcpinfo = (array_key_exists('dhcp_start', $netinfo)) ? $netinfo['dhcp_start'].' - '.$netinfo['dhcp_end'] : '-';
				$active = $netinfo['active'];
				$active_str = $lang->get( $active ? 'Yes' : 'No' );

				if ($dev == 'any interface')
					$dev = $lang->get('net_forward_dev_any');

				$actions = '';
				if (!$active) {
					$actions .= '<a href="?page='.$page.'&amp;action=net-start&amp;net='.$name.'"><img src="graphics/play.png" title="'.$lang->get('net_start').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=net-dumpxml&amp;net='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('net_dumpxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_NETWORK_EDIT))
						$actions .= '<a href="?page='.$page.'&amp;action=net-editxml&amp;net='.$name.'"><img src="graphics/edit.png" title="'.$lang->get('net_editxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_NETWORK_DELETE))
						$actions .= '<a href="?page='.$page.'&amp;action=net-undefine&amp;net='.$name.'"><img src="graphics/undefine.png" title="'.$lang->get('net_undefine').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=net-stop&amp;net='.$name.'"><img src="graphics/stop.png" title="'.$lang->get('net_stop').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=net-dumpxml&amp;net='.$name.'"><img src="graphics/dump.png title="'.$lang->get('net_dumpxml').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}

				echo "<tr class=";
				if(($i % 2) == 0) echo "odd"; else echo "even";
				echo ">
        	                        <td class=\"name\">
                	                    $name
                        	        </td>
                               	        <td>$ip</td>
                                        <td>$mask</td>
                                        <td>$range</td>
                                        <td>$forward</td>
                                        <td>$dev</td>
                                        <td>$dhcpinfo</td>
                                        <td align=\"center\">$active_str</td>
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
