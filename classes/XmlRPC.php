<?php
	class XmlRPC extends LoggerBase {
		private $log_override = false;
		private $is_xmlrpc = false;
		private $data = false;
		private $config = false;
		private $input = false;
		private $inputArr = false;

		function XmlRPC($config, $input = false) {
			$this->_config = $config;

			if (!$input) {
				$input = file_get_contents('php://input');
				$ret = $this->process_xml_rpc_from_xml($input);
			}
			else {
				if ($isFile)
					$ret = $this->process_xml_rpc_from_file($input);
				else
					$ret = $this->process_xml_rpc_from_xml($input);
			}

			$this->input = $input;
			$this->data = $this->format_rpc_reply($ret, true);
		}

		function getInput($arr = false) {
			return ($arr) ? $this->inputArr : $this->input;
		}

		function getData() {
			return $this->data;
		}

		function parse_xmlrpc_struct($x, $first = true) {
			$r = (array)$x['member'];

			$ret = array();
			if ($first)
				$ret['data'] = array();
			if (array_key_exists('name', $r)) {
				$key = $r['name'];

				$r = (array)$r['value'];
				if (array_key_exists('string', $r))
					$value = (string)$r['string'];
				elseif (array_key_exists('int', $r))
					$value = (int)$r['int'];
				elseif (array_key_exists('i4', $r))
					$value = (int)$r['int'];
				elseif (array_key_exists('boolean', $r))
					$value = (($r['boolean'] == '1') || ($r['boolean'] == 'true')) ? 1 : 0;
				elseif (array_key_exists('double', $r))
					$value = (double)$r['double'];
				elseif (array_key_exists('base64', $r))
					$value = (string)$r['base64'];
				elseif (array_key_exists('dateTime.iso8601', $r))
					$value = $this->format_datetime((string)$r['dateTime.iso8601']);
				elseif (array_key_exists('struct', $r))
					$value = $this->parse_xmlrpc_struct((array)$r['struct'], false);

				if ($first) {
					if ($key == 'apikey')
						$ret['apikey'] = $value;
					else
						$ret['data'][$key] = $value;
				}
				else
					$ret[$key] = $value;
			}
			else {
				for ($i = 0; $i < sizeof($r); $i++) {
					$tmp = (array)$r[$i];
					$key = $tmp['name'];

					$tmp = (array)$tmp['value'];

					if (array_key_exists('string', $tmp))
						$value = (string)$tmp['string'];
					elseif (array_key_exists('int', $tmp))
						$value = (int)$tmp['int'];
					elseif (array_key_exists('i4', $tmp))
						$value = (int)$tmp['int'];
					elseif (array_key_exists('boolean', $tmp))
						$value = (($tmp['boolean'] == '1') || ($tmp['boolean'] == 'true')) ? 1 : 0;
					elseif (array_key_exists('double', $tmp))
						$value = (double)$tmp['double'];
					elseif (array_key_exists('dateTime.iso8601', $tmp))
						$value = $this->format_datetime((string)$tmp['dateTime.iso8601']);
					elseif (array_key_exists('base64', $tmp))
						$value = (string)$tmp['base64'];
					elseif (array_key_exists('struct', $tmp))
						$value = $this->parse_xmlrpc_struct((array)$tmp['struct'], false);

					if ($first) {
						if ($key == 'apikey')
							$ret['apikey'] = $value;
						else
							$ret['data'][$key] = $value;
					}
					else
						$ret[$key] = $value;
				}
			}

			return $ret;
		}

		function process_xml_rpc_from_file($filename) {
			if (!function_exists('simplexml_load_file'))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'XML Error', 'Function simplexml_load_file() not present');

			$this->log(TYPE_INFO, __CLASS__.'::'.__FUNCTION__, 'XML Processing', 'Processing file '.$filename);

			$xml = simplexml_load_file($filename);
			return $this->process_xml_rpc($xml);
		}

		function process_xml_rpc_from_xml($xml) {
			if (!function_exists('simplexml_load_string'))
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'XML Error', 'Function simplexml_load_string() not present');

			$this->log(TYPE_INFO, __CLASS__.'::'.__FUNCTION__, 'XML Processing', 'Processing XML input');

			$tmp = simplexml_load_string($xml);
			return $this->process_xml_rpc($tmp);
		}

		function process_xml_rpc($xml) {
			if (!$xml)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'XML Processing', 'SimpleXMLObject not present');

			if ($xml->getName() != 'request') {
				if ($xml->getName() != 'methodCall')
					return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'XML Error', 'Invalid root tag');

				$r = array( $xml );
				$x = (array)$r[0];
				$method = (string)$x['methodName'];
				$r = (array)$x['params'];
				if (array_key_exists('param', $r)) {
					$x = (array)$r['param'];
					$r = (array)$x['value'];
					$x = (array)$r['struct'];

					$ret = $this->parse_xmlrpc_struct($x);
					$ret['method'] = $method;
				}
				else
					$ret = array('method' => $method);

				$this->is_xmlrpc = true;
			}
			else {
				$ret = array();
				foreach($xml->children() as $child) {
					$name = (string)$child->getName();
					$arr = (array)$child;
					if (!empty($arr)) {
						$ix = 0;
						foreach($child as $child2) {
							$child2 = (array)$child2;

							$ret[$name] = $child2;
							if (sizeof($child2) > 0)
								$ix++;
						}

						if ($ix == 0)
							$ret[$name] = $arr;
					}
					else {
						$value = (string)$child;

						$ret[$name] = $value;
					}
				}

				$this->is_xmlrpc = false;
			}

			$this->inputArr = $ret;

			$tmp = explode('.', $ret['method']);
			if (sizeof($tmp) != 2)
				return $this->log(TYPE_ERROR, __CLASS__.'::'.__FUNCTION__, 'Invalid method', 'Invalid method has been requested');

			$class = new $tmp[0]($this->_config);
			$method = 'rpc_'.$tmp[1];
			if (!method_exists($class, $method))
				return array('result' => 'ERROR', 'reason' => 'Invalid method');
			
			return $class->$method($ret);
		}

		function format_xmlrpc_reply($data, $encapsulate, $level) {
			$ret = '';
			if ((empty($data)) || ($data == false)) {
				$errmsg = false;
				$log = ($this->log_override) ? $this->log_override : $this->get_log();
				for ($i = sizeof($log) - 1; $i >= 0; $i--) {
					if ($log[$i]['type'] == TYPE_ERROR) {
						$errmsg = $log[$i]['msg'];
						break;
					}
				}

				if (!$errmsg)
					$errmsg = 'unknown';

				$ret = '<'."?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
				$ret .= "<methodResponse>\n";
				$ret .= "\t<fault>\n";
				$ret .= "\t\t<value>\n";
				$ret .= "\t\t\t<struct>\n";
				$ret .= "\t\t\t\t<member>\n";
				$ret .= "\t\t\t\t\t<name>faultCode</name>\n";
				$ret .= "\t\t\t\t\t<value><int>1</int></value>\n";
				$ret .= "\t\t\t\t</member>\n";
				$ret .= "\t\t\t\t<member>\n";
				$ret .= "\t\t\t\t\t<name>faultString</name>\n";
				$ret .= "\t\t\t\t\t<value><string>$errmsg</string></value>\n";
				$ret .= "\t\t\t\t</member>\n";
				$ret .= "\t\t\t</struct>\n";
				$ret .= "\t\t</value>\n";
				$ret .= "\t</fault>\n";
				$ret .= "</methodResponse>\n";

				return $ret;
			}

			if ($encapsulate) {
				for ($i = 0; $i < $level; $i++)
					$ret .= "\t";
				$ret .= '<'."?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
				$ret .= "<methodResponse>\n";
				$ret .= "\t<params>\n";
				$ret .= "\t\t<param>\n";
				$ret .= "\t\t\t<value>\n";
				$ret .= "\t\t\t\t<struct>\n";

				$level += 5;
				$this->log(TYPE_INFO, __CLASS__.'::'.__FUNCTION__, 'XML Formatting', 'Formatting XML reply data');
			}

			if (!is_array($data))
				$ret .= '<member><name>msg</name><value><string>'.htmlentities($data).'</string></value></member>';
			else {
				foreach ($data as $key => $item) {
					if (!strpos($key, '-datatype')) {
						for ($i = 0; $i < $level + 3; $i++)
							$ret .= "\t";

						$ret .= "<member>\n";

						for ($i = 0; $i < $level + 4; $i++)
							$ret .= "\t";

						$ret .= "<name>$key</name>\n";

						for ($i = 0; $i < $level + 4; $i++)
							$ret .= "\t";

						$ret .= "<value>\n";

						if (is_array($item)) {
							for ($i = 0; $i < $level + 5; $i++)
								$ret .= "\t";

							$ret .= "<struct>\n";

							$ret .= $this->format_xmlrpc_reply($item, false, $level + 3);

							for ($i = 0; $i < $level + 5; $i++)
								$ret .= "\t";

							$ret .= "</struct>\n";
						}
						else {
							for ($i = 0; $i < $level + 5; $i++)
								$ret .= "\t";

							if (array_key_exists($key.'-datatype', $data))
								$type = $data[$key.'-datatype'];
							else {
								if (is_string($item))
									$type = 'string';
								elseif (is_numeric($item)) {
									if (strpos($item, '.'))
										$type = 'double';
									else
										$type = 'int';
								}
								elseif (is_bool($item)) {
									$type = 'boolean';

									$item = $item ? 1 : 0;
								}
							}

							$ret .= "<$type>$item</$type>\n";
						}

						for ($i = 0; $i < $level + 4; $i++)
							$ret .= "\t";

						$ret .= "</value>\n";

						for ($i = 0; $i < $level + 3; $i++)
							$ret .= "\t";

						$ret .= "</member>\n";
					}
				}
			}

			if ($encapsulate) {
				$ret .= "</struct>\n";

				for ($i = 0; $i < $level - 2; $i++)
					$ret .= "\t";

				$ret .= "</value>\n";

				for ($i = 0; $i < $level - 3; $i++)
					$ret .= "\t";

				$ret .= "</param>\n";

				for ($i = 0; $i < $level - 4; $i++)
					$ret .= "\t";

				$ret .= "</params>\n";

				$ret .= "</methodResponse>";
			}

			return $ret;
		}

		function format_rpc_reply($data, $encapsulate=false, $level = 0) {
			if ($this->is_xmlrpc)
				return $this->format_xmlrpc_reply($data, $encapsulate, $level);

			$ret = '';
			if (empty($data)) {
				$errmsg = false;
				$log = ($this->log_override) ? $this->log_override : $this->get_log();
				for ($i = sizeof($log) - 1; $i > 0; $i--) {
					if ($log[$i]['type'] == TYPE_ERROR) {
						$errmsg = $log[$i]['msg'];
						break;
					}
				}

				if (!$errmsg)
					$errmsg = 'unknown';

				$ret = '<'."?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
				$ret .= "<reply>\n";
				$ret .= "\t<method>{$this->_scope}.{$this->_method}</method>\n";
				$ret .= "\t<apikey>{$this->_apikey}</apikey>\n";
				$ret .= "\t<data>\n";
				$ret .= "\t\t<error>$errmsg</error>\n";
				$ret .= "\t</data>\n";
				$ret .= "</reply>\n";

				return $ret;
			}

			if ($encapsulate) {
				for ($i = 0; $i < $level; $i++)
					$ret .= "\t";
				$ret .= '<'."?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
				$ret .= "<reply>\n";
				$ret .= "\t<method>{$this->_scope}.{$this->_method}</method>\n";
				$ret .= "\t<apikey>{$this->_apikey}</apikey>\n";
				$ret .= "\t<data>\n";

				$level += 2;
				$this->log(TYPE_INFO, __CLASS__.'::'.__FUNCTION__, 'XML Formatting', 'Formatting XML reply data');
			}

			foreach ($data as $key => $item) {
				if (!strpos($key, '-datatype')) {
					for ($i = 0; $i < $level; $i++)
						$ret .= "\t";

					$ret .= "<item>\n";

					for ($i = 0; $i < $level + 1; $i++)
						$ret .= "\t";

					$ret .= "<name>$key</name>\n";

					if (is_array($item)) {
						for ($i = 0; $i < $level + 1; $i++)
							$ret .= "\t";

						$ret .= "<array>\n";

						$ret .= $this->format_rpc_reply($item, false, $level + 2);

						for ($i = 0; $i < $level + 1; $i++)
							$ret .= "\t";

						$ret .= "</array>\n";
					}
					else {
						for ($i = 0; $i < $level + 1; $i++)
							$ret .= "\t";

						$ret .= "<value>$item</value>\n";
					}

					for ($i = 0; $i < $level; $i++)
						$ret .= "\t";

					$ret .= "</item>\n";
				}
			}

			if ($encapsulate) {
				for ($i = 0; $i < $level - 1; $i++)
					$ret .= "\t";

				$ret .= "</data>\n";

				for ($i = 0; $i < $level - 2; $i++)
					$ret .= "\t";

				$ret .= "</reply>";
			}

			return $ret;
		}
	}
?>
