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
		return KwpMustache::render($template_path, $this, $locals);
	}
}
