<?php
	if (!isset($type))
		$type = 'entry';

	$q = $lang->getText('delete-question');
	$q = str_replace('%T', $type, $q);
	$q = str_replace('%N', $name, $q);
?>
<h1><?php echo $lang->get($type.'-delete'); ?></h1>

<table id="delete-form">
<tr>
	<td colspan="2"><?php echo $q ?></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="field"><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;confirm=1"><?php echo $lang->get('yes') ?></a></td>
	<td class="field"><a href="?page=<?php echo $back ?>"><?php echo $lang->get('no') ?></a></td>
</tr>
</table>
