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
    /**
     * ready
     *
     * @return void
     */
    public static function ready()
    {
        header('Cache-Control: private');

        if ( $Config = Ah_Config::load('debug') )
        {
            if ( $Config['ErrorTracer']['enable'] === 'true' )
            {
                Ah_Event_Helper::getDispatcher()->listen('error.regular', array('Ah_Debug_Tracer', 'regularError'));
            }
        }

        Ah_Event_Helper::getDispatcher()->listen('response.send_before', array('Ah_Debug_Renderer', 'addOb'));
        Ah_Event_Helper::getDispatcher()->listen('response.send_before', array('Ah_Debug_Renderer', 'dump'));
    }
}