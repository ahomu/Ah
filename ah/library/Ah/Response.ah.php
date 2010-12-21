<?php

/**
 * Ah_Response provides managing http response detail.
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Response
{
    private
        $_version   = '1.1',
        $_charset   = 'UTF-8',
        $_mimetype  = 'text/html',
        $_location  = null,
        $_status    = '200 OK',
        $_body      = '';

    private static $INSTANCE;

    public static function getInstance()
    {
        if ( self::$INSTANCE === null ) {
            self::$INSTANCE = new self();;
        }
        return self::$INSTANCE;
    }

    /**
     * setStatusCode
     *
     * @param string $code
     * @return void
     */
    public function setStatusCode($code)
    {
        $this->_status  = $code.' '.Ah_Response::$statusCode[$code];
    }

    /**
     * getStatusCode
     *
     * @return strint $code
     */
    public function getStatusCode()
    {
        return substr($this->_status, 0, 3);
    }

    /**
     * isSetStatusCode
     *
     * @return boolean
     */
    public function isSetStatusCode()
    {
        return !!($this->_status);
    }

    /**
     * setVersion
     *
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->_version  = $version;
    }

    /**
     * setCharset
     *
     * @param string $charset
     * @return void
     */
    public function setCharset($charset)
    {
        $this->_charset  = $charset;
    }

    /**
     * setMimeType
     *
     * @param string $mimetype
     * @return void
     */
    public function setMimeType($mimetype)
    {
        $this->_mimetype = $mimetype;
    }

    /**
     * setLocation
     *
     * @param string $location
     * @return void
     */
    public function setLocation($location)
    {
        $this->_location = $location;
    }

    /**
     * setBody
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * send
     *
     * @param string $body
     * @return void
     */
    public function send($body = null)
    {
        if ( $body !== null ) {
            $this->_body = $body;
        }

        header("HTTP/{$this->_version} {$this->_status}");
        header("Content-Type: {$this->_mimetype}; charset={$this->_charset}");

        if ( $this->_location !== null ) header("Location: {$this->_location}");

        print $this->_body;
    }

    // status code list
    public static $statusCode = array(
        '100'   => 'Continue',
        '101'   => 'Switching Protocols',
        '102'   => 'Processing',
        '200'   => 'OK',
        '201'   => 'Created',
        '202'   => 'Accepted',
        '203'   => 'Non-Authoritative Information',
        '204'   => 'No Content',
        '205'   => 'Reset Content',
        '206'   => 'Partial Content',
        '207'   => 'Multi-Status',
        '208'   => 'Already Reported',
        '226'   => 'IM Used',
        '300'   => 'Multiple Choices',
        '301'   => 'Moved Permanently',
        '302'   => 'Found',
        '303'   => 'See Other',
        '304'   => 'Not Modified',
        '305'   => 'Use Proxy',
        '307'   => 'Temporary Redirect',
        '400'   => 'Bad Request',
        '401'   => 'Unauthorized',
        '402'   => 'Payment Required',
        '403'   => 'Forbidden',
        '404'   => 'Not Found',
        '405'   => 'Method Not Allowed',
        '406'   => 'Not Acceptable',
        '407'   => 'Proxy Authentication Required',
        '408'   => 'Request Timeout',
        '409'   => 'Conflict',
        '410'   => 'Gone',
        '411'   => 'Length Required',
        '412'   => 'Precondition Failed',
        '413'   => 'Request Entity Too Large',
        '414'   => 'Request-URI Too Long',
        '415'   => 'Unsupported Media Type',
        '416'   => 'Requested Range Not Satisfiable',
        '417'   => 'Expectation Failed',
        '422'   => 'Unprocessable Entity',
        '423'   => 'Locked',
        '424'   => 'Failed Dependency',
        '426'   => 'Upgrade Required',
        '500'   => 'Internal Server Error',
        '501'   => 'Not Implemented',
        '502'   => 'Bad Gateway',
        '503'   => 'Service Unavailable',
        '504'   => 'Gateway Timeout',
        '505'   => 'HTTP Version Not Supported',
        '506'   => 'Variant Also Negotiates',
        '507'   => 'Insufficient Storage',
        '508'   => 'Loop Detected',
        '510'   => 'Not Extended',
    );
}
