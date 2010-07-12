<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 1:42:35 AM
 * To change this template use File | Settings | File Templates.
 */


class Views_PageOptions_Index {
	function __construct() {
		global $post;
		$this->route_label = __("Exec Route", KWP_DOMAIN);
		$route = get_post_meta($post->ID, KWP_ROUTE, true);
		if (!empty($route)) {
			list($p_route, $p_placement) = explode('||', $route, 2);
			$this->route = $p_route;
			$this->placement = $p_placement or 'replace';
		}

		$this->output_label = __("Result Placement", KWP_DOMAIN);
		if ($this->placement == '')
			$this->placement = get_option('kwp_default_placement') or 'replace';
	}

	function nonce() {
		global $post;
		return wp_create_nonce($post->ID);
	}

	function kwp_placement_dropdown() {
		return Form::select('kwp[placement]',
			array('before' => 'Before Page Content', 'replace' => 'Replace Page Content', 'after' => 'After Page Content'),
			$this->placement
		);
	}
}
 
