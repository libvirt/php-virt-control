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

<div class="section"><?php echo $lang->get('title_vmc').' '.$lang->get('for_php') ?></div>

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

<div class="section"><?php echo $lang->get('conn_details') ?></div>

<?php
	$tmp  = $lv->get_connect_information();
?>

<div class="item">
        <div class="label"><?php echo $lang->get('hypervisor') ?></div>
        <div class="value"><?php echo (array_key_exists('hypervisor_string', $tmp) ? $tmp['hypervisor_string'] : '<i>'.$lang->get('error').'</i>' ) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn_uri') ?></div>
        <div class="value"><?php echo $tmp['uri'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('hostname') ?></div>
        <div class="value"><?php echo $tmp['hostname'] ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn_encrypted') ?></div>
        <div class="value"><?php echo $lang->get($tmp['encrypted']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('conn_secure') ?></div>
        <div class="value"><?php echo $lang->get($tmp['secure']) ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('hypervisor_limit') ?></div>
        <div class="value"><?php echo $tmp['hypervisor_maxvcpus'] ?> vCPUs per guest</div>
        <div class="nl">
</div>

<?php
	unset($tmp);
	$tmp = $lv->host_get_node_info();
?>
<div class="section"><?php echo $lang->get('host_details') ?></div>
<div class="item">
	<div class="label"><?php echo $lang->get('model') ?></div>
	<div class="value"><?php echo $tmp['model'] ?></div>
	<div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('mem') ?></div>
        <div class="value"><?php echo (int)($tmp['memory'] / 1024) ?> MiB</div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('pcpus') ?></div>
        <div class="value"><?php echo $tmp['cpus'].' ('.$tmp['nodes'].' nodes, '.$tmp['sockets'].' sockets, '.$tmp['cores'].' cores)' ?></div>
        <div class="nl">
</div>

<div class="item">
        <div class="label"><?php echo $lang->get('cpu_speed') ?></div>
        <div class="value"><?php echo $tmp['mhz'] ?> MHz</div>
        <div class="nl">
</div>
<?php unset($tmp) ?>
<div class="section"><?php echo $lang->get('modinfo') ?></div>
<?php echo $out ?>
</div>

<div class="section"><?php echo $lang->get('cpu_stats'); ?></div>
<?php
$tmp = $lv->node_get_cpu_stats();
if (is_array($tmp)) foreach ($tmp as $name => $value) {
    echo '<div class="label">'.$name.'</div>';
    echo '<div class="value">'.$value.'</div><div class="nl">';
}
?>
<div style="clear:both"></div>
<div class="section"><?php echo $lang->get('mem_stats'); ?></div>
<?php
$tmp = $lv->node_get_mem_stats();
if (is_array($tmp)) foreach ($tmp as $name => $value) {
    echo '<div class="label">'.$name.'</div>';
    echo '<div class="value">'.$value.'</div><div class="nl">';
}
?>
<div style="clear:both"></div>
<div class="section"><?php echo $lang->get('system_information'); ?></div>
<pre>
<?php
    $tmp = $lv->connect_get_sysinfo();
    echo htmlentities($tmp);
?>
</pre>
