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
	 * Renders an array of templates. Each template is processed then assigned as $content
	 * for next template.
	 *  
	 * @param  $templates List of template processed left to right.
	 * @param string $as_key The name of the variable to assign.
	 * @return void
	 */
	function render_pipe_text($templates, $locals = NULL, $as_key='content') {
		if (empty($locals)) {
			$locals = array();
		}

		$last = NULL;
		foreach ($templates as $template) {
			if (isset($last)) {
				$locals[$as_key] = $last;
			}
			$last = $this->render_text($template, $locals);
		}
		
		if (count($templates) > 1) {
			$last = $this->render_text($template, $locals);
		}

		return $last;
	}

	function render_pipe($templates, $locals = NULL, $as_key='content') {
		$this->request->response = $this->render_pipe_text($templates, $locals, $as_key);
	}

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
