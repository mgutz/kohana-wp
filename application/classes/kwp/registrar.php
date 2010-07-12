<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 2:36:34 PM
 * To change this template use File | Settings | File Templates.
 */
 
class KWP_Registrar {
	static function register_admin_hooks() {
		add_action('admin_menu', 'KWP_Admin_Filter::show_admin_items');
		add_filter('plugin_row_meta', 'KWP_Admin_Filter::plugin_row_meta', 10, 2);
		add_action('save_post', 'Controller_PageOptions::update');
	}

	static function register_content_hooks() {
		require_once 'nonadmin/filter.php';
		require_once 'nonadmin/widget.php';

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
