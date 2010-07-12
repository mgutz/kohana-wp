<?php
/**
 * Generates starter code blocks for Kohana-WP.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 10:15:39 PM
 */

class Controller_Generator extends Controller {
	function action_index() {
		$this->render(array('controlpanel/generator', 'layout/controlpanel'));
	}

	function action_generate_app() {
		$app_name = $_POST['app']['name'];
		$page_template = $_POST['app']['page_template'];
		$test_page = $_POST['app']['test_page'];

		// verify path does not already exist
	}
}