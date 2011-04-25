<?php

/**
 * Ah_Request
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Request
{
    /**
     * getHost
     *
     * @return $host
     */
    public static function getHost()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    }

    /**
     * getPort
     *
     * @return int|null
     */
    public static function getPort()
    {
        if ( preg_match('@[^:]+:(\d+)$@', self::getHost(), $match) ) {
            return intval($match[1]);
        }
        return null;
    }

    /**
     * getUri
     *
     * @return $uri
     */
    public static function getUri()
    {
        return (self::isSsl() ? 'https' : 'http').'://'.self::getHost().(!!self::getPort() ? ':'.self::getPort() : '').'/';
    }

    /**
     * getPath
     *
     * @return mixed
     */
    public static function getPath()
    {
        return preg_replace('/\/?(\?.*)?$/', '', $_SERVER['REQUEST_URI']);
    }

    /**
     * getMethod
     *
     * @return $method
     */
    public static function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * getExtension
     *
     * @return null|string
     */
    public static function getExtension()
    {
        if ( preg_match('@\.(\w+)$@', self::getPath(), $match) ) {
            return strval($match[1]);
        }
        return null;
    }

    /**
     * getReferer
     *
     * @return string
     */
    public static function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * getUa
     *
     * @return string
     */
    public static function getUa()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * isSsl
     *
     * @return $bool
     */
    public static function isSsl()
    {
        return !!( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' );
    }

    /**
     * isXhr - XHRであるかを調べる．X_REQUESTED_WITHの指定は，JavaScript側のライブラリ実装に依存する．
     *
     * @return $bool
     */
    public static function isXhr()
    {
        return !!( isset($_SERVER['X_REQUESTED_WITH']) && $_SERVER['X_REQUESTED_WITH'] === 'XMLHttpRequest' );
    }

    /**
     * isAcceptGzip
     *
     * @return bool
     */
    public static function isAcceptGzip()
    {
        return !!( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false );
    }

    /**
     * getParams
     *
     * @param string $type ( GET, POST, COOKIE, PUT )
     * @return bool
     */
    public static function getParams($type)
    {
        switch ( $type ) {
            case 'GET'  :
                $params = $_GET;
                break;
            case 'POST' :
                $params = $_POST;
                break;
            case 'COOKIE'   :
                $params = $_COOKIE;
                break;
            case 'PUT'  ;
                $params = array(file_get_contents('php://input'));
                break;
            default     :
                $params = array();
                break;
        }

        return $params;
    }
}
