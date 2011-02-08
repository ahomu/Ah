<?php

abstract class Action_Abstract
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
     */
    protected
        $Params             = null,
        $Response           = null,
        $_allow_external    = true,
        $_allow_internal    = true,
        $_allow_includes    = true,
        $_receive_params    = array(),
        $_validate_rule     = array();

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->Response = new Ah_Response();
    }

    /**
     * params
     *
     * @param array $params
     * @return void
     */
    public function params($params)
    {
        $this->Params = new Ah_Params($this->_receive_params, $params);
        $this->Params->validate($this->_validate_rule, Ah_Validator::singleton());
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

            throw new Ah_Exception_MethodNotAllowed(strtoupper(implode(', ', $allows)));
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
        if ( $this->_allow_external === false ) throw new Exception('External call not allowed');

        $this->Response->send();
    }

    /**
     * passing ( call from Ah_Resolver::internal )
     *
     * @return object $this
     */
    public function passing()
    {
        if ( $this->_allow_internal === false ) throw new Exception('Internal call not allowed');

        return $this;
    }

    /**
     * printing ( call from Ah_Resolver::includes )
     *
     * @return string $responseBody
     */
    public function printing()
    {
        if ( $this->_allow_includes === false ) throw new Exception('Includes call not allowed');

        return $this->Response->getBody();
    }
}
