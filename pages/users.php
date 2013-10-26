<?php
	$permsC = $user->checkUserPermission(USER_PERMISSION_USER_CREATE);
	$permsE = $user->checkUserPermission(USER_PERMISSION_USER_EDIT);
	$permsD = $user->checkUserPermission(USER_PERMISSION_USER_DELETE);

	$skip = false;
	if ($action == 'edit-assoc') {
		include('users-assoc-edit-form.php');
		$skip = true;
	}

	function translate_permissions($perms) {
		global $lang;

		$str = array();
		if ($perms & USER_PERMISSION_NODE_INFO)
			$str[] = $lang->get('permission-node-info');
		if ($perms & USER_PERMISSION_SAVE_CONNECTION)
			$str[] = $lang->get('permission-save-connection');
		if ($perms & USER_PERMISSION_VM_CREATE)
			$str[] = $lang->get('permission-vm-create');
		if ($perms & USER_PERMISSION_VM_EDIT)
			$str[] = $lang->get('permission-vm-edit');
		if ($perms & USER_PERMISSION_VM_DELETE)
			$str[] = $lang->get('permission-vm-delete');
		if ($perms & USER_PERMISSION_NETWORK_CREATE)
			$str[] = $lang->get('permission-network-create');
		if ($perms & USER_PERMISSION_NETWORK_EDIT)
			$str[] = $lang->get('permission-network-edit');
		if ($perms & USER_PERMISSION_NETWORK_DELETE)
			$str[] = $lang->get('permission-network-delete');
		if ($perms & USER_PERMISSION_USER_CREATE)
			$str[] = $lang->get('permission-user-create');
		if ($perms & USER_PERMISSION_USER_EDIT)
			$str[] = $lang->get('permission-user-edit');
		if ($perms & USER_PERMISSION_USER_DELETE)
			$str[] = $lang->get('permission-user-delete');

		if (empty($str))
			return '-';

		return implode(', ', $str);
	}

	if (!$skip):
?>
<h1><?php echo $lang->get('users'); ?></h1>

<table id="list-form" width="75%">
<tr>
	<th><?php echo $lang->get('username') ?></th>
	<th><?php echo $lang->get('permissions') ?></th>
	<th><?php echo $lang->get('actions') ?></th>
</tr>

<?php
	$users = $user->getUsers();

	for ($i = 0; $i < sizeof($users); $i++) {
		echo '<tr>
			<td>'.$users[$i]['username'].'</td>
			<td>'.translate_permissions($users[$i]['permission_bits']).'</td>
			<td>
			';

			if ($permsE)
				echo '<a href="?page='.$page.'&amp;action=edit&amp;id='.$users[$i]['id'].'">'.$lang->get('user-edit').'</a> ';
			if ($permsD)
				echo '<a href="?page='.$page.'&amp;action=del&amp;id='.$users[$i]['id'].'">'.$lang->get('user-del').'</a> ';
			if ((!$permsE) && (!$permsD))
				echo '-';

			echo '</td></tr>';
	}

	if ($permsC)
		echo "<tr><td colspan=\"3\"><a href=\"?page=$page&amp;action=add\">{$lang->get('user-add')}</a></td></tr>";
?>

</table>

<h1><?php echo $lang->get('my-connection-assoc'); ?></h1>
<table id="list-form" width="75%">
<tr>
        <th><?php echo $lang->get('connection') ?></th>
        <th><?php echo $lang->get('allowed-users') ?></th>
        <th><?php echo $lang->get('actions') ?></th>
</tr>
<?php
        $uconns = $connObj->getByUser();

	for ($i = 0; $i < sizeof($uconns); $i++) {
		$au = implode(', ', $connObj->getAllowedUsers($uconns[$i]['id']));
		if (!$au)
			$au = '-';
		echo '<tr>
			<td>'.$uconns[$i]['name'].'</td>
			<td>'.$au.'</td>
			<td>
				<a href="?page='.$page.'&amp;action=edit-assoc&amp;id='.$uconns[$i]['id'].'">'.$lang->get('user-assoc-edit').'</a>
			</td></tr>';
        }
?>
</table>
<?php
	endif;
?>
