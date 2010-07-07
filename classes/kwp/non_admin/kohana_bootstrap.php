<?php

class KohanaBootstrapper {
	/**
	 * recreate Kohana 3.0 index.php file with some modifications
	 */
	function index() {
		/**
		 * The directory in which your modules are located.
		 *
		 * @see  http://docs.kohanaphp.com/install#modules
		 */
		$modules = get_option('kwp_module_path');
		if (!is_dir($modules)) {
			$modules = KOHANA_ROOT . "framework/modules/";
			error_log("kwp_module_path option is an invalid directory: $modules");
		}

		/**
		 * The directory in which the Kohana resources are located. The system
		 * directory must contain the classes/kohana.php file.
		 *
		 * @see  http://docs.kohanaphp.com/install#system
		 */
		$system = get_option('kwp_system_path');
		if (!is_dir($system)) {
			$system = KOHANA_ROOT . "framework/current/system/";
			error_log("kwp_system_path option is an invalid directory: $system");
		}

		/**
		 * The default extension of resource files. If you change this, all resources
		 * must be renamed to use the new extension.
		 *
		 * @see  http://docs.kohanaphp.com/install#ext
		 */
		define('EXT', get_option('kwp_ext'));

		/**
		 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
		 * @see  http://php.net/error_reporting
		 *
		 * When developing your application, it is highly recommended to enable notices
		 * and strict warnings. Enable them by using: E_ALL | E_STRICT
		 *
		 * In a production environment, it is safe to ignore notices and strict warnings.
		 * Disable them by using: E_ALL ^ E_NOTICE
		 */
		//error_reporting(E_ALL | E_STRICT);

		/**
		 * End of standard configuration! Changing any of the code below should only be
		 * attempted by those with a working knowledge of Kohana internals.
		 *
		 * @see  http://docs.kohanaphp.com/bootstrap
		 */

		// Set the full path to the docroot
		define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

		// Make the modules relative to the docroot
		if (!is_dir($modules) AND is_dir(DOCROOT . $modules))
			$modules = DOCROOT . $modules;

		// Make the system relative to the docroot
		if (!is_dir($system) AND is_dir(DOCROOT . $system))
			$system = DOCROOT . $system;

		// Define the absolute paths for configured directories
		define('MODPATH', realpath($modules) . DIRECTORY_SEPARATOR);
		define('SYSPATH', realpath($system) . DIRECTORY_SEPARATOR);


		// Clean up the configuration vars
		unset($modules, $system);

		// Define the start time of the application
		define('KOHANA_START_TIME', microtime(TRUE));

		// Load the base, low-level functions	***** Not including base.php
		// require SYSPATH.'base'.EXT;

		// Load the core Kohana class			***** Include Kohana class from path defined in kohana settings
		require SYSPATH . 'classes/kohana/core' . EXT;

		# TODO the application path is not known at this time. In the future, we might our system core classes here
		#if (is_file(get_option('kwp_application_path') . 'classes/kohana' . get_option('kwp_ext'))) {
		#    // Application extends the core
		#    require get_option('kwp_application_path') . 'classes/kohana' . get_option('kwp_ext');
		#}
		#else {
			// Load empty core extension
			require SYSPATH . 'classes/kohana' . EXT;
		#}
	}

	/**
	 * Recreate the Kohana application/bootstrap.php process with some modifications
	 *
	 * @static
	 * @return void
	 */
	function bootstrap() {
		//-- Environment setup --------------------------------------------------------

		/**
		 * Set the default time zone.
		 *
		 * @see  http://docs.kohanaphp.com/features/localization#time
		 * @see  http://php.net/timezones
		 */
		date_default_timezone_set(get_option('timezone_string'));

		/**
		 * Enable the Kohana auto-loader.
		 *
		 * @see  http://docs.kohanaphp.com/features/autoloading
		 * @see  http://php.net/spl_autoload_register
		 */
		spl_autoload_register(array('Kohana', 'auto_load'));

		/**
		 * Enable Kohana exception handling, adds stack traces and error source.
		 *
		 * @see  http://docs.kohanaphp.com/features/exceptions
		 * @see  http://php.net/set_exception_handler
		 */
		set_exception_handler(array('Kohana', 'exception_handler'));

		/**
		 * Enable Kohana error handling, converts all PHP errors to exceptions.
		 *
		 * @see  http://docs.kohanaphp.com/features/exceptions
		 * @see  http://php.net/set_error_handler
		 */
		set_error_handler(array('Kohana', 'error_handler'));

		//-- Kohana configuration -----------------------------------------------------

		/**
		 * Initialize Kohana, setting the default options.
		 *
		 * The following options are available:
		 * - base_url:   path, and optionally domain, of your application
		 * - index_file: name of your index file, usually "index.php"
		 * - charset:    internal character set used for input and output
		 * - profile:    enable or disable internal profiling
		 * - caching:    enable or disable internal caching
		 */

		$kohana_base_url = str_replace(get_option('home'),'',get_option('siteurl') );
		if (!$kohana_base_url) {
			$kohana_base_url = '/';
		}
		Kohana::init(array('charset' => 'utf-8', 'base_url' => $kohana_base_url ));

		$this->register_modules();


		/**
		* Attach the file write to logging. Multiple writers are supported.
		*/
		Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));

		/**
		* Attach a file reader to config. Multiple readers are supported.
		*/
		Kohana::$config->attach(new Kohana_Config_File);

		/**
		 * Set the routes. Each route must have a minimum of a name, a URI and a set of
		 * defaults for the URI.
		 */
		Route::set('default', '(<controller>(/<action>(/<id>)))')
			->defaults(array(
				'controller' => get_option('kwp_default_controller'),
				'action' => get_option('kwp_default_action'),
				'id' => get_option('kwp_default_id')));
	}


	/**
	 * Register Kohana modules. Application modules have higher
	 * precedence than global modules. KWP may also be overriden.
	 *
	 * Any module found beneath an application folder are registered.
	 *
	 * Application modules installed: WORDPRESS_ROOT/wp-content/kohana/sites/all/<application>/modules
	 * Global modules instaled: WORDPRESS_ROOT/wp-content/kohana/modules
	 *
	 * TODO: Sholdn't the framework be isolated as well?
	 */
	function register_modules() {
		// Add KWP modules, any module beneat kohana-wp/modules will be installed, unless the name ends with '.off'!
		$kwp_modules = $this->get_dir_names(WP_PLUGIN_DIR . '/kohana-wp/modules');
		foreach ($kwp_modules as $name => $path) {
			if (substr($name, -4) != '.off') {
				$modules[$name] = $path;
			}
		}

		// Add global modules if not defined the application
		$selectable_modules = explode(',', get_option('kwp_modules') );
		foreach ($selectable_modules as $m) {
			$modules[trim($m)] = MODPATH.trim($m);
		}

		// Add application modules. Any module in an application's modules path will be installed!
		$app_modules = $this->get_dir_names(APPPATH . 'modules');
		foreach ($app_modules as $name => $path) {
			if (substr($name, -4) != '.off') {
				$modules[$name] = $path;
			}
		}

		Kohana::modules($modules);
	}

	/**
	 * Gets valid directory names from a path.
	 * @param  $path The path.
	 * @return array
	 */
	function get_dir_names($path) {
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
}



