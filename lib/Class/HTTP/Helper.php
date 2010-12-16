<?php

/**
 * HTTP_Helper provides simply wrap methods for "HTTP_Client".
 *
 * @package     HTTP
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 * @version     0.6
 */
class HTTP_Helper
{
    /**
     * get - Simply GET http request.
     *
     * @param string $url
     * @param int $redirect
     *
     * @return string|boolean $Http->body
     */
    public static function get($url, $redirect = false)
    {
        $Http = HTTP_Helper::send($url, 'GET', null, null, '', $redirect);
        return $Http->body ? $Http->body : false;
    }

    /**
     * post - Simply POST http request.
     *
     * @param string $url
     * @param int $redirect
     *
     * @return boolean $Http->error
     */
    public static function post($url, $redirect = false)
    {
        $Http = HTTP_Helper::send($url, 'POST', null, null, '', $redirect);
        return $Http->error ? false : true;
    }

    /**
     * basicAuth - Standard http request supported basic authorization.
     *
     * @param string $url
     * @param string $method
     * @param string $user
     * @param string $pass
     * @param int $redirect
     *
     * @return object $HTTP_Client
     */
    public static function basicAuth($url, $method = 'GET', $user, $pass, $redirect = 0)
    {
        $Http = HTTP_Helper::send($url, $method, $user, $pass, 'Basic', $redirect);
        return $Http;
    }

    /**
     * digestAuth - Standard http request supported digest authorization.
     *
     * @param string $url
     * @param string $method
     * @param string $user
     * @param string $pass
     * @param int $redirect
     *
     * @return object $HTTP_Client
     */
    public static function digestAuth($url, $method = 'GET', $user, $pass, $redirect = 0)
    {
        $Http = HTTP_Helper::send($url, $method, $user, $pass, 'Digest', $redirect);
        return $Http;
    }

    /**
     * send - Standard http request method.
     *
     * @param string $url
     * @param string $method
     * @param string $user
     * @param string $pass
     * @param string $auth
     * @param int $redirect
     *
     * @return string|boolean $Http->body
     */
    public static function send($url, $method = 'GET', $user = null, $pass = null, $auth = 'Basic', $redirect = 0)
    {
        $Http = new HTTP_Client();

        $Http->connect($url);
        $Http->setMethod($method);

        if ( !empty($user) && !empty($pass) )
        {
            $Http->setAuthMethod($auth, $user, $pass);
        }

        $Http->request();

        if ( $redirect != 0 ) HTTP_Helper::redirection($Http, $redirect);

        return $Http;
    }

    /**
     * redirection - Redirection helper method.
     *
     * @param object $Http
     * @param int $loop
     *
     * @return Object $HTTP_Client
     */
    public static function redirection(& $Http, $loop = 3)
    {
        $code = $Http->getResponseStatusCode();

        while ( 1
            and array_search($code, array(301, 302, 303, 307)) !== false
            and $loop != 0)
        {
            $loop -= 1;

            $redirect   = $Http->header['Location'];
            $tmp = parse_url($redirect);

            if ( empty($tmp['host']) )
            {
                $redirect = $Http->host.$redirect;
            }
            if ( empty($tmp['scheme']) )
            {
                $redirect = $Http->scheme.'://'.$redirect;
            }

            $Http->reconnect($redirect);
            $Http->request();

            $code = $Http->getResponseStatusCode();
        }

        return $Http;
    }
}