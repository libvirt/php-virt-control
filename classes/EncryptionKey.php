<?php
	class Keys extends Database {
		private $_fn = false;
		private $tab = 'EncKeys';
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array( 'EncKeys' );
		
		function __construct($fn) {
			parent::__construct($fn);
			$this->_ensure_database_models();
			$this->_fn = $fn;
		}

		function addNewKey($idUser, $secKey, $secSalt, $valid = true) {
			$valid = ($valid ? 1 : 0);

			$fields = array(
					'idUser' => $idUser,
					'secKey' => $secKey,
					'secSalt' => $secSalt,
					'valid' => $valid,
					'created' => time(),
					'userAgent' => $_SERVER['HTTP_USER_AGENT']
					);

			return $this->insert($this->tab, $fields);
		}

		function getLatestForUser($idUser, $onlyId = true, $valid = 1) {
			$conditions = array(
						'idUser' => $idUser,
						'valid' => $valid
					);

			$fields = array(
					'id'
					);

			if (!$onlyId) {
				$fields[] = 'secKey';
				$fields[] = 'secSalt';
			}

			$mods = array(
					'order' => 'id DESC',
 					 'last' => true
					);

			$ret = $this->select($this->tab, $conditions, $fields, $mods);
			if (empty($ret))
				return false;
			return $ret[0];
		}

		function editKey($idKey, $key, $salt) {
			$fields = array(
					'secKey' => $key,
					'secSalt' => $salt,
					'modified' => time()
					);

			$condition = array(
					'id' => $idKey
					);

			return $this->update($this->tab, $fields, $condition);
		}

		function invalidateKey($idKey) {
			$fields = array(
					'valid' => 0
					);

			$condition = array(
					'id' => $idKey
					);

			return $this->update($this->tab, $fields, $condition);
		}

		function generateKey($idUser, $lenKey = 128, $lenSalt = false) {
			if (!$idUser)
				return false;
			if (!$lenSalt)
				$lenSalt = $lenKey;

			if ($lenKey < 64)
				$key = $this->generateRandomChars($lenKey);
			else
				$key = md5($idUser . rand(1, $idUser * $lenKey)).
					$this->generateRandomChars($lenKey - 32);

			$salt = $this->generateRandomChars($lenSalt);

			return array(
					'key' => $key,
					'salt' => $salt
				);
		}

		function generateNewKey($idUser, $idKey = false, $lenKey = 128, $lenSalt = 128) {
			/* $idKey == false means generate new, otherwise rewrite old one */
			$key_array = $this->generateKey($idUser, $lenKey, $lenSalt);

			if ($idKey == false)
				return $this->addNewKey($idUser, $key_array['key'], $key_array['salt']);
			return $this->editKey($idKey, $key_array['key'], $key_array['salt']);
		}

		/* RPC functions */
		function rpc_foo($data) {
			/* TODO: Placeholder */
		}
	}
?>
