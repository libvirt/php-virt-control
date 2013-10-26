    <form action="#" method="POST" onsubmit="return saveChanges();">

    <script language="javascript">
    <!--
	function checkArchCompatibility() {
		var arch = document.getElementById("arch");
		var oArch = document.getElementById("arch_old");

		document.getElementById("msg-error").style.display = 'none';

		changeDomainEmulatorType();

		if ((oArch == null) || (oArch.length == 0))
			return true;

		if (oArch.value == arch.value)
			return true;

		// i686 -> x86_64
		if ((oArch.value == 'i686') && (arch.value == 'x86_64'))
			return true;

		// x86_64 -> i686
		if ((oArch.value == 'x86_64') && (arch.value == 'i686'))
			return true;

		document.getElementById("msg-error").style.display = 'block';
		return false;
	}

	function getVal(id) {
		var tmp = document.getElementById(id);

		if ((tmp == null) || (tmp.length == 0))
			return false;

		return tmp.value;
	}

	function getValCmp(id, key) {
		val = '';
		tmp = getVal(id);
		tmp_old = getVal(id + '_old');

		if (tmp != tmp_old)
			val = key + '=' + tmp + '&';

		return val;
	}

	function getBoolVal(id) {
		var tmp = document.getElementById(id);

		if ((tmp == null) || (tmp.length == 0))
			return 0;

		if ((tmp.type == 'checkbox') && (!tmp.checked))
			return 0;

		if ((tmp.type == 'hidden') && (tmp.value == '0'))
			return 0;

		return 1;
	}

	function getBoolValCmp(id, key) {
		val = '';
		tmp = getBoolVal(id);
		tmp_old = getBoolVal(id + '_old');

		if (tmp != tmp_old)
			val = key + '=' + tmp + '&';

		return val;
	}

	function getFeatures(query) {
		query += getBoolValCmp('feature_apic', 'f_apic');
		query += getBoolValCmp('feature_acpi', 'f_acpi');
		query += getBoolValCmp('feature_pae' , 'f_pae');
		query += getBoolValCmp('feature_hap' , 'f_hap');

		return query;
	}

	function getMachineChanges(query) {
		query += "arch=" + getVal('arch') + "&";
		query += "met=" + getVal('emulatorType') + "&";
		query += "mmt=" + getVal('machineType') + "&";

		return query;
	}

	function saveChanges() {
		query = getValCmp('clock_offset', 'clock_offset');

		update_query = getFeatures(query);
		update_query += getMachineChanges(query);

		/* CPU stuff */
		update_query += getValCmp('guest_vcpus', 'cpus');
		/* Memory stuff */
		update_query += getValCmp('guest_memory', 'memory');
		update_query += getValCmp('guest_maxmem', 'maxmem');
		/* Boot devices */
		update_query += getValCmp('bd_1st', 'boot_one');
		if (getValCmp('bd_1st', 'boot_one'))
			update_query += '&boot_two=' + getVal('bd_2nd');

		//alert(update_query);

		if (update_query == '')
			return false;

		uuid = document.getElementById('domainUUID').value;
		ajaxQuery('updateVMInformation', uuid, null, update_query);
		return false;
	}

	var xmlhttp;
	var gType = '?';
	if (window.XMLHttpRequest)
		xmlhttp=new XMLHttpRequest();
	else
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

	function ajaxProcess(result) {
		if (gType == 'getDomainTypes') {
			var arr = result.split(',');

			tag = '';
			for (i = 0; i < arr.length; i++) {
				sel = (document.getElementById('emulatorType').value == arr[i]);

				tag += '<option value="' + arr[i] + '" ' + (sel ? ' selected="selected"' : '')+ '>' + arr[i] + '</option>';
			}

			document.getElementById('emulatorType').innerHTML = tag;

			changeMachineType();
			document.getElementById('msg-info').style.display = 'none';
		}
		else
		if (gType == 'getMachines') {
			var arr = result.split(',');

			tag = '';
			for (i = 0; i < arr.length; i++) {
				sel = (document.getElementById('machineType').value == arr[i]);

				tag += '<option value="' + arr[i] + '" ' + (sel ? ' selected="selected"' : '')+ '>' + arr[i] + '</option>';
			}

			document.getElementById('machineType').innerHTML = tag;
			document.getElementById('msg-info').style.display = 'none';
		}
		else
		if (gType == 'updateVMInformation') {
			document.getElementById('msg-info').innerHTML = result;
			document.getElementById('msg-info').style.display = 'block';
		}
		else
		if (gType == 'blockDeviceAdd') {
			document.getElementById('msg-info').innerHTML = result;
			document.getElementById('msg-info').style.display = 'block';
			pageRedirect('disks');
		}
		else
		if (gType == 'blockDeviceDel') {
			document.getElementById('msg-info').innerHTML = result;
			document.getElementById('msg-info').style.display = 'block';
			pageRedirect('disks');
		}
		else
		if (gType == 'networkDeviceAdd') {
			document.getElementById('msg-info').innerHTML = result;
			document.getElementById('msg-info').style.display = 'block';
			pageRedirect('nics');
		}
		else
		if (gType == 'networkDeviceDel') {
			document.getElementById('msg-info').innerHTML = result;
			document.getElementById('msg-info').style.display = 'block';
			pageRedirect('nics');
		}
		else
			alert(result);
	}

	function ajaxQuery(type, param1, param2, addition) {
		gType = type;
		xmlhttp.open("POST","index.php?ajax=1", true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");

		document.getElementById('msg-info').innerHTML = '<?php echo $lang->get('loading') ?>';
		document.getElementById('msg-info').style.display = 'block';

		cmd = "cmd=" + escape(type) + '&param1=' + escape(param1);
		if (param2 != null)
			cmd += '&param2=' + escape(param2);
		if (addition != null)
			cmd += "&" + addition;
		xmlhttp.send(cmd);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
				ajaxProcess(xmlhttp.responseText);
		}
	}

	function pageRedirect(subpage) {
<?php
		$tmp = explode('&', $_SERVER['REQUEST_URI']);

		for ($i = 0; $i < sizeof($tmp); $i++) {
			$tmp2 = explode('=', $tmp[$i]);
			if ($tmp2[0] == 'subpage')
				unset($tmp[$i]);
		}

		$url = implode('&', $tmp)."&subpage=' + subpage";
		echo "url = '$url;\n";
		echo "location.href = url;\n";
?>
	}

	function askBlockDeviceDeletion(disk) {
		if (!confirm('<?php echo $lang->get("ask-disk-delete") ?>: ' + disk + ' ?'))
			return;

		uuid = document.getElementById('domainUUID').value;
		ajaxQuery('blockDeviceDel', uuid, disk, null);
	}

	function addBlockDevice() {
		image = document.getElementById('disk-img').value;
		bus = document.getElementById('disk-bus').value;
		driver = document.getElementById('disk-driver').value;
		dev = document.getElementById('disk-dev').value;

		uuid = document.getElementById('domainUUID').value;
		str = "img=" + image + '&bus=' + bus + '&driver=' + driver + '&dev=' + dev;
		ajaxQuery('blockDeviceAdd', uuid, null, str);

	}

	function askNetworkDeviceDeletion(disk) {
		if (!confirm('<?php echo $lang->get("ask-nic-delete") ?>: ' + disk + ' ?'))
			return;

		uuid = document.getElementById('domainUUID').value;
		ajaxQuery('networkDeviceDel', uuid, disk, null);
	}

	function addNetworkDevice() {
		mac = document.getElementById('network-mac').value;
		net = document.getElementById('network-net').value;
		type = document.getElementById('network-type').value;

		uuid = document.getElementById('domainUUID').value;
		str = "mac=" + mac + '&network=' + net + '&type=' + type;
		ajaxQuery('networkDeviceAdd', uuid, null, str);
	}

	function changeDomainEmulatorType() {
		el = document.getElementById('arch');
		if (el == null)
			return;

		arch = el.value;

		ajaxQuery('getDomainTypes', arch, null, null);
	}

	function changeMachineType() {
		el = document.getElementById('arch');
		if (el == null)
			return;

		arch = el.value;

		el = document.getElementById('emulatorType');
		if (el == null)
			return;

		mach = el.value;

		ajaxQuery('getMachines', arch, mach, null);
	}

	function changeSection(section) {
		sections = new Array("overview", "cpu", "memory", "boot", "disks", "nics", "multimedia", "host");

		document.getElementById('msg-info').style.display = 'none';
		for (i = 0; i < sections.length; i++) {
			/* Hide all elements */
			el = document.getElementById('s-' + sections[i]);
			if (el != null)
				el.style.display = 'none';
			/* ... and set their classes to inactive */
			el = document.getElementById('m-' + sections[i]);
			if (el != null)
				el.setAttribute('class', '');
		}

		el = document.getElementById('s-' + section);
		if (el != null)
			el.style.display = 'block';
		el = document.getElementById('m-' + section);
		if (el != null)
			el.setAttribute('class', 'active');

		return false;
	}
    -->
    </script>

    <div id="content">

<div id="msg-info" style="display: none; margin-bottom: 10px;"></div>
