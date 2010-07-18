<?php

// WP relaods this plugin many times on first page of admin
if (substr($_SERVER['REQUEST_URI'], -strlen('wp-admin')) == '/wp-admin/')
	return;

define('KWP_DOCROOT', WP_PLUGIN_DIR . '/kohana-wp/');

require 'registrar.php';
require 'request.php';
require 'bootstrapper.php';


/**
 * Encapsulate Kohana-WP in a class to avoid any conflicts.
 */
class KWP_Plugin {
	private static $_globals = array();

	static function set_global($key, $value) {
		self::$_globals[$key] = $value;
	}
	static function globals($key) {
		return self::$_globals[$key];
	}


	static function factory() {
		return new KWP_Plugin();
	}

	/**
	 * Define constants and variables.
	 *
	 * @static
	 * @return void
	 */
	function __construct() {
		define('KWP_IN_ADMIN', strpos($_SERVER['REQUEST_URI'], 'wp-admin/') !== false);

		// Applications directory contains many applications (parent of multiple DOCROOT)
		if (KWP_IN_ADMIN)
			define('KOHANA_APPS_ROOT', WP_PLUGIN_DIR.'/');
		else
			define('KOHANA_APPS_ROOT', WP_CONTENT_DIR . '/kohana/sites/all/');

		// Meta key for route.
		define('KWP_ROUTE', '_kwp_route');

		// Translation domain
		define('KWP_DOMAIN', 'kwp_domain');

		// NOTE: Other constants are defined in classes/kwp/nonadmin/hooker#execute_route, these depend
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
		self::add_new_option('kwp_modules', '');
		self::add_new_option('kwp_front_loader_in_nav', 0);
		self::add_new_option('kwp_page_template', '');
		
		// The directory in which generated and downloaded applications are installed.
		$apps_root = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'kohana' . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . 'all';
		if (!is_dir($apps_root)) {
			KWP::mkdir_p($apps_root);
		}

		self::add_new_option('kwp_applications_root', $apps_root);
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


		// TODO: if someone accidentally deactivates the plugin, all routes and plugin info is lost!
		//		 A better idea is to ask the user if found settings should be used when the plugin is re-activated.
		delete_option('kwp_default_placement');
		delete_option('kwp_process_all_uri');
		delete_option('kwp_front_loader_in_nav');
		delete_option('kwp_modules');
		delete_option('kwp_page_template');
		delete_option('kwp_applications_root');
		return;
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
		if (KWP_IN_ADMIN) {
			// admin has known fixed system/ and application/ directory, so boot early to take 
			// advantage of autoload features. the route is not executed
			KWP_Bootstrapper::boot('kohana-wp/controlpanel/load_only');

			register_deactivation_hook('kohana-wp/kohana-wp.php', 'KWP_Plugin::deactivate');
			register_activation_hook('kohana-wp/kohana-wp.php', 'KWP_Plugin::activate');
			KWP_Registrar::register_admin_hooks();
		}
		else {
			KWP_Registrar::register_content_hooks();
		}
	}
}

/**
 * Executes a kohana route. Checks if there is a new route on the query string. Will execute the
 * new route unless $force is set to true. New routes are set by views of the original $route to
 * new actions.
 *
 * @example echo kohana('pizza_shop/order')
 * @param string $route	Kohana route in this format: app/controller(/index(arg0/.../argn))
 * @param bool $force Force use of the route, do not use newer routes.
 * @return string The result of executing the route.
 */
function kohana($route, $force = false) {
	// see if there is an internal kr request on the URL (created by one of the views)
	if (!$force)
		$new_route = KWP_Request::parse_request();

	if (empty($new_route)) {
		$new_route = $route;
	}

	$result = KWP_Request::execute_route($new_route);
	return is_string($result) ? $result : $result->response;
}


/**
 * This is a replication of the Kohana magic function for i18n translations.
 * For use in application/views if you're leaving Wordpress i10n class to handle translations.
 *
 * Currently by default a site running this plugin will use Wordpress' i10n
 * class and the wordpress __() method for language translation.
 *
 * @param string $string
 * @param array $values
 * @return string
 */
function __k($string, array $values = NULL, $lang = 'en-us') {
	if ($lang !== I18n::$lang) {
		// The message and target languages are different
		// Get the translation for this message
		$string = I18n::get($string);
	}

	return empty($values) ? $string : strtr($string, $values);
}


/**
 * Enable Kohana translations to be default.
 * Comment out the method __() in wp-includes/i10n.php
 */
if (!function_exists('__')) {
	function __($string, $values = NULL, $lang = 'en-us') {
		if (!is_array($values)) {
			$temp = $values;
			$values = array();
			$values[] = $temp;
		}

		if ($lang !== I18n::$lang) {
			// The message and target languages are different
			// Get the translation for this message
			$string = I18n::get($string);
		}

		return empty($values) ? $string : strtr($string, $values);
	}
}