<?php
	include('../init.php');

	$tmp = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$lng = $tmp[0];
	unset($tmp);

	$tmp = explode(',', $lng);
	$lng = $tmp[0];
	unset($tmp);

	$tmp = explode('-', $lng);
	$lang = $tmp[0];
	unset($tmp);

	//$lang = 'cs';
	include('lang.php');

	function getPOSTData($ident) {
		return array_key_exists($ident, $_POST) ? $_POST[$ident] : '';
	}

	$val = 0;
	$ak = array_keys($user_permissions);
	for ($i = 0; $i < sizeof($ak); $i++)
		eval(' $val += '.$ak[$i].';');

	$all_permissions = $val;

	$msg = false;
	$skip = false;
	$final_step = false;
	if (array_key_exists('sent', $_POST)) {
		if ($_POST['user_password'] != $_POST['user_cpassword'])
			$msg = 'administrator-password-mismatch';
		else {
			$db = new Database(false);
			$ret = @$db->setup($_POST['dbserver'], $_POST['dbuser'], $_POST['dbpass']);
			if (!$ret)
				$msg = 'connect-failed';
			else {
				if ($_POST['dbnewpass'] != $_POST['dbcnewpass'])
					$msg = 'password-mismatch';
				else {
					if ($db->createNewUser($_POST['dbnewuser'], $_POST['dbnewpass'])) {
						if (!$db->createDatabase($_POST['dbnewname'], $_POST['dbnewuser']))
							$msg = 'create-db-failed';
					}
					else
						$msg = 'create-user-failed';
				}
			}
			unset($db);

			$cu = new User(false);
			$ret = $cu->setup($_POST['dbserver'], $_POST['dbnewuser'], $_POST['dbnewpass'], $_POST['dbnewname']);
			if ($ret) {
				$ret = $cu->register($_POST['user_name'], $_POST['user_password'], $_POST['user_email'], $_SERVER['HTTP_USER_AGENT'], $_POST['user_language'], $all_permissions);
				unset($cu);

				for ($i = 0; $i < sizeof($my_classes); $i++) {
					$c = new $my_classes[$i](false, false);
					$c->setup($_POST['dbserver'], $_POST['dbnewuser'], $_POST['dbnewpass'], $_POST['dbnewname']);
					unset($c);

				}

				if (!$msg)
					$skip = true;
			}
		}
	}

	if ((array_key_exists('step', $_GET)) && ($_GET['step'] == 'new-config') && (!$msg)) {
		$skip = true;
		$final_step = true;
	}
?>
<html>
<head>
 <title>php-virt-control - <?php echo getString('setup-title') ?></title>
 <link rel="STYLESHEET" type="text/css" href="../manager.css" />
</head>
<body>
  <div id="header">
    <div id="headerLogo"></div>
  </div>

  <div id="conn-detail">
    <div style="float:right;text-align: right; width:220px;font-size:11px;font-style:italic">
  </div>

<h1><?php echo getString('setup-title') ?></h1>

<div id="content">

<?php
	if (!$skip):
?>
<script>
<!--
	function new_window(v, type) {
		if (v != '#get')
			return true;

		if (type == 'dbtype') {
			url = 'http://www.php-virt-control.org/development.html#dbtype';
			document.getElementById('dbtype').value = 'mysql';
		}
		else
		if (type == 'lang') {
			url = 'http://www.php-virt-control.org/contributions.html#translate';
			document.getElementById('lang').value = '<?php echo $lang ?>';
		}

		var win=window.open(url, '_blank');
		win.focus();
	}
-->
</script>

<?php
	if ($msg)
		echo "<div id=\"msg-error\">".getString($msg)."</div><br />";
?>

