<?php defined('KWP_DOCROOT') or die('No direct script access.');

/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 6, 2010
 * Time: 9:36:57 PM
 * To change this template use File | Settings | File Templates.
 */
 
class KWP_NonAdmin_Filter {

	/**
	 * print any extra_head html that has been assigned to the Kohana request.
	 */
	static function wp_head() {
		global $wp;
		if (KWP_Request::is_kohana_request() && !empty($wp->kohana->extra_head)) {
			print $wp->kohana->extra_head;
		}
	}
	

	/**
	 * Function provides a filter on the wordpress list of pages typically used to build
	 * navigation in templates. Function will remove the Kohana front loader unless the option
	 * to include this page is present.
	 *
	 * @param array $pages
	 * @return array
	 */
	static function get_pages($pages) {
		// if we are in the dashboard skip this filter
		if (is_admin()) return $pages;

		foreach ($pages as $i => $page) {
			if ($page->ID == get_option('kwp_front_loader') && !get_option('kwp_front_loader_in_nav')) {
				unset($pages[$i]);
			}
		}
		return $pages;
	}

	

	/**
	 * Function provides a filter on the wordpress content before being displayed
	 *
	 * If content has been loaded from a Kohana controller this is where it is added
	 * to or replaces the wordpress content.
	 *
	 * @param string $content
	 * @return string
	 */
	static function the_content($content) {
		global $wp;
		if (!empty($wp->kohana->content) && $wp->kohana->content) {
			switch ($wp->kohana->placement) {
				case 'before':
					$content = $wp->kohana->content . $content;
					break;
				case 'after':
					$content = $content . $wp->kohana->content;
					break;
				case 'replace':
					$content = $wp->kohana->content;
					break;
			}
		}

		$exec = "kohana_exec ";
		// Look for any Kohana requests that are dropped directly into the content
		$tag = "/\\[$exec(.*?)\\]/i";
		$matches = array();
		if (preg_match_all($tag, $content, $matches)) {
			foreach ($matches[1] as $i => $match) {
				$output = kohana(trim($match));
				$content = str_replace("[$exec" . $match . ']', $output, $content);
			}
		}

		return $content;
	}

	/**
	 * Function inspects the request as determined by wordpress and determines
	 * if Kohana needs to handle any part of this request.
	 *
	 * Steps this method follows are:
	 *
	 * - Determine if the request is for a valid wordpress page/post
	 * - If no : Determine if the query string contains a request for a valid Kohana controller
	 * - If yes : Determine if the wordpress page has a Kohana routing option
	 *
	 * If a valid Kohana controller is found and there is no valid wordpress page being
	 * called then the request is changed to the Kohana front loader.
	 *
	 * Details of the Kohana request is added to the global $wp class. Eg:
	 * $wp->kohana->request = 'welcome/index'
	 *
	 * @return array $request
	 * @param array $request
	 */
	static function request($request) {
		// if kohana isn't set up skip
		if (empty($request)) return $request;

		global $wp;
		global $wpdb;

		// Get the wordpress page_name of our kohana front loader
		$wp->kohana->front_loader_slug = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = " . get_option('kwp_front_loader'));

		// attempt to validate the request by looking for a post or page id
		$requested_post_id = KWP_Request::post_id_from_request($request);
		//error_log( "Found the post/page id of $requested_post_id" );


		// If request is not for a valid word press page. Look for valid kohana request
		if (!$requested_post_id && get_option('kwp_process_all_uri')) {
			//error_log( "No page found and process all uri is enabled. Examining uri for Kohana controller request" );
			// Parse query string and look for kohana type requests
			$kohana_request = KWP_Request::parse_request();

			if ($kohana_request) {
				$wp->kohana->request = $kohana_request;
				$wp->kohana->placement = get_option('kwp_default_placement');
				// Set request to our kohana front loader
				$request = array();
				$request['page_id'] = get_option('kwp_front_loader');

			}
			// Request is for our wordpress kohana front loader
		}
		elseif ($requested_post_id == get_option('kwp_front_loader')) {
			//error_log( "Request for Kohana front loader provided. Examine URI for Kohana controller request" );
			$kohana_request = KWP_Request::parse_request();
			error_log("Kohana request is $kohana_request");
			$wp->kohana->request = ($kohana_request) ? $kohana_request : 'wp_kohana_default_request';
			$wp->kohana->placement = get_option('kwp_default_placement');
			// Just because we found the front loader, wp may still think this is a 404
			// Force page_id into the request array.
			$request = array();
			$request['page_id'] = get_option('kwp_front_loader');
		}
		else { // Look for Kohana Routing Option
			$route = get_post_meta($requested_post_id, KWP_ROUTE, true);
			if (!empty($route)) {
				list($post_route, $post_placement) = explode('||', $route, 2);
				$wp->kohana->request = $post_route;
				$wp->kohana->placement = $post_placement;

				// a kohana view may have linked to a controller/action different than the start controller/action
				// assigned in wp-admin, e.g. a page that hosts a multi-form kohana application
				if (isset($_GET['kr']) || isset($_POST['kr'])) {
					$kohana_request = KWP_Request::parse_request();
					if ($kohana_request) {
						$wp->kohana->request = $kohana_request;
					}
					else {
						// the querystring is an invalid controller/action, show Page Not Found
						$request['pagename'] = 0;
					}
				}
			}
		}
		return $request;
	}	

	/**
	 * Function provides a filter on the main wp class.
	 * This filter is called after wp has been completely loaded and created
	 * but before any content is loaded.
	 *
	 * If a Kohana request was found when filtering the wp request then this is where we
	 * create the first Kohana Request object via the kohana_request() function.
	 *
	 * Output from the Kohana request is placed into the wp class so that it is available
	 * when it comes time to display the combined wp and Kohana results.
	 *
	 * @param stdClass $wp
	 * @return stdClass
	 */
	static function wp($wp) {
		// if kohana isn't set up skip
		if (empty($wp->kohana->request)) return $wp;

		$wp->kohana->content = KWP_Request::kohana_page_request($wp->kohana->request);
		return $wp;
	}


	/**
	 * Function provides a filter on the title of the wordpress our post/page.
	 * When necessary the wordpress title is replaced with the Kohana title.
	 *
	 * NOTE: This function handles both wordpress filters 'the_title' and 'single_post_title'
	 *
	 * @param string $title
	 * @return string
	 */
	static function title($title) {
		global $wp;
		global $post;
		if (!empty($wp->kohana->title) && $title == $post->post_title && $post->ID == get_option('kwp_front_loader')) {
			$title = $wp->kohana->title;
		}
		return $title;
	}

	/**
	 * Replaces the page_template with the one specified in kohana_page_template
	 * if this is a kohana request.
	 * @param string $template
	 * @return string
	 */
	static function page_template($template) {
		if (KWP_Request::is_kohana_request() && get_option('kwp_page_template')) {
			return locate_template(array(get_option('kwp_page_template')));
		}
		return $template;
	}



}
