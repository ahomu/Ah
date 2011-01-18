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
function is_hash(& $array, $strict = false)
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
    if ( function_exists('ctype_alpha') ) return ctype_alpha($string);
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
    for ( $i = 0; $i < $cdLen; $i++ ) {
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
function startsWith($haystack, $needle){
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
function endsWith($haystack, $needle){
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
function matchesIn($haystack, $needle){
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

/**
 * d ( var_html )
 * http://zombiebook.seesaa.net/article/112484084.html
 *
 * @param mixed $obj
 * @param boolean $get
 * @return output|string
 */
function d($obj, $get = false){
  $space = ' ';
  $indent = '　';
  $return = "<br />";
  ob_start();
  var_dump($obj);
  $data = ob_get_clean();
  $data = trim($data);
  // string データ部を html エンティティに変換
  $pos = -1;
  while(($pos = strpos($data, 'string', $pos + 1)) !== false){
    // string の前方
    $tx1 = substr($data, 0, $pos);
    // string 以降
    $txx = substr($data, $pos);
    // string 以降から細部切出
    preg_match("/^(string\((\d+)\)\s\")([^\x1b]*)/", $txx, $mts);
    // string データ長
    $len = $mts[2];
    // string からデータ直前まで
    $tx2 = "string($len) " . '"';
    // string データ部
    $tx3 = substr($mts[3], 0, $mts[2]);
    // string データの後方
    $tx4 = substr($mts[3], $mts[2]);
    // データ部の変換
    $txm = str_replace(
      array("\n", ' '),
      array("\\n", ' '),
      htmlentities(
        $tx3,
        ENT_QUOTES,
        mb_internal_encoding()
      )
    );
    $data = $tx1 . $tx2 . $txm . $tx4;
    // string 検索開始位置更新
    $pos = $pos + (strlen(bin2hex($tx2 . $txm)) / 2);
  }
  // 以下可視性アップ
  // string データ部 \n を変換しないと string に騙される
  // 循環参照部分のチェック
  $data = preg_replace(
    "/(\n\s+)(\*RECURSION\*)(\n)/",
    '${1}<b style="color:red;">${2}</b>${3}',
    $data
  );
  // 参照渡し部分とオブジェクトの可視性を上げる
  $data = preg_replace(
    "/(&?)(array)(\(\d+\)\s\{\n)/",
    '<b style="color:purple;">${1}</b><b>${2}</b>${3}',
    $data
  );
  $data = preg_replace(
    "/(&?)(object\([^\n\s]+\))(#\d*\s\(\d+\)\s\{\n)/",
    '<b style="color:purple;">${1}</b><b>${2}</b>${3}',
    $data
  );
  // キーの可視性を上げる
  $data = preg_replace(
    "/(\s\[\"([^\n]+)\"\]=>\n)+?/",
    ' ["<b style="color:blue;">${2}</b>"]=>' . "\n",
    $data
  );
  $data = preg_replace(
    "/(\s\[(\d+)\]=>\n)+?/",
    ' [<b style="color:blue;">${2}</b>]=>' . "\n",
    $data
  );
  // <br />とインデントを追加
  $data = str_replace("\n", $return . "\n" . $indent, $data);
  // ２連の空白を $space に置換
  $data = str_replace(" ", $space, $data);
  if($get){
    return $indent . $data . $return . "\n";
  }else{
    echo $indent . $data . $return . "\n";
  }
}
