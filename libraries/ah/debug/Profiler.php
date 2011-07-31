<?php

namespace ah\debug;

/**
 * ah\debug\Profiler
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Profiler
{
    public static $timerStack   = array();
    public static $resultStack  = array();

    public static function timerStart(\sfEvent $Evt)
    {
        self::$timerStack[] = self::_now();
    }

    public static function timerEnd(\sfEvent $Evt)
    {
        $start    = array_shift(self::$timerStack);
        $end      = self::_now();
        self::$resultStack[strval($Evt->getSubject())] = round(($end - $start), 5);
    }

    public static function finish()
    {
        $ob = '<dl>';
        foreach ( self::$resultStack as $actionName => $time ) {
            $ob.= "<dt>$actionName</dt>";
            $ob.= "<dd>$time</dd>";
        }
        $ob.= '</dl>';
        Renderer::add($ob);
    }

    public static function _now()
    {
        list($msec,$sec)= explode(' ', microtime());
        return ( (float)$msec + (float)$sec );
    }
}