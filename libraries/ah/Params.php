<?php

namespace ah;

/**
 * ah\Params
 *
 * actionで扱われる，パラメーターの管理クラス．
 *
 * ah\action\Base::setParamsメソッドを実行すると，
 * Paramsプロパティに，ah\PAramsのインスタンスを割り当てられる．
 * {{{
 * $this->Params = new \ah\Params($this->_receive_params, $params, $this->_default_charset);
 * }}}
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Params
{
    /**
     * 受け取ってもよいパラメーターキーの配列
     *
     * @var array
     */
    private $_allows;

    /**
     * 実際に保持するパラメーターの連想配列
     *
     * @var array hash
     */
    private $_params;

    /**
     * 処理済みのバリデーター
     * ah\Params::valiate実行後に，処理済みのah\Validatorがセットされる．
     *
     * @see ah\Validator
     * @see ah\Params\validate()
     * @see ah\Params\isValid()
     * @see ah\Params\isValidAll()
     * @var object ah\Validator
     */
    private $_Validator;

    /**
     * パラメーターの想定される文字コードの指定
     *
     * @var string
     */
    private $_charset;

    /**
     * コンストラクタ
     *
     * ah\action\Base::setParams()でParamsが初期化された時点で，
     * 許可パラメーターの選別・文字コードのチェックが行われる.
     * ※setParams内では，続けてバリデートも自動実行される．
     *
     * @see ah\action\Base::setParams()
     * @param array $allows
     * @param array $params
     * @param null|string $charset
     */
    public function __construct($allows, $params, $charset = null)
    {
        if ( !is_array($allows) ) $allows = array();
        if ( !is_array($params) ) $params = array();

        // 初期化時に，文字コードをチェックする
        // TODO issue: todo 文字コードが不正だったときには，何らかの定数をセットするほうがよい? ハテナに置き換える?
        $this->_charset = $charset !== null ? $charset : mb_internal_encoding();
        array_walk_recursive($params, 'checkEncoding', $this->_charset);

        // 未定義のパラメーターには，nullをセットする
        $this->_allows = $allows;
        $this->_params = array();
        foreach ( $this->_allows as $key ) {
            $this->_params[$key] = isset($params[$key]) ? $params[$key] : null;
        }
    }

    /**
     * パラメーターの値を書き換える．
     * 許可されているキーであれば，書き換えてtrueを返す．
     * 許可されていないキーであれば，falseを返す．
     *
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function set($key, $val)
    {
        if ( in_array($key, $this->_allows) ) {
            $this->_params[$key] = $val;
            return true;
        } else {
            return false;
        }
    }

    /**
     * パラメーターの値を取得する．
     * 許可されていないキーであれば，falseを返す．
     * rawパラメーターがtrueの場合は，そのままの値を返すが，
     * 通常はhtmlspecialcharsをかけた状態にして値を返す．
     *
     * @todo TODO: 許可されていないキーへのアクセスは，falseでなく何らかの定数を返した方がよい
     *
     * @param string $key
     * @param bool $raw
     * @return mixed
     */
    public function get($key, $raw = false)
    {
        // undefined
        if ( !in_array($key, $this->_allows) ) return false;

        $val = $this->_params[$key];

        if ( $raw === true ) return $val;

        // escape
        if ( is_array($val) ) {
            array_walk_recursive($val, 'escapeParameter', $this->_charset);
        } else {
            escapeParameter($key, $val, $this->_charset);
        }

        return $val;
    }

    /**
     * パラメーターのバリデーション．
     *
     * ah\action\Base::setParams()で，Paramsが初期化された後に，
     * 自動でこのメソッドが実行される．
     *
     * 自動バリデート後に独自のルールで，バリデートを適用し直したいときは
     * action内で次のように，validateメソッドを実行し直す．
     * {{{
     * $this->Params->validate($my_rules, new \ah\Validator());
     * }}}
     *
     * @see ah\action\Base::setParams()
     * @param array $rule
     * @param Validator $Validator
     * @return void
     */
    public function validate($rule, Validator $Validator)
    {
        $this->_Validator = $Validator->validate($rule, $this->_params);
    }

    /**
     * 直近のバリデート結果が，すべてvalidであればtrue，
     * そうでなければfalseを返す．
     *
     * @return boolean
     */
    public function isValidAll()
    {
        return $this->_Validator->isValidAll();
    }

    /** 
     * 指定したキーの直近のバリデート結果が，validであればtrue,
     * そうでなければfalseを返す．
     *
     * @param string $key
     * @return boolean
     */
    public function isValid($key)
    {
        return $this->_Validator->isValid($key);
    }

    /**
     * バリデート結果を返す
     * isValidAllでなかったときの，エラー詳細用
     *
     * @param string $key
     * @return array
     */
    public function getResults($key = null)
    {
        return $this->_Validator->getResults($key);
    }

    /**
     * パラメーターを単純な連想配列として取得する．
     *
     * @return mixed array|null
     */
    public function toArray()
    {
        return $this->_params;
    }
}
