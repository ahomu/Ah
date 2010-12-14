<?php

abstract class View_Abstract
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * render
     *
     * @return string $responseBody
     */
    abstract public function render();
}