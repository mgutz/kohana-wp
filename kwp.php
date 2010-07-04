<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 4, 2010
 * Time: 1:47:31 PM
 * To change this template use File | Settings | File Templates.
 */
 
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
