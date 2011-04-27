<?php

/**
 * Util_MIME
 *
 * @package     Util
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Util_MIME
{
    protected static $INSTANCE;

    /**
     * detectMimeType
     *
     * @param string $extention
     * @return string $mimetype
     */
    public static function detectType($extention)
    {
        if ( !isset(Util_MIME::$INSTANCE) ) {
            Util_MIME::$INSTANCE = new Util_MIME();
        }
        // TODO exception: extention is not found
        return Util_MIME::$INSTANCE->$extention;
    }

    private
        $atom   = 'application/atom+xml',
        $ecma   = 'application/ecmascript',
        $js     = 'application/javascript',
        $json   = 'application/json',
        $mp4s   = 'application/mp4',
        $doc    = 'application/msword',
        $dot    = 'application/msword',
        $pdf    = 'application/pdf',
        $ai     = 'application/postscript',
        $eps    = 'application/postscript',
        $ps     = 'application/postscript',
        $rdf    = 'application/rdf+xml',
        $rsd    = 'application/rsd+xml',
        $rss    = 'application/rss+xml',
        $rtf    = 'application/rtf',
        $sbml   = 'application/sbml+xml',
        $shf    = 'application/shf+xml',
        $smi    = 'application/smil+xml',
        $smil   = 'application/smil+xml',
        $grxml  = 'application/srgs+xml',
        $ssml   = 'application/ssml+xml',
        $curl   = 'application/vnd.curl',
        $xml    = 'application/xml',
        $xsl    = 'application/xml',
        $dtd    = 'application/xml-dtd',
        $xslt   = 'application/xslt+xml',
        $xspf   = 'application/xspf+xml',
        $mxml   = 'application/xv+xml',
        $xhvml  = 'application/xv+xml',
        $xvml   = 'application/xv+xml',
        $xvm    = 'application/xv+xml',
        $zip    = 'application/zip',
        $xls    = 'application/vnd.ms-excel',
        $xlm    = 'application/vnd.ms-excel',
        $xla    = 'application/vnd.ms-excel',
        $xlc    = 'application/vnd.ms-excel',
        $xlt    = 'application/vnd.ms-excel',
        $xlw    = 'application/vnd.ms-excel',
        $bmp    = 'image/bmp',
        $g3     = 'image/g3fax',
        $gif    = 'image/gif',
        $jpeg   = 'image/jpeg',
        $jpg    = 'image/jpeg',
        $jpe    = 'image/jpeg',
        $png    = 'image/png',
        $svg    = 'image/svg+xml',
        $svgz   = 'image/svg+xml',
        $tiff   = 'image/tiff',
        $tif    = 'image/tiff',
        $psd    = 'image/vnd.adobe.photoshop',
        $ico    = 'image/x-icon',
        $pic    = 'image/x-pict',
        $pct    = 'image/x-pict',
        $rgb    = 'image/x-rgb',
        $ics    = 'text/calendar',
        $ifb    = 'text/calendar',
        $css    = 'text/css',
        $csv    = 'text/csv',
        $html   = 'text/html',
        $htm    = 'text/html',
        $txt    = 'text/plain',
        $text   = 'text/plain',
        $conf   = 'text/plain',
        $def    = 'text/plain',
        $list   = 'text/plain',
        $log    = 'text/plain',
        $in     = 'text/plain',
        $sgml   = 'text/sgml',
        $sgm    = 'text/sgml',
        $uri    = 'text/uri-list',
        $uris   = 'text/uri-list',
        $urls   = 'text/uri-list',
        $java   = 'text/x-java-source',
        $manifest= 'text/cache-manifest';
}