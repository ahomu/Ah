<?php

namespace ah\event;

/**
 * ah\event\Helper
 *
 * @package     Ah
 * @subpackage  Event
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Helper
{
    private static $_dispatcher;

    /**
     * getDispatcher
     *
     * @return \Ah\Event\Dispatcher
     */
    public static function getDispatcher()
    {
        if ( !(self::$_dispatcher instanceof Dispatcher) ) {
            self::$_dispatcher = new Dispatcher();
        }
        return self::$_dispatcher;
    }
}
