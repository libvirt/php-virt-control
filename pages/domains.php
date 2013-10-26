<h1><?php echo $lang->get('domains'); ?></h1>

<?php
	$perms = $user->checkUserPermission(USER_PERMISSION_VM_EDIT);
	/* TODO: Implement undefine command */
	$permsD = $user->checkUserPermission(USER_PERMISSION_VM_DELETE);
	$skip = false;
	if (!$lvObject->isConnected())
		$error_msg = $lang->get('not-connected');

	if ($action == 'start') {
		$id = urldecode($_GET['id']);

		if (!$lvObject->domainStart($id))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('domain-started');

		//$skip = true;
	}
	else
	if ($action == 'suspend') {
		$id = urldecode($_GET['id']);

		if (!$lvObject->domainSuspend($id))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('domain-suspended');

		//$skip = true;
	}
	else
	if ($action == 'undefine') {
		if ($permsD) {
			$id = urldecode($_GET['id']);
			if ((array_key_exists('confirm', $_GET)) && ($_GET['confirm'] == 1)) {
				if ($lvObject->domainUndefine($id))
					echo '<div id="msg-error">'.$lang->get('delete-failed').'</div>';
				else
					echo '<div id="msg-info">'.$lang->get('deleted').'</div>';
				
				$skip = true;
			}
			else {
				$name = $id;
				$back = 'domains';
				$type = 'domain';
				include('delete-form.php');
				$skip = true;
			}
		}
		else
			$error_msg = $lang->get('permission-denied');
	}
	else
	if ($action == 'resume') {
		$id = urldecode($_GET['id']);

		if (!$lvObject->domainResume($id))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('domain-resumed');

		//$skip = true;
	}
	else
	if ($action == 'destroy') {
		$id = urldecode($_GET['id']);

		if (!$lvObject->domainDestroy($id))
			$error_msg = $lvObject->getLastError();
		else
			$info_msg = $lang->get('domain-stopped');

		//$skip = true;
	}
	else
	if ($action == 'dump') {
		$id = urldecode($_GET['id']);

		$type = 'domain';
		$text = $lvObject->domainGetXml($id);
		$name = $id;
		include('dump-page.php');
		$skip = true;
	}
	else
	if ($action == 'screenshot') {
		$id = urldecode($_GET['id']);

		include('domain-screenshot.php');

		$skip = true;
	}
	else
	if (($action == 'dom-edit') && ($user->checkUserPermission(USER_PERMISSION_VM_EDIT))) {
		$uri = '?page='.$_GET['page'].'&amp;action='.$_GET['action'].'&amp;id='.$_GET['id'];

		$subpage = array_key_exists('subpage', $_GET) ? $_GET['subpage'] : 'overview';

		$slist = array(
				'overview' => '',
				'cpu' => '',
				'memory' => '',
				'boot' => '',
				'disks' => '',
				'nics' => '',
				'multimedia' => '',
				'host' => ''
				);

		$slist[$subpage] = ' class="active"';

		echo '<div id="domain-menu">
			<a id="m-overview" href="'.$uri.'&subpage=overview#"'.$slist['overview'].' onclick="return changeSection(\'overview\')">'.$lang->get('domain-menu-overview').'</a>
			| <a id="m-cpu" href="'.$uri.'&subpage=cpu#"'.$slist['cpu'].' onclick="return changeSection(\'cpu\')">'.$lang->get('domain-menu-processor').'</a>
			| <a id="m-memory" href="'.$uri.'&subpage=memory#"'.$slist['memory'].' onclick="return changeSection(\'memory\')">'.$lang->get('domain-menu-memory').'</a>
			| <a id="m-boot" href="'.$uri.'&subpage=boot#"'.$slist['boot'].' onclick="return changeSection(\'boot\')">'.$lang->get('domain-menu-boot').'</a>
			| <a id="m-disks" href="'.$uri.'&subpage=disks#"'.$slist['disks'].' onclick="return changeSection(\'disks\')">'.$lang->get('domain-menu-disks').'</a>
			| <a id="m-nics" href="'.$uri.'&subpage=nics#"'.$slist['nics'].' onclick="return changeSection(\'nics\')">'.$lang->get('domain-menu-nics').'</a>
			| <a id="m-multimedia" href="'.$uri.'&subpage=multimedia#"'.$slist['multimedia'].' onclick="return changeSection(\'multimedia\')">'.$lang->get('domain-menu-multimedia').'</a>
			| <a id="m-host" href="'.$uri.'&subpage=host#"'.$slist['host'].' onclick="return changeSection(\'host\')">'.$lang->get('domain-menu-host').'</a>
			</div>
			';

		$vm = $_GET['id'];

		include('domain-edit-start.php');

		$slistk = array_keys($slist);
		for ($xj = 0; $xj < sizeof($slistk); $xj++) {
			$subpage_active = ($slistk[$xj] == $subpage);

			include('domain-'.$slistk[$xj].'.php');
		}

		include('domain-edit-stop.php');

		$skip = true;
	}
	else
	if ($action == 'migrate') {
		if (sizeof($lvObjects) == 1)
			$error_msg = $lang->get('migrate-no-connection');
		else {
			if (array_key_exists('submitted', $_POST) && ($_POST['submitted'] == 1)) {
				$id = urldecode($_GET['id']);

				for ($i = 0; $i < sizeof($lvObjects); $i++) {
					if ($lvObjects[$i]['id'] == $_POST['conn'])
						$destLv = $lvObjects[$i]['obj'];
				}

				if (!$lvObject->migrate($id, $dc, $_POST['live'] ? true : false, $_POST['bandwidth']))
					$error_msg = $lvObject->getLastError();
				else
					$info_msg = $lang->get('domain-migrated');
			}
			else {
				echo '<form method="POST">
				<table id="connections-edit">
					<tr>
						<td class="title">'.$lang->get('connection').': </td>
						<td class="field">
							<select name="conn">';

				for ($i = 0; $i < sizeof($lvObjects); $i++) {
					if ($lvObjects[$i]['id'] != $sess->get('Connection-Last-Attached'))
						echo '<option value="'.$lvObjects[$i]['id'].'">'.$lvObjects[$i]['name'].'</option>';
				}

				echo '
							</select>
						</td>
					</tr>
					<tr>
						<td class="title">
							'.$lang->get('bandwidth').':
						</td>
						<td class="field">
							<input type="text" name="bandwidth" value="100" /> MiB/s
						</td>
					</tr>
					<tr align="center">
						<td class="title2" colspan="2">
							<input type="checkbox" value="1" name="live">
							'.$lang->get('live-migration').'
						</td>
					</tr>
					<tr>
						<td colspan="2" class="submit">
							<input type="submit" value="'.$lang->get('domain-migrate').'" />
						</td>
					</tr>
				</table>
				<input type="hidden" value="1" name="submitted" />
				</form>';

				$skip = true;
			}
		}

		//$skip = true;
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
	<th><?php echo $lang->get('arch') ?></th>
	<th><?php echo $lang->get('vcpus') ?></th>
	<th><?php echo $lang->get('memory') ?></th>
	<th><?php echo $lang->get('disks') ?></th>
	<th><?php echo $lang->get('nics') ?></th>
	<th><?php echo $lang->get('state') ?></th>
	<th><?php echo $lang->get('persistent') ?></th>
	<th><?php echo $lang->get('id') ?></th>
	<th><?php echo $lang->get('graphics-port') ?></th>
	<th><?php echo $lang->get('actions') ?></th>
</tr>
<?php
	$domains = $lvObject->getDomains();

	for ($i = 0; $i < sizeof($domains); $i++) {
		$dres = $lvObject->getDomainObject($domains[$i]);
		if (is_resource($dres)) {
			$info = $lvObject->domainGetInfo($dres);

			$id = $lvObject->domainGetId($dres);
			if (!$id)
				$id = ' - ';

			$graphics = $lvObject->domainGetVncPort($dres);
			if ((!$graphics) || ((int)$graphics == '-1')) {
				$graphics = $lvObject->domainGetSpicePort($dres);
				if ((!$graphics) || ((int)$graphics == '-1'))
					$graphics = ' - ';
				else
					$graphics .= ' (Spice)';
			}
			else
				$graphics .= ' (VNC)';

			$disk = $lvObject->getDiskCount($dres);
			if ($disk)
				$disk .= ' ('.$lvObject->getDiskCapacity($dres).')';
			else
				$disk = $lang->get('diskless');

			$nicAlt = '';
			if ($lvObject->getNetworkCards($dres) > 0) {
				$nicInfo = $lvObject->getNicInfo($dres);
				for ($j = 0; $j < sizeof($nicInfo); $j++)
					$nicAlt .= '#'.($j + 1).') '.$nicInfo[$j]['mac'].' ('.$nicInfo[$j]['network'].')'.($j < (sizeof($nicInfo) - 1) ? '&#013;' : '');
			}

			$diskAlt = '';
			$diskStats = $lvObject->getDiskStats($dres);
			if (!empty($diskStats)) {
				for ($j = 0; $j < sizeof($diskStats); $j++)
					$diskAlt .= '#'.($j + 1).') '.$diskStats[$j]['file'].' ('.$lvObject->formatSize($diskStats[$j]['capacity'], 2).')'.
						($j < (sizeof($diskStats) - 1) ? '&#013;' : '');
			}

			$d = urlencode($domains[$i]);
			$memAlt = $lvObject->formatSize($info['memory'] * 1024, 2).' / max: '.$lvObject->formatSize($info['maxMem'] * 1024, 2);
			echo '<tr><td>';
			if ($perms)
				echo '<a href="?page='.$page.'&amp;action=dom-edit&amp;id='.$d.'">'.$domains[$i].'</a>';
			else
				echo $domains[$i];
			echo '</td><td>'.$lvObject->domainGetArch($dres).'</td>
				<td>'.$info['nrVirtCpu'].'</td>
				<td title="'.$memAlt.'">'.$lvObject->formatSize($info['memory'] * 1024, 2).'</td>
				<td title="'.$diskAlt.'">'.$disk.'</td>
				<td title="'.$nicAlt.'">'.$lvObject->getNetworkCards($dres).'</td>
				<td>'.$lvObject->translateDomainState($info['state']).'</td>
				<td>'.$lang->get($lvObject->domainIsPersistent($dres) ? 'yes' : 'no').'</td>
				<td>'.$id.'</td>
				<td>'.$graphics.'</td>
				<td>';

			if (!$lvObject->domainIsRunning($dres)) {
				if ($info['state'] == 3)
					echo '<a href="?page='.$page.'&amp;action=resume&amp;id='.$d.'"><img src="graphics/play.png" title="'.$lang->get('domain-resume').'" /></a>';
				else
					echo '<a href="?page='.$page.'&amp;action=start&amp;id='.$d.'"><img src="graphics/play.png" title="'.$lang->get('domain-start').'" /></a>';
			}
			else {
				echo '<a href="?page='.$page.'&amp;action=suspend&amp;id='.$d.'"><img src="graphics/pause.png" title="'.$lang->get('domain-suspend').'" /></a>';
				echo '<a href="?page='.$page.'&amp;action=destroy&amp;id='.$d.'"><img src="graphics/stop.png" title="'.$lang->get('domain-destroy').'" /></a>';
				echo '<a href="?page='.$page.'&amp;action=migrate&amp;id='.$d.'"><img src="graphics/migrate.png" title="'.$lang->get('domain-migrate').'" /></a>';
			}

			echo '<a href="?page='.$page.'&amp;action=dump&amp;id='.$d.'"><img src="graphics/dump.png" title="'.$lang->get('domain-dump').'" /></a>';

			if ($perms)
				echo '<a href="?page='.$page.'&amp;action=dom-edit&amp;id='.$d.'"><img src="graphics/edit-small.png" title="'.$lang->get('domain-edit').'" /></a>';

			if ($permsD)
				echo '<a href="?page='.$page.'&amp;action=undefine&amp;id='.$d.'"><img src="graphics/undefine.png" title="'.$lang->get('domain-undefine').'" /></a>';

			if ($lvObject->domainIsRunning($dres))
				echo '<a href="?page='.$page.'&amp;action=screenshot&amp;id='.$d.'"><img src="graphics/screenshot.png" title="'.$lang->get('domain-screenshot').'" /></a>';

			echo '
					</td>
				</tr>';
		}
		else
			echo '<tr><td colspan="11">'.$domains[$i].$lang->get('error').'</td></tr>';

		$dres = $lvObject->resourceUnset($dres);
	}

	/* Make sure even last entries are present */
	$lvObject->resourceUnset($dres);

	if ($user->checkUserPermission(USER_PERMISSION_VM_CREATE))
		echo "<tr><td colspan=\"11\"><a href=\"?page=$page&amp;action=add\">{$lang->get('domain-add')}</a></td></tr>";
?>
</table>
<?php
	endif;
?>
