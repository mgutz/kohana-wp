<?php

/**
 * Encapsulate Kohana-WP in a class to avoid any conflicts.
 */
class KWP_Plugin {

	/**
	 * Define constants and variables.
	 *
	 * @static
	 * @return void
	 */
	function define_constants() {
		define('KWP_ROOT', WP_PLUGIN_DIR . '/kohana-wp/');

		// Directory containing MVC framework, modules and site applications. (not the plugin root)
		define('KOHANA_ROOT', WP_CONTENT_DIR . '/kohana/');

		// Meta key for route.
		define('KWP_ROUTE', '_kwp_route');

		// Meta key for output placement.
		define('KWP_PLACEMENT', '_kwp_placement');

		// Translation domain
		define('KWP_DOMAIN', 'kwp_domain');

		// NOTE: Other constants are defined in classes/kwp/non_admin/hooker#execute_route, these depend
		// on dynamic application/controller paths, which are unknown until execution of a request
	}

	/**
	 * Function is called when plugin is activated by wordpress
	 *
	 * Creates a wordpress page which will act as our Kohana frontloader
	 * and creates the default kohana options.
	 * @return
	 */
	static function activate() {
		error_log('activating kohana plugin');

		// Create a page in word press to act as the kohana frontloader
		$my_post = array();
		$my_post['post_title'] = 'Kohana';
		$my_post['post_content'] = '';
		$my_post['post_status'] = 'publish';
		$my_post['post_type'] = 'page';

		// Insert the post into the database
		$kohana_front_loader = wp_insert_post($my_post);

		self::add_update_option('kwp_activated', '1');
		self::add_new_option('kwp_front_loader', $kohana_front_loader);
		self::add_new_option('kwp_default_placement', 'replace');
		self::add_new_option('kwp_process_all_uri', 1);
		self::add_new_option('kwp_system_path', WP_CONTENT_DIR . '/kohana/framework/current/system/');
		self::add_new_option('kwp_module_path', WP_CONTENT_DIR . '/kohana/modules/');
		self::add_new_option('kwp_application_path', WP_CONTENT_DIR . '/kohana/sites/all/');
		self::add_new_option('kwp_bootstrap_path', '');
		self::add_new_option('kwp_ext', '.php');
		self::add_new_option('kwp_modules', '');
		self::add_new_option('kwp_default_controller', 'welcome');
		self::add_new_option('kwp_default_action', 'index');
		self::add_new_option('kwp_default_id', '');
		self::add_new_option('kwp_front_loader_in_nav', 0);
		self::add_new_option('kwp_page_template', '');
	}

	/**
	 * Function is called when plugin is deactivated by wordpress
	 *
	 * Deletes the wordpress page that was acting as Kohana front loader
	 * and removes all kohana options.
	 * @return
	 */
	static function deactivate() {
		error_log('deactivating kohana plugin');

		update_option('kwp_activated', '0');
		wp_delete_post(get_option('kwp_front_loader'));
		delete_option('kwp_front_loader');

		return;

		// TODO: if someone accidentally deactivates the plugin, all routes and plugin info is lost!
		//		 A better idea is to ask the user if found settings should be used when the plugin is re-activated.
		delete_option('kwp_default_placement');
		delete_option('kwp_process_all_uri');
		delete_option('kwp_system_path');
		delete_option('kwp_modules_path');
		delete_option('kwp_application_path');
		delete_option('kwp_bootstrap_path');
		delete_option('kwp_ext');
		delete_option('kwp_front_loader_in_nav');
		delete_option('kwp_modules');
		delete_option('kwp_default_controller');
		delete_option('kwp_default_action');
		delete_option('kwp_default_id');
		delete_option('kwp_page_template');
	}

	static function add_update_option($key, $value) {
		add_option($key, $value) or update_option($key, $value);
	}

	static function add_new_option($key, $value) {
		if (!(get_option($key) !== false)) {
			add_option($key, $value);
		}
	}


	/**
	 * The main function. This file is never loaded by WordPress if the plugin is not activated.
	 *
	 * @return void
	 */
	function run() {
		$this->define_constants();

		$is_in_admin = (strpos($_SERVER['REQUEST_URI'], 'wp-admin/'));
		if ($is_in_admin) {
			register_deactivation_hook('kohana-wp/kohana-wp.php', 'KWP_Plugin::deactivate');
			register_activation_hook('kohana-wp/kohana-wp.php', 'KWP_Plugin::activate');
			include_once 'admin/hooker.php';
			$admin = new KWP_Admin_Hooker();
			$admin->register_hooks();
		}
		else {
			include_once 'non_admin/hooker.php';
			$non_admin = new KWP_NonAdmin_Hooker();
			$non_admin->register_hooks();
		}
	}
}