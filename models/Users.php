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
			'username' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => false
					),
			'password' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => false,
					'comment' => 'In SHA-1 format'
					),
			'email' =>	array(
					'type' => 'varchar',
					'length' => 128,
					'null' => false,
					'comment' => 'E-mail for password recovery purposes'
					),
			'awaiting_recovery_token' => array(
					'type' => 'varchar',
					'length' => 128,
					'null' => true,
					'comment' => 'Token for user awaiting password recovery, NULL if not awaiting',
					'default' => 'NULL'
					),
			'permissions' => array(
					'type' => 'int',
					'null' => false,
					'default' => 0,
					'comment' => 'User\'s permission bits'
					),
			'regUserAgent' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => true,
					'comment' => 'UserAgent used for registration'
					),
			'regFrom' => array(
					'type' => 'bigint',
					'null' => false,
					'default' => 0,
					'comment' => 'Registration date in UNIX timestamp format'
					),
			'lastLogin' => array(
					'type' => 'bigint',
					'null' => false,
					'default' => 0,
					'comment' => 'Last user login in UNIX timestamp format'
					),
			'numLogins' => array(
					'type' => 'int',
					'null' => false,
					'default' => 0,
					'comment' => 'Number of logins'
					),
			'apikey' => array(
					'type' => 'varchar',
					'length' => 255,
					'null' => false,
					'comment' => 'API Key used to access this account, must be unique'
					),
			'lang' => array(
					'type' => 'varchar',
					'length' => 8,
					'null' => false,
					'comment' => 'Language used for the account'
					)
			);
?>
