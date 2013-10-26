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
					'comment' => 'Foreign key to table Users'
					),
			'idConnection' => array(
					'type' => 'bigint',
					'comment' => 'Foreign key to table Connections'
					),
			'createdUser' => array(
					'type' => 'bigint',
					'null' => false,
					'comment' => 'idUser of entry creation'
					),
			'created' => array(
					'type' => 'bigint',
					'null' => false,
					'comment' => 'Entry creation timestamp'
					)
		);
?>
