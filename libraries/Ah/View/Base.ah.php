<?php

namespace Ah\View;

abstract class Base
{
    /**
     * __construct
     *
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