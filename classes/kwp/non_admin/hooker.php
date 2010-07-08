<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 7, 2010
 * Time: 12:24:13 AM
 * To change this template use File | Settings | File Templates.
 */

require_once 'filter.php';

class KWP_NonAdmin_Hooker {
	/**
	 * @static Register WordPress hooks.
	 * @return void
	 */
	function register_hooks() {
		/**
		 * Register Actions
		 */
		add_action('wp_head', 'KWP_NonAdmin_Hooker::wp_head');
		//add_action('widgets_init', create_function('', 'return register_widget("KohanaWidget");'));


		/**
		 * Register Filters
		 */
		add_filter('get_pages', 'KWP_NonAdmin_Filter::get_pages');
		add_filter('page_template', 'KWP_NonAdmin_Filter::page_template');
		add_filter('request', 'KWP_NonAdmin_Filter::request');
		add_filter('single_post_title', 'KWP_NonAdmin_Filter::title');
		add_filter('the_content', 'KWP_NonAdmin_Filter::the_content');
		add_filter('the_title', 'KWP_NonAdmin_Filter::title');
		add_filter('wp', 'KWP_NonAdmin_Filter::wp');
	}

	/**
	 * print any extra_head html that has been assigned to the Kohana request.
	 */
	static function wp_head() {
		global $wp;
		if (is_kohana_request() && !empty($wp->kohana->extra_head)) {
			print $wp->kohana->extra_head;
		}
	}

}


include_once 'widget.php';


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
	if (empty($kr))
		return '';
	global $wp;

	$kr = ($kr == 'wp_kohana_default_request') ? '' : $kr;

	try {
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

	$result = execute_request($kr);
	return is_string($result) ? $result : $result->response;
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
	list($app, $controller, $rest) = explode('/', $kr, 3);
	$app_path = KOHANA_ROOT . 'sites/all/' . $app . '/';

	define('APPPATH', $app_path);

	$controller_path = $app_path . 'classes/controller/' . $controller . '.php';
	if (!is_file($controller_path)) {
		return "<span style='color:red; font-weight:bold'>Invalid Kohana route:<br />route => <code>$app/$controller</code><br/>path not found => $controller_path<code></code> </span>";
	}

	$page_url = get_permalink();
	$prefix = strpos($page_url, '?') ? '&kr=' : '?kr=';
	define('KWP_PAGE_URL', $page_url . $prefix);
	define('KWP_APP_URL', KWP_PAGE_URL . $app);
	define('KWP_CONTROLLER_URL', KWP_APP_URL . '/' . $controller);

	include_once 'kohana_bootstrap.php';
	$bootstrapper = new KohanaBootstrapper();
	$bootstrapper->index();

	# TODO: Should bootstrap path be unique to application?
	#$custom_bootstrap = get_option('kwp_bootstrap_path');
	#if ($custom_bootstrap !== false) {
	#	include_once $custom_bootstrap;
	#} else {
		$bootstrapper->bootstrap();
	#}

	$result = Request::factory($controller . '/' . $rest)->execute();
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

