<?php

/**
 * Ah_Resolver
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Resolver
{
    /**
     * external
     *
     * @param  string $path
     * @param  string $method
     * @return voi ( send http response )d
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
     * internal
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
     * includes
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
     * partial - get internal uri content
     *
     * @param string $path
     * @return string $staticStrings
     */
    public static function partial($path)
    {
        return '';
    }

    /**
     * redirect - go external uri content
     *
     * @param string $path
     * @retur ( send http response )n void
     */
    public static function redirect($path)
    {
        if ( !preg_match('/^https?:\/\//', $path) ) {
            $path = (ENABLE_SSL ? 'https://' : 'http://').REQUEST_HOST.$path;
        }

        $Res = new Ah_Response();
        $Res->setStatusCode(303);
        $Res->setLocation($path);
        $Res->send();
    }

    /**
     * _run
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @param string $final
     * @return object $Action
     */
    private static function _run($path, $method, $params, $final)
    {
        try
        {
            $method = strtolower($method);
            $Action = Ah_Resolver::_actionDispatcher($path);

            $Action->params($params);
            return $Action->execute($method)->$final();
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
            if ( empty($chunk) ) continue;
            $stacks[]   = ucwords($chunk);
        }

        if ( count($stacks) === 1 ) $stacks[] = 'Index';
        $actionName = implode('_', $stacks);

        class_exists($actionName);
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

                // スラッシュで生まれる空白を詰める
                $chunks = array_clean($chunks);

                // 小さい方の数を選択
                $count  = min(array(count($args), count($chunks)));

                // 選択数の分だけ切り出す
                $chunks = array_slice($chunks, 0, $count);
                $args   = array_slice($args, 0, $count);

                if ( empty($args) || empty($chunks) ) return array();

                $rawPath = $path;
                return array_combine($args, $chunks);
            }
        }
        return array();
    }
}
