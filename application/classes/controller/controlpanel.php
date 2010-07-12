<?php



class Controller_ControlPanel extends Controller {

	function action_index() {
		$this->model = Model_GeneralSettings::obj()->first();
	    $this->render(array('controlpanel/general', 'layout/controlpanel'));
	}
	
	function action_routes() {
	    $this->render(array('controlpanel/routes', 'layout/controlpanel'));
	}
	

	function action_update_general() {
		$this->model = Model_GeneralSettings::obj()->create($_POST)->save();
		$this->add_flash_notice("Setings saved.");
		$this->render(array('controlpanel/general', 'layout/controlpanel'));
	}

	function action_delete_route() {
		$this->delete_route($_POST['route_post_id']);
		$this->add_flash_notice("Route deleted");
		$this->action_routes();
	}


	private function delete_route($post_id) {
		$sql = <<<SQL
			DELETE
			FROM wp_postmeta
			WHERE 
				meta_key = '_kwp_route'
				AND post_id = %d;
SQL;
		global $wpdb;
		$wpdb->query($wpdb->prepare($sql, $post_id));
	}

	/**
	 * Sets the flash message in WordPress.
	 * 
	 * @param  $message
	 * @return void
	 */
	function add_flash_notice($message, $type = 'updated') {
		add_settings_error('general', 'settings_updated', __($message), $type);
		//set_transient('settings_errors', get_settings_errors(), 30);
	}
}