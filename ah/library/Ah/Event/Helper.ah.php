<?php
/**
 * Ah_Event_Helper
 *
 * @package     Ah
 * @subpackage  Event
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Event_Helper
{
    private static $_dispatcher;

    /**
     * getDispatcher
     *
     * @return $object Ah_Event_Dispatcher
     */
    public static function getDispatcher()
    {
        if ( !(self::$_dispatcher instanceof Ah_Event_Dispatcher) ) {
            self::$_dispatcher = new Ah_Event_Dispatcher();
        }
        return self::$_dispatcher;
    }
}
