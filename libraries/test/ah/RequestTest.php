<?php
namespace ah;

require_once dirname(__FILE__).'/../init.php';

/**
 * Test class for Request.
 * Generated by PHPUnit on 2011-05-05 at 22:17:08.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $object;
    protected $SERVER;
    protected $GET;
    protected $POST;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->SERVER = $_SERVER;
        $this->GET    = $_GET;
        $this->POST   = $_POST;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_SERVER = $this->SERVER;
        $_POST   = $this->GET;
        $_GET    = $this->POST;
    }

    public function serverProvider()
    {
        return array(
            array(array(
                'HTTP_HOST'       => 'ayumusato.com:443',
                'REQUEST_METHOD'  => 'GET',
                'SCRIPT_NAME'     => '/hoge/index.php',
                'REQUEST_URI'     => '/hoge/fuga/piyo/?hoge=fuga',
                'HTTPS'           => 'on',

                'EXPECT_HOST'     => 'ayumusato.com:443',
                'EXPECT_ROOT'     => 'https://ayumusato.com:443/',
                'EXPECT_PORT'     => 443,
                'EXPECT_BASE'     => '/hoge',
                'EXPECT_PATH'     => '/fuga/piyo/',
                'EXPECT_EXTENSION'=> '',
            )),
            array(array(
                'HTTP_HOST'       => 'havelog.ayumusato.com:80',
                'REQUEST_METHOD'  => 'GET',
                'SCRIPT_NAME'     => '/fuga/index.php',
                'REQUEST_URI'     => '/fuga/piyo/',
                'HTTPS'           => null,

                'EXPECT_HOST'     => 'havelog.ayumusato.com:80',
                'EXPECT_ROOT'     => 'http://havelog.ayumusato.com:80/',
                'EXPECT_PORT'     => 80,
                'EXPECT_BASE'     => '/fuga',
                'EXPECT_PATH'     => '/piyo/',
                'EXPECT_EXTENSION'=> '',
           )),
            array(array(
                'HTTP_HOST'       => 'ahomu.example.com',
                'HTTP_X_FORWARDED_HOST' => 'ah.ayumusato.com',
                'REQUEST_METHOD'  => 'GET',
                'SCRIPT_NAME'     => '/index.php',
                'REQUEST_URI'     => '/foo/bar.html',
                'HTTPS'           => null,

                'EXPECT_HOST'     => 'ah.ayumusato.com',
                'EXPECT_ROOT'     => 'http://ah.ayumusato.com/',
                'EXPECT_PORT'     => '',
                'EXPECT_BASE'     => '',
                'EXPECT_PATH'     => '/foo/bar.html',
                'EXPECT_EXTENSION'=> 'html',
            )),
        );
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetHost($vars)
    {
        $_SERVER = $vars;
        $host = Request::getHost();
        $this->assertEquals($_SERVER['EXPECT_HOST'], $host);

        return $host;
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetPort($vars)
    {
        $_SERVER = $vars;
        $port = Request::getPort();
        $this->assertEquals($_SERVER['EXPECT_PORT'], $port);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetRootUri($vars)
    {
        $_SERVER = $vars;
        $root = Request::getRootUri();
        $this->assertEquals($_SERVER['EXPECT_ROOT'], $root);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetRequestUri($vars)
    {
        $_SERVER = $vars;
        $request = Request::getRequestUri();
        $this->assertEquals($_SERVER['REQUEST_URI'], $request);

        return $request;
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetBaseUri($vars)
    {
        $_SERVER = $vars;
        $base = Request::getBaseUri();

        // 末尾に / をつけない
        $this->assertFalse(!!(preg_match('/\/$/', $base)));
        $this->assertEquals($_SERVER['EXPECT_BASE'], $base);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetPath($vars)
    {
        $_SERVER = $vars;
        $path = Request::getPath();
        $this->assertEquals($_SERVER['EXPECT_PATH'], $path);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetExtension($vars)
    {
        $_SERVER = $vars;
        $ext = Request::getExtension();
        $this->assertEquals($_SERVER['EXPECT_EXTENSION'], $ext);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testIsSsl($vars)
    {
        $_SERVER = $vars;
        $ssl = Request::isSsl();
        $this->assertInternalType('bool', $ssl);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testIsXhr($vars)
    {
        $_SERVER = $vars;
        $xhr = Request::isXhr();
        $this->assertInternalType('bool', $xhr);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testIsAcceptGzip($vars)
    {
        $_SERVER = $vars;
        $gzip = Request::isAcceptGzip();
        $this->assertInternalType('bool', $gzip);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetParams($vars)
    {
        $_SERVER = $vars;
        $_GET  = array('hoge'=>'fuga');
        $_POST = array();

        $params = Request::getParams('GET');
        $this->assertArrayHasKey('hoge', $params);
        $this->assertEquals('fuga', $params['hoge']);

        $params = Request::getParams('POST');
        $this->assertEquals(array(), $params);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetMethod($vars)
    {
        $_SERVER = $vars;
        $base = Request::getMethod();
        $this->assertEquals('GET', $base);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetReferer($vars)
    {
        $_SERVER = $vars;
        $ref = Request::getReferer();
        $this->assertEquals('', $ref);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGetUa($vars)
    {
        $_SERVER = $vars;
        $ua = Request::getUa();
        $this->assertEquals('', $ua);
    }

}
?>
