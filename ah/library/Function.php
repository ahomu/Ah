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
 * is_digit
 *
 * @param string $string
 * @return boolean
 */
function is_digit($string)
{
    if ( function_exists('ctype_digit') ) return ctype_digit(strval($string));
    return (bool) preg_match('/^[0-9]+$/', $string);
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
 * bytelen
 * http://zombiebook.seesaa.net/article/33192046.html
 *
 * @param mixed $data
 * @retrn int $length
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
 * @overwrite json decode & encode functions.
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

/**
 * fixEncodingAssoc
 *
 * @param mixed $val
 * @return void
 */
if ( !function_exists('fixEncodingAssoc') ) {
    function fixEncodingAssoc(& $val)
    {
        if ( is_array($val) ) {
            array_walk($val, 'fixEncodingAssoc');
        } else {
            if ( get_magic_quotes_gpc() ) $val = stripslashes($val);
            if ( !!($enc = mb_detect_encoding($val, 'UTF-8, EUC-JP, SJIS-win')) ) {
                $val    = mb_convert_encoding($val, 'UTF-8', $enc);
            }
        }
        return true;
    }
}

/**
 * inputEscapingAssoc
 *
 * @param mixed $val
 * @return void
 */
if ( !function_exists('inputEscapingAssoc') ) {
    function inputEscapingAssoc(& $val)
    {
        if ( is_array($val) ) {
            array_walk($val, 'inputEscapingAssoc');
        } else {
            $val    = htmlentities($val, ENT_QUOTES, 'UTF-8');
        }
        return true;
    }
}
