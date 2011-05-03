<?php

namespace ah;

use ah\exception;

/**
 * ah\Autoloader
 *
 * オートロードのサポートを行うクラス．
 * 基本的には，ah(libraries/ah/)とapp(app/)の名前空間からのロードをサポートする．
 *
 * その他，PEARのような従来ライブラリにおけるFoo_Bar_Classのような命名規則は，
 * libraries/commonと，app/common内でサポートする
 *
 * クラス名と，読み込まれるファイルの対応例：
 * \app\action\Index    app/action/Index.php
 * \ah\action\Base      libraries/ah/action/Base.php
 * Foo_Bar_Class        libraries/common/foo/bar/Class.php または
 *                      app/common/foo/bar/Class.php
 *
 * ※ symfony由来のクラスなどは，固有のルールで読み込まれる
 *
 * @package     Ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Autoloader
{
    /**
     * オートローダーに処理を登録する．
     * 簡易的には，無名関数を渡してもよいし，従来通りの関数指定をしてもよい．
     *
     * {{{
     * \ah\Autoloader::register(function($className) {
     *     // ロード処理
     * }, true);
     * }}}
     *
     * @param callable $func
     * @param bool $throw
     * @return void
     */
    public function register($func, $throw = false)
    {
        spl_autoload_register($func, $throw);
    }

    /**
     * ahのライブラリサポート範囲のオートロード処理を，プレフィックスで振り分ける
     *
     * @param string $className
     * @see ah\Autoloader::ahCoreLoad()
     * @see ah\Autoloader::ahAppLoad()
     * @see ah\Autoloader::ahCommonLoad()
     * @return void
     */
    public function ahLoad($className)
    {
        $package = $this->_getNamespace($className);

        switch ($package) {
            case 'ah'       :
                $this->ahCoreLoad($className);
                break;
            case 'app'   :
                $this->ahAppLoad($className);
                break;
            default         :
                $this->ahCommonLoad($className);
                break;
        }

    }

    /**
     * appディレクトリからロードする．名前空間を使用する必要がある．
     * 名前空間を使わない旧来のライブラリを共存させる場合は，commonディレクトリを利用する．
     *
     * @param string $className
     * @return void
     */
    public function ahAppLoad($className)
    {
        $className = substr($className, strlen('app\\'));
        $this->_traversal(DIR_APP, $className);
    }

    /**
     * libraries/ahディレクトリからロードする．
     *
     * @param string $className
     * @return void
     */
    public function ahCoreLoad($className)
    {
        $this->_traversal(DIR_LIB, $className);
    }

    /**
     * 従来の命名規則のクラス（たとえばFoo_Bar_Class）を，commonディレクトリからロードする．
     * librariesとappのそれぞれのcommonディレクトリに対して試行する
     *
     * @param string $className
     * @return void
     */
    public function ahCommonLoad($className)
    {
        $this->_traversal(DIR_LIB.'/common', $className);
        $this->_traversal(DIR_APP.'/common', $className);
    }

    /**
     * Symfony由来のライブラリをロードする
     *
     * @param string $className
     * @return void
     */
    public function sfLoad($className)
    {
        $filePath = DIR_LIB.'/vendor/sf/'.$className.'.php';
        $this->_load($filePath);
    }

    /**
     * クラス名の先頭からルートの名前空間に相当する部分を取得する．
     *
     * @param string $className
     * @return string
     */
    private function _getNamespace($className)
    {
        $separator = $this->_getSeparator($className);

        return substr($className, 0, strpos($className, $separator));
    }

    /**
     * クラス名のセパレータを取得する．
     *
     * @param string $className
     * @return string
     */
    private function _getSeparator($className)
    {
        return strpos($className, '\\') !== false ? '\\' : '_';
    }

    /**
     * 探索元のベースパスとクラス名を元にファイルパスを生成し，ロードを試みる．
     *
     * @param string $basepath
     * @param string $className
     * @return void
     */
    private function _traversal($basepath, $className)
    {
        $separator = $this->_getSeparator($className);

        $pathStack = explode($separator, $className);
        array_unshift($pathStack, $basepath);

        $filePath  = implode('/', $pathStack).'.php';
        $this->_load($filePath);
    }

    /**
     * 渡されたファイルパスが，読み込み可能な状態であれば，require_onceする．
     *
     * @param string $filePath
     * @return void
     */
    private function _load($filePath)
    {
        if ( is_readable($filePath) ) {
            require_once($filePath);
        }
    }

    /**
     * すべてのオートロード処理に失敗した場合に，例外を投げる．
     *
     * @param string $className
     * @return void
     */
    public function terminate($className)
    {
        throw new exception\NotFound($className);
    }
}