<form method="POST">
<table id="connections-edit">
	<tr>
		<td colspan="2" class="section"><?php echo getString('database') ?></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-type') ?>:</td>
		<td class="field">
			<select name="dbtype" id="dbtype" onchange="new_window(this.value, 'dbtype')">
				<option value="mysql">MySQL database</option>
				<option value="#get"> - <?php echo getString('add-new-database-type') ?> -</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-host') ?>:</td>
		<td class="field"><input type="text" name="dbserver" value="<?php echo getPOSTData('dbserver') ?>" /></td>
	</tr>
	<tr>
		<td colspan="2" class="section-small"><?php echo getString('database-existing-user') ?></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-user') ?>: </td>
		<td class="field"><input type="text" name="dbuser" value="<?php echo getPOSTData('dbuser') ?>" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-password') ?>: </td>
		<td class="field"><input type="password" name="dbpass" /></td>
	</tr>
	<tr>
		<td colspan="2" class="section-small"><?php echo getString('database-new-title') ?></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-new-name') ?>: </td>
		<td class="field"><input type="text" name="dbnewname" value="<?php echo getPOSTData('dbnewname') ?>" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-new-user') ?>: </td>
		<td class="field"><input type="text" name="dbnewuser" value="<?php echo getPOSTData('dbnewuser') ?>" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-new-password') ?>: </td>
		<td class="field"><input type="password" name="dbnewpass" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('database-new-cpassword') ?>: </td>
		<td class="field"><input type="password" name="dbcnewpass" /></td>
	</tr>
	<tr>
		<td colspan="2" class="section"><?php echo getString('system-admin-title') ?></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('system-admin-username') ?>: </td>
		<td class="field"><input type="text" name="user_name" value="<?php echo getPOSTData('user_name') ?>" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('system-admin-email') ?>: </td>
		<td class="field"><input type="text" name="user_email" value="<?php echo getPOSTData('user_email') ?>" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('system-admin-password') ?>: </td>
		<td class="field"><input type="password" name="user_password" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('system-admin-cpassword') ?>: </td>
		<td class="field"><input type="password" name="user_cpassword" /></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo getString('system-admin-language') ?>: </td>
		<td class="field">
			<select id="lang" name="user_language" onchange="new_window(this.value, 'lang')">
<?php
	$dh = opendir('../lang');
	if ($dh) {
		$langs = array();
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php')) {
				include('../lang/'.$file);

				echo "<option value=\"{$info['code']}\" ".($lang == $info['code'] ? ' selected="selected"' : '').">{$info['name']}</option>\n";
			}
		}
	}
	closedir($dh);
?>
				<option value="#get">- <?php echo getString('add-new-language') ?> -</option>
			</select>
		</td>
	</tr>
	<tr>
		<input type="hidden" name="sent" value="1" />
		<td colspan="2" class="submit"><input type="submit" value="<?php echo getString('setup') ?>" /></td>
	</tr>
</table>
</form>
<?php
	else:

	$imsg = false;
	$emsg = false;

	$tmp = explode('?', $_SERVER['REQUEST_URI']);
	$txt = $tmp[0];
	unset($tmp);

	$ch = '<a href="'.$txt.'?step=new-config">'.getString('click-here-when-done').'.</a>';
	if ($final_step) {
		if (is_writable('../data/config_db.php')) {
			$emsg = getString('incorrect-permissions').$ch;

			$dir = str_replace('/setup', '', getcwd());
			$msg = getString('incorrect-permissions-help').': <pre>'.
				"chmod 644 ".$dir."/data/config_db.php\n".
				"chmod 755 ".$dir."/data\n".
				'</pre>';
		}
		else {
			$user = new User('file://config_db.php');
			$ret = $user->isConnected();
			unset($user);

			if ($ret) {
				$imsg = getString('login-prompt');
				$imsg = str_replace('%LINK', '<a href="../">', $imsg);
				$imsg = str_replace('#LINK', '</a>', $imsg);
			}
			else
				$emsg = getString('db-cannot-connect').' '.$ch;
		}
	}
	else {
		$fc = "<?php
\t\$host = '".$_POST['dbserver']."';
\t\$username = '".$_POST['dbnewuser']."';
\t\$password = base64_decode('".base64_encode($_POST['dbnewpass'])."');
\t\$dbname = '".$_POST['dbnewname']."';
?>";
		if (is_writable('../data/config_db.php')) {
			$fp = fopen('../data/config_db.php', 'w');
			fputs($fp, $fc);
			fclose($fp);

			$imsg = getString('config-perms-disable-prompt').'. '.$ch;
			$msg = false;
		}
		else {
			$msg = getString('config-perms-save-file-prompt').': <br /><pre>'.htmlentities($fc).'</pre>';
			$imsg = $ch;
		}
	}

	if ($emsg)
		echo '<div id="msg-error">'.$emsg.'</div>';
	if ($imsg)
		echo '<div id="msg-info">'.$imsg.'</div>';
?>
	<br />
	<?php echo $msg ?>
<?php
	endif;
?>
</div>

</body>
</html>
