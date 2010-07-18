<?php

if (!current_user_can('manage_options')) {
	wp_die( __('You do not have sufficient permissions to access this page.') );
}


class Controller_ControlPanel extends Controller {

	function action_index() {
		$this->retrieve_settings();

		$app_root = $this->settings->kwp_applications_root;
		$controller_url = $this->controller_url;
		if (!realpath($app_root)) {
			$mk_link = "<a href='$controller_url/mk_apps_root'>Make Directory!</a>";
			$this->add_flash_notice("Applications root is not a valid directory: $app_root $mk_link", 'error');
		}
		
	    $this->render('controlpanel/general');


	}

	private function retrieve_settings() {
		$this->settings = isset($this->settings) ? $this->settings : Model_GeneralSettings::factory()->first();
	}
	
	function action_routes() {
	    $this->render('controlpanel/routes');
	}

	function action_mk_apps_root() {
		$this->retrieve_settings();
		$this->mk_apps_root();
		$this->render('controlpanel/general');
	}
	

	private function mk_apps_root() {
		// create applications root
		try {
			KWP::mkdir_p($this->settings->kwp_applications_root);
			$this->add_flash_notice("Setings saved.");
		}
		catch(Exception $e) {
			$this->add_flash_notice($e->getMessage());
		}
	}

	function action_update_general() {
		$this->settings = Model_GeneralSettings::factory()->create($_POST)->save();
		$this->render('controlpanel/general');
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