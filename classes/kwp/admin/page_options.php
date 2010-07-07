<?php


/**
 * Page options controller.
 */
class KWP_Admin_PageOptions {

	/**
	 * Renders the Kohana-WP Integration options box in admin | Edit Page.
	 */
	function show() {
		global $post;

		// Use nonce for verification
		$nonce = wp_create_nonce(plugin_basename(__FILE__));
		$route_label = __("Exec Route", KWP_DOMAIN);
		$route = get_post_meta($post->ID, KWP_ROUTE, true) or '';
		$output_label = __("Result Placement", KWP_DOMAIN);
		$placement = get_post_meta($post->ID, KWP_PLACEMENT, true) or '';
		if ($placement == '')
			$placement = get_option('kwp_default_placement') or 'replace';

?>
		<input type="hidden" name="kwp[noncename]" value="<?php print $nonce; ?>" />

		<p><strong><?php print $route_label; ?></strong></p>
		<input type="text" name="kwp[route]" style="width: 100%;" value="<?php print $route; ?>" />
		<p>format: app/controller(/action(/args))</p>

		<p><strong><?php print $output_label; ?></strong></p>
		<select name="kwp[placement]">
			<option value="before" <?php print $placement == "before" ? "selected='true'" : ''; ?>>Before Page Content</option>
			<option value="replace" <?php print $placement == "replace" ? "selected='true'" : ''; ?>>Replace Page Content</option>
			<option value="after" <?php print $placement == "after" ? "selected='true'" : ''; ?>>After Page Content</option>
		</select>
<?php
	}


	/**
	 * Save data entered in options box.
	 */
	function save($page_id) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times

		if (empty($_POST['kwp']['noncename'])) {
			return $page_id;
		}

		if (!wp_verify_nonce($_POST['kwp']['noncename'], plugin_basename(__FILE__))) {
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
			$this->add_update_post_meta($page_id, KWP_ROUTE, $_POST['kwp']['route']);
			$this->add_update_post_meta($page_id, KWP_PLACEMENT, $_POST['kwp']['placement']);
		}



		return true;
	}

	/**
	 * Adds or updates a post meta data.
	 */
	private function add_update_post_meta($post_id, $key, $value) {
		add_post_meta($post_id, $key, $value, true) or update_post_meta($post_id, $key, $value);
	}
}


?>
