<?php defined('KWP_DOCROOT') or die('No direct script access.');


/**
 * Page options controller. Not a normal controller. WP manages the Page settings.
 */
class Controller_PageOptions {

	/**
	 * Renders the Kohana-WP Integration options box in admin | Edit Page.
	 */
	static function index() {
		echo View_Mustache::mustache_auto_class('pageoptions/index', null);
	}


	/**
	 * Save data entered in options box.
	 *
	 * NOTE: This a WP call back so there is no render.
	 */
	static function update($page_id) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times

		if (empty($_POST['kwp']['noncename'])) {
			return $page_id;
		}

		if (!wp_verify_nonce($_POST['kwp']['noncename'], $page_id)) {
			return $page_id;
		}

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $page_id;


		// Check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $page_id))
				return $page_id;
		}

		// Add hidden metadata (underscore)
		if (empty($_POST['kwp']['route'])) {
			delete_post_meta($page_id, KWP_ROUTE);
			delete_post_meta($page_id, KWP_ROUTE);
		}
		else {
			self::add_update_post_meta($page_id, KWP_ROUTE, $_POST['kwp']['route']);
			self::add_update_post_meta($page_id, KWP_PLACEMENT, $_POST['kwp']['placement']);
		}

		return true;
	}

	/**
	 * Adds or updates a post meta data.
	 */
	static function add_update_post_meta($post_id, $key, $value) {
		add_post_meta($post_id, $key, $value, true) or update_post_meta($post_id, $key, $value);
	}
}


?>
