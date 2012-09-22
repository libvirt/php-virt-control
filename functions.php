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

	function verify_user($db, $perm=false) {
		if (array_key_exists('logged_in', $_SESSION)) {
			if ($perm != false) {
				if ($_SESSION['user_perms'] == '*')
					return true;
				else
					return ($_SESSION['user_perms'] & $perm) ? true : false;
			}
			else
				return true;
		}

		$logged_in = false;
		if (array_key_exists('user', $_POST)) {
			$perm = $db->verify_user($_POST['user'], $_POST['password']);
			if (is_string($perm)) {
				$logged_in = base64_encode("{$_POST['user']}\n{$_POST['password']}");

				if ($perm == true) {
					$_SESSION['user_perms'] = '*';
					$_SESSION['logged_in'] = $logged_in;
				}
				else {
					$_SESSION['user_perms'] = $perm;
					$_SESSION['logged_in'] = $logged_in;
				}
			}
		}

		return $logged_in;
        }

	function get_languages()
	{
		$dh = opendir('lang');

		if (!$dh)
			return false;

		$langs = array();
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php')) {
				include('lang/'.$file);

				$langs[] = array(
						'name' => $lang_name,
						'code' => $lang_code
						);
			}
		}
		closedir($dh);

		return $langs;
	}
?>
