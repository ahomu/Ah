<?php

namespace ah\debug;

use ah\Config,
    ah\Params,
    ah\event;

/**
 * ah\debug\Manager
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Manager
{
    public static $tools = array('Tracer', 'Profiler');

    /**
     * ready
     *
     * @return void
     */
    public static function ready()
    {
        header('Cache-Control: private');

        $Config = new Params(self::$tools, Config::load('debug'));

        if ( $Config->get('Tracer') === 'true' ) {
            event\Helper::getDispatcher()->connect('error.regular', array('ah\debug\Tracer', 'regularError'));
        }

        if ( $Config->get('Profiler') === 'true' ) {
            event\Helper::getDispatcher()->connect('resolver.action_before', array('ah\debug\Profiler', 'timerStart'));
            event\Helper::getDispatcher()->connect('resolver.action_after', array('ah\debug\Profiler', 'timerEnd'));
            event\Helper::getDispatcher()->connect('response.send_before', array('ah\debug\Profiler', 'finish'));
        }

        event\Helper::getDispatcher()->connect('response.send_before', array('ah\debug\Renderer', 'addOb'));
        event\Helper::getDispatcher()->connect('response.send_before', array('ah\debug\Renderer', 'dump'));
    }
}