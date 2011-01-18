<?php

/**
 * Ah_Config
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Config
{
    public static $RAM = array();

    /**
     * load
     *
     * @param string $key
     * @param string $needle
     * @return void
     */
    public static function load($key, $needle = null)
    {
        if ( !array_key_exists($key, self::$RAM) ) {
            $ymlPath    = DIR_YML."/config.$key.yml";

            if ( Ah_Cache::isValid($ymlPath, 'config') ) {
                self::$RAM[$key] = unserialize(Ah_Cache::load($ymlPath, 'config'));
            } else {
                self::$RAM[$key] = Spyc::YAMLLoad($ymlPath);
                if ( !empty(self::$RAM[$key]) )
                {
                    Ah_Cache::save($ymlPath, serialize(self::$RAM[$key]), 'config');
                }
            }
        }

        if ( $needle !== null ) {
            if ( !empty(self::$RAM[$key][$needle]) ) {
                return self::$RAM[$key][$needle];
            } else {
                return false;
            }
        } else {
            return self::$RAM[$key];
        }
    }
}
