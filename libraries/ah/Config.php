<?php

namespace ah;

/**
 * ah\Config
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Config
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
            $ymlPath    = DIR_YML."/$key.yml";

            if ( !Cache::isModified($ymlPath, 'config') ) {
                self::$RAM[$key] = unserialize(Cache::load($ymlPath, 'config'));
            } else {
                self::$RAM[$key] = \Spyc::YAMLLoad($ymlPath);
                if ( !empty(self::$RAM[$key]) ) {
                    Cache::save($ymlPath, serialize(self::$RAM[$key]), 'config');
                }
            }
        }

        if ( $needle !== null ) {
            if ( !empty(self::$RAM[$key][$needle]) ) {
                return self::$RAM[$key][$needle];
            } else {
                // TODO exception: throw undfined index config
                return false;
            }
        } else {
            return self::$RAM[$key];
        }
    }
}
