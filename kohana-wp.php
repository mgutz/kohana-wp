<?php
/**
 * Plugin Name: Kohana-WP
 * Plugin URI: http://www.mgutz.com/kohana-wp
 * Description: Enables the integration of Kohana PHP Applications with Wordpress
 * Author: Mario L Gutierrez
 * Version: 0.1
 * Author URI: http://www.mgutz.com
 */

require 'application/classes/kwp/plugin.php';

KWP_Plugin::factory()->run();