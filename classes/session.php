<?php
	class Session {
		function __construct($name = false) {
			if (!$this->isStarted()) {
				if ($name)
					session_name($name);
				session_start();
			}
		}

		function isStarted() {
			return ($this->getId() == '') ? false : true;
		}
		
		function getId() {
			return session_id();
		}

		function set($class, $val) {
			$_SESSION[$class] = $val;
		}

		function get($class) {
			if (!array_key_exists($class, $_SESSION))
				return false;

			return $_SESSION[$class];
		}
		
		function del($class) {
			if (!array_key_exists($class, $_SESSION))
				return false;
			
			unset($_SESSION[$class]);
			return true;
		}
	}
?>
