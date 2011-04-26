<?php

/* * * * * * * * * * * * * * * * * * * * * * *
 *
 * "Ah" - PHP WebAPI Framework
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
 * sfEventDispatcher
 *     /lib/Vendor/sf/sfEvent.php
 *     /lib/Vendor/sf/sfEventDispatcher.php
 */

namespace Ah;

require_once(DIR_LIB.'/Ah/Autoloader.ah.php');
require_once(DIR_LIB . '/function.php');

$Loader = new Autoloader();
$Loader->register(array($Loader, 'ahLoad'), true);
$Loader->register(array($Loader, 'sfLoad'), true);
$Loader->register(array($Loader, 'terminate'), true);

/**
 * Ah_Application
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
abstract class Application
{
    /**
     * initialize
     *
     * @param bool|callable $isDebug
     * @return void
     */
    public static function initialize($isDebug = false)
    {
        // #EVENT startup
        Event\Helper::getDispatcher()->notify(new Event\Subject(null, 'app.startup'));

        // output buffering
        ob_start();
        ob_implicit_flush(false);

        // set internal encoding
        mb_internal_encoding('UTF-8');

        // set charset & encoding directives
        ini_set('default_charset', 'UTF-8');
        ini_set('mbstring.script_encoding', 'UTF-8');
        ini_set('mbstring.substitute_character', '?');
        ini_set('mbstring.http_input' , 'UTF-8');
        ini_set('mbstring.http_output' , 'pass');
        ini_set('arg_separator.output', '&');

        // initialize error report
        if ( is_callable($isDebug) ) {
            $isDebug();
        } else if ( !!$isDebug ) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', './error_log');

            Debug\Manager::ready();
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
            $stacks = array_splice($stacks, 1);
            Event\Helper::getDispatcher()->notify(new Event\Subject(array($errno, $errstr, $errfile, $errline, $stacks), 'error.regular'));
        }, E_ALL);

        // #EVENT shutdown
        register_shutdown_function(function()
        {
            Event\Helper::getDispatcher()->notify(new Event\Subject(null, 'app.shutdown'));
        });
    }
}