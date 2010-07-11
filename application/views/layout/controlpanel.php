<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 11:34:07 AM
 * To change this template use File | Settings | File Templates.
 */

class Views_Layout_ControlPanel {
	function nav_list() {
		
		// tab_pages assigned as a property via pipe rendering from context Controller_ControlPanel
		foreach ($this->tab_pages as $page) {
			$navs[] = array(
				'class' => KWP_Plugin::globals('current_action') == $page['action'] ? 'active' : '',
				'href' => $this->controller_url . '/' . $page['action'],
				'caption' => $page['caption']
			);
		}
		
		return $navs;
	}
}


 
