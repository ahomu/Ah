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

    public function external();

    public function internal();

    public function includes();

    public function finalyIsAllowed($final);

    public function methodIsExists($method);
}