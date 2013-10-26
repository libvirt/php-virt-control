<?php
	class Application extends Database {
		private $_fn = false;
		private $reqUA = 'virtDroid';
		
		function __construct($fn) {
			parent::__construct($fn);
			$this->_fn = $fn;
			$this->_ensure_database_models();
		}
		
		/* RPC functions */
		function rpc_CheckAvailable($data) {
			$ret = array(
					'result' => 'OK'
					);

			if (!substr('.'.$_SERVER['HTTP_USER_AGENT'], $this->reqUA)) {
				$ret['result'] = 'Error';
				$ret['reason'] = 'Invalid project';
			}

			return $ret;
		}
	}
?>
