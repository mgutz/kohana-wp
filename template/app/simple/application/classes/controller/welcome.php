<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {
	function action_index() {
		// $this->request->response = "Hello world!";
		$this->render('welcome/index', array('file' => DOCROOT));
	}
}
