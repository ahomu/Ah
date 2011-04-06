<?php

define('DIR_ROOT', dirname(__FILE__));
define('DIR_LIB',  DIR_ROOT.'/lib');
define('DIR_ACT',  DIR_ROOT.'/app/action');
define('DIR_TMP',  DIR_ROOT.'/app/cache');
define('DIR_TPL',  DIR_ROOT.'/app/template');
define('DIR_YML',  DIR_ROOT.'/app/config');

require_once('./lib/bootstrap.php');

class MyApp extends Ah_Application
{
    /**
     * boot
     *
     * @param boolean $isDebug
     * @see Ah_Application::initialize()
     * @return void
     */
    public static function boot($isDebug)
    {
        parent::initialize($isDebug);

        $path   = Ah_Request::getPath();
        $method = Ah_Request::getMethod();

        Ah_Resolver::external($path, $method);
    }
}

MyApp::boot(true);