<?php

namespace Ah\Debug;

use Ah,
    Ah\Event;

/**
 * Ah\Debug\Manager
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

        if ( $Config = Ah\Config::load('debug') )
        {
            if ( $Config['ErrorTracer']['enable'] === 'true' )
            {
                Event\Helper::getDispatcher()->connect('error.regular', array('Ah\Debug\Tracer', 'regularError'));
            }
        }

        Event\Helper::getDispatcher()->connect('response.send_before', array('Ah\Debug\Renderer', 'addOb'));
        Event\Helper::getDispatcher()->connect('response.send_before', array('Ah\Debug\Renderer', 'dump'));
    }
}