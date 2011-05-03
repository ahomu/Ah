<?php

namespace ah;

/**
 * ah\Validator
 *
 * ah\Params内で利用されるバリデーションクラス．
 *
 * ah\action\Base::setParams()を通して，
 * ah\Params::validate()から主に呼ばれる．
 *
 * 単体で利用する場合は次のようになる．
 * {{{
 * $Validator = new \ah\Validator();
 * $Validator->validate($my_rule, $params);
 *
 * if ( $Validator->isValidAll() ) {
 *     // OK
 * } else {
 *     // NG
 * }
 * }}}
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
// TODO issue: ユーザー拡張のエンドポイントを考える
class Validator
{
    /**
     * validate実行時に与えられた，元のパラメーター．
     * equalToなど，パラメーターを俯瞰する必要があるときにも利用される．
     * @var array
     */
    private $_rawparams;
    /**
     * validate結果を納める先．
     * $_result[パラメーターキー][バリデートメソッド][(bool)結果]
     * @var array
     */
    private $_result;

    /**
     * 与えられたパラメーターにバリデートルールを適用して，
     * バリデート結果を保持した状態の自身のインスタンスを返す．
     *
     * @see ah\Params::validate()
     * @see ah\action\Base::setParams()
     * @param array $rule
     * @param array $params
     * @return Validator
     */
    public function validate($rule, $params)
    {
        $this->_rawparams = $params;
        $result           = array();

        foreach ( $this->_rawparams as $param => $val ) {
            // $param = paramator name
            // $val   = paramator value

            // ルールの指定がなければtrue
            if ( empty($rule[$param]) ) $result[$param] = array(true);

            foreach ( $rule[$param] as $method => $args_or_method ) {
                if ( is_int($method) ) { 
                    $method = $args_or_method;
                    $args   = array();
                } else {
                    $args   = $args_or_method;
                }
                $result[$param][$method] = $this->fire($method, $val, $args);
            }
        }

        $this->_result = $result;
        return $this;
    }

    /**
     * 指定されたキーのバリデート結果がvalidであればtrueを，
     * そうでない，またはバリデートされていなければfalseを返す．
     *
     * @see ah\Params::isValid()
     * @param string $key
     * @return boolean
     */
    public function isValid($key)
    {
        if ( empty($this->_result[$key]) ) return false;

        if ( in_array(false, $this->_result[$key]) ) {
            return false;
        }
        return true;
    }

    /**
     * バリデート結果がすべてvalidであればtrueを，
     * そうでない，またはバリデート前であればfalseを返す
     *
     * @see ah\Params::isValidAll()
     * @return boolean
     */
    public function isValidAll()
    {
        if ( empty($this->_result) ) return false;

        foreach ( $this->_result as $row ) {
            if ( in_array(false, $row) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * バリデートメソッドを起動して，結果を返す．
     * requiredでなく，パラメーターがnullであればtrueとする．
     *
     * @param string $method
     * @param mixed $val
     * @param array $args
     * @return boolean
     */
    public function fire($method, $val, $args)
    {
        if ( $method !== 'required' && $val === null ) {
            return true;
        }

        // 引数なし
        if ( empty($args) ) {
            return $this->$method($val);
        }

        // 引数あり
        if ( !is_array($args) ) {
            $args = array($args);
        }
        return $this->$method($val, $args);
    }

    /**
     * equal
     *
     * @param mixed $val
     * @param args[0] string $differ
     * @param args[1] bool $strict
     * @return bool
     */
    protected function equal($val, $args)
    {
        // needle?
        if ( empty($args[0]) ) return false;

        // strict?
        if ( !empty($args[1]) && $args[1] === true ) {
            return ($val === $args[0]);
        } else {
            return ($val == $args[0]);
        }
    }

    /**
     * equalTo
     *
     * @param mixed $val
     * @param args[0] string $to
     * @param args[1] bool $strict
     * @return bool
     */
    protected function equalTo($val, $args)
    {
        // needle?
        if ( empty($args[0]) || empty($this->_rawparams[$args[0]]) ) return false;

        // strict?
        if ( !empty($args[1]) && $args[1] === true ) {
            return ($val === $this->_rawparams[$args[0]]);
        } else {
            return ($val == $this->_rawparams[$args[0]]);
        }
    }

    protected function required($val)
    {
        return ( !empty($val) || ('0' === @$val) );
    }

    protected function notNull($val)
    {
        return !is_null($val);
    }

    protected function min($val, $args)
    {
        $min = ($args[0]) ? $args[0] : 0;
        return ($val >= $min);
    }

    protected function max($val, $args)
    {
        $max = ($args[0]) ? $args[0] : 0;
        return ($val <= $max);
    }

    protected function range($val, $args)
    {
        $min = ($args[0]) ? $args[0] : 1;
        $max = ($args[1]) ? $args[1] : 0;
        return in_range($val, $min, $max);
    }

    protected function minLength($val, $args)
    {
        return $this->min(strlen($val), $args);
    }

    protected function maxLength($val, $args)
    {
        return $this->max(strlen($val), $args);
    }

    protected function rangeLength($val, $args)
    {
        return $this->range(strlen($val), $args);
    }

    protected function minByte($val, $args)
    {
        return $this->min(bytelen($val), $args);
    }

    protected function maxByte($val, $args)
    {
        return $this->max(bytelen($val), $args);
    }

    protected function rangeByte($val, $args)
    {
        return $this->range(bytelen($val), $args);
    }

    protected function alpha($val)
    {
        return is_alpha($val);
    }

    protected function digit($val)
    {
        return is_digit($val);
    }

    protected function numeric($val)
    {
        return is_numeric($val);
    }

    protected function alnum($val)
    {
        return is_alnum($val);
    }

    protected function regex($val, $args)
    {
        $regex = ($args[0]) ? $args[0] : '//';
        return preg_match($regex, $val);
    }

    protected function date($val, $args)
    {
        $opt = ($args[0]) ? $args[0] : 'iso';
        return is_date($val, $opt);
    }

}
