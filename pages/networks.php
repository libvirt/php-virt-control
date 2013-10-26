<h1><?php echo $lang->get('networks'); ?></h1>

<?php
	$permsC = $user->checkUserPermission(USER_PERMISSION_NETWORK_CREATE);
	$permsE = $user->checkUserPermission(USER_PERMISSION_NETWORK_EDIT);
	$permsD = $user->checkUserPermission(USER_PERMISSION_NETWORK_DELETE);

	$skip = false;
	if (!$lvObject->isConnected())
		$error_msg = $lang->get('not-connected');

	if ($action == 'start') {
		$id = urldecode($_GET['name']);

		if (!$lvObject->setNetworkActive($id, true))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('network-started');

		//$skip = true;
	}
	else
	if ($action == 'stop') {
		$id = urldecode($_GET['name']);

		if (!$lvObject->setNetworkActive($id, false))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('network-stopped');

		//$skip = true;
	}
	else
	if ($action == 'undefine') {
		if ($permsD) {
			$id = urldecode($_GET['name']);

			if ((array_key_exists('confirm', $_GET)) && ($_GET['confirm'] == 1)) {
				$id = urldecode($_GET['name']);

				if (!$lvObject->networkUndefine($id))
					$error_msg = $lvObject->getLastError();
				else
					$info_msg = $lang->get('network-undefined');
				
				//$skip = true;
			}
			else {
				$info_msg = false;
				$error_msg = false;
				$name = $id;
				$back = 'networks';
				$type = 'networks';
				include('delete-form.php');
				$skip = true;
			}
		}
		else
			$error_msg = $lang->get('permission-denied');

		//$skip = true;
	}
	else
	if ($action == 'dump') {
		$id = urldecode($_GET['name']);

		$type = 'network';
		$text = $lvObject->networkGetXml($id);
		$name = $id;
		include('dump-page.php');
		$skip = true;
	}

	if ($error_msg)
		echo '<div id="msg-error">'.$error_msg.'</div><br />';
	if ($info_msg)
		echo '<div id="msg-info">'.$info_msg.'</div><br />';

	if (($lvObject->isConnected()) && (!$skip)):
?>

<table id="list-form">
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
		$nets = $lvObject->getNetworks();
		$num = 0;
		if ($nets) {
			sort($nets);
			for ($i = 0; $i < sizeof($nets); $i++) {
				$name = $nets[$i];
				$netinfo = $lvObject->getNetworkInformation($name);
				$ip = $netinfo['ip'];
				$mask = $netinfo['netmask'];
				$range = $netinfo['ip_range'];
				$forward = $netinfo['forwarding'];
				$dev = $netinfo['forward_dev'];
				$dhcpinfo = (array_key_exists('dhcp_start', $netinfo)) ? $netinfo['dhcp_start'].' - '.$netinfo['dhcp_end'] : '-';
				$active = $netinfo['active'];
				$active_str = $lang->get( $active ? 'yes' : 'no' );

				if ($dev == 'any interface')
					$dev = $lang->get('net-forward-dev-any');

				$actions = '';
				if (!$active) {
					$actions .= '<a href="?page='.$page.'&amp;action=start&amp;name='.$name.'"><img src="graphics/play.png" title="'.$lang->get('net-start').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=dump&amp;name='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('net-dump').'" /></a> ';
					if ($permsE)
						$actions .= '<a href="?page='.$page.'&amp;action=edit&amp;name='.$name.'"><img src="graphics/edit.png" title="'.$lang->get('net-edit').'" /></a> ';
					if ($permsD)
						$actions .= '<a href="?page='.$page.'&amp;action=undefine&amp;name='.$name.'"><img src="graphics/undefine.png" title="'.$lang->get('net-undefine').'" /></a> ';

					$actions[ strlen($actions) - 2 ] = ' ';
					$actions = Trim($actions);
				}
				else {
					$actions  = '<a href="?page='.$page.'&amp;action=stop&amp;name='.$name.'"><img src="graphics/stop.png" title="'.$lang->get('net-stop').'" /></a> ';
					$actions .= '<a href="?page='.$page.'&amp;action=dump&amp;name='.$name.'"><img src="graphics/dump.png" title="'.$lang->get('net-dump').'" /></a> ';

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

		if ($permsC)
			echo "<tr><td colspan=\"10\"><a href=\"?page=$page&amp;action=add\">{$lang->get('network-add')}</a></td></tr>";
?>
</table>
<?php
	endif;
?>
