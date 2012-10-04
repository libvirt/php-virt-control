<?php
	$lvInfo = new LibvirtInfo($lv, $lang);
	$moduleInfo = $lvInfo->getModuleInfo();
	$connectInfo = $lvInfo->getConnectInfo();
	$nodeInfo = $lvInfo->getNodeInfo();
	$cpuStats = $lvInfo->getCpuStats();
	$cpuStatsEach = $lvInfo->getCpuStatsEachCPU();
	$memStats = $lvInfo->getMemoryStats();
	$sysInfo = $lvInfo->getSystemInfo();
?>

<div id="content">

<div class="section"><?php echo $lang->get('title-vmc').' '.$lang->get('for-php') ?></div>

<div class="item">
        <div class="label"><?php echo $lang->get('version') ?></div>
        <div class="value"><?php echo PHPVIRTCONTROL_VERSION ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('website') ?></div>
        <div class="value"><a target="_blank" href="<?php echo PHPVIRTCONTROL_WEBSITE ?>"><?php echo PHPVIRTCONTROL_WEBSITE ?></a></div>
        <div class="nl">
</div>

<div class="section"><?php echo $lang->get('conn-details') ?></div>

<div class="item">
        <div class="label"><?php echo $lang->get('hypervisor') ?></div>
        <div class="value"><?php echo (array_key_exists('hypervisor_string', $connectInfo) ? $connectInfo['hypervisor_string'] : '<i>'.$lang->get('error').'</i>' ) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn-uri') ?></div>
        <div class="value"><?php echo $connectInfo['uri'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('hostname') ?></div>
        <div class="value"><?php echo $connectInfo['hostname'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn-encrypted') ?></div>
        <div class="value"><?php echo $lang->get($connectInfo['encrypted']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn-secure') ?></div>
        <div class="value"><?php echo $lang->get($connectInfo['secure']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('hypervisor-limit') ?></div>
        <div class="value"><?php echo $connectInfo['hypervisor_maxvcpus'] ?> vCPUs per guest</div>
        <div class="nl">
</div>

<div class="section"><?php echo $lang->get('host-details') ?></div>
<div class="item">
	<div class="label"><?php echo $lang->get('model') ?></div>
	<div class="value"><?php echo $nodeInfo['model'] ?></div>
	<div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('mem') ?></div>
        <div class="value"><?php echo (int)($nodeInfo['memory'] / 1024) ?> MiB</div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('pcpus') ?></div>
        <div class="value"><?php echo $nodeInfo['cpus'].' ('.$nodeInfo['nodes'].' nodes, '.$nodeInfo['sockets'].' sockets, '.$nodeInfo['cores'].' cores)' ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('cpu-speed') ?></div>
        <div class="value"><?php echo $nodeInfo['mhz'] ?> MHz</div>
        <div class="nl">
</div>
<?php unset($tmp) ?>
<div class="section"><?php echo $lang->get('modinfo') ?></div>
<?php echo $moduleInfo ?>
</div>

<div class="section"><?php echo $lang->get('cpu-stats'); ?></div>
<?php
	$numCpus = $cpuStats['cpus'];
	if (is_array($cpuStats)) foreach ($cpuStats as $name => $value) {
		echo '<div class="label">'.$name.'</div>';
		echo '<div class="value">'.$value.'</div><div class="nl">';
	}
?>
<br />
<div class="section"><?php echo $lang->get('cpu-stats-per-each-cpu'); ?></div>
<?php
	foreach ($cpuStatsEach as $k => $value) {
		echo '<div class="label">CPU #'.$k.'</div>';
		echo '<div class="value">';
		foreach ($value as $key => $val) {
			if ($key != 'time')
				echo '<div class="label">'.$key.'</div><div class="value" style="text-align: right">'.$val.'</div>';
		}

		echo '</div><br />';
	}
?>
<div style="clear:both"></div>
<div class="section"><?php echo $lang->get('mem-stats'); ?></div>
<?php
	if (is_array($memStats)) foreach ($memStats as $name => $value) {
		echo '<div class="label">'.$name.'</div>';

		if ($name != 'time')
			echo '<div class="value">'.round($value / 1024).' MiB</div><div class="nl">';
		else
			echo '<div class="value">'.@Date($lang->get('date-format'), $value).'</div><div class="nl">';
	}
?>
<br />
<div style="clear:both"></div>
<div class="section"><?php echo $lang->get('system-information'); ?></div>
<pre>
<?php
	echo htmlentities($sysInfo);
?>
</pre>
<?php
	unset($lvInfo);
	unset($moduleInfo);
	unset($connectInfo);
	unset($nodeInfo);
	unset($cpuStats);
	unset($cpuStatsEach);
	unset($memStats);
	unset($sysInfo);
?>
