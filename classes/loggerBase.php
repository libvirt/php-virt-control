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
	}
?>
