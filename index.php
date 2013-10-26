<?php
	define('DEBUG', true);
	require('init.php');
	
	$config = 'file://config_db.php';
	$sess = new Session();
	$user = new User($config);
	if (array_key_exists('wusername', $_POST)) {
		if (($id = $user->login($_POST['wusername'], $_POST['wpassword'])))
			$sess->set('User', $id);
	}
	
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;
	$langOverride = array_key_exists('lang-override', $_GET) ? $_GET['lang-override'] : false;
	if ($action == 'logout') {
		if ($user->logout($sess))
			Die(Header('Location: ?'));
	}

	if (array_key_exists('renew_hash', $_GET)) {
		$user->confirmPasswordReset($_GET['username'], $_GET['renew_hash']);
	}

	$lang = new Language($config, 'en');
	if (!$user->isLoggedIn($sess)) {
		die(include('pages/login-form.php'));
	}

	if ($langOverride)
		$user->edit(false, false, $langOverride, false);

	$lang->setCode($user->getLang());

	$info_msg = false;
	$error_msg = false;
	$page = array_key_exists('page', $_GET) ? $_GET['page'] : 'home';
	$lvObject = new Libvirt($config, $lang);
	$connObj = new Connection($config);

	if (array_key_exists('attach', $_GET)) {
		$idConnection = (int)$_GET['attach'];
		$arr = $connObj->get($idConnection);
		$conn = $arr[0];
		if (!$lvObject->testConnectionUri($conn['hypervisor'], $conn['host'] ? true : false,
				$conn['method'], $conn['username'], $conn['password'], $conn['host'], false)) {
				$error_msg = $conn['name'].': '.$lang->get('connection-failed').' ('.$lvObject->getLastError().')';
				$page = 'connections';
		}
		else {
			$val = $sess->get('Connections');
			$skip = false;
			for ($i = 0; $i < sizeof($val); $i++)
				if ($val[$i] == $idConnection)
					$skip = true;
			if (!$skip) {
				if (!$val)
					$val = array( $idConnection );
				else
					$val[] = $idConnection;
				$sess->set('Connections', $val);
			}
			
			$info_msg = $conn['name'].': '.$lang->get('connection-successful');
			$page = 'connections';
			$sess->set('Connection-Last-Attached', $idConnection);
		}
	}

	if (array_key_exists('detach', $_GET)) {
		$idConnection = (int)$_GET['detach'];
		$val = $sess->get('Connections');
		
		$val_new = array();
		for ($i = 0; $i < sizeof($val); $i++) {
			if ($val[$i] != $idConnection)
				$val_new[] = $val[$i];
		}
		
		$sess->set('Connections', $val_new);
	}
	
	/* Find all connections and initiate objects */
	$lvObjects = array();
	$conns = $sess->get('Connections');
	for ($i = 0; $i < sizeof($conns); $i++) {
		$arr = $connObj->get($conns[$i]);
		$conn = $arr[0];
		
		$lUri = $conn['uri_override'];
		if (!$lUri)
			$lUri = $lvObject->generateConnectionUri($conn['hypervisor'], $conn['host'] ? true : false,
				$conn['method'], $conn['username'], $conn['host'], false);

		$log_file = $conn['log_file'] ? $conn['log_file'] : false;
		$lvObj = new Libvirt($config, $lang, $lUri, $conn['username'], $conn['password'], $log_file);
		
		if ($lvObj->getLastError() == false) {
			$lvObjects[] = array(
					'id' => $conn['id'],
					'name' => $conn['name'],
					'obj' => $lvObj
					);

			if ($conn['id'] == $sess->get('Connection-Last-Attached')) {
				$lvObject = $lvObj;
				$uri = $lUri;
			}
		}
	}
	
	if (array_key_exists('ajax', $_GET) && ($_GET['ajax'] == 1)) {
		function getPOSTValue($key) {
			return array_key_exists($key, $_POST) ? $_POST[$key] : false;
		}

		$cmd = array_key_exists('cmd', $_POST) ? $_POST['cmd'] : false;
		if ($cmd == 'getDomainTypes') {
			$tmp = $lvObject->getTypesForArchitecture($_POST['param1']);
			if (is_array($tmp))
				echo implode(',', $tmp);
		}
		else
		if ($cmd == 'getMachines') {
			$tmp = $lvObject->getEmulatorInformationForArchitecture($_POST['param1']);
			if (!array_key_exists($_POST['param2'], $tmp))
				die('#ERR');
			$tmp = $tmp[$_POST['param2']];
			if (is_array($tmp['machines']))
				echo implode(',', $tmp['machines']);
		}
		else
		if ($cmd == 'updateVMInformation') {
			$vm_data = array(
				'clock' => getPOSTValue('clock_offset'),
				'arch' => getPOSTValue('arch'),
				'typeEmulator' => getPOSTValue('met'),
				'typeMachine' => getPOSTValue('mmt'),
				'boot' => array(
						'first' => getPOSTValue('boot_one'),
						'second' => getPOSTValue('boot_two')
						),
				'domain' => array(
						'uuid' => $_POST['param1'],
						'name' => $lvObject->domainGetNameByUuid($_POST['param1']),
						'cpu' => getPOSTValue('cpus'),
						'memory' => array(
									'current' => getPOSTValue('memory'),
									'maxmem' => getPOSTValue('maxmem')
								)
						),
				'features' => array(
					'apic' => getPOSTValue('f_apic'),
					'acpi' => getPOSTValue('f_acpi'),
					'pae'  => getPOSTValue('f_pae'),
					'hap'  => getPOSTValue('f_hap')
					)
			);

			if ($vm_data['typeEmulator'])
				$vm_data['domain']['emulator'] = $lvObject->getEmulatorPathForArchType($vm_data['arch'], $vm_data['typeEmulator']);

			if ($vm_data['typeMachine'])
				$vm_data['domain']['machine'] = $vm_data['typeMachine'];

			if ($vm_data['typeEmulator'])
				$vm_data['domain']['type'] = $vm_data['typeEmulator'];

			unset($vm_data['typeEmulator']);
			unset($vm_data['typeMachine']);

			echo ($lvObject->domainChangeByArray($vm_data)) ? $lang->get('domain-edit-ok') : $lang->get('domain-edit-failed');
		}
		else
		if ($cmd == 'blockDeviceAdd') {
			$name = $lvObject->domainGetNameByUuid( getPOSTValue('param1') );
			echo (@$lvObject->domainDiskAdd( $name, getPOSTValue('img'), getPOSTValue('dev'),
					getPOSTValue('bus'), getPOSTValue('driver'))) ? 'OK' : '#ERR: '.$lvObject->getLastError();
		}
		else
		if ($cmd == 'blockDeviceDel') {
			$name = $lvObject->domainGetNameByUuid( getPOSTValue('param1') );
			echo ($lvObject->domainDiskRemove($name, getPOSTValue('param2'))) ? 'OK' : '#ERR: '.$lvObject->getLastError();
		}
		else
		if ($cmd == 'networkDeviceAdd') {
			$name = $lvObject->domainGetNameByUuid( getPOSTValue('param1') );

			echo (@$lvObject->domainNicAdd($name, getPOSTValue('mac'), getPOSTValue('network'), getPOSTValue('type'))) ? 'OK'
				: '#ERR: '.$lvObject->getLastError();
		}
		else
		if ($cmd == 'networkDeviceDel') {
			$name = $lvObject->domainGetNameByUuid( getPOSTValue('param1') );
			echo ($lvObject->domainNicRemove($name, getPOSTValue('param2'))) ? 'OK' : '#ERR: '.$lvObject->getLastError();

		}
		else
			print_r($_POST);
		exit;
	}

	if ((array_key_exists('getMac', $_GET)) && ($_GET['getMac'] == 1)) {
		echo $lvObject->generateRandomMacAddr();
		exit;
	}

	$name = false;
