<?php

class action_hoge_Fuga extends \ah\action\Base
{
    protected
        $_receive_params = array(

        ),
        $_validate_rule = array(

        );

    public function get()
    {
//        var_dump($this->Params->isValid('hoge'));
        $base['hoge'];
        $root_vars = array(
            'title'  => "(´-`)・・・",
            'header' => array(
                            'hogehoge'  => "",
                            'hoge:loop' => array( // is_array
                                                array('hoge1' => '??1-1', 'hoge2' => '??1-2', 'hoge3' => array('child' => 'eiei')), // is_hash
                                                array('hoge1' => '??2-1', 'hoge2' => '??2-2'),
                                                array('hoge1' => '??3-1', 'hoge2' => '??3-2'),
                                            ),
                            ),
            'footer' => array(
                            'fuga1'=> array(), // empty or is_hash
                            'fuga2' => array(),
                            ),
        );

        $View = new \ah\view\Template('index2', 'html');
        $this->Response->setMimeType(Util_MIME::detectType('html'));
        $this->Response->setStatusCode(200);
        $this->Response->setBody($View->build($root_vars)->render());

//        $this->Response->setBody(
//                str_replace('"/themes/', '"http://www.try110.com/themes/',
//                file_get_contents('http://www.try110.com')
//            )
//        );

    }
}
