<?php
	class LibvirtInfo extends LoggerBase {
		private $lv = false;

		function LibvirtInfo($libvirtInstance = false) {
			if (!$libvirtInstance)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			$this->lv = $libvirtInstance;
		}

		function getModuleInfo() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

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

		function getConnectInfo() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->get_connect_information();
		}

		function getNodeInfo() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->host_get_node_info();
		}

		function getCpuStats() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->node_get_cpu_stats();
		}

		function getCpuStatsEachCPU() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->node_get_cpu_stats_each_cpu(2);
		}

		function getMemoryStats() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->node_get_mem_stats();
		}

		function getSystemInfo() {
			if (!$this->lv)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Libvirt Information', 'Libvirt class is not set');

			return $this->lv->connect_get_sysinfo();
		}

		function rpc_get($idUser, $lv, $ret) {
			if (!array_key_exists('data', $ret))
				return false;
			if (!array_key_exists('data', $ret['data']))
				return false;
			if (!array_key_exists('type', $ret['data']['data']))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Type is missing. Type can be one of following:'
						.' connection, node, cpustats, eachcpustats, memstats, system');

			switch ($ret['data']['data']['type']) {
				case 'connection':	return $lv->get_connect_information();
							break;
				case 'node':		return $lv->host_get_node_info();
							break;
				case 'cpustats':	return $lv->node_get_cpu_stats();
							break;
				case 'eachcpustats':	return $lv->node_get_cpu_stats_each_cpu(2);
							break;
				case 'memstats':	return $lv->node_get_mem_stats();
							break;
				case 'system':		return $lv->connect_get_sysinfo();
							break;
			}

			return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Type not supported. Type can be one of following: '
						.' connection, node, cpustats, eachcpustats, memstats, system');
		}
	}
?>
