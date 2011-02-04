<?php

/**
 * Ah_Resolver provides URI routing and resolve request.
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Resolver
{
    /**
     * external - external action and send response
     *
     * @param  string $path
     * @param  string $method
     * @return void ( send http response )
     */
    public static function external($path, $method)
    {
        // map args
        $params = Ah_Resolver::_argumentsMapper($path, $method);

        // set params
        if ( empty($params) ) {
            switch ( $method ) {
                case 'POST' :
                    $params = $_POST;
                    break;
                case 'GET'  :
                    $params = $_GET;
                    break;
                case 'PUT'  :
                    $params = array(file_get_contents('php://input'));
                    break;
            }
        }

        return self::_run($path, $method, $params, 'output');
    }

    /**
     * internal - internal action and get executed action instance
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @return object $Action
     */
    public static function internal($path, $method, $params = array())
    {
        // map args
        if ( empty($params) ) {
            $params = Ah_Resolver::_argumentsMapper($path, $method);
        }
        return self::_run($path, $method, $params, 'passing');
    }

    /**
     * includes - internal action and get response body
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @return string $responseBody
     */
    public static function includes($path, $method, $params = array())
    {
        // map args
        if ( empty($params) ) {
            $params = Ah_Resolver::_argumentsMapper($path, $method);
        }
        return self::_run($path, $method, $params, 'printing');
    }

    /**
     * redirect - go external uri
     *
     * @param string $path
     * @param array $params get query params
     * @retur ( send http response )n void
     */
    public static function redirect($path, $params = array())
    {
        if ( !empty($params) ) {
            $path .= '?'.http_build_query($params);
        }
        if ( !preg_match('/^https?:\/\//', $path) ) {
            $path = (ENABLE_SSL ? 'https://' : 'http://').REQUEST_HOST.$path;
        }

        $Res = new Ah_Response();
        $Res->setStatusCode(303);
        $Res->setLocation($path);
        $Res->send();
    }

    /**
     * _run - run action
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @param string $final
     * @return object $Action
     */
    private static function _run($path, $method, $params, $final)
    {
        // TODO : 例外時の処理を外に出す
        try
        {
            $method = strtolower($method);
            $Action = Ah_Resolver::_actionDispatcher($path);

            // set params
            $Action->params($params);

            // #EVENT action before
            Ah_Event_Helper::getDispatcher()->notify(new Ah_Event_Subject($Action, 'resolver.action_before'));

            // action execute
            $Action->execute($method);

            // #EVENT action after
            Ah_Event_Helper::getDispatcher()->notify(new Ah_Event_Subject($Action, 'resolver.action_after'));

            return $Action->$final();
        }
        catch ( Ah_Exception_MethodNotAllowed $e )
        {
            $Res = new Ah_Response();
            $Res->setStatusCode(405);
            header('Allow: '.$e->getMessage());
            $Res->setBody(
                 '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
                .'<html><head>'
                .'<title>405 Method Not Allowed</title>'
                .'</head><body>'
                .'<h1>Method Not Allowed</h1>'
                .'<p>The requested Method '.$method.' was not allowed on this resource.</p>'
                .'<p>( note : Allowed methods are "'.$e->getMessage().'". )</p>'
                .'</body></html>'
            );
            $Res->send();
        }
        catch ( Ah_Exception_NotFound $e )
        {
            $Res = new Ah_Response();
            $Res->setStatusCode(404);
            $Res->setBody(
                 '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
                .'<html><head>'
                .'<title>404 Not Found</title>'
                .'</head><body>'
                .'<h1>Not Found</h1>'
                .'<p>The requested URL '.$path.' was not found on this server.</p>'
                .'<p>( note : "'.$e->getMessage().'" class file is missing. )</p>'
                .'</body></html>'
            );
            $Res->send();
        }
        catch ( Exception $e )
        {
            die('Unspecified Exception: '.$e->getMessage());
        }
    }

    /**
     * _actionDispatcher
     *
     * @param  $path
     * @return object $Action
     */
    private static function _actionDispatcher($path)
    {
        $chunks = explode('/', strtolower($path));
        $stacks = array('Action');

        foreach ( $chunks as $chunk ) {
            if ( $chunk === '' ) continue;
            $stacks[] = ucfirst($chunk);
        }

        if ( count($stacks) === 1 ) $stacks[] = 'Index';
        $actionName = implode('_', $stacks);

        $Action = new $actionName();

        if ( !$Action instanceof Action_Interface ) {
            throw new Exception('Calling '.$actionName.' class does not implement Action_Interface.');
        }

        return $Action;
    }

    /**
     * _argumentsMapper
     *
     * @param string $rawPath
     * @param string $method
     * @return array $args
     */
    private static function _argumentsMapper(& $rawPath, $method)
    {
        $map = Ah_Config::load('map', 'arguments_mapper');
        if ( empty($map[$method]) ) return array();

        foreach ( $map[$method] as $path => $args ) {
            if ( strpos($rawPath, $path) === 0 ) {
                $chunks = explode('/', substr($rawPath, strlen($path)));

                // remove blank
                $chunks = array_clean($chunks);

                // adjust samller length
                $count  = min(array(count($args), count($chunks)));

                // key
                $args   = array_slice($args, 0, $count);

                // value
                $chunks = array_slice($chunks, 0, $count);

                if ( empty($args) || empty($chunks) ) return array();

                $rawPath = $path;
                return array_combine($args, $chunks);
            }
        }
        return array();
    }
}
