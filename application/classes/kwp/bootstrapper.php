<?php defined('KWP_DOCROOT') or die('No direct script access.');

final class KWP_Bootstrapper {

	/**
	 * Bootstraps Kohana system from a route.
	 * @static
	 * @param  $route application/controller(/action(/args(...(/argn)))
	 * @return void
	 */
	static function boot($route) {
		static $bootstrapped = false;
		
		if ($bootstrapped) return;

		$bootstrapped = true;
		$strapper = new KWP_Bootstrapper();
		$app_root = $strapper->app_specific_setup($route);
		$strapper->load_kohana($app_root);
	}



	private function app_specific_setup($route) {
		list($app_name, $controller, $rest) = explode('/', $route, 3);

		$app_root = KOHANA_APPS_ROOT . $app_name;
		$controller_path = "$app_root/application/classes/controller/$controller.php";
		if (!is_file($controller_path)) {
			return "<span style='color:red; font-weight:bold'>Invalid Kohana route:<br />route => <code>$app/$controller</code><br/>path not found => $controller_path<code></code> </span>";
		}
 
		// define constants for URL helpers
		$page_url = $this->page_url();

		// get rid of existing kr since any outgoing URL will be rebuilt (will keep appending otherwise)
		$page_url = preg_replace('/(&|\?)kr=.*/i', '', $page_url);

		$prefix = strpos($page_url, '?') ? '&kr=' : '?kr=';
		define('KWP_PAGE_URL', $page_url . $prefix);
		define('KWP_APP_URL', KWP_PAGE_URL . $app_name);
		define('KWP_CONTROLLER_URL', KWP_APP_URL . '/' . $controller);

		return $app_root;
	}

	private function load_kohana($docroot) {
		// use Kohana-WP's default system if application does not provide it
		if (is_file($docroot.'/system/classes/kohana/core.php')) {
			$system = 'system';
		}
		else {
			$system = KWP_DOCROOT.'system';
		}

		$this->index($docroot, 'application', 'modules', $system);
		$this->bootstrap();
	}

	/**
	 * recreate Kohana 3.0 index.php file with some modifications
	 */
	private function index($docroot, $app_dir = 'application', $mod_dir = 'modules', $sys_dir = 'system') {
		/**
		 * The directory in which your application specific resources are located.
		 * The application directory must contain the bootstrap.php file.
		 *
		 * @see  http://kohanaframework.org/guide/about.install#application
		 */
		$application = $app_dir;

		/**
		 * The directory in which your modules are located.
		 *
		 * @see  http://kohanaframework.org/guide/about.install#modules
		 */
		$modules = $mod_dir;

		/**
		 * The directory in which the Kohana resources are located. The system
		 * directory must contain the classes/kohana.php file.
		 *
		 * @see  http://kohanaframework.org/guide/about.install#system
		 */
		$system = $sys_dir;

		/**
		 * The default extension of resource files. If you change this, all resources
		 * must be renamed to use the new extension.
		 *
		 * @see  http://kohanaframework.org/guide/about.install#ext
		 */
		define('EXT', '.php');

		/**
		 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
		 * @see  http://php.net/error_reporting
		 *
		 * When developing your application, it is highly recommended to enable notices
		 * and strict warnings. Enable them by using: E_ALL | E_STRICT
		 *
		 * In a production environment, it is safe to ignore notices and strict warnings.
		 * Disable them by using: E_ALL ^ E_NOTICE
		 *
		 * When using a legacy application with PHP >= 5.3, it is recommended to disable
		 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
		 */
		//error_reporting(E_ALL | E_STRICT);

		/**
		 * End of standard configuration! Changing any of the code below should only be
		 * attempted by those with a working knowledge of Kohana internals.
		 *
		 * @see  http://kohanaframework.org/guide/using.configuration
		 */

		// Set the full path to the docroot
		define('DOCROOT', realpath($docroot).DIRECTORY_SEPARATOR);

		// Make the application relative to the docroot
		if ( ! is_dir($application) AND is_dir(DOCROOT.$application))
			$application = DOCROOT.$application;

		// Make the modules relative to the docroot
		if ( ! is_dir($modules) AND is_dir(DOCROOT.$modules))
			$modules = DOCROOT.$modules;

		// Make the system relative to the docroot
		if ( ! is_dir($system) AND is_dir(DOCROOT.$system))
			$system = DOCROOT.$system;

		// Define the absolute paths for configured directories
		define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
		define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
		define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

// mgutz: low-level functions define __() which WordPress alredy defines, so do not load it
//		// Clean up the configuration vars
//		unset($application, $modules, $system);
//
//		if (file_exists('install'.EXT))
//		{
//			// Load the installation check
//			return include 'install'.EXT;
//		}
//
//		// Load the base, low-level functions
//		require SYSPATH.'base'.EXT;

		if ( ! defined('KOHANA_START_TIME'))
		{
			/**
			 * Define the start time of the application, used for profiling.
			 */
			define('KOHANA_START_TIME', microtime(TRUE));
		}

		if ( ! defined('KOHANA_START_MEMORY'))
		{
			/**
			 * Define the memory usage at the start of the application, used for profiling.
			 */
			define('KOHANA_START_MEMORY', memory_get_usage());
		}

		// Load the core Kohana class
		require SYSPATH.'classes/kohana/core'.EXT;


		if (is_file(APPPATH.'classes/kohana'.EXT))
		{
			// Application extends the core
			require APPPATH.'classes/kohana'.EXT;
		}
		else
		{
			// Load empty core extension
			require SYSPATH.'classes/kohana'.EXT;
		}
	}



