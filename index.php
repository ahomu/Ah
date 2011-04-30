<?php

use ah\Autoloader,
    ah\Application,
    ah\Request,
    ah\Resolver;

define('DIR_ROOT',dirname(__FILE__));
define('DIR_LIB', DIR_ROOT.'/libraries');
define('DIR_APP', DIR_ROOT.'/app');

define('DIR_TMP', DIR_APP.'/cache');
define('DIR_YML', DIR_APP.'/config');
define('DIR_TPL', DIR_APP.'/template');

require_once(DIR_LIB . '/ah/Autoloader.php');
require_once(DIR_LIB . '/function.php');

$Loader = new Autoloader();
$Loader->register(array($Loader, 'ahLoad'), true);
$Loader->register(array($Loader, 'sfLoad'), true);
$Loader->register(array($Loader, 'terminate'), true);

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