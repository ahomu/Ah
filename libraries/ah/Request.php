<?php

namespace ah;

/**
 * ah\Request
 *
 * クライアントからのHTTPリクエストに関する情報を取得する．
 * staticメソッド群として実装．
 * 各action間で共有しているグローバルなリクエスト情報．
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Request
{
    /**
     * ホストを取得する．ポートが含まれている場合は，それも含む．
     *
     * @return string
     */
    public static function getHost()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST']
                                                        : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']
                                                                                        : '');
    }

    /**
     * ホスト部に含まれるポートを取得する．
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
     * スキーム・ホスト・ポートまでのURIを取得する．
     *
     * @return string
     */
    public static function getRootUri()
    {
        return (self::isSsl() ? 'https' : 'http').'://'.self::getHost().'/';
    }

    /**
     * リクエストURIを取得する．
     *
     * @return string
     */
    public static function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * フロントコントローラまでのパスを取得する．
     *
     * @return string
     */
    public static function getBaseUri()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];
        $request_uri = self::getRequestUri();

        if ( strpos($request_uri, $script_name) === 0 ) {
            return $script_name;
        } elseif ( strpos($request_uri, dirname($script_name)) === 0 ) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    /**
     * リクエストパスを取得する．
     *
     * @return string
     */
    public static function getPath()
    {
        $base_uri    = self::getBaseUri();
        $request_uri = self::getRequestUri();

        if ( false !== ($pos = strpos($request_uri, '?')) ) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        return substr($request_uri, strlen($base_uri));
    }

    /**
     * リクエストメソッドを取得する．
     *
     * @return string
     */
    public static function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * リクエスト中の拡張子を取得する．
     *
     * @return string
     */
    public static function getExtension()
    {
        if ( preg_match('@\.(\w+)$@', self::getPath(), $match) ) {
            return strval($match[1]);
        }
        return '';
    }

    /**
     * リファラを取得する．
     *
     * @return string
     */
    public static function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * ユーザーエージェントを取得する．
     *
     * @return string
     */
    public static function getUa()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * SSL通信であるかを判断する．
     *
     * @return bool
     */
    public static function isSsl()
    {
        return !!( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' );
    }

    /**
     * XHRであるかを判断する．X_REQUESTED_WITHの指定は，JavaScript側のライブラリ実装に依存する．

     * @return bool
     */
    public static function isXhr()
    {
        return !!( isset($_SERVER['X_REQUESTED_WITH']) && $_SERVER['X_REQUESTED_WITH'] === 'XMLHttpRequest' );
    }

    /**
     * gzipが許可されているかを判断する．
     *
     * @return bool
     */
    public static function isAcceptGzip()
    {
        return !!( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false );
    }

    /**
     * リクエストメソッドに応じて，パラメータを取得する．
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
