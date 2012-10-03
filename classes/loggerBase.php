<?php
	/**
	* Basic logger class for common usage purposes
	*/

        define('TYPE_INFO', 1);
        define('TYPE_WARN', 2);
        define('TYPE_ERROR', 3);
        define('TYPE_FATAL', 4);

	class LoggerBase {
		private $log = array();
		private $debug = true;

		function log($type, $lib, $msg, $data='') {
			if (!$this->debug) return;
			$this->log[ sizeof($this->log) ] = array(
				'type' => $type,
				'lib'  => $lib,
				'msg'  => $msg,
				'data' => $data
			);
		}

		function log_overwrite($log_obj) {
			if (!$this->debug) return;

			$this->log = $log_obj;
		}

		function get_log() {
			return $this->log;
		}

		function type_fmt($type) {
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

		function log_print($data) {
			$type = $this->type_fmt($data['type']);

			if ($type == false)
				return false;

			switch ($data['type']) {
				case TYPE_INFO: $col = 'lightgreen';
						break;
				case TYPE_ERROR: $col = '#b22222';
						break;
				case TYPE_FATAL: $col = '#b22522';
						break;
				case TYPE_WARN: $col = 'gray';
						break;
			}

			return "<tr align=\"center\" style=\"background-color:$col; font-weight: bold;\"><td>$type</td><td>{$data['lib']}</td>"
				."<td>{$data['msg']}</td><td>{$data['data']}</td></tr>";
		}

		function log_dump($showFooter = false) {
			if (!$this->debug) return;

			$entries = 0;

			echo "<table border=0 cellspacing=0 width=\"95%\" align=\"center\"><tr><td>&nbsp;</td></tr>";
			echo "<tr><th>Type</th><th>Source</th><th>Message</th><th>Additional data</th></tr>";
			echo "<tr><td>&nbsp;</td></tr>";
			for ($i = 0; $i < sizeof($this->log); $i++) {
				$tmp = $this->log_print($this->log[$i]);

				if (is_string($tmp)) {
					echo $tmp;
					$entries++;
				}
			}

			if ($entries == 0)
				echo '<tr align="center" style="background-color: lightgreen"><td colspan="4">No items in the log</td></tr>';
			echo '</table>';

			if ($showFooter)
				echo '<br /><center>Number of displayed log entries: '.$entries.'</center>';
		}

		function has_error() {
			for ($i = 0; $i < sizeof($this->log); $i++) {
				if ($this->log[$i]['type'] == TYPE_ERROR)
					return true;
			}

			return false;
                }

		function has_error_fatal() {
			for ($i = 0; $i < sizeof($this->log); $i++) {
				if ($this->log[$i]['type'] == TYPE_FATAL)
					return true;
			}

			return false;
		}
	}
?>
