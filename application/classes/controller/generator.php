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
		$settings = Model_GeneralSettings::factory()->first();

        $this->app = $_POST['app'];
        $app = $this->app['name'];
        $title = $this->app['test_page'];
		$page_template = $this->app['page_template'];
        if (empty($title)) {
            $title = Inflector::humanize("Test $app");
        }


		$app_root = KWP::slash($settings->kwp_applications_root) . $app;
		// verify path does not already exist
        if (realpath($app_root) !== false) {
            $this->add_flash_notice('Application already exists: ' . $app_root, 'error');
            $this->render('controlpanel/generator');
            return;
        }

        // copy the application template to the installation directory
        Helper_KWP::cp_r(KWP_DOCROOT . 'template/app/simple', $app_root);

        // create a test page for it        
        $test_page = array(
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => null,
			'comment_status' => 'closed'
		);
        $page_id = wp_insert_post($test_page);
        
        // add metadata for Kohana-WP
        Helper_KWP::add_update_post_meta($page_id, KWP_ROUTE, "$app/welcome||replace");

		// add
		Helper_KWP::add_update_post_meta($page_id, '_wp_page_template', "$page_template");

        $view_page_link = get_permalink($page_id);
        
        $this->add_flash_notice("Your application has been generated. <a href='$view_page_link'>View $title</a>");
        $this->render('controlpanel/generator');
        return;
	}
}