<?php
class Model_GeneralSettings extends Model_WP_Options {
	var $kwp_default_placement;
	var $kwp_page_template;
	var $kwp_front_loader_in_nav;
	var $kwp_process_all_uri;

	static function obj() {
		return new Model_GeneralSettings();
	}
}