<?php

interface Action_Interface
{
    /**
     * params
     *
     * @param array $params
     * @return chain
     */
    public function params($params);

    /**
     * exectute
     *
     * @param string $method
     * @return chain
     */
    public function execute($method);

    /**
     * output
     *
     * @return void ( send http response )
     */
    public function output();

    /**
     * passing
     *
     * @return object $this
     */
    public function passing();
}