?>
<html>
<head>
 <title>php-virt-control - <?php echo $lang->get('title-vmc') ?></title>
 <link rel="STYLESHEET" type="text/css" href="manager.css" />
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
</head>
<body>
  <div id="header">
    <div id="headerLogo"></div>
    <div id="headShowUsers" onclick="location.href='?page=users'" title="<?php echo $lang->get('upper-menu-users') ?>"></div>
    <div id="headProfileEdit" onclick="location.href='?page=settings'" title="<?php echo $lang->get('upper-menu-settings') ?>"></div>
    <div id="headLogout" onclick="location.href='?action=logout'" title="<?php echo $lang->get('upper-menu-logout') ?>"></div>
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
	if (($action == 'add') || ($action == 'edit') || ($action == 'del'))
		$page .= '-edit-form';
	if (!File_Exists('./pages/'.$page.'.php'))
		$page = 'error';
	include('./pages/'.$page.'.php');
	endif;
?>

<?php
	function dumpLibvirtObjectResources($lvObject) {
		$lvR = $lvObject->printResources();
		if (sizeof($lvR) > 0) {
			echo '<table border=0 cellspacing=0 width="95%" align="center"><tr><td colspan="3" class="log_head">Class: Libvirt (after all finished)</td></tr><tr>';
			echo '<th class="log_head_th">Object type</th><th class="log_head_th">Address</th><th class="log_head_th">Message</th></tr>';

			for ($i = 0; $i < sizeof($lvR); $i++) {
				$tmp = explode(' ', $lvR[$i]);
				if ((sizeof($tmp) == 0) || ($tmp[0] == ''))
					break;
				$type = $tmp[1];
				$addr = $tmp[4];

				$type[0] = strtoupper($type[0]);

				echo '<tr class="log_info"><td>'.$type.'</td><td>'.$addr.'</td><td>'.$lvR[$i].'</td></tr>';
			}

			echo '</table>';
		}
	}

	if (DEBUG) {
		echo '<div id="content">';
		echo '<div class="section">Query debug</div>';
		$user->log_dump();
		$lang->log_dump();

		$idx = false;
		for ($i = 0; $i < sizeof($lvObjects); $i++) {
			if ($lvObjects[$i]['obj'] != $lvObject) {
				$lvObjects[$i] = $lvObject->resourceUnset($lvObjects[$i], 0);
				$idx = $i;
			}
		}

		if (!is_bool($idx))
			$lvObject->resourceUnset($lvObjects[$idx], 1);

		$lvObject->log_dump();

		dumpLibvirtObjectResources($lvObject);
		echo '</div>';
		
		if (array_key_exists('genstr', $_GET))
			echo $lang->generateStaticStrings('tmp/lang-strings-%l.php');
	}

	/* Free all libvirt objects to call it's destructors */
	unset($lvObject);

	for ($i = 0; $i < sizeof($lvObjects); $i++)
		unset($lvObjects[$i]['obj']);
?>

</body>
</html>
