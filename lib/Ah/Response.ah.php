<?php

/**
 * Ah_Response provides managing detail of http response.
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Response
{
    const
        _version    = '1.1';

    private
        $_charset   = 'UTF-8',
        $_mimetype  = 'text/html',
        $_location  = null,
        $_status    = '200',
        $_body      = '',
        $_cache     = '',
        $_nocache   = false,
        $_headers   = array();

    /**
     * setStatusCode
     *
     * @param string $code
     * @return void
     */
    public function setStatusCode($code)
    {
        $this->_status  = $code;
    }

    /**
     * getStatusCode
     *
     * @return string $code
     */
    public function getStatusCode()
    {
        return $this->_status;
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
     * getBody
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
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
     * setNoCache
     *
     * @param bool $bool
     * @return void
     */
    public function setNoCache(boolean $bool)
    {
        $this->_nocache = $bool;
    }

    /**
     * setCacheControl
     *
     * @param string $control
     * @return void
     */
    public function setCacheControl($control)
    {
        $this->_cache = $control;
    }

    /**
     * setHeader
     *
     * @param string $filed_key
     * @param string $field_name
     * @return void
     */
    public function setHeader($key, $val)
    {
        $this->_headers[$key] = $val;
    }

    /**
     * sendHeader
     *
     * @return void
     */
    private function _sendHeader()
    {
        foreach ( $this->_headers as $key => $val ) {
            $header = $key.': '.$val;
            header($header);
        }
    }

    /**
     * sendBody
     *
     * @return void
     */
    private function _sendBody()
    {
        print $this->_body;
    }

    /**
     * send
     *
     * @param string $body
     * @return void
     */
    public function send()
    {
        // first version & response code
        header(sprintf('HTTP/%s %s %s',
                       self::_version,
                       $this->_status,
                       self::$statusCode[$this->_status]
        ));

        // send response headers
        if ( $this->_nocache === true )
        {
            // no nocache
            $this->setHeader('Cache-Control', 'no-cache');
        }
        elseif ( !empty($this->_cache) )
        {
            // cache control
            $this->setHeader('Cache-Control', $this->_cache);
        }

        if ( $this->_location !== null )
        {
            // redirect location
            $this->setHeader('Location', $this->_location);
        }
        else
        {
            // MIME type & charset
            $this->setHeader('Content-Type', "{$this->_mimetype}; charset={$this->_charset}");

            // content length
            $this->setHeader('Content-Length', bytelen($this->_body));

            // disable MIME sniffing of IE8
            $this->setHeader('X-Content-Type-Options', 'nosniff');
        }

        // #EVENT send before
        Ah_Event_Helper::getDispatcher()->notify(new Ah_Event_Subject($this, 'response.send_before'));

        // send response header
        $this->_sendHeader();

        // send response body
        $this->_sendBody();

        // #EVENT send after
        Ah_Event_Helper::getDispatcher()->notify(new Ah_Event_Subject($this, 'response.send_after'));
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
        '418'   => "I'm a teapot",
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
