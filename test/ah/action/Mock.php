<?php

namespace app\action;

class Mock extends \ah\action\Base
{
    protected $_receive_params = array(
        'foo', 'bar'
    );

    public function setParams($params)
    {
        parent::setParams($params);
    }

    public function get()
    {
        $this->Response->setBody('Dummy Response Body');
    }
}
