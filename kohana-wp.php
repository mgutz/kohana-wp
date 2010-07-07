<?php 
#     /* 
#     Plugin Name: Kohana-WP 
#     Plugin URI: http://www.mgutz.com/kohana-wp
#     Description: Create WordPress pages, plugins, ... with Kohana 3 MVC
#     Author: Mario L Gutierrez
#     Version: 0.1
#     Author URI: http://www.mgutz.com
#     */   

/**
 * Encapsulate Kohana-WP in a class to avoid any conflicts.
 */
class KWP_Plugin {
	/**
	 * Registers the plugin and sets a flag indicating plugin's activated state.
	 * @return void
	 */
	function __construct() {
		register_deactivation_hook('kohana-wp/kohana-wp.php', array($this, 'deactivate'));
		register_activation_hook('kohana-wp/kohana-wp.php', array($this, 'activate'));
	}

	function register_admin_hooks() {
		add_action('admin_menu', 'kohana_register_admin_menu');
		add_action('admin_menu', 'kwp_page_add_box');
		add_filter('plugin_row_meta', 'KWP_Filter::plugin_row_meta', 10, 2);
	}


	/**
	 * @static Register WordPress hooks.
	 * @return void
	 */
	function register_content_hooks() {
		/**
		 * Register Actions
		 */
		add_action('wp_head', 'kohana_wp_head');
		add_action('widgets_init', create_function('', 'return register_widget("KohanaWidget");'));

		/**
		 * Register Filters
		 */
		add_filter('get_pages', 'KWP_Filter::get_pages');
		add_filter('page_template', 'KWP_Filter::page_template');
		add_filter('request', 'KWP_Filter::request');
		add_filter('single_post_title', 'KWP_Filter::title');
		add_filter('the_content', 'KWP_Filter::the_content');
		add_filter('the_title', 'KWP_Filter::title');
		add_filter('wp', 'KWP_Filter::wp');
	}

	/**
	 * Define constants and variables.
	 *
	 * @static
	 * @return void
	 */
	function define_constants_and_vars() {
		// Directory containing MVC framework, modules and site applications. (not the plugin root)
		define('KOHANA_ROOT', WP_CONTENT_DIR . '/kohana/');

		// Meta key for route.
		define('KWP_ROUTE', '_kwp_route');

		// Meta key for output placement.
		define('KWP_PLACEMENT', '_kwp_placement');

		// Translation domain
		define('KWP_DOMAIN', 'kwp_domain');
	}

	/**
	 * Function is called when plugin is activated by wordpress
	 *
	 * Creates a wordpress page which will act as our Kohana frontloader
	 * and creates the default kohana options.
	 * @return
	 */
	function activate() {
		error_log('activating kohana plugin');

		// Create a page in word press to act as the kohana frontloader
		$my_post = array();
		$my_post['post_title'] = 'Kohana';
		$my_post['post_content'] = '';
		$my_post['post_status'] = 'publish';
		$my_post['post_type'] = 'page';

		// Insert the post into the database
		$kohana_front_loader = wp_insert_post($my_post);

		add_option('kwp_front_loader', $kohana_front_loader);
		add_option('kwp_default_placement', 'replace');
		add_option('kwp_process_all_uri', 1);
		add_option('kwp_system_path', WP_CONTENT_DIR . '/kohana/framework/current/system/');
		add_option('kwp_module_path', WP_CONTENT_DIR . '/kohana/modules/');
		add_option('kwp_application_path', WP_CONTENT_DIR . '/kohana/sites/all/');
		add_option('kwp_bootstrap_path', '');
		add_option('kwp_ext', '.php');
		add_option('kwp_modules', '');
		add_option('kwp_default_controller', 'welcome');
		add_option('kwp_default_action', 'index');
		add_option('kwp_default_id', '');
		add_option('kwp_front_loader_in_nav', 0);
		add_option('kwp_page_template', '');
		add_option('kwp_activated', '1');
	}

