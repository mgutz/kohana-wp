<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {
	/**
	 * Default action.
	 */
	public function action_index() {
		$this->render('welcome/index', array('file' => DOCROOT));
	}
}