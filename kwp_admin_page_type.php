<?php

/* Use the save_post action to do something with the data entered */
add_action('save_post', 'kwp_page_save_postdata');

add_meta_box('kwp_routing', __( 'Kohana-WP Integration', KWP_DOMAIN), 
            'kwp_page_inner_custom_box', 'page', 'advanced' );


/* Prints the inner fields for the custom post/page section */
function kwp_page_inner_custom_box() {
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


/* When the post is saved, saves our custom data */
function kwp_page_save_postdata($post_id) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if (empty($_POST['kwp']['noncename'])) {
		return $post_id;
	}

	if (!wp_verify_nonce($_POST['kwp']['noncename'], plugin_basename(__FILE__))) {
	  	return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
	  	return $post_id;


	// Check permissions
	if ('page' == $_POST['post_type']) {
	  	if (!current_user_can('edit_page', $post_id))
	    	return $post_id;
	}

	// Add hidden metadata (underscore)
	if (empty($_POST['kwp']['route'])) {
		delete_post_meta($post_id, KWP_ROUTE);
		delete_post_meta($post_id, KWP_ROUTE);
	}
	else {
		add_update_post_meta($post_id, KWP_ROUTE, $_POST['kwp']['route']);
		add_update_post_meta($post_id, KWP_PLACEMENT, $_POST['kwp']['placement']);
	}


	
	return true;
}

/**
 * Adds or updates a post meta data.
 */
function add_update_post_meta($post_id, $key, $value) {
	add_post_meta($post_id, $key, $value, true) or update_post_meta($post_id, $key, $value);
}

?>
