<?php

/**
 * Ah_Autoloader
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Autoloader
{
    static public $path = array();

    /**
     * register
     *
     * @return void
     */
    public static function register($func, $throw = false)
    {
        spl_autoload_register($func, $throw);
    }

    /**
     * load
     *
     * @throws Ah_Exception_Resolver
     * @param  string $className
     * @return void
     */
    public static function load($className)
    {
        $prefix = substr($className, 0, strpos($className, '_'));

        if ( in_array($prefix, array('Model', 'View', 'Ah')) )
        {
            $pathStack  = array(DIR_LIB);
            $compType   = '.'.strtolower($prefix); // .model | .view | .ah
        }
        elseif ( $prefix === 'Action' )
        {
            $pathStack  = array(DIR_ACT);
            $compType   = '.action';
            $className  = substr($className, strlen('Action_'));
        }
        else
        {
            $pathStack  = array(DIR_LIB.'/Class');
            $compType   = '';
        }

        $chunks     = explode('_', $className);
        foreach ( $chunks as $chunk ) {
            $pathStack[] = $chunk;
        }

        $classPath  = implode('/', $pathStack)."$compType.php";
        if ( is_readable($classPath) ) {
            require_once($classPath);
        } else {
//            throw new Ah_Exception_NotFound($className);
        }
    }
}
