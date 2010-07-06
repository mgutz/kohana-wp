<?php

/**
 * Base controller to facilitate working within WordPress.
 */ 
class Controller_KWP extends Controller {

 	/**
	 * Renders a view then assigns it to response.
	 *
	 * @param $view_path Path to view relative to views/ directory.
	 */
    function render($view_path) {
        $this->request->response = View::factory($view_path);
    }
	
	/**
	 * Creates a new view object with $kwp helper injected.
	 *
	 * @param $view_path Path to view relative to views/ directory.
	 * @return A view.
	 */
	function view($view_path) {
		return View::factory($view_path)->set('kwp', new Helper_KWP($this));
	}
}

?>
