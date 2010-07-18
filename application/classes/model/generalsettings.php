<?php
class Model_GeneralSettings extends Model_WP_Options {
	var $kwp_front_loader;
	var $kwp_default_placement;
	var $kwp_page_template;
	var $kwp_front_loader_in_nav;
	var $kwp_process_all_uri;
	var $kwp_applications_root;

	static function factory() {
		return new Model_GeneralSettings();
	}
}