AhはPHPのWebAPIフレームワークです．作っている最中です．
全称(Axxxxx Hxxxxx?)を考え中．

##Ahについて

作法的です．

+  CakePHPやSymfony，CodeIgniterなど世間のPHPフレームワークを使ったことがない
+  フレームワークを理解したいなら，フレームワークを作って理解すればいいじゃない
+  ひとつひとつのActionに，WebAPIのような振る舞いを強制する作法ってどうだろう
+  究極的にはControllerやViewなんてフロントエンドのJSに託してしまってもいいんじゃ



##Actionの振る舞い

+  明示的なConrollerを持たず，実行されるActionとURLが直結している
+  すべてのActionは，WebAPIのようにパスと引数を指定してアクセスできる
+  すべてのActionは，リクエストメソッドによって異なった振る舞いを定義できる
+  Actionへのリクエストは，内部と外部を問わず同様のインターフェースである
+  Actionへのリクエストは，内部または外部からのリクエストに限定することができる



##Actionの起動ルールと，ファイルの既定位置

クラス名はリクエストと対応します．/fooのリクエストで呼び出されるActionは，Action_Fooとなります．この場合のクラスファイルは，actionディレクトリ(/app/action)内に，/action/Foo.action.phpとして配置します．クラス名の部分は常にアッパーケースであることに注意して下さい．

指定されたリクエストメソッドによって，Action内で実行されるメソッドが異なります．GETリクエストであればgetメソッドが，POSTリクエストであればpostメソッドがメインプロセスとして実行されます．

GET /
    Action_Index::get()
    /app/action/Index.action.php

GET /foo
    Action_Foo::get()
    /app/action/Foo.action.php

POST /foo/bar
    Action_Foo_Bar::post()
    /app/action/Hoge/Fuga.action.php



##Ah_ResolverによるActionの起動パターン

Actionは，Ah_Resolverによって起動します．

Ah_Resolverクラスはexternalとinternalとredirectというスタティックメソッドを持ちます．これらは，リクエストパスとメソッドを与えることで，パスに対するActionの解決と実行を行います．

###externalメソッド
通常は，Applicationクラスで一度だけ実行されます．Actionはoutputメソッドを起動し，HTTPレスポンスを返します

###internalメソッド
内部的に別のアクションを呼び出す際に利用します．internalメソッドによって実行されたActionは実行メソッドが処理された後の状態で自分自身のインスタンスを返します．例えば，これをそのまま呼び出し元の実行メソッドの戻り値とすることで，あるActionの結果に他のActionの結果を，そのまま流用する事ができます．

###includesメソッド
基本的にはinternalと同様ですが，インスタンスではなくレスポンスボディのみを返します．

###redirectメソッド
クライアントをリダイレクトさせます．httpまたはhttpsで始まるリクエストでない場合は，現在のベースURLを元に補完します．リダイレクト先には，GETに限りパラメーターの受け渡しが可能です．

###各メソッドの呼び出し例

####external
    $path   = \Ah\Request::getPath();
    $method = \Ah\Request::getMethod();
    \Ah\Resolver::external($path, $method);

####internal
    \Ah\Resolver::internal('/hoge/fuga', 'POST');
    Action_Hoge_Fuga::post()

####includes
    \Ah\Resolver::includes('/hoge', 'GET');
    Action_Hoge::get()

####redirect
    \Ah\Resolver::redirect('http://example.com');



##Actionクラスのテンプレート

###サンプル ( php )

    <?php

    class Action_Index extends \Ah\Action\Abstract
    {
        protected
            // 引数として受け取れるパラメーターの定義
            $_receive_params = array(
                'hoge'
            ),
            // 自動バリデートのルール
            $_validate_rule = array(
                'hoge' => array('required')
            ),
            // 内部からの呼び出し(Ah_Resolver::internal)を禁止する
            $_allow_internal    = false;

        public function get()
        {
            /**
             * サンプルリクエスト
             * GET /?hoge=fuga
             *
             * + \Ah\Params $this->Params
             * \Ah\Resolver::externalから呼ばれた場合はリクエストがGETであれば$_GETが，POSTであれば$_POSTが自動セットされる．
             * internalから呼ばれた場合は，第3引数に指定された連想配列が自動セットされる
             *
             * + \Ah\Response $this->Response
             * HTTPレスポンスを管理する．\Ah\Resolver::externalから呼ばれた場合はクライアントに返されるが，
             * internalから呼ばれた場合は，レスポンスは実行されず，ActionインスタンスがResponseを内包したまま返される．
             *
             */

            // パラメーターの取得
            $this->Params->get('hoge');     // 'fuga'

            // パラメーターのバリデート結果
            $this->Params->isValid('hoge'); // true

            // MIMEタイプの指定（デフォルトはtext/html）
            $this->Response->setMimeType(Util_MIME::detectType('html'));

            // ステータスコードの指定（デフォルトは200）
            $this->Response->setStatusCode(200);

            // レスポンスボディの指定
            $this->Response->setBody('Hello World');
        }
    }



##リクエストマッピング

リクエストパスに対するパラメーターマッピングも可能です．http://example.com/entry/123/ のようなリクエストに対して，Entryアクションを起動し，123をアクションに対するパラメーターとして与えることが可能です．

マッピングは現在，/app/config/config.map.yaml で定義します．マッピングによって決定されたパラメーターがある場合，Actionにスーパーグローバルなパラメーターはセットされません．

###サンプル ( yml )

    arguments_mapper    :
      - GET :
        - /foo/bar :
          - id
          - status
          - order


上記のサンプルでは，/foo/bar/1/close/recent というリクエストによってAction_Foo_Barが起動し，パラメーター配列はarray('id'=>1, 'status' => 'close', 'order' => 'recent')というように変換されてセットされます．
