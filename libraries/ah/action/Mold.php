<?php

namespace ah\action;

/**
 * ah\action\Mold
 *
 * @package     Ah
 * @subpackage  Action
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
interface Mold
{
    public function setParams($params);

    public function execute($method);

    public function output();

    public function passing();

    public function printing();
}