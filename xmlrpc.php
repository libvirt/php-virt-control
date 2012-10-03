<?php
	error_reporting(0);

	$is_xmlrpc = true;
	require('init.php');

	$rpc = new XmlRPC($db);
	echo $rpc->getData();
	unset($rpc);
?>
