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
			'idUser' => array(
					'type' => 'bigint',
					'null' => false,
					'comment' => 'User ID as foreign key to Users table'
					),
			'timestamp' => array(
					'type' => 'bigint',
					'null' => false,
					'default' => 0,
					'comment' => 'Login timestamp in UNIX format'
					),
			'userAgent' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => true,
					'comment' => 'UserAgent used for registration'
					)
			);
?>