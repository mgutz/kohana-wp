<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 10, 2010
 * Time: 2:49:34 AM
 * To change this template use File | Settings | File Templates.
 */
 
class Views_ControlPanel_Routes {
	public $routes_active = 'active';

	/**
	 * Gets all post routes.
	 */
	function routes() {
		$sql = <<<SQL
			SELECT
				pm.post_id,
				pm.meta_value as route,
				wp_posts.post_title
			FROM
				wp_postmeta pm
			INNER JOIN wp_posts ON pm.post_id = wp_posts.ID
			WHERE
				pm.meta_key = '_kwp_route'
			ORDER BY route;
SQL;
		global $wpdb;
		$rows = $wpdb->get_results($sql);

		foreach ($rows as $item) {
			ob_start();
			edit_post_link('edit', '', '', $item->post_id);
			$link = ob_get_contents();
			ob_end_clean();
			$item->edit_link = $link;
			list($p_route, $p_placement) = explode('||', $item->route);
			$item->route = $p_route;
		}
		
		return $rows;
	}
}
