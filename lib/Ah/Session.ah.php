<?php

/**
 * Ah_Session
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Session extends ArrayObject implements IteratorAggregate
{
    private static $INSTANCE;

    public static function getInstance()
    {
        if ( self::$INSTANCE === null ) {
            self::$INSTANCE = new self();
        }
        return self::$INSTANCE;
    }

    public function __construct($flags = 0, $iterator = 'ArrayIterator')
    {
        session_name('ah_session');
        session_start();
        session_regenerate_id(true);
        if ( !empty($_SESSION['ah_user']) ) {
            $vars = unserialize($_SESSION['ah_user']);
        } else {
            $vars = array();
        }
        return parent::__construct($vars, $flags, $iterator);
    }

    public function __destruct()
    {
        $_SESSION['ah_user'] = serialize(self::$INSTANCE);
    }
}
