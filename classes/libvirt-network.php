<?php
	class LibvirtNetwork extends LoggerBase {
		private $data = false;
		private $lang = false;
		private $lv = false;

		function LibvirtNetwork($libvirtInstance, $languageInstance, $action = false, $input = array()) {
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

			if ($action == 'net-start') {
				if (!array_key_exists('net', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Network start failed', 'Network name not present');

				$name = $input['net'];

				$msg = $lv->set_network_active($name, true) ? $lang->get('net-start-ok') :
					$lang->get('net-start-err').': '.$lv->get_last_error();
			}
			else
			if ($action == 'net-stop') {
				if (!array_key_exists('net', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Network stop failed', 'Network name not present');

				$name = $input['net'];
				$msg = $lv->set_network_active($name, false) ? $lang->get('net-stop-ok') :
					$lang->get('net-stop-err').': '.$lv->get_last_error();
			}
			else
			if (($action == 'net-undefine') && (verify_user($db, USER_PERMISSION_NETWORK_CREATE))){
				if (!array_key_exists('net', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Network undefine failed', 'Network name not present');

				$name = $input['net'];
				if ((!array_key_exists('confirmed', $_GET)) || ($_GET['confirmed'] != 1)) {
					$frm = '<div class="section">'.$lang->get('net-undefine').'</div>
						<table id="form-table">
						<tr>
							<td colspan="3">'.$lang->get('net-undefine-question').' '.$lang->get('name').': <u>'.$name.'</u></td>
						</tr>
						<tr align="center">
							<td><a href="'.$_SERVER['REQUEST_URI'].'&amp;confirmed=1">'.$lang->get('Yes').'</a></td>
							<td><a href="?page='.$page.'">'.$lang->get('No').'</a></td>
						</tr>
						</table>';
				}
				else {
					$msg = $lv->network_undefine($name) ? $lang->get('net-undefine-ok') :
						$lang->get('net-undefine-err').': '.$lv->get_last_error();
				}
			}
			else
			if ($action == 'net-dumpxml') {
				if (!array_key_exists('net', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Network undefine failed', 'Network name not present');

				$name = $input['net'];

				$xml = $lv->network_get_xml($name);
				$frm = '<div class="section">'.$lang->get('net-xmldesc').' - <i>'.$name.'</i></div><form method="POST">
					<table id="form-table"><tr><td>'.$lang->get('net-xmldesc').': </td>
					<td><textarea readonly="readonly" name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
					</tr></form></table>';
			}
			else
			if (($action == 'net-editxml') && (verify_user($db, USER_PERMISSION_NETWORK_EDIT))) {
				if (!array_key_exists('net', $input))
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Network undefine failed', 'Network name not present');

				$name = $input['net'];

				if (array_key_exists('xmldesc', $_POST)) {
				$msg = $lv->network_change_xml($name, $_POST['xmldesc']) ? $lang->get('net-define-changed') :
					$lang->get('net-define-change-err').': '.$lv->get_last_error();
				}
				else {
					$xml = $lv->network_get_xml($name);
					$frm = '<div class="section">'.$lang->get('net-editxml').' - <i>'.$name.'</i></div><form method="POST"><table id="form-table"><tr><td>'.
						$lang->get('net-xmldesc').': </td><td><textarea name="xmldesc" rows="25" cols="90%">'.$xml.'</textarea></td></tr><tr align="center"><td colspan="2">
						<input type="submit" value=" '.$lang->get('net-editxml').' "></tr></form></table>';

				}
			}

			$this->data = array(
					'msg' => $msg,
					'frm' => $frm,
					'xml' => $xml
					);

		}

		function createNewNetwork($input) {
			$lv = $this->lv;
			$lang = $this->lang;

			$skip = false;
			$msg = false;

			if (array_key_exists('sent', $input)) {
				if ($input['ip_range_cidr'])
					$ipinfo = $input['net_cidr'];
				else
					$ipinfo = array('ip' => $input['net_ip'], 'netmask' => $input['net_mask']);

				$dhcpinfo = ($input['setup_dhcp']) ? $input['net_dhcp_start'].'-'.$input['net_dhcp_end'] : false;

				$tmp = $lv->network_new($input['name'], $ipinfo, $dhcpinfo, $input['forward'], $input['net_forward_dev']);
				if (!$tmp)
					$msg = $lv->get_last_error();
				else {
					$skip = true;
					$msg = $lang->get('net-created');
				}
				unset($tmp);
			}

			return array(
					'skip' => $skip,
					'msg' => $msg
					);
		}
	}
?>
