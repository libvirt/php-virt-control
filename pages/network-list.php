<?php
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

	$lvNetwork = new LibvirtNetwork($lv, $lang, $action, $_GET);
	$lvNetworkData = $lvNetwork->getData();

	if ($lvNetworkData) {
		$frm = $lvNetworkData['frm'];
		$msg  = $lvNetworkData['msg'];
		$xml = $lvNetworkData['xml'];
	}
	else
		$frm = $msg = $xml = false;
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

<div class="section"><?php echo $lang->get('network-list') ?></div>

<?php
	if (verify_user($db, USER_PERMISSION_NETWORK_CREATE)):
?>
<div style="padding: 10px; font-size: 14px; font-weight: bold; width: 100%; border: 1px solid grey;margin-bottom: 10px;">
<a href="?page=new-net"><?php echo $lang->get('create-new-network') ?></a>
</div>
<?php
	endif;
?>

<table id="domain-list">
  <tr>
    <th><?php echo $lang->get('name') ?></th>
    <th><?php echo $lang->get('net-ip') ?></th>
    <th><?php echo $lang->get('net-mask') ?></th>
    <th><?php echo $lang->get('net-range') ?></th>
    <th><?php echo $lang->get('net-forward') ?></th>
    <th><?php echo $lang->get('net-dev') ?></th>
    <th><?php echo $lang->get('net-dhcp-range') ?></th>
    <th><?php echo $lang->get('net-active') ?></th>
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
					$dev = $lang->get('net-forward-dev-any');

				$actions = '';
				if (!$active) {
					$actions .= '<a href="?page='.$page.'&amp;action=net-start&amp;net='.$name.'"><img src="graphics/play.png" title="'.$lang->get('net-start').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=net-dumpxml&amp;net='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('net-dumpxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_NETWORK_EDIT))
						$actions .= '<a href="?page='.$page.'&amp;action=net-editxml&amp;net='.$name.'"><img src="graphics/edit.png" title="'.$lang->get('net-editxml').'" /></a> ';
					if (verify_user($db, USER_PERMISSION_NETWORK_DELETE))
						$actions .= '<a href="?page='.$page.'&amp;action=net-undefine&amp;net='.$name.'"><img src="graphics/undefine.png" title="'.$lang->get('net-undefine').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=net-stop&amp;net='.$name.'"><img src="graphics/stop.png" title="'.$lang->get('net-stop').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=net-dumpxml&amp;net='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('net-dumpxml').'" /></a> ';

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
			echo "<tr><td colspan=\"9\">".$lang->get('net-none')."</td></tr>";
	?>
</table>

</div>
