<?php
	define('PHPVIRTCONTROL_VERSION', '0.1.1');
	define('PHPVIRTCONTROL_WEBSITE', 'http://www.php-virt-control.org');
	define('ENABLE_TRANSLATOR_MODE', true);

	require('classes/loggerBase.php');
	require('classes/Security.php');
	require('classes/database.php');
	require('classes/graphics.php');
	require('classes/libvirt.php');
	require('classes/session.php');
	require('classes/XmlRPC.php');
	
	require('classes/EncryptionKey.php');
	require('classes/Application.php');
	require('classes/Connection.php');
	require('classes/Language.php');
	require('classes/User.php');

	/* User permission defines */
	define('USER_PERMISSION_NODE_INFO', 0x01);
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
				'USER_PERMISSION_NODE_INFO'             => 'permission-node-info',
				'USER_PERMISSION_SAVE_CONNECTION'       => 'permission-save-connection',
				'USER_PERMISSION_VM_CREATE'             => 'permission-vm-create',
				'USER_PERMISSION_VM_EDIT'               => 'permission-vm-edit',
				'USER_PERMISSION_VM_DELETE'             => 'permission-vm-delete',
				'USER_PERMISSION_NETWORK_CREATE'        => 'permission-network-create',
				'USER_PERMISSION_NETWORK_EDIT'          => 'permission-network-edit',
				'USER_PERMISSION_NETWORK_DELETE'        => 'permission-network-delete',
				'USER_PERMISSION_USER_CREATE'           => 'permission-user-create',
				'USER_PERMISSION_USER_EDIT'             => 'permission-user-edit',
				'USER_PERMISSION_USER_DELETE'           => 'permission-user-delete',
				);
?>
