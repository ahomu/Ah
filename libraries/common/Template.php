<?php

/**
 * Template provides simply textbase template engine.
 *
 * @package     unknown
 * @copyright   2009 Hiroyuki Takahashi
 * @license     unknown
 * @author      Hiroyuki Takahashi (@_tk84)
 */
class Template
{
    var $_tokens        = array();

    var $_blockIdLabel  = array();
    var $_blockLabelId  = array();
    var $_blockIdTokenBegin = array();
    var $_blockIdTokenEnd   = array();
    var $_blockTokenIdBegin = array();
    var $_blockTokenIdEnd   = array();
    var $_blockIdTxt    = array(0=>null);

    var $_varIdLabel    = array();
    var $_varLabelId    = array();
    var $_varIdOption   = array();
    var $_varIdToken    = array();
    var $_varTokenId    = array();

    var $_Corrector     = null;

    function Template($txt, $Corrector=null)
    {
        if ( is_object($Corrector) and method_exists($Corrector, 'correct') ) {
            $this->_Corrector =& $Corrector;
        }

        $txt    = preg_replace(array(
            '@<!--[\t 　]*[BEGIN]{3,6}+[\t 　]+([^\t 　]+)[\t 　]*-->@',
            '@<!--[\t 　]*[END]{2,4}+[\t 　]+([^\t 　]+)[\t 　]*-->@',
            '@(?<!\\\)\{([^}\n]+)(?<!\\\)\}\[([^\]\n]+)\]@',
            '@(?<!\\\)\{([^}\n]+)(?<!\\\)\}@',
        ), array(
            '<!-- BEGIN $1 -->',
            '<!-- END $1 -->',
            '<!--%$1%-->$2 -->',
            '<!--%$1 -->',
        ), $txt);
        $txt    = str_replace(array('\{','\}'), array('{','}'), $txt);

        $tokens = preg_split('@(<!-- BEGIN |<!-- END | -->|<!--%|%-->)@'
            , $txt, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        //----------
        // validate
        $labels = array();
        $cnt    = count($tokens);
        for ( $i=0; $i<$cnt; $i++ ) {
            $token  = $tokens[$i];
            if ( '<!-- BEGIN ' == $token ) {
                $label  = $tokens[$i+1];
                if ( isset($labels[$label]) ) {
                    unset($tokens[$i]);
                    unset($tokens[$i+1]);
                    unset($tokens[$i+2]);
                } else {
                    $labels[$label] = $i;
                }
                $i  += 2;
            } else if ( '<!-- END ' == $token ) {
                $label  = $tokens[$i+1];
                if ( !isset($labels[$label]) ) {
                    unset($tokens[$i]);
                    unset($tokens[$i+1]);
                    unset($tokens[$i+2]);
                } else {
                    $from   = $labels[$label];
                    $to     = $i;
                    unset($labels[$label]);
                    foreach ( $labels as $_label => $pos ) {
                        if ( $from < $pos and $pos < $to ) {
                            unset($tokens[$pos]);
                            unset($tokens[$pos+1]);
                            unset($tokens[$pos+2]);
                            unset($labels[$_label]);
                        }
                    }
                    unset($labels[$label]);
                }
                $i  += 2;
            }
        }

        $i          = 1;
        $blockId    = 1;
        $varId      = 0;
        $this->_tokens[0]           = '';
        $this->_blockIdTokenBegin[0]= 0;
        while ( null !== ($token = array_shift($tokens)) ) {
            if ( '<!-- BEGIN ' == $token ) {
                $label  = array_shift($tokens);
                array_shift($tokens);

                $this->_blockIdTxt[$blockId]        = null;
                $this->_blockIdLabel[$blockId]      = $label;
                $this->_blockLabelId[$label][]      = $blockId;
                $this->_blockIdTokenBegin[$blockId] = $i;

                $blockId++;
                continue;
            } else if ( '<!-- END ' == $token )  {
                $label  = array_shift($tokens);
                array_shift($tokens);

                $ids    = $this->_blockLabelId[$label];
                $this->_blockIdTokenEnd[end($ids)]  = ($i-1);

                continue;
            } else if ( '<!--%' == $token ) {
                $label  = array_shift($tokens);

                $this->_varIdToken[$varId]      = $i;
                $this->_varIdLabel[$varId]      = $label;
                $this->_varLabelId[$label][]    = $varId;

                if ( '%-->' == array_shift($tokens) ) {
                    $this->_varIdOption[$varId] = array_shift($tokens);
                    array_shift($tokens);
                }
                $token  = null;

                $varId++;
            }
            $this->_tokens[$i++]    = $token;
        }
        $this->_tokens[$i]  = '';
        $this->_blockIdTokenEnd[0]  = $i;

        $this->_blockTokenIdBegin   = array_flip($this->_blockIdTokenBegin);
        $this->_blockTokenIdEnd     = array_flip($this->_blockIdTokenEnd);
        $this->_varTokenId          = array_flip($this->_varIdToken);

        return true;
    }

    function add($blocks=array(), $vars=array())
    {
        if ( null != $this->_blockIdTxt[0] ) {
            trigger_error('root is already touched.', E_USER_NOTICE);
            return false;
        }

        if ( !is_array($blocks) ) {
            $blocks = is_string($blocks) ? array($blocks) : array();
        }
        $blocks = array_reverse($blocks);
        if ( !is_array($vars) ) $vars = array();

        $pt     = 0;
        foreach ( $blocks as $block ) {
            if ( !isset($this->_blockLabelId[$block]) ) return false;
            $ids    = $this->_blockLabelId[$block];

            $id = null;
            foreach ( $ids as $_id ) {
                if ( $pt > $_id ) continue;
                $id = $_id;
                break;
            }
            if ( is_null($id) ) return false;
            if ( $this->_blockIdTokenEnd[$pt] < $this->_blockIdTokenEnd[$id] ) return false;
            $pt = $id;
        }
        $begin  = $this->_blockIdTokenBegin[$pt];
        $end    = $this->_blockIdTokenEnd[$pt];

        //----------
        // variable
        foreach ( $vars as $key => $value ) {
            if ( empty($this->_varLabelId[$key]) ) continue;
            $ids    = $this->_varLabelId[$key];
            foreach ( $ids as $id ) {
                $token  = $this->_varIdToken[$id];
                if ( $begin < $token and $token < $end ) {
                    if ( isset($this->_Corrector) ) {
                        $value  = $this->_Corrector->correct($value
                            , isset($this->_varIdOption[$id]) ? $this->_varIdOption[$id] : ''
                        , $key);
                    }
                    $this->_tokens[$token]  = strval($value);
                }
            }
        }

        //-------
        // touch
        $active     = array($pt => true);
        $ids        = array();
        $buf        = array();
         for ( $i=$begin; $i<=$end; $i++ ) {

            if ( isset($this->_blockTokenIdBegin[$i]) ) {
                array_unshift($ids, $this->_blockTokenIdBegin[$i]);
            }
            $id = $ids[0];

            if ( !empty($active[$id]) ) {
                $this->_blockIdTxt[$pt] .= $this->_tokens[$i];
            } else {
                if ( null !== $this->_blockIdTxt[$id] ) {
                    $txt    = '';
                    foreach ( $buf as $tokenId => $token ) {
                        if ( isset($this->_blockTokenIdBegin[$tokenId]) ) {
                            $active[$this->_blockTokenIdBegin[$tokenId]]    = true;
                        }
                        $txt    .= $token;
                    }
                    $this->_blockIdTxt[$pt] .= $txt;
                    $buf    = array();

                    $this->_blockIdTxt[$pt] .= $this->_blockIdTxt[$id];
                    $this->_blockIdTxt[$id] = null;
                    $i      = $this->_blockIdTokenEnd[$id];

                    array_shift($ids);
                    continue;
                } else if ( isset($this->_blockTokenIdEnd[$i]) ) {
                    for ( $j=$this->_blockIdTokenBegin[$id]; $j<$i; $j++ ) unset($buf[$j]);

                    array_shift($ids);
                    continue;
                }
                $buf[$i]    = $this->_tokens[$i];
            }

            if ( isset($this->_varTokenId[$i]) ) {
                if ( null !== $this->_tokens[$i] ) {
                    $txt    = '';
                    foreach ( $buf as $tokenId => $token ) {
                        if ( isset($this->_blockTokenIdBegin[$tokenId]) ) {
                            $active[$this->_blockTokenIdBegin[$tokenId]]    = true;
                        }
                        $txt    .= $token;
                    }
                    $this->_blockIdTxt[$pt] .= $txt;
                    $buf    = array();
                }
                $this->_tokens[$i]  = null;
            }

            if ( isset($this->_blockTokenIdEnd[$i]) ) array_shift($ids);
        }
    }

    function get()
    {
        if ( is_null($this->_blockIdTxt[0]) ) $this->add();
        return $this->_blockIdTxt[0];
    }
}
