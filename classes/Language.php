<?php
	class Language extends Database {
		private $_fn = false;
		private $_lang = false;
		private $_str  = array();
		private $_langs = array();
		private $tab = 'LangStrings';
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array( 'LangStrings' );
		
		function __construct($fn, $lang = 'en') {
			parent::__construct($fn);
			$this->_fn = $fn;
			$this->_lang = $lang;
			$this->_ensure_database_models();

			if ($lang != false)
				$this->_langs = $this->getLanguages();
		}
		
		function getLanguages() {
			$dh = opendir('lang');

			if (!$dh)
				return false;

			$langs = array();
			while (($file = readdir($dh)) !== false) {
				if (strpos($file, '.php')) {
					include('lang/'.$file);

					$langs[] = array(
								'name' => $info['name'],
								'code' => $info['code'],
								'translator' => $info['translator']
							);
				}
			}

			closedir($dh);

			return $langs;
		}

		function getCode() {
			return $this->_lang;
		}

		function setCode($lang) {
			if ($lang != '')
				$this->_lang = $lang;
		}

		function loadAll() {
			if ((!empty($this->_str)) && (!empty($this->_str['strings'])))
				return;

			$fn = 'lang/'.$this->_lang.'.php';
			if (!File_Exists($fn)) {
				$this->_str = array('strings' => array(), 'texts' => array());
				return;
			}
			include($fn);
			
			$this->_str = array(
					'strings' => $strings,
					'texts' => $texts
					);
		}
		
		function get($ident, $lang = false) {
			$this->loadAll();

			$strings = $this->_str['strings'];
			if (isset($strings) && (array_key_exists($ident, $strings)))
				return $strings[$ident];

			if (!$lang)
				$lang = $this->_lang;

			if ($lang == 'lang')
				return $this->_langs[$ident];

			$fields = array(
					'value'
					);
			
			$conditions = array(
						'lang' => $lang,
						'ident' => $ident
					);
			
			$ret = $this->select($this->tab, $conditions, $fields);
			if (!$ret)
				return $ident;
			return $ret[0]['value'];
		}
		
		function getText($ident) {
			$this->loadAll();
			
			$texts = $this->_str['texts'];
			if (isset($texts) && (array_key_exists($ident, $texts)))
				return $texts[$ident];
				
			return $ident;
		}

		function getAllKeys() {
			if (!array_key_exists('strings', $this->_str))
				return;

			$tmp = $this->_str;
			return $tmp;
		}

		function generateLanguageFile($code, $name, $translator, $translator_mail, $arr) {
			Header('Content-Type: text/plain');

			echo '<'.'?php'."\n";
			echo "  /*\n";
			echo "   * PLEASE SAVE THIS FILE TO lang DIRECTORY OF YOUR PHP-VIRT-CONTROL INSTALLATION\n";
			echo "   * UNDER NAME \"$code.php\". THIS WILL ENABLE SELECTION YOUR LANGUAGE IN THE SETTINGS PAGE\n";
			echo "   * Translated by: $translator <$translator_mail>\n";
			echo "   */\n\n";
			echo "  \$info = array(\n";
			echo "          'code' => '$code',\n";
			echo "          'name' => '$name',\n";
			echo "          'translator' => array(\n";
			echo "                                'name' => '$translator',\n";
			echo "                                'email' => '$translator_mail'\n";
			echo "                               )\n";
			echo "          );\n\n";
			echo "  \$strings = array(\n";

			$i = 0;
			while (list($key, $val) = each($arr['strings'])) {
				$val = addslashes($val);
				echo "                '$key' => '$val'";
				if ($i++ < (sizeof($arr['strings'])) - 1)
					echo ',';
				echo "\n";
			}

			echo "             );\n\n";
			echo "  \$texts = array(\n";

			$i = 0;
			while (list($key, $val) = each($arr['texts'])) {
				$val = addslashes($val);
				echo "                '$key' => '$val'";
				if ($i++ < (sizeof($arr['texts'])) - 1)
					echo ',';
				echo "\n";
			}
			echo "           );\n";
			echo '?'.'>'."\n";
		}

		function generateStaticStrings($fn = false) {
			$lang = $this->_lang;
			$fields = array(
					'ident',
					'value'
					);
					
			$conditions = array(
					'lang' => $lang
					);
					
			$mods = array(
					'order' => 'ident'
					);
					
			$ret = $this->select($this->tab, $conditions, $fields, $mods);
			
			$rv = "\t\t\$strings = array(\n";
			
			for ($i = 0; $i < sizeof($ret); $i++) {
				$rv .= "\t\t\t'".$ret[$i]['ident']."' => '".$ret[$i]['value']."'";
				if ($i < sizeof($ret) - 1)
					$rv .= ',';
				$rv .= "\n";
			}
			$rv .= "\t\t);";
			
			if (!$fn)
				return $rv;
				
			$fn = str_replace('%l', $lang, $fn);
			$fp = fopen($fn, 'w');
			fputs($fp, "<?php\n$rv\n?>");
			fclose($fp);
			
			return $fn;
		}

		function _getUniqueLanguages()
		{
			$mods = array(
					'group' => 'lang'
					);

			$fields = array( 'lang' );

			$res = $this->select($this->tab, array(), $fields, $mods);

			$ret = array();
			for ($i = 0; $i < sizeof($res); $i++) {
				if ($res[$i]['lang'] != 'lang')
					$ret[] = array(
						'code' => $res[$i]['lang'],
						'name' => $this->get($res[$i]['lang'], 'lang')
						);
			}

			return $ret;
		}
	}
?>
