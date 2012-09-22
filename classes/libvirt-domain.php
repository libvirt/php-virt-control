<?php
	class LibvirtDomain extends LoggerBase {
		private $data = false;
		private $lang = false;
		private $lv = false;

		function LibvirtDomain($libvirtInstance, $languageInstance, $action = false, $input = array()) {
			$this->lv = $libvirtInstance;
			$this->lang = $languageInstance;

			if ($action)
				$this->_processAction($action, $input);
		}

		function getData() {
			return $this->data;
		}

		function _processAction($action, $input = array()) {
			if (!$action)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No action defined', 'Cannot process empty action');

			$msg = false;
			$frm = false;
			$xml = false;

			$lv = $this->lv;
			$lang = $this->lang;

			if ($action == 'domain-start') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain start failed', 'Domain name not present');

				$name = $input['dom'];
				$msg = $lv->domain_start($name) ? $lang->get('dom-start-ok') :
					$lang->get('dom-start-err').': '.$lv->get_last_error();
			}

			if ($action == 'domain-stop') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain shutdown failed', 'Domain name not present');

				$name = $input['dom'];
				$msg = $lv->domain_shutdown($name) ? $lang->get('dom-shutdown-ok') :
					$lang->get('dom-shutdown-err').': '.$lv->get_last_error();
			}

			if ($action == 'domain-destroy') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain destroy failed', 'Domain name not present');

				$name = $input['dom'];
				$msg = $lv->domain_destroy($name) ? $lang->get('dom-destroy-ok') :
					$lang->get('dom-destroy-err').': '.$lv->get_last_error();
			}

			if (($action == 'domain-undefine') && (verify_user($db, USER_PERMISSION_VM_DELETE))) {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain undefine failed', 'Domain name not present');

				$name = $input['dom'];
				if ((!array_key_exists('confirmed', $_GET)) || ($_GET['confirmed'] != 1)) {
					$frm = '<div class="section">'.$lang->get('dom-undefine').'</div>
						<table id="form-table">
						<tr>
						  <td colspan="3">'.$lang->get('dom-undefine-question').' '.$lang->get('name').': <u>'.$name.'</u></td>
						</tr>
						<tr align="center">
						  <td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1">'.$lang->get('delete').'</a></td>
						  <td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1&amp;deldisks=1">'.$lang->get('delete-with-disks').'</a></td>
						  <td><a href="?page='.$page.'">'.$lang->get('No').'</a></td>
						</td>
						</table>';
				}
				else {
					$err = '';
					if (array_key_exists('deldisks', $_GET) && $_GET['deldisks'] == 1) {
						$disks = $lv->get_disk_stats($name);

						for ($i = 0; $i < sizeof($disks); $i++) {
							$img = $disks[$i]['file'];

							if (!$lv->remove_image($img, array(2) ))
								$err .= $img.': '.$lv->get_last_error();
						}
					}

					$msg = $lv->domain_undefine($name) ? $lang->get('dom-undefine-ok') :
						$lang->get('dom-undefine-err').': '.$lv->get_last_error();

					if ($err)
						$msg .= ' (err: '.$err.')';
				}
			}

			if ($action == 'domain-dump') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain dump failed', 'Domain name not present');

				$name = $input['dom'];
				$inactive = (!$lv->domain_is_running($name)) ? true : false;

				$xml = $lv->domain_get_xml($name, $inactive);
				$frm = '<div class="section">'.$lang->get('dom-xmldesc').' - <i>'.$name.'</i></div><form method="POST">
					<table id="form-table"><tr><td>'.$lang->get('dom-xmldesc').': </td>
					<td><textarea readonly="readonly" name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
					</tr></form></table>';
			}

			if ($action == 'domain-migrate') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain migration failed', 'Domain name not present');

				$name = $input['dom'];
				if (!array_key_exists('dest-uri', $_POST)) {
					$uris = array();

					if (isset($conns) && $conns) {
						foreach ($conns as $conn) {
							if ($conn['connection_uri'] != $uri)
								$uris[] = array(
									'id' => $conn['id'],
									'name' => $conn['connection_name']
									);
						}
					}
					else
						$uris = array();

					if (sizeof($uris) == 0)
						echo $lang->get('no-destination-present');
					else {
						echo "<form method='POST'>".$lang->get('choose-destination')." ($name): <br /><select name='dest-uri' style='width: 150px'>";

						foreach ($uris as $cn)
							echo "<option value=\"${cn['id']}\">{$cn['name']}</option>";

						echo "</select><br /><input type='submit' value='".$lang->get('dom-migrate')."'>";
					}
				}
				else {
					$arr = false;

					for ($i = 0; $i < sizeof($conns); $i++) {
						if ($conns[$i]['id'] == $_POST['dest-uri']) {
							$arr = $conns[$i];
							break;
						}
					}

					if ($arr && (!$lv->migrate($name, $arr)))
						echo '<b>'.$lang->get('error-page-title').'</b>: '.$lv->get_last_error();
				}
			}

			if ($action == 'domain-edit') {
				if (!array_key_exists('dom', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Domain editing failed', 'Domain name not present');

				$name = $input['dom'];
				$inactive = (!$lv->domain_is_running($name)) ? true : false;

				if (array_key_exists('xmldesc', $_POST)) {
					$msg = $lv->domain_change_xml($name, $_POST['xmldesc']) ? $lang->get('dom-define-changed') :
						$lang->get('dom-define-change-err').': '.$lv->get_last_error();
				}
				else {
					$xml = $lv->domain_get_xml($name, $inactive);
					$frm = '<div class="section">'.$lang->get('dom-editxml').' - <i>'.$name.'</i></div><form method="POST"><table id="form-table"><tr><td>'.$lang->get('dom-xmldesc').': </td>
						<td><textarea name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
						<input type="submit" value=" '.$lang->get('dom-editxml').' "></tr></form></table>';
				}
			}

			$this->data = array(
					'msg' => $msg,
					'frm' => $frm,
					'xml' => $xml
					);
		}

		function createNewVM($input) {
			$lv = $this->lv;
			$lang = $this->lang;

			$skip = false;
			$msg = false;
			if (array_key_exists('sent', $input)) {
				$features = array('apic', 'acpi', 'pae', 'hap');
				$iso_path = ini_get('libvirt.iso_path');
				$img = $iso_path.'/'.$input['install_img'];

				$feature = array();
				for ($i = 0; $i < sizeof($features); $i++)
					if (array_key_exists('feature_'.$features[$i], $input))
						$feature[] = $features[$i];

				$nic = array();
				if ($input['setup_nic']) {
					$nic['mac'] = $input['nic_mac'];
					$nic['type'] = $input['nic_type'];
					$nic['network'] = $input['nic_net'];
				}
				$disk = array();
				if ($input['setup_disk']) {
					if ($input['new_vm_disk']) {
						$disk['image'] = $input['name'].'.'.$input['disk_driver'];
						$disk['size'] = (int)$input['img_data'];
						$disk['bus'] = $input['disk_bus'];
						$disk['driver'] = $input['disk_driver'];
					}
					else {
						$disk['image'] = $input['img_data'];
						$disk['size'] = 0;
						$disk['bus'] = $input['disk_bus'];
						$disk['driver'] = $input['disk_driver'];
					}
				}

				$tmp = $lv->domain_new($input['name'], $img, $input['cpu_count'], $feature, $input['memory'], $input['maxmem'], $input['clock_offset'], $nic, $disk, $input['setup_persistent']);
				if (!$tmp)
					$msg = $lv->get_last_error();
				else {
					$skip = true;
					$msg = $lang->get('new-vm-created');
				}
			}

			$isos = libvirt_get_iso_images();

			if (empty($isos))
				$msg = $lang->get('no-iso');

			$ci  = $lv->get_connect_information();
			$maxvcpu = $ci['hypervisor_maxvcpus'];
			unset($ci);

			return array(
						'skip' => $skip,
						'msg' => $msg,
						'isos' => $isos,
						'maxvcpu' => $maxvcpu
					);
		}
	}
?>
