<?php defined('KWP_DOCROOT') or die('No direct script access.');

/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 7, 2010
 * Time: 12:06:23 AM
 * To change this template use File | Settings | File Templates.
 */


class KWP_Admin {
	function register_hooks() {
		add_action('admin_menu', 'KWP_Admin::show_admin_items');
		add_filter('plugin_row_meta', 'KWP_Admin::plugin_row_meta', 10, 2);
		add_action('save_post', 'Controller_PageOptions::update');
	}

	/**
	 * Add settings link to plugin admin page
	 */
	static function plugin_row_meta($links, $file) {
		$plugin = plugin_basename(__FILE__);
		// create link
		if ($file == $plugin) {
			return array_merge(
				$links,
				array(sprintf('<a href="options-general.php?page=%s">%s</a>',
					'Kohana', __('Settings')))
			);
		}
		return $links;
	}
	
	/**
	 * Function adds the Kohana options page to wordpress dashboard
	 * @return
	 */
	static function show_admin_items() {
		add_options_page("Kohana-WP", "Kohana-WP", 'manage_options', "kohana-wp", "KWP_Admin_Hooker::show_control_panel");
		add_meta_box('kwp_routing', __( 'Kohana-WP Integration', KWP_DOMAIN), 'Controller_PageOptions::index', 'page', 'advanced' );
	}

	/**
	 * Function includes the Kohana options/admin page for display
	 * @return
	 */
	static function show_control_panel() {
		echo kohana('kohana-wp/controlpanel/index');
	}
}
 
