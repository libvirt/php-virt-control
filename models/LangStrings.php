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
			'lang' => array(
					'type' => 'varchar',
					'length' => 8,
					'null' => false,
					'comment' => 'Language code'
					),
			'ident' => array(
					'type' => 'varchar',
					'length' => 64,
					'null' => false,
					'comment' => 'Identifier string'
					),
			'value' => array(
					'type' => 'varchar',
					'length' => 255,
					'comment' => 'Value of localized string'
					)
		);
?>
