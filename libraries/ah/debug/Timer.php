<?php

namespace ah\debug;

/**
 * ah\debug\Timer
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Timer
{
    private static $INSTANCE;

    var $start;
    var $end;
    var $time;
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
        return $this;
    }
    
    public function stop()
    {
        $this->end      = $this->_now();
        $this->time     = round(($this->end - $this->start), 5);
        return $this;
    }

    private function _now()
    {
        list($msec,$sec)= explode(' ', microtime());
        return ( (float)$msec + (float)$sec );
    }
}
