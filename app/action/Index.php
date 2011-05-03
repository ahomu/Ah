<?php

namespace app\action;

use ah\action\Base;

class Index extends Base
{
    protected
        $_receive_params = array(
        ),
        $_validate_rule = array(
        );

    public function get()
    {
        $root_vars = array(
            'title' => 'Welcome to Ah Frameworks!',
        );

        $View = new \ah\view\Template('index', 'html');

        $this->Response->setMimeType(\Util_MIME::detectType('html'));
        $this->Response->setStatusCode(200);
        $this->Response->setBody($View->build($root_vars)->render());
    }
}
