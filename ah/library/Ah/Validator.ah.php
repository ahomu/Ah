<?php

/**
 * Ah_Validator
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
// TODO issue: ユーザー拡張のエンドポイントを考える
class Ah_Validator
{
    private static $INSTANCE;

    public static function singleton()
    {
        if ( self::$INSTANCE === null ) {
            self::$INSTANCE = new self();;
        }
        return self::$INSTANCE;
    }

    private $_temporary;

    /**
     * validate
     *
     * @param array $validate_rule
     * @param array $params
     * @return array $result
     */
    public function validate($rule, $params)
    {
        $this->_temporary = $params;
        $result           = array();

        foreach ( $this->_temporary as $param => $val ) {
            // $param = paramator name
            // $val   = paramator value

            if ( empty($rule[$param]) ) continue;

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
        return $result;
    }

    /**
     * fire
     *
     * @param string $method
     * @param mixed $val
     * @param array $args
     * @return boolean
     */
    public function fire($method, $val, $args)
    {
        if ( $method !== 'required' && $val === null )
            return true;

        if ( empty($args) )
            return $this->$method($val);

        if ( !is_array($args) ) $args = array($args);
            return $this->$method($val, $args);
    }

    /**
     * equal
     *
     * @param mixed $val
     * @param args[0] string $differ
     * @param args[1] boolean $strict
     * @return boolean
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
     * @param args[1] boolean $strict
     * @return boolean
     */
    protected function equalTo($val, $args)
    {
        // needle?
        if ( empty($args[0]) || empty($this->_teporary[$args[0]]) ) return false;

        // strict?
        if ( !empty($args[1]) && $args[1] === true ) {
            return ($val === $this->_teporary[$args[0]]);
        } else {
            return ($val == $this->_teporary[$args[0]]);
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
