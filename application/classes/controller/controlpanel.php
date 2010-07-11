<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 10, 2010
 * Time: 1:49:09 AM
 * To change this template use File | Settings | File Templates.
 */
 
class Controller_ControlPanel extends Controller {

	function action_index() {
	    $this->render(array('controlpanel/options', 'layout/controlpanel'));
	}
	
	function action_routes() {
	    $this->render(array('controlpanel/routes', 'layout/controlpanel'));
	}

	function action_delete_route() {
		$this->delete_route($_POST['route_post_id']);
		$this->action_routes();
	}

	private function delete_route($post_id) {
		$sql = <<<SQL
			DELETE
			FROM wp_postmeta
			WHERE ( 
			    meta_key = '_kwp_route'
				OR meta_key = '_kwp_placement'
			) AND post_id = %d;
SQL;
		global $wpdb;
		$wpdb->query($wpdb->prepare($sql, $post_id));
	}
}
