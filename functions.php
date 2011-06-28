<?php
	function getDBObject($uri) {
		$tmp = explode(':', $uri);

		if (Trim($tmp[0]) == 'file')
			return new DatabaseFile( Trim($tmp[1]) );

		return false;
	}
?>
