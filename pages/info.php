<?php
	ob_start();
	PHPInfo();
	$c = ob_get_contents();
	ob_end_clean();

	$c = substr($c, strpos($c, 'module_libvirt'));
        $c = substr($c, strpos($c, 'h2') + 3);

	$p = strpos($c, 'module') - 3;
	$out = substr($c, 0, $p);

        $out = str_replace('<tr>', '<div class="item">', $out);
	$out = str_replace('<td class="e">', '<div class="label">', $out);
	$out = str_replace('</td><td class="v">', '</div><div class="value">', $out);
	$out = str_replace('</td></tr>', '</div><div class="nl" /></div>', $out);

	$tmp = explode("\n", $out);
	$start_el = false;
	$last_el = false;
	for ($i = 0; $i < sizeof($tmp); $i++) {
		if (strpos('.'.$tmp[$i], '</table'))
			$last_el = $i;
		if (strpos('.'.$tmp[$i], '<table'))
			$start_el = $i + 1;
	}

	$tmp2 = array();
	for ($i = $start_el; $i < $last_el; $i++)
		$tmp2[] = $tmp[$i];
	unset($tmp);

	$out = join("\n", $tmp2);
?>

<div id="content">

<div class="section">Virtual machine control for PHP</div>

<div class="item">
        <div class="label">Version</div>
        <div class="value"><?= PHPVIRTCONTROL_VERSION ?></div>
        <div class="nl">
</div>

<div class="section">Connection details</div>

<?php
	$tmp  = $lv->get_connect_information();
?>

<div class="item">
        <div class="label">Hypervisor</div>
        <div class="value"><?= $tmp['hypervisor_string'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">Connection URI</div>
        <div class="value"><?= $tmp['uri'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">Hostname</div>
        <div class="value"><?= $tmp['hostname'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">Encrypted</div>
        <div class="value"><?= $tmp['encrypted'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">Secure</div>
        <div class="value"><?= $tmp['secure'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">Hypervisor limit</div>
        <div class="value"><?= $tmp['hypervisor_maxvcpus'] ?> vCPUs per guest</div>
        <div class="nl">
</div>

<?php
	unset($tmp);
	$tmp = $lv->host_get_node_info();
?>
<div class="section">Host machine details</div>
<div class="item">
	<div class="label">Model</div>
	<div class="value"><?= $tmp['model'] ?></div>
	<div class="nl">
</div>

<div class="item">
        <div class="label">Memory</div>
        <div class="value"><?= (int)($tmp['memory'] / 1024) ?> MiB</div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">CPUs/cores</div>
        <div class="value"><?= $tmp['cpus'].' ('.$tmp['nodes'].' nodes, '.$tmp['sockets'].' sockets, '.$tmp['cores'].' cores)' ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label">CPU Speed</div>
        <div class="value"><?= $tmp['mhz'] ?> MHz</div>
        <div class="nl">
</div>
<?php unset($tmp) ?>
<div class="section">libvirt PHP module information</div>
<?= $out ?>

</div>
