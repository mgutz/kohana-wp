<?php 
#     /* 
#     Plugin Name: Kohana-WP 
#     Plugin URI: http://www.mgutz.com/kohana-wp
#     Description: Create WordPress pages, plugins, ... with Kohana 3 MVC
#     Author: Mario L Gutierrez
#     Version: 0.1
#     Author URI: http://www.mgutz.com
#     */



require_once 'application/classes/kwp/plugin.php';

$kwp = new KWP_Plugin();
$kwp->run();


