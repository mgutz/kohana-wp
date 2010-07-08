<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 7, 2010
 * Time: 5:30:16 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Adds some nice features like creating an auto Mustache view without having to define a class.
 */
class Controller_Mustache extends Controller {
	/**
	 * URL paths compatible with WordPress. Must be used for applications to work correctly in WordPress.
	 */
	public $app_url = KWP_APP_URL;
	public $controller_url = KWP_CONTROLLER_URL;
	public $page_url = KWP_PAGE_URL;

	/**
	 * Renders a Mustache view.
	 *
	 * If a PHP class file resides in the same directory
	 * as the template, the class will be instantiated. If not, the template
	 * will be used directly.
	 *
	 * @parm $template_path The path of the template relative to classes/view.
	 * @param $locals Local variables.
	 * @param $ignore_class Ignore loading the class.
	 * @return void
	 */
	function render($template_path, $locals = NULL, $ignore_class = false) {
		if (!$ignore_class)
			$class_file = Kohana::find_file('classes/view', $template_path);
		
		if ($class_file)
			$view = $this->view_class($template_path, $locals);
		else
			$view = $this->view($template_path, $locals);
		$this->request->response = $view;
	}


	/**
	 * Renders a Mustache view with both a class and template.
	 *
	 * @param  $class_path Path of PHP class relative to view.
	 * @param  $locals Local variables.
	 * @return void
	 */
	function render_class($class_path, $locals = NULL) {
		$this->request->response = $this->view_class($class_path, $locals);
	}

	/**
	 * Creates a Mustache view using a template only.
	 *
	 * @parm $template_path The path of the template relative to classes/view.
	 * @param $locals Local variables.
	 * @return void
	 */
	function view($template_path, $locals = NULL) {
		$class = new stdClass();
		return $this->view_common($class, $template_path, $locals);
	}

	/**
	 * Creates a Mustache view using both a class and template.
	 *
	 * @param  $template_path The path of the template and class relative to classes/view.
	 * @param  $locals Local variables.
	 * @return string
	 */
	function view_class($template_path, $locals=NULL) {
		$name = str_replace('/', '_', 'view/' . $template_path);
		$class = new $name();

		return $this->view_common($class, $template_path, $locals);
	}


	private function view_common($class, $template_path, $arr = NULL) {
		$template = Kohana::find_file('classes/view', $template_path, 'mustache');

		if ($template)
			$content = file_get_contents($template);
		else
			throw new Kohana_Exception('Template file not found: view/' . $template_path);

		foreach ($this as $key => $value) {
			if ($key != 'request') {
				$instance_vars[$key] = $value;
			}
		}

		$vars = empty($arr) ? $instance_vars : Arr::merge($arr, $instance_vars);

		// Add $vars to instantiated class, but do not overwrite existing properties
		foreach ($vars as $key => $value) {
			if (!property_exists($class, $key)) {
				$class->$key = $value;
			}
		}

		$mustache = new Mustache;
		return $mustache->render($content, $class, $vars);
	}

}
