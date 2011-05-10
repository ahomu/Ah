<?php

namespace ah;

use ah\event,
    ah\exception\ExtendsRequired,
    ah\exception\MethodNotAllowed,
    ah\exception\ExecuteNotAllowed;

/**
 * ah\Resolver
 *
 * 各publicメソッドに，リクエストパスとメソッドを与えると，実行すべきActionの探索と実行を行い，
 * HTTPレスポンス・Action自身のインスタンス・Actionのレスポンスボディのいずれかを返す．
 *
 * ah\Resolver::external()は，Actionを解決して，HTTPレスポンスの返却までを行う．
 * {{{
 * // ah\Requestはクライアントからのリクエスト情報を持つ
 * \ah\Resolver::external(\ah\Request::getPath(), \ah\Request::getMethod());
 * }}}
 *
 * ah\Resolver::internal()は，Actionを解決して，処理済みのActionインスタンスを返す．
 * {{{
 * $prams = array(
 *     'foo'  => 'bar',
 *     'hoge' => 'fuga'
 * );
 * $Action = \ah\Resolver::internal('/foo/bar/class', 'GET', $params);
 * }}}
 *
 * ah\Resolver::includes()は，Actionを解決して，Actionの持つレスポンスボディのみを返す．
 * {{{
 * $responsBody = \ah\Resolver::includes('/foo/bar/class', 'GET');
 * }}}
 *
 * ah\Resolver::redirect()は，Actionの解決ではなく，303 See Otherリダイレクトを行う．
 * {{{
 * $prams = array(
 *     'foo'  => 'bar',
 *     'hoge' => 'fuga'
 * );
 * // http://example.com/?foo=bar&hoge=fuga にリダイレクトする
 * \ah\Resolver::redirect('http://example.com/', $params);
 * }}}
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Resolver
{
    /**
     * Actionを解決して，HTTPレスポンスをクライアントに送出する．
     * 引数マップを参照した結果，パラメーターが得られなければGETやPOSTを参照する．
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

        return self::_run($path, $method, $params, __METHOD__);
    }

    /**
     * Actionを解決して，処理済みのActionインスタンスを返す．
     * パラメーターが空のときのみ，引数マップを参照する．
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
        return self::_run($path, $method, $params, __METHOD__);
    }

    /**
     * Actionを解決して，処理済みのActionのレスポンスボディを返す．
     * パラメーターが空のときのみ，引数マップを参照する．
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
        return self::_run($path, $method, $params, __METHOD__);
    }

    /**
     * 指定されたURLにリダイレクト（303 See Other）する．
     * パラメーターが指定された場合は，GETクエリーとしてURLに付与される．
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
     * Actionを処理するメインロジック．
     *
     * 次の順番で，Actionの解決が行われる．
     * 1. Actionの呼び出しと生成
     * 2. 指定された最終処理が許可されているか
     * 3. 指定されたメソッドが存在するか
     * 4. \ah\action\Baseを継承・実装しているか
     * 5. パラメーターのセット
     * 6. Actionのメイン処理の実行
     * 7. Actionの最終処理の実行
     *
     * この処理の最中，次のイベントが配信される．
     * resolver.action_before   : Actionを処理する直前（パラメーターセットと自動バリデートは終わっている）
     * resolver.action_after    : Actionを処理した直後
     *
     * 例外発生時は，ah\action\Errorでレスポンスを処理する．
     *
     * @param string $path
     * @param string $method
     * @param array $params
     * @param string $final
     * @return object
     */
    private static function _run($path, $method, $params, $final)
    {
        try
        {
            $path   = preg_replace('/(\.'.Request::getExtension().')$/', '', $path);
            $method = strtolower($method);
            $final  = substr($final, (strpos($final, '::')+2));
            $Action = self::_actionDispatcher($path);

            if ( !$Action instanceof \ah\action\Base ) {
                throw new ExtendsRequired('Calling '.$Action.' class does not extend \ah\action\Base.');
            }

            $allowed = $Action->methodIsExists($method);
            if ( $allowed !== true ) {
                throw new MethodNotAllowed(strtoupper(implode(', ', $allowed)));
            }

            if ( !$Action->finalyIsAllowed($final) ) {
                throw new ExecuteNotAllowed($final.' call not allowed');
            }

            $Action->setParams($params);

            event\Helper::getDispatcher()->notify(new event\Subject($Action, 'resolver.action_before'));

            $Action->execute($method);

            event\Helper::getDispatcher()->notify(new event\Subject($Action, 'resolver.action_after'));

            return $Action->$final();
        }
        catch ( \Exception $e )
        {
            $Error = new action\Error();
            $Error->setParams(array(
                'exception' => $e,
                'path'      => $path,
                'method'    => $method,
                'params'    => $params,
                'final'     => $final
            ));
            $Error->execute('GET');
            return $Error->external();
        }
    }

    /**
     * 与えられたパスから，起動するアクションのパスを組み立ててインスタンスを返す．
     *
     * @param  $path
     * @return object $Action
     */
    private static function _actionDispatcher($path)
    {
        if ( $path === '/' ) $path = '/index';

        $stacks = array_clean(explode('/', strtolower($path)));
        array_unshift($stacks, '\app\action');
        array_splice($stacks, -1, 1, ucfirst(end($stacks)));

        $actionName = implode('\\', $stacks);

        $Action = new $actionName();

        return $Action;
    }

    /**
     * 設定ファイルから，パスに含まれる引数マップを解決して，
     * パラメーターを連想配列で返す．
     *
     * 単純に前方一致で判定を行っているので，引数部分はパスの後方に寄らなければならない．
     * 引数マップが解決された場合，元のパスは引数部分を除いた状態に書き換えられる．
     *
     * @param string $rawPath
     * @param string $method
     * @return array $args
     */
    private static function _argumentsMapper(& $rawPath, $method)
    {
        $map = Config::load('map', $method);

        if ( !empty($map) ) {
            foreach ( $map as $path => $args ) {
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
        }
        return array();
    }
}
