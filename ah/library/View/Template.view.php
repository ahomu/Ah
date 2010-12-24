<?php

class View_Template extends View_Abstract
{
    private $tpl;

    /**
     * __construct
     *
     * @param string $path
     * @param string $extension
     */
    public function __construct($path = null, $extension = null)
    {
        if ( 1
            and $path !== null
            and $extension !== null
        ) {
            $this->setTpl($path, $extension);
        }
        return parent::__construct();
    }

    /**
     * setTpl
     *
     * @param string $path
     * @param string $extension
     * @return void
     */
    public function setTpl($path, $extension)
    {
        $tplPath = implode('/', array_merge(explode('/', DIR_TPL), explode('/', $path))).".$extension";

        if ( is_readable($tplPath) ) {
            $tpl = file_get_contents($tplPath);
            $this->tpl = new Template($tpl);
        } else {
            // throw exception template file is not found
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
        return is_object($this->tpl);
    }

    /**
     * add
     *
     * @param mixed $blocks
     * @param mixed $vars
     * @return chain
     */
    public function add($blocks=array(), array $vars=array())
    {
        $this->tpl->add($blocks, $vars);
        return $this;
    }

    /**
     * build
     * 
     * @param array $vars
     * @param null $block
     * @return chain
     */
    public function build(array &$vars, $block = null)
    {
        $stack      = array();
        $isHash     = is_hash($vars);
        $isTouch    = empty($vars);

        foreach ( $vars as $key => $val ) {
            if ( is_array($val) ) {
                // isHash? || 旧メモ(前提条件として連想配列のkeyにintが使われないこと)
                // hashでなければ、loop等と見なして親ブロックを固定
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