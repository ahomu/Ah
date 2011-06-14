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

            // ルールの指定がなければtrue
            if ( empty($rule[$param]) ) {
                $result[$param] = array(true);
                continue;
            }

            // 配列にfix
            $paramRule = is_array($rule[$param]) ? $rule[$param] : array($rule[$param]);

            foreach ( $paramRule as $maybe_method => $args_or_method ) {
                // keyがintであれば，単純配列なので，メソッドはvalとする
                if ( is_int($maybe_method) ) {
                    $method = $args_or_method;
                    $args   = array();
                } else {
                // そうでなければ，連想配列なので，keyをメソッド，valを引数とする
                    $method = $maybe_method;
                    // 配列にfix
                    $args   = is_array($args_or_method) ? $args_or_method : array($args_or_method);
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
     * バリデート結果を返す
     * isValidAllでなかったときの，エラー詳細用
     *
     * @param string $key
     * @return array
     */
    public function getResults($key = null)
    {
        if ( $key !== null ) {
            return $this->_result[$key];
        } else {
            return $this->_result;
        }
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

        // 引数あり
        if ( !is_array($args) ) {
            $args = array($args);
        }

        return static::$method($val, $args, $this);
    }

    /**
     * equal
     *
     * @param mixed $val
     * @param array $args ( [0]string $differ, [1]bool $strict )
     * @return bool
     */
    public static function equal($val, $args)
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
     * @param array $args ( [0]string $to, [1]bool $strict )
     * @param ah\Validator $that
     * @return bool
     */
    public static function equalTo($val, $args, $that)
    {
        // needle?
        if ( empty($args[0]) || empty($that->_rawparams[$args[0]]) ) return false;

        // strict?
        if ( !empty($args[1]) && $args[1] === true ) {
            return ($val === $that->_rawparams[$args[0]]);
        } else {
            return ($val == $that->_rawparams[$args[0]]);
        }
    }

    public static function required($val)
    {
        return ( !empty($val) || ('0' === @$val) );
    }

    public static function notEmpty($val)
    {
        return !empty($val);
    }

    public static function min($val, $args)
    {
        $min = isset($args[0]) ? $args[0] : 0;
        return ($val >= $min);
    }

    public static function max($val, $args)
    {
        $max = isset($args[0]) ? $args[0] : 0;
        return ($val <= $max);
    }

    public static function range($val, $args)
    {
        $min = isset($args[0]) ? $args[0] : 1;
        $max = isset($args[1]) ? $args[1] : 0;
        return in_range($val, $min, $max);
    }

    public static function minLength($val, $args)
    {
        return self::min(strlen($val), $args);
    }

    public static function maxLength($val, $args)
    {
        return self::max(strlen($val), $args);
    }

    public static function rangeLength($val, $args)
    {
        return self::range(strlen($val), $args);
    }

    public static function minByte($val, $args)
    {
        return self::min(bytelen($val), $args);
    }

    public static function maxByte($val, $args)
    {
        return self::max(bytelen($val), $args);
    }

    public static function rangeByte($val, $args)
    {
        return self::range(bytelen($val), $args);
    }

    public static function int($val)
    {
        return is_int($val);
    }

    public static function float($val)
    {
        return is_float($val);
    }

    public static function alpha($val)
    {
        return is_alpha($val);
    }

    public static function numeric($val)
    {
        return is_numeric($val);
    }

    public static function alnum($val)
    {
        return is_alnum($val);
    }

    public static function regex($val, $args)
    {
        $regex = ($args[0]) ? $args[0] : '//';
        return (bool) preg_match($regex, $val);
    }

    public static function date($val, $args)
    {
        $opt = isset($args[0]) ? $args[0] : 'iso';
        return is_date($val, $opt);
    }

}
