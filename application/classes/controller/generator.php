<?php
/**
 * Generates starter code blocks for Kohana-WP.
 * User: mgutz
 * Date: Jul 11, 2010
 * Time: 10:15:39 PM
 */

class Controller_Generator extends Controller_ControlPanel {
    var $app = array();
    
	function action_index() {
		$this->render('controlpanel/generator');
	}

	function action_generate_app() {
        $this->app = $_POST['app'];
        $app = $this->app['name'];
        $title = $this->app['test_page'];
        if (empty($title)) {
            $title = Inflector::humanize("Test $app");
        }
        
        // TODO: add this as an option for the plugin settings
        define('KWP_USER_APPS_ROOT', WP_CONTENT_DIR . '/kohana/sites/all/');
        if (!is_dir(KWP_USER_APPS_ROOT)) {
            Helper_KWP::mkdir_p(KWP_USER_APPS_ROOT);
        }

        
        $docroot = realpath(KWP_USER_APPS_ROOT . $app);
		// verify path does not already exist
        if ($docroot !== false) {
            $this->add_flash_notice('Application already exists: ' . realpath($docroot), 'error');
            $this->render('controlpanel/generator');
            return;
        }

        // copy the application template to the installation directory
        Helper_KWP::cp_r(KWP_DOCROOT . 'template/app/simple', KWP_USER_APPS_ROOT . $app);

        // create a test page for it        
        $test_page = array(
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => null,
			'comment_status' => 'closed',
			'menu_order' => 100 // ensure is last
		);
        $page_id = wp_insert_post($test_page);
        
        // add metadata for Kohana-WP
        Helper_KWP::add_update_post_meta($page_id, KWP_ROUTE, "$app/welcome||replace");
        
        $view_page_link = get_permalink($page_id);
        
        $this->add_flash_notice("Your application has been generated. <a href='$view_page_link'>View $title</a>");
        $this->render('controlpanel/generator');
        return;
	}
}