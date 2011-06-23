<?php
  define('PHPVIRTCONTROL_VERSION', '0.0.1');

  session_start();
  require('libvirt.php');
  $uri = array_key_exists('connection_uri', $_SESSION) ? $_SESSION['connection_uri'] : 'null';
  $lg = array_key_exists('connection_logging', $_SESSION) ? $_SESSION['connection_logging'] : false;

  if ($lg == '')
	$lg = false;

  if ($lg)
    $lg = 'logs/'.$lg;

  $errmsg = false;
  $lv = new Libvirt($uri, $lg);
  if ($lv->get_last_error()) {
    $page = 'overview';
    $name = false;
    $errmsg = 'Cannot connect to hypervisor. Please change connection information.';
  }
  else {
    $name = array_key_exists('name', $_GET) ? $_GET['name'] : false;
    $res = $lv->get_domain_by_name($name);
    $page = array_key_exists('page', $_GET) ? $_GET['page'] : 'overview';
  }
?>
<html>
<head>
 <title>phpVirtControl - Virtual machine controller</title>
 <link rel="STYLESHEET" type="text/css" href="manager.css"> 
</head>
<body>
  <h1>PHP Virtual machine controller</h1>

  <?php include('main-menu.php'); ?>

  <?php
	if ($name):
  ?>
	<h2 id="vm-name">Virtual machine <?= $name ?></h2>
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
</body>
</html>
