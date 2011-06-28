<?php
	class Database {
		var $unimpl = 'Function is not implemented';
		var $log = array();

		function Database($type) {
			$this->connect();
		}

		function err($func, $msg) {
			$this->log[] = array('func' => $func, 'msg' => $msg);

			return false;
		}

		function get_log() {
			return $this->log;
		}

		function connect() {
			return $this->err('connect', $this->unimpl);
		}

		function close() {
			return $this->err('close', $this->unimpl);
		}

		/* Listing functions */
		function list_connections() {
			return $this->err('list_connections', $this->unimpl);
		}

		/* Add/remove/edit functions */
		function add_connection($name, $hv, $type, $method, $user, $host, $logfile) {
			return $this->err('add_connection', $this->unimpl);
		}

		function edit_connection($id, $name, $hv, $type, $method, $user, $host, $logfile) {
			return $this->err('edit_connection', $this->unimpl);
		}

		function remove_connection($id) {
			return $this->err('remove_connection', $this->unimpl);
		}
	}
?>
