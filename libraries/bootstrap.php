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
 *     /libraries/Common/Services_JSON.class.php
 * Spyc
 *     /libraries/Common/Spyc.class.php
 * Template
 *     /libraries/Common/Template.class.php
 * sfEventDispatcher
 *     /libraries/Vendor/sf/sfEvent.php
 *     /libraries/Vendor/sf/sfEventDispatcher.php
 */

namespace ah;

use ah\event;

/**
 * ah\Application
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
        event\Helper::getDispatcher()->notify(new event\Subject(null, 'app.startup'));

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

            debug\Manager::ready();
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
            event\Helper::getDispatcher()->notify(new event\Subject(array($errno, $errstr, $errfile, $errline, $stacks), 'error.regular'));
        }, E_ALL);

        // #EVENT shutdown
        register_shutdown_function(function()
        {
            event\Helper::getDispatcher()->notify(new event\Subject(null, 'app.shutdown'));
        });
    }
}
