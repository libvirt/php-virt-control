<?php
	function get_languages()
	{
		$dh = opendir('lang');

		if (!$dh)
			return false;

		$langs = array();
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php')) {
				include('lang/'.$file);

				$langs[] = array(
						'name' => $lang_name,
						'code' => $lang_code
						);
			}
		}
		closedir($dh);

		return $langs;
	}
?>

<script language="javascript">
<!--
	function change_lang(lang) {
		location.href = '?lang-override='+lang+'&page=settings';
	}
-->
</script>

<div id="content">

<div class="section"><?= $lang->get('settings') ?></div>

<div class="item">
        <div class="label"><?= $lang->get('language') ?>: </div>
        <div class="value">
		<select name="language" onchange="change_lang(this.value)">
<?php
	$langs = get_languages();

	for ($i = 0; $i < sizeof($langs); $i++):
?>
			<option value="<?= $langs[$i]['code'] ?>" <?= ($lang_str == $langs[$i]['code'] ? 'selected="selected"' : '') ?>><?= $langs[$i]['name'] ?></option>
<?php
	endfor;
?>
		</select>
	</div>
        <div class="nl">
</div>

<div class="section"><?= $lang->get('using-ssh-auth') ?></div>

<?= $lang->get('info-apache-key-copy') ?>

</div>
