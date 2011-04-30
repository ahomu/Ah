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
        $this->_traversal(DIR_APP, $className);
    }

    /**
     * ahCoreLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCoreLoad($className)
    {
        $this->_traversal(DIR_LIB, $className);
    }

    /**
     * ahCommonLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCommonLoad($className)
    {
        $this->_traversal(DIR_LIB.'/common', $className);
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
     * @return void
     */
    private function _traversal($basepath, $className)
    {
        if ( strpos($className, '\\') !== false ) {
            $separator = '\\';
        } else {
            $separator = '_';
        }

        $pathStack = explode($separator, $className);
        array_unshift($pathStack, $basepath);

        $filePath  = implode('/', $pathStack).'.php';

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
