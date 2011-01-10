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
        $_status    = '200',
        $_body      = '';

    public function __construct()
    {
        
    }

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
     * send
     *
     * @param string $body
     * @return void
     */
    public function send()
    {
        // first version & response code
        header(sprintf('HTTP/%s %s %s',
                       $this->_version,
                       $this->_status,
                       Ah_Response::$statusCode[$this->_status]
               ));

        if ( $this->_location !== null )
        {
            // redirect location
            header("Location: {$this->_location}");
        }
        else
        {
            // MIME type & charset
            header("Content-Type: {$this->_mimetype}; charset={$this->_charset}");

            // disable MIME sniffing of IE8
            header("X-Content-Type-Options: nosniff");
        }

        Ah_Debug_ErrorTrace::stack(ob_get_clean());
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
