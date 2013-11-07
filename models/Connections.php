<?php
	$table = array(
			'id' => array(
					'type' => 'bigint',
					'null' => false,
					'options' => array(
							'auto_increment',
							'primary_key'
							)
					),
			'name' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => false,
					'comment' => 'Name of the connection'
					),
			'hypervisor' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => false,
					'comment' => 'Hypervisor string'
					),
			'method' => array(
					'type' => 'varchar',
					'length' => 32,
					'comment' => 'Method to be used for connection'
					),
			'host' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => true,
					'comment' => 'Host for remote connection. Null for local connection.'
					),
			'username' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => true,
					'comment' => 'Username for remote connection'
					),
			'password' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => true,
					'comment' => 'Password for remote connection'
					),
			'log_file' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => true,
					'comment' => 'Log file for connection. Null for no log file.'
					),
			'created' => array(
					'type' => 'bigint',
					'null' => false,
					'comment' => 'Entry creation timestamp'
					),
			'uri_override' => array(
					'type' => 'varchar',
					'length' => 250,
					'null' => true,
					'comment' => 'URI override for the connection'
					),
			'creatorId' => array(
					'type' => 'bigint',
					'null' => false,
					'comment' => 'User ID of creator (foreign key to Users table)'
					)
		);
?>
