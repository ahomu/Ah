<?php

/**
 * Ah_Debug_Timer
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Debug_Timer
{
    private static $INSTANCE;

    var $start;
    var $end;
    var $time;
    var $mem;
    var $usage;

    public static function getInstance()
    {
        if ( self::$INSTANCE === null ) {
            self::$INSTANCE = new self();;
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
        return $this;
    }

    public function start()
    {
        $this->start    = $this->_now();
        $this->mem      = memory_get_usage();
        return $this;
    }
    
    public function stop()
    {
        $this->end      = $this->_now();
        $this->time     = round(($this->end - $this->start), 5);
        $this->usage    = memory_get_usage() - $this->mem;
        return $this;
    }

    private function _now()
    {
        list($msec,$sec)= explode(' ', microtime());
        return ( (float)$msec + (float)$sec );
    }
}
