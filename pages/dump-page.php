<?php
	$tIdent = 'dump';

	if ($type == 'domain') {
		$tIdent = 'domain-dump';

		$type = $lang->getText('dump');
		$type = str_replace('%N', $name, $type);
	}
	else
	if ($type == 'network') {
		$tIdent = 'network-dump';

		$type = $lang->getText('dump');
		$type = str_replace('%N', $name, $type);
	}
?>

<h1><?php echo $lang->get($tIdent); ?></h1>

<table id="dump-form">
<tr>
        <td colspan="2" class="field"><?php echo $type ?></td>
</tr>
<tr>
        <td colspan="2">
		<pre><?php echo htmlentities($text) ?></pre>
	</td>
</tr>
<tr>
</tr>
</table>
