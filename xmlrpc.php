<?php
	error_reporting(0);

	$is_xmlrpc = true;
	require('init.php');

	$rpc = new XmlRPC('file://config_db.php');
	$input = print_r($rpc->getInput(true), 1);
	$output = $rpc->getData();
	unset($rpc);

	$time = time();
	$fp = fopen('tmp/test.tmp', 'a');
	fputs($fp, ">>> $time <<<\n\nUser-Agent: {$_SERVER['HTTP_USER_AGENT']}\nI: $input\nO: $output\n\n");
	fclose($fp);

	echo $output;
?>
