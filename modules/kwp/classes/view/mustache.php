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
	* $output = View::capture($file, $data);
	*
	* @param string filename
	* @param array variables
	* @return string
	*/
	protected static function capture($kohana_view_filename, array $kohana_view_data) {
		return self::mustache($kohana_view_filename, $kohana_view_data, View::$_global_data);
	}


	/**
	 * Renders a Mustache template. Will instantiate a code-behind class of the same name if it exists in the
	 * same directory.
	 *
	 * @throws Kohana_Exception
	 * @param  string $template Is the template file relative to views directory.
	 * @example	'welcome/index' == APPPATH . '/views/welcome/index.mustache'
	 * @param  array $locals
	 * @return string
	 */
	static function mustache($template, $locals = NULL) {
		// Mustache will auto-instante but, we need to pass local.
		// Create the template class manually (stdclass is used if template class does not exist)
		// and merge local variables into it.
		$view = self::new_code_behind_class($template);
		if (!empty($locals)) {
			foreach ($locals as $key => $value) {
				if ($key != 'request') {
					$view->$key = $value;
				}
			}
		}
		
		$mustache = new Mustache;
		$template_base = APPPATH . 'views' . DIRECTORY_SEPARATOR;
		$mustache->_setTemplateBase($template_base);
		$mustache->_setTemplateName($template);
		$output = $mustache->render(null, $view);

		return $output;
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
			$klass = new $name();
		}
		else
			$klass = new stdClass();

		return $klass;
	}
}