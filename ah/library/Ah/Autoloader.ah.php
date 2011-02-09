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
    /**
     * register
     *
     * @return void
     */
    public function register($func, $throw = false)
    {
        spl_autoload_register($func, $throw);
    }

    /**
     * ahLoad
     *
     * @return void
     */
    public function ahLoad($className)
    {
        $prefix = $this->_getUnscoPrefix($className);

        switch ($prefix) {
            case 'Ah'       :
                $this->ahCoreLoad($className);
                break;
            case 'Action'   :
                $this->ahActionLoad($className);
                break;
            case 'Model'    :
                $this->ahModelLoad($className);
                break;
            case 'View'     :
                $this->ahViewLoad($className);
                break;
            default         :
                $this->ahCommonLoad($className);
                break;
        }

    }

    /**
     * ahCoreLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCoreLoad($className)
    {
        $this->_traversal($className, '.ah');
    }

    /**
     * ahActionLoad
     *
     * @param string $className
     * @return void
     */
    public function ahActionLoad($className)
    {
        $this->_traversal($className, '.action');
    }

    /**
     * ahModelLoad
     *
     * @param string $className
     * @return void
     */
    public function ahModelLoad($className)
    {
        $this->_traversal($className, '.model');
    }

    /**
     * ahViewLoad
     *
     * @param string $className
     * @return void
     */
    public function ahViewLoad($className)
    {
        $this->_traversal($className, '.view');
    }

    /**
     * ahCommonLoad
     *
     * @param string $className
     * @return void
     */
    public function ahCommonLoad($className)
    {
        $this->_traversal($className, null, 'Common');
    }

    /**
     * sfLoad
     *
     * @param string $className
     * @return void
     */
    public function sfLoad($className)
    {
        $classPath = DIR_LIB.'/Vendor/sf/'.$className.'.php';
        $this->_load($classPath);
    }

    /**
     * userLoad
     *
     * @param string $className
     * @return void
     */
    public function userLoad($className)
    {
        // TODO issue: ユーザー拡張について考える
        return false;
    }

    /**
     * _getUnscoPrefix
     *
     * @param string $className
     * @return string
     */
    private function _getUnscoPrefix($className)
    {
        return substr($className, 0, strpos($className, '_'));
    }

    /**
     * _traversal
     * @param string $className
     * @param string $extension
     * @param string $prefix
     */
    private function _traversal($className, $extension = '', $prefix = '')
    {
        $pathStack = array(DIR_LIB);
        if ( $prefix !== '' ) $pathStack[] = $prefix;

        $chunks     = explode('_', $className);
        foreach ( $chunks as $chunk ) {
            $pathStack[] = $chunk;
        }
        $classPath  = implode('/', $pathStack)."$extension.php";

        $this->_load($classPath);
    }

    /**
     * _load
     *
     * @param string $classPath
     * @return void
     */
    private function _load($classPath)
    {
        if ( is_readable($classPath) ) {
            require_once($classPath);
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
        throw new Ah_Exception_NotFound($className);
    }
}
