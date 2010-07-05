<?php
class Controller_KWP extends Controller {
    # placeholder for common methods 
    function render($view_path) {
        $this->request->response = View::factory($view_path);
    }
	
	function view($view_path) {
		return View::factory($view_path)->set('kwp', new Helper_KWP($this));
	}
}

?>
