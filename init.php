<?php
	define('DEBUG', false);
	define('LOGDIR', getcwd().'/logs');
	define('LIBVIRT_PHP_REQ_VERSION', '0.4.4');
	define('PHPVIRTCONTROL_VERSION', '0.0.3');
	define('PHPVIRTCONTROL_WEBSITE', 'http://www.php-virt-control.org');
	define('CONNECT_WITH_NULL_STRING', false);
	define('ALLOW_EXPERIMENTAL_VNC', true);

	/* User permission defines */
	define('USER_PERMISSION_BASIC', 0x01);
	define('USER_PERMISSION_SAVE_CONNECTION', 0x02);
	define('USER_PERMISSION_VM_CREATE', 0x04);
	define('USER_PERMISSION_VM_EDIT', 0x08);
	define('USER_PERMISSION_VM_DELETE', 0x10);
	define('USER_PERMISSION_NETWORK_CREATE', 0x20);
	define('USER_PERMISSION_NETWORK_EDIT', 0x40);
	define('USER_PERMISSION_NETWORK_DELETE', 0x80);
	define('USER_PERMISSION_USER_CREATE', 0x100);
	define('USER_PERMISSION_USER_EDIT', 0x200);
	define('USER_PERMISSION_USER_DELETE', 0x400);

	$user_permissions = array(
				'USER_PERMISSION_BASIC'                 => 'permission_basic',
				'USER_PERMISSION_SAVE_CONNECTION'       => 'permission_save_connection',
				'USER_PERMISSION_VM_CREATE'             => 'permission_vm_create',
				'USER_PERMISSION_VM_EDIT'               => 'permission_vm_edit',
				'USER_PERMISSION_VM_DELETE'             => 'permission_vm_delete',
				'USER_PERMISSION_NETWORK_CREATE'        => 'permission_network_create',
				'USER_PERMISSION_NETWORK_EDIT'          => 'permission_network_edit',
				'USER_PERMISSION_NETWORK_DELETE'        => 'permission_network_delete',
				'USER_PERMISSION_USER_CREATE'           => 'permission_user_create',
				'USER_PERMISSION_USER_EDIT'             => 'permission_user_edit',
				'USER_PERMISSION_USER_DELETE'           => 'permission_user_delete',
				);

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
	require('classes/graphics.php');
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
	if (!libvirt_check_version($tmp[0], $tmp[1], $tmp[2], VIR_VERSION_BINDING)) {
		include('error-need-update.php');
		exit;
	}

	/* If connection.php in config dir doesn't exist override to local config dir */
	if (!include('/etc/php-virt-control/connection.php'))
		$cstr = 'mysql:config/mysql-connection.php';

	else $cstr = $type.':/etc/php-virt-control/'.$config;

	$db = getDBObject($cstr);
	$db->init();
	if ($db->has_fatal_error()) {
		include('error-connection-db.php');
		exit;
	}

	if (array_key_exists('action', $_GET) && ($_GET['action'] == 'logout')) {
		unset($_SESSION['logged_in']);
		unset($_SESSION['user_perms']);
	}

	if (!verify_user($db)) {
		include('dialog-login.php');
		exit;
	}
?>
