<?php

abstract class Action_Abstract
{
    protected
        $Params,
        $Response;

    protected
        $_receive_params,
        $_validate_rule;

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
        $this->Response->send();
    }

    /**
     * passing ( call from Ah_Resolver::internal )
     *
     * @return object $this
     */
    public function passing()
    {
        return $this;
    }

    /**
     * printing ( call from Ah_Resolver::includes )
     *
     * @return string $responseBody
     */
    public function printing()
    {
        return $this->Response->getBody();
    }
}
