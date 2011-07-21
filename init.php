<?php
	session_start();

	if (array_key_exists('lang-override', $_GET)) {
		$_SESSION['language'] = $_GET['lang-override'];
		if (array_key_exists('page', $_GET))
			Header('Location: ?page='.$_GET['page']);
		else
			Header('Location: ?');
		exit;
	}

	if (!array_key_exists('language', $_SESSION)) {
		$tmp = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$tmp = explode('-', $tmp[0]);
		$lang_str = $tmp[0];
		unset($tmp);
	}
	else
		$lang_str = $_SESSION['language'];

	define('LOGDIR', getcwd().'/logs');
	define('PHPVIRTCONTROL_VERSION', '0.0.1');
	define('PHPVIRTCONTROL_WEBSITE', 'http://www.php-virt-control.org');
	define('ALLOW_EXPERIMENTAL_VNC', false);

	if (!File_Exists(LOGDIR)) {
		if (!mkdir(LOGDIR, 0777))
			define(LOGDIR, false);
	}

	require('functions.php');
	require('classes/libvirt.php');
	require('classes/language.php');
	require('classes/database.php');
	require('classes/database-file.php');

	$lang = new Language($lang_str);
	$db = getDBObject('file:data/test.dat');
?>
