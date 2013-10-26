<?php
        define('TYPE_CREATE',  0x10);
        define('TYPE_DESTROY', 0x20);

	class Libvirt extends LoggerBase {
		public $_log = array();
		private $_oldRes = array();
		private $conn;
		private $_config;
		private $last_error;
		private $allow_cached = true;
		private $dominfos = array();
		private $_langObject = false;
		private $_screenshot_allow_fallback = false;
		private $enabled = false;
		private $debug = true;

		function __construct($config, $langObject, $uri = false, $login = false, $pwd = false, $debug = false) {
			if ($debug)
				$this->setLogFile('logs/'.$debug);
			if ($uri != false) {
				$this->enabled = true;
				$this->connect($uri, $login, $pwd);
			}
			$this->_config = $config;
			$this->_langObject = $langObject;
		}

		function _set_last_error()
		{
			$this->last_error = libvirt_get_last_error();
			return false;
		}

		function log($type, $objType, $addr, $conn, $msg) {
			if (!$this->debug) return;
			$this->_log[ sizeof($this->_log) ] = array(
				'type' => $type,
				'objType' => $objType,
				'addr' => $addr,
				'conn' => $conn,
				'msg'  => $msg
			);
		}

		function type_fmt($type) {
			if ($type == TYPE_CREATE)
				return 'Created';
			else
			if ($type == TYPE_DESTROY)
				return 'Destroyed';

			return false;
		}

		function log_print($data) {
			$type = $this->type_fmt($data['type']);

			if ($type == false)
				return false;

			switch ($data['type']) {
				case TYPE_CREATE: $class = 'log_info';
						break;
				case TYPE_DESTROY: $class = 'log_warn';
			}

			if (!$data['conn'])
				$data['conn'] = ' - ';

			return "<tr class=\"$class\"><td>$type</td><td>{$data['objType']}</td>"
				."<td>{$data['addr']}</td><td>{$data['conn']}</td><td>{$data['msg']}</td></tr>";
		}

		function log_dump() {
			if (!$this->debug) return;
			$rs = $this->printResources();
			if (sizeof($rs) == 0)
				return;

			echo "<table border=0 cellspacing=0 width=\"95%\" align=\"center\"><tr><td colspan=\"4\" class=\"log_head\">Class: Libvirt</td></tr>";
			echo "<tr><th class=\"log_head_th\">Type</th><th class=\"log_head_th\">Object type</th><th class=\"log_head_th\">";
			echo "Address</th><th class=\"log_head_th\">Connection address</th><th class=\"log_head_th\">Message</th></tr>";
			for ($i = 0; $i < sizeof($this->_log); $i++) {
				$tmp = $this->log_print($this->_log[$i]);

				if (is_string($tmp))
					echo $tmp;
			}
			echo '</table>';
		}

		function isEnabled() {
			return $this->enabled;
		}

		function setLogFile($filename)
		{
			if (!libvirt_logfile_set($filename))
				return $this->_set_last_error();

			return true;
		}

		function sortArrayBy($arr, $key) {
			$ak = array_keys($arr);

			$tmp = array();
			for ($i = 0; $i < sizeof($ak); $i++) {
				$item = $arr[$ak[$i]];

				$tmp[$item[$key]] = $item;
			}

			ksort($tmp);

			$res = array();
			$ak = array_keys($tmp);
			for ($i = 0; $i < sizeof($ak); $i++) {
				$res[] = $tmp[$ak[$i]];
			}

			return $res;
		}

		function sortArray($arr) {
			$ak = array_keys($arr);

			$tmp = array();
			for ($i = 0; $i < sizeof($ak); $i++) {
				$name = $arr[$ak[$i]];
				$tmp[$name] = $name;
			}

			ksort($tmp);

			$res = array();
			$ak = array_keys($tmp);
			for ($i = 0; $i < sizeof($ak); $i++)
				$res[] = $tmp[$ak[$i]];

			return $res;
		}

		function isConnected() {
			return $this->conn ? true : false;
		}

		function getCapabilities() {
			$tmp = libvirt_connect_get_capabilities($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getDefaultEmulator() {
			$tmp = libvirt_connect_get_capabilities($this->conn, '//capabilities/guest/arch/domain/emulator');
			return ($tmp) ? $tmp : $this->_set_last_error();
		}


		function getEmulatorInformationForArchitecture($arch, $xmlStr = false) {
			if (!$xmlStr)
				$xmlStr = $this->getCapabilities();

			$xml = new SimpleXMLElement($xmlStr);
			$res = $xml->xpath('//capabilities/guest/arch[@name="'.$arch.'"]');
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				$mMachines = $a['machine'];
			}
			unset($xml);

			$mEmu = $this->getEmulatorForArchitecture($arch);
			$path = '//capabilities/guest/arch[@name="'.$arch.'"]/domain';
			$xml = new SimpleXMLElement($xmlStr);
			$res = $xml->xpath($path);
			$ret = array();
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				$machines = $mMachines;
				$type = $a['@attributes']['type'];

				if (array_key_exists('machine', $a)) {
					for ($j = 0; $j < sizeof($a['machine']); $j++) {
						$exists = false;

						for ($k = 0; $k < sizeof($machines); $k++)
							if ($machines[$k] == $a['machine'][$j])
								$exists = true;

						if (!$exists)
							$machines[] = $a['machine'][$j];
					}
				}

				$ret[$type] = array(
						'emulator' => array_key_exists('emulator', $a) ? $a['emulator'] : $mEmu,
						'machines' => $machines
						);
			}

			return $ret;
		}

		function getTypesForArchitecture($arch, $xmlStr = false) {
			if (!$xmlStr)
				$xmlStr = $this->getCapabilities();

			$mEmu = $this->getEmulatorForArchitecture($arch);
			$xml = new SimpleXMLElement($xmlStr);
			$res = $xml->xpath('//capabilities/guest/arch[@name="'.$arch.'"]/domain/@type/..');
			$ret = array();
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				$ret[] = $a['@attributes']['type'];
			}

			return $ret;
		}

		function getAllSupportedArchitectures($xmlStr = false) {
			if (!$xmlStr)
				$xmlStr = $this->getCapabilities();

			$xml = new SimpleXMLElement($xmlStr);
			$res = $xml->xpath('//capabilities/guest/arch/@name');
			$ret = array();
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				$ret[] = $a['@attributes']['name'];
			}

			return $ret;
		}

		function getEmulatorForArchitecture($arch, $xmlStr = false) {
			if (!$xmlStr)
				$xmlStr = $this->getCapabilities();

			$xml = new SimpleXMLElement($xmlStr); 
			$res = $xml->xpath('//guest/arch[@name="'.$arch.'"]');
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				return $a['emulator'];
			}

			return false;
		}

		function getAllArchitectureEmulators() {
			$xmlStr = $this->getCapabilities();
			$archs = $this->getAllSupportedArchitectures($xmlStr);

			$ret = array();
			for ($i = 0; $i < sizeof($archs); $i++)
				$ret[$archs[$i]] = $this->getEmulatorForArchitecture($archs[$i], $xmlStr);

			print_r($ret);
			return $ret;
		}

		function getHostArchitecture() {
			$xmlStr = $this->getCapabilities();

			$xml = new SimpleXMLElement($xmlStr); 
			$res = $xml->xpath('//host/cpu');

			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				return $a['arch'];
			}

			return false;
		}

		function getVCPUCountForMachineType($type, $arch='x86_64') {
			$xmlStr = $this->getCapabilities();

			$type = 'kvm';
			$xp = '//guest/arch[@name="'.$arch.'"]/domain[@type="'.$type.'"]/machine/@maxCpus';

			$xml = new SimpleXMLElement($xmlStr);
			$res = $xml->xpath($xp);
			settype($res[0], 'array');

			/* Default value */
			$ret = 16;

			if (array_key_exists('@attributes', $res[0]))
				$ret = (int)$res[0]['@attributes']['maxCpus'];

			return $ret;
		}

		function getEmulatorPathForArchType($arch, $type, $xmlStr = false) {
			$emul = false;
			if (!$xmlStr)
				$xmlStr = $this->getCapabilities();

			$xml = new SimpleXMLElement($xmlStr); 
			$res = $xml->xpath('//guest/arch[@name="'.$arch.'"]/domain[@type="'.$type.'"]');
			for ($i = 0; $i < sizeof($res); $i++) {
				$a = $res[$i];
				settype($a, 'array');
				$emul = array_key_exists('emulator', $a) ? $a['emulator'] : false;
			}

			if ($emul == false)
				$emul = $this->getEmulatorForArchitecture($arch);

			return $emul;
		}

		function domainCreateNew($name, $img, $vcpus, $features, $mem, $maxmem, $clock, $nic, $disk, $sound_type, $persistent=true) {
			$uuid = $this->domainGenerateUuid();
			$emulator = $this->getDefaultEmulator();

			$mem *= 1024;
			$maxmem *= 1024;

			$fs = '';
			for ($i = 0; $i < sizeof($features); $i++) {
				$fs .= '<'.$features[$i].' />';
			}

			$diskstr = '';
			if (!empty($disk)) {
				if ($disk['size']) {
					$disk['image'] = str_replace(' ', '_', $disk['image']);
					if (!$this->create_image($disk['image'], $disk['size'], $disk['driver']))
						return false;
				}

				if ($disk['image'][0] != '/')
					$path = ini_get('libvirt.image_path').'/'.$disk['image'];
				else
					$path = $disk['image'];

				$diskstr = "<disk type='file' device='disk'>
						<driver name='qemu' type='{$disk['driver']}' />
                                                <source file='$path'/>
                                                <target bus='{$disk['bus']}' dev='hda' />
                                         </disk>";
			}
			$netstr = '';
			if (!empty($nic)) {
				$model = '';
				if ($nic['type'] != 'default')
					$model = "<model type='{$nic['type']}'/>";
				$netstr = "
					    <interface type='network'>
					      <mac address='{$nic['mac']}'/>
					      <source network='{$nic['network']}'/>
					      $model
					    </interface>";
			}

			$xml = "<domain type='kvm'>
				<name>$name</name>
				<currentMemory>$mem</currentMemory>
				<memory>$maxmem</memory>
				<uuid>$uuid</uuid>
				<os>
					<type arch='i686'>hvm</type>
					<boot dev='cdrom'/>
					<boot dev='hd'/>
				</os>
				<features>
				$fs
				</features>
				<clock offset=\"$clock\"/>
				<on_poweroff>destroy</on_poweroff>
				<on_reboot>destroy</on_reboot>
				<on_crash>destroy</on_crash>
				<vcpu>$vcpus</vcpu>
				<devices>
					<emulator>$emulator</emulator>
					$diskstr
				";

			if ($img != '-')
				$xml .= "
					<disk type='file' device='cdrom'>
						<driver name='qemu'/>
						<source file='$img'/>
						<target dev='hdc' bus='ide'/>
						<readonly/>
					</disk>
					";

			$xml .= "
					$netstr
					<input type='mouse' bus='ps2'/>
					<graphics type='vnc' port='-1'/>
					<console type='pty'/>
				";

			if ($sound_type != 'none')
				$xml .= "<sound model='$sound_type'/>";

			$xml .= "
					<video>
						<model type='cirrus'/>
					</video>
				</devices>
				</domain>";

			$tmp = @libvirt_domain_create_xml($this->conn, $xml);
			if (!$tmp)
				return $this->_set_last_error();

			if ($persistent) {
				$xml = "<domain type='kvm'>
					<name>$name</name>
					<currentMemory>$mem</currentMemory>
					<memory>$maxmem</memory>
					<uuid>$uuid</uuid>
					<os>
						<type arch='i686'>hvm</type>
						<boot dev='hd'/>
					</os>
					<features>
					$fs
					</features>
					<clock offset=\"$clock\"/>
					<on_poweroff>destroy</on_poweroff>
					<on_reboot>destroy</on_reboot>
					<on_crash>destroy</on_crash>
					<vcpu>$vcpus</vcpu>
					<devices>
						<emulator>$emulator</emulator>
						$diskstr
						$netstr
						<input type='mouse' bus='ps2'/>
						<graphics type='vnc' port='-1'/>
						<console type='pty'/>
					";

					if ($sound_type != 'none')
						$xml .= "<sound model='$sound_type'/>";

					$xml .= "
						<video>
							<model type='cirrus'/>
						</video>
					</devices>
					</domain>";
				
				$tmp = libvirt_domain_define_xml($this->conn, $xml);
				return ($tmp) ? $tmp : $this->_set_last_error();
			}
			else
				return $tmp;
		}

		function createNewNetwork($input) {
			if (array_key_exists('sent', $input)) {
				if ($input['ip_range_cidr'])
					$ipinfo = $input['net_cidr'];
				else
					$ipinfo = array('ip' => $input['net_ip'], 'netmask' => $input['net_mask']);

				$dhcpinfo = ($input['setup_dhcp']) ? $input['net_dhcp_start'].'-'.$input['net_dhcp_end'] : false;

				$tmp = $this->networkNew($input['name'], $ipinfo, $dhcpinfo, $input['forward'], $input['net_forward_dev'], false, array_key_exists('edit', $input));
				if (!$tmp) {
					$this->_set_last_error();
					return 2;
				}

				return 1;
			}

			return 0;
		}

		function imageCreate($image, $size, $driver) {
			$tmp = libvirt_image_create($this->conn, $image, $size, $driver);
                        return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function imageDestroy($image, $ignore_error_codes=false ) {
			$tmp = libvirt_image_remove($this->conn, $image);
			if ((!$tmp) && ($ignore_error_codes)) {
				$err = libvirt_get_last_error();
				$comps = explode(':', $err);
				$err = explode('(', $comps[sizeof($comps)-1]);
				$code = (int)Trim($err[0]);

				if (in_array($code, $ignore_error_codes))
					return true;
			}

			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function migrateToUri($domain, $uri, $live = false, $bandwidth = 100) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$name = $this->domainGetName($dom);
			$tmp = libvirt_domain_migrate_to_uri($dom, $uri, $live ? VIR_MIGRATE_LIVE : 0, $name, $bandwidth);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetUUIDString($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			return libvirt_domain_get_uuid_string($dom);
		}

		function migrate($domain, $conn, $live = false, $bandwidth = 100) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$name = $this->domain_get_name($dom);
			if (!is_resource($conn)) {
				$uri = $conn['connection_uri'];
				$login = $conn['connection_credentials'][VIR_CRED_AUTHNAME];
				$pwd = $conn['connection_credentials'][VIR_CRED_PASSPHRASE];

				if ($login && $pwd)
					$dconn = libvirt_connect($uri, false, array(VIR_CRED_AUTHNAME => $login, VIR_CRED_PASSPHRASE => $password));
				else
					$dconn = libvirt_connect($uri, false);

				if ($dconn && $dom && $name)
					$tmp = libvirt_domain_migrate($dom, $dconn, $live ? VIR_MIGRATE_LIVE : 0, $name, $bandwidth);
				else
					$tmp = false;

				unset($dconn);
			}
			else
				$tmp = libvirt_domain_migrate($dom, $conn, $live ? VIR_MIGRATE_LIVE : 0, $name, $bandwidth);

			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function generateConnectionUri($hv, $remote, $remote_method, $remote_username, $remote_hostname, $session = false) {
			if ($hv == 'qemu') {
				if ($session)
					$append_type = 'session';
				else
					$append_type = 'system';
			}

			if (!$remote) {
				if ($hv == 'xen')
					return 'xen:///';
				if ($hv == 'qemu')
					return 'qemu:///'.$append_type;

				return false;
			}

			$ret = '';
			if ($hv == 'xen')
				$ret = 'xen+'.$remote_method.'://'.$remote_username.'@'.$remote_hostname;
			else
			if ($hv == 'qemu')
				$ret = 'qemu+'.$remote_method.'://'.$remote_username.'@'.$remote_hostname.'/'.$append_type;
				
			/* Automatically handle known hosts, i.e. add entry if not exists but reject if exists but key mismatch */
			if ($remote_method == 'libssh2')
				$ret .= '?known_hosts_verify=auto';
			
			return $ret;
		}

		function testConnectionUri($hv, $rh, $rm, $un, $pwd, $hn, $session=false) {
	                $uri = $this->generateConnectionUri($hv, $rh, $rm, $un, $hn, $session);
	                if (strlen($pwd) > 0) {
				$credentials = array(VIR_CRED_AUTHNAME => $un, VIR_CRED_PASSPHRASE => $pwd);
                		$test = @libvirt_connect($uri, false, $credentials);
	                }
        	        else
				$test = @libvirt_connect($uri);
			$ok = is_resource($test);
			unset($test);

			if (!$ok)
				$this->_set_last_error();

			return $ok;
		}

		function printResources() {
			return libvirt_print_binding_resources();
		}

		function connect($uri = 'null', $login = false, $password = false) {
			if ($login !== false && $password !== false) {
				$this->conn=@libvirt_connect($uri, false, array(VIR_CRED_AUTHNAME => $login, VIR_CRED_PASSPHRASE => $password));
			} else {
				$this->conn=@libvirt_connect($uri, false);
			}
			if ($this->conn==false)
				return $this->_set_last_error();
			$this->updateResources($uri);
		}

                function domainDiskAdd($domain, $img, $dev, $type='scsi', $driver='raw') {
                        $dom = $this->getDomainObject($domain);

                        $tmp = @libvirt_domain_disk_add($dom, $img, $dev, $type, $driver);
                        return ($tmp) ? $tmp : $this->_set_last_error();
                }

		function domainChangeNumVCpus($domain, $num) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_change_vcpus($dom, $num);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainChangeMemoryAllocation($domain, $memory, $maxmem) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_change_memory($dom, $memory, $maxmem);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainChangeBootDevices($domain, $first, $second) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_change_boot_devices($dom, $first, $second);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetScreenshot($domain) {
			$dom = $this->getDomainObject($domain);

			$arr = libvirt_domain_get_screenshot_api($dom, 0);
			if (is_array($arr)) {
				$fp = fopen($arr['file'], 'rb');
				$tmp = fread($fp, filesize($arr['file']));
				fclose($fp);

				return array(
					'data' => $tmp,
					'mime' => $arr['mime']
						);
			}

			if (!$this->_screenshot_allow_fallback)
				return false;

			$tmp = libvirt_domain_get_screenshot($dom, $this->getHostName(), 8 );
			if (Graphics::isBMPStream($tmp)) {
				$gc = new Graphics();
				$fn = tempnam("/tmp", "php-virt-control.tmp");
				$fn2 = tempnam("/tmp", "php-virt-control.tmp");

				$fp = fopen($fn, "wb");
				fputs($fp, $tmp);
				fclose($fp);

				unset($tmp);
				if ($gc->ConvertBMPToPNG($fn, $fn2) == false) {
					unlink($fn);
					return false;
				}

				$fp = fopen($fn2, "rb");
				$tmp = fread($fp, filesize($fn2));
				fclose($fp);

				unlink($fn2);
				unlink($fn);
				unset($gc);
			}

			$arr = array(
					'data' => $tmp,
					'mime' => 'image/png'
				);

			return ($tmp) ? $arr : $this->_set_last_error();
		}

		function domainGetScreenshotThumbnail($domain, $w=120) {
			$screen = $this->domainGetScreenshot($domain);
			$imgFile = tempnam("/tmp", "libvirt-php-tmp-resize-XXXXXX");;

			if ($screen) {
				$fp = fopen($imgFile, "wb");
				fwrite($fp, $screen['data']);
				fclose($fp);
			}

			if (file_exists($imgFile) && $screen) {
				list($width, $height) = getimagesize($imgFile); 
				$h = ($height / $width) * $w;
			} else {
				$w = $h = 1;
				//$h = $w * (3 / 4.5);
			}

			$new = imagecreatetruecolor($w, $h);
			if ($screen) {
				$img = imagecreatefrompng($imgFile);
				imagecopyresampled($new,$img,0,0,0,0, $w,$h,$width,$height);
				imagedestroy($img);
			}
			else {
				$c = imagecolorallocate($new, 255, 255, 255);
				imagefill($new, 0, 0, $c);
			}

			imagepng($new, $imgFile);
			imagedestroy($new);

			$fp = fopen($imgFile, "rb");
			$data = fread($fp, filesize($imgFile));
			fclose($fp);

			unlink($imgFile);
			return $data;
		}

                function domainGetScreenDimensions($domain) {
			$dom = $this->getDomainObject($domain);

			$arr = libvirt_domain_get_screenshot_api($dom, 0);
			if (is_array($arr)) {
				$img = imagecreatefrompng($arr['file']);
				$w = imagesx($img);
				$h = imagesy($img);
				imagedestroy($img);

				return array(
					'height' => $h,
					'width' => $w
						);
                        }

			if (!$this->_screenshot_allow_fallback)
				return false;

			$tmp = libvirt_domain_get_screen_dimensions($dom, $this->getHostName() );

			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainSendKeys($domain, $keys) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_send_keys($dom, $this->get_hostname(), $keys);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainSendPointerEvent($domain, $x, $y, $clicked = 1, $release = false) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_send_pointer_event($dom, $this->get_hostname(), $x, $y, $clicked, $release);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainDiskRemove($domain, $dev) {
			$dom = $this->getDomainObject($domain);

			$tmp = @libvirt_domain_disk_remove($dom, $dev);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function supports($name) {
			return libvirt_has_feature($name);
		}

		function _macbyte($val) {
			if ($val < 16)
				return '0'.dechex($val);

			return dechex($val);
		}

		function generateRandomMacAddr($seed=false) {
			if (!$seed)
				$seed = 1;

			if ($this->getHypervisorName() == 'qemu')
				$prefix = '52:54:00';
			else
			if ($this->getHypervisorName() == 'kvm')
				$prefix = '52:54:00';
			else
			if ($this->getHypervisorName() == 'xen')
				$prefix = '00:16:3e';
			else
				$prefix = $this->_macbyte(($seed * rand()) % 256).':'.
                                $this->_macbyte(($seed * rand()) % 256).':'.
                                $this->_macbyte(($seed * rand()) % 256);

			return $prefix.':'.
				$this->_macbyte(($seed * rand()) % 256).':'.
				$this->_macbyte(($seed * rand()) % 256).':'.
				$this->_macbyte(($seed * rand()) % 256);
		}

		function domainNicAdd($domain, $mac, $network, $model=false) {
			$dom = $this->getDomainObject($domain);

			if ($model == 'default')
				$model = false;

			$tmp = libvirt_domain_nic_add($dom, $mac, $network, $model);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainNicRemove($domain, $mac) {
			$dom = $this->getDomainObject($domain);

			$tmp = libvirt_domain_nic_remove($dom, $mac);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getConnection() {
			return $this->conn;
		}

		function getHostname() {
			return libvirt_connect_get_hostname($this->conn);
		}

		function getParsedResources($add = '') {
			$tmp = $this->printResources();

			$val = array();
			for ($i = 0; $i < sizeof($tmp); $i++) {
				$tmp2 = explode(' ', $tmp[$i]);

				$type = $tmp2[1];
				$type[0] = strtoupper($type[0]);

				$addr = $tmp2[4];

				$addrConn = false;
				if ($type == 'Domain')
					$addrConn = str_replace(')', '', $tmp2[6]);

				$val[] = array(
						'objType' => $type,
						'addr' => $addr,
						'conn' => $addrConn,
						'msg' => $tmp[$i]
						);
			}

			return $val;
		}

		function logInArray($arr, $entry) {
			for ($i = 0; $i < sizeof($arr); $i++)
				if ($arr[$i] == $entry)
					return true;

			return false;
		}

		function updateResources() {
			$tmp = $this->getParsedResources();

			for ($i = 0; $i < sizeof($tmp); $i++)
				if (!$this->logInArray($this->_oldRes, $tmp[$i]))
					$this->log(TYPE_CREATE, $tmp[$i]['objType'], $tmp[$i]['addr'],
						$tmp[$i]['conn'], $tmp[$i]['msg']);

			for ($i = 0; $i < sizeof($this->_oldRes); $i++)
				if (!$this->logInArray($tmp, $this->_oldRes[$i])) {
					$this->log(TYPE_DESTROY, $this->_oldRes[$i]['objType'],
						$this->_oldRes[$i]['addr'], $this->_oldRes[$i]['conn'],
						$this->_oldRes[$i]['msg']);
			}

			$this->_oldRes = $tmp;
		}

		function getDomainObject($nameRes) {
			if (is_resource($nameRes))
				return $nameRes;

			$dom=libvirt_domain_lookup_by_name($this->conn, $nameRes);
			if (!$dom) {
				$dom=libvirt_domain_lookup_by_uuid_string($this->conn, $nameRes);
				if (!$dom)
					return $this->_set_last_error();
			}

			$this->updateResources();
			return $dom;
		}

		function resourceUnset($res) {
			$this->updateResources();
			unset($res);
			$res = null;
			return $res;
		}

		function getXPath($domain, $xpath, $inactive = false) {
			$dom = $this->getDomainObject($domain);
			if ($dom == false)
				return false;

			$flags = 0;
			if ($inactive)
				$flags = VIR_DOMAIN_XML_INACTIVE;

			$tmp = libvirt_domain_xml_xpath($dom, $xpath, $flags); 
			if (!$tmp)
				return $this->_set_last_error();

			return $tmp;
		}

		function getCdromStats($domain, $sort=true) {
			$dom = $this->getDomainObject($domain);

			$buses =  $this->getXPath($dom, '//domain/devices/disk[@device="cdrom"]/target/@bus', false);
			$disks =  $this->getXPath($dom, '//domain/devices/disk[@device="cdrom"]/target/@dev', false);
			$files =  $this->getXPath($dom, '//domain/devices/disk[@device="cdrom"]/source/@file', false);

			$ret = array();
			for ($i = 0; $i < $disks['num']; $i++) {
				$tmp = @libvirt_domain_get_block_info($dom, $disks[$i]);
				if ($tmp) {
					$tmp['bus'] = $buses[$i];
					$ret[] = $tmp;
				}
				else {
					$this->_set_last_error();

					$ret[] = array(
							'device' => $disks[$i],
							'file'   => $files[$i],
							'type'   => '-',
							'capacity' => '-',
							'allocation' => '-',
							'physical' => '-',
							'bus' => $buses[$i]
                                                        );
				}
			}

			if ($sort) {
				for ($i = 0; $i < sizeof($ret); $i++) {
					for ($ii = 0; $ii < sizeof($ret); $ii++) {
						if (strcmp($ret[$i]['device'], $ret[$ii]['device']) < 0) {
							$tmp = $ret[$i];
							$ret[$i] = $ret[$ii];
							$ret[$ii] = $tmp;
						}
					}
				}
			}

			unset($buses);
			unset($disks);
			unset($files);

			return $ret;
		}

		function getDiskStats($domain, $sort=true) {
			$dom = $this->getDomainObject($domain);

			$buses =  $this->getXPath($dom, '//domain/devices/disk[@device="disk"]/target/@bus', false);
			$disks =  $this->getXPath($dom, '//domain/devices/disk[@device="disk"]/target/@dev', false);
			$files =  $this->getXPath($dom, '//domain/devices/disk[@device="disk"]/source/@file', false);

			$ret = array();
			for ($i = 0; $i < $disks['num']; $i++) {
				$tmp = libvirt_domain_get_block_info($dom, $disks[$i]);
				if ($tmp) {
					$tmp['bus'] = $buses[$i];
					$ret[] = $tmp;
				}
				else {
					$this->_set_last_error();

					$ret[] = array(
							'device' => $disks[$i],
							'file'   => $files[$i],
							'type'   => '-',
							'capacity' => '-',
							'allocation' => '-',
							'physical' => '-',
							'bus' => $buses[$i]
							);
				}
			}

			if ($sort) {
				for ($i = 0; $i < sizeof($ret); $i++) {
					for ($ii = 0; $ii < sizeof($ret); $ii++) {
						if (strcmp($ret[$i]['device'], $ret[$ii]['device']) < 0) {
							$tmp = $ret[$i];
							$ret[$i] = $ret[$ii];
							$ret[$ii] = $tmp;
						}
					}
				}
			}

			unset($buses);
			unset($disks);
			unset($files);

			return $ret;
		}

                function getNicInfo($domain) {
                        $dom = $this->getDomainObject($domain);

                        $macs =  $this->getXPath($dom, '//domain/devices/interface/mac/@address', false);
			if (!$macs)
				return $this->_set_last_error();

			$ret = array();
			for ($i = 0; $i < $macs['num']; $i++) {
				$tmp = libvirt_domain_get_network_info($dom, $macs[$i]);
				if ($tmp)
					$ret[] = $tmp;
				else {
					$this->_set_last_error();

					$ret[] = array(
							'mac' => $macs[$i],
							'network' => '-',
							'nic_type' => '-'
							);
				}
			}

                        return $ret;
                }

                function getDomainType($domain) {
                        $dom = $this->getDomainObject($domain);

                        $tmp = $this->getXPath($dom, '//domain/@type', false);
                        if ($tmp['num'] == 0)
                            return $this->_set_last_error();

                        $ret = $tmp[0];
                        unset($tmp);

                        return $ret;
                }

                function getDomainMachineType($domain) {
                        $dom = $this->getDomainObject($domain);

                        $tmp = $this->getXPath($dom, '//domain/os/type/@machine', false);
                        if ($tmp['num'] == 0)
                            return $this->_set_last_error();

                        $ret = $tmp[0];
                        unset($tmp);

                        return $ret;
                }

                function getDomainEmulator($domain) {
                        $dom = $this->getDomainObject($domain);

                        $tmp =  $this->getXPath($dom, '//domain/devices/emulator', false);
                        if ($tmp['num'] == 0)
                            return $this->_set_last_error();

                        $ret = $tmp[0];
                        unset($tmp);

                        return $ret;
                }

		function getNetworkCards($domain) {
			$dom = $this->getDomainObject($domain);

			$nics = $this->getXPath($dom, '//domain/devices/interface[@type="network"]', false);
			if (is_bool($nics))
				return 0;//$this->_set_last_error();

			return $nics['num'];
		}

		function getDiskCapacity($domain, $physical=false, $disk='*', $unit='?') {
			$dom = $this->getDomainObject($domain);
			$tmp = $this->getDiskStats($dom);

			$ret = 0;
			for ($i = 0; $i < sizeof($tmp); $i++) {
				if (($disk == '*') || ($tmp[$i]['device'] == $disk))
					if ($physical)
						$ret += $tmp[$i]['physical'];
					else
						$ret += $tmp[$i]['capacity'];
			}
			unset($tmp);

			return $this->formatSize($ret, 2, $unit);
		}

		function getDiskCount($domain) {
			$dom = $this->getDomainObject($domain);
			$tmp = $this->getDiskStats($dom);
			$ret = sizeof($tmp);
			unset($tmp);

			return $ret;
		}

		function formatSize($value, $decimals, $unit='?') {
			if ($value == '-')
				return 'unknown';

			/* Autodetect unit that's appropriate */
			if ($unit == '?') {
				/* (1 << 40) is not working correctly on i386 systems */
				if ($value > 1099511627776)
					$unit = 'T';
				else
				if ($value > (1 << 30))
					$unit = 'G';
				else
				if ($value > (1 << 20))
					$unit = 'M';
				else
				if ($value > (1 << 10))
					$unit = 'K';
				else
					$unit = 'B';
			}

			$unit = strtoupper($unit);

			switch ($unit) {
				case 'T': return number_format($value / (float)1099511627776, $decimals, '.', ' ').' TB';
				case 'G': return number_format($value / (float)(1 << 30), $decimals, '.', ' ').' GB';
				case 'M': return number_format($value / (float)(1 << 20), $decimals, '.', ' ').' MB';
				case 'K': return number_format($value / (float)(1 << 10), $decimals, '.', ' ').' kB';
				case 'B': return $value.' B';
			}

			return false;
		}

		function getUri() {
			$tmp = libvirt_connect_get_uri($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getDomainCount() {
			$tmp = libvirt_domain_get_counts($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getStoragePools() {
			$tmp = libvirt_list_storagepools($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getStoragePoolRes($res) {
			if ($res == false)
				return false;
			if (is_resource($res))
				return $res;

			$tmp = libvirt_storagepool_lookup_by_name($this->conn, $res);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getStoragePoolInfo($name) {
			if (!($res = $this->getStoragePoolRes($name)))
				return false;

			$path = libvirt_storagepool_get_xml_desc($res, '/pool/target/path');
			if (!$path)
				return $this->_set_last_error();
			$perms = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/mode');
			if (!$perms)
				return $this->_set_last_error();
			$otmp1 = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/owner');
			if (!is_string($otmp1))
				return $this->_set_last_error();
			$otmp2 = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/group');
			if (!is_string($otmp2))
				return $this->_set_last_error();
			$tmp = libvirt_storagepool_get_info($res);
			$tmp['volume_count'] = sizeof( libvirt_storagepool_list_volumes($res) );
			$tmp['active'] = libvirt_storagepool_is_active($res);
			$tmp['path'] = $path;
			$tmp['permissions'] = $perms;
			$tmp['id_user'] = $otmp1;
			$tmp['id_group'] = $otmp2;

			return $tmp;
		}

		function storagePoolGetVolumeInformation($pool, $name=false) {
			if (!is_resource($pool))
				$pool = $this->getStoragePoolRes($pool);
			if (!$pool)
				return false;

			$out = array();
			$tmp = libvirt_storagepool_list_volumes($pool);
			for ($i = 0; $i < sizeof($tmp); $i++) {
				if (($tmp[$i] == $name) || ($name == false)) {
					$r = libvirt_storagevolume_lookup_by_name($pool, $tmp[$i]);
					$out[$tmp[$i]] = libvirt_storagevolume_get_info($r);
					$out[$tmp[$i]]['path'] = libvirt_storagevolume_get_path($r);
					unset($r);
				}
			}

			return $out;
		}

		function storageVolumeDelete($path) {
			$vol = libvirt_storagevolume_lookup_by_path($this->conn, $path);
			if (!libvirt_storagevolume_delete($vol))
				return $this->_set_last_error();

			return true;
		}

		function translateVolumeType($type) {
			if ($type == 1)
				return 'Block device';

			return 'File image';
		}

		function translatePerms($mode) {
			$mode = (string)((int)$mode);

			$tmp = '---------';

			for ($i = 0; $i < 3; $i++) {
				$bits = (int)$mode[$i];
				if ($bits & 4)
					$tmp[ ($i * 3) ] = 'r';
				if ($bits & 2)
					$tmp[ ($i * 3) + 1 ] = 'w';
				if ($bits & 1)
					$tmp[ ($i * 3) + 2 ] = 'x';
			}
			

			return $tmp;
		}

		function parseSize($size) {
			$unit = $size[ strlen($size) - 1 ];

			$size = (int)$size;
			switch (strtoupper($unit)) {
				case 'T': $size *= 1099511627776;
					  break;
				case 'G': $size *= 1073741824;
					  break;
				case 'M': $size *= 1048576;
					  break;
				case 'K': $size *= 1024;
					  break;
			}

			return $size;
		}

		function createNewVM($input) {
			$skip = false;
			$msg = false;
			if (array_key_exists('sent', $input)) {
				$features = array('apic', 'acpi', 'pae', 'hap');

				if ($input['install_img'] != '-') {
					$iso_path = ini_get('libvirt.iso_path');
					$img = $iso_path.'/'.$input['install_img'];
				}
				else
					$img = $input['install_img'];

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
				if (array_key_exists('setup_disk', $input) && ($input['setup_disk'])) {
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

				$tmp = $this->domainCreateNew($input['name'], $img, $input['cpu_count'], $feature,
							$input['guest_memory'], $input['guest_maxmem'], $input['clock_offset'],
							$nic, $disk, $input['sound_type'], $input['setup_persistent']);

				return $tmp ? 1 : 2;
			}

			return 0;
		}

		function storageVolumeCreate($pool, $name, $capacity, $allocation) {
			$pool = $this->getStoragePoolRes($pool);

			$capacity = $this->parseSize($capacity);
			$allocation = $this->parseSize($allocation);

			$xml = "<volume>\n".
                               "  <name>$name</name>\n".
                               "  <capacity>$capacity</capacity>\n".
                               "  <allocation>$allocation</allocation>\n".
                               "</volume>";

			$tmp = libvirt_storagevolume_create_xml($pool, $xml);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getHypervisorName() {
			$tmp = libvirt_connect_get_information($this->conn);
			$hv = $tmp['hypervisor'];
			unset($tmp);

			switch (strtoupper($hv)) {
				case 'QEMU': $type = 'qemu';
					break;
				case 'XEN': $type = 'xen';
					break;

				default:
					$type = $hv;
			}

			return $type;
		}

		function getConnectInformation() {
			if (!$this->isConnected())
				return false;
			$tmp = libvirt_connect_get_information($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainChangeXml($domain, $xml) {
			$dom = $this->getDomainObject($domain);

			if (!($old_xml = libvirt_domain_get_xml_desc($dom, NULL)))
				return $this->_set_last_error();
			if (!libvirt_domain_undefine($dom))
				return $this->_set_last_error();
			if (!libvirt_domain_define_xml($this->conn, $xml)) {
				$this->last_error = libvirt_get_last_error();
				libvirt_domain_define_xml($this->conn, $old_xml);
				return false;
			}

			return true;
		}

		function domainChangeByArray($data) {
			$domain = $data['domain']['name'];

			if ($data['boot']['first'])
				$this->domainChangeBootDevices($domain, $data['boot']['first'], $data['boot']['second']);

			$dom = $this->getDomainObject($domain);

			$xml = $this->domainGetXml($dom, true);
			$tmp = explode("\n", $xml);
			for ($i = 0; $i < sizeof($tmp); $i++) {
				if ((strpos($tmp[$i], '<type arch=')) && ($data['arch'])) {
					$os_type = "<type arch='{$data['arch']}' machine='{$data['domain']['machine']}'>hvm</type>";

					$tmp[$i] = str_replace($tmp[$i], $os_type, $tmp[$i]);
				}
				else
				if ((strpos($tmp[$i], '<emulator')) && (array_key_exists('emulator', $data['domain'])))
					$tmp[$i] = str_replace($tmp[$i], '<emulator>'.$data['domain']['emulator'].'</emulator>', $tmp[$i]);
				else
				if ((strpos($tmp[$i], '<clock offset')) && (array_key_exists('clock', $data)) && ($data['clock']))
					$tmp[$i] = str_replace($tmp[$i], "<clock offset='{$data['clock']}'/>", $tmp[$i]);
				else
				if ((strpos($tmp[$i], 'domain type=')) && (array_key_exists('type', $data['domain'])) && ($data['domain']['type']))
					$tmp[$i] = str_replace($tmp[$i], "<domain type='{$data['domain']['type']}'>", $tmp[$i]);
				else
				if ((strpos($tmp[$i], '<vcpu>')) && (array_key_exists('cpu', $data['domain'])) && ($data['domain']['cpu']))
					$tmp[$i] = str_replace($tmp[$i], "<vcpu>{$data['domain']['cpu']}</vcpu>", $tmp[$i]);
				else
				if ((strpos($tmp[$i], '<memory')) && (array_key_exists('maxmem', $data['domain']['memory'])) && ($data['domain']['memory']['maxmem'])) {
					$mem = $data['domain']['memory']['maxmem'] * 1024;
					$tmp[$i] = str_replace($tmp[$i], "<memory unit='KiB'>$mem</memory>", $tmp[$i]);
				}
				else
				if ((strpos($tmp[$i], '<currentMemory')) && (array_key_exists('current', $data['domain']['memory'])) && ($data['domain']['memory']['current'])) {
					$mem = $data['domain']['memory']['current'] * 1024;
					$tmp[$i] = str_replace($tmp[$i], "<currentMemory unit='KiB'>$mem</currentMemory>", $tmp[$i]);
				}
			}

			$xml = implode("\n", $tmp);

			/* Features */
			if (!strpos($xml, '<features>')) {
				$ak = array_keys($data['features']);
				$tmp = '';
				for ($i = 0; $i < sizeof($ak); $i++) {
					$key = $ak[$i];

					if ($data['features'][$key])
						$tmp .= '<'.$key."/>\n";
				}

				if ($tmp)
					$xml = str_replace('</os>', "</os>\n<features>\n$tmp</features>\n", $xml);
				echo $xml;
				exit;
			}
			else {
				$tmp = explode('<features>', $xml);
				$tmp2 = explode('</features>', $tmp[1]);

				$tmpx = explode("\n", $tmp2[0]);

				$ak = array_keys($data['features']);
				for ($i = 0; $i < sizeof($ak); $i++) {
					$key = $ak[$i];
					$val = $data['features'][$key];

					if (!is_bool($val)) {
						if ($val)
							$tmp2[0] .= ' <'.$key.'/>';
						else
							$tmp2[0] = str_replace('<'.$key.'/>', '', $tmp2[0]);
					}
				}

				$tmp[1] = implode('</features>', $tmp2);
				$xml = implode('<features>', $tmp);
			}

			return $this->domainChangeXml($dom, $xml);
		}

		function networkChangeXml($network, $xml) {
			$net = $this->getNetworkRes($network);

			if (!($old_xml = libvirt_network_get_xml_desc($net, NULL))) {
				return $this->_set_last_error();
			}
			if (!libvirt_network_undefine($net)) {
				return $this->_set_last_error();
			}
			if (!libvirt_network_define_xml($this->conn, $xml)) {
				$this->last_error = libvirt_get_last_error();
				libvirt_network_define_xml($this->conn, $old_xml);
				return false;
			}

			return true;
		}

		function connectGetMachineTypes() {
			return libvirt_connect_get_machine_types($this->conn);
		}

		function networkNew($name, $ipinfo, $dhcpinfo=false, $forward=false, $forward_dev=false, $bridge=false, $edit=false) {
			$uuid = $this->networkGenerateUuid();
			if (!$bridge) {
				$maxid = -1;
				$nets = $this->getNetworks();
				for ($i = 0; $i < sizeof($nets); $i++) {
					$bridge = $this->getNetworkBridge($nets[$i]);
					if ($bridge) {
						$tmp = explode('br', $bridge);
						$id = (int)$tmp[1];

						if ($id > $maxid)
							$maxid = $id;
					}
				}

				$newid = $maxid + 1;
				$bridge = 'virbr'.$newid;
			}

			$forwards = '';
			if ($forward) {
				if (!$forward_dev)
					$forwards = "<forward mode='$forward' />";
				else
					$forwards = "<forward mode='$forward' dev='$forward_dev' />";
			}

			/* array('ip' => $ip, 'netmask' => $mask) has been passed */
			if (is_array($ipinfo)) {
				$ip = $ipinfo['ip'];
				$mask = $ipinfo['netmask'];
			}
			else {
				/* CIDR definition otherwise, like 192.168.122.0/24 */
				$tmp = explode('/', $ipinfo);
				$ipc = explode('.', $tmp[0]);
				$ipc[3] = (int)$ipc[3] + 1;
				$ip = implode('.', $ipc);

				$bin = '';
				for ($i = 0; $i < $tmp[1]; $i++)
					$bin .= '1';

				$tmp = bindec($bin);
				$ipc[0] = $tmp         % 256;
				$ipc[1] = ($tmp >> 8 ) % 256;
				$ipc[2] = ($tmp >> 16) % 256;
				$ipc[3] = ($tmp >> 24) % 256;

				$mask = implode('.', $ipc);
			}

			$dhcps = '';
			if ($dhcpinfo) {
				/* For definition like array('start' => $dhcp_start, 'end' => $dhcp_end) */
				if (is_array($dhcpinfo)) {
					$dhcp_start = $dhcpinfo['start'];
					$dhcp_end = $dhcpinfo['end'];
				}
				else {
					/* Definition like '$dhcp_start - $dhcp_end' */
					$tmp = explode('-', $dhcpinfo);
					$dhcp_start = Trim($tmp[0]);
					$dhcp_end = Trim($tmp[1]);
				}

				$dhcps = "<dhcp>
                                                <range start='$dhcp_start' end='$dhcp_end' />
                                        </dhcp>";
			}

			$xml = "<network>
				<name>$name</name>
				<uuid>$uuid</uuid>
				$forwards
				<bridge name='$bridge' stp='on' delay='0' />
				<ip address='$ip' netmask='$mask'>
					$dhcps
				</ip>
				</network>";

			if ($edit)
				$this->networkUndefine($name);
			return $this->networkDefine($xml);
		}

		function networkDefine($xml) {
			$tmp = @libvirt_network_define_xml($this->conn, $xml);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function networkUndefine($network) {
			$net = $this->getNetworkRes($network);
			$tmp = libvirt_network_undefine($net);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function translateStoragePoolState($state) {
			$lang = $this->_langObject;
			$ret = $lang->get('unknown');
			switch ($state) {
				case 0: $ret = $lang->get('pool-not-running');
					break;
				case 1: $ret = $lang->get('pool-building');
					break;
				case 2: $ret = $lang->get('pool-running');
					break;
				case 3: $ret = $lang->get('pool-running-deg');
					break;
				case 4: $ret = $lang->get('pool-running-inac');
					break;
			}
			unset($lang);

			return $ret;
		}

		function getDomains() {
			$tmp = libvirt_list_domains($this->conn);
			return ($tmp) ? $this->sortArray($tmp) : $this->_set_last_error();
		}

		function getDomainByName($name) {
			$tmp = libvirt_domain_lookup_by_name($this->conn, $name);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNetworks($type = VIR_NETWORKS_ALL) {
			$tmp = libvirt_list_networks($this->conn, $type);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNicModels($arch = false) {
			$tmp = libvirt_connect_get_nic_models($this->conn, $arch);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getSoundHwModels($arch = false) {
			$tmp = libvirt_connect_get_soundhw_models($this->conn, $arch, VIR_CONNECT_FLAG_SOUNDHW_GET_NAMES);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNetworkRes($network) {
			if ($network == false)
				return false;
			if (is_resource($network))
				return $network;

			$tmp = libvirt_network_get($this->conn, $network);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNetworkBridge($network) {
			$res = $this->getNetworkRes($network);
			if ($res == false)
				return false;

			$tmp = libvirt_network_get_bridge($res);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNetworkActive($network) {
			$res = $this->getNetworkRes($network);
			if ($res == false)
				return false;

			$tmp = libvirt_network_get_active($res);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function setNetworkActive($network, $active = true) {
			$res = $this->getNetworkRes($network);
			if ($res == false)
				return false;

			if (!libvirt_network_set_active($res, $active ? 1 : 0))
				return $this->_set_last_error();

			return true;
		}

		function getNetworkInformation($network) {
			$res = $this->getNetworkRes($network);
			if ($res == false)
				return false;

			$tmp = libvirt_network_get_information($res);
			if (!$tmp)
				return $this->_set_last_error();
			$tmp['active'] = $this->getNetworkActive($res);
			return $tmp;
		}

		function getNetworkXml($network) {
			$res = $this->getNetworkRes($network);
			if ($res == false)
				return false;

			$tmp = libvirt_network_get_xml_desc($res, NULL);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNodeDevices($dev = false) {
			$tmp = ($dev == false) ? libvirt_list_nodedevs($this->conn) : libvirt_list_nodedevs($this->conn, $dev);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNodeDeviceRes($res) {
			if ($res == false)
				return false;
			if (is_resource($res))
				return $res;

			$tmp = libvirt_nodedev_get($this->conn, $res);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNodeDeviceCaps($dev) {
			$dev = $this->getNodeDeviceRes($dev);

			$tmp = libvirt_nodedev_capabilities($dev);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNodeDeviceCapOptions() {
			$all = $this->getNodeDevices();

			$ret = array();
			for ($i = 0; $i < sizeof($all); $i++) {
				$tmp = $this->getNodeDeviceCaps($all[$i]);

				for ($ii = 0; $ii < sizeof($tmp); $ii++)
					if (!in_array($tmp[$ii], $ret))
						$ret[] = $tmp[$ii];
			}

			return $ret;
		}

		function getNodeDeviceXml($dev) {
			$dev = $this->getNodeDeviceRes($dev);

			$tmp = libvirt_nodedev_get_xml_desc($dev, NULL);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getNodeDeviceInformation($dev) {
			$dev = $this->getNodeDeviceRes($dev);

			$tmp = libvirt_nodedev_get_information($dev);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetName($res) {
			if (!is_resource($res))
				return false;

			return libvirt_domain_get_name($res);
		}

		function domainGetInfoCall($name = false, $name_override = false) {
			$ret = array();

			if ($name != false) {
				$dom = $this->getDomainObject($name);
				if (!$dom)
					return false;

				if ($name_override)
					$name = $name_override;

				$ret[$name] = libvirt_domain_get_info($dom);
				return $ret;
			}
			else {
				$doms = libvirt_list_domains($this->conn);
				foreach ($doms as $dom) {
					$tmp = $this->domainGetName($dom);
					$ret[$tmp] = libvirt_domain_get_info($dom);
				}
			}

			ksort($ret);
			return $ret;
		}

		function domainGetInfo($name = false, $name_override = false) {
			if (!$name)
				return false;

			if (!$this->allow_cached)
				return $this->domainGetInfoCall($name, $name_override);

			$domname = $name_override ? $name_override : $name;
			$domkey  = $name_override ? $name_override : $this->domainGetName($name);

			if ((!$domkey) && (is_string($name))) {
				$dom = $this->getDomainObject($name);
				if (!$dom)
					return false;
				return libvirt_domain_get_info($dom);
			}

			if (!array_key_exists($domkey, $this->dominfos)) {
				$tmp = $this->domainGetInfoCall($name, $name_override);
				$this->dominfos[$domkey] = $tmp[$domname];
			}

			return $this->dominfos[$domkey];
		}

		function getLastError() {
			return $this->last_error;
		}

		function domainGetXml($domain, $get_inactive = false) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_get_xml_desc($dom, $get_inactive ? VIR_DOMAIN_XML_INACTIVE : 0);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function networkGetXml($network) {
			$net = $this->getNetworkRes($network);
			if (!$net)
				return false;

			$tmp = libvirt_network_get_xml_desc($net, NULL);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function networkGetXPath($network, $xpath) {
			$net = $this->getNetworkRes($network);
			if (!$net)
				return false;

			$tmp = libvirt_network_get_xml_desc($net, $xpath);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetId($domain, $name = false) {
			$dom = $this->getDomainObject($domain);
			if ((!$dom) || (!$this->domainIsRunning($dom, $name)))
				return false;

			$tmp = libvirt_domain_get_id($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetInterfaceStats($nameRes, $iface) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_interface_stats($dom, $iface);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetMemoryStats($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_memory_stats($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainStart($dom) {
			$dom=$this->getDomainObject($dom);
			if ($dom) {
				$ret = @libvirt_domain_create($dom);
				$this->last_error = libvirt_get_last_error();
				return $ret;
			}

			$ret = libvirt_domain_create_xml($this->conn, $dom);
			$this->last_error = libvirt_get_last_error();
			return $ret;
		}

		function domainDefine($xml) {
			$tmp = libvirt_domain_define_xml($this->conn, $xml);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainDestroy($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = @libvirt_domain_destroy($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainReboot($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_reboot($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainSuspend($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_suspend($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainResume($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_resume($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainGetNameByUuid($uuid) {
			$dom = @libvirt_domain_lookup_by_uuid_string($this->conn, $uuid);
			if (!$dom)
				return false;
			$tmp = libvirt_domain_get_name($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainIsPersistent($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;
			$tmp = libvirt_domain_is_persistent($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function generateUuid($seed=false) {
			if (!$seed)
				$seed = time();
			srand($seed);

			$ret = array();
			for ($i = 0; $i < 16; $i++)
				$ret[] = $this->_macbyte(rand() % 256);

			$a = $ret[0].$ret[1].$ret[2].$ret[3];
			$b = $ret[4].$ret[5];
			$c = $ret[6].$ret[7];
			$d = $ret[8].$ret[9];
			$e = $ret[10].$ret[11].$ret[12].$ret[13].$ret[14].$ret[15];

			return $a.'-'.$b.'-'.$c.'-'.$d.'-'.$e;
		}

		function domainGenerateUuid() {
			$uuid = $this->generateUuid();

			while ($this->domainGetNameByUuid($uuid))
				$uuid = $this->generateUuid();

			return $uuid;
		}

		function networkGenerateUuid() {
			/* TODO: Fix after virNetworkLookupByUUIDString is exposed
				 to libvirt-php to ensure UUID uniqueness */
			return $this->generateUuid();
		}

		function domainShutdown($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_shutdown($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainUndefine($domain) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = libvirt_domain_undefine($dom);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function domainIsRunning($domain, $name = false) {
			$dom = $this->getDomainObject($domain);
			if (!$dom)
				return false;

			$tmp = $this->domainGetInfo( $domain, $name );
			if (!$tmp)
				return $this->_set_last_error();
			$ret = ( ($tmp['state'] == VIR_DOMAIN_RUNNING) || ($tmp['state'] == VIR_DOMAIN_BLOCKED) );
			unset($tmp);
			return $ret;
		}

		function translateDomainState($state) {
			$lang = $this->_langObject;

			$ret = $lang->get('unknown');
			switch ($state) {
				case VIR_DOMAIN_RUNNING:  $ret = $lang->get('domain-running');
						  	  break;
				case VIR_DOMAIN_NOSTATE:  $ret = $lang->get('domain-nostate');
							  break;
				case VIR_DOMAIN_BLOCKED:  $ret = $lang->get('domain-blocked');
							  break;
				case VIR_DOMAIN_PAUSED:   $ret = $lang->get('domain-paused');
							  break;
				case VIR_DOMAIN_SHUTDOWN: $ret = $lang->get('domain-shutdown');
							  break;
				case VIR_DOMAIN_SHUTOFF:  $ret = $lang->get('domain-shutoff');
							  break;
				case VIR_DOMAIN_CRASHED:  $ret = $lang->get('domain-crashed');
							  break;
			}
			unset($lang);

			return $ret;
		}

		function domainGetGraphicsPort($domain, $type) {
			$tmp = $this->getXPath($domain, '//domain/devices/graphics[@type="'.$type.'"]/@port', false);
			$var = (int)$tmp[0];
			unset($tmp);

			return $var;
		}

		function domainGetVncPort($domain) {
			return $this->domainGetGraphicsPort($domain, 'vnc');
		}

		function domainGetSpicePort($domain) {
			return $this->domainGetGraphicsPort($domain, 'spice');
		}

		function domainGetArch($domain) {
			$domain = $this->getDomainObject($domain);

			$tmp = $this->getXPath($domain, '//domain/os/type/@arch', false);
			$var = $tmp[0];
			unset($tmp);

			return $var;
		}

		function domainGetDescription($domain) {
			$tmp = $this->getXPath($domain, '//domain/description', false);
			$var = $tmp[0];
			unset($tmp);

			return $var;
		}

		function domainGetClockOffset($domain) {
			$tmp = $this->getXPath($domain, '//domain/clock/@offset', false);
			$var = $tmp[0];
			unset($tmp);

			return $var;
		}

		function domainGetFeature($domain, $feature) {
			$tmp = $this->getXPath($domain, '//domain/features/'.$feature.'/..', false);
			$ret = ($tmp != false);
			unset($tmp);

			return $ret;
		}

		function domainGetBootDevices($domain) {
			$tmp = $this->getXPath($domain, '//domain/os/boot/@dev', false);
			if (!$tmp)
				return false;

			$devs = array();
			for ($i = 0; $i < $tmp['num']; $i++)
				$devs[] = $tmp[$i];

			return $devs;
		}

		function _getSingleXPathResult($domain, $xpath) {
			$tmp = $this->getXPath($domain, $xpath, false);
			if (!$tmp)
				return false;

			if ($tmp['num'] == 0)
				return false;

			return $tmp[0];
		}

		function domainGetMultimediaDevice($domain, $type, $display=false) {
			$domain = $this->getDomainObject($domain);

			if ($type == 'console') {
				$type = $this->_getSingleXPathResult($domain, '//domain/devices/console/@type');
				$targetType = $this->_getSingleXPathResult($domain, '//domain/devices/console/target/@type');
				$targetPort = $this->_getSingleXPathResult($domain, '//domain/devices/console/target/@port');

				if ($display)
					return $type.' ('.$targetType.' on port '.$targetPort.')';
				else
					return array('type' => $type, 'targetType' => $targetType, 'targetPort' => $targetPort);
			}
			else
			if ($type == 'input') {
				$type = $this->_getSingleXPathResult($domain, '//domain/devices/input/@type');
				$bus  = $this->_getSingleXPathResult($domain, '//domain/devices/input/@bus');

				if ($display)
					return $type.' on '.$bus;
				else
					return array('type' => $type, 'bus' => $bus);
			}
			else
			if ($type == 'graphics') {
				$type = $this->_getSingleXPathResult($domain, '//domain/devices/graphics/@type');
				$port = $this->_getSingleXPathResult($domain, '//domain/devices/graphics/@port');
				$autoport = $this->_getSingleXPathResult($domain, '//domain/devices/graphics/@autoport');

				if ($display)
					return $type.' on port '.$port.' with'.($autoport ? '' : 'out').' autoport enabled';
				else
					return array('type' => $type, 'port' => $port, 'autoport' => $autoport);
			}
			else
			if ($type == 'sound') {
				$desc = false;
				$type = $this->_getSingleXPathResult($domain, '//domain/devices/sound/@model');
				$tmp = $this->getSoundHwModels();
				for ($i = 0; $i < sizeof($tmp); $i++) {
					if ($tmp[$i]['name'] == $type)
						$desc = $tmp[$i]['description'];
				}

				return $desc ? $desc : $type;
			}
			else
			if ($type == 'video') {
				$type  = $this->_getSingleXPathResult($domain, '//domain/devices/video/model/@type');
				$vram  = $this->_getSingleXPathResult($domain, '//domain/devices/video/model/@vram');
				$heads = $this->_getSingleXPathResult($domain, '//domain/devices/video/model/@heads');

				if ($display)
					return $type.' with '.($vram / 1024).' MB VRAM, '.$heads.' head(s)';
				else
					return array('type' => $type, 'vram' => $vram, 'heads' => $heads);
			}
			else
				return false;
		}

		function domainGetHostDevicesPci($domain) {
			$xpath = '//domain/devices/hostdev[@type="pci"]/source/address/@';

			$dom  = $this->getXPath($domain, $xpath.'domain', false);
			$bus  = $this->getXPath($domain, $xpath.'bus', false);
			$slot = $this->getXPath($domain, $xpath.'slot', false);
			$func = $this->getXPath($domain, $xpath.'function', false);

			$devs = array();
			for ($i = 0; $i < $bus['num']; $i++) {
				$d = str_replace('0x', '', $dom[$i]);
				$b = str_replace('0x', '', $bus[$i]);
				$s = str_replace('0x', '', $slot[$i]);
				$f = str_replace('0x', '', $func[$i]);
				$devid = 'pci_'.$d.'_'.$b.'_'.$s.'_'.$f;
				$tmp2 = $this->get_node_device_information($devid);
				$devs[] = array('domain' => $dom[$i], 'bus' => $bus[$i],
						'slot' => $slot[$i], 'func' => $func[$i],
						'vendor' => $tmp2['vendor_name'],
						'vendor_id' => $tmp2['vendor_id'],
						'product' => $tmp2['product_name'],
						'product_id' => $tmp2['product_id']);
			}

			return $devs;
		}

		function _lookupDeviceUsb($vendor_id, $product_id) {
			$tmp = $this->getNodeDevices(false);
			for ($i = 0; $i < sizeof($tmp); $i++) {
				$tmp2 = $this->getNodeDeviceInformation($tmp[$i]);
				if (array_key_exists('product_id', $tmp2)) {
					if (($tmp2['product_id'] == $product_id)
						&& ($tmp2['vendor_id'] == $vendor_id))
							return $tmp2;
				}
			}

			return false;
		}

		function domainGetHostDevicesUsb($domain) {
			$xpath = '//domain/devices/hostdev[@type="usb"]/source/';

			$vid = $this->getXPath($domain, $xpath.'vendor/@id', false);
			$pid = $this->getXPath($domain, $xpath.'product/@id', false);

			$devs = array();
			for ($i = 0; $i < $vid['num']; $i++) {
				$dev = $this->_lookupDeviceUsb($vid[$i], $pid[$i]);
				$devs[] = array('vendor_id' => $vid[$i], 'product_id' => $pid[$i],
						'product' => $dev['product_name'],
						'vendor' => $dev['vendor_name']);
			}

			return $devs;
		}

		function domainGetHostDevices($domain) {
			$domain = $this->getDomainObject($domain);

			$devs_pci = $this->domainGetHostDevicesPci($domain);
			$devs_usb = $this->domainGetHostDevicesUsb($domain);

			return array('pci' => $devs_pci, 'usb' => $devs_usb);
		}

		function domainSetFeature($domain, $feature, $val) {
			$domain = $this->getDomainObject($domain);

			if ($this->domainGetFeature($domain, $feature) == $val)
				return true;

			$xml = $this->domainGetXml($domain, true);
			if ($val) {
				if (strpos('features', $xml))
					$xml = str_replace('<features>', "<features>\n<$feature/>", $xml);
				else
					$xml = str_replace('</os>', "</os><features>\n<$feature/></features>", $xml);
			}
			else
				$xml = str_replace("<$feature/>\n", '', $xml);

			return $this->domainChangeXml($domain, $xml);
		}

		function domainSetClockOffset($domain, $offset) {
			$domain = $this->getDomainObject($domain);

			if (($old_offset = $this->domainGetClockOffset($domain)) == $offset)
				return true;

			$xml = $this->domainGetXml($domain, true);
			$xml = str_replace("<clock offset='$old_offset'/>", "<clock offset='$offset'/>", $xml);

			return $this->domainChangeXml($domain, $xml);
		}

		function domainSetDescription($domain, $desc) {
			$domain = $this->getDomainObject($domain);

			$description = $this->domainGetDescription($domain);
			if ($description == $desc)
				return true;

			$xml = $this->domainGetXml($domain, true);
			if (!$description)
				$xml = str_replace("</uuid>", "</uuid><description>$desc</description>", $xml);
			else {
				$tmp = explode("\n", $xml);
				for ($i = 0; $i < sizeof($tmp); $i++)
					if (strpos('.'.$tmp[$i], '<description'))
						$tmp[$i] = "<description>$desc</description>";

				$xml = join("\n", $tmp);
			}

			return $this->domainChangeXml($domain, $xml);
		}

		function hostGetNodeInfo() {
			$tmp = libvirt_node_get_info($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function nodeGetCpuStatsEachCpu($sec = 0) {
			$tmp = libvirt_node_get_cpu_stats_for_each_cpu($this->conn, $sec);

			if ($sec <= 1)
				return ($tmp) ? $tmp : $this->_set_last_error();

			if (!$tmp) return $this->_set_last_error();

			$numcpus = sizeof($tmp[0]) - 1;
			$numvalues = sizeof($tmp) - 1;
			$out = array();
			for ($i = 0; $i < $numcpus; $i++) {
				$out[$i]['kernel'] = (($tmp[ sizeof($tmp) - 2 ][$i]['kernel'] - $tmp[0][$i]['kernel']) / $numvalues);
				$out[$i]['iowait'] = (($tmp[ sizeof($tmp) - 2 ][$i]['iowait'] - $tmp[0][$i]['iowait']) / $numvalues);
				$out[$i]['idle'] = (($tmp[ sizeof($tmp) - 2 ][$i]['idle'] - $tmp[0][$i]['idle']) / $numvalues);
				$out[$i]['user'] = (($tmp[ sizeof($tmp) - 2 ][$i]['user'] - $tmp[0][$i]['user']) / $numvalues);
			}

			return $out;
		}

		function nodeGetCpuStatsRaw($cpu = VIR_NODE_CPU_STATS_ALL_CPUS) {
			$tmp = libvirt_node_get_cpu_stats($this->conn, $cpu);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function nodeGetCpuStats($cpu = VIR_NODE_CPU_STATS_ALL_CPUS) {
			$tmp = libvirt_node_get_cpu_stats($this->conn, $cpu);
			if (!$tmp) return $this->_set_last_error();

			// tmp has two array, collected at 1sec delay. Make a diff
			$newvalues = array();
			foreach ($tmp[0] as $key => $elem) {
				$newvalues[$key] = $tmp[1][$key] - $elem;
			}
			$newvalues['cpus'] = $tmp['cpus'];
			return $newvalues;
		}

		function nodeGetMemStats() {
			$tmp = libvirt_node_get_mem_stats($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function connectGetSysinfo() {
			$tmp = libvirt_connect_get_sysinfo($this->conn);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

		function getModuleInfo() {
			ob_start();
			PHPInfo();
			$c = ob_get_contents();
			ob_end_clean();

			$c = substr($c, strpos($c, 'module_libvirt'));
			$c = substr($c, strpos($c, 'h2') + 3);

			$p = strpos($c, 'module') - 3;
			$out = substr($c, 0, $p);

			$out = str_replace('<tr>', '<div class="item">', $out);
			$out = str_replace('<td class="e">', '<div class="label">', $out);
			$out = str_replace('</td><td class="v">', '</div><div class="value">', $out);
			$out = str_replace('</td></tr>', '</div><div class="nl" /></div>', $out);

			$tmp = explode("\n", $out);
			$start_el = false;
			$last_el = false;
			for ($i = 0; $i < sizeof($tmp); $i++) {
				if (strpos('.'.$tmp[$i], '</table'))
					$last_el = $i;
				if (strpos('.'.$tmp[$i], '<table'))
					$start_el = $i + 1;
			}

			$tmp2 = array();
			for ($i = $start_el; $i < $last_el; $i++)
				$tmp2[] = $tmp[$i];
			unset($tmp);

			return join("\n", $tmp2);
		}
	}
?>
