<?php
	class Database extends LoggerBase {
		private $_loaded = false;
		private $_res = false;
		private $_debug = false;
		private $_cached_rec = false;
		private $_tableArray = false;
		// Uncomment to enable logging
		//private $_logfile = 'tmp/query.log';
		private $_logfile = false;
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array();
		
		function Database($cfg) {
			if (strpos($cfg, '://')) {
				$tmp = explode('://', $cfg);
				$protocol = $this->safe_string($tmp[0]);
				$data = $this->safe_string($tmp[1]);
				
				$this->_loaded = $this->_load($protocol, $data);
			}
		}
		
		function _load($protocol, $data) {
			$debug = false;
			$host = false;
			$username = false;
			$password = false;
			$dbname = false;
			
			if ($protocol == 'file') {
				if (!file_exists('data/'.$data))
					return false;
				include('data/'.$data);
			}
			else
			if ($protocol == 'raw') {
				$tmp = explode(';', $data);
				for ($i = 0; $i < sizeof($tmp); $i++) {
					$tmp2 = explode('=', $tmp[$i]);
					$var = $tmp2[0];
					$val = $tmp2[1];
					
					if ($var == 'host')
						$host = $val;
					if ($var == 'username')
						$username = $val;
					if ($var == 'password')
						$password = $val;
					if ($var == 'dbname')
						$dbname = $val;
					if ($var == 'debug')
						$debug = ($val == 'true');
				}
			}
			else
			if ($protocol == 'serialize') {
				$dss = stripslashes($data);
				$dat = unserialize( $dss );
				
				$host = $dat['host'];
				$username = $dat['username'];
				$password = $dat['password'];
				$dbname = $dat['dbname'];
				$debug = $dat['debug'] ? true : false;
			}
			
			if ($host == false)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Configuration missing', 'Missing "host"');
			if ($username == false)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Configuration missing', 'Missing "username"');
			if ($password == false)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Configuration missing', 'Missing "password"');
			if ($dbname == false)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Configuration missing', 'Missing "dbname"');
			
			if (!$this->_connect($host, $username, $password, $dbname))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Invalid configuration', 'Cannot connect');
				
			$this->_debug = $debug;
		}
		
		function _connect($host, $username, $password, $dbname) {
			$this->_db = mysql_connect($host, $username, $password);
			if (!$this->_db)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Connection error', 'Connection error');
			if (!mysql_select_db($dbname))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Database selection error', 'Cannot select database');
			
			return true;
		}
		
		function _ensure_loaded() {
			if ($this->_loaded)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Config not loaded', 'Configuration settings are not loaded');
				
			return true;
		}
		
		function query($query) {
			if (!$this->_ensure_loaded())
				return false;
			if ($this->_debug)
				$this->log(TYPE_INFO, __CLASS__.'::'.__FUNCTION__, 'Query requested', $query);
			if (($this->_logfile) && ($query)) {
				$fp = fopen($this->_logfile, 'a');
				fputs($fp, Date('[Y-m-d H:i:s]').' '.$query."\n");
				fclose($fp);
			}
			
			$this->_res = mysql_query($query);
			return $this->_res;
		}
		
		function last_insert_id($res = false) {
			if (!($res = $this->_ensure_result($res)))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error getting result', 'Cannot get result');
				
			return mysql_insert_id();
		}
		
		function select($tabName, $conditions, $fields, $mods = false) {
			$fld = '*';
			if ($fields) {
				if (is_array($fields))
					$fld = implode(', ', $fields);
				else
					$fld = $fields;
			}

			if (!empty($conditions)) {
				$ak = array_keys($conditions);
				$conds = '';
				for ($i = 0; $i < sizeof($ak); $i++) {
					$key = $ak[$i];
					$val = $conditions[$key];
				
					$conds .= $key.' = "'.$val.'" AND ';
				}
				$conds = trim($conds);
				$conds[strlen($conds) - 3] = ' ';
				$conds[strlen($conds) - 2] = ' ';
				$conds[strlen($conds) - 1] = ' ';

				$cond = ' WHERE '.$conds;
			}
			else
				$cond = '';
			
			$qry = 'SELECT '.$fld.' FROM '.$tabName.$cond;

			if ($mods) {
				if (array_key_exists('order', $mods))
					$qry .= ' ORDER BY '.$mods['order'];

				if (array_key_exists('group', $mods))
					$qry .= ' GROUP BY '.$mods['group'];

				/* Use 'else' construction as 'last' and 'limit' cannot co-exist */
				if ((array_key_exists('last', $mods)) && ($mods['last'] == true))
					$qry .= ' LIMIT 1';
				else
				if (array_key_exists('limit', $mods))
					$qry .= ' LIMIT '.$mods['limit'];
			}

			$res = $this->query($qry);
			
			if ((!$res) && (mysql_error()))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error running query', mysql_error());

			$ret = array();
			while ($rec = $this->fetch_assoc()) {
				if (($fields) && (!is_array($fields))) {
					$ret[] = $rec[$fields];
				}
				else
					$ret[] = $rec;
			}

			if (sizeof($ret) == 0)
				return $this->log(TYPE_WARN, __CLASS__.'::'.__FUNCTION__, 'Empty result', 'Query returned empty row set');

			return $ret;
		}
		
		function update($tabName, $fields, $conditions) {
			if (empty($fields))
				return;
			$ak = array_keys($fields);
			$fld = '';
			for ($i = 0; $i < sizeof($ak); $i++) {
				$key = $ak[$i];
				$val = $fields[$key];
				
				$fld .= $key.' = "'.$val.'", ';
			}
			$fld = trim($fld);
			$fld[strlen($fld) - 1] = ' ';
			
			$ak = array_keys($conditions);
			$conds = '';
			for ($i = 0; $i < sizeof($ak); $i++) {
				$key = $ak[$i];
				$val = $conditions[$key];
				
				$conds .= $key.' = "'.$val.'" AND ';
			}
			$conds = trim($conds);
			$conds[strlen($conds) - 3] = ' ';
			$conds[strlen($conds) - 2] = ' ';
			$conds[strlen($conds) - 1] = ' ';
			
			$qry = 'UPDATE '.$tabName.' SET '.$fld.' WHERE '.$conds;
			$res = $this->query($qry);
			
			if ((!$res) && (mysql_error()))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error running query', mysql_error());
				
			return true;
		}

		function insert($tabName, $fields, $pk = false) {
			$ak = array_keys($fields);
			
			if ($pk) {
				if (is_array($pk)) {
					$conds = '';
					for ($i = 0; $i < sizeof($pk); $i++) {
						$key = $pk[$i];
						$val = array_key_exists($key, $fields) ? $fields[$key] : false;
				
						if ($val)
							$conds .= $key.' = "'.$val.'" AND ';
					}
					$conds = trim($conds);
					$conds[strlen($conds) - 3] = ' ';
					$conds[strlen($conds) - 2] = ' ';
					$conds[strlen($conds) - 1] = ' ';
				}
				else
					$conds = $pk.' = "'.$fields[$pk].'"';
					
				$qry = 'SELECT id FROM '.$tabName.' WHERE '.$conds;
				$res = $this->query($qry);
				if ($this->num_rows() != 0)
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Cannot insert entry', 'Entry with same primary key exists');
			}
			
			$flds = '';
			$vals = '';
			for ($i = 0; $i < sizeof($ak); $i++) {
				$flds .= $ak[$i].', ';
				$vals .= '"'.$fields[$ak[$i]].'", ';
			}
			$flds = trim($flds);
			$flds[strlen($flds)-1] = ' ';
			$vals = trim($vals);
			$vals[strlen($vals)-1] = ' ';
			
			$qry = 'INSERT INTO '.$tabName.'('.$flds.') VALUES('.$vals.')';
			$res = $this->query($qry);
			
			if ((!$res) && (mysql_error()))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error running query', mysql_error());
				
			return true;
		}

		function delete($tabName, $conditions) {
			$ak = array_keys($conditions);
			$conds = '';
			for ($i = 0; $i < sizeof($ak); $i++) {
				$key = $ak[$i];
				$val = $conditions[$key];
				
				$conds .= $key.' = "'.$val.'" AND ';
			}
			$conds = trim($conds);
			$conds[strlen($conds) - 3] = ' ';
			$conds[strlen($conds) - 2] = ' ';
			$conds[strlen($conds) - 1] = ' ';
			
			$qry = 'DELETE FROM '.$tabName.' WHERE '.$conds;
			$res = $this->query($qry);
			
			if ((!$res) && (mysql_error()))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error running query', mysql_error());
				
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

		function _table_exists($tabName) {
			if (!$this->_tableArray) {
				$res = $this->query('SHOW TABLES');
				while ($rec = $this->fetch_assoc()) {
					$ak = array_keys($rec);
					$fld = $ak[0];
					$this->_tableArray[] = $rec[$fld];
				}
			}

			for ($i = 0; $i < sizeof($this->_tableArray); $i++) {
				if ($this->_tableArray[$i] == $tabName)
					return true;
			}
			
			return false;
		}

		function _ensure_database_models() {
			for ($i = 0; $i < sizeof($this->_tables); $i++) {
				$name = $this->_tables[$i];
				$fn = 'models/'.$name.'.php';
				if ((!$this->_table_exists($name)) && (File_Exists($fn))) {
					include($fn);

					$qry = 'CREATE TABLE '.$name.'(';
					$ak = array_keys($table);
					$pks = array();
					for ($i = 0; $i < sizeof($ak); $i++) {
						$idx = $ak[$i];
						
						$ai = false;
						$type = false;
						$null = true;
						$opts = false;
						$length = false;
						$default = false;
						$comment = false;
						$has_default = false;
						if (array_key_exists('type', $table[$idx]))
							$type = $table[$idx]['type'];
						if (array_key_exists('null', $table[$idx]))
							$null = $table[$idx]['null'];
						if (array_key_exists('length', $table[$idx]))
							$length = $table[$idx]['length'];
						if (array_key_exists('options', $table[$idx])) {
							$opts = $table[$idx]['options'];
							
							for ($j = 0; $j < sizeof($opts); $j++) {
								$val = $opts[$j];
								
								if ($val == 'primary_key')
									$pks[] = $idx;
								if ($val == 'auto_increment')
									$ai = true;
							}
						}
						if (array_key_exists('default', $table[$idx])) {
							$default = $table[$idx]['default'];
							$has_default = true;
						}
						if (array_key_exists('comment', $table[$idx]))
							$comment = $table[$idx]['comment'];
							
						$qry .= $idx.' '.$type;
						if ($length)
							$qry .= '('.$length.')';
						
						if (!$null)
							$qry .= ' NOT NULL';
							
						/* Options */
						if ($ai)
							$qry .= ' AUTO_INCREMENT';
						if ($has_default)
							$qry .= ' DEFAULT "'.$default.'"';
						if ($comment)
							$qry .= ' COMMENT "'.$comment.'"';
						$qry .= ', ';
					}
					
					if (!empty($pks)) {
						for ($i = 0; $i < sizeof($pks); $i++)
							$qry .= ' PRIMARY KEY('.$pks[$i].'), ';
					}
					
					$qry[strlen($qry) - 2] = ' ';
					$qry = trim($qry);
					$qry .= ');';
				
					$res = $this->query($qry);
					
					if (!$res)
						return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Cannot add table', 'Cannot add table '.$name);
				}
			}
			
			return true;
		}

		function _rebuild_tables() {
			for ($i = 0; $i < sizeof($this->_tables); $i++) {
				$name = $this->_tables[$i];

				$this->query('DROP TABLE '.$name);
			}

			$this->_ensure_database_models();
			$this->_ensure_database_models();
		}

		function num_rows($res = false) {
			if (!($res = $this->_ensure_result($res)))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error getting num_rows', 'Cannot get number of rows');

			return mysql_num_rows($res);
		}
		
		function field_by_name($field_name, $res = false) {
			if (!($res = $this->_ensure_result($res)))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error getting result', 'Cannot get result');
			
			if ($this->_cached_rec)
				$rec = $this->_cached_rec;
			else {
				$rec = mysql_fetch_assoc($res);
				$this->_cached_rec = $rec;
			}
			if (!is_array($rec))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No result', 'No result in query');
			
			return array_key_exists($field_name, $rec) ? $rec[$field_name] : false;
		}
		
		function fetch_assoc($res = false) {
			if (!($res = $this->_ensure_result($res)))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Error getting result', 'Cannot get result');
				
			return mysql_fetch_assoc($res);
		}
		
		function _reset_cached() {
			$this->_cached_rec = false;
		}

		function _ensure_result($res = false) {
			if (!$res)
				$res = $this->_res;
				
			if (!$res)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No cached result', 'No result is cached');

			return $res;
		}
	}
?>
