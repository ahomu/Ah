<?php

/* * * * * * * * * * * * * * * * * * * * * * *
 *
 * "Ah" - PHP Application Framework
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
define('DIR_ACT',  DIR_ROOT.'/action');
define('DIR_LIB',  DIR_ROOT.'/library');
define('DIR_JS',   DIR_ROOT.'/js');
define('DIR_LOG',  DIR_ROOT.'/log');
define('DIR_TMP',  DIR_ROOT.'/cache');
define('DIR_TPL',  DIR_ROOT.'/template');
define('DIR_YML',  DIR_ROOT.'/config');

require_once(DIR_ROOT.'/library/Ah/Autoloader.ah.php');
require_once(DIR_ROOT.'/library/Function.php');

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
    public static function initialize($isDebug = false)
    {
        // #EVENT startup
        Ah_Event_Helper::getDispatcher()->notify(null, 'app.startup');

        // is DEBUG?

        // output buffering
        ob_start();
        ob_implicit_flush(false);

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

        define('IP_CLIENT', $_SERVER['REMOTE_ADDR']);
        define('IP_SERVER', $_SERVER['SERVER_ADDR']);

        define('ENABLE_DEBUG', $isDebug);
        define('ENABLE_GZIP', !!( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false));
        define('ENABLE_SSL',  !!( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'));

        // anti "magic_quotes_gpc" directive [@ref http://pentan.info/php/magic_quotes_on.html]
        if ( get_magic_quotes_gpc() ) {
            $_GET     = array_walk_recursive($_GET, 'stripslashes');
            $_POST    = array_walk_recursive($_POST, 'stripslashes');
            $_REQUEST = array_walk_recursive($_REQUEST, 'stripslashes');
            $_COOKIE  = array_walk_recursive($_COOKIE, 'stripslashes');
        }

        // initialize error report
        if ( !!ENABLE_DEBUG ) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', './error_log');

            Ah_Debug_Manager::ready();
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', './error_log');
        }

        // #EVENT error
        set_error_handler(function($errno, $errstr, $errfile, $errline)
        {
            $stacks = debug_backtrace();
            Ah_Event_Helper::getDispatcher()->notify(array($errno, $errstr, $errfile, $errline, $stacks), 'error.regular');
        }, E_ALL);

        // #EVENT shutdown
        register_shutdown_function(function()
        {
            Ah_Event_Helper::getDispatcher()->notify(null, 'app.shutdown');
        });
    }
}
