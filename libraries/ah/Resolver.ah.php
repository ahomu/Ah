<?php

namespace ah;

use ah\event,
    ah\exception\ExtendsRequired;

/**
 * ah\Resolver
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Resolver
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
        $params = self::_argumentsMapper($path, $method);

        // set params
        if ( empty($params) ) {
            $params = Request::getParams($method);
        }

        return self::_run($path, $method, $params, 'output');
    }

    /**
     * internal - internal action and get executed action instance
     *
     * @param  string $path
     * @param  string $method
     * @param  array $params
     * @return object $Action
     */
    public static function internal($path, $method, $params = array())
    {
        // map args
        if ( empty($params) ) {
            $params = self::_argumentsMapper($path, $method);
        }
        return self::_run($path, $method, $params, 'passing');
    }

    /**
     * includes - internal action and get response body
     *
     * @param  string $path
     * @param  string $method
     * @param  array $params
     * @return string $responseBody
     */
    public static function includes($path, $method, $params = array())
    {
        // map args
        if ( empty($params) ) {
            $params = self::_argumentsMapper($path, $method);
        }
        return self::_run($path, $method, $params, 'includes');
    }

    /**
     * redirect - goto uri
     *
     * @param  string $path path or url
     * @param  array $params GET only
     * @return void
     */
    public static function redirect($path, $params = array())
    {
        if ( !empty($params) ) {
            $path .= '?'.http_build_query($params);
        }
        if ( !preg_match('/^https?:\/\//', $path) ) {
            $host = Request::getHost();
            $path = (Request::isSsl() ? 'https://' : 'http://').$host.$path;
        }

        $Res = new Response();
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
     * @return object|bool
     */
    private static function _run($path, $method, $params, $final)
    {
        // TODO task: 例外時の処理を外に出す
        try
        {
            // resolve variables
            $method = strtolower($method);

            // remove extension
            $path   = preg_replace('/(\.'.Request::getExtension().')$/', '', $path);

            // dispatch Action
            $Action = self::_actionDispatcher($path);

            // set params
            $Action->setParams($params);

            // #EVENT action before
            event\Helper::getDispatcher()->notify(new event\Subject($Action, 'resolver.action_before'));

            // action execute
            $Action->execute($method);

            // #EVENT action after
            event\Helper::getDispatcher()->notify(new event\Subject($Action, 'resolver.action_after'));

            return $Action->$final();
        }
        // TODO issue: Actionの外で投げられる例外のレスポンスボディのデフォルトを定義する(json or html or xmlなど)
        // 各種エラー系ステータスコードのデフォルトテンプレートを各種の形式で用意（集積的なエラーレスポンスメソッド）
        // エラーレスポンスメソッドは，Actionからも利用できるようにする
        // 拡張子が自動決定される際は，Ah_Request::getExtension か Paramsのformatプロパティ が参照される
        // internalのときはpath上の拡張子は利用できない（除去はされるが，Requestクラスに保存されないので，それ以後に参照しようがない）
        // internalのときは，安易に例外を投げて内部的な404や405を返していいのか？
        //     -> internalであっても404や405は開発者がつくってる最中に出くわす不注意によるエラーなので止めてok
        catch ( \ah\exception\MethodNotAllowed $e )
        {
            $Res = new Response();
            $Res->setStatusCode(405);
            $Res->setHeader('Allow', $e->getMessage());
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
        catch ( \ah\exception\NotFound $e )
        {
            $Res = new Response();
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
        catch ( \Exception $e )
        {
            die('Unspecified Exception: '.$e->getMessage());
        }

        return false;
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
        $stacks = array('action');

        foreach ( $chunks as $chunk ) {
            if ( $chunk === '' ) continue;
            $stacks[] = ucfirst($chunk);
        }

        if ( count($stacks) === 1 ) $stacks[] = 'Index';
        $actionName = implode('_', $stacks);

        $Action = new $actionName();

        if ( !$Action instanceof \ah\action\Base ) {
            throw new ExtendsRequired('Calling '.$actionName.' class does not extend \ah\action\Base.');
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
        // TODO issue: yamlファイル定義以外のマッピングを考える

        $map = Config::load('map', 'arguments_mapper');
        if ( empty($map[$method]) ) return array();

        foreach ( $map[$method] as $path => $args ) {
            // 前方一致によって判定を行う
            if ( strpos($rawPath, $path) === 0 ) {
                $chunks = explode('/', substr($rawPath, strlen($path)));

                // remove blank
                $chunks = array_clean($chunks);

                // adjust smaller length
                $count  = min(array(count($args), count($chunks)));

                // key
                $args   = array_slice($args, 0, $count);

                // value
                $chunks = array_slice($chunks, 0, $count);

                if ( empty($args) || empty($chunks) ) return array();

                // 元のパスを書き換える
                $rawPath = $path;

                // 切り出したパラメーターを返す
                return array_combine($args, $chunks);
            }
        }
        return array();
    }
}
