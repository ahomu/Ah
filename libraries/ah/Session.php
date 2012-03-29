<?php

namespace ah;

/**
 * Session
 *
 * @package     ah
 * @copyright   2011 ayumusato.com
 * @license     MIT License
 * @author      Ayumu Sato
 */
class Session
{
    /**
     * Sessionのsingletonインスタンス
     *
     * @var \ah\Session
     */
    protected static $INSTANCE;

    /**
     * セッション名
     *
     * @var string
     */
    protected $sess_name = 'ah_session';

    /**
     * セッション変数内で，このSessionクラスのデータを格納するキー名
     *
     * @var string
     */
    protected $sess_storage = 'ah_storage';

    protected $sess_tokens  = 'ah_token';

    /**
     * インスタンス内で保持するセッションデータ
     *
     * @var array|mixed
     */
    protected $storage;

    /**
     * セッションクッキーの有効時間(秒)
     *
     * @var int
     */
    protected $lifetime  = 604800;

    /**
     * クッキーが有効なパス
     *
     * @var string
     */
    protected $path      = '';

    /**
     * クッキーが有効なドメイン
     *
     * @var string
     */
    protected $domain    = '';

    /**
     * HTTPSを強制
     *
     * @var bool
     */
    protected $secure    = false;

    /**
     * HTTPのアクセスのみで参照
     *
     * @var bool
     */
    protected $httponly  = true;

    /**
     * singletonインスタンスを返却する
     * インスタンスが未作成であれば，生成して初期化
     * $configが有効なのは，最初のメソッド使用〜コンストラクタ起動時のみ．
     *
     * @param array $config
     * @return \Ah\Session
     */
    public static function getInstance($config = array())
    {
        if ( self::$INSTANCE === null ) {
            self::$INSTANCE = new self($config);
        }
        return self::$INSTANCE;
    }

    /**
     * コンストラクタ
     *
     * singletonパターンに基づいて，
     * Session::getInstance自身のみがインスタンスにすることができる
     *
     * @param array $config
     */
    private function __construct($config)
    {
        // default
        $host = Request::getHost();
        $this->path   = '/';
        $this->domain = ($host !== 'localhost') ? $host : false;

        // configのオーバーライト
        foreach ( $config as $key => $val ) {
            if ( !property_exists($this, $key) ) {
                continue;
            }
            $this->$key = $val;
        }

        // 上位ドメインのクッキーを削除する( Session Adoption )
        $domainTokens = array_reverse(explode('.', $this->domain));
        array_pop($domainTokens);

        $highLevelDomain = '';
        foreach ( $domainTokens as $domainToken ) {
            $highLevelDomain = empty($highLevelDomain) ? $domainToken
                                                       : $domainToken.'.'.$highLevelDomain;
            setcookie($this->sess_name, '', time()-666, $this->path, $highLevelDomain);
        }

        // セッションの開始
        session_name($this->sess_name);
        session_set_cookie_params($this->lifetime, $this->path, $this->domain, $this->secure, $this->httponly);
        session_start();

        // セッション変数から，前回の状態を取得
        $this->storage = isset($_SESSION[$this->sess_storage]) ? unserialize($_SESSION[$this->sess_storage])
                                                               : array();
    }

    /**
     * デストラクタ
     *
     * セッション変数に，インスタンスのstorageを格納する
     */
    public function  __destruct()
    {
        $self = self::$INSTANCE;
        $_SESSION[$self->sess_storage] = serialize($self->storage);
    }

    /**
     * storageから特定のキーのバリューを返却する
     *
     * @param string $key
     * @return array|bool|mixed
     */
    public function get($key)
    {
        return isset($this->storage[$key]) ? $this->storage[$key] : false;
    }

    /**
     * storageに特定のキーとバリューのペアをセットする
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function set($key, $val)
    {
        $this->storage[$key] = $val;
    }

    /**
     * storageの特定のキーをクリアする
     *
     * @param string $key
     * @return void
     */
    public function clear($key)
    {
        unset($this->storage[$key]);
    }

    /**
     * storageを空にする
     *
     * @return void
     */
    public function destroy()
    {
        $this->storage = array();
    }

    /**
     * cookieを更新する
     * 0 でブラウザ終了まで, -1 で即時削除
     *
     * @param int|null $lifetime
     * @return bool
     */
    public function expire($lifetime = null)
    {
        $lifetime = ($lifetime !== null) ? intval($lifetime) : $this->lifetime;
        return setcookie(session_name(), session_id(), time()+$lifetime, $this->path, $this->domain, $this->secure, $this->httponly);
    }

    /**
     * セッションIDを更新する
     * ログインしたとき等，重要なセッションの更新タイミングで実行する ( Session Fixation )
     *
     * @return bool
     */
    public function regenerate()
    {
        return session_regenerate_id(true);
    }

    /**
     * 現在のセッションIDを取得する
     * CSRF対策のトークンとして使用する際に利用する
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 与えた文字列が，現在のセッションIDと一致するか検査する
     *
     * @param string $id
     * @return bool
     */
    public function checkId($id)
    {
        return ($id === session_id());
    }

    public function getToken($ns = '')
    {
        $id     = $ns.$this->sess_tokens;
        $tokens = $this->get($id);

        if ( $tokens === false ) $tokens = array();
        if ( count($tokens) >= 5 ) array_shift($tokens);

        $token  = sha1($ns.$this->getId().microtime());

        $tokens[] = $token;
        $this->set($id, $tokens);

        return $token;
    }

    public function checkToken($token, $unset = true, $ns = '')
    {
        $id     = $ns.$this->sess_tokens;
        $tokens = $this->get($id);

        if ( ($pos = array_search($token, $tokens, true)) !== false ) {
            if ( $unset === true) {
                unset($tokens[$pos]);
            } else {
                // 使用されたトークンを最後尾に移動して延命
                $tokens[] = $tokens[$pos];
                unset($tokens[$pos]);
            }
            $this->set($id, $tokens);
            return true;
        } else {
            return false;
        }
    }
}
