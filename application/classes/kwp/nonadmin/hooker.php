<?php defined('KWP_DOCROOT') or die('No direct script access.');

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


