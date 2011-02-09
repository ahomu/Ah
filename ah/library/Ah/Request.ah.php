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
        return !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    }

    /**
     * getUri
     *
     * @return $uri
     */
    public static function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * getPath
     *
     * @return mixed
     */
    public static function getPath()
    {
        return preg_replace('/\/?(\?.*)?$/', '', self::getUri());
    }

    /**
     * getMethod
     *
     * @return $method
     */
    public static function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
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
     * @param string $type ( GET, POST, COOKIE )
     * @param string $charset
     * @return bool
     */
    public static function getParams($type, $charset = null)
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
            default     :
                $params = array();
                break;
        }

        // TODO exception: when invalid encoding parameter found out
        $charset = $charset !== null ? $charset : mb_internal_encoding();
        array_walk_recursive($params, 'checkEncoding', $charset);
        return $params;
    }
}
