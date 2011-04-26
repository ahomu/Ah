<?php

namespace Ah;

/**
 * Ah\Cache
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Cache
{
    public static function isValid($realPath, $ns = null)
    {
        $cachePath  = self::_getPath($realPath, $ns);

        if ( 1
            and file_exists($cachePath)
            and filemtime($cachePath) > filemtime($realPath)
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function load($realPath, $ns = null)
    {
        return file_get_contents(self::_getPath($realPath, $ns));
    }

    public static function save($realPath, $content, $ns = null)
    {
        $cachePath = self::_getPath($realPath, $ns);

        $dirPath   = preg_replace('/(\/\w+)$/', '', $cachePath);
        if ( !file_exists($dirPath) ) {
            mkdir($dirPath);
        }

        return file_put_contents($cachePath, $content);
    }

    private static function _getPath($realPath, $ns = null)
    {
        if ( $ns === null ) {
            return DIR_TMP."/".md5($realPath);
        } else {
            return DIR_TMP."/$ns/".md5($realPath);
        }
    }
}