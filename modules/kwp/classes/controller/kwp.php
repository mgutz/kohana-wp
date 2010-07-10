<?php

class Controller_KWP extends Kohana_Controller {
	/**
	 * URL paths compatible with WordPress. Must be used for applications to work correctly in WordPress.
	 */
	public $app_url = KWP_APP_URL;
	public $controller_url = KWP_CONTROLLER_URL;
	public $page_url = KWP_PAGE_URL;
	public $public_url = KWP_PUBLIC_URL;

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
	function render_text($template_path, $locals = NULL) {

		foreach (array($this, $locals) as $arr) {
			if (empty($arr)) continue;
			
			foreach ($arr as $key => $value) {
				if ($key != 'request') {
					$context[$key] = $value;
				}
			}
		}
		return (string) View::factory($template_path, $context);
	}
}
