<?php
	$allowed = true;
	if (!$user->checkUserPermission(USER_PERMISSION_SAVE_CONNECTION))
		$allowed = false;

	$msg_info = false;
	$msg_error = false;
	$skip = false;

	if (!$allowed) {
		$skip = true;
		echo "<div id=\"msg-error\"><b>{$lang->get('msg')}: </b>{$lang->get('permission-denied')}</div>";
	}

	if ($action == 'del') {
		if ((array_key_exists('confirm', $_GET)) && ($_GET['confirm'] == 1)) {
			$id = $_GET['id'];

			$ident = $connObj->del($_GET['id']);
			if (is_string($ident))
				echo '<div id="msg-error">'.$lang->get($ident).'</div>';
			else
				echo '<div id="msg-info">'.$lang->get('deleted').'</div>';
				
			$skip = true;
		}
		else {
			$name = $connObj->getName($_GET['id']);
			$back = 'connections';
			$type = 'connection';
			include('delete-form.php');
			$skip = true;
		}
	}
	if (array_key_exists('name', $_POST) && ($user->checkUserPermission(USER_PERMISSION_SAVE_CONNECTION))) {
		$ret = false;
		if ($_POST['connection_uri']) {
			$lv = new Libvirt(false, $lang, $_POST['connection_uri'], $_POST['username'], $_POST['password']);
			$ret = $lv->isConnected();
		}
		else {
			$lv = new Libvirt(false, $lang);
			$ret = $lv->testConnectionUri($_POST['hypervisor'], $_POST['host'] ? true : false, $_POST['conn-method'],
				$_POST['username'], $_POST['host'], false);
		}

		if ($ret) {
			if ($_POST['id'] == -1) {
				$ident = $connObj->add($_POST['name'], $_POST['hypervisor'], $_POST['conn-method'], $_POST['host'] ? $_POST['host'] : false,
					$_POST['username'], $_POST['password'], $_POST['connection_uri'] ? $_POST['connection_uri'] : false,
					($_POST['log-type'] == 'enabled') ? $_POST['log_file'] : false);

				if (is_string($ident))
					$msg_error = $lang->get($ident);
				else
					$msg_info = $lang->get('connection-added');
			}
			else {
				$ident = $connObj->edit($_POST['id'], $_POST['name'], $_POST['hypervisor'], $_POST['conn-method'],
					$_POST['host'] ? $_POST['host'] : false, $_POST['username'], $_POST['password'],
					$_POST['connection_uri'] ? $_POST['connection_uri'] : false,
					($_POST['log-type'] == 'enabled') ? $_POST['log_file'] : false);
					if (is_string($ident))
						$msg_error = $lang->get($ident);
					else
						$msg_info = $lang->get('connection-edited');
			}
		}
		else
			$msg_error = $lang->get('operation-failed');

		unset($lv);
	}

	$conn = array(
			'name' => '',
			'hypervisor' => '',
			'host' => '',
			'method' => '',
			'username' => '',
			'password' => '',
			'uri_override' => '',
			'log_file' => '',
			'id' => -1
			);

	$tIdent = 'connection-add';
	if (array_key_exists('id', $_GET)) {
		$arr = $connObj->get($_GET['id']);
		$conn = $arr[0];
		$conn['id'] = $_GET['id'];

		$tIdent = 'connection-edit';
	}

	if (!$skip):
?>
<h1><?php echo $lang->get($tIdent); ?></h1>

<div id="msgs"></div>
<script language="JavaScript">
<!--
	function changeConnectionType(t) {
		ct = document.getElementById('conntype');
		if (ct == null)
			return false;
			
		if (t == 'remote')
			ct.style.display = 'block';
		else
			ct.style.display = 'none';
	}
	
	function changeLogType(t) {
		ct = document.getElementById('logtype');
		if (ct == null)
			return false;
			
		if (t == 'enabled')
			ct.style.display = 'block';
		else
			ct.style.display = 'none';
	}

	var xmlhttp;
	var _uriChanged = false;
	if (window.XMLHttpRequest)
		xmlhttp=new XMLHttpRequest();
	else
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

        function elementExists(elName) {
                var id = document.getElementById(elName);
                if (id == null)
                        return false;

		return id.value;
	}

	function generateOrTestUri(type) {
		var hv = elementExists('cf-hypervisor');
		var ct = elementExists('cf-conn-type');
		var ch = elementExists('cf-host');
		var cm = elementExists('cf-method');
		var cu = elementExists('cf-username');
		var cr = elementExists('cf-uri');
		var cp = elementExists('cf-password');
		
		if (ct == 'local') {
			ch = '';
			cm = '';
			cu = '';
			cp = '';
		}

		xmlhttp.open("POST", "process-ajax.php", true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		data = "atype="+type+"&hv=" + escape(hv)+'&type='+escape(ct)+'&host='+escape(ch)+'&method='+escape(cm)+'&username='+escape(cu)+'&password='+escape(cp)+'&uri='+escape(cr);
		xmlhttp.send(data);
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				if (type == 'test') {
					if (xmlhttp.responseText.length > 0)
						txt = '<div id="msg-error">' + xmlhttp.responseText + '</div>';
					else
						txt = '<div id="msg-info">Test OK</div>';

					document.getElementById("msgs").innerHTML = txt;
				}
				else
					document.getElementById("cf-uri").value = xmlhttp.responseText;

				_uriChanged = false;
			}
		}
	}

	function uriChanged() {
		return _uriChanged;
	}

	function setBoxProps() {
		document.getElementById('cf-uri').onkeypress = function(event) {
			_uriChanged = true;
		}
	}

	window.document.onload = function(e){
		setBoxProps();
	}

	window.onload = function(e) {
		setBoxProps();
	}

	function preSubmit() {
		var id = document.getElementById('cf-name');
		if (id == null)
			return true;
		if (id.value.length == 0) {
			alert('<?php echo $lang->get('connection-must-have-name') ?>');
			return false;
		}
		if (_uriChanged)
			return confirm('<?php echo $lang->get('connection-uri-changed') ?>');

		return true;
	}
