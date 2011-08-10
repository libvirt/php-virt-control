<?php
	define('DEBUG', true);
	define('LOGDIR', getcwd().'/logs');
	define('LIBVIRT_PHP_REQ_VERSION', '0.4.3');
	define('PHPVIRTCONTROL_VERSION', '0.0.2');
	define('PHPVIRTCONTROL_WEBSITE', 'http://www.php-virt-control.org');
	define('ALLOW_EXPERIMENTAL_VNC', false);

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

	if (!File_Exists(LOGDIR)) {
		if (!mkdir(LOGDIR, 0777))
			define(LOGDIR, false);
	}

	require('functions.php');
	require('classes/libvirt.php');
	require('classes/language.php');
	require('classes/database.php');
	require('classes/database-file.php');
	require('classes/database-mysql.php');

	$lang = new Language($lang_str);

	/* Check for libvirt-php */
	if (!function_exists('libvirt_check_version')) {
		include('error-missing.php');
		exit;
	}

	/* Now check for correct version of libvirt-php */
	$tmp = explode('.', LIBVIRT_PHP_REQ_VERSION);
	if (!libvirt_check_version($tmp[0], $tmp[1], $tmp[2])) {
		include('error-need-update.php');
		exit;
	}

	//$db = getDBObject('file:data/test.dat');
	$db = getDBObject('mysql:data/mysql_conn.php');
	if ($db->has_fatal_error()) {
		include('error-connection-db.php');
		exit;
	}
?>
