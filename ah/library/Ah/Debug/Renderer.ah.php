<?php

/**
 * Ah_Debug_Renderer
 *
 * @package     Ah
 * @subpackage  Debug
 * @copyright   2010 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Ah_Debug_Renderer
{
    private static
        $_store;

    /**
     * add
     *
     * @param  $ob
     * @return void
     */
    public static function add($ob)
    {
        if ( is_string($ob) ) {
            self::$_store .= $ob;
        }
    }

    /**
     * addOb
     *
     * @return void
     */
    public static function addOb()
    {
        self::$_store .= ob_get_clean();
    }

    /**
     * clean
     *
     * @return string $ob
     */
    public static function clean()
    {
        $ob = self::$_store;
        self::$_store = null;
        return $ob;
    }

    /**
     * dump
     *
     * @return void
     */
    public static function dump()
    {
        $ob = self::clean();

        if ( !empty($ob) ) {
            echo self::getStyle();
            echo '<pre id="ah_debug_trace-log">'.$ob.'</pre>';
        }
    }

    /**
     * getStyle
     *
     * @return string
     */
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
