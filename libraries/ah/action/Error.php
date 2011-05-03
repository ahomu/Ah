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
               .'<p>( note : "'.$e->getMessage().'" class file is missing. )</p>'
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
        } else {
            die($e->getMessage());
        }
    }
}
