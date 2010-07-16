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
            $title = "$app Test";
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
        $test_page = array();
        $test_page['post_title'] = "Test $app";
        $test_page['post_content'] = '';
        $test_page['post_status'] = 'publish';
        $test_page['post_type'] = 'page';
        $test_page['post_parent'] = null;

        // insert the new page
        $page_id = wp_insert_post($test_page);
        
        // add metadata for Kohana-WP
        Helper_KWP::add_update_post_meta($page_id, KWP_ROUTE, "$app/welcome||replace");
        
        $view_link = get_permalink($page_id);
        
        $this->add_flash_notice("Your application has been created. <a href='$view_link'>View $app Test Page.</a>");
        $this->render('controlpanel/generator');
        return;
	}
}