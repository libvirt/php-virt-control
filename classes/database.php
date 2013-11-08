<?php
	class Database extends LoggerBase {
		private $_db = false;
		private $_type = 'mysql';
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array();
		
		function Database($cfg, $type = false) {
			if (!$type)
				$type = $this->_type;

			if (is_dir('../classes/layers'))
				$dir = '../classes/layers';
			else
			if (is_dir('./classes/layers'))
				$dir = './classes/layers';

			$cn = $this->_getClassFromFile($dir.'/'.$type.'.php');
			if (!$cn) {
				$dh = opendir($dir);
				if ($dh) {
					while (($file = readdir($dh)) !== false) {
						if ($file[0] != '.') {
							$className = $this->_getClassFromFile($dir.'/'.$file);

							if (strtolower($className) == strtolower('database'.$type)) {
								$cn = $className;
								break;
							}
						}
					}
				}
				closedir($dh);
			}

			if (!class_exists($cn))
				die('ERROR: Class <i>'.$cn.'</i> doesn\'t exist');
			$this->_db = new $cn($cfg);
		}

		/* Public functions */
		function isConnected() {
			if (!$this->_ensureConnected(true))
				return false;
			$ret = $this->_db->isConnnected();
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function setup($server, $user, $password, $dbname = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->setup($server, $user, $password, $dbname);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function createNewUser($username, $password, $host = false, $canOverride = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->createNewUser($username, $password, $host, $canOverride);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function createDatabase($dbname, $username = false, $host = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->createDatabase($dbname, $username, $host);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function query($query) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->query($query);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}
		
		function lastInsertID($res = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->lastInsertID($res);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}
		
		function select($tabName, $conditions, $fields, $mods = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->select($tabName, $conditions, $fields, $mods);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}
		
		function insert($tabName, $fields, $pk = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->insert($tabName, $fields, $pk);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function update($tabName, $fields, $conditions, $autoEscape = true) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->update($tabName, $fields, $conditions, $autoEscape);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function delete($tabName, $conditions) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->delete($tabName, $conditions);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function numRows($res = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->numRows($res);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function fieldByName($field_name, $res = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->fieldByName($field_name, $res);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function fetchAssoc($res = false) {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->fetchAssoc($res);
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function _ensure_database_models() {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->_ensure_database_models();
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		function close() {
			if (!$this->_ensureConnected())
				return false;
			$ret = $this->_db->close();
			$this->setLog( $this->_db->getLog() );

			return $ret;
		}

		/* Private functions */
		function _ensureConnected($attachOnly = false) {
			if (!$this->_db)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No database layer attached', 'Database layer is not attached');

			if ($attachOnly)
				return true;

			if (!$this->_db->isConnected())
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No database connection', 'Database layer is not connected');

			return true;
		}

		function _getClassFromFile($filename) {
			$ret = false;

			if (!File_Exists($filename))
				return false;

			$fp = fopen($filename, 'r');
			while (!feof($fp)) {
				$s = fgets($fp, 1024);
				if (strpos($s, 'class ')) {
					$tmp = explode(' ', $s);
					$ret = $tmp[1];
					unset($tmp);
				}
			}
			fclose($fp);

			return $ret;
		}
	}
?>
