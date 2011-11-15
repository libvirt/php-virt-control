<?php
	/*
		Database table structures:

		CREATE TABLE IF NOT EXISTS `connections` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `hv` varchar(5) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `method` varchar(3) NOT NULL,
		  `require_pwd` tinyint(4) NOT NULL,
		  `user` varchar(255) NOT NULL,
		  `host` varchar(255) NOT NULL,
		  `logfile` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Table of connection for php-virt-control';

	*/
	class DatabaseMySQL extends Database {
		private $server;
		private $user;
		private $password;
		private $dbname;
		private $prefix;
		private $tab_connections = 'connections';
		private $connections = array();
		private $db;

		function DatabaseMySQL($data) {
			if (!File_Exists($data))
				return $this->set_db_fatal('no-datafile');

			$server   = false;
			$user     = false;
			$password = false;
			$dbname   = false;
			$prefix   = false;

			include($data);

			if (!$server)
				return $this->set_db_fatal('no-server');
			if (!$user)
				return $this->set_db_fatal('no-user');
			if (!$password)
				return $this->set_db_fatal('no-password');
			if (!$dbname)
				return $this->set_db_fatal('no-dbname');
			if (!$prefix)
				$prefix = '';

			$this->server = $server;
			$this->user = $user;
			$this->password = $password;
			$this->dbname = $dbname;
			$this->prefix = $prefix;

			$this->connect();
		}

		function connect() {
			$this->db = mysql_connect($this->server, $this->user, $this->password);
			if (!$this->db)
				return $this->set_db_fatal('db-failure-connect');

			if (!mysql_select_db($this->dbname, $this->db))
				return $this->set_db_fatal('db-failure-select');
			

			return true;
		}

		function close() {
			mysql_close($this->db);
			return true;
		}

		function init() {
			$res = mysql_query('SELECT * FROM '.$this->prefix.$this->tab_connections);
			if ($res)
				return true;

			$qry = 'CREATE TABLE IF NOT EXISTS '.$this->prefix.$this->tab_connections.' ('.
					'id int(11) NOT NULL AUTO_INCREMENT,'.
					'name varchar(255) NOT NULL,'.
					'hv varchar(5) NOT NULL,'.
					'type tinyint(4) NOT NULL,'.
					'method varchar(3) NOT NULL,'.
					'require_pwd tinyint(4) NOT NULL,'.
					'user varchar(255) NOT NULL,'.
					'host varchar(255) NOT NULL,'.
					'logfile varchar(255) NOT NULL,'.
					'PRIMARY KEY (id)'.
				') ENGINE=MyISAM  DEFAULT CHARSET=utf8';

			return is_resource( mysql_query($qry) );
		}

		function refresh() {
			$res = mysql_query('SELECT * FROM '.$this->prefix.$this->tab_connections);
			if (!$res)
				return false;

			$this->connections = array();
			while ($rec = mysql_fetch_assoc($res)) {
				$rec['hypervisor'] = $rec['hv'];
				$rec['remote'] = ($rec['type'] == 1) ? 1 : 0;
			
				$this->connections[] = $rec;
			}

			return true;
		}

		/* Listing functions */
		function list_connections($refresh=false) {
			if ($refresh)
				$this->refresh();

			return $this->connections;
		}

		/* Add/edit/remove functions */
		function add_connection($name, $hv, $type, $method, $require_pwd, $user, $host, $logfile) {
			if ($require_pwd)
				$require_pwd = 1;
			else
				$require_pwd = 0;

			$qry = 'INSERT INTO '.$this->prefix.$this->tab_connections.'(name, hv, type, method, require_pwd, user, host, logfile) '.
				"VALUES('$name', '$hv', '$type', '$method', $require_pwd, '$user', '$host', '$logfile')";

			if (!mysql_query($qry))
				return false;

			return mysql_insert_id();
		}

		function edit_connection($id, $name, $hv, $type, $method, $require_pwd, $user, $host, $logfile) {
			if ($require_pwd)
				$require_pwd = 1;
			else
				$require_pwd = 0;

			$qry = 'UPDATE '.$this->prefix.$this->tab_connections." SET name = '$name', hv = '$hv', type = '$type', method = '$method', ".
				"require_pwd = $require_pwd, user = '$user', host = '$host', logfile = '$logfile' WHERE id = $id";

			return mysql_query($qry) ? true : false;
		}

		function remove_connection($id) {
			$qry = 'DELETE FROM '.$this->prefix.$this->tab_connections.' WHERE id = '.$id;

			return mysql_query($qry) ? true : false;
		}
	}
?>
