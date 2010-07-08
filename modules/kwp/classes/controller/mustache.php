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
	// must use triple braces {{{ }}} on urls so they are not escaped
	public $app_url = KWP_APP_URL;
	public $controller_url = KWP_CONTROLLER_URL;
	
	/**
	 * A normal view has no logic behind it and does not require a code-behind class. 
	 *
	 * @parm $path The relative path to the template from 
	 * @return void
	 */
	function view($template_path, $locals = NULL) {
		$template = Kohana::find_file('classes/view', $template_path, 'mustache');

		if ($template)
			$content = file_get_contents($template);
		else
			throw new Kohana_Exception('Template file not found: view/' . $template_path);

		$mustache = new Mustache;

		foreach ($this as $key => $value) {
			if ($key == 'request') {
				continue;
			}
			$instance_vars[$key] = $value;
		}

		return $mustache->render($content, empty($locals) ? $instance_vars : Arr::merge($locals, $instance_vars));
	}


	function render($template_path, $locals = NULL) {
		$this->request->response = $this->view($template_path, $locals);
	}


	function view_class($template_path, $locals=NULL) {
		$template = Kohana::find_file('classes/view', $template_path, 'mustache');

		if ($template)
			$content = file_get_contents($template);
		else
			throw new Kohana_Exception('Template file not found: view/' . $template_path);


		foreach ($this as $key => $value) {
			if ($key == 'request') {
				continue;
			}
			$instance_vars[$key] = $value;
		}

		$name = Inflector::underscore('view_' . $template_path);
		$name = str_replace('/', '_', $name);
		$vars = empty($locals) ? $instance_vars : Arr::merge($locals, $instance_vars);
		$class = new $name($vars);

		foreach ($vars as $key => $value) {
			$class->$key = $value;
		}

		$mustache = new Mustache;
		return $mustache->render($content, $class, $vars);
	}

	/**
	 * Renders a view using a code-behind class.
	 *
	 * @param  $class_path Path of PHP class relative to view.
	 * @param  $locals
	 * @return void
	 */
	function render_class($class_path, $vars = NULL) {
		$this->request->response = $this->view_class($class_path, $vars);
	}

	/**
	 * TODO: need a way to exclude instance variables.
	 * @param  $locals
	 * @return void
	 */
	function assign_instance_variables($locals) {
		foreach ($this as $key => $value) {
			$locals[$key] = $value;
		}
	}

}
