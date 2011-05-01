<?php

/**
 * HTTP_Client provides original HTTP request send & parse response.
 *
 * @package     HTTP
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 * @version     Release: 0.7.1
 */
class HTTP_Client
{
    private
        $_connection,
        $_header,
        $_method,
        $_auth,
        $_digest,
        $_url;

    protected
        $version    = '1.1',
        $maxlen     = 2048,
        $blocking   = true,
        $timeout    = 10,
        $eol        = "\r\n",
        $permited   = array(200, 201, 202, 203);

    public
        $scheme,
        $host,
        $port,
        $sslport,
        $user,
        $pass,
        $path,
        $query,
        $fragment;

    public
        $header,
        $body,
        $error;

    public function __construct()
    {
        $this->_initialize();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function __toString()
    {
        return $this->host.$this->path.$this->query;
    }

    /**
     * _initialize : initialize properties
     *
     * @return void
     */
    private function _initialize()
    {
        $this->scheme   = 'http';
        $this->host     = 'localhost';
        $this->port     = 80;
        $this->sslport  = 443;
        $this->user     = '';
        $this->pass     = '';
        $this->path     = '/';
        $this->query    = '';
        $this->fragment = '';

        $this->_method  = 'GET';
        $this->_auth    = 'Basic';
        $this->_digest  = '';
        $this->_url     = '';

        $this->_header  = array(
            'Accept'            => '',
            'Accept-Charset'    => '',
            'Accept-Language'   => '',
            'Accept-Encoding'   => 'gzip',
            'Allow'             => '',
            'Authorization'     => '',
            'Cache-Control'     => '',
            'Connection'        => 'close',
            'Content-Language'  => '',
            'Content-Length'    => '',
            'Content-Type'      => '',
            'Expect'            => '',
            'Host'              => '',
            'If-Modified-Since' => '',
            'Max-Forwards'      => '',
            'Range'             => '',
            'Referer'           => '',
            'User-Agent'        => 'PHP/'.phpversion(),
            'WWW-Authenticate'  => '',
        );

        $this->header   = null;
        $this->body     = null;
        $this->error    = true;
    }

    /**
     * connect - Initialize and open socket.
     *
     * @param string $url
     * @return void
     */
    public function connect($url)
    {
        if ( $this->host != parse_url($url, PHP_URL_HOST) )
        {
            $this->disconnect();
        }

        $this->_initialize();
        $this->_parseUrl($url);
        $this->_url = $url;

        $scheme = ($this->scheme == 'https') ? 'ssl://' : '';
        $port   = ($this->scheme == 'https') ? $this->sslport : $this->port;
        $this->_connection = @fsockopen($scheme.$this->host, $port, $errno, $errstr, $this->timeout);
    }

    /**
     * reconnect - Socket reopen without initialization.
     *
     * @param string $url
     * @return void
     */
    public function reconnect($url)
    {
        $this->disconnect();
        $this->_parseUrl($url);

        $scheme = ($this->scheme == 'https') ? 'ssl://' : '';
        $port   = ($this->scheme == 'https') ? $this->sslport : $this->port;
        $this->_connection = @fsockopen($scheme.$this->host, $port, $errno, $errstr, $this->timeout);
    }

    /**
     * _parseUrl - Parsing url and set property.
     * 
     * @param string $url
     * @return void
     */
    private function _parseUrl($url)
    {
        $parsed = parse_url($url);

        foreach ( $parsed as $key => $val ) {
            $this->$key = $val;
        }
    }

    /**
     * disconnect - Close socket.
     *
     * @return string $url
     */
    public function disconnect()
    {
        if ( is_resource($this->_connection) )
        {
            @fclose($this->_connection);
        }
        else
        {
            $this->_connection = null;
        }
        return $this->_url;
    }

    /**
     * setMethod - Set http method.
     *
     * @param string $method['GET'|'POST'|'PUT'|'DELETE']
     * @return void
     */
    public function setMethod($method)
    {
        $this->_method = strtoupper($method);
    }

    /**
     * setAuthMethod - Set authorization method.
     *
     * @param string $auth['Basic'|'Digest']
     * @param string $user
     * @param string $pass
     * @return void
     */
    public function setAuthMethod($auth, $user, $pass)
    {
        $this->_auth = ucwords($auth);
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * setHeader - Set http request header.
     * 
     * @param string $key
     * @param string $val
     * @return void
     */
    public function setHeader($key, $val)
    {
        $key = str_replace(array("\r\n","\r","\n"), '', $key);
        $val = str_replace(array("\r\n","\r","\n"), '', $val);
        $this->_header[$key] = $val;
    }

    /**
     * request - This is basic request method. Involve standard error handling.
     *
     * @return boolean|string $response
     */
    public function request()
    {
        $this->error    = true;

        if ( is_resource($this->_connection) )
        {
            if ( !!($this->writeRequest()) )
            {
                if ( in_array($this->parseResponse(), $this->permited) )
                {
                    $this->error    = false;
                }
            }
        }

        return $this->error ? false
                            : $this->body;
    }

    /**
     * sendRequest - Send request.
     *
     * @return boolean $fwrite
     */
    public function writeRequest()
    {
        $request    = $this->_buildRequest();
        $wroteLen   = fwrite($this->_connection, $request);
        return (bool)(strlen($request) == $wroteLen);
    }

    /**
     * _buildRequest - Build request.
     *
     * @return string $request
     */
    private function _buildRequest()
    {
        $eol     = $this->eol;
        $header  = array_merge(array_diff($this->_header, array('')));
        $request = '';

        // TODO issue: ヘッダから改行コードを除去する

        // Host
        $header['Host']  = "{$this->host}";

        // Basic Authorization
        if ( $this->_auth == 'Basic' && !empty($this->user) && !empty($this->pass) )
        {
            $header['Authorization'] = 'Basic '.base64_encode("{$this->user}:{$this->pass}");
        }

        // Digest Authorization ( only once try )
        if ( $this->_auth == 'Digest' && !empty($this->_digest) )
        {
            $header['Authorization'] = 'Digest '.$this->_digest;
        }

        // Build
        switch ( $this->_method )
        {
            case 'POST' :
                $request    = "{$this->_method} {$this->path} HTTP/{$this->version}{$eol}";
                $header['Content-Type']   = 'application/x-www-form-urlencoded';
                $header['Content-Length'] = strlen($this->query);
                break;
            case 'GET'  :
                $request    = "{$this->_method} {$this->path}";
                $request   .= !empty($this->query) ? "?{$this->query} HTTP/{$this->version}{$eol}"
                                                   : " HTTP/{$this->version}{$eol}";
                break;
        }

            // header
            foreach ( $header as $key => $val ) {
                $request .= "{$key}: {$val}{$eol}";
            }

            // body?
            if ( $this->_method == 'POST' && !empty($this->query) )
            {
                $data     = $this->query;
                $request .= "{$eol}{$data}";
            }

            // important!
            $request .= $eol;

        return $request;
    }

    /**
     * parseResponse - Parsing response header and body.
     *
     * @return int $code
     */
    public function parseResponse()
    {
        $eol    = array("\r", "\n", '\r\n');
        $regex  = '/^\s?HTTP\/([0-9].[0-9x])\s+([0-9]{3})\s+([0-9a-zA-Z\s-]*)$/';

        while ( '' !== ($line = str_replace($eol, '', fgets($this->_connection))) ) {
            if ( strpos($line, ':') === false && preg_match($regex, $line, $match) )
            {
                $this->header['Status-Code'] = array(
                    'version'   => $match[1],
                    'code'      => $match[2],
                    'status'    => $match[3],
                );
                $rawHeader = $line.$this->eol;
            }
            else
            {
                list($key, $val) = explode(':', $line, 2);
                $this->header[$key] = ltrim($val);
                $rawHeader .= $line.$this->eol;
            }
        }

        $code   = $this->getResponseStatusCode();

        // digest
        if ( 1
            and $code == 401
            and isset($this->header['WWW-Authenticate'])
            and strpos($this->header['WWW-Authenticate'], 'Digest ') == 0
            and $this->_auth == 'Digest'
            and !empty($this->user)
            and !empty($this->pass)
            and empty($this->_digest)
            )
        {
            return $this->_digestRequest();
        }

        // default
        if ( $code >= 200 && $code != 204 && $code != 304 )
        {
            $this->body = stream_get_contents($this->_connection);

            if ( @$this->header['Transfer-Encoding'] == 'chunked' )
            {
                $this->body = $this->_chunkdecode($this->body);
            }

            if ( @$this->header['Content-Encoding'] == 'gzip' )
            {
                $this->body = $this->_gzdecode($this->body);
            }
        }

        return $code;
    }

    /**
     * _digestRequest - Support digest authorization.
     *
     * @return void
     */
    private function _digestRequest()
    {
        $string = substr($this->header['WWW-Authenticate'], strlen('Digest '));
        $chunks = explode(',', $string);

        foreach ( $chunks as $chunk ) {
            preg_match('/^\s?(.*?)="?([^"].*[^"])"?\s?$/', $chunk, $matches);
            $digest[$matches[1]] = $matches[2];
        }

        if ( !empty( $digest['qop'] ) )
        {
            $digest['cnonce']    = md5(date('U'));
        }
        $digest['uri']       = $this->path;
        $digest['username']  = $this->user;
        $digest['nc']        = !empty($digest['nc']) ? $digest['nc']
                                                     : '00000001';

        // A1
        $A1 = null;
        if ( $digest['algorithm'] == 'MD5' || empty($digest['algorithm']) )
        {
            $A1 =   md5(
                $this->user.':'.
                $digest['realm'].':'.
                $this->pass
            );
        }
        else
        {
            // MD5-sess
        }

        // A2
        $A2 = null;
        if ( $digest['qop'] == 'auth' || empty($digest['auth'])  )
        {
            $A2 =   md5(
                $this->_method.':'.
                $digest['uri']
            );
        }
        elseif ( $digest['qop'] == 'auth-int' )
        {
            // auth-int
        }

        // D
        $D = null;
        if ( empty($digest['qop']) )
        {
            $D  =   md5(
                $A1.':'.
                $digest['nonce'].':'.
                $A2
            );
        }
        elseif ( $digest['qop'] == 'auth' || $digest['qop'] == 'auth-int' )
        {
            $D  =   md5(
                $A1.':'.
                $digest['nonce'].':'.
                $digest['nc'].':'.
                $digest['cnonce'].':'.
                $digest['qop'].':'.
                $A2
            );
        }

        $digest['response'] = $D;

        foreach ( $digest as $key => $val ) {
            if ( $key == 'nc' || $key == 'qop' || $key == 'algorithm' )
            {
                $digest[$key]   = $key.'='.$val;
            }
            else
            {
                $digest[$key]   = $key.'="'.$val.'"';
            }
        }

        // set digest
        $this->_digest  =   $digest['username'].', '.
                            $digest['realm'].', '.
                            $digest['nonce'].', '.
                            $digest['uri'].', '.
                            $digest['algorithm'].', '.
                            $digest['qop'].', '.
                            $digest['nc'].', '.
                            $digest['cnonce'].', '.
                            $digest['response'];

        // retry
        $this->reconnect($this->_url);
        return $this->request();
    }

    /**
     * _gzdecode
     *
     * @param string $data
     * @return string $decoded
     */
    private function _gzdecode($data)
    {
        if ( function_exists('gzdecode') )
        {
            return gzdecode($data);
        }
        else
        {
            $data   = "data:application/x-gzip;base64,".base64_encode($data); 
            $fp     = gzopen($data, "r");
            return gzread($fp, 524288); 
        }
    }

    /**
     * _chunkdecode
     * ( http://jp.php.net/manual/ja/function.fsockopen.php#73581 )
     *
     * @param string $str
     * @param string $eol
     * @return string $str
     */
    private function _chunkdecode ($str, $eol = "\r\n")
    {
        $tmp    = $str;
        $add    = strlen($eol);
        $str    = '';

        do {
            $tmp    = ltrim($tmp);
            $pos    = strpos($tmp, $eol);
            $len    = hexdec(substr($tmp, 0, $pos));

            $str   .= substr($tmp, ($pos + $add), $len);

            $tmp    = substr($tmp, ($len + $pos + $add));
            $check  = trim($tmp);
        } while ( !empty($check) );

        return $str;
    }

    /**
     * getResponseStatus
     *
     * @return array(3) $statusCode['version'|'code'|'status']
     */
    public function getResponseStatus()
    {
        return $this->header['Status-Code'];
    }

    /**
     * getResponseStatusCode
     *
     * @return string $statusCode
     */
    public function getResponseStatusCode()
    {
        return $this->header['Status-Code']['code'];
    }

    /**
     * getResponseHeader
     *
     * @return array $respopnseHeader
     */
    public function getResponseHeader()
    {
        return $this->header;
    }

    /**
     * getResponseBody
     *
     * @return string $responseBody
     */
    public function getResponseBody()
    {
        return $this->body;
    }
}
