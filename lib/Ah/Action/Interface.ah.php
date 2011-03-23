<?php

interface Ah_Action_Interface
{
    /**
     * params
     *
     * @param array $params
     * @return void
     */
    public function params($params);

    /**
     * exectute
     *
     * @param string $method
     * @return void
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

    /**
     * printing
     *
     * @return string $responseBody
     */
    public function printing();
}