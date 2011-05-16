<?php

namespace ah\action;

use ah\Params,
    ah\Response,
    ah\Validator;

/**
 * ah\action\Base
 *
 * Actionのベースクラス．
 * すべてのActionは，何らかの形でこのクラスを継承している必要がある．
 *
 * 主にah\Resolverと，Actionのコミュニケーションが実装されている．
 *
 * @package     Ah
 * @subpackage  Action
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
abstract class Base implements Mold
{
    // TODO issue: メソッドごとにparamsとruleを設定できないのを解決する

    /**
     * Actionのパラメーターを保持する
     * @see ah\Params
     * @var object ah\Params
     */
    public $Params             = null;

    /**
     * Actionのレスポンスを保持する
     * @see ah\Response
     * @var object ah\Response
     */
    public $Response           = null;

    /**
     * このActionで使用するバリデーションクラス名
     * Action起動時の自動バリデート時に利用される
     *
     * @var string
     */
    protected $_init_validator = 'ah\Validator';

    /**
     * 各種の最終処理の実行を許可するかどうかの真偽値．
     * falseの場合，最終実行時に例外が投げられる
     *
     * @see ah\action\Base::output()
     * @see ah\action\Base::passing()
     * @see ah\action\Base::printing()
     * @var bool
     */
    protected $_allow_external    = true;
    protected $_allow_internal    = true;
    protected $_allow_includes    = true;

    /**
     * 許可パラメーターのキー名を保持する配列
     * @var array
     */
    protected $_receive_params    = array();

    /**
     * Params初期化時の自動バリデートに利用される，バリデートルール．
     * $_validate_rule[パラメーターキー][バリデートメソッド][バリデート引数]
     * @var array
     */
    protected $_validate_rule     = array();

    /**
     * Actionの想定される取り扱い文字コード
     * @var string
     */
    protected $_default_charset   = 'UTF-8';

    /**
     * コンストラクタ
     *
     * Responseを初期化する．
     *
     * @return void
     */
    public function __construct()
    {
        $this->Response = new Response();
        $this->Response->setCharset($this->_default_charset);
    }

    /**
     * Actionクラス名を返す
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * 与えられたパラメーターと，自身の$_receive_paramsを元に，Paramsを初期化する．
     * 同時に，自身の$_validate_ruleを元に，パラメーターのバリデートを行う．
     *
     * @param array $params
     * @return void
     */
    public function setParams($params)
    {
        $this->Params = new Params($this->_receive_params, $params, $this->_default_charset);

        /**
         * 自動validate
         * 手動の時は，$this->Params->validate($my_rules, new Validator()) としてActionのメインロジック内で実行する
         */
        $this->Params->validate($this->_validate_rule, new $this->_init_validator());
    }

    /**
     * Actionのメイン処理の起動．
     * 指定されたリクエストメソッドと，実際のActionに定義されたメソッドが対応する．
     * 存在しないメソッドがリクエストされた場合は例外 ( 405 Method Not Allowed ) を投げる．
     *
     * @param string $method
     * @return void
     */
    public function execute($method)
    {
        $this->$method();
    }

    /**
     * HTTPレスポンスを送信するActionの最終処理
     *
     * @see ah\Resolver::external()
     * @return object $this ( & send http response )
     */
    public function external()
    {
        $this->Response->send();
        return $this;
    }

    /**
     * 自身のインスタンスを返すActionの最終処理
     *
     * @see ah\Resolver::internal()
     * @return object $this
     */
    public function internal()
    {
        return $this;
    }

    /**
     * レスポンスボディのみを返すActionの最終処理
     *
     * @see ah\Resolver::includes()
     * @return string $responseBody
     */
    public function includes()
    {
        return $this->Response->getBody();
    }

    /**
     * 指定された最終処理の実行が許可されているか調べる．
     * 許可フラグになっているプロパティの真偽値を直接返す
     *
     * @param string $final
     * @return bool
     */
    public function finalyIsAllowed($final)
    {
        $property = '_allow_'.$final;
        return $this->$property;
    }

    /**
     * 指定されたメソッドが存在するか調べる．
     * 存在すればtrueを返し，存在しなければ実行可能な存在するメソッドを配列で返す．
     *
     * @param string $method
     * @return array|bool
     */
    public function methodIsExists($method)
    {
        if ( !method_exists($this, $method) ) {
            $methods = get_class_methods($this);
            $allows  = array();

            if ( in_array('get', $methods) ) $allows[]      = 'get';
            if ( in_array('post', $methods) ) $allows[]     = 'post';
            if ( in_array('put', $methods) ) $allows[]      = 'put';
            if ( in_array('delete', $methods) ) $allows[]   = 'delete';

            return $allows;
        }
        return true;
    }
}
