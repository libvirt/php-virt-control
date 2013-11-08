<?php
	/**
	* Basic logger class for common usage purposes
	*/

        define('TYPE_INFO', 1);
        define('TYPE_WARN', 2);
        define('TYPE_ERROR', 3);
        define('TYPE_FATAL', 4);

	class LoggerBase {
		public $_log = array();
		public $_origin = '&lt;root class&gt;';
		public $_log_head = '&lt;log head&gt;';
		private $debug = true;

		function log($type, $lib, $msg, $data='',$origin=false) {
			if (!$this->debug) return;
			if (!$origin)
				$origin = $this->_origin;
			$this->_log[ sizeof($this->_log) ] = array(
				'type' => $type,
				'origin' => $origin,
				'lib'  => $lib,
				'msg'  => $msg,
				'data' => $data
			);
		}
		
		function safeString($str) {
			return addslashes($str);
		}

		function getConfigDir($filename = false, $all = false) {
			$dirs = array( '/etc/php-virt-control', '../data', 'data' );

			if ($all) {
				$ret = '';
				for ($i = 0; $i < sizeof($dirs); $i++) {
						if (strpos('.'.$dirs[$i], '../')) {
						$tmp2 = str_replace('../' ,'/', $dirs[$i]);
						$tmp = explode('/', getcwd());
						unset($tmp[sizeof($tmp) - 1]);
						$ret .= implode('/', $tmp).$tmp2.', ';
					}
					else
						$ret .= $dirs[$i].', ';
				}

				$ret[strlen($ret) - 2] = ' ';
				return $ret;
			}

			for ($i = 0; $i < sizeof($dirs); $i++) {
				if (is_dir($dirs[$i])) {
					if (!$filename)
						return $dirs[$i];
					if (file_exists($dirs[$i].'/'.$filename))
						return $dirs[$i].'/'.$filename;
				}
			}

			return false;
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
			for ($i = 0; $i < sizeof($ak); $i++)
				$res[] = $tmp[$ak[$i]];

			return $res;
		}

		function generateRandomChars($len = 8) {
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

			$ret = '';
			for ($i = 0; $i < $len; $i++)
				$ret = $ret.$chars[rand() % strlen($chars)];

			return $ret;
		}
		
		function getData($data, $name) {
			return array_key_exists($name, $data['data']) ? $data['data'][$name] : false;
		}

		function setData($data, $name, $value) {
			if ((is_bool($value)) && ($value == false))
				return $data;

			$data['data'][$name] = $value;

			return $data;
		}

		function setLog($log_obj) {
			if (!$this->debug) return;

			$this->_log = $log_obj;
		}

		function getLog() {
			return $this->_log;
		}

		function _type_fmt($type) {
			if ($type == TYPE_INFO)
				return 'Information';
			else
			if ($type == TYPE_ERROR)
				return 'Error';
			else
			if ($type == TYPE_WARN)
				return 'Warning';
			else
			if ($type == TYPE_FATAL)
				return 'Fatal error';

			return false;
		}

		function _log_print($data) {
			$type = $this->_type_fmt($data['type']);

			if ($type == false)
				return false;

			switch ($data['type']) {
				case TYPE_INFO: $class = 'log_info';
						break;
				case TYPE_ERROR: $class = 'log_error';
						break;
				case TYPE_FATAL: $class = 'log_fatal';
						break;
				case TYPE_WARN: $class = 'log_warn';
						break;
			}

			return "<tr class=\"$class\"><td>$type</td><td>{$data['origin']}</td>"
				."<td>{$data['lib']}</td><td>{$data['msg']}</td><td>{$data['data']}</td></tr>";
		}

		function logDump($filename = false, $showFooter = false) {
			if (!$this->debug) return;

			if ($filename) {
				$fp = fopen($filename, 'w');
				for ($i = 0; $i < sizeof($this->_log); $i++) {
					$data = $this->_log[$i];
					$type = $this->_type_fmt($data['type']);
					
					switch ($data['type']) {
						case TYPE_INFO: $t = ' INFO';
								break;
						case TYPE_ERROR: $t = 'ERROR';
								break;
						case TYPE_FATAL: $t = 'FATAL';
								break;
						case TYPE_WARN: $t = ' WARN';
								break;
					}

					fputs($fp, "($t) {$data['origin']} {$data['msg']} {$data['data']} ({$data['lib']})\n");
				}
				fclose($fp);

				return;
			}
			
			$entries = 0;
			echo "<table border=0 cellspacing=0 width=\"95%\" align=\"center\"><tr><td colspan=\"5\" class=\"log_head\">Class: {$this->_log_head}</td></tr>";
			echo "<tr><th class=\"log_head_th\">Type</th><th class=\"log_head_th\">Origin</th><th class=\"log_head_th\">";
			echo "Source</th><th class=\"log_head_th\">Message</th><th class=\"log_head_th\">Additional data</th></tr>";
			for ($i = 0; $i < sizeof($this->_log); $i++) {
				$tmp = $this->_log_print($this->_log[$i]);

				if (is_string($tmp)) {
					echo $tmp;
					$entries++;
				}
			}

			if ($entries == 0)
				echo '<tr align="center" style="background-color: lightgreen"><td colspan="5">No items in the log</td></tr>';
			echo '</table>';

			if ($showFooter)
				echo '<br /><center>Number of displayed log entries: '.$entries.'</center>';
		}
		
		function _logAppend($log, $origin) {
			for ($i = 0; $i < sizeof($this->_log); $i++) {
				$log_entry = $this->_log[$i];
				$log_entry['origin'] = $origin;
				$log[] = $log_entry;
			}
			
			return $log;
		}

		function hasError() {
			for ($i = 0; $i < sizeof($this->_log); $i++) {
				if ($this->log[$i]['type'] == TYPE_ERROR)
					return true;
			}

			return false;
                }

		function hasErrorFatal() {
			for ($i = 0; $i < sizeof($this->_log); $i++) {
				if ($this->log[$i]['type'] == TYPE_FATAL)
					return true;
			}

			return false;
		}
	}
?>
