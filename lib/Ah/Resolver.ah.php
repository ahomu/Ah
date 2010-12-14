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

        self::_run($path, $method, $params, 'output');
    }

    public static function internal($path, $method, $params = array())
    {
        return self::_run($path, $method, $params, 'passing');
    }

    public static function redirect($path)
    {
        if ( !preg_match('/^https?:\/\//', $path) ) {
            $path = (ENABLE_SSL ? 'https://' : 'http://').REQUEST_HOST.$path;
        }

        $Res = HTTP_Response::getInstance();
        $Res->setStatusCode(303);
        $Res->setLocation($path);
        $Res->setBody(null);
        $Res->send();
    }

    private static function _run($path, $method, $params, $final)
    {
        try
        {
            $method = strtolower($method);
            $Action = Ah_Resolver::_actionDispatcher($path);
            return $Action->params($params)->execute($method)->$final();
        }
        catch ( Ah_Exception_MethodNotAllowed $e )
        {
            header('HTTP/1.1 405 Method Not Allowed');
            header('Content-type: text/html; charset=iso-8859-1');
            header('Allow: '.$e->getMessage());
            die(''
                .'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
                .'<html><head>'
                .'<title>405 Method Not Allowed</title>'
                .'</head><body>'
                .'<h1>Method Not Allowed</h1>'
                .'<p>The requested Method '.$method.' was not allowed on this resource.</p>'
                .'<p>( note : Allowed methods are "'.$e->getMessage().'". )</p>'
                .'</body></html>'
            );
        }
        catch ( Ah_Exception_NotFound $e )
        {
            header('HTTP/1.1 404 Not Found');
            header('Content-type: text/html; charset=iso-8859-1');
            die(''
                .'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
                .'<html><head>'
                .'<title>404 Not Found</title>'
                .'</head><body>'
                .'<h1>Not Found</h1>'
                .'<p>The requested URL '.$path.' was not found on this server.</p>'
                .'<p>( note : "'.$e->getMessage().'" class file is missing. )</p>'
                .'</body></html>'
            );
        }
    }

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
        return $Action;
    }

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
