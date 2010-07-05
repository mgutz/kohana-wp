<?php

// Meta key for route.
define('KWP_ROUTE', '_kwp_route');

// Meta key for output placement.
define('KWP_PLACEMENT', '_kwp_placement');

// Translation domain
define('KWP_DOMAIN', 'kwp_domain');

class KWP {
	/**
	 * Ensures a string ends with a forward slash.
	 * 
	 * @static $str
	 * @return void
	 */
	static function slash($str) {
		if (strlen($str) == 0) {
			return '/';
		} elseif (substr($str, -1) != '/') {
			return $str . '/';
		} else {
			return $str;
		}
	}
	
}
