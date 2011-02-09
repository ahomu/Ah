<?php

/**
 * Ah_Params
 *
 * @package     Ah
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Params
{
    private
        $_allows,
        $_params,
        $_meta;

    /**
     * __construct
     *
     * @param array $allows
     * @param array $params
     * @return void
     */
    public function __construct($allows, $params)
    {
        // TODO exception: $params isHash?
        // TODO exception: $allows, $params are empty?
        if ( !is_array($allows) ) $allows = array();
        if ( !is_array($params) ) $params = array();

        // undefined param's value = null
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
        $charset = mb_internal_encoding();

        if ( is_array($val) ) {
            $val = array_walk_recursive($val, 'escapeParameter', $charset);
        } else {
            $val = escapeParameter($key, $val, $charset);
        }

        return $val;
    }

    /**
     * validate
     *
     * @param array $validate_condition_hash
     * @param object $Validator
     * @return void
     */
    public function validate($rule, Ah_Validator $Validator)
    {
        $this->_meta['validate'] = $Validator->validate($rule, $this->_params);
    }

    /**
     * isValidAll
     *
     * @return boolean
     */
    public function isValidAll()
    {
        // TODO exception: validation not yet
        if ( empty($this->_meta['validate']) ) return true;

        foreach ( $this->_meta['validate'] as $row ) {
            if ( in_array(false, $row) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * isValid
     *
     * @param string $params key
     * @return boolean
     */
    public function isValid($key)
    {
        if ( empty($this->_meta['validate'][$key]) ) return true;

        if ( in_array(false, $this->_meta['validate'][$key]) ) {
            return false;
        }
        return true;
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
