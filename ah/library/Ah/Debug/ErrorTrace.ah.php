<?php
/**
 * Ah_Debug_ErrorTrace
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Debug_ErrorTrace
{
    private static
        $_store;

    public static function ready()
    {
        Ah_Event_Helper::getDispatcher()->listen('error.regular', array('Ah_Debug_ErrorTrace', 'regular'));
        Ah_Event_Helper::getDispatcher()->listen('app.shutdown', array('Ah_Debug_ErrorTrace', 'fatal'));
    }

    public static function regular($errorInfo)
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

        Ah_Debug_ErrorTrace::stack($ob);
    }

    public static function fatal()
    {
        Ah_Debug_ErrorTrace::stack(ob_get_clean());
        Ah_Debug_ErrorTrace::dump();
    }

    public static function stack($ob)
    {
        Ah_Debug_ErrorTrace::$_store .= $ob;
    }

    public static function clean()
    {
        $ob = Ah_Debug_ErrorTrace::$_store;
        Ah_Debug_ErrorTrace::$_store = null;
        return $ob;
    }

    public static function dump()
    {
        $ob = Ah_Debug_ErrorTrace::clean();
        if ( !empty($ob) ) {
            echo Ah_Debug_ErrorTrace::getStyle();
            echo '<pre id="ah_debug_trace-log">'.$ob.'</pre>';
        }
    }
    public static function getStyle()
    {
return  <<< DOC_END

<style type="text/css">
pre#ah_debug_trace-log {
    font-size: 13px;
    color: lightgreen;
    padding: 10px;
    background-color: #333;
}
table.ah_debug_trace-log_stack {
    margin: 10px 0 20px;
    border: 1px solid white;
    border-collapse: collapse;
}
table.ah_debug_trace-log_stack th {
    letter-spacing: 1px;
    color: yellow;
    padding: 2px 5px;
    text-align: center;
    background-color: #222;
}
table.ah_debug_trace-log_stack td {
    color: white;
    padding: 2px 5px;
    text-align: left;
}
table.ah_debug_trace-log_stack tr {
    border-bottom: 1px solid #666;
}
</style>

DOC_END;
    }
}