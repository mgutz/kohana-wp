<?php
class Controller_Multistep extends Controller {
	public $company = "Kohana Hawaiian Pizza";

	function action_index() {
	    // if template path is not provided, template name is derived from current controller/action
	    //
	    // renders views/multistep/index.mustache
		$this->render();
	}
	
	/**
	 * More complex example of a Mustache view which uses a class to format the price.
	 * The class itself, view/order/summary.php, is a POPO.
	 *
	 * All instance variables ($company) are passed, as well as $order and $price as local
	 * variables data.
	 * 
	 * @return void
	 */
	function action_summary() {
		$order = $_POST['order'];

		// Since multistep/summary.php code-behind class exists, it will be instantiated.
		// Use a code-behind class when a view requires view logic.
        //
		// local variables must be passed in manually
		$locals = array('order' => $order, 'price' => 100000);
		$this->render('multistep/summary', $locals);
	}
}

