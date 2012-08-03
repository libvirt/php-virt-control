<?php
	require('init.php');
	$uri = array_key_exists('connection_uri', $_SESSION) ? $_SESSION['connection_uri'] : 'null';
	$lg = array_key_exists('connection_logging', $_SESSION) ? $_SESSION['connection_logging'] : false;

	if ($lg == '')
		$lg = false;

	if ($lg && LOGDIR)
		$lg = LOGDIR.'/'.$lg;

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

	/* Get new MAC address in plain text - called by Ajax from pages/new-vm.php */
	if (array_key_exists('get_mac', $_GET)) {
		die( $lv->generate_random_mac_addr() );
	}
	if (!$lv->enabled() || ($lv->get_last_error())) {
		$page = 'overview';
		$name = false;
		$errmsg = $lang->get('cannot_connect').' '.$lv->get_last_error();
	}
	else {
		$name = array_key_exists('name', $_GET) ? $_GET['name'] : false;
		$res = $lv->get_domain_by_name($name);
		$page = array_key_exists('page', $_GET) ? $_GET['page'] : 'overview';
	}
?>
<html>
<head>
 <title>php-virt-control - <?php echo $lang->get('title_vmc') ?></title>
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
	<h2 id="vm-name"><?php echo $lang->get('vm_title').' '.$name ?></h2>
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
