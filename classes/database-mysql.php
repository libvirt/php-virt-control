<?php
	class DatabaseMySQL extends Database {
		private $server;
		private $user;
		private $password;
		private $dbname;
		private $prefix;
		private $default_user = 'admin';
		private $default_password = 'admin';
		private $tab_connections = 'connections';
		private $tab_users = 'users';
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
					'password varchar(255) NULL,'.
					'user varchar(255) NOT NULL,'.
					'host varchar(255) NOT NULL,'.
					'logfile varchar(255) NOT NULL,'.
					'PRIMARY KEY (id)'.
				') ENGINE=MyISAM  DEFAULT CHARSET=utf8';

			if (!mysql_query($qry))
				return false;

			$qry = 'CREATE TABLE IF NOT EXISTS '.$this->prefix.$this->tab_users.' ('.
					'id int(11) NOT NULL AUTO_INCREMENT,'.
					'username varchar(255) NOT NULL,'.
					'password varchar(255) NOT NULL,'.
					'permissions int(11) NOT NULL,'.
					'PRIMARY KEY (id)'.
				') ENGINE=MyISAM DEFAULT CHARSET=utf8';

			if (!mysql_query($qry))
				return false;

			/* Create a user with full permissions */
			global $user_permissions;
			$perms = 0;
			while (list($key, $val) = each($user_permissions))
				eval('$perms |= '.$key.';');

			$qry = 'INSERT INTO '.$this->prefix.$this->tab_users.'(username, password, permissions) '.
				'VALUES("'.$this->default_user.'", "'.hash('sha512', $this->default_password).'", '.$perms.')';
			return mysql_query($qry) ? true : false;
		}

		function verify_user($user, $password) {
			$user = mysql_real_escape_string($user);
			$password = hash('sha512', $password);
			$qry = 'SELECT permissions FROM '.$this->prefix.$this->tab_users.' WHERE username = "'.$user.'" '.
						'AND password = "'.$password.'"';

			$res = mysql_query($qry);
			if (!$res)
				return false;

			if (mysql_num_rows($res) == 0)
				return false;

			$rec = mysql_fetch_row($res);
			return $rec[0];
		}

		function user_add($user, $password, $perms) {
			$user = mysql_real_escape_string($user);
			$password = hash('sha512', $password);
			$perms = (int)$perms;

			$qry = 'SELECT id FROM '.$this->prefix.$this->tab_users.' WHERE username = "'.$user.'"';
			$res = mysql_query($qry);
			if (mysql_num_rows($res) > 0)
				return false;

			$qry = 'INSERT INTO '.$this->prefix.$this->tab_users.'(username, password, permissions) VALUES("'.$user.'", "'.
				$password.'", '.$perms.')';

			return (mysql_query($qry) ? true : false);
		}

		function user_edit($id, $user, $password, $perms) {
			$user = mysql_real_escape_string($user);
			$password = (strlen($password) > 0) ? hash('sha512', $password) : false;

			$qry = 'SELECT permissions FROM '.$this->prefix.$this->tab_users.' WHERE username = "'.$user.'"';
			$res = mysql_query($qry);
			if (mysql_num_rows($res) == 0)
				return false;

			if ($perms == false) {
				$rec = mysql_fetch_row($res);
				$perms = (int)$rec[0];
			}
			else
				$perms = (int)$perms;

			if ($password)
				$qry = 'UPDATE '.$this->prefix.$this->tab_users.' SET password = "'.$password.'", permissions = '.$perms.
					' WHERE username = "'.$user.'" AND id = '.$id;
			else
				$qry = 'UPDATE '.$this->prefix.$this->tab_users.' SET permissions = '.$perms.
					' WHERE username = "'.$user.'" AND id = '.$id;

			return (mysql_query($qry) ? true : false);
		}

		function user_del($id, $user) {
			$user = mysql_real_escape_string($user);

			$qry = 'SELECT id FROM '.$this->prefix.$this->tab_users.' WHERE username = "'.$user.'"';
			$res = mysql_query($qry);
			if (mysql_num_rows($res) == 0)
				return false;

			$qry = 'DELETE FROM '.$this->prefix.$this->tab_users.' WHERE username = "'.$user.'" AND id = '.$id;
			return (mysql_query($qry) ? true : false);
		}

		function get_users() {
			$res = mysql_query('SELECT id, username, permissions FROM '.$this->prefix.$this->tab_users);

			$ret = array();
			while ($rec = mysql_fetch_assoc($res)) {
				$ret[] = array(
						'id'   => $rec['id'],
						'name' => $rec['username'],
						'permissions' => $rec['permissions']
						);
			}

			return $ret;
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
		function add_connection($name, $hv, $type, $method, $pwd, $user, $host, $logfile) {
			if (strlen($pwd) > 0)
				$require_pwd = 1;
			else
				$require_pwd = 0;

			$qry = 'INSERT INTO '.$this->prefix.$this->tab_connections.'(name, hv, type, method, require_pwd, password, user, host, logfile) '.
				"VALUES('$name', '$hv', '$type', '$method', $require_pwd, '$pwd', '$user', '$host', '$logfile')";

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
