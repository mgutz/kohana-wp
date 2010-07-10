<?php

/**
 * Acts as an object wrapper for HTML pages with embedded PHP, called "views".
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class View_Mustache extends Kohana_View {

	/**
	 * Sets the view filename.
	 *
	 *     $view->set_filename($file);
	 *
	 * @param   string  view filename
	 * @return  View
	 * @throws  Kohana_View_Exception
	 */
	public function set_filename($file) {
		// Store the file path locally
		$this->_file = $file;

		return $this;
	}

	/**
	 * Captures the output that is generated when a view is included.
	 * The view data will be extracted to make local variables. This method
	 * is static to prevent object scope resolution.
	 *
	 *     $output = View::capture($file, $data);
	 *
	 * @param   string  filename
	 * @param   array   variables
	 * @return  string
	 */
	protected static function capture($kohana_view_filename, array $kohana_view_data) {
		return self::mustache($kohana_view_filename, $kohana_view_data, View::$_global_data);
	}


	/**
	 * Renders a Mustache template. Will instantiate a code-behind class of the same name if it exists in the
	 * same directory.
	 *
	 * @throws Kohana_Exception
	 * @param  string $template_path
	 * @param  stdClass $context
	 * @param  array $locals
	 * @return string
	 */
	static function mustache($template_path, $context, $locals = NULL) {
		$class = self::new_code_behind_class($template_path);

		// assign/override from instance variables of context
		foreach ($context as $key => $value) {
			if ($key != 'request') {
				$class->$key = $value;
			}
		}

		// assign/override from passed-in local variables
		if (!empty($locals)) {
			foreach ($locals as $key => $value) {
				if ($key != 'request') {
					$class->$key = $value;
				}
			}
		}

		$template = Kohana::find_file('views', $template_path, 'mustache');
		if ($template)
			$content = file_get_contents($template);
		else
			throw new Kohana_Exception('Template file not found: views/' . $template_path);

		$mustache = new Mustache;
		$base = dirname(APPPATH . 'views/' . $template_path);
		$mustache->_setTemplateBase($base);
		return $mustache->render($content, $class);
	}

	/**
	 * Creates the code-behind class for a template. If a corresponding class is not found, then
	 * a standard class is used.
	 *
	 * @param  string $template_path Path to template relative to classes/view/.
	 * @return stdClass or template-specific class
	 */
	private static function new_code_behind_class($template_path) {
		$class_file = APPPATH . "views/$template_path.php";
		if (is_file($class_file)) {
			require $class_file;
			$name = str_replace('/', '_', 'views/' . $template_path);
			$class = new $name();
		}
		else
			$class = new stdClass();

		return $class;
	}

}