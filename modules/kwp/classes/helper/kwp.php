<?php
class Helper_KWP {
	/**
	 * @param  $controller_instance Some methods need the controller's name to build links.
	 * @return void
	 */
	function __construct($controller_instance) {
		$this->controller_name = get_class($controller_instance);
		$this->controller_short_name = self::short_name($this->controller_name);
	}

	# Extracts the short name of a class. By convention, the directory is encoded as part of the class.
	#
	# @example
	#	short_name('Controller_Post') == 'post'
	#
	static function short_name($kohana_name) {
		$class = strrchr($kohana_name, '_');
		$result = strlen($class) > 0 ? substr($class, 1) : $kohana_name;
		$result = strtolower($result);
		return $result;
	}

    # Converts a short-form URL into an absolute host URL, where host is the WordPress page invoking Kohana-WP.
	#
	# @param $query_string
	#
	#	Shortcuts are similar to linux shell command line:
	#	'/' - site url. Use to have absolute control. Will likely not invoke Kohana-WP.
	#		to_wp_url("/crm/main/index") #=> http://wordpress_site/crm/main/index
	#
	#	'^' - host url. Use to switch application space.
	#		to_wp_url("^/crm/main/index") #=> "http://wordpress_site/some_page/?kr=crm/main/index"
	#
	#	'~' - current application url. Use to refer to controllers and actions.
	#		to_wp_url("~/post/index") #=> "http://wordpress_site/some_page/?kr=current_app/post/index
	#
	#	'.' or '' - current controller url. Use to refer to actions of current controller.
	#		to_wp_url("update/1") #=> "http://wordpress_site/some_page/?kr=current_app/current_controller/update/1
	#		to_wp_url("./update/1") #=> "http://wordpress_site/some_page/?kr=current_app/current_controller/update/1
	#
	function link_to($path) {
		$lead = substr($path, 0, 2);

		if ($lead == '~/') {
			$url = KWP_APP_URL . '/' . substr($path, 2);
		} elseif ($lead == '^/') {
			$url = KWP_PAGE_URL . substr($path, 2);
		} elseif ($path[0] =='/') {
			$url = $path;
		} elseif ($lead == './') {
			$url = KWP_APP_URL . '/' . $this->controller_short_name . '/' . substr($path, 2);
		} else {
			$url = KWP_APP_URL . '/' . $this->controller_short_name . '/' . $path;
		}

		return $url;
	}

	/**
	 * Converts a literal array, into an object array.
	 *
	 * @example
	 * 		$arr = array('apple, 'orange');
	 * 		$items = objectify($arr);
	 * 		foreach ($item in $items) {
	 * 			echo $item.i . ' => ' . $item.value . '\n';
	 * 		}
	 *
	 * 	prints
	 * 		0 => apple
	 * 		1 => orange
	 *
	 * @static
	 * @param  $literal_array
	 * @param string $key
	 * @return void
	 */
	static function objectify($literal_array, $index_key = 'i', $value_key = 'value', $skip_empty = true) {
		if (!($literal_array)) {
			return $literal_array;
		}
		$result = array();
		$i = 0;
		foreach ($literal_array as $item) {
			if ($skip_empty && empty($item))
				continue;

			$o = new stdClass();
			$o->$index_key = $i;
			$o->$value_key = $item;
			$result[] = $o;
			$i++;
		}

		return $result;
	}
}
?>