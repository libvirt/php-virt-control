<h1><?php echo $lang->get('connections'); ?></h1>

<?php
	if ($error_msg)
		echo '<div id="msg-error">'.$error_msg.'</div><br />';
	if ($info_msg)
		echo '<div id="msg-info">'.$info_msg.'</div><br />';
?>

<table id="list-form">
<tr>
	<th><?php echo $lang->get('name') ?></th>
	<th><?php echo $lang->get('hypervisor') ?></th>
	<th><?php echo $lang->get('connection-host') ?></th>
	<th><?php echo $lang->get('connection-username') ?></th>
	<th><?php echo $lang->get('connection-uri') ?></th>
	<th><?php echo $lang->get('connection-logfile') ?></th>
	<th><?php echo $lang->get('creation-date') ?></th>
	<th><?php echo $lang->get('actions') ?></th>
</tr>
<?php
	$perms = $user->checkUserPermission(USER_PERMISSION_SAVE_CONNECTION);
	$cl = $user->getConnections();
	$edit = $lang->get('edit');
	$delete = $lang->get('delete');
	$local = $lang->get('local');
	$df = $lang->get('date-format');
	for ($i = 0; $i < sizeof($cl); $i++) {
		$item = $cl[$i];
		
		if ($item['uri_override'])
			$item['conn_uri'] = $item['uri_override'];
		else
			$item['conn_uri'] = $lvObject->generateConnectionUri($item['hypervisor'], $item['host'] ? true : false,
				$item['method'], $item['username'], $item['host'], false);
		
		if (!$item['host'])
			$item['host'] = $local;
		
		$ak = array_keys($item);
		for ($j = 0; $j < sizeof($ak); $j++)
			if (!$item[$ak[$j]])
				$item[$ak[$j]] = ' - ';
		
		echo '<tr><td>'.$item['name'].'</td><td>'.$item['hypervisor'].'</td><td>'.$item['host'].'</td><td>'.$item['username'].'</td><td>'.
				$item['conn_uri'].'</td><td>'.$item['log_file'].'</td><td>'.@date($df, $item['created']).'</td><td>';
		if ($perms) {
			echo '<a href="?page='.$page.'&amp;action=edit&amp;id='.$item['id'].'"><img src="graphics/edit.png" title="'.$edit.'" /></a>';
			echo '<a href="?page='.$page.'&amp;action=del&amp;id='.$item['id'].'"><img src="graphics/undefine.png" title="'.$delete.'" /></a>';
		}
		else
			echo '-';
		echo '</td></tr>';
	}
	
	if (sizeof($cl) == 0)
		echo '<tr><td colspan="8"><i>'.$lang->get('no-connection').'</i></td></tr>';

	if ($user->checkUserPermission(USER_PERMISSION_SAVE_CONNECTION))
		echo "<tr><td colspan=\"8\"><a href=\"?page=$page&amp;action=add\">{$lang->get('connection-add')}</a></td></tr>";
?>
</table>
