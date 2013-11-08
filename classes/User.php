<?php
	class User extends Database {
		private $_fn = false;
		private $_lang = false;
		private $_idUser = false;
		private $tabUsers = 'Users';
		private $tabUsersLoginHistory = 'LoginHistory';
		private $tabAssocUserConnections = 'AssocUserConnections';
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array('Users', 'LoginHistory', 'AssocUserConnections' );
		
		function __construct($fn) {
			parent::__construct($fn);
			$this->_ensure_database_models();
			$this->_fn = $fn;
		}
		
		function login($username, $password, $userAgent = false) {
			$username = $this->safeString($username);
			$password = sha1($password);
			
			if (!$userAgent)
				$userAgent = $this->safeString($_SERVER['HTTP_USER_AGENT']);
			
			$res = $this->query('SELECT id, numLogins FROM '.$this->tabUsers.' WHERE username = "'.$username.'" AND password = "'.$password.'"');
			if (!$this->numRows($res))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Invalid user or password', 'No such user found or invalid password');
				
			$id = $this->fieldByName('id', $res);
			if (!$id)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Missing field in query', 'Missing field "id" in query');
			
			$this->_idUser = $id;
						
			$numLogins = $this->fieldByName('numLogins', $res);
			if ($numLogins == false)
				$numLogins = 0;
			
			$numLogins++;
			$tm = time();
			$fields = array(
					'lastLogin' => $tm,
					'numLogins' => $numLogins
					);
			$condition = array(
					'id' => $id
					);
			
			if (!$this->update($this->tabUsers, $fields, $condition))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Update error', 'Cannot update user metadata');
				
			if (!$this->_history_add($id, $tm, $userAgent))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Update error', 'Cannot update user metadata for login');
			return $id;
		}
		
		function isLoggedIn($sess) {
			$idUser = $sess->get('User');
			
			if (!$idUser)
				return false;
			$this->_idUser = $idUser;
			return true;
		}

		function logout($sess) {
			if (!$sess->get('User'))
				return false;
			$sess->del('User');
			$sess->del('Connections');
			$sess->del('Connection-Last-Attached');
			return true;
		}

		function _translate_permissions($perms) {
			global $user_permissions;
			$str = array();

			reset($user_permissions);
			while (list($key, $val) = each($user_permissions)) {
				eval('$val = '.$key.';');
				if ($perms & $val)
					$str[] = $key;
			}
			return $str;
		}

		function setAPIKey($idUser, $apikey, $ensureUnique = true) {
			$fields = array(
					'apikey' => $apikey
					);

			if ($ensureUnique) {
				$ret = $this->select($this->tabUsers, $fields, array('id'));
				if (sizeof($ret) > 0)
					return false;
			}

			$conditions = array('id' => $idUser);
			return $this->update($this->tabUsers, $fields, $conditions);
		}

		function getUsers() {
			$users = $this->select($this->tabUsers, array(), array('id', 'username', 'permissions', 'regUserAgent', 'regFrom', 'lastLogin', 'numLogins', 'apikey', 'lang'));

			for ($i = 0; $i < sizeof($users); $i++) {
				$users[$i]['permission_bits'] = $users[$i]['permissions'];
				$users[$i]['permissions'] = $this->_translate_permissions($users[$i]['permissions']);
			}

			return $users;
		}

		function getUser($id) {
			$users = $this->select($this->tabUsers, array('id' => $id), array('id', 'username', 'permissions', 'regUserAgent', 'regFrom', 'lastLogin', 'numLogins', 'apikey',
					'email', 'awaiting_recovery_token', 'lang'));

			for ($i = 0; $i < sizeof($users); $i++) {
				$users[$i]['permission_bits'] = $users[$i]['permissions'];
				$users[$i]['permissions'] = $this->_translate_permissions($users[$i]['permissions']);
			}

			return $users[0];
		}

		function getUserByName($username) {
			$users = $this->select($this->tabUsers, array('username' => $username), array('id') );

			return $users[0]['id'];
		}

		function getUserName($id) {
			$user = $this->getUser($id);

			return $user['username'];
		}

		function getLang($id=false) {
			if (!$id)
				$id = $this->_idUser;

			$user = $this->getUser($id);
			return $user['lang'];
		}

		function checkUserPermission($perm) {
			$ret = $this->getUser($this->_idUser);
			
			return ($ret['permission_bits'] & $perm) ? true : false;
		}

		function getMyId() {
			return $this->_idUser;
		}

		function getConnectionNames($idsOnly = false, $idUser = false) {
			if (!$idUser)
				$idUser = $this->_idUser;
			
			if (!$idUser)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No user', 'No user is currently logged-in');
			
			$condition = array(
					'idUser' => $idUser
					);

			$conns = $this->select($this->tabAssocUserConnections, $condition, 'idConnection');
			if ($idsOnly)
				return $conns;

			$r = array();
			$c = new Connection($this->_fn);
			for ($i = 0; $i < sizeof($conns); $i++) {
				$r[] = array(
						'id' => $conns[$i],
						'name' => $c->getName($conns[$i])
					);
			}
			$r = $this->sortArrayBy($r, 'name');
			$this->setLog($c->_logAppend($this->getLog(), __CLASS__.'::'.__FUNCTION__));
			unset($c);
			
			return $r;
		}
		
		function getConnections($idsOnly = false, $idUser = false) {
			if (!$idUser)
				$idUser = $this->_idUser;
				
			if (!$idUser)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'No user', 'No user is currently logged-in');
			
			$condition = array(
					'idUser' => $idUser
					);
			
			$conns = $this->select($this->tabAssocUserConnections, $condition, 'idConnection');
			if ($idsOnly)
				return $conns;
				
			$r = array();
			$c = new Connection($this->_fn);
			for ($i = 0; $i < sizeof($conns); $i++) {
				$r[] = $c->get($conns[$i])[0];
			}
			$r = $this->sortArrayBy($r, 'name');
			$this->setLog($c->_logAppend($this->getLog(), __CLASS__.'::'.__FUNCTION__));
			unset($c);
			
			return $r;
		}
		
		function _history_add($id, $timestamp, $userAgent) {
			$fields = array(
					'idUser' => $id,
					'timestamp' => $timestamp,
					'userAgent' => $userAgent
					);

			return $this->insert($this->tabUsersLoginHistory, $fields);
		}
		
		function register($username, $password = false, $email = false, $userAgent = false, $lang = false, $permissions = array()) {
			if (!$username)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Register error', 'Invalid username');
			
			$username = $this->safeString($username);
			
			if (!$password) {
				$password = $this->generateRandomChars(16);
				/* Send by e-mail */
			}
			
			$password = sha1($password);

			if (is_array($permissions)) {
				$val = 0;
				if (!empty($permissions)) {
					for ($i = 0; $i < sizeof($permissions); $i++)
						eval('$val += '.$permissions[$i].';');
				}
			}
			else
				$val = $permissions;

			if (!$userAgent)
				$userAgent = $_SERVER['HTTP_USER_AGENT'];

			if (!$lang) {
				$tmp = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$v = $tmp[0];
				unset($tmp);
				$tmp = explode(',', $v);
				$v = $tmp[0];
				unset($tmp);
				$tmp = explode('-', $v);
				$v = $tmp[0];
				unset($tmp);

				$lang = $v;
			}
				
			$userAgent = $this->safeString($userAgent);
			
			$fields = array(
					'username'     => $username,
					'password'     => $password,
					'regUserAgent' => $userAgent,
					'lastLogin'    => 0,
					'numLogins'    => 0,
					'permissions'  => $val,
					'regFrom'      => time(),
					'apikey'       => '-',
					'lang'         => $lang,
					'email'        => $email
					);

			return $this->insert($this->tabUsers, $fields, 'username');
		}

		function edit($username, $password = false, $lang = false, $permissions = array()) {
			$idUser = false;
			if (!$username) {
				$idUser = $this->_idUser;
				if (!$idUser)
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Edit error', 'Invalid username');
			}
			
			$username = $this->safeString($username);

			$val = -1;
			if (!is_bool($permissions)) {
				$val = 0;
				if (!empty($permissions)) {
					for ($i = 0; $i < sizeof($permissions); $i++)
						eval('$val += '.$permissions[$i].';');
				}
			}
			
			$fields = array();
			if ($password)
				$fields['password'] = sha1($password);
			if ($lang)
				$fields['lang'] = $lang;
			if ($val != -1)
				$fields['permissions'] = $val;

			if ($idUser)
				$conditions = array('id' => $idUser);
			else
				$conditions = array('username' => $username);

			return $this->update($this->tabUsers, $fields, $conditions);
		}

		function changePassword($username, $password) {
			$fields = array(
					'password' => sha1($password)
					);

			$conditions = array('username' => $username);

			return $this->update($this->tabUsers, $fields, $conditions);
		}

		function prepareForNewPassword($username) {
			$id = $this->getUserByName($username);
			if (!$id)
				return false;

			$tmp = $this->getUser($id);
			$email = $tmp['email'];

			$tmp = explode('/', $_SERVER['REQUEST_URI']);
			unset($tmp[sizeof($tmp) - 1]);
			$add = implode('/', $tmp);

			$token = $this->generateRandomChars(100);
			$fields = array(
					'awaiting_recovery_token' => $token.'-'.(time() + 86400)
					);

			$host = array_key_exists('REMOTE_HOST', $_SERVER) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
			if ($host == '::1')
				$host = 'localhost';

			$address = 'http://'.$host.'/'.$add.'?username='.$username.'&renew_hash='.$token;
			$msg = "Hi,\nsomebody (hopefully you) requested a password reset on one of your instance of php-virt-control project.\nIf you would".
				" like to continue, please click on the following link: \n\n$address\n\nSystem Administrator";

			if (!mail($email, 'php-virt-control password reset', $msg, 'Content-Type: text/plain; charset="utf-8"'))
				return false;

			$conditions = array('username' => $username);
			return $this->update($this->tabUsers, $fields, $conditions);
		}

		function confirmPasswordReset($username, $renew_hash) {
			$fields = array(
					'id',
					'email',
					'awaiting_recovery_token'
					);
			$conditions = array(
					'username' => $username
					);

			$ret = $this->select($this->tabUsers, $conditions, $fields);
			if (sizeof($ret) == 0)
				return false;

			$id = $ret[0]['id'];
			$email = $ret[0]['email'];
			$token = $ret[0]['awaiting_recovery_token'];

			$tmp = explode('-', $token);
			if (sizeof($tmp) < 2)
				return false;

			if (time() > (int)$tmp[1])
				return false;

			if ($tmp[0] != $renew_hash)
				return false;

			$pwd = $this->generateRandomChars(16);
			$msg = "Hi,\nyour new password for php-virt-control instance is \"$pwd\".\n\nFor security reasons please change your password after login.\n\n".
				"System Administrator";
			if (!mail($email, 'php-virt-control new password', $msg, 'Content-Type: text/plain; charset="utf-8"'))
				return false;

			$fields = array(
					'password' => sha1($pwd),
					'awaiting_recovery_token' => null
					);

			$conditions = array('username' => $username);
			return $this->update($this->tabUsers, $fields, $conditions);
		}

		function del($id) {
			$id = (int)$id;

			$conditions = array(
					'id' => $id
					);

			return $this->delete($this->tabUsers, $conditions);
		}

		function getUserIdByAPIKey($apikey) {
			$conditions = array(
					'apikey' => $apikey
			);

			$fields = array(
					'id',
					'lang'
					);

			$ret = $this->select($this->tabUsers, $conditions, $fields);
			if (empty($ret))
				return false;
			$this->_lang = $ret['lang'];
			return $ret[0]['id'];
		}

		function getLanguage() {
			return $this->_lang;
		}
		
		/* User RPC methods */
		function rpc_ChangePassword($input) {
			$data = $input['data'];
			$username = $data['username'];
			$newpwd = $data['password'];
			$apikey = $input['apikey'];

			if (!$username)
				return false;

			$ret = false;
			$this->_idUser = $this->getUserIdByAPIKey($apikey);
			if ($this->checkUserPermission(USER_PERMISSION_USER_EDIT))
				$ret = $this->changePassword($username, $newpwd);

			return array('result' => $ret);
		}

		function rpc_ResetPassword($input) {
			$data = $input['data'];
			$username = $data['username'];
			$apikey = $input['apikey'];

			if (!$username)
				return false;

			$ret = false;
			$this->_idUser = $this->getUserIdByAPIKey($apikey);
			if ($this->checkUserPermission(USER_PERMISSION_USER_EDIT)) {
				$ret = $this->prepareForNewPassword($username);
			}

			return array('result' => $ret);
		}
	}
?>
