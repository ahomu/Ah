<?php

abstract class Action_Abstract
{
    protected
        $View,
        $Params,
        $Response;

    protected
        $_receive_params,
        $_validate_condition;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->Response = HTTP_Response::getInstance();
    }

    /**
     * __call
     *
     * @return void
     */
    public function __call($methodName, $args)
    {
        
    }

    /**
     * params
     *
     * @param array $params
     * @return chain
     */
    public function params($params)
    {
        $this->Params = new Ah_Params($this->_receive_params, $params);
        $this->Params->validate($this->_validate_condition, Ah_Validator::getInstance());
        return $this;
    }

    /**
     * execute
     *
     * @return chain
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
        return $this->$method();
    }

    /**
     * output
     *
     * @return void
     */
    public function output()
    {
        if ( $this->View !== null ) {
            $this->Response->send($this->View->render());
        } else {
            $this->Response->send();
        }
    }

    /**
     * passing
     *
     * @return object $this
     */
    public function passing()
    {
        return $this;
    }
}
