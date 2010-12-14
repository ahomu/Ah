<?php

/* * * * * * * * * * * * * * * * * * * * * * *
 *
 * "Ah" - Action Highway / PHP Application Framework
 *
 * * * * * * * * * * * * * * * * * * * * * * *
 *
 Dependencies Libraries
 *
 * Services_JSON
 *     /lib/Class/Services_JSON.class.php
 * Spyc
 *     /lib/Class/Spyc.class.php
 * Template
 *     /lib/Class/Template.class.php
 */

define('DIR_ROOT', dirname(__FILE__));

//require_once(DIR_ROOT.'/lib/Class/Twig/Autoloader.php');
require_once(DIR_ROOT.'/lib/Ah/Autoloader.ah.php');
require_once(DIR_ROOT.'/lib/Function.php');

//Ah_Autoloader::register(array('Twig_Autoloader', 'autoload'), true);
Ah_Autoloader::register(array('Ah_Autoloader', 'load'), true);

/**
 * Ah_Application
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
abstract class Ah_Application
{
    public static function initialize($isDebug)
    {
        // is DEBUG?
        define('DEBUG_MODE', $isDebug);

        // initialize error report
        if ( !!DEBUG_MODE ) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', './error_log');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', './error_log');
        }

        // set internal encoding
        mb_internal_encoding('UTF-8');

        // set mb encoding directives
        ini_set('mbstring.script_encoding', 'UTF-8');
        ini_set('mbstring.substitute_character', '?');
        ini_set('mbstring.http_input' , 'UTF-8');
        ini_set('mbstring.http_output' , 'pass');

        // anti "&amp;" for http_bulid_query()
        ini_set('arg_separator.output', '&');

        // define constants
        define('REQUEST_HOST', !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
        define('REQUEST_URI', $_SERVER['REQUEST_URI']);
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);

        define('IP_SERVER', $_SERVER['SERVER_ADDR']);
        define('IP_CLIENT', $_SERVER['REMOTE_ADDR']);

        define('ENABLE_GZIP', !!(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false));
        define('ENABLE_SSL',  !!( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'));

        define('DIR_ACT',  DIR_ROOT.'/act');
        define('DIR_LIB',  DIR_ROOT.'/lib');
        define('DIR_JS',   DIR_ROOT.'/js');
        define('DIR_LOG',  DIR_ROOT.'/log');
        define('DIR_TMP',  DIR_ROOT.'/tmp');
        define('DIR_TPL',  DIR_ROOT.'/tpl');
        define('DIR_YML',  DIR_ROOT.'/yml');

        // anti "magic_quotes_gpc" directive [@ref http://pentan.info/php/magic_quotes_on.html]
        if ( get_magic_quotes_gpc() ) {
            $_GET     = array_walk_recursive($_GET, 'stripslashes');
            $_POST    = array_walk_recursive($_POST, 'stripslashes');
            $_REQUEST = array_walk_recursive($_REQUEST, 'stripslashes');
            $_COOKIE  = array_walk_recursive($_COOKIE, 'stripslashes');
        }
    }
}
