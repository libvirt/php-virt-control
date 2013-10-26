<?php
	$msg = false;

	$info = array();
	$texts = array();
	$strings = array();

	if (!ENABLE_TRANSLATOR_MODE):
		echo '<div id="msg-error">'.$lang->get('msg').': '.$lang->get('permission-denied').'</div>';
	else:
	if (!empty($_POST)) {
		$err = false;
		$fkey = false;
		while (list($key, $val) = each($_POST['strings'])) {
			if ($val == '')
				$err = true;
		}

		if ($err)
			$msg = $lang->get('translation-missing-string');
		else {
			while (list($key, $val) = each($_POST['texts'])) {
				if ($val == '')
					$err = true;
			}

			if ($err)
				$msg = $lang->get('translation-missing-text');
		}

		if (($_POST['code'] == '') || ($_POST['name'] == '') || ($_POST['tname'] == '') || ($_POST['tmail'] == ''))
			$err = true;

		if (!$err) {
			ob_end_clean();
			$lang->generateLanguageFile($_POST['code'], $_POST['name'], $_POST['tname'], $_POST['tmail'], $_POST);
			exit;
		}

		$texts = $_POST['texts'];
		$strings = $_POST['strings'];
		$info = array(
				'code' => $_POST['code'],
				'name' => $_POST['name'],
				'tname' => $_POST['tname'],
				'tmail' => $_POST['tmail']
				);

	}
?>
<div id="content">

<div class="section"><?php echo $lang->get('translator-mode') ?></div>

<form method="POST">
<?php
	if ($msg)
		echo '<div id="msg-error">'.$msg.'</div>';
?>

<br />

<table id="list-form">
<tr>
<td align="right"><?php echo $lang->get('language-code') ?></td>
<td><input type="text" name="code" value="<?php if (array_key_exists('code', $info)) echo $info['code'] ?>" /></td>
</tr>
<tr>
<td><?php echo $lang->get('language-name') ?></td>
<td><input type="text" name="name" value="<?php if (array_key_exists('name', $info)) echo $info['name'] ?>" /></td>
</tr>
<tr>
<td><?php echo $lang->get('translator-name') ?></td>
<td><input type="text" name="tname" value="<?php if (array_key_exists('tname', $info)) echo $info['tname'] ?>" /></td>
</tr>
<tr>
<td><?php echo $lang->get('translator-email') ?></td>
<td><input type="text" name="tmail" value="<?php if (array_key_exists('tmail', $info)) echo $info['tmail'] ?>" /></td>
</tr>
</table>

<br />

<table id="list-form">
<tr>
<th>#</th>
<th><?php echo $lang->get('translation-for') ?></th>
<th><?php echo $lang->get('translated-string') ?></th>
</tr>
<?php
	$str = $lang->getAllKeys();
	$idx = 1;
	while (list($key, $val) = each($str['strings'])) {
		echo "<tr>";
		echo "<td>$idx</td>";
		echo "<td align=\"right\" style=\"text-align: right\">$val</td> ";
		echo "<td><input type=\"text\" name=\"strings[$key]\" style=\"width: 400px\" value=\"".(array_key_exists($key, $strings) ? $strings[$key] : '')."\" /></td>";
		echo "</tr>";
		$idx++;
	}
?>
</table>

<br />
<br />

<table id="list-form">
<tr>
<th>#</th>
<th><?php echo $lang->get('translation-for-texts') ?></th>
<th><?php echo $lang->get('translated-text') ?></th>
</tr>
<?php
	$idx = 1;
	while (list($key, $val) = each($str['texts'])) {
		echo "<tr>";
		echo "<td>$idx</td>";
		echo "<td align=\"right\" style=\"text-align: right\">$val</td> ";
		echo "<td><textarea name=\"texts[$key]\" style=\"width: 600px\" rows=\"10\"/>".(array_key_exists($key, $texts) ? $texts[$key] : '')."</textarea></td>";
		echo "</tr>";
		$idx++;
	}
?>

<tr>
	<td colspan="3">
		<table id="connections-edit" width="100%">
		<tr align="center">
			<td class="field"><input class="submit" type="submit" value="<?php echo $lang->get('translation-generate') ?>" style="cursor: pointer" /></td>
		</tr>
		</table>
	</td>
</tr>

</table>

</form>

</div>
<?php
	endif;
?>
