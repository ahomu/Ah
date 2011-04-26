<?php

namespace Ah\View;

/**
 * Base
 *
 * @package     Ah
 * @subpackage  View
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
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