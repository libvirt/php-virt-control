<?php
	$_lang_strings = array();
	$dh = opendir('.');
	if ($dh) {
		while (($file = readdir($dh)) !== false) {
			if ((strpos('.'.$file, 'lang_')) && (strpos($file, '.php'))) {
				include($file);

				$_lang_strings[$code] = $strings;
			}
		}
	}
	closedir($dh);

	$_lang_strings_r = array_key_exists($lang, $_lang_strings) ? $_lang_strings[$lang] : $_lang_strings['en'];
	function getString($ident) {
		global $_lang_strings_r;

		return array_key_exists($ident, $_lang_strings_r) ? $_lang_strings_r[$ident] : $ident;
	}
?>
