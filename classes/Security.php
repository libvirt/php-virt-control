<?php
	class Security {
		function encrypt($input, $key, $salt) {
			$init = 0;
			for ($i = 0; $i < strlen($key); $i++)
				$init += ord($key[$i]) * ord($salt[$i % strlen($salt)]);

			$ret = '';
			for ($i = 0; $i < strlen($input); $i++) {
				$shifted = false;
				$val = (($init + ord($key[$i % strlen($key)]) - ord($salt[$i % strlen($salt)])) % 256);
				$num = $val - ord($input[$i]);
				if ($num < 0) {
					$num = 256 + $num;
					$shifted = true;
				}

				$tmp = dechex($num);
				if (strlen($tmp) == 1)
					$tmp = '0'.dechex($num);
				if ($shifted) {
					if (is_numeric($tmp[0]))
						$tmp[0] = chr( ord('G') + (int)$tmp[0] );
					else
						$tmp[0] = strtoupper($tmp[0]);

					if (is_numeric($tmp[1]))
						$tmp[1] = chr( ord('G') + (int)$tmp[1] );
					else
						$tmp[1] = strtoupper($tmp[1]);
				}
				$ret .= $tmp;
			}

			$k = 0;
			for ($i = 0; $i < strlen($input); $i++)
				$k += ord($input[$i]);

			$ret .= ($k % 10);

			return $ret;
		}

		function decrypt($input, $key, $salt) {
			$init = 0;
			for ($i = 0; $i < strlen($key); $i++)
				$init += ord($key[$i]) * ord($salt[$i % strlen($salt)]);

			$cs = false;
			if (strlen($input) % 2 == 1) {
				$cs = $input[ strlen($input) - 1 ];
				$len = strlen($input) - 1;
			}
			else
				$len = strlen($input);

			$j = 0;
			$ret = '';
			for ($i = 0; $i < $len; $i += 2) {
				$shifted = false;
				$val = (($init + ord($key[$j % strlen($key)]) - ord($salt[$j % strlen($salt)])) % 256);
				$chars = $input[$i].$input[$i + 1];
				if (((ord($chars[0]) >= ord('G')) && (ord($chars[0]) <= ord('P')))
					|| ((ord($chars[0]) >= ord('A'))) && (ord($chars[0]) <= ord('Z'))) {
					if ((ord($chars[0]) >= ord('G')) && (ord($chars[0]) <= ord('P')))
						$chars[0] = strval(ord( $chars[0] ) - ord('G'));
					$shifted = true;
				}
				if (((ord($chars[1]) >= ord('G')) && (ord($chars[1]) <= ord('P')))
					|| ((ord($chars[1]) >= ord('A'))) && (ord($chars[1]) <= ord('Z'))) {
					if ((ord($chars[1]) >= ord('G')) && (ord($chars[1]) <= ord('P')))
						$chars[1] = strval(ord( $chars[1] ) - ord('G'));
					$shifted = true;
				}
				$tmp = hexdec($chars);
				if ($shifted)
					$tmp = 256 + $tmp;

				$ret .= chr( $val - $tmp );
				$j++;
			}

			if (!is_bool($cs)) {
				$k = 0;
				for ($i = 0; $i < strlen($ret); $i++)
					$k += ord($ret[$i]);

				/* Checksum check failed */
				if (($k % 10) != $cs)
					return false;
			}

			return $ret;
		}
	}
?>