-->
</script>

<?php
	if ($msg_info)
		echo '<div id="msg-info">'.$msg_info.'</div>';
	if ($msg_error)
		echo '<div id="msg-error">'.$msg_error.'</div>';

	$hvs = array(
			'qemu' => 'Qemu/KVM',
			'xen'  => 'Xen',
			'lxc'  => 'LXC',
			'openvz'=>'OpenVZ',
			'uml'  => 'UML',
			'vbox' => 'VirtualBox',
			'esx'  => 'VMware ESX',
			'vmwareplayer' => 'VMware Player',
			'vmwarews' => 'VMware Workstation',
			'hyperv' => 'Microsoft Hyper-V',
			'parallels' => 'Parallels Cloud Server'
		);

	$connMethods = array(
				'ssh' => $lang->get('conn-method-ssh'),
				'tcp' => $lang->get('conn-method-tcp'),
				'libssh2' => $lang->get('conn-method-libssh2')
			);
?>

<form method="POST" onSubmit="return preSubmit();">
<table id="connections-edit">
<tr>
	<td class="title"><?php echo $lang->get('name') ?>: </td>
	<td class="field"><input type="text" name="name" id="cf-name" value="<?php echo $conn['name'] ?>" /></td>
</tr>
<tr>
	<td class="title"><?php echo $lang->get('hypervisor') ?>: </td>
	<td class="field">
		<select name="hypervisor" id="cf-hypervisor" />
<?php
	foreach ($hvs as $key => $item)
		echo '<option value="'.$key.'" '.($conn['hypervisor'] == $key ? ' selected="selected"' : '').'>'.$item.'</option>';

	$ls = $rs = $lfd = $lfe = '';
	if (!$conn['host']) {
		$ls = ' selected="selected"';
		$ct = 'local';
	}
	else {
		$rs = ' selected="selected"';
		$ct = 'remote';
	}
	if (!$conn['log_file']) {
		$lfd = ' selected="selected"';
		$cf = 'disabled';
	}
	else {
		$lfe = ' selected="selected"';
		$cf = 'enabled';
	}
?>
		</select>
	</td>
</tr>
<tr>
	<td class="title"><?php echo $lang->get('connection-type') ?>: </td>
	<td class="field">
		<select id="cf-conn-type" name="conn-type" onchange="changeConnectionType(this.value)">
			<option value="local" <?php echo $ls ?>><?php echo $lang->get('local') ?></option>
			<option value="remote" <?php echo $rs ?>><?php echo $lang->get('remote') ?></option>
		</select>
		<table id="conntype" style="display: none">
			<tr>
				<td class="title" style="padding-left: 10px"><?php echo $lang->get('connection-host') ?>: </td>
				<td class="field"><input type="text" id="cf-host" name="host" value="<?php echo $conn['host'] ?>" /></td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('connection-method') ?>: </td>
				<td class="field">
					<select name="conn-method" id="cf-method">
<?php
	foreach ($connMethods as $key => $item)
		echo '<option value="'.$key.'" '.($conn['method'] == $key ? ' selected="selected"' : '').'>'.$item.'</option>';
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('connection-username') ?>: </td>
				<td class="field"><input type="text" name="username" id="cf-username" value="<?php echo $conn['username'] ?>" /></td>
			</tr>
			<tr>
				<td class="title"><?php echo $lang->get('connection-password') ?>: </td>
				<td class="field"><input type="password" name="password" id="cf-password" value="<?php echo $conn['password'] ?>" /></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="title"><?php echo $lang->get('connection-uri') ?>: </td>
	<td class="field">
		<input id="cf-uri" type="text" name="connection_uri" />
		<input class="btn" type="button" value=" <?php echo $lang->get('generate') ?> " onclick="generateOrTestUri('generate')">
		<input class="btn" type="button" value=" <?php echo $lang->get('test') ?> " onclick="generateOrTestUri('test')">
	</td>
</tr>
<tr>
	<td class="title"><?php echo $lang->get('connection-logging') ?>: </td>
	<td class="field">
		<select name="log-type" onchange="changeLogType(this.value)">
			<option value="disabled" <?php echo $lfd ?>><?php echo $lang->get('disabled') ?></option>
			<option value="enabled" <?php echo $lfe ?>><?php echo $lang->get('enabled') ?></option>
		</select>
		<table id="logtype" style="display: none">
			<tr>
				<td class="title" style="padding-left: 10px"><?php echo $lang->get('connection-logfile') ?>: </td>
				<td class="field"><input type="text" name="log_file" value="<?php echo $conn['log_file'] ?>" /></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="submit" colspan="2"><input type="submit" value="<?php echo $lang->get($tIdent) ?>" /></td>
</tr>
<?php
	if (isset($ct))
        echo '<script language="JavaScript">
                <!--
                changeConnectionType("'.$ct.'");
                -->
                </script>';

	if (isset($cf))
	echo '<script language="JavaScript">
                <!--
                changeLogType("'.$cf.'");
                -->
                </script>';
?>
<input type="hidden" name="id" value="<?php echo $conn['id'] ?>" />
</form>
</table>

<?php
	endif;
?>
