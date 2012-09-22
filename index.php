<?php
	require('init.php');
	$uri = array_key_exists('connection_uri', $_SESSION) ? $_SESSION['connection_uri'] : 'null';
	$lg = array_key_exists('connection_logging', $_SESSION) ? $_SESSION['connection_logging'] : false;

	if ($lg == '')
		$lg = false;

	$errmsg = false;
	if (!CONNECT_WITH_NULL_STRING && $uri == 'null')
		$uri = false;
	if (isset($_SESSION['connection_credentials']))
		$lv = new Libvirt(
			$uri,
			$_SESSION['connection_credentials'][VIR_CRED_AUTHNAME],
			$_SESSION['connection_credentials'][VIR_CRED_PASSPHRASE],
			$lg,
			$lang_str
		);
	else
		$lv = new Libvirt($uri, null, null, $lg, $lang_str);

	$conns = array();
	if (array_key_exists('connections', $_SESSION))
		$conns = $_SESSION['connections'];

	if (array_key_exists('attach', $_GET) && ($_GET['attach'])) {
		$tmp = $db->list_connections(true);
		$rid = (int)$_GET['attach'];

		$new_uri = false;
		for ($i = 0; $i < sizeof($tmp); $i++) {
			if ($tmp[$i]['id'] == $rid) {
				$id = $tmp[$i]['id'];
				$hv = $tmp[$i]['hypervisor'];
				$nm = $tmp[$i]['name'];
				$rh = $tmp[$i]['remote'];
				$rm = $tmp[$i]['method'];
				$rp = $tmp[$i]['require_pwd'];
				$un = $tmp[$i]['user'];
				$pwd= $tmp[$i]['password'];
				$hn = $tmp[$i]['host'];
				$lg = $tmp[$i]['logfile'];
			}
		}

		if ($hv) {
			if ($lv->test_connection_uri($hv, $rh, $rm, $un, $pwd, $hn)) {
				$new_uri = $lv->generate_connection_uri($hv, $rh, $rm, $un, $hn);
				$new_conn = array();
				$new_conn['connection_uri'] = $new_uri;
				$new_conn['connection_name'] = $nm;
				$new_conn['connection_logging'] = $lg;
				$new_conn['id'] = $id;
				if (isset($un) && isset($pwd))
					$new_conn['connection_credentials'] = array(
						VIR_CRED_AUTHNAME => $un,
						VIR_CRED_PASSPHRASE => $pwd
					);
			}
		}

		$skip = false;
		foreach ($conns as $item) {
			if ($item['connection_uri'] == $new_uri)
				$skip = true;
		}

		if (!$skip) {
			$conns[] = $new_conn;
			$_SESSION['connections'] = $conns;
		}

		$_GET['connect'] = $rid;
	}

	if (array_key_exists('detach', $_GET) && ($_GET['detach'])) {
		$tmp = $db->list_connections(true);
		$rid = (int)$_GET['detach'];

		for ($i = 0; $i < sizeof($tmp); $i++) {
			if ($tmp[$i]['id'] == $rid) {
				$id = $tmp[$i]['id'];

				for ($j = 0; $j < sizeof($conns); $j++) {
					if ($conns[$j]['id'] == $id)
						unset($conns[$j]);
				}
			}
		}

		$_SESSION['connections'] = $conns;
	}

	if ($lg && LOGDIR)
		$lg = LOGDIR.'/'.$lg;

	/* Get new MAC address in plain text - called by Ajax from pages/new-vm.php */
	if (array_key_exists('get_mac', $_GET)) {
		die( $lv->generate_random_mac_addr() );
	}
	if (!$lv->enabled() || ($lv->get_last_error())) {
		$page = 'overview';
		$name = false;
		$errmsg = $lang->get('cannot-connect').' '.$lv->get_last_error();
	}
	else {
		$name = array_key_exists('name', $_GET) ? $_GET['name'] : false;
		$res = $lv->get_domain_by_name($name);
		$page = array_key_exists('page', $_GET) ? $_GET['page'] : 'overview';
	}
?>
<html>
<head>
 <title>php-virt-control - <?php echo $lang->get('title-vmc') ?></title>
 <link rel="STYLESHEET" type="text/css" href="html/main.css" />
 <link rel="STYLESHEET" type="text/css" href="manager.css" />
</head>
<body>
  <div id="header">
    <div id="headerLogo"></div>
  </div>

<?php
	include('main-menu.php');
	if ($name):
?>
	<h2 id="vm-name"><?php echo $lang->get('vm-title').' '.$name ?></h2>
<?php
	include('menu.php');
	if (File_Exists('./pages/details/'.$page.'.php'))
		include('./pages/details/'.$page.'.php');
	else
		include('error.php');
	else:
	if (File_Exists('./pages/'.$page.'.php'))
		include('./pages/'.$page.'.php');
	else
		include('error.php');
	endif;
?>

<?php
	if (DEBUG) {
		echo '<div id="content">';
		echo '<div class="section">Debug - Libvirt-php resources</div>';

		$resources = $lv->print_resources();
		for ($i = 0; $i < sizeof($resources); $i++) {
			echo '<div class="item">';
			echo '        <div class="label">Resource #'.($i + 1).'</div>';
			echo '        <div class="value">'.$resources[$i].'</div>';
			echo '        <div class="nl">';
			echo '</div>';
		}

		echo '</div>';
	}
?>

</body>
</html>
