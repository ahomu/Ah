<?php
/**
 * Ah_Debug_Tracer
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Debug_Tracer
{
    /**
     * regularError
     *
     * @param array $errorInfo
     * @return void
     */
    public static function regularError($errorInfo)
    {
        list($errno, $errstr, $errfile, $errline, $stacks) = $errorInfo;

        $ob ="$errno $errstr in $errfile on line $errline";

        $ob.='<table class="ah_debug_trace-log_stack">';
        $ob.='<tr><th>class</th><th>function</th><th>file (line)</th></tr>';
        foreach ( $stacks as $stack ) {
            if ( empty($stack['class']) ) $stack['class'] = '-';

            extract($stack, EXTR_PREFIX_ALL, '');
            $ob.="<tr><td>$_class</td><td>$_function</td><td>$_file ($_line)</td></tr>";
        }
        $ob.= '</table>';

        Ah_Debug_Renderer::add($ob);
    }
}