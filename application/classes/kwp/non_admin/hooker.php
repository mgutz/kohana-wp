<?php

require_once 'request.php';
require_once 'filter.php';
require_once 'widget.php';


/**
 * Registers for non-administration hooks.
 */
class KWP_NonAdmin_Hooker {
	/**
	 * @static Register WordPress hooks.
	 * @return void
	 */
	function register_hooks() {
		/**
		 * Register Actions
		 */
		add_action('wp_head', 'KWP_NonAdmin_Filter::wp_head');
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
}


/**
 * Executes a kohana route.
 * 
 * @example echo kohana('pizza_shop/order')
 * @param string $url_segment	Kohana URL segment in this format: app/controller(/index(arg0/.../argn))
 * @return string The result of executing the route.
 */
function kohana($route) {
	$result = KWP_NonAdmin_Request::execute_route($route);
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