<?php

namespace ah\view;

class Json extends Base
{
    private $_json;

    /**
     * build
     *
     * @param  $vars
     * @return \ah\View\Json
     */
    public function build($vars)
    {
        $this->_json = json_encode($vars);
        return $this;
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