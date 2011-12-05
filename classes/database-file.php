<?php
	/*
		Database file format is:

		File-Version: 0.0.1
		Tab: connections
		START_TAB
		name,hv,type,method,require_pwd,user,host,logfile
		name1,qemu,0,,0,,,,
		END_TAB
	*/
	class DatabaseFile extends Database {
		private $req_version = '0.0.1';
		private $connections = array();
		private $filename;
		private $fp;

		function DatabaseFile($data) {
			$this->filename = $data;
			$this->connect();
		}

		function connect() {
			$this->fp = fopen($this->filename, 'r');
			if (!$this->fp)
				return false;

			if (!$this->parse_data())
				return $this->err('connect', 'Error occured, possible bad file version');

			fclose($this->fp);

			return true;
		}

		function close() {
			return true;
		}

		function init() {
			return true;
		}

		function verify_user($user, $password) {
			return true;
		}

		function user_add($user, $password, $perms) {
			return false;
		}

		function user_edit($id, $user, $password, $perms) {
			return false;
		}

		function user_del($id, $user) {
			return false;
		}

		function get_users() {
			return array();
		}

		/* Parse function */
		function parse_data() {
			$id = 0;
			rewind($this->fp);

			$in_tab = false;
			$this->connections = array();
			while (!feof($this->fp)) {
				$s = Trim(fgets($this->fp, 1024));

				if (strpos('.'.$s, 'File-Version:')) {
					$tmp = explode(':', $s);
					if (Trim($tmp[1]) != $this->req_version)
						return false;
				}
				else
				if (strpos('.'.$s, 'Tab:')) {
					$tmp = explode(':', $s);
					$tab_name = Trim($tmp[1]);
					unset($tmp);
				}
				else
				if ($s == 'START_TAB') {
					$in_tab = true;
				}
				else
				if ($s == 'END_TAB') {
					$in_tab = false;
				}
				else
				if ($in_tab) {
					if ($tab_name == 'connections') {
						$tmp = explode(',', $s);
						$id++;
						$e = array(
								'id'		=> $id,
								'name'		=> Trim($tmp[0]),
								'hypervisor'	=> Trim($tmp[1]),
								'remote'	=> Trim($tmp[2]),
								'method'	=> Trim($tmp[3]),
								'require_pwd'	=> Trim($tmp[4]),
								'user'		=> Trim($tmp[5]),
								'host'		=> Trim($tmp[6]),
								'logfile'	=> Trim($tmp[7])
							);

						$this->connections[] = $e;
					}

					//echo 'Tab name: '.$tab_name.' => '.$s.'<br />';
				}
			}

			return true;
		}

		/* Listing functions */
		function list_connections($refresh=false) {
			if ($refresh)
				$this->connect();

			return $this->connections;
		}

		/* Add/edit/remove functions */
		function add_connection($name, $hv, $type, $method, $require_pwd, $user, $host, $logfile) {
			$fp = fopen($this->filename, 'w');
			if (!$fp)
				return false;

			if ($require_pwd)
				$require_pwd = 1;
			else
				$require_pwd = 0;

			fputs($fp, "File-Version: {$this->req_version}\n");
			fputs($fp, "Tab: connections\n");
			fputs($fp, "START_TAB\n");
			for ($i = 0; $i < sizeof($this->connections); $i++) {
				$name1 = $this->connections[$i]['name'];
				$hv1 = $this->connections[$i]['hypervisor'];
				$type1 = $this->connections[$i]['remote'];
				$method1 = $this->connections[$i]['method'];
				$require_pwd1 = $this->connections[$i]['require_pwd'];
				$user1 = $this->connections[$i]['user'];
				$host1 = $this->connections[$i]['host'];
				$logfile1 = $this->connections[$i]['logfile'];

				fputs($fp, "$name1,$hv1,$type1,$method1,$require_pwd1,$user1,$host1,$logfile1\n");
			}
			fputs($fp, "$name,$hv,$type,$method,$require_pwd,$user,$host,$logfile\n");
			fputs($fp, "END_TAB\n");
			
			fclose($fp);

			return true;
		}

		function edit_connection($id, $name, $hv, $type, $method, $require_pwd, $user, $host, $logfile) {
			$fp = fopen($this->filename, 'w');
			if (!$fp)
				return false;

			if ($require_pwd)
				$require_pwd = 1;
			else
				$require_pwd = 0;

			fputs($fp, "File-Version: {$this->req_version}\n");
			fputs($fp, "Tab: connections\n");
			fputs($fp, "START_TAB\n");
			for ($i = 0; $i < sizeof($this->connections); $i++) {
				$name1 = $this->connections[$i]['name'];
				$hv1 = $this->connections[$i]['hypervisor'];
				$type1 = $this->connections[$i]['remote'];
				$method1 = $this->connections[$i]['method'];
				$require_pwd1 = $this->connections[$i]['require_pwd'];
				$user1 = $this->connections[$i]['user'];
				$host1 = $this->connections[$i]['host'];
				$logfile1 = $this->connections[$i]['logfile'];

				if ($i + 1 != $id)
					fputs($fp, "$name1,$hv1,$type1,$method1,$require_pwd1,$user1,$host1,$logfile1\n");
				else
					fputs($fp, "$name,$hv,$type,$method,$require_pwd,$user,$host,$logfile\n");
			}
			fputs($fp, "END_TAB\n");
			
			fclose($fp);

			return true;
		}

		function remove_connection($id) {
			$fp = fopen($this->filename, 'w');
			if (!$fp)
				return false;

			fputs($fp, "File-Version: {$this->req_version}\n");
			fputs($fp, "Tab: connections\n");
			fputs($fp, "START_TAB\n");
			for ($i = 0; $i < sizeof($this->connections); $i++) {
				$name1 = $this->connections[$i]['name'];
				$hv1 = $this->connections[$i]['hypervisor'];
				$type1 = $this->connections[$i]['remote'];
				$method1 = $this->connections[$i]['method'];
				$require_pwd1 = $this->connections[$i]['require_pwd'];
				$user1 = $this->connections[$i]['user'];
				$host1 = $this->connections[$i]['host'];
				$logfile1 = $this->connections[$i]['logfile'];

				if ($i + 1 != $id)
					fputs($fp, "$name1,$hv1,$type1,$method1,$require_pwd1,$user1,$host1,$logfile1\n");
			}
			fputs($fp, "END_TAB\n");
			
			fclose($fp);

			return true;
		}
	}
?>
