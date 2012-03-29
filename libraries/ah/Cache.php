<?php

namespace ah;

// TODO issue: staticで扱うべきか要検討

/**
 * ah\Cache
 *
 * 静的ファイルのキャッシュ管理を行うクラス．
 * 主にyamlのようロードにコストのかかるファイルの中身を
 * シリアライズして保存しておく用途にする．
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Cache
{
    /**
     * 指定した静的ファイルに更新があったかを確認する
     * キャッシュファイルがなかったら true
     *
     * @param string $realPath
     * @param string $ns
     * @return bool
     */
    public static function isModified($realPath, $ns)
    {
        $cachePath  = self::_getPath($realPath, $ns);

        if ( !file_exists($cachePath) || filemtime($cachePath) < filemtime($realPath) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 指定したパス+nsからキャッシュを同定してロードする．
     * 戻り値を，emptyなどで厳密でない判定をすると，
     * 空のキャッシュを判別できないので要注意．
     * 一度にすべて読み込むので，
     *
     * @param string $realPath
     * @param string $ns
     * @return mixed
     */
    public static function load($realPath, $ns = null)
    {
        // TODO issue: ファイルポインタを返すメソッドも必要?
        $cachePath = self::_getPath($realPath, $ns);
        if ( is_readable($cachePath) ) {
            return file_get_contents($cachePath);
        } else {
            return false;
        }
    }

    /**
     * キャッシュコンテンツを保存する．
     * 書き込みに成功したらtrue, 失敗したらfalse
     *
     * @param string $realPath
     * @param mixed $content
     * @param string $ns
     * @return bool|int
     */
    public static function save($realPath, $content, $ns)
    {
        $cachePath = self::_getPath($realPath, $ns);

        $dirPath   = dirname($cachePath);

        if ( !file_exists($dirPath) && is_writable(dirname($dirPath)) ) {
            mkdir($dirPath, PERM_EDITABLE_DIR);
        }

        if ( is_writable($dirPath) ) {
            return file_put_contents($cachePath, $content);
        } else {
            return false;
        }
    }

    /**
     * キャッシュコンテンツを削除する．
     * 削除に成功したらtrue，失敗したらfalse
     * キャッシュが存在しなかった場合はtrueを返す．
     *
     * @param string $realPath
     * @param string $ns
     * @return bool
     */
    public static function clear($realPath, $ns)
    {
        $cachePath = self::_getPath($realPath, $ns);
        if ( !file_exists($cachePath) ) {
            return true;
        } else {
            return unlink($cachePath);
        }
    }

    /**
     * realpathからハッシュを生成し，
     * nsの指定はディレクトリ名，cacheディレクトリ内のサブディレクトリ名となる．
     * (nsの指定に / は認めていないので，存在すると除去される)
     *
     * @param string $realPath
     * @param string $ns
     * @return string
     */
    private static function _getPath($realPath, $ns)
    {
        $ns = str_replace('/', '', $ns);

        if ( $ns === null ) {
            return DIR_TMP."/".sha1($realPath);
        } else {
            return DIR_TMP."/$ns/".sha1($realPath);
        }
    }
}