	/**
	 * Function is called when plugin is deactivated by wordpress
	 *
	 * Deletes the wordpress page that was acting as Kohana front loader
	 * and removes all kohana options.
	 * @return
	 */
	function deactivate() {
		error_log('deactivating kohana plugin');

		update_option('kwp_activated', '0');
		wp_delete_post(get_option('kwp_front_loader'));

		// TODO: if someone accidentally deactivates the plugin, all routes and plugin info is lost!
		//		 A better idea is to ask the user if found settings should be used when the plugin is re-activated.
		delete_option('kwp_front_loader');
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

	/**
	 * The main function.
	 *
	 * @return void
	 */
	function main() {
		$is_activated = (get_option('kwp_activated'));
		if ($is_activated) 
			return;
		
		$this->define_constants_and_vars();

		$is_in_admin = (strpos($_SERVER['REQUEST_URI'], 'wp-admin/'));
		if ($is_in_admin) {
			$this->register_admin_hooks();
		}
		else {
			$this->register_content_hooks();
			include_once 'kwp_widget.php';
			include_once 'kohana_index.php';
		}

	}

	/**
	 * Runs the plugin.
	 * 
	 * @static
	 * @return void
	 */
	static function run() {
		$kwp = new KWP_Plugin();
		$kwp->main();
	}
}

require_once 'classes/kwp/filter.php';
KWP_Plugin::run();


/* Adds a custom section to the "advanced" Post and Page edit screens */
function kwp_page_add_box() {
	include_once 'kwp_admin_page_type.php';
}


/**
 * Checks if a Kohana request is in progress.
 * Must be called after kohana_request_filter has been triggered.
 *
 * @return boolean
 */
function is_kohana_request() {
	global $wp;
	return !empty($wp->kohana->request);
}



/**
 * print any extra_head html that has been assigned to the Kohana request.
 */
function kohana_wp_head() {
	global $wp;
	if (is_kohana_request() && !empty($wp->kohana->extra_head)) {
		print $wp->kohana->extra_head;
	}
}


function bootstrap($app_path) {

	static $bootstrapped = false;
	if ($bootstrapped) return;
	$bootstrapped = true;

	if (!realpath($app_path)) {
		throw new Exception("Invalid application path.", $app_path);
	}
	define('APPPATH', $app_path);
	define('KWP_PAGEURL', get_permalink());
	$app = substr(strrchr(trim(APPPATH, '/'), '/'), 1);
	$prefix = strpos(KWP_PAGEURL, '?') ? '&kr=' : '?kr=';
	define('KWP_HOSTURL', KWP_PAGEURL . $prefix);
	define('KWP_APPURL', KWP_HOSTURL . $app . '/');

	# TODO: Should bootstrap path be unique to application?
	if (get_option('kwp_bootstrap_path')) {
		include get_option('kwp_bootstrap_path');
	} else {
		include 'kohana_bootstrap.php';
	}
}



/**
 * Function adds the Kohana options page to wordpress dashboard
 * @return
 */
function kohana_register_admin_menu() {
	add_options_page("Kohana-WP", "Kohana-WP", 'manage_options', "Kohana", "kohana_admin_menu");
}

/**
 * Function includes the Kohana options/admin page for diplay
 * @return
 */
function kohana_admin_menu() {
	include_once dirname(__FILE__) . '/kwp_admin_control_panel.php';
}



/**
 * Function returns false if Kohana is not set up
 * @return
 */
function should_kohana_run() {
	static $is_ok = false;

	// Do not run within the admin area, typos in kohana options have rendered sections of the admin inoperable
	if (strpos($_SERVER['REQUEST_URI'], 'wp-admin/')) {
		return false;
	}

	if ($is_ok)
		return true;
	// no need to check
	$is_ok = true;
	return true;

	// If options are not set then return false
	if (!get_option('kwp_system_path')) {
		return false;
	}
	// If main kohana class file is not found in system path return false
	if (!is_file(get_option('kwp_system_path') . 'classes/kohana.php'))
		return false;

	// If default route not set return false
	if (!get_option('kwp_default_controller') OR !get_option('kwp_default_action'))
		return false;

	// We should be good to go, return true.
	$is_ok = true;
	return true;
}


/**
 * Returns the post id if the request is for a valid wordpress page/post.
 * Returns false or 0 if the request is going to result in a wordpress 404.
 *
 * @return post id
 * @param array $request
 */
function kohana_validate_wp_request($request) {
	global $wpdb;
	global $wp;

	// Check to see if we are requesting the wordpress homepage
	if (is_wp_homepage('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])) {
		// Check to see if the home page is a wordpress page or blog listings
		if (get_option('page_on_front')) {
			// return the ID of the page 
			return get_option('page_on_front');
		}
	}


	//request contains a page id or a post id
	if (!empty($request['page_id'])) {
		return $request['page_id'];
	}
	if (!empty($request['p'])) {
		return $request['p'];
	}
	// request contains a 'pagename' or 'name' (permalinks)
	if (!empty($request['pagename'])) {
		$name = $request['pagename'];
	}
	if (!empty($request['name'])) {
		$name = $request['name'];
	}
	if (isset($name)) {
		$has_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$name'");
		return ($has_id) ? $has_id : 0;
	}

	// This could be a request for our front loader with a Kohana Controller URI appended
	$full_uri = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$wp_uri = substr($full_uri, strlen(get_option('home') . '/'));
	if ($wp->kohana->front_loader_slug == substr($wp_uri, 0, strlen($wp->kohana->front_loader_slug))) {
		return get_option('kwp_front_loader');
	}
	return 0;
}

/**
 * Function returns true if the current request is for the wordpress homepage.
 * @return Boolean
 * @param string $full_uri
 */
function is_wp_homepage($full_uri) {
	// Check to see if the request ends in a trailing slash
	if (substr($full_uri, -1) == '/') {
		$full_uri = substr($full_uri, 0, -1);
	}
	return ($full_uri == get_option('home')) ? true : false;
}


/**
 * Function parses the query string to determine if a kohana request is being made.
 *
 * Function first looks for values assigned to 'kr' in the query string
 * eg:  example.com/index.php?kr=examples/pagination
 *
 * If nothing is found in $_GET['kr'] function parses the _SERVER['REQUEST_URI'] and
 * looks for possible request in standard Kohana format
 * eg: example.com/examples/pagination
 *
 * Function then checks to make sure that Kohana has a valid controller. If found the
 * Kohana request is returned if not a blank string is returned.
 *
 * @return string $kr
 */
function kohana_parse_request() {
	global $wp;

	$kr = '';

	if ($_GET['kr']) {
		$kr = $_GET['kr'];
		if (strpos($kr, '/public/')) {
			return NULL;
		}
	} else {
		$kr = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		if (get_option('kwp_base_url') != '/') {
			$kr = str_replace(get_option('kwp_base_url'), '', $kr);
		}
	}
	// Remove index.php from our string
	$kr = str_replace('/index.php', '', $kr);

	$kr = trim($kr, '/');

	// check for presence of the kohana front loader slug
	if ($wp->kohana->front_loader_slug == substr($kr, 0, strlen($wp->kohana->front_loader_slug))) {
		$kr = substr($kr, strlen($wp->kohana->front_loader_slug . '/'));
	}
	//error_log("Removed front loader slug Examining KR: $kr");

	// Get the controller name.
	list($app, $controller, $action) = explode('/', $kr, 3);

	//error_log("Found Controller = $k_controller :: Examining: $kr");
	// Check for the presence of a kohana controller for current request
	if ($kr && is_file(KOHANA_ROOT . 'sites/all/' . $app . '/classes/controller/' . $controller . '.php')) {
		return $kr;
	}

	// Look for a defined route
	if ($kr) {
		$defined_routes = Route::all();
		if (isset($defined_routes[$kr])) {
			return $kr;
		}
		else {
			error_log("Invalid Kohana route: $kr");
		}
	}

	return '';
}


/**
 * This function creates and executes a Kohana Request object.
 * If this request has a title defined this is added to the wp global object
 *
 * @param string $kr
 * @return string  The response from the Kohana Request
 */
function kohana_page_request($kr) {
	if (!should_kohana_run())
		return '';
	global $wp;

	$kr = ($kr == 'wp_kohana_default_request') ? '' : $kr;

	try {
		# TODO isn't this the same?
		#$req = Request::instance($kr);
		#$req = $req->execute();
		$req = execute_request($kr);
	} catch (Exception $e) {
		if ($req->status == 404) {
			global $wp_query;
			$wp_query->set_404();
			return 'Page Not Found';
		}
		throw $e;
	}

	if (!empty($req->title)) {
		$wp->kohana->title = $req->title;
	}
	if (!empty($req->extra_head)) {
		$wp->kohana->extra_head = $req->extra_head;
	}
	return $req->response;
}

/**
 * Function intended to be used by template files. Returns the response from a Kohana request
 *
 * @param string $kr
 * @return string
 */
function kohana_request($kr) {
	if (!$kr) {
		return '';
	}
	return execute_request($kr)->response;
}

/**
 * Executes a request to the Kohana Framework! This is the magic behind the plugin.
 *
 * @param  $kr Kohana request segment. application/controller/action/arg0/.../argn
 * @return Kohana_Request
 */
function execute_request($kr) {
	// [0] = application namespace
	// [1] = controller/action/arg0/.../argn
	list($app, $controller_rest) = explode('/', $kr, 2);
	$app_path = KOHANA_ROOT . 'sites/all/' . $app . '/';

	bootstrap($app_path);
	$result = Request::factory($controller_rest)->execute();
	return $result;
}


/**
 * For use with template files. Echo's the result of kohana_request
 * @param string $kr
 */
function kohana($kr) {
	echo kohana_request($kr);
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
