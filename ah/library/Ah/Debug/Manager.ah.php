<?php
/**
 * Ah_Debug_Debug_Manager
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Debug_Manager
{
    public static function ready()
    {
        Ah_Event_Helper::getDispatcher()->listen('error.regular', array('Ah_Debug_ErrorTrace', 'regular'));
        Ah_Event_Helper::getDispatcher()->listen('app.shutdown', array('Ah_Debug_ErrorTrace', 'fatal'));
    }
}