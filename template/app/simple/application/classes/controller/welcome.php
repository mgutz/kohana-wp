<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {
	/**
	 * Default action.
	 */
	public function action_index() {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . basename(__FILE__, '.php');
		$this->render('welcome/index', array('file' => $file));
	}
}