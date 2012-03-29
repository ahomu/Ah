<?php

namespace ah\view;

/**
 * Template
 *
 * Templateクラスを元にしたViewクラス．
 * テンプレートエンジンのラッパメソッドです．
 */
class Template extends Base
{
    /**
     * Templateインスタンス
     * @var \Template
     */
    private $tpl;

    /**
     * __construct
     *
     * @param string $path
     */
    public function __construct($path)
    {
        if (!empty($path) ) {
            $this->setTpl($path);
        }
        parent::__construct();
    }

    /**
     * setTpl
     *
     * @param string $path
     * @return void
     */
    public function setTpl($path)
    {
        $tplPath = implode('/', array_merge(explode('/', DIR_TPL), explode('/', $path)));

        if ( is_readable($tplPath) ) {
            $tpl = file_get_contents($tplPath);
            $this->tpl = new \Template($tpl);
        } else {
            $this->tpl = null;
        }
    }

    /**
     * hasTpl
     *
     * @return boolean
     */
    public function hasTpl()
    {
        return $this->tpl instanceof \Template;
    }

    /**
     * add
     *
     * @param null|string|array $blocks
     * @param array $vars
     * @return \ah\View\Template
     */
    public function add($blocks = null, $vars = array())
    {
        $this->tpl->add($blocks, $vars);
        return $this;
    }

    /**
     * build
     * 
     * @param array $vars
     * @param null $block
     * @return \ah\View\Template
     */
    public function build(array &$vars, $block = null)
    {
        $stack      = array();
        $isHash     = is_hash($vars);
        $isTouch    = empty($vars);

        foreach ( $vars as $key => $val ) {
            if ( is_array($val) ) {
                // hashでなければ、loop等と見なして親ブロックを固定
                $val = array_clean($val);
                $key = ($isHash === false) ? $block : $key;
                $this->build($val, $key);
            } else {
                $stack[$key] = $val;
            }
        }
        if ( $isHash === true || $isTouch === true ) {
            $this->tpl->add($block, $stack);
        }

        return $this;
    }

    /**
     * render
     *
     * @return string $bulidedTempalte
     */
    public function render()
    {
        return $this->tpl->get();
    }

}