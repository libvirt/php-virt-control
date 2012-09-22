<?php
	class LibvirtInfo extends LoggerBase {
		private $lv = false;

		function LibvirtInfo($libvirtInstance) {
			$this->lv = $libvirtInstance;
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

		function getConnectInfo() {
			return $this->lv->get_connect_information();
		}

		function getNodeInfo() {
			return $this->lv->host_get_node_info();
		}

		function getCpuStats() {
			return $this->lv->node_get_cpu_stats();
		}

		function getCpuStatsEachCPU() {
			return $this->lv->node_get_cpu_stats_each_cpu(2);
		}

		function getMemoryStats() {
			return $this->lv->node_get_mem_stats();
		}

		function getSystemInfo() {
			return $this->lv->connect_get_sysinfo();
		}
	}
?>
