<?php

use ah\Application,
    ah\Request,
    ah\Resolver;

define('DIR_ROOT', dirname(__FILE__));
define('DIR_LIB',  DIR_ROOT.'/libraries');

define('DIR_TMP',  DIR_ROOT.'/app/cache');
define('DIR_YML',  DIR_ROOT.'/app/config');
define('DIR_ACT',  DIR_ROOT.'/app/libraries/action');
define('DIR_TPL',  DIR_ROOT.'/app/template');

require_once('./libraries/bootstrap.php');

class MyApp extends Application
{
    /**
     * boot
     *
     * @param boolean $isDebug
     * @see ah\Application::initialize()
     * @return void
     */
    public static function boot($isDebug)
    {
        parent::initialize($isDebug);

        $path   = Request::getPath();
        $method = Request::getMethod();

        Resolver::external($path, $method);
    }
}

MyApp::boot(true);