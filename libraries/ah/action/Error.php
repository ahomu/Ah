<?php

namespace ah\action;

use ah\action\Base;

class Error extends Base
{
    protected
        $_receive_params = array(
            'exception',
            'path',
            'method',
            'params',
            'final'
        ),
        $_validate_rule = array(
        );

    public function get()
    {
        // TODO issue: JSONでも返せるようにVIEWを利用して，paramsのformatキーまたは，ah\Request::getExtensionを参照する
        $e = $this->Params->get('exception', true);

        $this->Response->setMimeType(\Util_MIME::detectType('html'));

        if ( $e instanceof \ah\exception\NotFound ) {
            $this->Response->setStatusCode(404);
            $this->Response->setBody(
                '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
               .'<html><head>'
               .'<title>404 Not Found</title>'
               .'</head><body>'
               .'<h1>Not Found</h1>'
               .'<p>The requested URL '.$this->Params->get('path').' was not found on this server.</p>'
               .'</body></html>'
            );
        } elseif ( $e instanceof \ah\exception\ClassNotFound ) {
            $this->Response->setStatusCode(500);
            $this->Response->setBody(
                '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
               .'<html><head>'
               .'<title>500 Class Not Found</title>'
               .'</head><body>'
               .'<h1>Class Not Found</h1>'
               .'<p>note : "'.$e->getMessage().'" class is missing.</p>'
               .'</body></html>'
            );
        } elseif ( $e instanceof \ah\exception\MethodNotAllowed ) {
            $this->Response->setStatusCode(405);
            $this->Response->setHeader('Allow', $e->getMessage());
            $this->Response->setBody(
                 '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
                .'<html><head>'
                .'<title>405 Method Not Allowed</title>'
                .'</head><body>'
                .'<h1>Method Not Allowed</h1>'
                .'<p>The requested Method '.$this->Params->get('method').' was not allowed on this resource.</p>'
                .'<p>( note : Allowed methods are "'.$e->getMessage().'". )</p>'
                .'</body></html>'
            );
        } elseif ( $e instanceof \ah\exception\ExecuteNotAllowed ) {
            $this->Response->setStatusCode(503);
            $this->Response->setBody(
                '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
               .'<html><head>'
               .'<title>503 Service Unavailable</title>'
               .'</head><body>'
               .'<h1>Service Unavailable</h1>'
               .'</body></html>'
            );
        } elseif ( $e instanceof \ah\exception\ExtendsRequired ) {
            $this->Response->setStatusCode(503);
            $this->Response->setBody(
                '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
               .'<html><head>'
               .'<title>503 Service Unavailable</title>'
               .'</head><body>'
               .'<h1>Service Unavailable</h1>'
               .'</body></html>'
            );
        } else {
            die(\get_class($e).': '.$e->getMessage());
        }
    }
}
