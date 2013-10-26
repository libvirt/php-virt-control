<h1><?php echo $lang->get('node-information'); ?></h1>

<?php
	$allowed = true;
	if (!$user->checkUserPermission(USER_PERMISSION_NODE_INFO))
		$allowed = false;

	$skip = false;
	if (!$allowed) {
		$skip = true;
		echo "<div id=\"msg-error\">{$lang->get('permission-denied')}</div><br />";
	}

	if (!$lvObject->isConnected()) {
		echo "<div id=\"msg-error\">{$lang->get('not-connected')}</div><br />";
		$skip = true;
	}

	if (!$skip):
	$moduleInfo = $lvObject->getModuleInfo();
	$connectInfo = $lvObject->getConnectInformation();
	$nodeInfo = $lvObject->hostGetNodeInfo();
	$cpuStats = $lvObject->nodeGetCpuStats();
	$cpuStatsEach = $lvObject->nodeGetCpuStatsEachCPU();
	$memStats = $lvObject->nodeGetMemStats();
	$sysInfo = $lvObject->connectGetSysinfo();
?>

<table id="connections-edit" width="100%">
	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('title-vmc').' '.$lang->get('for-php') ?></td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('version') ?>:</td>
		<td class="field">
			<?php echo PHPVIRTCONTROL_VERSION ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('website') ?>:</td>
		<td class="field">
			<a target="_blank" href="<?php echo PHPVIRTCONTROL_WEBSITE ?>"><?php echo PHPVIRTCONTROL_WEBSITE ?></a>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('connection-details') ?></td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('hypervisor') ?>:</td>
		<td class="field">
			<?php echo (array_key_exists('hypervisor_string', $connectInfo) ? $connectInfo['hypervisor_string'] : '<i>'.$lang->get('error').'</i>' ) ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('conn-uri') ?>:</td>
		<td class="field">
			<?php echo $connectInfo['uri'] ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('hostname') ?>:</td>
		<td class="field">
			<?php echo $connectInfo['hostname'] ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('conn-encrypted') ?>:</td>
		<td class="field">
			<?php echo $lang->get(strtolower($connectInfo['encrypted'])) ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('conn-secure') ?>:</td>
		<td class="field">
			<?php echo $lang->get(strtolower($connectInfo['secure'])) ?>
		</td>
	</tr>
<!--
	/* Commented out as not realiable because of misleading documentation in libvirt API docs */
	<tr>
		<td class="title"><?php echo $lang->get('hypervisor-limit') ?>:</td>
		<td class="field">
			<?php echo $connectInfo['hypervisor_maxvcpus'] ?> vCPUs per guest
		</td>
	</tr>
-->
	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('host-details') ?></td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('model') ?>:</td>
		<td class="field">
			<?php echo $nodeInfo['model'] ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('memory') ?>:</td>
		<td class="field">
			<?php echo (int)($nodeInfo['memory'] / 1024) ?> MiB
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('pcpus') ?>:</td>
		<td class="field">
			<?php echo $nodeInfo['cpus'].' ('.$nodeInfo['nodes'].' nodes, '.$nodeInfo['sockets'].' sockets, '.$nodeInfo['cores'].' cores)' ?>
		</td>
	</tr>
	<tr>
		<td class="title"><?php echo $lang->get('cpu-speed') ?>:</td>
		<td class="field">
			<?php echo $nodeInfo['mhz'] ?> MHz
		</td>
	</tr>
	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('modinfo') ?></td>
	</tr>
		
<?php
		$tmp = explode('</div', str_replace('<div class="nl" />', '', $moduleInfo));

		for ($i = 0; $i < sizeof($tmp) - 2; $i += 3) {
			$k = strip_tags($tmp[$i]);
			$v = strip_tags($tmp[$i+1]);
			if ($k[0] == '>')
				$k = substr($k, 1, strlen($k));
			if ($v[0] == '>')
				$v = substr($v, 1, strlen($v));
			$k = Trim($k);
			$v = Trim($v);

			echo "<tr><td class=\"title\">$k</td><td class=\"field\">$v</td></tr>";
		}
?>

	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('cpu-stats') ?></td>
	</tr>
<?php
	$numCpus = $cpuStats['cpus'];
	if (is_array($cpuStats))
	foreach ($cpuStats as $name => $value) {
		if ($name != 'time')
			echo '<tr>
			<td class="title">'.$name.':</td>
			<td class="field">'.$value.'</td>
			</tr>';
	}
?>

	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('cpu-stats-per-each-cpu') ?></td>
	</tr>


<?php
	foreach ($cpuStatsEach as $k => $value) {
		if (is_numeric($k)) {
			echo '<tr><td class="title">Data:</td><td class="field">';
			//print_r($value);
			foreach ($value as $key => $val) {
				if (is_numeric($key))
					echo 'CPU #'.$key.' = kernel '.$val['kernel'].', user '.$val['user'].', idle '.$val['idle'].', iowait '.$val['iowait'].'<br />';
			}
			echo "</td></tr>";
		}
/*
		else {
			echo '<tr><td class="title">Times:</td><td class="field">';
			echo 'start = '.$value['start'].', finish = '.$value['finish'].', duration = '.$value['duration'];
			echo "</td></tr>";
		}
*/
		echo "</td></tr>";
	}
?>

	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('mem-stats') ?></td>
	</tr>

<?php
	if (is_array($memStats)) foreach ($memStats as $name => $value) {
		echo '<tr>';
		echo '<td class="title">'.$name.'</td>';
		echo '<td class="field">';

		if ($name != 'time')
			echo round($value / 1024).' MiB';
		else
			echo @Date($lang->get('date-format'), $value);

		echo '</td></tr>';
	}
?>
	<tr>
		<td colspan="2" class="section"><?php echo $lang->get('system-information') ?></td>
	</tr>
	<tr>
		<td class="field" colspan="2" style="padding-left: 30px"><pre><?php echo htmlentities($sysInfo); ?></pre></td>
	</tr>
</table>
<?php
	endif;
?>
