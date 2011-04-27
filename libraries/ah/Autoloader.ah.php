<?php

namespace ah;

use ah\exception;

/**
 * ah\Autoloader
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Autoloader
{
    /**
     * register
     *
     * @param function $func
     * @param bool $throw
     * @return void
     */
    public function register($func, $throw = false)
    {
        spl_autoload_register($func, $throw);
    }

    /**
     * ahLoad
     *
     * @param string $className
     * @return void
     */
    public function ahLoad($className)
    {
        if ( strpos($className, '\\') !== false ) {
            $separator = '\\';
        } else {
            $separator = '_';
        }

        $package = $this->_getPackage($className, $separator);

        switch ($package) {
            case 'ah'       :
                $this->ahCoreLoad($className, $separator);
                break;
            case 'action'   :
                $this->ahActionLoad($className, $separator);
                break;
            // TODO issue: 'view' will rename to 'surface'
            case 'view'     :
                $this->ahViewLoad($className, $separator);
                break;
            default         :
                $this->ahCommonLoad($className, $separator);
                break;
        }

    }

    /**
     * ahActionLoad
     *
     * @param string $className
     * @return void
     */
    public function ahActionLoad($className)
    {
        $this->_traversal(DIR_ACT, $className, 'action');
    }

    /**
     * ahCoreLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCoreLoad($className)
    {
        $this->_traversal(DIR_LIB.'/ah', $className, 'ah');
    }

    /**
     * ahViewLoad
     *
     * @param string $className
     * @return void
     */
    public function ahViewLoad($className)
    {
        $this->_traversal(DIR_LIB.'/view', $className, 'view');
    }

    /**
     * ahCommonLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCommonLoad($className)
    {
        $this->_traversal(DIR_LIB.'/common', $className, null);
    }

    /**
     * sfLoad
     *
     * @param string $className
     * @return void
     */
    public function sfLoad($className)
    {
        $filePath = DIR_LIB.'/vendor/sf/'.$className.'.php';
        $this->_load($filePath);
    }

    /**
     * _getPackage
     *
     * @param string $className
     * @param string $needle
     * @return string
     */
    private function _getPackage($className, $needle)
    {
        return substr($className, 0, strpos($className, $needle));
    }

    /**
     * _traversal
     *
     * @param string $basepath
     * @param string $className
     * @param string $prefix
     * @return void
     */
    private function _traversal($basepath, $className, $prefix = null)
    {
        if ( strpos($className, '\\') !== false ) {
            $separator = '\\';
        } else {
            $separator = '_';
        }

        if ( $prefix !== null ) {
            $extension = '.'.strtolower($prefix);
            $className = substr($className, strlen($prefix.$separator));
        } else {
            $extension = '';
        }

        $chunks     = explode($separator, $className);
        $pathStack = array($basepath);

        foreach ( $chunks as $chunk ) {
            $pathStack[] = $chunk;
        }

        $filePath  = implode('/', $pathStack)."$extension.php";

        $this->_load($filePath);
    }

    /**
     * _load
     *
     * @param string $filePath
     * @return void
     */
    private function _load($filePath)
    {
        if ( is_readable($filePath) ) {
            require_once($filePath);
        }
    }

    /**
     * terminate
     *
     * @throws Ah_Exception_NotFound
     * @param string $className
     * @return void
     */
    public function terminate($className)
    {
        throw new exception\NotFound($className);
    }
}
