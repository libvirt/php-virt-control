<?php
	$tmp = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$tmp = explode('-', $tmp[0]);
	$lang_str = $tmp[0];
	unset($tmp);

	session_start();
	define('LOGDIR', getcwd().'/logs');
	define('PHPVIRTCONTROL_VERSION', '0.0.1');
	define('PHPVIRTCONTROL_WEBSITE', 'http://www.php-virt-control.org');

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
