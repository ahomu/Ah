<?php

namespace ah;

use ah\event;

/**
 * ah\Response
 *
 * actionで扱われる，HTTPレスポンスの管理クラス．
 *
 * ah\action\Baseのコンストラクタ内で，
 * Responseプロパティに，ah\Responseのインスタンスを割り当てられる．
 * {{{
 * $this->Response = new \ah\Response();
 * }}}
 *
 * action内では，次のように使用される．
 * {{{
 * // MIMEタイプの指定
 * $this->Response->setMimeType(\Util_MIME::detectType('html'));
 *
 * // ステータスコードの設定（デフォルトは200）
 * $this->Response->setStatusCode(200);
 *
 * // レスポンスボディのセット
 * $this->Response->setBody($response_string);
 * }}}
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Response
{
    const HTTP_VERSION  = '1.1';

    /**
     * ステータスコード
     *
     * @see ah\Response\setStatusCode()
     * @see ah\Response\getStatusCode()
     * @var int
     */
    private $_status    = 200;

    /**
     * 文字コード
     *
     * @see ah\Response\setCharset()
     * @var string
     */
    private $_charset   = 'UTF-8';

    /**
     * MIMEタイプ
     *
     * @see ah\Response\setMimeType()
     * @var string
     */
    private $_mimetype  = 'text/html';

    /**
     * レスポンスボディ
     *
     * @see ah\Response\setBody()
     * @see ah\Response\getBody()
     * @see ah\Response\_sendNody()
     * @var string
     */
    private $_body      = '';

    /**
     * リダイレクト先
     *
     * @see ah\Response\setLocation()
     * @var string
     */
    private $_location  = null;

    /**
     * キャッシュコントロールの指定
     *
     * @see ah\Response\setCacheControl()
     * @var string Cache-Control
     */
    private $_cache     = '';

    /**
     * キャッシュ無効フラグ
     *
     * @see ah\Response\setNoCache()
     * @var bool
     */
    private $_nocache   = false;

    /**
     * クロスドメインのリソース利用許可
     *
     * @see ah\Response\setAllowOrigin()
     * @var string|array
     */
    private $_allowOrigin = null;

    /**
     * レスポンスヘッダ
     *
     * @see ah\Response\setHeader()
     * @see ah\Response\_sendHeader()
     */
    private $_headers   = array();

    /**
     * ステータスコード（3桁）を指定する．
     *
     * @param string $code
     * @return void
     */
    public function setStatusCode($code)
    {
        $this->_status  = $code;
    }

    /**
     * 現在のステータスコードを取得する．
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->_status;
    }

    /**
     * レスポンスボディを指定する．
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * 現在のレスポンスボディを取得する．
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * 文字コードを指定する．
     *
     * @param string $charset
     * @return void
     */
    public function setCharset($charset)
    {
        $this->_charset  = $charset;
    }

    /**
     * MIMEタイプを指定する．
     *
     * @param string $mimetype
     * @return void
     */
    public function setMimeType($mimetype)
    {
        $this->_mimetype = $mimetype;
    }

    /**
     * Locationヘッダを指定する．リダイレクト用．
     * 30x系のステータスコードと併用する．
     *
     * @param string $location
     * @return void
     */
    public function setLocation($location)
    {
        $this->_location = $location;
    }

    /**
     * キャッシュ無効の指定を行う．
     * ここでtrueが指定された状態でレスポンスが返されると，
     * Cache-Control: no-cache
     *
     * @param bool $bool
     * @return void
     */
    public function setNoCache($bool)
    {
        $this->_nocache = $bool;
    }

    /**
     * クロスドメインのリソース利用を許可する通信元ドメインを指定する
     *
     * @param string|array $allows
     * @return void
     */
    public function setAllowOrigin($allows)
    {
        $this->_allowOrigin = $allows;
    }

    /**
     * Cache-Controlを指定する．
     * _nocacheプロパティがtrueの場合は，そちらが優先される．
     *
     * @param string $control
     * @return void
     */
    public function setCacheControl($control)
    {
        $this->_cache = $control;
    }

    /**
     * レスポンスヘッダーをkey-val式で指定する．
     *
     * @param string $key
     * @param string $val
     * @return void
     */
    public function setHeader($key, $val)
    {
        $this->_headers[$key] = $val;
    }

    /**
     * レスポンスヘッダーを全て送出する．
     * PHP4.4.2および5.1.2以降で，header関数自体がヘッダインジェクション対策を持っている．
     * ただし，5.3.xで，CR(\x0D)を対象としていないことがあるのでremoveBreakを通過させる．
     * http://php.net/manual/ja/function.header.php
     *
     * @see ah\Response\send()
     * @return void
     */
    private function _sendHeaders()
    {
        // build phase
        $statusLine = sprintf('HTTP/%s %s %s',
            self::HTTP_VERSION,
            $this->_status,
            self::$statusCode[$this->_status]
        );

        removeBreak($statusLine);
        array_walk($this->_headers, 'removeBreak');

        // send phase
        header($statusLine);

        foreach ( $this->_headers as $key => $val ) {
            $header = $key.': '.$val;
            header($header);
        }
    }

    /**
     * レスポンスボディを送出する．
     *
     * @see ah\Response\send()
     * @return void
     */
    private function _sendBody()
    {
        print $this->_body;
    }

    /**
     * HTTPレスポンスをクライアントに返す
     *
     * @return void
     */
    public function send()
    {
        // fix response headers
        if ( $this->_nocache === true ) {
            // no nocache
            $this->setHeader('Cache-Control', 'no-cache');
        } elseif ( !empty($this->_cache) ) {
            // cache control
            $this->setHeader('Cache-Control', $this->_cache);
        }

        if ( $this->_location !== null ) {
            // redirect location
            $this->setHeader('Location', $this->_location);
        } else {
            // MIME type & charset
            $this->setHeader('Content-Type', "{$this->_mimetype}; charset={$this->_charset}");

            // content length
            $this->setHeader('Content-Length', bytelen($this->_body));

            // disable MIME sniffing of IE8
            $this->setHeader('X-Content-Type-Options', 'nosniff');

            // Allow-Access-Control-Origin
            if ( $this->_allowOrigin !== null ) {
                $this->setHeader('Access-Control-Allow-Origin', is_array($this->_allowOrigin) ? implode(',', $this->_allowOrigin) : $this->_allowOrigin);
            }
        }

        // #EVENT send before
        event\Helper::getDispatcher()->notify(new event\Subject($this, 'response.send_before'));

        // send response header ( テスト時は動作させない )
        if ( !isset($_SERVER['APPLICATION_ENV']) || $_SERVER['APPLICATION_ENV'] !== 'unittest' ) {
            $this->_sendHeaders();
        }

        // send response body
        $this->_sendBody();

        // #EVENT send after
        event\Helper::getDispatcher()->notify(new event\Subject($this, 'response.send_after'));
    }

    /**
     * ステータスコードと，メッセージの対応表
     *
     * @see ah\Response\send()
     * @var array
     */
    public static $statusCode = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
    );
}
