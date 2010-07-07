<?php
/**
 * Created by PhpStorm.
 * User: mgutz
 * Date: Jul 7, 2010
 * Time: 12:39:36 PM
 * To change this template use File | Settings | File Templates.
 */


class View_Mustache extends Kostache {
	// must use triple braces {{{ }}} on urls so they are not escaped
	public $app_url = KWP_APP_URL;
	public $controller_url = KWP_CONTROLLER_URL;
}
