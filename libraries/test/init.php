<?php

use ah\Autoloader;

define('DIR_ROOT',realpath(dirname(__FILE__).'/../..'));
define('DIR_LIB', DIR_ROOT.'/libraries');
define('DIR_APP', DIR_ROOT.'/app');

define('DIR_TMP', DIR_LIB.'/test/mock/cache');
define('DIR_YML', DIR_LIB.'/test/mock/config');
define('DIR_TPL', DIR_LIB.'/test/mock/template');

define('PERM_WRITABLE', 0777);

require_once(DIR_LIB . '/ah/Autoloader.php');
require_once(DIR_LIB . '/function.php');

$_SERVER = array_merge($_SERVER, array(
    'APPLICATION_ENV' => 'unittest',
    'SERVER_NAME'     => 'localhost',
    'REQUEST_URI'     => '',
));

$Loader = new Autoloader();
$Loader->register(array($Loader, 'ahLoad'), true);
$Loader->register(array($Loader, 'sfLoad'), true);
$Loader->register(array($Loader, 'terminate'), true);
