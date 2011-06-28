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

<div class="section"><?= $lang->get('title_vmc').' '.$lang->get('for_php') ?></div>

<div class="item">
        <div class="label"><?= $lang->get('version') ?></div>
        <div class="value"><?= PHPVIRTCONTROL_VERSION ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('website') ?></div>
        <div class="value"><a target="_blank" href="<?= PHPVIRTCONTROL_WEBSITE ?>"><?= PHPVIRTCONTROL_WEBSITE ?></a></div>
        <div class="nl">
</div>

<div class="section"><?= $lang->get('conn_details') ?></div>

<?php
	$tmp  = $lv->get_connect_information();
?>

<div class="item">
        <div class="label"><?= $lang->get('hypervisor') ?></div>
        <div class="value"><?= $tmp['hypervisor_string'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('conn_uri') ?></div>
        <div class="value"><?= $tmp['uri'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('hostname') ?></div>
        <div class="value"><?= $tmp['hostname'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('conn_encrypted') ?></div>
        <div class="value"><?= $lang->get($tmp['encrypted']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('conn_secure') ?></div>
        <div class="value"><?= $lang->get($tmp['secure']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('hypervisor_limit') ?></div>
        <div class="value"><?= $tmp['hypervisor_maxvcpus'] ?> vCPUs per guest</div>
        <div class="nl">
</div>

<?php
	unset($tmp);
	$tmp = $lv->host_get_node_info();
?>
<div class="section"><?= $lang->get('host_details') ?></div>
<div class="item">
	<div class="label"><?= $lang->get('model') ?></div>
	<div class="value"><?= $tmp['model'] ?></div>
	<div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('mem') ?></div>
        <div class="value"><?= (int)($tmp['memory'] / 1024) ?> MiB</div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('pcpus') ?></div>
        <div class="value"><?= $tmp['cpus'].' ('.$tmp['nodes'].' nodes, '.$tmp['sockets'].' sockets, '.$tmp['cores'].' cores)' ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?= $lang->get('cpu_speed') ?></div>
        <div class="value"><?= $tmp['mhz'] ?> MHz</div>
        <div class="nl">
</div>
<?php unset($tmp) ?>
<div class="section"><?= $lang->get('modinfo') ?></div>
<?= $out ?>

</div>
