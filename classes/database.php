<?php
	class Database extends LoggerBase {
		var $unimpl = 'Function is not implemented';
		var $log = array();
		private $fatal = false;

		function Database($type) {
			$this->connect();
		}

		function set_db_fatal($func) {
			$this->fatal = true;
			$this->err('connect', $func);

			return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Connect error', $this->unimpl);
		}

		function init() {
			return $this->err('init', $this->unimpl);
		}

		function verify_user($user, $password) {
			return $this->err('verify_user', $this->unimpl);
		}

		function user_add($user, $password, $perms) {
			return $this->err('user_add', $this->unimpl);
		}

		function user_renew_apikey($id) {
			return $this->err('user_renew_apikey', $this->unimpl);
		}

		function user_edit($id, $user, $password, $perms) {
			return $this->err('user_edit', $this->unimpl);
		}

		function user_del($id, $user) {
			return $this->err('user_del', $this->unimpl);
		}

		function get_users() {
			return $this->err('get_users', $this->unimpl);
		}

		function has_fatal_error() {
			return $this->fatal;
		}

		function get_fatal_error() {
			if (!$this->fatal)
				return false;

			return $this->log[0]['msg'];
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

		function refresh() {
		}

		function get_user_connection($idUser) {
			return array();
		}

		function generate_random_chars($len = 6) {
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

			$ret = '';
			for ($i = 0; $i < $len; $i++)
				$ret = $ret.$chars[rand() % strlen($chars)];

			return $ret;
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

		/* Used for XmlRPC */
		function get_by_apikey($apikey) {
			return false;
		}
	}
?>
