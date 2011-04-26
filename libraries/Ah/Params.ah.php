<?php

namespace Ah;

/**
 * Ah\Params
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Params
{
    private
        $_allows,
        $_params,
        $_validator,
        $_charset;

    /**
     * __construct
     *
     * @param array $allows
     * @param array $params
     * @param null|string $charset
     */
    public function __construct($allows, $params, $charset = null)
    {
        if ( !is_array($allows) ) $allows = array();
        if ( !is_array($params) ) $params = array();

        // 初期化時に，文字コードをチェックする
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
     * set
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
     * get
     *
     * @param string $key
     * @param bool $raw
     * @return mixed
     */
    public function get($key, $raw = false)
    {
        // undefined
        if ( !in_array($key, $this->_allows) ) return false;

        // temporary
        $val = $this->_params[$key];

        // raw value
        if ( $raw === true ) return $val;

        // safety value
        if ( is_array($val) ) {
            $val = array_walk_recursive($val, 'escapeParameter', $this->_charset);
        } else {
            $val = escapeParameter($key, $val, $this->_charset);
        }

        return $val;
    }

    /**
     * validate
     *
     * @param array $rule
     * @param Ah_Validator $Validator
     * @return void
     */
    public function validate($rule, Validator $Validator)
    {
        $this->_validator = $Validator->validate($rule, $this->_params);
    }

    /**
     * isValidAll
     *
     * @return boolean
     */
    public function isValidAll()
    {
        return $this->_validator->isValidAll();
    }

    /**
     * isValid
     *
     * @param string $key
     * @return boolean
     */
    public function isValid($key)
    {
        return $this->_validator->isValid($key);
    }

    /**
     * toArray
     *
     * @return mixed array|null
     */
    public function toArray()
    {
        return $this->_params;
    }
}
