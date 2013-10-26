<?php
	require('init.php');

	if (!array_key_exists('hv', $_POST))
		Die(Header('HTTP/1.0 404 Not Found'));

	if (array_key_exists('uri', $_POST))
		$_POST['uri'] = str_replace(' ', '+', $_POST['uri']);

	$res = '';
	if ($_POST['atype'] == 'generate') {
		$lv = new Libvirt(false, false);
		$res = $lv->generateConnectionUri($_POST['hv'], $_POST['host'] ? $_POST['host'] : false, $_POST['method'],
				$_POST['username'], $_POST['host'], false);
	}
	else
	if ($_POST['atype'] == 'test') {
		if (array_key_exists('uri', $_POST)) {
			$lv = new Libvirt(false, false, $_POST['uri'], $_POST['username'], $_POST['password']);
		}
		else {
			$lv = new Libvirt(false, false);
			$res = $lv->testConnectionUri($_POST['hv'], $_POST['host'] ? $_POST['host'] : false, $_POST['method'],
				$_POST['username'], $_POST['host'], $_POST['password'], false);
		}
				
		$res = $lv->getLastError();
	}

	unset($lv);

	echo $res;
?>
