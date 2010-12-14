<?php

class View_Json extends View_Abstract
{
    private $_json;

    /**
     * build
     *
     * @param  $vars
     * @return void
     */
    public function build($vars)
    {
        $this->_json = json_encode($vars);
    }

    /**
     * render
     * 
     * @return string $responsBody
     */
    public function render()
    {
        return $this->_json;
    }
}