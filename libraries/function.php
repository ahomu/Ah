<?php

/**
 * array_clean
 * http://d.hatena.ne.jp/H6K/20090601/p1
 *
 * @param array $array
 * @return array
 */
function array_clean($array)
{
    return array_merge(array_diff($array, array('')));
}

/**
 * array_average
 *
 * @param array $array
 * @return float
 */
function array_average($array) {
    return array_sum($array)/count($array);
}

/**
 * is_hash
 * http://d.hatena.ne.jp/fbis/20091112/1258002754
 *
 * @param array $array
 * @return boolean
 */
function is_hash($array)
{
    $i = 0;
    foreach ( $array as $k => $dummy ) {
        if ( $k !== $i++ ) return true;
    }
    return false;
}

/**
 * is_hash_lite
 * http://d.hatena.ne.jp/fbis/20091112/1258002754
 *
 * @param array $array
 * @return bool
 */
function is_hash_lite($array)
{
    list($k) = reset($array);
    return $k !== 0;
}

/**
 * is_date
 * http://www.php.net/manual/ja/function.checkdate.php#89773
 *
 * @param string $date
 * @param string $opt
 * @return boolean
 */
function is_date($date, $opt)
{
    $date   = str_replace(array('\'', '-', '.', ',', ' '), '/', $date);
    $dates  = array_clean(explode('/', $date));

    if ( count($dates) != 3 ) return false;

    switch ( $opt ) {
        case 'iso'  :
            $year   = $dates[0];
            $month  = $dates[1];
            $day    = $dates[2];
        break;

        case 'usa'  :
            $year   = $dates[2];
            $month  = $dates[0];
            $day    = $dates[1];
        break;

        case 'eng'  :
            $year   = $dates[2];
            $month  = $dates[1];
            $day    = $dates[0];
        break;

        default     :
            return false;
    }

    if ( !is_numeric($month) || !is_numeric($day) || !is_numeric($year) ) {
        return false;
    } elseif ( !checkdate($month, $day, $year) ) {
        return false;
    } else {
        return true;
    }
}

/**
 * is_alpha
 *
 * @param string $string
 * @return boolean
 */
function is_alpha($string)
{
    if ( function_exists('ctype_alpha') ) return ctype_alpha(strval($string));
    return (bool) preg_match('/^[a-zA-Z]+$/', $string);
}

/**
 * is_alnum
 *
 * @param string $string
 * @return boolean
 */
function is_alnum($string)
{
    if ( function_exists('ctype_alnum') ) return ctype_alnum(strval($string));
    return (bool) preg_match('/^[a-zA-Z0-9]+$/', $string);
}

/**
 * in_range
 *
 * @param int $i
 * @param int $min
 * @param int $max
 * @return boolean
 */
function in_range($i, $min, $max)
{
    return ($i >= $min) && ($i <= $max);
}

/**
 * is_serialized
 * http://www.weberdev.com/get_example-4099.html
 *
 * @param string $val
 * @return bool
 */
function is_serialized($val) {
    if ( !is_string($val) ) { return false; }
    if ( trim($val) == "" ) { return false; }
    if ( preg_match("/^(i|s|a|o|d):(.*);/si",$val) !== false ) { return true; }
    return false;
}

/**
 * is_closure
 *
 * @param Closure $func
 * @return bool
 */
function is_closure($func) {
    return (is_object($func) && is_callable($func));
}

/**
 * bytelen
 * http://zombiebook.seesaa.net/article/33192046.html
 *
 * @param mixed $data
 * @return int $length
 */
function bytelen($data)
{
    return strlen(bin2hex($data)) / 2;
}

/**
 * getUnique
 *
 * @param int $length
 * @param string $cdStr
 * @return string $unique
 */
function getUnique($length, $cdStr = null)
{
    srand( (double)microtime() * 19850826 );
    if ( empty($cdStr) ) {
        $cdStr = 'abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ2345679';
    }
    $cdGem = preg_split('//', $cdStr, 0, PREG_SPLIT_NO_EMPTY);

    $unique = null;
    for ( $i = 0; $i < $length; $i++ ) {
        $unique .= $cdGem[array_rand($cdGem, 1)];
    }

    return $unique;
}

/**
 * startsWith
 * http://blog.anoncom.net/2009/02/20/124.html
 *
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function startsWith($haystack, $needle)
{
    return strpos($haystack, $needle, 0) === 0;
}

/**
 * endsWith
 * http://blog.anoncom.net/2009/02/20/124.html
 * 
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function endsWith($haystack, $needle)
{
    $length = (strlen($haystack) - strlen($needle));
    if( $length <0) return false;
    return strpos($haystack, $needle, $length) !== false;
}

/**
 * matchesIn
 * http://blog.anoncom.net/2009/02/20/124.html
 * 
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function matchesIn($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
} 

/**
 * checkEncoding ( with array_walk_recursive )
 *
 * @param mixed $key
 * @param mixed $val
 * @param string $charset
 * @return void
 */
function checkEncoding(&$key, &$val, $charset = 'UTF-8')
{
    $key = mb_check_encoding($key, $charset) ? $key : ($charset === 'UTF-8' ? "\xEF\xBF\xBD" : '?');
    $val = mb_check_encoding($val, $charset) ? $val : ($charset === 'UTF-8' ? "\xEF\xBF\xBD" : '?');
}

/**
 * escapeParameter ( with array_walk_recursive )
 *
 * @param mixed $key
 * @param mixed $val
 * @param string $charset
 * @return void
 */
function escapeParameter(&$key, &$val, $charset = 'UTF-8')
{
    $key = htmlspecialchars($key, ENT_QUOTES, $charset);
    $val = htmlspecialchars($val, ENT_QUOTES, $charset);
}

/**
 * removeBreak
 *
 * @param string $str
 * @return void
 */
function removeBreak(&$str)
{
    $str = str_replace(array("\x0D", "\x0A"), '', $str);
}

/**
 * overwrite json decode & encode functions.
 */
if ( !function_exists('json_decode') ) {
    function json_decode($content, $assoc=false)
    {
        if ( $assoc ){
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON();
        }
        return $json->decode($content);
    }
}
if ( !function_exists('json_encode') ) {
    function json_encode($content)
    {
        $json = new Services_JSON();
        return $json->encode($content);
    }
}

