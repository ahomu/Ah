<?php

namespace ah\action;

use ah\Params,
    ah\Response,
    ah\Validator,
    ah\exception\MethodNotAllowed,
    ah\exception\ExecuteNotAllowed;

/**
 * ah\action\Base
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
     * protected properties
     *
     * @var Ah_Param    $Params
     * @var Ah_Response $Response
     * @var boolean     $_allow_external
     * @var boolean     $_allow_internal
     * @var boolean     $_allow_includes
     * @var array       $_receive_params
     * @var array       $_validate_rule
     * @var string      $_default_charset
     */
    protected
        $Params             = null,
        $Response           = null,
        $_allow_external    = true,
        $_allow_internal    = true,
        $_allow_includes    = true,
        $_receive_params    = array(),
        $_validate_rule     = array(),
        $_default_charset   = null;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->Response = new Response();
    }

    /**
     * setParams
     *
     * @param array $params
     * @return void
     */
    public function setParams($params)
    {
        $this->Params = new Params($this->_receive_params, $params, $this->_default_charset);

        /**
         * 自動validate
         * 手動の時は，$this->Params->validate($myRules, new Validator()) としてActionのメインロジック内で実行する
         */
        $this->Params->validate($this->_validate_rule, new Validator());
    }

    /**
     * execute
     *
     * @param string $method
     * @return void
     */
    public function execute($method)
    {
        if ( !method_exists($this, $method) ) {
            $methods = get_class_methods($this);
            $allows  = array();

            if ( in_array('get', $methods) ) $allows[]      = 'get';
            if ( in_array('post', $methods) ) $allows[]     = 'post';
            if ( in_array('put', $methods) ) $allows[]      = 'put';
            if ( in_array('delete', $methods) ) $allows[]   = 'delete';

            throw new MethodNotAllowed(strtoupper(implode(', ', $allows)));
        }

        $this->$method();
    }

    /**
     * output ( call from Ah_Resolver::external )
     *
     * @return void ( send http response )
     */
    public function output()
    {
        if ( $this->_allow_external === false ) throw new ExecuteNotAllowed('External call not allowed');

        $this->Response->send();
    }

    /**
     * passing ( call from Ah_Resolver::internal )
     *
     * @return object $this
     */
    public function passing()
    {
        if ( $this->_allow_internal === false ) throw new ExecuteNotAllowed('Internal call not allowed');

        return $this;
    }

    /**
     * printing ( call from Ah_Resolver::includes )
     *
     * @return string $responseBody
     */
    public function printing()
    {
        if ( $this->_allow_includes === false ) throw new ExecuteNotAllowed('Includes call not allowed');

        return $this->Response->getBody();
    }
}