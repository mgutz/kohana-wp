<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 12, 2010
 * Time: 1:13:27 AM
 * To change this template use File | Settings | File Templates.
 */

abstract class Model_WP_Options extends Model_KWP {
	function create($arr) {
		$class = get_class($this);
		foreach ($arr as $key => $value) {
			// do not assign a property which is not defined for the model
			if (property_exists($class, $key))
				$this->$key = $value;
		}

		return $this;
	}

	function save($validate = true) {
		foreach ($this as $key => $value) {
			$this->add_update_option($key, $value);
		}

		return $this;
	}

	private static function factory() {
		$class = get_called_class();
		$model = new $class();
		return $model;
	}

	function first() {
		foreach ($this as $key => $value) {
			$this->$key = get_option($key);
		}

		return $this;
	}

	function delete($key) {
		delete_option($key);

		return $this;
	}

	function delete_all() {
		foreach ($this as $key => $value) {
			delete_option($key);
			$this->$key = NULL;
		}

		return $this;
	}

	private function add_update_option($key, $value) {
		add_option($key, $value) or update_option($key, $value);
	}
}
