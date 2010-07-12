<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 9:00:57 PM
 * To change this template use File | Settings | File Templates.
 */

class Views_ControlPanel_Generator {
	function page_templates() {
		ob_start();
		page_template_dropdown(get_option('kwp_page_template'));
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}
