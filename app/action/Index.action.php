<?php

class Action_Index extends Ah_Action_Abstract implements Ah_Action_Interface
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

        $View = new View_Template('index', 'html');

        $this->Response->setMimeType(Util_MIME::detectType('html'));
        $this->Response->setStatusCode(200);
        $this->Response->setBody($View->build($root_vars)->render());
    }
}
