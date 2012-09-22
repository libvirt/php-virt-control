<script language="javascript">
<!--
	function change_lang(lang) {
		location.href = '?lang-override='+lang+'&page=settings';
	}
-->
</script>

<div id="content">

<div class="section"><?php echo $lang->get('settings') ?></div>

<div class="item">
        <div class="label"><?php echo $lang->get('language') ?>: </div>
        <div class="value">
		<select name="language" onchange="change_lang(this.value)">
<?php
	$langs = get_languages();

	for ($i = 0; $i < sizeof($langs); $i++):
?>
			<option value="<?php echo $langs[$i]['code'] ?>" <?php echo ($lang_str == $langs[$i]['code'] ? 'selected="selected"' : '') ?>><?php echo $langs[$i]['name'] ?></option>
<?php
	endfor;
?>
		</select>
	</div>
        <div class="nl">
</div>

<div class="section"><?php echo $lang->get('using-ssh-auth') ?></div>

<?php echo $lang->get('info-apache-key-copy') ?>

</div>
