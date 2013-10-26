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
	$code = $lang->getCode();
	$langs = $lang->getLanguages();
	
	for ($i = 0; $i < sizeof($langs); $i++)
		echo '<option value="'.$langs[$i]['code'].'" '.($code == $langs[$i]['code'] ? ' selected="selected"' : '').'>'.$langs[$i]['name'].'</option>';
?>
		</select>
	</div>
        <div class="nl">
</div>

<div class="section"><?php echo $lang->get('using-ssh-auth') ?></div>

<?php echo $lang->getText('info-apache-key-copy') ?>

</div>
