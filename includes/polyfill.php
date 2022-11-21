<?php

require('json_encode.php');
require('json_decode.php');

// if (!function_exists('json_encode')) {

// 	define('JSON_HEX_TAG', 1);
// 	define('JSON_HEX_AMP', 2);
// 	define('JSON_HEX_APOS', 4);
// 	define('JSON_HEX_QUOT', 8);
// 	define('JSON_FORCE_OBJECT', 16);
// 	define('JSON_NUMERIC_CHECK', 32);
// 	define('JSON_UNESCAPED_SLASHES', 64);
// 	define('JSON_PRETTY_PRINT', 128);
// 	define('JSON_UNESCAPED_UNICODE', 256);
// 	define('JSON_ERROR_NONE', 0);
// 	define('JSON_ERROR_DEPTH', 1);
// 	define('JSON_ERROR_STATE_MISMATCH', 2);
// 	define('JSON_ERROR_CTRL_CHAR', 3);
// 	define('JSON_ERROR_SYNTAX', 4);
// 	define('JSON_ERROR_UTF8', 5);
// 	define('JSON_OBJECT_AS_ARRAY', 1);
// 	define('JSON_BIGINT_AS_STRING', 2);
// 	$json_error = "";

// 	function json_encode($a=false, $params=0)
// 	{
// 		if (is_null($a)) return 'null';
// 		if ($a === false) return 'false';
// 		if ($a === true) return 'true';
// 		if (is_scalar($a))
// 		{
// 			if (is_float($a))
// 			{
// 				// Always use "." for floats.
// 				return floatval(str_replace(",", ".", strval($a)));
// 			}

// 			if (is_string($a))
// 			{
// 				$replace_what = array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"');
// 				$replace_with = array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
// 				$res = str_replace($replace_what, $replace_with, $a);
// 				if($params === JSON_HEX_APOS) {
// 					$res = str_replace("'", "\u0027", $res);
// 				}
// 				return '"' . str_replace($replace_what, $replace_with, $a) . '"';
// 			}
// 			else
// 			return $a;
// 		}
// 		$isList = true;
// 		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
// 		{
// 			if (key($a) !== $i)
// 			{
// 				$isList = false;
// 				break;
// 			}
// 		}
// 		$result = array();
// 		if ($isList)
// 		{
// 			foreach ($a as $v) $result[] = json_encode($v);
// 			return '[' . join(',', $result) . ']';
// 		}
// 		else
// 		{
// 			foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
// 			return '{' . join(',', $result) . '}';
// 		}
// 	}

// 	function json_last_error() {
// 		global $json_error;
// 		return $json_error;
// 	}
// }

if (!function_exists('password_hash')) {

	define('PASSWORD_BCRYPT', 0);
	function password_hash($password, $algo=PASSWORD_BCRYPT) {
		return crypt($password, "$2y$");
	}
}

if (!function_exists('password_verify')) {

	function password_verify($password, $hash) {
		return crypt($password, "$2y$") === $hash;
	}
}


if (!function_exists('http_response_code')) {

	function http_response_code($response_code) {

		$protocol = 'HTTP/1.1';
		$message = '';
		if($response_code === 301) {
			$message = '301 Moved Permanently';
		}
		else if($response_code === 302) {
			$message = '302 Found';
		}
		else if($response_code === 404) {
			$message = '404 Not Found';
		}
		else if($response_code === 500) {
			$message = '500 Internal Server Error';
		}
		else {
			$message = '200 OK';
		}

		header($protocol.' '.$message);
	}
}

if (!function_exists('memory_get_usage')) {

	function memory_get_usage() {
		return 0;
	}
}

?>
