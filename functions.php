<?php
	function getDBObject($uri) {
		$tmp = explode(':', $uri);

		$proto = Trim($tmp[0]);
		$pdata = Trim($tmp[1]);

		if ($proto == 'file')
			return new DatabaseFile( $pdata );
		else
		if ($proto == 'mysql')
			return new DatabaseMySQL( $pdata );

		return false;
	}
?>
