<?php

class Views_ControlPanel_General {
	public $options_active = 'active';

	function front_loader() {
		/**
		 * Determine the Kohana Front Loader URL
		 */
		 $my_kohana_front = get_option('siteurl');
		 global $wpdb;

		 if (!get_option('permalink_structure')) {
			 $my_kohana_front .= '/?page_id=' . $this->settings->kwp_front_loader;
		 }
		else {
			 $my_kohana_front .= '/' . $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = " . $this->settings->kwp_front_loader);
		 }
		
		return $my_kohana_front;
	}

	function kohana_apps_root() {
		return WP_CONTENT_DIR . '/kohana/sites/all';
	}

	function nonce_field() {
		return wp_nonce_field('update-options');
	}

	function result_placement_dropdown() {
		$current = $this->settings->kwp_default_placement;
		if (empty($current)) $current = 'replace';

		return Form::select('kwp_default_placement',
			array( 'before' => 'Before Page Content', 'replace' => 'Replace Page Content', 'after' => 'After Page Content'),
			$current
		);
	}
	
	function has_page_templates() {
		return 0 != count(get_page_templates());
	}

	function page_template_options() {
		ob_start();
		page_template_dropdown($this->settings->kwp_page_template);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function process_all_uri_checked() {
		return ($this->settings->kwp_process_all_uri) ? 'checked="true"' : '';
	}

	function front_loader_in_nav_checked() {
		return ($this->settings->kwp_front_loader_in_nav) ? 'checked="true"' : '';
	}

	function edit_front_loader_url() {
		return get_option('siteurl').'/wp-admin/page.php?action=editpost='.get_option('kwp_front_loader');
	}
}
