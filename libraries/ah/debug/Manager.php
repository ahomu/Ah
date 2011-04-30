<?php

namespace ah\debug;

use ah\Config,
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
    /**
     * ready
     *
     * @return void
     */
    public static function ready()
    {
        header('Cache-Control: private');

        if ( $Config = Config::load('debug') )
        {
            if ( $Config['ErrorTracer']['enable'] === 'true' )
            {
                event\Helper::getDispatcher()->connect('error.regular', array('ah\debug\Tracer', 'regularError'));
            }
        }

        event\Helper::getDispatcher()->connect('response.send_before', array('ah\debug\Renderer', 'addOb'));
        event\Helper::getDispatcher()->connect('response.send_before', array('ah\debug\Renderer', 'dump'));
    }
}