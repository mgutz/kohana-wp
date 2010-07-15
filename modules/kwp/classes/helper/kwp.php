<?php

class Helper_KWP {
	
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
	 * @param array $literal_array
	 * @param string $index_key The key name for index.
	 * @param string $value_key The key name for value.
	 * @param int $index_base Defaults to 0-based array. To make 1-based, pass in 1.
	 * @param bool $skip_empty Skip emty values. Default is true.
	 * @return void
	 */
	static function objectify($literal_array, $index_key = 'i', $value_key = 'value', $index_base = 0, $skip_empty = true) {
		if (!($literal_array)) {
			return $literal_array;
		}
		$result = array();
		$i = 0;
		foreach ($literal_array as $item) {
			if ($skip_empty && empty($item))
				continue;

			$o = new stdClass();
			$o->$index_key = $i + $index_base;
			$o->$value_key = $item;
			$result[] = $o;
			$i++;
		}

 		return $result;
	}
}