	# TODO: Should bootstrap path be unique to application?
	#$custom_bootstrap = get_option('kwp_bootstrap_path');
	#if ($custom_bootstrap !== false) {
	#	include_once $custom_bootstrap;
	#} else {
	#	$kohana->bootstrap();
	#}



	/**
	 * Recreate the Kohana application/bootstrap.php process with some modifications
	 *
	 * @static
	 * @return void
	 */
	private function bootstrap() {
		//-- Environment setup --------------------------------------------------------

		/**
		 * Set the default time zone.
		 *
		 * @see  http://kohanaframework.org/guide/using.configuration
		 * @see  http://php.net/timezones
		 */
		date_default_timezone_set('America/Chicago');

		/**
		 * Set the default locale.
		 *
		 * @see  http://kohanaframework.org/guide/using.configuration
		 * @see  http://php.net/setlocale
		 */
		setlocale(LC_ALL, 'en_US.utf-8');

		/**
		 * Enable the Kohana auto-loader.
		 *
		 * @see  http://kohanaframework.org/guide/using.autoloading
		 * @see  http://php.net/spl_autoload_register
		 */
		spl_autoload_register(array('Kohana', 'auto_load'));

		/**
		 * Enable the Kohana auto-loader for unserialization.
		 *
		 * @see  http://php.net/spl_autoload_call
		 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
		 */
		ini_set('unserialize_callback_func', 'spl_autoload_call');

		//-- Configuration and initialization -----------------------------------------

		/**
		 * Initialize Kohana, setting the default options.
		 *
		 * The following options are available:
		 *
		 * - string   base_url    path, and optionally domain, of your application   NULL
		 * - string   index_file  name of your index file, usually "index.php"       index.php
		 * - string   charset     internal character set used for input and output   utf-8
		 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
		 * - boolean  errors      enable or disable error handling                   TRUE
		 * - boolean  profile     enable or disable internal profiling               TRUE
		 * - boolean  caching     enable or disable internal caching                 FALSE
		 */
		Kohana::init(array(
			'charset' => 'utf-8',
			'base_url'   => '/',
			'index_file' => FALSE,
		));

		/**
		 * Attach the file write to logging. Multiple writers are supported.
		 */
		Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));

		/**
		 * Attach a file reader to config. Multiple readers are supported.
		 */
		Kohana::$config->attach(new Kohana_Config_File);

		$modules = $this->get_combined_modules(array(
			WP_PLUGIN_DIR . '/kohana-wp/modules',
			MODPATH
		));

		Kohana::modules($modules);

//		/**
//		 * Enable modules. Modules are referenced by a relative or absolute path.
//		 */
//		Kohana::modules(array(
//			// 'auth'       => MODPATH.'auth',       // Basic authentication
//			// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
//			// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
//			// 'database'   => MODPATH.'database',   // Database access
//			// 'image'      => MODPATH.'image',      // Image manipulation
//			// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
//			// 'pagination' => MODPATH.'pagination', // Paging of results
//			// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
//			));

		/**
		 * Set the routes. Each route must have a minimum of a name, a URI and a set of
		 * defaults for the URI.
		 */
		Route::set('default', '(<app>/<controller>(/<action>(/<id>)))')
			->defaults(array(
				'controller' => 'welcome',
				'action'     => 'index',
			));

//		/**
//		 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
//		 * If no source is specified, the URI will be automatically detected.
//		 */
//		echo Request::instance()
//			->execute()
//			->send_headers()
//			->response;
	}


	/**
	 * Register Kohana modules.
	 *
	 * All modules located inside an applications modules/ folder are registered unless the module module
	 * directory is suffixed with '.off'.
	 */
	private function get_combined_modules($dirs) {
		foreach ($dirs as $dir) {
			$mod = $this->get_dir_names($dir);
			foreach ($mod as $name => $path) {
				if (substr($name, -4) != '.off') {
					$modules[$name] = $path;
				}
			}
		}

		return $modules;
	}

	/**
	 * Gets valid directory names from a path.
	 * @param  $path The path.
	 * @return array
	 */
	private function get_dir_names($path) {
		$dirs = array();
		if (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (false !== ($dir = readdir($handle))) {
					if ($dir == "." || $dir == "..")
						continue;

					$full_path = "$path/$dir";
					if (is_dir($full_path)) {
						$dirs[$dir] = $full_path;
					}
				}
				closedir($handle);
			}
		}
		
		return $dirs;
	}

	private function page_url() {
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

}