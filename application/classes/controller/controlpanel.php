<?php

class Controller_ControlPanel extends Controller {
	// add other tabs here and they will show as a tab in control panel
	public $tab_pages = array(
		array('caption' => 'General Settings', 'action' => 'index'),
		array('caption' => 'Page Routing', 'action' => 'routes'),
		array('caption' => 'Generator', 'action' => 'generator')
	);

	function action_index() {
	    $this->render(array('controlpanel/general', 'layout/controlpanel'));
	}
	
	function action_routes() {
	    $this->render(array('controlpanel/routes', 'layout/controlpanel'));
	}
	
	function action_generator() {
		$this->render(array('controlpanel/generator', 'layout/controlpanel'));
	}
	

	function action_delete_route() {
		$this->delete_route($_POST['route_post_id']);
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
	function set_flash($message) {
		add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
		set_transient('settings_errors', get_settings_errors(), 30);
	}
}