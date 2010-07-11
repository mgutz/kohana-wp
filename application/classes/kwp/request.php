<?php

/**
 * Kohana request processor.
 */
class KWP_Request {


	
	/**
	 * Checks if a Kohana request is in progress.
	 * Must be called after kohana_request_filter has been triggered.
	 *
	 * @return boolean
	 */
	static function is_kohana_request() {
		global $wp;
		return !empty($wp->kohana->request);
	}
	
	/**
	 * Returns the post id if the request is for a valid wordpress page/post.
	 * Returns false or 0 if the request is going to result in a wordpress 404.
	 *
	 * @return post id
	 * @param array $request
	 */
	static function post_id_from_request($request) {
		global $wpdb;
		global $wp;

		// Check to see if we are requesting the wordpress homepage
		if (self::is_wp_homepage('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])) {
			// Check to see if the home page is a wordpress page or blog listings
			$front_id = get_option('page_on_front');
			if ($front_id) {
				// return the ID of the page
				return $front_id;
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
	 * Function parses the query string to determine if a kohana request is being made.
	 *
	 * Function first looks for values assigned to 'kr' in the query string
	 * eg:  example.com/index.php?kr=app/controller/action
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
	static function parse_request() {
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
		if (!empty($wp->kohana->front_loader_slug) && $wp->kohana->front_loader_slug == substr($kr, 0, strlen($wp->kohana->front_loader_slug))) {
			$kr = substr($kr, strlen($wp->kohana->front_loader_slug . '/'));
		}
		//error_log("Removed front loader slug Examining KR: $kr");

		// Get the controller name.
		list($app, $controller, $action) = explode('/', $kr, 3);

		//error_log("Found Controller = $k_controller :: Examining: $kr");
		// Check for the presence of a kohana controller for current request
		$controller_path = KOHANA_APPS_ROOT."$app/application/classes/controller/$controller.php";
		if ($kr && is_file($controller_path)) {
			return $kr;
		}

		// TODO: should invalid routes be allowed to go through?
		error_log("Controller does not exist for route: $controller_path");

		// TODO: application defined routes are not known yet.
		//       May need init script which allows apps to register routs with the sytem. e
//		if ($kr) {
//			$defined_routes = Route::all();
//			if (isset($defined_routes[$kr])) {
//				return $kr;
//			}
//			else {
//				error_log("Invalid Kohana route: $kr");
//			}
//		}

		return '';
	}



	/**
	 * This function creates and executes a Kohana Request object.
	 * If this request has a title defined this is added to the wp global object
	 *
	 * @param string $kr
	 * @return string  The response from the Kohana Request
	 */
	static function kohana_page_request($kr) {
		if (empty($kr))
			return '';
		global $wp;

		$kr = ($kr == 'wp_kohana_default_request') ? '' : $kr;

		try {
			$req = self::execute_route($kr);
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
	 * Executes a route to the Kohana Framework! This is the magic behind the plugin.
	 *
	 * @param  string $kr Kohana request segment. application/controller/action/arg0/.../argn
	 * @return Kohana_Request
	 */
	static function execute_route($route) {
		if (!($route) || !preg_match('/^[a-z_][a-z0-9_\-]*\/[a-z_][a-z0-9_]*\/?([a-z_][a-z0-9_]*)*/i', $route)) {
			error_log("Invalid kohana route: $route");
			return '';
		}

		$app_root = self::app_specific_setup($route);
		self::load_kohana($app_root);

		list($app, $kohana_route) = explode('/', $route, 2);
		$result = Request::factory($route)->execute();

		return $result;
	}

	static function page_url() {
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	private static function app_specific_setup($route) {
		// [0] = application namespace
		// [1] = controller/action/arg0/.../argn
		list($app_name, $controller, $rest) = explode('/', $route, 3);

		$app_root = KOHANA_APPS_ROOT . $app_name;
		$controller_path = "$app_root/application/classes/controller/$controller.php";
		if (!is_file($controller_path)) {
			return "<span style='color:red; font-weight:bold'>Invalid Kohana route:<br />route => <code>$app/$controller</code><br/>path not found => $controller_path<code></code> </span>";
		}

		// define constants for URL helpers
		$page_url = self::page_url();
		
		// get rid of existing kr since we will be rebuilding it (will keep appending otherwise)
		$page_url = preg_replace('/(&|\?)kr=.*/i', '', $page_url);

		$prefix = strpos($page_url, '?') ? '&kr=' : '?kr=';
		define('KWP_PAGE_URL', $page_url . $prefix);
		define('KWP_APP_URL', KWP_PAGE_URL . $app_name);
		define('KWP_CONTROLLER_URL', KWP_APP_URL . '/' . $controller);
		
		return $app_root;
	}

	private static function load_kohana($docroot) {
		// use Kohana-WP's default system if application does not provide it
		if (is_file($docroot.'/system/classes/kohana/core.php')) {
			$system = 'system';
		} 
		else {
			$system = KWP_DOCROOT.'system';
		}

		require_once 'bootstrapper.php';
		KWP_Bootstrapper::boot($docroot, 'application', 'modules', $system);
	}


	/**
	 * Calculates the doc root of a Kohana application. DOCROOT refers to the parent directory of application/, modules/
	 * and system/ by convention.
	 *
	 * @static
	 * @param  $app_name
	 * @return string
	 */
	private static function doc_root($app_name) {
		return KOHANA_APPS_ROOT."$app_name/";
	}

	/**
	 * Function returns true if the current request is for the wordpress homepage.
	 * @return Boolean
	 * @param string $full_uri
	 */
	private static function is_wp_homepage($full_uri) {
		// Check to see if the request ends in a trailing slash
		if (substr($full_uri, -1) == '/') {
			$full_uri = substr($full_uri, 0, -1);
		}
		return ($full_uri == get_option('home')) ? true : false;
	}

}
