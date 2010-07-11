<?php

class Controller_KWP extends Kohana_Controller {
	/**
	 * URL paths compatible with WordPress. Must be used for applications to work correctly in WordPress.
	 */
	public $app_url = KWP_APP_URL;
	public $controller_url = KWP_CONTROLLER_URL;
	public $page_url = KWP_PAGE_URL;

	/**
	 * Renders a Mustache template and assigns it to the response stream.
	 *
	 * If a PHP class file resides in the same directory
	 * as the template, the class will be instantiated. If not, the template
	 * will be used directly.
	 *
	 * @param string $template_path The path of the template relative to classes/view.
	 * @param array $locals Local variables.
	 * @return void
	 */
	function render($template_path, $locals = NULL) {
		$this->request->response = $this->render_text($template_path, $locals);
	}

	/**
	 * Renders a Mustache template as a string.
	 *
	 * @param string $template_path The path of the template relative to classes/view.
	 * @param array $locals Local variables.
	 * @return string
	 */
	function render_text($template_paths, $locals = NULL) {
		foreach (array($this, $locals) as $arr) {
			if (empty($arr)) continue;
			
			foreach ($arr as $key => $value) {
				if ($key != 'request') {
					$context[$key] = $value;
				}
			}
		}
		return (string) View::factory($template_paths, $context);
	}
	

//	/**
//	 * TODO: Adding a layout likes this breaks how Kohana works with views. The rendering pipeline
//	 * needs to happen here as well, othwerise different view engines will not work.
//	 *
//	 * Appends the layout to an array. If $templates is a string, it is converted into an array.
//	 * @param  $templates
//	 * @return array
//	 */
//	private function append_layout($templates) {
//		if (is_string($templates)) {
//			$templates = array($templates);
//		}
//
//		if (empty($this->_layout))
//			return $templates;
//
//		// verify it does not exist
//		foreach ($templates as $t) {
//			if (strcasecmp($this->_layout, $t) == 0) {
//				return $templates;
//			}
//		}
//
//		$templates[] = $this->_layout;
//
//		return $templates;
//	}
//
//	private $_layout = "";
//
//	protected function set_layout($layout) {
//		$_layout = $layout;
//	}
	
}
